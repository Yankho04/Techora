
<?php
session_start();
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/activity_log.php";

// Allow administrators only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    die("Invalid product ID.");
}

$sql = "SELECT * FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    die("Product not found.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $category = trim($_POST['category'] ?? '');
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

    $image_path = $product['image']; // Keep the existing picture

if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {

        $allowed_types = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        $file_type = mime_content_type($_FILES['image']['tmp_name']);

        if (isset($allowed_types[$file_type])) {

            $extension = $allowed_types[$file_type];
            $file_name = uniqid('product_', true) . '.' . $extension;

            $upload_directory = __DIR__ . '/../assets/images/';

            if (move_uploaded_file(
                $_FILES['image']['tmp_name'],
                $upload_directory . $file_name
            )) {
                $image_path = 'assets/images/' . $file_name;
            } else {
                $message = "Could not upload the picture.";
            }

        } else {
            $message = "Only JPG, PNG and WEBP pictures are allowed.";
        }

    } else {
        $message = "There was an error uploading the picture.";
    }
}

    if ($name === '' || $description === '' || $category === ''
        || $price === false || $price === null || $price <= 0
        || $stock === false || $stock === null || $stock < 0) {

        $message = "Please enter valid product details.";

    } else {
       $sql = "UPDATE products
        SET name = ?, description = ?, price = ?,
            category = ?, stock = ?, image = ?
        WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);
       mysqli_stmt_bind_param(
    $stmt, "ssdsisi",
    $name, $description, $price, $category,
    $stock, $image_path, $id
    
);


       if (mysqli_stmt_execute($stmt)) {

    logAdminActivity(
        $conn,
        (int) $_SESSION['user_id'],
        "Edited Product",
        "Updated product: " . $name . " (ID: " . $id . ")"
    );

    mysqli_stmt_close($stmt);

    header("Location: products.php");
    exit;

} else {
    mysqli_stmt_close($stmt);
    $message = "Could not update the product. Please try again.";
}

    } 

} 

?>
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Techora</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<h1>Edit Product</h1>

<p><?php echo htmlspecialchars($message); ?></p>

<form method="POST"
      action="edit_product.php?id=<?php echo (int) $product['id']; ?>"
      enctype="multipart/form-data"
      class="edit-product-form">

    <label for="name">Product Name:</label><br>
    <input type="text" name="name" id="name"
           value="<?php echo htmlspecialchars($product['name']); ?>"
           required><br><br>

    <label for="description">Description:</label><br>
    <textarea name="description" id="description"
              required><?php echo htmlspecialchars($product['description']); ?></textarea><br><br>

    <label for="price">Price (MWK):</label><br>
    <input type="number" name="price" id="price" min="0.01"
           step="0.01"
           value="<?php echo htmlspecialchars($product['price']); ?>"
           required><br><br>

    <label for="category">Category:</label><br>

<select name="category" id="category" required>
    <option value="Phone Cases" <?php echo $product['category'] === 'Phone Cases' ? 'selected' : ''; ?>>Phone Cases</option>

    <option value="Chargers" <?php echo $product['category'] === 'Chargers' ? 'selected' : ''; ?>>Chargers</option>

    <option value="USB Cables" <?php echo $product['category'] === 'USB Cables' ? 'selected' : ''; ?>>USB Cables</option>

    <option value="Earphones" <?php echo $product['category'] === 'Earphones' ? 'selected' : ''; ?>>Earphones</option>

    <option value="Power Banks" <?php echo $product['category'] === 'Power Banks' ? 'selected' : ''; ?>>Power Banks</option>

    <option value="Screen Protectors" <?php echo $product['category'] === 'Screen Protectors' ? 'selected' : ''; ?>>Screen Protectors</option>
</select>

<br><br>

    <label for="stock">Stock Quantity:</label><br>
    <input type="number" name="stock" id="stock" min="0" step="1"
           value="<?php echo (int) $product['stock']; ?>"
           required><br><br>

          <label>Current Product Picture:</label><br>

<?php if (!empty($product['image'])): ?>
    <img id="imagePreview"
         src="../<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>"
         alt="Current product picture"
         width="200"
         style="object-fit: contain; border: 1px solid #ccc; padding: 5px;">
<?php else: ?>
    <p>No picture available.</p>

    <img id="imagePreview"
         src=""
         alt="Product image preview"
         width="200"
         style="display: none; object-fit: contain; border: 1px solid #ccc; padding: 5px;">
<?php endif; ?>

<br><br>

<label for="image">Upload New Picture:</label><br>

<input type="file" name="image" id="image"
       accept="image/jpeg,image/png,image/webp">

<p id="imageError" style="color: red;"></p>

<br><br>

    <button type="submit" class="edit-product-btn">Update Product</button>

</form>

<p><a href="products.php">Back to Products</a></p>
<p><a href="dashboard.php">Back to Dashboard</a></p>

<script>
const imageInput = document.getElementById('image');
const imagePreview = document.getElementById('imagePreview');
const imageError = document.getElementById('imageError');

imageInput.addEventListener('change', function () {

    const file = imageInput.files[0];

    imageError.textContent = '';

    if (!file) {
        return;
    }

    // Allowed image types
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    if (!allowedTypes.includes(file.type)) {
        imageError.textContent = 'Please select a JPG, PNG, or WEBP image.';
        imageInput.value = '';
        return;
    }

    // Maximum file size: 2 MB
    const maxSize = 2 * 1024 * 1024;

    if (file.size > maxSize) {
        imageError.textContent = 'Image size must not exceed 2 MB.';
        imageInput.value = '';
        return;
    }

    // Preview the newly selected image
    imagePreview.src = URL.createObjectURL(file);
    imagePreview.style.display = 'block';

});
</script>

<script>
const editProductForm = document.querySelector('.edit-product-form');

editProductForm.addEventListener('submit', function () {

    const updateButton = editProductForm.querySelector('.edit-product-btn');

    updateButton.textContent = 'Saving Changes...';
    updateButton.disabled = true;

});
</script>
</body>
</html>
