
<?php
session_start();

require_once "config/db.php";

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
$action = $_POST['action'] ?? '';

if (!$product_id || !in_array($action, ['increase', 'decrease'], true)) {
    sendResponse(false, "Invalid product or action.");
}

if (!isset($_SESSION['cart'][$product_id])) {
    sendResponse(false, "This product is not in your cart.");
}

// Get the product's current price and stock.
$sql = "SELECT price, stock FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    sendResponse(false, "Unable to check product details.");
}

mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$product) {
    sendResponse(false, "Product not found.");
}

$quantity = (int) $_SESSION['cart'][$product_id];
$stock = (int) $product['stock'];
$price = (float) $product['price'];

if ($action === 'increase') {

    if ($quantity >= $stock) {
        sendResponse(false, "There is not enough stock available.");
    }

    $quantity++;

} else {

    if ($quantity <= 1) {
        sendResponse(false, "Quantity cannot be less than 1.");
    }

    $quantity--;
}

// Save the new quantity.
$_SESSION['cart'][$product_id] = $quantity;

// Calculate the updated product subtotal.
$subtotal = $price * $quantity;

// Calculate the entire cart total.
$total = 0;

foreach ($_SESSION['cart'] as $id => $cart_quantity) {

    $sql = "SELECT price FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        sendResponse(false, "Unable to calculate cart total.");
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $cart_product = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if ($cart_product) {
        $total += (float) $cart_product['price'] * (int) $cart_quantity;
    }
}

sendResponse(true, "Cart updated successfully.", [
    'quantity' => $quantity,
    'subtotal' => number_format($subtotal, 2, '.', ''),
    'total' => number_format($total, 2, '.', ''),
    'cart_count' => array_sum($_SESSION['cart'])
]);
