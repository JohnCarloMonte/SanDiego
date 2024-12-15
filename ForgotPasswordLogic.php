<?php
session_start();
include("connection.php");

// Get POST data
$username = $_POST['username'];
$old_password = $_POST['old_password'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

$errors = [];

// Validate input
if (empty($username) || empty($old_password) || empty($new_password) || empty($confirm_password)) {
    $errors[] = "All fields are required.";
}
if ($new_password !== $confirm_password) {
    $errors[] = "New password and confirmation do not match.";
}

// Check if the username and old password are correct
if (empty($errors)) {
    $query = "SELECT password FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $old_password === $user['password']) { // Directly compare old password
        // Update password without hashing (not recommended for production)
        $update_query = "UPDATE users SET password = ? WHERE username = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ss", $new_password, $username);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Password reset successfully.";
            header("Location: successpage.php");
            echo"Password successfully changed!";
            exit();
        } else {
            $errors[] = "Failed to reset password.";
        }
    } else {
        $errors[] = "Invalid old password.";
    }
}

// If there are errors, redirect back with errors
$_SESSION['forgot_password_errors'] = $errors;
header("Location: forgot_password.php");
exit();
