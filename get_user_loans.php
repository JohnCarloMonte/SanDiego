<?php
include 'db_connect.php';
$user_id = $_GET['user_id'];
$query = "SELECT * FROM loans WHERE username = (SELECT username FROM users WHERE id = $user_id)";
$result = $conn->query($query);

while ($loan = $result->fetch_assoc()) {
    echo "<div>{$loan['status']} - Amount: {$loan['loan_amount']}, Total: {$loan['total_amount']}</div>";
}
?>
