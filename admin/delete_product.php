
<?php
session_start();

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/activity_log.php";

// Allow administrators only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get product ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    die("Invalid product ID.");
}

// Check whether the product exists
$sql = "SELECT id, name FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$product = mysqli_fetch_assoc($result);

if (!$product) {
    die("Product not found.");
}

$product_name = $product['name'];

// Prevent deletion if the product is linked to an order
$sql = "SELECT COUNT(*) AS total FROM order_items WHERE product_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if ($row['total'] > 0) {
    die("This product cannot be deleted because it belongs to an existing order. Please keep it to preserve order history.");
}

// Delete the product
$sql = "DELETE FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {

    logAdminActivity(
        $conn,
        (int) $_SESSION['user_id'],
        "Deleted Product",
        "Deleted product: " . $product_name . " (ID: " . $id . ")"
    );

    mysqli_stmt_close($stmt);

    header("Location: products.php?deleted=1");
    exit;

} else {
    mysqli_stmt_close($stmt);
    die("Could not delete the product.");
}
?>

