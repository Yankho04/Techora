
<?php
session_start();

header('Content-Type: application/json');

function sendResponse(bool $success, string $message, array $data = []): void
{
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, "Invalid request.");
}

$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

if (!$product_id || !isset($_SESSION['cart'][$product_id])) {
    sendResponse(false, "Product not found in your cart.");
}

// Remove the product from the session cart.
unset($_SESSION['cart'][$product_id]);

// Calculate the updated cart total.
require_once "config/db.php";

$total = 0;

foreach ($_SESSION['cart'] as $id => $quantity) {

    $sql = "SELECT price FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        sendResponse(false, "Unable to calculate cart total.");
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if ($product) {
        $total += (float) $product['price'] * (int) $quantity;
    }
}

sendResponse(true, "Product removed successfully.", [
    'total' => number_format($total, 2, '.', ''),
    'empty' => empty($_SESSION['cart']),
    'cart_count' => array_sum($_SESSION['cart'])
]);
