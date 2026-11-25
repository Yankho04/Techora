
<?php
session_start();
require_once "config/db.php";

// Customer must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
// Show confirmation after a successful order
$order_id = isset($_GET['success']) ? (int) $_GET['success'] : 0;

$message = "";
$total = 0;
$products = [];

// Get cart products from the database
if ($order_id === 0 && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $quantity) {
    $sql = "SELECT id, name, price, stock FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);

    if ($product) {
        $quantity = (int) $quantity;

        $product['quantity'] = $quantity;
        $product['subtotal'] = $product['price'] * $quantity;

        $products[] = $product;
        $total += $product['subtotal'];
    }
}

}

// Place the order
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['place_order'])) {
    if (empty($products)) {
        die("Your cart has no valid products.");
    }

    mysqli_begin_transaction($conn);

    try {
        $user_id = (int) $_SESSION['user_id'];
        $status = "Pending";

        $delivery_address = trim($_POST['delivery_address'] ?? '');
$phone_number = trim($_POST['phone_number'] ?? '');

if ($delivery_address === '' || $phone_number === '') {
    throw new Exception("Please enter your delivery address and phone number.");
}

$sql = "INSERT INTO orders
        (user_id, total_amount, status, delivery_address, phone_number)
        VALUES (?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "idsss",
    $user_id,
    $total,
    $status,
    $delivery_address,
    $phone_number
);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Could not save the order.");
        }

        $order_id = mysqli_insert_id($conn);

        $sql = "INSERT INTO order_items
                (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)";
        $item_stmt = mysqli_prepare($conn, $sql);

        foreach ($products as $product) {
            $product_id = (int) $product['id'];
            $quantity = (int) $product['quantity'];
            $price = (float) $product['price'];

            mysqli_stmt_bind_param(
                $item_stmt, "iiid",
                $order_id, $product_id, $quantity, $price
            );

            if (!mysqli_stmt_execute($item_stmt)) {
                throw new Exception("Could not save an order item.");
            }

            $stock_sql = "UPDATE products
                          SET stock = stock - ?
                          WHERE id = ? AND stock >= ?";
            $stock_stmt = mysqli_prepare($conn, $stock_sql);
            mysqli_stmt_bind_param(
                $stock_stmt, "iii",
                $quantity, $product_id, $quantity
            );

            if (!mysqli_stmt_execute($stock_stmt) ||
                mysqli_stmt_affected_rows($stock_stmt) !== 1) {
                throw new Exception(
                    "Stock is no longer sufficient for " . $product['name']
                );
            }
            mysqli_stmt_close($stock_stmt);
        }

        mysqli_commit($conn);

        $_SESSION['cart'] = [];

        header("Location: checkout.php?success=" . $order_id);
        exit;

    } catch (Throwable $e) {
        mysqli_rollback($conn);
        $message = "The order could not be placed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Techora</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<h2>Techora Checkout</h2>

<?php if (isset($_GET['success'])): ?>

    <h3>Order placed successfully!</h3>
    <p>Your order number is:
        <?php echo (int) $_GET['success']; ?>
    </p>
    <p>Payment method: Cash on Delivery</p>
    <p>You will pay when your order is delivered.</p>
    <a href="products.php">Continue Shopping</a>

<?php else: ?>

    <p><?php echo htmlspecialchars($message); ?></p>

    <h3>Order Summary</h3>

    <?php foreach ($products as $product): ?>
        <p>
            <?php echo htmlspecialchars($product['name']); ?>
            × <?php echo (int) $product['quantity']; ?>
            — MWK <?php echo number_format($product['subtotal'], 2); ?>
        </p>
    <?php endforeach; ?>

    <h3>Total: MWK <?php echo number_format($total, 2); ?></h3>

<h3>Delivery Details</h3>

<form method="POST" action="checkout.php" id="checkoutForm">

<div id="checkoutMessage"></div>
    <label for="delivery_address">Delivery Address:</label><br>
    <textarea name="delivery_address" id="delivery_address"
              required></textarea><br><br>

    <label for="phone_number">Phone Number:</label><br>
    <input type="tel" name="phone_number" id="phone_number"
           required><br><br>

    <h3>Payment Method</h3>
    <p>Cash on Delivery</p>

    <button type="submit" name="place_order" value="1">
        Place Order
    </button>

</form>

    <p><a href="cart.php">Return to Cart</a></p>

<?php endif; ?>

<script>

const checkoutForm = document.getElementById('checkoutForm');
const checkoutMessage = document.getElementById('checkoutMessage');

checkoutForm.addEventListener('submit', function (event) {

             const confirmOrder = confirm('Are you sure you want to place this order?');

    if (!confirmOrder) {
        event.preventDefault();
        return;
    }
    
    const deliveryAddress = document.getElementById('delivery_address').value.trim();
    const phoneNumber = document.getElementById('phone_number').value.trim();

    checkoutMessage.textContent = '';
    checkoutMessage.style.color = 'red';

    if (deliveryAddress.length < 10) {
        event.preventDefault();

        checkoutMessage.textContent =
            'Please enter a valid delivery address (at least 10 characters).';

        return;
    }

    if (phoneNumber.length < 7) {
        event.preventDefault();

        checkoutMessage.textContent =
            'Please enter a valid phone number.';

        return;
    }

});

</script>
</body>
</html>
