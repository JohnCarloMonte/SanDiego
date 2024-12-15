<?php
session_start();
include 'connection.php';  // Database connection

// Check if the admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");  // Redirect to admin login if not logged in
    exit();
}

// Handle payment acceptance
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['payment_id'])) {
        $payment_id = intval($_POST['payment_id']); // Get the payment ID from the POST data

        // Step 1: Fetch payment details
        $stmt = $conn->prepare("SELECT username, partial_amount FROM pending_payments WHERE id = ?");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $stmt->bind_result($username, $partial_amount);
        $stmt->fetch();
        $stmt->close();

        // If payment exists
        if ($username) {
            // Step 2: Update the payment status to 'accepted'
            $stmt_update = $conn->prepare("UPDATE pending_payments SET status = 'accepted' WHERE id = ?");
            $stmt_update->bind_param("i", $payment_id);

            if ($stmt_update->execute()) {
                // Optional: If you want to update the user's balance or perform any other related action
                // Example: Decreasing the user's balance by the partial amount
                
                $stmt_balance = $conn->prepare("UPDATE users SET balance = balance - ? WHERE username = ?");
                $stmt_balance->bind_param("ds", $partial_amount, $username);
                $stmt_balance->execute();
                

                // Set success message
                $_SESSION['message'] = "Payment from {$username} has been accepted.";
            } else {
                $_SESSION['message'] = "Error updating payment status: " . $stmt_update->error;
            }
            $stmt_update->close();
        } else {
            $_SESSION['message'] = "Payment not found or already processed.";
        }

        // Redirect to admin page or pending payments page after the operation
        header("Location: admin.php");
        exit();
    } else {
        // If payment_id is not set, redirect with an error
        $_SESSION['message'] = "No payment ID provided.";
        header("Location: admin.php");
        exit();
    }
} else {
    // If accessed directly (without POST), redirect
    header("Location: admin.php");
    exit();
}
?>
