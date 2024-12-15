<?php
session_start();
include("connection.php");

// Check if the user has verified their mobile number
if (!isset($_SESSION['verified']) || $_SESSION['verified'] !== true) {
    header("Location: Mverify.php");
    exit();
}

$errors = [];
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize user inputs
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Basic validation
    if (empty($password) || empty($confirm_password)) {
        $errors[] = "Both password fields are required.";
    } else {
        // Check password length
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }

        // Check if password contains at least one number
        if (!preg_match('/\d/', $password)) {
            $errors[] = "Password must contain at least one number.";
        }

        // Check if passwords match
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }

        // If there are no errors, proceed to save to the database
        if (empty($errors)) {
            // Store the username and email from the session
            $username = $_SESSION['username'];
            $email = $_SESSION['email'];

            // Prepare SQL to insert user data without password hashing
            $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";

            // Execute the query
            if ($conn->query($sql) === TRUE) {
                // Clear the verification session variable
                unset($_SESSION['verified']);

                // Redirect to say.php
                header("Location: say.php");
                exit();
            } else {
                $errors[] = "Error: " . $conn->error; // Capture any SQL errors
            }
        }
    }
}
?>

<!-- Display errors if any -->
<?php if (!empty($errors)): ?>
    <div class="error-messages">
        <?php foreach ($errors as $error): ?>
            <p><?php echo $error; ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>San Diego - Create Password</title>
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
        .cpassword-content {
            flex: 1;
            min-width: 300px; /* Adjust based on form width */
            display: flex;
            justify-content: center; /* Center the form horizontally */
            width: 100%;
        }

        /* Create Password Form Container */
        .cpassword-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
        }

        /* Form Heading */
        .cpassword-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
        }

        /* Password Criteria Checklist */
        .password-criteria {
            margin-bottom: 20px;
            color: #555555;
            font-size: 0.9rem;
        }

        .password-criteria ul {
            list-style: none;
            padding-left: 0;
        }

        .password-criteria li {
            margin-bottom: 5px;
            position: relative;
            padding-left: 20px;
        }

        .password-criteria li::before {
            content: "✔";
            position: absolute;
            left: 0;
            color: green;
        }

        /* Form Styling */
        .cpassword-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .cpassword-form label {
            font-size: 1rem;
            color: #333333;
        }

        .cpassword-form input {
            padding: 10px;
            border: 2px solid #F5E071; /* Outline color */
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            width: 100%;
        }

        .cpassword-form input:focus {
            border-color: #e6d165; /* Darker outline on focus */
            outline: none;
        }

        .cpassword-form button {
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

        .cpassword-form button:hover {
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
        .text-content h1 {
            
    text-align: center;
    margin-left: auto;
    margin-right: auto;
}
        

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
                gap: 50px; /* Reduced space between stacked elements */
                align-items: center;
            }

            /* Adjust Create Password Content for mobile */
            .cpassword-content {
                width: 100%;
                justify-content: center; /* Ensure form is centered */
            }

            /* Adjust Create Password Form for mobile */
            .cpassword-container {
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
        .cpassword-form input:focus,
        .cpassword-form button:focus,
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
            .nav-btn {
                padding: 10px 10px;
            }
        }

        @media (max-width: 480px) {
            .flag-icon {
                width: 20px; /* Even smaller flags on very small screens */
            }
        }
        #signup {

            color: black;
            text-decoration: none;
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
            <a href="Signup.php" class="nav-btn">Change Mobile Number</a>
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
                <h1 id="firsth1">Create Your Password</h1>
            </div>

            <!-- Create Password Content (Password Creation Form) -->
            <div class="cpassword-content">
                <div class="cpassword-container" aria-labelledby="cpassword-heading">
                    <h2 id="cpassword-heading">Set Your Password</h2>

                    <!-- Display Success Message if Any -->
                    <?php
                        if (!empty($success)) {
                            echo '<div class="success">' . $success . '</div>';
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

                    <!-- Password Criteria Checklist -->
                    <div class="password-criteria">
                        <strong>Password must contain:</strong>
                        <ul>
                            <li>At least 8 characters</li>
                            <li>At least one number</li>
                        </ul>
                    </div>

                    <!-- Create Password Form -->
                    <form class="cpassword-form" method="POST" action="cpassword.php" aria-labelledby="cpassword-heading">
                        <label for="password">Create Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>

                        <label for="confirm_password">Re-enter Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>

                        <button type="submit"><a href="say.php" id="signup">Sign up</a></button>

                        <!-- Secondary Buttons -->
                        <div class="secondary-btns">
                            <a href="Login.php" class="outline-btn">Already have an account? Login</a>
                            <!-- Optionally, add more secondary buttons if needed -->
                        </div>

                        <!-- Change Mobile Number Button -->
                        <div class="secondary-btns" style="margin-top: 10px;">
                            <a href="Signup.php" class="outline-btn">Change Mobile Number</a>
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
        // Allow keyboard navigation for the create password form
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.cpassword-form');
            form.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    form.submit();
                }
            });

            // Optionally, add real-time password strength validation
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const criteriaList = document.querySelectorAll('.password-criteria li');

            passwordInput.addEventListener('input', () => {
                const password = passwordInput.value;

                // Check for minimum length
                if (password.length >= 8) {
                    criteriaList[0].style.color = 'green';
                } else {
                    criteriaList[0].style.color = '#555555';
                }

                // Check for at least one number
                if (/\d/.test(password)) {
                    criteriaList[1].style.color = 'green';
                } else {
                    criteriaList[1].style.color = '#555555';
                }
            });

            // Optional: Confirm password matching
            confirmPasswordInput.addEventListener('input', () => {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;

                if (password === confirmPassword) {
                    confirmPasswordInput.style.borderColor = 'green';
                } else {
                    confirmPasswordInput.style.borderColor = 'red';
                }
            });
        });
    </script>

</body>
</html>
