<?php

$host = 'localhost';           // Database host (usually 'localhost')
$db   = 'sandiegodatabase';    // Name of your database
$username = 'root';    // Your database username
$password = '';    // Your database password
$conn = new mysqli($host, $username, $password, $db);
if($conn->connect_error){
    die("Connection failed".$conn->connect_error);
}
echo "";
?>