<?php
session_start();
include("connection.php");

// Check if the user has signed up
if (!isset($_SESSION['email'])) {
    header("Location: signup.php");
    exit();
}

$errors = [];
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['resend'])) {
        // Resend OTP logic here (e.g., send SMS)
        $success = "A new OTP has been sent to your mobile number.";
    }

    if (isset($_POST['verify'])) {
        $entered_otp = trim($_POST['otp1']) . trim($_POST['otp2']) . trim($_POST['otp3']) . trim($_POST['otp4']);
        $correct_otp = "1234"; // Replace with actual OTP generation and storage logic

        if ($entered_otp === $correct_otp) {
            $_SESSION['verified'] = true;
            header("Location: cpassword.php");
            exit();
        } else {
            $errors[] = "Invalid OTP. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>San Diego - Mobile Verification</title>
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

        /* Mobile Verification Content Styling */
        .mverify-content {
            flex: 1;
            min-width: 300px; /* Adjust based on form width */
            display: flex;
            justify-content: center; /* Center the form horizontally */
            width: 100%;
        }

        /* Mobile Verification Form Container */
        .mverify-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
        }

        /* Form Heading */
        .mverify-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
        }

        /* Message Styling */
        .mverify-message {
            text-align: center;
            margin-bottom: 20px;
            color: #555555;
            font-size: 1rem;
        }

        /* Form Styling */
        .mverify-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .mverify-form label {
            font-size: 1rem;
            color: #333333;
        }

        /* OTP Input Boxes */
        .otp-inputs {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .otp-inputs input {
            width: 60px;
            padding: 10px;
            border: 2px solid #F5E071; /* Outline color */
            border-radius: 10px;
            font-size: 1.5rem;
            text-align: center;
            transition: border-color 0.3s ease;
        }

        .otp-inputs input:focus {
            border-color: #e6d165; /* Darker outline on focus */
            outline: none;
        }

        .mverify-form button {
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

        .mverify-form button:hover {
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
            text-align: center;
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
            .nav-btn {
                padding: 10px 10px;
            }
            .main-container {
                flex-direction: column;
                gap: 50px; /* Reduced space between stacked elements */
                align-items: center;
            }

            /* Adjust Mobile Verification Content for mobile */
            .mverify-content {
                width: 100%;
                justify-content: center; /* Ensure form is centered */
            }

            /* Adjust Mobile Verification Form for mobile */
            .mverify-container {
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
        .mverify-form input:focus,
        .mverify-form button:focus,
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
            <a href="Signup.php" class="nav-btn">Change email</a>
        </div>

        <!-- Center of Navbar -->
        <div class="nav-center">
            <a href="Home.php" class="site-name" onclick="location.reload()">San Diego</a>
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
            <!-- Text Content (Optional) -->
            <div class="text-content">
            <img src="logo.png" class="logo-print" alt="Logo">
                <h1 id="firsth1">Mobile Verification</h1>
                <p>Please enter the 4-digit code sent to your mobile number to verify your account.</p>
            </div>

            <!-- Mobile Verification Content (Verification Form) -->
            <div class="mverify-content">
                <div class="mverify-container" aria-labelledby="mverify-heading">
                    <h2 id="mverify-heading">Verify Your Mobile Number</h2>

                    <!-- Display Success Message if Any -->
                    <?php
                        if (!empty($success)) {
                            echo '<div class="success">' . $success . '</div>';
                            // If OTP was verified, trigger the redirect
                            if (isset($_SESSION['verified']) && $_SESSION['verified'] === true) {
                                echo '<script>
                                    setTimeout(function(){
                                        window.location.href = "cpassword.php";
                                    }, 1000); // 1000 milliseconds = 1 second
                                </script>';
                            }
                        }

                        // Display Error Messages if Any
                        if (!empty($errors)) {
                            echo '<div class="error"><ul>';
                            foreach ($errors as $error) {
                                echo '<li>' . htmlspecialchars($error) . '</li>';
                            }
                            echo '</ul></div>';
                        }
                    ?>

                    <!-- Verification Form -->
                    <form class="mverify-form" method="POST" action="Mverify.php" aria-labelledby="mverify-heading">
                       
                    <div id="otpMessage" style="display: none; color: green;"></div>
                    <div class="mverify-message">
                            <?php
                                // Retrieve the mobile number from the session
                                $mobile_number = htmlspecialchars($_SESSION['email']);
                                echo "We have sent a 4-digit code to <strong>" . $mobile_number . "</strong>.";
                            ?>
                        </div>

                        <div class="otp-inputs">
                            <input type="text" id="otp1" name="otp1" maxlength="1" pattern="[0-9]" required autofocus>
                            <input type="text" id="otp2" name="otp2" maxlength="1" pattern="[0-9]" required>
                            <input type="text" id="otp3" name="otp3" maxlength="1" pattern="[0-9]" required>
                            <input type="text" id="otp4" name="otp4" maxlength="1" pattern="[0-9]" required>
                        </div>

                        <button href="say.php" type="submit" name="verify">Next</button>

                        <!-- Secondary Buttons -->
                        <div class="secondary-btns">
    <button type="button" name="resend" id="resendBtn" class="outline-btn">Resend OTP</button>
</div>


                        <a href="Signup.php" class="outline-btn">Change mobile number</a>

                        <!-- Already have an account button -->
                        <div class="secondary-btns" style="margin-top: 10px;">
                            <a href="Login.php" class="outline-btn">Already have an account? Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
                                <!-- Final Text -->
        <div class="final-text">
            Building Trust, Funding Dreams.
            <p>San Diego | 2024</p>
        </div>
    </div>


        <div class="flag-container">
            <a href="#" class="flag-link" aria-label="United States">
                <img src="united-states.png" alt="United States Flag" class="flag-icon">
            </a>
            <a href="#" class="flag-link" aria-label="Philippines">
                <img src="philippines.png" alt="Philippines Flag" class="flag-icon">
            </a>
        </div>
    <!-- Optional JavaScript for Accessibility Enhancements -->
    <script>
        // Handle OTP input focus
        const otpInputs = document.querySelectorAll('.otp-inputs input');

        otpInputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                if (input.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && input.value === '' && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });

            // Ensure only numeric input is allowed
            input.addEventListener('keypress', (e) => {
                const char = String.fromCharCode(e.which);
                if (!/[0-9]/.test(char)) {
                    e.preventDefault();
                }
            });
        });

        // Allow keyboard navigation for the form
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.mverify-form');
            form.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    // Prevent form submission when pressing Enter in the OTP fields
                    // Only submit if focus is on the last OTP input or a button
                    if (document.activeElement === otpInputs[otpInputs.length - 1]) {
                        form.submit();
                    }
                }
            });
        });
        document.getElementById('resendBtn').addEventListener('click', function(event) {
    // No need for preventDefault() since it's not a submit button
    
    const messageElement = document.getElementById('otpMessage');
    messageElement.textContent = 'OTP resent successfully';
    messageElement.style.display = 'block';

    // Optional: Hide the message after a few seconds
    setTimeout(() => {
        messageElement.style.display = 'none';
    }, 3000);
});
    </script>

</body>
</html>
