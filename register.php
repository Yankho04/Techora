
<?php
session_start();
require_once "config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($full_name === "" || $email === "" || $password === "") {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $message = "This email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = "customer";

            $sql = "INSERT INTO users (full_name, email, password, role)
                    VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param(
                $stmt, "ssss",
                $full_name, $email, $hashed_password, $role
            );

            if (mysqli_stmt_execute($stmt)) {
                $message = "Registration successful! You can now log in.";
            } else {
                $message = "Registration failed. Please try again.";
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
    <title>Register - Techora</title>
    <link rel="stylesheet" href="assets/css/style.css?v=2">
</head>

<body>

<div class="auth-page">

    <div class="auth-card">

        <a href="index.php" class="auth-logo">
            <img src="assets/images/techora-logo.jpg" alt="Techora logo" width="170">
        </a>
        <h1>Create account</h1>

        <p class="auth-subtitle">
            Join Techora and discover quality phone accessories.
        </p>

        <?php if (!empty($message)): ?>
            <p class="auth-message">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

       <form method="POST" action="register.php" class="auth-form" id="registerForm">

            <label for="full_name">Full Name</label>
            <input
                type="text"
                id="full_name"
                name="full_name"
                placeholder="Enter your full name"
                required
            >

  <label for="email">Email Address</label>
 <input
   type="email"
  id="email"
   name="email"
 placeholder="Enter your email address"
 required
      >
   <label for="password">Password (at least 8 characters)</label>


<div class="password-wrapper">

    <input
        type="password"
        id="password"
        name="password"
        placeholder="Create a password"
        minlength="8"
        required
    >

    <button type="button"
            class="toggle-password"
            id="togglePassword">
        Show Password
    </button>

</div>

<div id="passwordStrength" class="password-strength"></div>
<div id="passwordRequirements">

    <p id="reqLength"> At least 8 characters</p>

    <p id="reqUppercase"> One uppercase letter (A-Z)</p>

    <p id="reqLowercase">One lowercase letter (a-z)</p>

    <p id="reqNumber"> One number (0-9)</p>

    <p id="reqSpecial"> One special character (!@#$...)</p>

</div>

<label for="confirm_password">Confirm Password</label>

<input
    type="password"
    id="confirm_password"
    name="confirm_password"
    placeholder="Confirm your password"
    required
>
<div id="passwordMatch"></div>

     <button type="submit">Create account</button>
        </form>

        <p class="auth-switch">
            Already have an account?
            <a href="login.php">Sign in</a>
        </p>

        <a href="index.php" class="auth-back">← Back to Techora</a>

    </div>

</div>

<script>
const passwordInput = document.getElementById('password');
const togglePassword = document.getElementById('togglePassword');

// Show and hide password.
togglePassword.addEventListener('click', function () {

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        togglePassword.textContent = 'Hide Password';
    } else {
        passwordInput.type = 'password';
        togglePassword.textContent = 'Show Password';
    }

});

// Check password strength.
const passwordStrength = document.getElementById('passwordStrength');

passwordInput.addEventListener('input', function () {

    const password = passwordInput.value;
    let strength = 0;

    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    if (password.length === 0) {
        passwordStrength.textContent = '';
    } else if (strength <= 2) {
        passwordStrength.textContent = 'Weak password';
        passwordStrength.style.color = 'red';
    } else if (strength <= 4) {
        passwordStrength.textContent = 'Medium password';
        passwordStrength.style.color = 'orange';
    } else {
        passwordStrength.textContent = 'Strong password';
        passwordStrength.style.color = 'green';
    }

});

// Your existing password strength checker
passwordInput.addEventListener('input', function () {

    // Existing strength-checking code stays here

});

// NEW password requirements code goes here
const reqLength = document.getElementById('reqLength');
const reqUppercase = document.getElementById('reqUppercase');
const reqLowercase = document.getElementById('reqLowercase');
const reqNumber = document.getElementById('reqNumber');
const reqSpecial = document.getElementById('reqSpecial');

// Continue with the new requirement-checking function...

// Check whether both passwords match.
const confirmPassword = document.getElementById('confirm_password');
const passwordMatch = document.getElementById('passwordMatch');

function checkPasswords() {

    if (confirmPassword.value === '') {
        passwordMatch.textContent = '';
    } else if (passwordInput.value === confirmPassword.value) {
        passwordMatch.textContent = 'Passwords match';
        passwordMatch.style.color = 'green';
    } else {
        passwordMatch.textContent = 'Passwords do not match';
        passwordMatch.style.color = 'red';
    }

}

passwordInput.addEventListener('input', checkPasswords);
confirmPassword.addEventListener('input', checkPasswords);

const registerForm = document.getElementById('registerForm');

registerForm.addEventListener('submit', function (event) {

    const password = passwordInput.value;

    const isValid =
        password.length >= 8 &&
        /[A-Z]/.test(password) &&
        /[a-z]/.test(password) &&
        /[0-9]/.test(password) &&
        /[^A-Za-z0-9]/.test(password);

    if (!isValid) {
        event.preventDefault();

        alert('Please meet all password requirements before creating your account.');
    }

});

registerForm.addEventListener('submit', function () {

    const submitButton = registerForm.querySelector('button[type="submit"]');

    submitButton.disabled = true;
    submitButton.textContent = 'Creating account...';

});
</script>
</body>

</html>
