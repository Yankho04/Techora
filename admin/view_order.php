
<?php
session_start();

require_once __DIR__ . "/../config/db.php";

// Allow administrators only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get the selected order ID
$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$order_id) {
    die("Invalid order ID.");
}

// Get order details and customer information
$sql = "SELECT orders.*, users.full_name, users.email
        FROM orders
        JOIN users ON orders.user_id = users.id
        WHERE orders.id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    die("Order not found.");
}

// Get products belonging to this order
$items_sql = "SELECT order_items.*, products.name
              FROM order_items
              JOIN products ON order_items.product_id = products.id
              WHERE order_items.order_id = ?";

$items_stmt = mysqli_prepare($conn, $items_sql);
mysqli_stmt_bind_param($items_stmt, "i", $order_id);
mysqli_stmt_execute($items_stmt);

$items_result = mysqli_stmt_get_result($items_stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>View Order - Techora</title>

    <link rel="stylesheet" href="../assets/css/style.css?v=4">
</head>

<body>

<div class="admin-layout">

    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">

        <a href="../index.php" class="logo">
            <span class="logo-mark">
                <img src="../assets/images/techora-logo.jpg" alt="Techora logo">
            </span>

            <span class="logo-tagline">Phone Accessories Store</span>
        </a>

        <p>Admin Panel</p>

        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Manage Products</a>
        <a href="orders.php" class="active">Manage Orders</a>
        <a href="../index.php">Visit Store</a>
        <a href="../logout.php">Logout</a>

    </aside>

    <!-- Main Content -->
    <main class="admin-content">

        <h1>Order Details</h1>

        <div class="receipt-header">
            <img class="receipt-logo" src="../assets/images/techora-logo.jpg" alt="Techora store logo" width="120">
            <p>Accessories That Power Your World</p>
            <p>Thank you for shopping with us!</p>
        </div>
    <div class="order-actions">
    <a href="orders.php" class="view-order-btn">
        ← Back to Manage Orders
    </a>

    <button onclick="window.print()" class="print-order-btn">
        Print Order
    </button>

    <button onclick="window.print()" class="download-pdf-btn">
        Download PDF
    </button>
</div>

        <div class="receipt-info">
    <p>
        <strong>Receipt No:</strong>
        #<?php echo (int) $order['id']; ?>
    </p>

    <p>
        <strong>Print Date:</strong>
        <?php echo date('d M Y, h:i A'); ?>
    </p>
</div>
        <!-- Customer Information -->
        <div class="order-details-card">

            <h2>Customer Information</h2>

            <p>
                <strong>Order ID:</strong>
                #<?php echo (int) $order['id']; ?>
            </p>

            <p>
                <strong>Customer:</strong>
                <?php echo htmlspecialchars($order['full_name']); ?>
            </p>

            <p>
                <strong>Email:</strong>
                <?php echo htmlspecialchars($order['email']); ?>
            </p>

            <p>
                <strong>Delivery Address:</strong>
                <?php echo htmlspecialchars($order['delivery_address']); ?>
            </p>

            <p>
                <strong>Phone Number:</strong>
                <?php echo htmlspecialchars($order['phone_number']); ?>
            </p>

            <p>
                <strong>Order Status:</strong>

                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                    <?php echo htmlspecialchars($order['status']); ?>
                </span>
            </p>

            <p>
                <strong>Order Date:</strong>
                <?php echo htmlspecialchars($order['created_at']); ?>
            </p>

            <p>
                <strong>Total Amount:</strong>
                MWK <?php echo number_format((float) $order['total_amount'], 2); ?>
            </p>

        </div>

        <!-- Ordered Products -->
        <h2>Ordered Products</h2>

        <table class="admin-table">

            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>

            <tbody>

                <?php if ($items_result && mysqli_num_rows($items_result) > 0): ?>

                    <?php while ($item = mysqli_fetch_assoc($items_result)): ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($item['name']); ?>
                            </td>

                            <td>
                                <?php echo (int) $item['quantity']; ?>
                            </td>

                            <td>
                                MWK <?php echo number_format((float) $item['price'], 2); ?>
                            </td>

                            <td>
                                MWK <?php echo number_format(
                                    (float) $item['price'] * (int) $item['quantity'],
                                    2
                                ); ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="4">No products found for this order.</td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

        <div class="receipt-footer">

    <p>
        <strong>Payment Method:</strong>
        Cash on Delivery
    </p>

    <h2>
        Order Total:
        MWK <?php echo number_format((float) $order['total_amount'], 2); ?>
    </h2>

    <p class="receipt-thank-you">
        Thank you for shopping with Techora!
    </p>

</div>

    </main>

</div>

</body>
</html>
