<?php
require_once "config/db.php";

$token = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? trim($_POST['token'] ?? '')
    : trim($_GET['token'] ?? '');
$message = '';
$tokenValid = false;
$resetComplete = false;
$userId = null;

if (preg_match('/\A[a-f0-9]{64}\z/', $token) === 1) {
    $tokenHash = hash('sha256', $token);
    $stmt = mysqli_prepare(
        $conn,
        "SELECT user_id
         FROM password_resets
         WHERE token_hash = ? AND expires_at > NOW()
         LIMIT 1"
    );

    if ($stmt === false) {
        error_log("Techora password reset failed: unable to prepare token query.");
        http_response_code(500);
        $message = "Unable to verify this reset link. Please try again later.";
    } else {
        mysqli_stmt_bind_param($stmt, "s", $tokenHash);

        if (!mysqli_stmt_execute($stmt)) {
            error_log("Techora password reset failed: unable to verify token.");
            http_response_code(500);
            $message = "Unable to verify this reset link. Please try again later.";
        } else {
            $result = mysqli_stmt_get_result($stmt);
            $reset = mysqli_fetch_assoc($result);

            if ($reset) {
                $tokenValid = true;
                $userId = (int) $reset['user_id'];
            } else {
                $message = "This reset link is invalid or has expired.";
            }
        }

        mysqli_stmt_close($stmt);
    }
} else {
    $message = "This reset link is invalid or has expired.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } elseif ($password !== $confirmPassword) {
        $message = "The passwords do not match.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        if (!mysqli_begin_transaction($conn)) {
            error_log("Techora password reset failed: unable to start transaction.");
            http_response_code(500);
            $message = "Unable to update your password. Please try again later.";
        } else {
            $updateStmt = mysqli_prepare(
                $conn,
                "UPDATE users SET password = ? WHERE id = ?"
            );

            if ($updateStmt === false) {
                mysqli_rollback($conn);
                error_log("Techora password reset failed: unable to prepare password update.");
                http_response_code(500);
                $message = "Unable to update your password. Please try again later.";
            } else {
                mysqli_stmt_bind_param($updateStmt, "si", $hashedPassword, $userId);
                $updated = mysqli_stmt_execute($updateStmt);
                mysqli_stmt_close($updateStmt);

                if (!$updated) {
                    mysqli_rollback($conn);
                    error_log("Techora password reset failed: password update query failed.");
                    http_response_code(500);
                    $message = "Unable to update your password. Please try again later.";
                } else {
                    $deleteStmt = mysqli_prepare(
                        $conn,
                        "DELETE FROM password_resets WHERE user_id = ?"
                    );

                    if ($deleteStmt === false) {
                        mysqli_rollback($conn);
                        error_log("Techora password reset failed: unable to prepare token cleanup.");
                        http_response_code(500);
                        $message = "Unable to update your password. Please try again later.";
                    } else {
                        mysqli_stmt_bind_param($deleteStmt, "i", $userId);
                        $deleted = mysqli_stmt_execute($deleteStmt);
                        mysqli_stmt_close($deleteStmt);

                        if (!$deleted || !mysqli_commit($conn)) {
                            mysqli_rollback($conn);
                            error_log("Techora password reset failed: unable to consume reset token.");
                            http_response_code(500);
                            $message = "Unable to update your password. Please try again later.";
                        } else {
                            $resetComplete = true;
                            $tokenValid = false;
                            $message = "Your password has been updated. You can now sign in.";
                        }
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Techora</title>
    <link rel="stylesheet" href="assets/css/style.css?v=2">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <a href="index.php" class="auth-logo">
            <img src="assets/images/techora-logo.jpg" alt="Techora logo" width="170">
        </a>

        <h1>Reset password</h1>

        <?php if ($message !== ''): ?>
            <p class="auth-message">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>

        <?php if ($tokenValid && !$resetComplete): ?>
            <form method="POST" action="reset_password.php" class="auth-form">
                <input
                    type="hidden"
                    name="token"
                    value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>"
                >

                <label for="password">New password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >

                <label for="confirm_password">Confirm new password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >

                <button type="submit">Update password</button>
            </form>
        <?php elseif ($resetComplete): ?>
            <a href="login.php" class="auth-back">Sign in</a>
        <?php else: ?>
            <a href="forgot_password.php" class="auth-back">Request a new reset link</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
