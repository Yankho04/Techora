
<?php
session_start();

require_once "config/db.php";
require_once __DIR__ . "/vendor/autoload.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, full_name FROM users WHERE email = ?"
        );

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if ($user) {

        // Generate a secure random token.
$token = bin2hex(random_bytes(32));

// Hash the token before saving it.
$tokenHash = hash('sha256', $token);

// Set the token to expire after one hour.
$expiresAt = date('Y-m-d H:i:s', time() + 3600);

// Remove any previous reset tokens for this user.
$deleteStmt = mysqli_prepare(
    $conn,
    "DELETE FROM password_resets WHERE user_id = ?"
);

mysqli_stmt_bind_param($deleteStmt, "i", $user['id']);
mysqli_stmt_execute($deleteStmt);
mysqli_stmt_close($deleteStmt);

// Save the new token hash in the database.

    $insertStmt = mysqli_prepare(
        $conn,
        "INSERT INTO password_resets (user_id, token_hash, expires_at)
         VALUES (?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $insertStmt,
        "iss",
        $user['id'],
        $tokenHash,
        $expiresAt
    );

    mysqli_stmt_execute($insertStmt);
    mysqli_stmt_close($insertStmt);

    // Load email configuration.
    $mailConfig = require __DIR__ . "/config/mail.php";

    // Create the password reset link only when a user account was found.
    $resetLink = rtrim($mailConfig['base_url'], '/')
        . "/reset_password.php?token="
        . urlencode($token);

        // Configure PHPMailer.
$mail = new \PHPMailer\PHPMailer\PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = $mailConfig['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $mailConfig['username'];
    $mail->Password = $mailConfig['password'];
    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int) $mailConfig['port'];

    $mail->CharSet = 'UTF-8';

    // Sender and recipient.
    $mail->setFrom($mailConfig['username'], 'Techora');
    $mail->addAddress($email, $user['full_name']);

    $mail->isHTML(true);
    $mail->Subject = 'Reset your Techora password';
    $mail->Body = 'Click this link to reset your password: '
        . '<a href="' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '">Reset Password</a>'; 
    $mail->AltBody = 'Click this link to reset your password: ' . $resetLink;

    $mail->send();
} catch (\PHPMailer\PHPMailer\Exception $e) {
    error_log("Techora password reset email failed: " . $e->getMessage());
}
        }
    }

    $message = "If an account exists with that email, password reset instructions will be sent.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Techora</title>
    <link rel="stylesheet" href="assets/css/style.css?v=2">
</head>
<body>

<div class="auth-page">

    <div class="auth-card">

        <a href="index.php" class="auth-logo">
            <img src="assets/images/techora-logo.jpg" alt="Techora logo" width="170">
        </a>

        <h1>Forgot password?</h1>

        <p class="auth-subtitle">
            Enter your registered email address and we'll help you reset your password.
        </p>

        <?php if (!empty($message)): ?>
            <p class="auth-message">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="forgot_password.php" class="auth-form">

            <label for="email">Email Address</label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your registered email"
                required
            >

            <button type="submit">Send Reset Link</button>

        </form>

        <p class="auth-switch">
            Remembered your password?
            <a href="login.php">Sign in</a>
        </p>

        <a href="index.php" class="auth-back">
            ← Back to Techora
        </a>

    </div>

</div>

</body>
</html>
