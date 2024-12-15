<?php
include 'db_connect.php';
$user_id = $_GET['user_id'];
$conn->query("DELETE FROM users WHERE id = $user_id");
?>
