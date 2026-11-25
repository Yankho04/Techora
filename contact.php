
<?php
require_once "config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if (
    $name === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $message === ''
) {
    die("Please provide a valid name, email and message.");
}

$sql = "INSERT INTO contact_messages (name, email, message)
        VALUES (?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Unable to process your message. Please try again.");
}

mysqli_stmt_bind_param($stmt, "sss", $name, $email, $message);

if (mysqli_stmt_execute($stmt)) {
    echo "<h2>Message Sent Successfully!</h2>";
    echo "<p>Thank you for contacting Techora. We appreciate your message.</p>";
    echo '<a href="index.php">Return to Homepage</a>';
} else {
    echo "Unable to save your message. Please try again.";
}

mysqli_stmt_close($stmt);
?>
