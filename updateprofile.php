<?php
// Start the session
session_start();
include("connection.php");

// Get the username from the session
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

$user_email = '';

if ($username) {
    // Prepare and execute the SQL query to fetch the email
    $query = "SELECT email FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username); // "s" for string type
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the email
        $row = $result->fetch_assoc();
        $user_email = $row['email'];
    } else {
        echo "User not found.";
    }
    $stmt->close();
} else {
    echo "No username found in session.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>San Diego - Update Profile</title>
    <!-- Link to Google Fonts for Open Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
    <style>
        /* [Existing CSS styles as provided] */
        /* Reset some default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            position: relative;
            min-height: 100vh;
            overflow-x: hidden;
            background-color: #ffffff;
        }

        /* Background Circles */
        .background-circle {
            position: absolute;
            border-radius: 50%;
            opacity: 0.6;
            z-index: -1;
        }

        .circle1 {
            width: 150vw;
            height: 700px;
            background-color: #f9dd4e;
            bottom: 720px;
            left: 50%;
            transform: translateX(-50%) translateY(50%);
            opacity: 0.6;
        }

        .circle2 {
            width: 150vw;
            height: 700px;
            background-color: #f7e96c;
            bottom: 770px;
            left: 50%;
            transform: translateX(-50%) translateY(50%);
        }

        .circle3 {
            width: 150vw;
            height: 700px;
            background-color: #FFFCC9;
            bottom: 820px;
            left: 50%;
            transform: translateX(-50%) translateY(50%);
        }

        /* Navbar container */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: rgba(248, 249, 250, 0.9);
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .nav-left {
            flex: 1;
            text-align: left;
        }

        .nav-center {
            flex: 1;
            text-align: center;
        }

        .nav-right {
            flex: 1;
            text-align: right;
        }

        .site-name {
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            color: black;
        }

        .nav-btn {
            background-color: #F5E071;
            padding: 10px 15px;
            border-radius: 50px;
            text-decoration: none;
            color: black;
            font-weight: bold;
            transition: background-color 0.3s ease;
            display: inline-block;
        }

        .nav-btn:hover {
            background-color: #e6d165;
        }

        .content {
            padding: 100px 20px 20px 20px;
            min-height: 100vh;
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .profile-section {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .profile-section h3 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #333333;
        }

        .profile-info p {
            font-size: 1rem;
            color: #333333;
            margin: 10px 0;
        }

        .profile-buttons {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .update-btn, .logout-btn {
            background-color: #F5E071;
            padding: 10px;
            border-radius: 50px;
            text-decoration: none;
            color: black;
            font-weight: bold;
            transition: background-color 0.3s ease;
            display: inline-block;
            width: 100%;
            text-align: center;
        }

        .update-btn:hover, .logout-btn:hover {
            background-color: #e6d165;
        }
    </style>
</head>
<body>

    <!-- Background Circles -->
    <div class="background-circle circle1"></div>
    <div class="background-circle circle2"></div>
    <div class="background-circle circle3"></div>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-left">
            <a href="Mainpage.php" class="nav-btn">Back to Home</a>
        </div>
        <div class="nav-center">
            <a href="index.html" class="site-name" onclick="location.reload()">San Diego</a>
        </div>
        <div class="nav-right"></div>
    </nav>

    <!-- Main Content -->
    <div class="content">
    <div class="profile-section" id="profile-section">
        <h3>Profile Information</h3>
        <div class="profile-info">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
            <p><strong>mobile number:</strong> <?php echo htmlspecialchars($user_email); ?></p>
        </div>
        <div class="profile-buttons">
            <a href="Updateinfo.php" class="update-btn">Update Account Info</a>
            <a href="Home.html" class="logout-btn">Log Out</a>
        </div>
    </div>
</div>

</body>
</html>
