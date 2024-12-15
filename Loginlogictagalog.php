<?php
session_start();
include("connection.php");

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username)) {
        $errors[] = "Username is required.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        // Check if username exists
        $stmt = $conn->prepare("SELECT username, password FROM users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            echo "Number of results: " . $stmt->num_rows;  // Debugging: Check if username was found

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($db_username, $db_password);
                $stmt->fetch();

                echo "Database password: " . $db_password; // Debugging: Output the database password

                if ($password === $db_password) {  // Check plain-text password match
                    $_SESSION['username'] = $db_username;
                    session_regenerate_id(true);
                    header("Location: Mainpagetagalog.php");
                    exit();
                } else {
                    $errors[] = "Invalid username or password.";
                }
            } else {
                $errors[] = "Invalid username or password.";
            }

            $stmt->close();
        } else {
            $errors[] = "An error occurred. Please try again later.";
        }
    }

    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors;
        $_SESSION['login_username_email'] = htmlspecialchars($username);
        header("Location: Login.php");
        exit();
    }
} else {
    header("Location: Login.php");
    exit();
}
?>
