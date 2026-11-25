
<?php
session_start();

require_once "../config/db.php";
if (!isset($conn) || !$conn) {
    die("Database connection failed.");
}

// Allow administrators only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$sql = "SELECT * FROM products ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Techora</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<h1>Manage Products</h1>

<?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1'): ?>
    <p style="color: green;">Product deleted successfully!</p>
<?php endif; ?>

<p><a href="dashboard.php">Back to Dashboard</a></p>

<p><a href="add_product.php">Add New Product</a></p>
<input
    type="text"
    id="adminProductSearch"
    placeholder="Search by product name or category..."
>

<table border="1" cellpadding="10" id="adminProductsTable">
    <tr>
        <th>ID</th>
        <th>Product Name</th>
        <th>Price (MWK)</th>
        <th>Category</th>
        <th>Stock</th>
        <th>Action</th>

    </tr>

    <?php while ($product = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo (int) $product['id']; ?></td>
            <td><?php echo htmlspecialchars($product['name']); ?></td>
            <td><?php echo number_format((float) $product['price'], 2); ?></td>
            <td><?php echo htmlspecialchars($product['category']); ?></td>
            <td><?php echo (int) $product['stock']; ?></td>
            <td>
    <td>
    <a href="edit_product.php?id=<?php echo (int) $product['id']; ?>">
        Edit
    </a>

   <a href="delete_product.php?id=<?php echo (int) $product['id']; ?>"
   class="delete-product-btn"
   data-product-name="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
    Delete
</a>
</td>
        </tr>
    <?php endwhile; ?>
</table>

<script>
const adminProductSearch = document.getElementById('adminProductSearch');
const adminProductsTable = document.getElementById('adminProductsTable');

adminProductSearch.addEventListener('input', function () {

    const searchTerm = adminProductSearch.value.toLowerCase().trim();

    const productRows = adminProductsTable.querySelectorAll('tr');

    productRows.forEach(function (row, index) {

        // Skip the table header
        if (index === 0) {
            return;
        }

        const productData = row.textContent.toLowerCase();

        if (productData.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }

    });

});
</script>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="delete-modal">

    <div class="delete-modal-content">

        <h2>Confirm Deletion</h2>

        <p id="deleteMessage">
            Are you sure you want to delete this product?
        </p>

        <div class="delete-modal-actions">

            <button type="button" id="cancelDelete">
                Cancel
            </button>

            <a href="#" id="confirmDelete">
                Delete
            </a>

        </div>

    </div>

</div>

<script>
const deleteModal = document.getElementById('deleteModal');
const deleteMessage = document.getElementById('deleteMessage');
const confirmDelete = document.getElementById('confirmDelete');
const cancelDelete = document.getElementById('cancelDelete');

const deleteButtons = document.querySelectorAll('.delete-product-btn');

deleteButtons.forEach(function (button) {

    button.addEventListener('click', function (event) {

        event.preventDefault();

        const productName = button.dataset.productName;
        const deleteUrl = button.href;

        deleteMessage.textContent =
            'Are you sure you want to delete "' + productName + '"?';

        confirmDelete.href = deleteUrl;

        deleteModal.classList.add('show');

    });

});

// Cancel deletion
cancelDelete.addEventListener('click', function () {
    deleteModal.classList.remove('show');
});

// Close modal when clicking outside it
deleteModal.addEventListener('click', function (event) {

    if (event.target === deleteModal) {
        deleteModal.classList.remove('show');
    }

});
</script>

</body>
</html>
