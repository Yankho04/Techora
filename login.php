
<?php
session_start();
require_once "config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $sql = "SELECT id, full_name, email, password, role
            FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user["password"])) {
        session_regenerate_id(true);

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["full_name"] = $user["full_name"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["role"] = $user["role"];

        header("Location: index.php");
        exit;
    } else {
        $message = "Incorrect email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Techora</title>
    <link rel="stylesheet" href="assets/css/style.css?v=2">
</head>
<body>

<div class="auth-page">

    <div class="auth-card">

        <a href="index.php" class="auth-logo">
            <img src="assets/images/techora-logo.jpg" alt="Techora logo" width="170">
        </a>

        <h1>Welcome back</h1>

        <p class="auth-subtitle">
            Sign in to continue shopping with Techora.
        </p>

        <?php if (!empty($message)): ?>
            <p class="auth-message">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="login.php" class="auth-form">

            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your email address"
                required
            >

            <div class="password-wrapper">

    <input
        type="password"
        id="password"
        name="password"
        placeholder="Enter your password"
        required
    >

    <button type="button" class="toggle-password" id="togglePassword">
        Show Password
    </button>

</div>
    <button type="submit" class="auth-submit">Sign in</button>

    <p class="forgot-password">
    <a href="forgot_password.php">Forgot your password?</a>
</p>

        <p class="auth-switch">
            Don't have an account?
            <a href="register.php">Create an account</a>
        </p>

        <a href="index.php" class="auth-back">← Back to Techora</a>

    </div>

</div>

<script>
const passwordInput = document.getElementById('password');
const togglePassword = document.getElementById('togglePassword');

togglePassword.addEventListener('click', function () {

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        togglePassword.textContent = 'Hide';
    } else {
        passwordInput.type = 'password';
        togglePassword.textContent = 'Show';
    }

});
</script>

</body>
</html>
