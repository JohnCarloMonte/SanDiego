<?php
// Start the session
session_start();
include("connection.php");

// Retrieve error messages from session if any
$errors = isset($_SESSION['login_errors']) ? $_SESSION['login_errors'] : [];

// Retrieve previously entered username/email if available
$previous_username_email = isset($_SESSION['login_username_email']) ? $_SESSION['login_username_email'] : '';

// Clear the error messages and preserved input from session
unset($_SESSION['login_errors']);
unset($_SESSION['login_username_email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>San Diego - Login</title>
    <!-- Link to Google Fonts for Open Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
    <style>
        /* [Existing CSS styles as provided] */
        /* ... (Keep your existing CSS here) ... */
        
        /* Reset some default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            position: relative; /* To position background circles absolutely within the body */
            min-height: 100vh;
            overflow-x: hidden; /* Prevent horizontal scroll due to circles */
            background-color: #ffffff; /* Base background color */
        }

        /* Background Circles */
        .background-circle {
            position: absolute;
            border-radius: 50%;
            opacity: 0.6; /* Adjust opacity for overlapping effect */
            z-index: -1; /* Ensure circles are behind all content */
        }

        /* First Circle */
        .circle1 {
            width: 150vw;
            height: 700px;
            background-color: #f9dd4e; /* Yellow */
            bottom: 720px;
            left: 50%;
            transform: translateX(-50%) translateY(50%);
            opacity: 0.6; /* Corrected opacity */
        }

        /* Second Circle */
        .circle2 {
            width: 150vw;
            height: 700px;
            background-color: #f7e96c; /* Gold */
            bottom: 770px;
            left: 50%;
            transform: translateX(-50%) translateY(50%);
            opacity: 1; /* Corrected opacity */
        }

        /* Third Circle */
        .circle3 {
            width: 150vw;
            height: 700px;
            background-color: #FFFCC9; /* Light Yellow */
            bottom: 820px;
            left: 50%;
            transform: translateX(-50%) translateY(50%);
            opacity: 1; /* Corrected opacity */
        }

        /* Navbar container */
        .navbar {
            display: flex;
            justify-content: space-between; /* Space between left and center */
            align-items: center;
            padding: 10px 20px;
            background-color: rgba(248, 249, 250, 0.9); /* Slight transparency to show background */
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1000; /* Ensure navbar is on top */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Left Side of Navbar */
        .nav-left {
            flex: 1;
            text-align: left;
        }

        /* Center of Navbar */
        .nav-center {
            flex: 1;
            text-align: center;
        }

        /* Right Side of Navbar */
        .nav-right {
            flex: 1;
            text-align: right;
        }

        /* Website name */
        .site-name {
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            color: black;
        }

        /* Navigation Button Styling */
        .nav-btn {
            background-color: #F5E071; /* Same as login button */
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

        /* Content Container */
        .content {
            padding: 100px 20px 20px 20px; /* Top padding accounts for fixed navbar */
            min-height: 100vh;
            position: relative;
            z-index: 1; /* Ensure content is above background circles */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Main Container for Two Columns */
        .main-container {
            display: flex;
            align-items: center;
            width: 100%;
        }

        /* Text Content Styling */
        .text-content {
            flex: 1;
            min-width: 250px;
            position: relative;
            margin-left: 80px;
            font-size: 30px;
        }

        /* Login Content Styling */
        .login-content {
            flex: 1;
            min-width: 300px; /* Adjust based on login form width */
            display: flex;
            justify-content: center; /* Center the login form horizontally */
            width: 100%;
        }

        /* Login Form Container */
        .login-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
        }

        /* Form Heading */
        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
        }

        /* Form Styling */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .login-form label {
            font-size: 1rem;
            color: #333333;
        }

        .login-form input {
            padding: 10px;
            border: 2px solid #F5E071; /* Outline color */
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            width: 100%;
        }

        .login-form input:focus {
            border-color: #e6d165; /* Darker outline on focus */
            outline: none;
        }

        .login-form button {
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

        .login-form button:hover {
            background-color: #e6d165;
        }

        /* Secondary Buttons Styling */
        .secondary-btns {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .secondary-btn {
            background-color: #F5E071; /* Same as login button */
            padding: 8px 12px;
            border-radius: 50px;
            text-decoration: none;
            color: black;
            font-weight: bold;
            transition: background-color 0.3s ease;
            display: inline-block;
            font-size: 0.9rem;
        }

        .secondary-btn:hover {
            background-color: #e6d165;
        }

        /* Error Message Styling */
        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
                gap: 50px; /* Reduced space between stacked elements */
                align-items: center;
            }

            /* Adjust Login Content for mobile */
            .login-content {
                width: 100%;
                justify-content: center; /* Ensure login form is centered */
            }

            /* Adjust Login Form for mobile */
            .login-container {
                max-width: 100%; /* Ensure it takes full width on mobile */
                width: 100%;
            }

            /* Text Content Alignment on Mobile */
            .text-content {
                text-align: center;
                margin-left: 0px; /* Center text on mobile for better aesthetics */
            }

            .text-content h1 {
                font-size: 2rem; /* Adjust heading size for mobile */
            }

            .text-content p {
                font-size: 1rem; /* Adjust paragraph size for mobile */
            }

            /* Stack Secondary Buttons Vertically on Mobile */
            .secondary-btns {
                flex-direction: column;
                gap: 10px;
            }

            .secondary-btn {
                width: 100%;
                text-align: center;
            }
        }

        /* Focus States for Accessibility */
        .login-form input:focus,
        .login-form button:focus,
        .nav-btn:focus,
        .secondary-btn:focus {
            outline: 2px solid #F5E071;
        }
                        /* Final Text */
                        .final-text {
            text-align: center;
            font-size: 1.5rem;
            color: #333333;
            margin-top: 40px;
            font-weight: bold;
        }

        /* Flag Container */
        .flag-container {
            justify-content: center;
            display: flex;
            gap: 20px; /* Space between flags */
        }

        /* Flag Links */
        .flag-link {
            display: inline-block;
            transition: transform 0.3s ease;
        }

        .flag-link:hover,
        .flag-link:focus {
            transform: scale(1.1); /* Slightly enlarge on hover/focus */
        }

        /* Flag Icons */
        .flag-icon {
            width: 30px; /* Adjust size as needed */
            height: auto;
        }

        /* Responsive Adjustments for Flags */
        @media (max-width: 768px) {
            .flag-icon {
                width: 25px; /* Smaller flags on mobile */
            }
        }

        @media (max-width: 480px) {
            .flag-icon {
                width: 20px; /* Even smaller flags on very small screens */
            }
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
        <!-- Left Side of Navbar -->
        <div class="nav-left">
            <a href="Hometagalog.html" class="nav-btn">Bumalik</a>
        </div>

        <!-- Center of Navbar -->
        <div class="nav-center">
            <a href="index.html" class="site-name" onclick="location.reload()">San Diego</a>
        </div>

        <!-- Right Side of Navbar (Empty for now) -->
        <div class="nav-right">
            <!-- Placeholder for future navigation items -->
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content">
        <!-- Main Container for Two Columns -->
        <div class="main-container">
            <!-- Text Content (Heading and Paragraph) -->
            <div class="text-content">
                <h1 id="firsth1">Pagtatag ng Tiwala, Pagtupad ng Pangarap.</h1>
            </div>

            <!-- Login Content (Login Form) -->
            <div class="login-content">
                <div class="login-container" aria-labelledby="login-heading">
                    <h2 id="login-heading">Maligayang Pagbabalik!</h2>

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

                    <!-- Login Form -->
                    <form class="login-form" method="POST" action="Loginlogictagalog.php" aria-labelledby="login-heading">
                        <label for="username">Username or Email</label>
                        <input type="text" id="username" name="username" placeholder="Enter your username or email" value="<?php echo htmlspecialchars($previous_username_email); ?>" required>

                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>

                        <button href="Mainpage.php"type="submit">Mag-login</button>

                        <!-- Secondary Buttons -->
                        <div class="secondary-btns">
                            <a href="forgot_password.php" class="secondary-btn">Nakalimutan ang Password?</a>
                            <a href="Signup.php" class="secondary-btn">Gumawa ng Account</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
        <!-- Final Text -->
        <div class="final-text">
     
            <p>San Diego | 2024</p>
        </div>
    </div>


        <div class="flag-container">
            <a href="Login.php" class="flag-link" aria-label="United States">
                <img src="united-states.png" alt="United States Flag" class="flag-icon">
            </a>
            <a href="#" class="flag-link" aria-label="Philippines">
                <img src="philippines.png" alt="Philippines Flag" class="flag-icon">
            </a>
        </div>
    <!-- Optional JavaScript for Accessibility Enhancements -->
    <script>
        // Allow keyboard navigation for the login form
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.login-form');
            form.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    form.submit();
                }
            });
        });
    </script>

</body>
</html>
