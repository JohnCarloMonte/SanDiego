<?php
session_start();

// If you have set a success message in session, display it
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the session message after showing it
} else {
    $message = "Password changed successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Change Success</title>
    
    <!-- Meta tag to redirect after 2 seconds -->
    <meta http-equiv="refresh" content="2;url=Login.php">
    
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
        }
        
        .success-container {
            text-align: center;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .success-message {
            font-size: 1.5rem;
            color: green;
            margin-bottom: 20px;
        }
        
        .redirect-message {
            font-size: 1rem;
            color: #333;
        }
        iframe {
            display: none;
        }
    </style>
</head>
<body>

    <div class="success-container">
        <div class="success-message">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <div class="redirect-message">
            You will be redirected to the login page shortly...
        </div>
    </div>

</body>
</html>
