<?php
include 'connection.php';

// Retrieve POST data
$user_id = $_POST['user_id'];
$amount = $_POST['amount'];

$response = ['success' => false, 'message' => '', 'new_balance' => 0];

if (is_numeric($amount) && $amount > 0) {
    // Fetch user's current balance
    $query = "SELECT balance FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $new_balance = max(0, $user['balance'] - $amount); // Prevent negative balances

        // Update the balance in the database
        $update_query = "UPDATE users SET balance = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("di", $new_balance, $user_id);
        if ($update_stmt->execute()) {
            $response['success'] = true;
            $response['new_balance'] = $new_balance;
        } else {
            $response['message'] = "Failed to update balance.";
        }
    } else {
        $response['message'] = "User not found.";
    }
} else {
    $response['message'] = "Invalid amount.";
}

echo json_encode($response);
?>
