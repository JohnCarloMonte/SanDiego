<?php
include 'connection.php';

$username = 'admin'; // Replace with your desired admin username
$password = 'admin123'; // Replace with your desired password

// Hash the password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Insert the admin user into the database
$stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hashed_password);

if ($stmt->execute()) {
    echo "Admin user created successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
