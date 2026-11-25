
<?php
session_start();

if (!isset($conn)) {
    require_once __DIR__ . "/../config/db.php";
}

if (!isset($conn) || !$conn) {
    die("Database connection failed.");
}

// Check whether the user is logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}


$sql = "
    SELECT
        admin_activity_logs.id,
        users.full_name,
        admin_activity_logs.action,
        admin_activity_logs.details,
        admin_activity_logs.created_at
    FROM admin_activity_logs
    JOIN users
        ON admin_activity_logs.admin_id = users.id
    ORDER BY admin_activity_logs.created_at DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Unable to load activity logs.");
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Activity Logs - Techora</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="admin-container">

    <h2>Admin Activity Logs</h2>

    <table class="admin-table">

        <thead>
            <tr>
                <th>ID</th>
                <th>Administrator</th>
                <th>Action</th>
                <th>Details</th>
                <th>Date & Time</th>
            </tr>
        </thead>

        <tbody>

            <?php if (mysqli_num_rows($result) > 0): ?>

                <?php while ($log = mysqli_fetch_assoc($result)): ?>

                    <tr>
                        <td><?php echo (int) $log['id']; ?></td>

                        <td>
                            <?php echo htmlspecialchars($log['full_name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($log['action']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($log['details']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($log['created_at']); ?>
                        </td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="5">No activity records found.</td>
                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</div>

</body>
</html>
