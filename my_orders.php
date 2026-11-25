
<?php
session_start();
require_once "config/db.php";

// Customer must log in first
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$sql = "SELECT * FROM orders
        WHERE user_id = ?
        ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$items_sql = "SELECT products.name, order_items.quantity, order_items.price
              FROM order_items
              JOIN products ON order_items.product_id = products.id
              WHERE order_items.order_id = ?";

$items_stmt = mysqli_prepare($conn, $items_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Techora</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<h2>My Orders</h2>

<?php if (mysqli_num_rows($result) > 0): ?>

    <?php while ($order = mysqli_fetch_assoc($result)): ?>

        <div class="order-card">

            <h3>Order #<?php echo (int) $order['id']; ?></h3>

            <p>
                Date:
                <?php echo htmlspecialchars($order['created_at']); ?>
            </p>

            <p>
                Total:
                MWK <?php echo number_format((float) $order['total_amount'], 2); ?>
            </p>

            <p>
                Status:
                <?php echo htmlspecialchars($order['status']); ?>
            </p>

            <p>
                Delivery Address:
                <?php echo htmlspecialchars($order['delivery_address']); ?>
            </p>

            <p>
                Phone Number:
                <?php echo htmlspecialchars($order['phone_number']); ?>
            </p>

            
<h4>Products Ordered</h4>

<?php
mysqli_stmt_bind_param($items_stmt, "i", $order['id']);
mysqli_stmt_execute($items_stmt);
$items_result = mysqli_stmt_get_result($items_stmt);
?>

<?php while ($item = mysqli_fetch_assoc($items_result)): ?>

    <p>
        <?php echo htmlspecialchars($item['name']); ?>
        × <?php echo (int) $item['quantity']; ?>
        — MWK <?php echo number_format((float) $item['price'], 2); ?>
    </p>

<?php endwhile; ?>


        </div>

        <hr>

    <?php endwhile; ?>

<?php else: ?>

    <p>You have not placed any orders yet.</p>

<?php endif; ?>

<p><a href="products.php">Continue Shopping</a></p>
<p><a href="index.php">Back to Home</a></p>

</body>
</html>
