<?php
session_start();

require_once __DIR__ . "/../config/db.php";

// Allow administrators only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Calculate sales and order statistics
$sql = "SELECT
            COUNT(*) AS total_orders,

            SUM(CASE
                WHEN status = 'Delivered' THEN 1
                ELSE 0
            END) AS delivered_orders,

            SUM(CASE
                WHEN status = 'Pending' THEN 1
                ELSE 0
            END) AS pending_orders,

            COALESCE(SUM(total_amount), 0) AS total_order_value,

            COALESCE(SUM(CASE
                WHEN status = 'Delivered' THEN total_amount
                ELSE 0
            END), 0) AS delivered_sales

        FROM orders";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Unable to calculate sales reports.");
}

$sales = mysqli_fetch_assoc($result);

// Calculate sales for today
$today_sql = "SELECT COALESCE(SUM(total_amount), 0) AS total
              FROM orders
              WHERE status = 'Delivered'
              AND DATE(created_at) = CURDATE()";

$today_result = mysqli_query($conn, $today_sql);
$today_sales = mysqli_fetch_assoc($today_result)['total'];


// Calculate sales for this week
$weekly_sql = "SELECT COALESCE(SUM(total_amount), 0) AS total
               FROM orders
               WHERE status = 'Delivered'
               AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";

$weekly_result = mysqli_query($conn, $weekly_sql);
$weekly_sales = mysqli_fetch_assoc($weekly_result)['total'];


// Calculate sales for this month
$monthly_sql = "SELECT COALESCE(SUM(total_amount), 0) AS total
                FROM orders
                WHERE status = 'Delivered'
                AND MONTH(created_at) = MONTH(CURDATE())
                AND YEAR(created_at) = YEAR(CURDATE())";

$monthly_result = mysqli_query($conn, $monthly_sql);
$monthly_sales = mysqli_fetch_assoc($monthly_result)['total'];// Get the 5 best-selling products
$best_sellers_sql = "
    SELECT
        products.name,
        SUM(order_items.quantity) AS total_sold,
        SUM(order_items.quantity * order_items.price) AS total_revenue
    FROM order_items
    JOIN products ON order_items.product_id = products.id
    JOIN orders ON order_items.order_id = orders.id
    GROUP BY products.id, products.name
    ORDER BY total_sold DESC
    LIMIT 5
";

$best_sellers = mysqli_query($conn, $best_sellers_sql);

if (!$best_sellers) {
    die('Unable to load best-selling products.');
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sales Reports - Techora</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="admin-layout">

    <aside class="admin-sidebar">

        <a href="../index.php" class="logo">
            <span class="logo-mark">
                <img src="../assets/images/techora-logo.jpg"
                     alt="Techora logo">
            </span>

            <span class="logo-tagline">Phone Accessories Store</span>
        </a>

        <p>Admin Panel</p>

        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Manage Products</a>
        <a href="orders.php">Manage Orders</a>
        <a href="reports.php" class="active">Sales Reports</a>
        <a href="contact_messages.php">Customer Messages</a>
        <a href="../index.php">Visit Store</a>
        <a href="../logout.php">Logout</a>

    </aside>

    <main class="admin-content">

    <h1>Sales Reports and Analytics</h1>

    <p>Monitor the financial performance of Techora.</p>

   
<h2>Sales Overview</h2>

<div class="dashboard-stats">

    <div class="stat-card">
        <h3>Total Orders</h3>
        <p><?php echo (int) $sales['total_orders']; ?></p>
    </div>

    <div class="stat-card">
        <h3>Delivered Orders</h3>
        <p><?php echo (int) $sales['delivered_orders']; ?></p>
    </div>

    <div class="stat-card">
        <h3>Pending Orders</h3>
        <p><?php echo (int) $sales['pending_orders']; ?></p>
    </div>

    <div class="stat-card">
        <h3>Total Order Value</h3>
        <p>
            MWK <?php echo number_format((float) $sales['total_order_value'], 2); ?>
        </p>
    </div>

    <div class="stat-card">
        <h3>Delivered Sales</h3>
        <p>
            MWK <?php echo number_format((float) $sales['delivered_sales'], 2); ?>
        </p>
    </div>

</div>


<h2>Sales by Period</h2>

<div class="dashboard-stats">

    <div class="stat-card">
        <h3>Today's Sales</h3>
        <p>
            MWK <?php echo number_format((float) $today_sales, 2); ?>
        </p>
    </div>

    <div class="stat-card">
        <h3>This Week's Sales</h3>
        <p>
            MWK <?php echo number_format((float) $weekly_sales, 2); ?>
        </p>
    </div>

    <div class="stat-card">
        <h3>This Month's Sales</h3>
        <p>
            MWK <?php echo number_format((float) $monthly_sales, 2); ?>
        </p>
    </div>

</div>


<h2>Top 5 Best-Selling Products</h2>

<table class="admin-table">

    <thead>
        <tr>
            <th>Product Name</th>
            <th>Quantity Sold</th>
            <th>Total Revenue</th>
        </tr>
    </thead>

    <tbody>

        <?php if (mysqli_num_rows($best_sellers) > 0): ?>

            <?php while ($product = mysqli_fetch_assoc($best_sellers)): ?>

                <tr>
                    <td>
                        <?php echo htmlspecialchars($product['name']); ?>
                    </td>

                    <td>
                        <?php echo (int) $product['total_sold']; ?>
                    </td>

                    <td>
                        MWK <?php echo number_format((float) $product['total_revenue'], 2); ?>
                    </td>
                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>
                <td colspan="3">No sales records available yet.</td>
            </tr>

        <?php endif; ?>

    </tbody>

</table>


        <div class="stat-card">
            <h3>Total Order Value</h3>
            <p>
                MWK <?php echo number_format((float) $sales['total_order_value'], 2); ?>
            </p>
        </div>

        <div class="stat-card">
            <h3>Delivered Sales</h3>
            
                MWK <?php echo number_format((float) $sales['delivered_sales'], 2); ?>
            </p>
        </div>

    </div>

</main>

</div>

</body>
</html>