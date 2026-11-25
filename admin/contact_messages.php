
<?php
session_start();
require_once __DIR__ . "/../config/db.php";

// Protect the page: administrators only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Mark a customer message as read
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['mark_read_id'])) {

    $message_id = (int) $_POST['mark_read_id'];

    $update_sql = "UPDATE contact_messages SET is_read = 1 WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);

    if ($update_stmt) {
        mysqli_stmt_bind_param($update_stmt, "i", $message_id);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
    }

    header("Location: contact_messages.php");
    exit;
}

// Fetch customer messages
$sql = "SELECT id, name, email, message, created_at, is_read
        FROM contact_messages
        ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Unable to load customer messages.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Messages - Techora Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <div class="admin-container">

        <h1>Customer Messages</h1>
        <p>View enquiries and messages submitted by Techora customers.</p>

        <a href="dashboard.php">Back to Dashboard</a>

        <div class="messages-list">

            <?php if (mysqli_num_rows($result) > 0): ?>

                <?php while ($message = mysqli_fetch_assoc($result)): ?>

                    <div class="message-card">

                        <h3>
                            <?php echo htmlspecialchars($message['name']); ?>
                        </h3>

                        <p>
                            <strong>Email:</strong>
                            <?php echo htmlspecialchars($message['email']); ?>
                        </p>

                        <p>
                            <strong>Date:</strong>
                            <?php echo htmlspecialchars($message['created_at']); ?>
                        </p>

                        <p>
                            <strong>Message:</strong>
                        </p>

                        <p>
                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                        </p>

                        <?php if ($message['is_read'] == 0): ?>

    <p class="unread-status">Unread</p>

    <form method="POST" action="contact_messages.php">
        <input
            type="hidden"
            name="mark_read_id"
            value="<?php echo (int) $message['id']; ?>"
        >

        <button type="submit" class="mark-read-btn">
            Mark as Read
        </button>
    </form>

<?php else: ?>

    <p class="read-status">Read</p>

<?php endif; ?>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <p>No customer messages have been received yet.</p>

            <?php endif; ?>

        </div>

    </div>

</body>
</html>