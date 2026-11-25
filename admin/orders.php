
<?php
session_start();

require_once __DIR__ . "/../config/db.php";

// Allow administrators only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get search and filter values
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';

// Build the orders query
$sql = "SELECT orders.*, users.full_name, users.email
        FROM orders
        JOIN users ON orders.user_id = users.id
        WHERE 1=1";

$params = [];
$types = "";

// Search by order ID or customer name
if ($search !== '') {
    $sql .= " AND (orders.id = ? OR users.full_name LIKE ?)";

    $params[] = (int) $search;
    $params[] = "%" . $search . "%";

    $types .= "is";
}

// Filter by order status
$allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered'];

if (in_array($status, $allowed_statuses, true)) {
    $sql .= " AND orders.status = ?";

    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY orders.id DESC";

// Prepare and execute the query
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Could not prepare orders query.");
}

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Could not load orders.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Techora</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<h1>Manage Orders</h1>

<?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
    <p style="color: green;">Order status updated successfully!</p>
<?php endif; ?>

<p><a href="dashboard.php">Back to Dashboard</a></p>

<form method="GET" action="orders.php" class="order-filter-form">

    <input
    type="text"
    name="search"
    id="liveOrderSearch"
    placeholder="Search by Order ID, Customer Name or Email"
    value="<?php echo htmlspecialchars($search); ?>"
>
<select name="status" id="orderStatusFilter">
        <option value="">All Order Statuses</option>

        <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>
            Pending
        </option>

        <option value="Processing" <?php echo $status === 'Processing' ? 'selected' : ''; ?>>
            Processing
        </option>

        <option value="Shipped" <?php echo $status === 'Shipped' ? 'selected' : ''; ?>>
            Shipped
        </option>

        <option value="Delivered" <?php echo $status === 'Delivered' ? 'selected' : ''; ?>>
            Delivered
        </option>
    </select>

    <button type="submit">Search / Filter</button>

    <a href="orders.php">Clear Filters</a>

</form>

<table id="ordersTable">
    <tr>
        <th>Order ID</th>
        <th>Customer</th>
        <th>Email</th>
        <th>Delivery Address</th>
        <th>Phone Number</th>
        <th>Total (MWK)</th>
        <th>Status</th>
        <th>Order Date</th>
        <th>Update Status</th>
    </tr>

    <?php while ($order = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo (int) $order['id']; ?></td>
            <td><?php echo htmlspecialchars($order['full_name']); ?></td>
            <td><?php echo htmlspecialchars($order['email']); ?></td>
            <td><?php echo htmlspecialchars($order['delivery_address']); ?></td>
            <td><?php echo htmlspecialchars($order['phone_number']); ?></td>
            <td><?php echo number_format((float) $order['total_amount'], 2); ?></td>
            <td>
    <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
        <?php echo htmlspecialchars($order['status']); ?>
    </span>
</td>
            <td><?php echo htmlspecialchars($order['created_at']); ?></td>

            <td>
    <form method="POST" action="update_order_status.php" class="order-status-form">
        <input type="hidden" name="order_id"
               value="<?php echo (int) $order['id']; ?>">

        <select name="status">
            <option value="Pending">Pending</option>
            <option value="Processing">Processing</option>
            <option value="Shipped">Shipped</option>
            <option value="Delivered">Delivered</option>
        </select>

        <button type="submit" class="order-update-btn">Update</button>
    </form>
</td>
        </tr>
    <?php endwhile; ?>
</table>

<script>
const liveOrderSearch = document.getElementById('liveOrderSearch');
const ordersTable = document.getElementById('ordersTable');

liveOrderSearch.addEventListener('input', function () {

    const searchTerm = liveOrderSearch.value.toLowerCase().trim();

    const orderRows = ordersTable.querySelectorAll('tr');

    orderRows.forEach(function (row, index) {

        // Skip the table header
        if (index === 0) {
            return;
        }

        const orderData = row.textContent.toLowerCase();

        if (orderData.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }

    });

});
</script>

<script>
const orderStatusFilter = document.getElementById('orderStatusFilter');
const orderTable = document.getElementById('ordersTable');

orderStatusFilter.addEventListener('change', function () {

    const selectedStatus = orderStatusFilter.value.toLowerCase();

    const rows = orderTable.querySelectorAll('tr');

    rows.forEach(function (row, index) {

        // Skip table header
        if (index === 0) {
            return;
        }

        const statusBadge = row.querySelector('.status-badge');

        if (!statusBadge) {
            return;
        }

        const orderStatus = statusBadge.textContent.trim().toLowerCase();

        if (selectedStatus === '' || orderStatus === selectedStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }

    });

});
</script>

<script>
document.querySelectorAll('.order-status-form').forEach(function (form) {

    form.addEventListener('submit', function () {

        const updateButton = form.querySelector('.order-update-btn');

        updateButton.textContent = 'Updating...';
        updateButton.disabled = true;

    });

});
</script>
</body>
</html>
