
<?php
session_start();
require_once "../config/db.php";

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Database connection is unavailable.");
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Count all products
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products");
$total_products = mysqli_fetch_assoc($result)['total'];

// Count all orders
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders");
$total_orders = mysqli_fetch_assoc($result)['total'];

// Calculate the total value of orders
$result = mysqli_query($conn, "SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders");
$total_sales = mysqli_fetch_assoc($result)['total'];

// Count pending orders
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders WHERE status = 'Pending'");
$pending_orders = mysqli_fetch_assoc($result)['total'];

// Count products with low stock
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE stock BETWEEN 1 AND 10");
$low_stock_products = mysqli_fetch_assoc($result)['total'];

// Count products that are out of stock
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE stock = 0");
$out_of_stock_products = mysqli_fetch_assoc($result)['total'];

// Count unread customer messages
$unread_sql = "SELECT COUNT(*) AS unread_count
               FROM contact_messages
               WHERE is_read = 0";

$unread_result = mysqli_query($conn, $unread_sql);

$unread_count = 0;

if ($unread_result) {
    $unread_data = mysqli_fetch_assoc($unread_result);
    $unread_count = (int) $unread_data['unread_count'];
}

// Get products with low or zero stock
$stock_alerts = mysqli_query($conn, "
    SELECT id, name, stock
    FROM products
    WHERE stock <= 10
    ORDER BY stock ASC
");

// Get the 5 most recent orders
$recent_orders = mysqli_query($conn, "
    SELECT orders.id, users.full_name, orders.total_amount,
           orders.status, orders.created_at
    FROM orders
    JOIN users ON orders.user_id = users.id
    ORDER BY orders.created_at DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Techora</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=3">
</head>
<body>


<div class="admin-layout">

    <aside class="admin-sidebar">
        <a href="../index.php" class="logo" aria-label="Techora home">
            <span class="logo-mark">
                <img src="../assets/images/techora-logo.jpg" alt="Techora logo" width="170" height="113">
            </span>
            <span class="logo-tagline">Phone Accessories Store</span>
        </a>

        <p>Admin Panel</p>

        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="create_admin.php">Create Admin Account</a>
        <a href="products.php">Manage Products</a>
        <a href="orders.php">Manage Orders</a>
        <a href="reports.php">Sales Reports</a>
        <a href="activity_logs.php">Activity Logs</a>
        <a href="../index.php">Visit Store</a>
        <a href="../logout.php" class="logout-link">Logout</a>
        <a href="contact_messages.php">Customer Messages</a>
    </aside>

    <main class="admin-content">

        <h1>Admin Dashboard</h1>

        <p>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>

        <h2>Store Statistics</h2>


<div class="dashboard-stats">

<a href="products.php" class="stat-card stat-link">
    <div class="stat-icon products-icon">▣</div>
    <h3>Total Products</h3>
    <p><?php echo (int) $total_products; ?></p>
    <span class="stat-description">Products in your store</span>
</a>


<a href="orders.php" class="stat-card stat-link">
    <div class="stat-icon orders-icon">▤</div>
    <h3>Total Orders</h3>
    <p><?php echo (int) $total_orders; ?></p>
    <span class="stat-description">Orders received</span>
</a>

<a href="orders.php" class="stat-card stat-link">
    <div class="stat-icon sales-icon">MWK</div>
    <h3>Total Order Value</h3>
    <p>MWK <?php echo number_format((float) $total_sales, 2); ?></p>
    <span class="stat-description">Value of all orders</span>
</a>

<a href="orders.php" class="stat-card stat-link">
    <div class="stat-icon pending-icon">◷</div>
    <h3>Pending Orders</h3>
    <p><?php echo (int) $pending_orders; ?></p>

    <?php if ($pending_orders > 0): ?>
        <span class="pending-notice">Needs attention</span>
    <?php else: ?>
        <span class="stat-description">All orders are up to date</span>
    <?php endif; ?>
</a>

<a href="contact_messages.php" class="stat-card stat-link">
    <div class="stat-icon">✉</div>
    <h3>Unread Messages</h3>
    <p><?php echo (int) $unread_count; ?></p>

    <?php if ($unread_count > 0): ?>
        <span class="pending-notice">Needs attention</span>
    <?php else: ?>
        <span class="stat-description">All messages have been read</span>
    <?php endif; ?>
</a>

</div>

<h2>Stock Alerts</h2>

<div class="dashboard-stats">

    <div class="stat-card">
        <h3>Low Stock Products</h3>
        <p><?php echo (int) $low_stock_products; ?></p>
        <span class="stat-description">Products with 1–10 items remaining</span>
    </div>

    <div class="stat-card">
        <h3>Out of Stock Products</h3>
        <p><?php echo (int) $out_of_stock_products; ?></p>
        <span class="stat-description">Products with zero stock</span>
    </div>

</div>

<h2>Products Needing Restocking</h2>

<table class="admin-table">
    <thead>
        <tr>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Available Stock</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        <?php if ($stock_alerts && mysqli_num_rows($stock_alerts) > 0): ?>
            <?php while ($product = mysqli_fetch_assoc($stock_alerts)): ?>
                <tr>
                    <td><?php echo (int) $product['id']; ?></td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><?php echo (int) $product['stock']; ?></td>
                    <td>
                        <?php if ($product['stock'] == 0): ?>
                            <span class="status-badge status-cancelled">Out of Stock</span>
                        <?php else: ?>
                            <span class="pending-notice">Low Stock</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_product.php?id=<?php echo (int) $product['id']; ?>"
                           class="view-order-btn">
                            Edit Product
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">All products have sufficient stock.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<h2>Recent Orders</h2>

<table class="admin-table">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>

   <?php if ($recent_orders && mysqli_num_rows($recent_orders) > 0): ?>
    <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
        <tr>
            <td>#<?php echo (int) $order['id']; ?></td>

            <td><?php echo htmlspecialchars($order['full_name']); ?></td>

            <td>MWK <?php echo number_format((float) $order['total_amount'], 2); ?></td>

            <td>
                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                    <?php echo htmlspecialchars($order['status']); ?>
                </span>
            </td>

            <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>

            <td>
                <a href="view_order.php?id=<?php echo (int) $order['id']; ?>" class="view-order-btn">
                    View Order
                </a>
            </td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="6">No orders found.</td>
    </tr>
<?php endif; ?>
</tbody>

</table>

<div class="view-all-orders">
    <a href="orders.php">View All Orders →</a>
</div>

    </main>
</div>


    <script>
    document.addEventListener('DOMContentLoaded', function () {

        const logoutLink = document.querySelector('.logout-link');

        if (logoutLink) {

            logoutLink.addEventListener('click', function (event) {

                const confirmLogout = confirm('Are you sure you want to logout?');

                if (!confirmLogout) {
                    event.preventDefault();
                }

            });

        }

    });
    </script>

</body>
</html>


