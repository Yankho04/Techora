
<?php
session_start();
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/activity_log.php";

// Allow administrators only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

$image = $_FILES['image'] ?? null;
$image_path = '';

if ($image && $image['error'] === UPLOAD_ERR_OK) {

    $allowed_types = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    $file_type = mime_content_type($image['tmp_name']);

    if (isset($allowed_types[$file_type])) {

        $extension = $allowed_types[$file_type];
        $file_name = uniqid('product_', true) . '.' . $extension;

        $upload_directory = __DIR__ . '/../assets/images/';

        if (move_uploaded_file(
            $image['tmp_name'],
            $upload_directory . $file_name
        )) {
            $image_path = 'assets/images/' . $file_name;
        }
    }
}
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $category = trim($_POST['category'] ?? '');
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

    if ($name === '' || $description === '' || $category === ''
    || $price === false || $price === null || $price <= 0
    || $stock === false || $stock === null || $stock < 0
    || $image_path === '') {

       $message = "Please enter valid product details and select a valid picture.";

    } else {
       
$sql = "INSERT INTO products
        (name, description, price, category, stock, image)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt && mysqli_stmt_bind_param(
    $stmt, "ssdsis",
    $name, $description, $price, $category, $stock, $image_path
) && mysqli_stmt_execute($stmt)) {

    logAdminActivity(
        $conn,
        (int) $_SESSION['user_id'],
        "Added Product",
        "Added product: " . $name
    );

    mysqli_stmt_close($stmt);

    header("Location: products.php");
    exit;

} else {

    $message = "Could not add the product. Please try again.";

}

    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Techora</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <h1>Add New Product</h1>

    <p><?php echo htmlspecialchars($message); ?></p>

<form method="POST" action="add_product.php"
      enctype="multipart/form-data" class="add-product-form">

    <label for="name">Product Name:</label><br>
    <input type="text" name="name" id="name" required><br><br>

    <label for="description">Description:</label><br>
    <textarea name="description" id="description" required></textarea><br><br>

    <label for="price">Price (MWK):</label><br>
    <input type="number" name="price" id="price"
           min="0.01" step="0.01" required><br><br>

   <label for="category">Category:</label><br>
<select name="category" id="category" required>
    <option value="">Select Category</option>
    <option value="Phone Cases">Phone Cases</option>
    <option value="Chargers">Chargers</option>
    <option value="USB Cables">USB Cables</option>
    <option value="Earphones">Earphones</option>
    <option value="Power Banks">Power Banks</option>
    <option value="Screen Protectors">Screen Protectors</option>
</select>
<br><br>

    <label for="stock">Stock Quantity:</label><br>
    <input type="number" name="stock" id="stock"
           min="0" step="1" required><br><br>

<label for="image">Product Picture:</label><br>

<input type="file" name="image" id="image" accept="image/*" required>

<p id="imageError" style="color: red;"></p>

<br><br>

<img id="imagePreview" src="" alt="Product image preview"
     style="display: none; width: 200px; height: 200px; object-fit: contain; border: 1px solid #ccc; padding: 5px;">

<br><br>
<button type="submit" class="add-product-btn">Add Product</button>
</form>

<p><a href="products.php">Back to Products</a></p>
<p><a href="dashboard.php">Back to Dashboard</a></p>

<script>
const imageInput = document.getElementById('image');
const imagePreview = document.getElementById('imagePreview');
const imageError = document.getElementById('imageError');

imageInput.addEventListener('change', function () {

    const file = imageInput.files[0];

    // Reset previous error
    imageError.textContent = '';

    if (!file) {
        imagePreview.style.display = 'none';
        imagePreview.src = '';
        return;
    }

    // Allowed image types
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    if (!allowedTypes.includes(file.type)) {
        imageError.textContent = 'Please select a JPG, PNG, or WEBP image.';
        imageInput.value = '';
        imagePreview.style.display = 'none';
        imagePreview.src = '';
        return;
    }

    // Maximum file size: 2 MB
    const maxSize = 2 * 1024 * 1024;

    if (file.size > maxSize) {
        imageError.textContent = 'Image size must not exceed 2 MB.';
        imageInput.value = '';
        imagePreview.style.display = 'none';
        imagePreview.src = '';
        return;
    }

    // Display valid image
    imagePreview.src = URL.createObjectURL(file);
    imagePreview.style.display = 'block';

});
</script>

<script>
const addProductForm = document.querySelector('.add-product-form');

addProductForm.addEventListener('submit', function () {

    const addButton = addProductForm.querySelector('.add-product-btn');

    addButton.textContent = 'Adding Product...';
    addButton.disabled = true;

});
</script>
</body>
</html>
