<?php
include 'db_connect.php';
$user_id = $_GET['user_id'];
$conn->query("UPDATE users SET balance = 0 WHERE id = $user_id");
?>
