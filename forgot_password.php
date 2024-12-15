<?php
// Start the session
session_start();
include("connection.php");

// Retrieve error messages from session if any
$errors = isset($_SESSION['forgot_password_errors']) ? $_SESSION['forgot_password_errors'] : [];

// Clear the error messages from the session
unset($_SESSION['forgot_password_errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>San Diego - Forgot Password</title>
    <!-- Link to Google Fonts for Open Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Form Container */
        .forgot-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
        }

        /* Form Heading */
        .forgot-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
        }
        iframe {
            display: none;
        }

        /* Form Styling */
        .forgot-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .forgot-form label {
            font-size: 1rem;
            color: #333333;
        }

        .forgot-form input {
            padding: 10px;
            border: 2px solid #F5E071;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            width: 100%;
        }

        .forgot-form input:focus {
            border-color: #e6d165;
            outline: none;
        }

        .forgot-form button {
            padding: 10px;
            background-color: #F5E071;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: bold;
            color: black;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        .forgot-form button:hover {
            background-color: #e6d165;
        }

        /* Error Message Styling */
        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <!-- Forgot Password Form -->
    <div class="forgot-container">
        <h2>Forgot Password</h2>

        <!-- Display Error Message if Any -->
        <?php
            if (!empty($errors)) {
                echo '<div class="error"><ul>';
                foreach ($errors as $error) {
                    echo '<li>' . htmlspecialchars($error) . '</li>';
                }
                echo '</ul></div>';
            }
        ?>

        <!-- Forgot Password Form -->
        <form class="forgot-form" method="POST" action="ForgotPasswordLogic.php" autocomplete="off">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" placeholder="Enter your username" autocomplete="new-username" required>

    <label for="old_password">Old Password</label>
    <input type="password" id="old_password" name="old_password" placeholder="Enter your old password" autocomplete="new-password" required>

    <label for="new_password">New Password</label>
    <input type="password" id="new_password" name="new_password" placeholder="Enter your new password" autocomplete="new-password" required>

    <label for="confirm_password">Re-enter New Password</label>
    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your new password" autocomplete="new-password" required>

    <button type="submit">Reset Password</button>
</form>
<div style="text-align: center; margin-top: 20px;">
    <button onclick="location.href='Home.html'" style="padding: 10px; background-color: #F5E071; border: none; border-radius: 50px; font-size: 1rem; font-weight: bold; color: black; cursor: pointer; transition: background-color 0.3s ease; width: auto;">
        Back to Main Page
    </button>
</div>
    </div>


</body>
</html>
