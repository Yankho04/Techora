
<?php
function logAdminActivity(mysqli $conn, int $admin_id, string $action, string $details = ""): bool
{
    $sql = "INSERT INTO admin_activity_logs
            (admin_id, action, details)
            VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "iss",
        $admin_id,
        $action,
        $details
    );

    $success = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $success;
}
