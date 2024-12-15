<?php
include 'connection.php';

if (isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];

    // Clear the user's balance
    $conn->query("UPDATE users SET balance = 0 WHERE id = '$userId'");
    
    // Delete the user's loan data
    $conn->query("DELETE FROM loans WHERE username = (SELECT username FROM users WHERE id = '$userId')");

    echo "User's balance and loan data cleared.";
} else {
    echo "Invalid request.";
}
?>
