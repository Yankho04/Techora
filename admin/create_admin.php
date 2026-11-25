
<?php
session_start();

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/activity_log.php";

// Allow administrators only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}


$message = "";

if (isset($_POST['create_admin'])) {

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check that passwords match
    if ($password !== $confirm_password) {

        $message = "Passwords do not match.";

    } elseif (strlen($password) < 8) {

        $message = "Password must contain at least 8 characters.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } else {

        // Check whether email already exists
        $check_sql = "SELECT id FROM users WHERE email = ?";

        $check_stmt = mysqli_prepare($conn, $check_sql);

        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {

            $message = "This email address is already registered.";

        } else {

            // Securely hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Create administrator account
            $insert_sql = "INSERT INTO users (full_name, email, password, role)
                           VALUES (?, ?, ?, 'admin')";

            $insert_stmt = mysqli_prepare($conn, $insert_sql);

            mysqli_stmt_bind_param(
                $insert_stmt,
                "sss",
                $full_name,
                $email,
                $hashed_password
            );

             if (mysqli_stmt_execute($insert_stmt)) {

    logAdminActivity(
        $conn,
        (int) $_SESSION['user_id'],
        "Created Admin Account",
        "Created administrator account for " . $full_name
    );

    $message = "Administrator account created successfully!";

}

            else {

                $message = "Unable to create administrator account.";
            }

            mysqli_stmt_close($insert_stmt);
        }

        mysqli_stmt_close($check_stmt);
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account - Techora</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="admin-layout">

    <aside class="admin-sidebar">
        <h2>TECHORA.</h2>
        <p>Administrator Panel</p>

        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Manage Products</a>
        <a href="orders.php">Manage Orders</a>
        <a href="reports.php">Sales Reports</a>
        <a href="contact_messages.php">Customer Messages</a>
        <a href="../index.php">Visit Store</a>
        <a href="../logout.php">Logout</a>
    </aside>

    <main class="admin-content">

        <h1>Create Admin Account</h1>

        <?php if (!empty($message)): ?>

    <p class="form-message">
        <?php echo htmlspecialchars($message); ?>
    </p>

<?php endif; ?>

        <p>Register a new administrator for Techora.</p>

        <form method="POST" action="create_admin.php">

            <label>Full Name</label>
            <input type="text" name="full_name"
                   placeholder="Enter full name" required>

            <label>Email Address</label>
            <input type="email" name="email"
                   placeholder="Enter email address" required>

            <label>Password</label>
            <input type="password" name="password"
                   placeholder="Enter password" required>

            <label>Confirm Password</label>
            <input type="password" name="confirm_password"
                   placeholder="Confirm password" required>

            <button type="submit" name="create_admin">
                Create Admin Account
            </button>

        </form>

        <p><a href="dashboard.php">Back to Dashboard</a></p>

    </main>

</div>

</body>
</html>
