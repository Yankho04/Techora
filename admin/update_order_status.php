
<?php
session_start();

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/activity_log.php";

// Allow administrators only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Accept POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: orders.php");
    exit;
}

// Get the submitted values
$order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$status = $_POST['status'] ?? '';

// Allow only these statuses
$allowed_statuses = [
    'Pending',
    'Processing',
    'Shipped',
    'Delivered'
];

if (!$order_id || $order_id < 1 || !in_array($status, $allowed_statuses, true)) {
    die("Invalid order ID or status.");
}

// Update the order status
$sql = "UPDATE orders SET status = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "si", $status, $order_id);

if (mysqli_stmt_execute($stmt)) {

    logAdminActivity(
        $conn,
        (int) $_SESSION['user_id'],
        "Updated Order Status",
        "Changed order #" . $order_id . " status to " . $status
    );

    mysqli_stmt_close($stmt);

    header("Location: orders.php?updated=1");
    exit;

} else {
    mysqli_stmt_close($stmt);
    die("Could not update order status.");
}
?>
