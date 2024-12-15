<?php
// Start the session
session_start();
$errors = [];
$success = '';

// Include database connection
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize user inputs
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    // Basic validation
    if (empty($username)) {
        $errors[] = "Username is required.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } 
    // If no errors, proceed
    if (empty($errors)) {
        // Save user data to session temporarily
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;

        // Redirect to Mverify.php for OTP verification
        header("Location: Mverify.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>San Diego - Signup</title>
    <!-- Link to Google Fonts for Open Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
    <style>
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

        /* Right Side of Navbar (Empty for now) */
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
            margin-left: 8  0px;
            font-size: 30px;
        }

        /* Signup Content Styling */
        .signup-content {
            flex: 1;
            min-width: 300px; /* Adjust based on signup form width */
            display: flex;
            justify-content: center; /* Center the signup form horizontally */
            width: 100%;
        }

        /* Signup Form Container */
        .signup-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
        }

        /* Form Heading */
        .signup-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
        }

        /* Form Styling */
        .signup-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .signup-form label {
            font-size: 1rem;
            color: #333333;
        }

        .signup-form input {
            padding: 10px;
            border: 2px solid #F5E071; /* Outline color */
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            width: 100%;
        }

        .signup-form input:focus {
            border-color: #e6d165; /* Darker outline on focus */
            outline: none;
        }

        .signup-form button {
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

        .signup-form button:hover {
            background-color: #e6d165;
        }

        /* Secondary Buttons Styling */
        .secondary-btns {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        /* New Class for Outline Button */
        .outline-btn {
            background-color: #ffffff; /* White background */
            border: 2px solid #F5E071; /* Outline color */
            color: black; /* Text color matches outline */
            padding: 8px 12px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease, color 0.3s ease;
            display: inline-block;
            font-size: 0.9rem;
        }

        .outline-btn:hover {
            background-color: #F5E071; /* Fill background on hover */
            color: black; /* Change text color on hover */
        }

        /* Success Message Styling */
        .success {
            color: green;
            text-align: center;
            margin-bottom: 10px;
            font-size: 0.9rem;
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

            /* Adjust Signup Content for mobile */
            .signup-content {
                width: 100%;
                justify-content: center; /* Ensure signup form is centered */
            }

            /* Adjust Signup Form for mobile */
            .signup-container {
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

            .secondary-btn,
            .outline-btn {
                width: 100%;
                text-align: center;
            }
        }

        /* Focus States for Accessibility */
        .signup-form input:focus,
        .signup-form button:focus,
        .nav-btn:focus,
        .outline-btn:focus {
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
        .logo-print {
                display: block;
                margin: 0 auto;
                width: 200px; /* Adjust size as needed */
            }
            #firsth1 {
    text-align: center;
}
.mobile-number-container {
    display: flex;
    align-items: center;
}

.country-code {
    font-weight: bold;
    margin-right: 5px;
    padding: 5px;
    background-color: #f0f0f0;
    border: 1px solid #ccc;
    border-radius: 5px;
}

input[type="tel"] {
    flex-grow: 1;
    padding: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
    width: 100%;
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
            <a href="Home.html" class="nav-btn">Back to Home</a>
        </div>
        <div class="nav-center">
            <a href="index.html" class="site-name" onclick="location.reload()">San Diego</a>
        </div>
        <div class="nav-right"></div>
    </nav>

    <!-- Main Content -->
    <div class="content">
        <div class="main-container">
            <!-- Text Content -->
 
            <div class="text-content">
            <img src="logo.png" class="logo-print" alt="Logo">
                <h1 id="firsth1">Join Us Today!</h1>
            </div>

            <!-- Signup Form -->
            <div class="signup-content">
                <div class="signup-container" aria-labelledby="signup-heading">
                    <h2 id="signup-heading">Create Account</h2>

                    <!-- Display Success or Error Messages -->
                    <?php if (!empty($success)) : ?>
                        <div class="success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($errors)) : ?>
                        <div class="error">
                            <ul>
                                <?php foreach ($errors as $error) : ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                        <form class="signup-form" method="POST" action="Signup.php" aria-labelledby="signup-heading">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" placeholder="Enter your username" required>
                            
                            <label for="email">Email</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           placeholder="Enter your email address" 
                           required 
                           maxlength="100" 
                           title="Please enter a valid email address">




                            <button type="submit" id="signup">Signup</button>

                            <div class="secondary-btns">
                                <a href="Login.php" class="outline-btn">Already have an account? Login</a>
                            </div>
                        </form>
                </div>
            </div>
        </div>

        <div class="final-text">
            <p>Building Trust, Funding Dreams.</p>
            <p>San Diego | 2024</p>
        </div>

        <div class="flag-container">
            <a href="#" class="flag-link" aria-label="United States">
                <img src="united-states.png" alt="United States Flag" class="flag-icon">
            </a>
            <a href="#" class="flag-link" aria-label="Philippines">
                <img src="philippines.png" alt="Philippines Flag" class="flag-icon">
            </a>
        </div>
    </div>

    <script>
        // Allow keyboard navigation for the signup form
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.signup-form');
            form.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    form.submit();
                }
            });
        });
        function addPrefix(input) {
    // Ensure the value starts with '+63' and limit the total input length to 12 characters (including '+63')
    let currentValue = input.value;
    
    if (currentValue.startsWith('+63')) {
        // Strip out non-numeric characters except the initial '+63'
        input.value = '+63' + currentValue.slice(3).replace(/[^0-9]/g, '').slice(0, 10);
    } else {
        // If the value doesn't start with '+63', just start it
        input.value = '+63' + currentValue.replace(/[^0-9]/g, '').slice(0, 10);
    }
}

    </script>
</body>

</html>
