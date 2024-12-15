<?php
// Start the session
session_start();
include ("connection.php");

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Query to fetch the user's balance from the database
    $balanceQuery = "SELECT balance FROM users WHERE username = ?";
    $stmt = $conn->prepare($balanceQuery);

    if ($stmt === false) {
        die("Prepare failed: " . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($user_balance);
    $stmt->fetch();
    $stmt->close();

    // Optionally, you can format the balance for display
    $formatted_balance = number_format($user_balance, 2);
} else {
    echo "User is not logged in.";
}

$amountAfterDue = ($user_balance > 0) ? $user_balance + 100 : 0;

// Optionally, you can format the balance
$formatted_balance = number_format($user_balance, 2);

// Retrieve user's email from the session
$user_email = isset($_SESSION['email']) ? $_SESSION['email'] : 'Not Provided';

if (isset($_SESSION['loan_success'])) {
    echo "<script>
        alert('Request Loan Successful, wait for the confirmation. Thank you!');
    </script>";
    unset($_SESSION['loan_success']); // Clear the session variable
}

// Fetch loan data for the logged-in user
$sql = "SELECT id, loan_amount, total_amount, months, payment_method, payment_date, first_due_date, status 
        FROM loans 
        WHERE username = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Fetch payment records for the logged-in user with the status "pending" or "accepted"
$user_payment_query = "SELECT * FROM payments WHERE name = ? AND status IN ('pending', 'accepted') ORDER BY date DESC";
$stmt = $conn->prepare($user_payment_query);
$stmt->bind_param("s", $username);
$stmt->execute();
$user_payment_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>San Diego - Homepage</title>
    <!-- Link to Google Fonts for Open Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
    <style>
        /* Reset some default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Enable smooth scrolling */
        html {
            scroll-behavior: smooth;
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
            bottom: 1500px;
            left: 50%;
            transform: translateX(-50%) translateY(50%);
            opacity: 0.6; /* Corrected opacity */
        }

        /* Second Circle */
        .circle2 {
            width: 150vw;
            height: 700px;
            background-color: #f7e96c; /* Gold */
            bottom: 1200px;
            left: 50%;
            transform: translateX(-50%) translateY(50%);
            opacity: 1; /* Corrected opacity */
        }

        /* Third Circle */
        .circle3 {
            width: 150vw;
            height: 600px;
            background-color: #FFFCC9; /* Light Yellow */
            bottom: 1100px;
            left: 50%;
            transform: translateX(-50%) translateY(50%);
            opacity: 1; /* Corrected opacity */
        }

        /* Top Navbar container */
        .navbar {
    display: flex;
    justify-content: space-between; /* Space between left, center, and right */
    align-items: center;
    padding: 10px 20px;
    background-color: rgba(248, 249, 250, 0.9); /* Slight transparency */
    position: fixed;
    width: 100%;
    top: 0;
    left: 0;
    z-index: 1000; /* Ensure navbar is on top */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}


        /* Left Side of Navbar */
        .nav-left {
    display: flex;
    align-items: center;
}

.site-name {
    font-size: 24px;
    font-weight: bold;
    text-decoration: none;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px; /* Adds spacing between the logo and text */
}

.site-logo {
    width: 30px; /* Adjust width as needed */
    height: auto; /* Maintain aspect ratio */
    display: inline-block;
    vertical-align: middle;
}

/* Right Side of Navbar */
.nav-right {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 10px; /* Add spacing between buttons */
}

/* Navigation Button Styling */
.nav-btn {
    background-color: #F5E071;
    padding: 8px 12px;
    border-radius: 50px;
    text-decoration: none;
    color: black;
    font-weight: bold;
    transition: background-color 0.3s ease;
    font-size: 0.9rem;
}

.nav-btn:hover {
    background-color: #e6d165;
}

        th {
            background-color: #F5E071;
        }@media (max-width: 768px) {
    .navbar {
        flex-wrap: wrap; /* Allow items to wrap to the next line */
        padding: 10px; /* Adjust padding for smaller screens */
    }
    .content {
 margin-top: 50px;
    }

    .nav-left {

        justify-content: center; /* Center-align the site name/logo */
    }

    .nav-right {
        flex: 1; /* Take full width on mobile */
        justify-content: center; /* Center-align buttons */
        flex-wrap: wrap; /* Wrap buttons to the next line if needed */
        gap: 5px; /* Reduce spacing between buttons */
        margin-top: 10px; /* Add spacing from the site name/logo */
    }

    .nav-btn {
        font-size: 0.8rem; /* Adjust button size for smaller screens */
        padding: 6px 10px; /* Adjust padding for smaller buttons */
    }

    .site-logo {
        width: 24px; /* Smaller logo size for mobile */
    }

    .site-name {
        font-size: 20px; /* Adjust font size for the site name */
    }
}



        /* Content Container */


        /* Greeting Message */
        .greeting {
    font-size: 2rem;
    color: #333333;
    text-align: center;
    width: 100%; /* Only take up as much space as the content */
    margin: 0 auto; /* Horizontally center */
    background-color: white;
    padding: 5px;
    border: none;
    border-radius: 20px;
}

    .centered {
        transform: translateY(0); /* Center the section */
    }

    .moved-down {
        transform: translateY(50%); /* Move the section down */
    }

        /* Balance Section (Home) */
        .balance-section {
            background-color: #ffffff;
            padding: 20px 30px;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            width: 100%;

            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .balance-section.hidden {
            opacity: 0;
            transform: scale(0.95);
            pointer-events: none;
        }

        .balance-section h3 {
            font-size: 1.5rem;
            color: #555555;
            margin-bottom: 10px;
        }

        .balance-amount {
            font-size: 2.5rem;
            color: #333333;
            margin-bottom: 20px;
        }

        /* Due Date and Amount After Due */
        .due-info {
            font-size: 1.1rem;
            color: #555555;
            margin-bottom: 10px;
        }

        .due-info strong {
            color: #333333;
        }

        /* Pay Now Button */
        .pay-now-btn {
            background-color: #F5E071;
            padding: 10px 20px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: bold;
            color: black;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .pay-now-btn:hover {
            background-color: #e6d165;
        }

        /* Profile Section */
        .profile-section {
            background-color: #ffffff;
            padding: 20px 30px;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            width: 100%;
            max-width: 400px;
            display: none; /* Hidden by default */
            transition: opacity 0.3s ease, transform 0.3s ease;


        }

        .profile-section.active {
            display: block;
            opacity: 1;
            transform: scale(1);
            transform: translateY(-300px);
        }

        .profile-section h3 {
            font-size: 1.5rem;
            color: #555555;
            margin-bottom: 10px;
        }

        .profile-info {
            margin-bottom: 20px;
            color: #333333;
            text-align: left;
        }

        .profile-info p {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        /* Profile Buttons */
        .profile-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .update-btn, .logout-btn {
            background-color: #F5E071;
            padding: 10px 20px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: bold;
            color: black;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
            width: 100%;
        }

        .update-btn:hover, .logout-btn:hover {
            background-color: #e6d165;
        }

        /* Bottom Navbar container */
        .bottom-navbar {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: rgba(248, 249, 250, 0.9);
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 10px 0;
            box-shadow: 0 -2px 4px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        /* Bottom Navbar Buttons */
        .bottom-nav-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: #333333;
            transition: color 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .bottom-nav-btn:hover, .bottom-nav-btn:focus {
            color: #F5E071;
            outline: none;
        }

        /* Icon Styles (Using Unicode characters as placeholders) */
        .bottom-nav-btn .icon {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            /* Adjust Navbar Layout */
            .navbar {
                justify-content: space-between;
            }



            .nav-center {
                text-align: left;
            }

            .nav-right {
                text-align: right;
            }

            /* Adjust Greeting Font Size */

            /* Adjust Balance Section */
            .balance-section, .profile-section {
                padding: 15px 20px;
            }

            .balance-amount {
                font-size: 2rem;
            }

            .pay-now-btn, .update-btn, .logout-btn {
                font-size: 0.9rem;
                padding: 8px 16px;
            }

            /* Adjust Bottom Navbar Icons for smaller screens */
            .bottom-nav-btn .icon {
                font-size: 1.2rem;
            }
        }

        /* Focus States for Accessibility */
        .pay-now-btn:focus,
        .update-btn:focus,
        .logout-btn:focus,
        .site-name:focus,
        .bottom-nav-btn:focus,
        .nav-btn:focus {
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


.profile-photo {
    width: 110px;
    height: 110px;
    border-radius: 50%; /* Makes the image circular */
    object-fit: cover; /* Ensures the image fits nicely */
}
/* Mobile styling - when the screen width is 600px or less */
@media (max-width: 600px) {
    .greeting {
        flex-direction: column; /* Stack the profile photo on top of the text */
        align-items: center; /* Align items to the start of the container */
        gap: 5px; /* Reduce gap for mobile */
    }

    .profile-photo {
        margin-bottom: 5px; /* Add a small margin between the image and text */
    }
}
/* Additional Content Styling */
.content p {
            text-align: center;
            font-size: 1.2rem;
            color: #555555;
            margin-top: 20px;
        }

        /* Loan Application Box */
        .loan-box {
            border-radius: 20px;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 40px auto;
        }

        .loan-box h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
        }

        .slider-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }

        #loan-amount {
            margin-bottom: 10px;
            font-size: 1.2rem;
            font-weight: bold;
        }

        #loan-slider {
            width: 100%;
        }

        .installment-buttons {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 20px;
        }

        .installment-btn {
            flex: 1;
            background-color: #f0f0f0;
            border: none;
            border-radius: 50px;
            padding: 10px 0;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-family: 'Open Sans', sans-serif;
            font-size: 1rem;
        }

        .installment-btn.active {
            background-color: #F5E071;
        }

        .installment-btn:hover {
            background-color: #e0e0e0;
        }

        .loan-result {
            text-align: center;
            margin-bottom: 20px;
        }

        .loan-result p {
            font-size: 1.2rem;
            color: #333333;
        }

        /* Apply Now Button */
        .apply-btn {
            display: block;
            text-align: center;
            background-color: #F5E071;
            padding: 10px 15px;
            border-radius: 50px;
            text-decoration: none;
            color: black;
            font-weight: bold;
            transition: background-color 0.3s ease;
            font-family: 'Open Sans', sans-serif;
            margin: 0 auto;
            width: 130px;
            margin-bottom: 15px;
        }

        .apply-btn:hover {
            background-color: #e6d165;
        }
    .main-content {
    display: flex;
    justify-content: space-between; /* Ensures items are spaced out */
    gap: 20px; /* Space between the columns */
    align-items: center; /* Vertical centering */
    height: 100vh; /
    flex-wrap: nowrap;
}

.content {
    flex: 1;
    width: 100%; /* Adjust width as needed */
    padding-top: 10px;
}

.loan-content {
    flex: 1;
    min-width: 300px; /* Adjust width as needed */
}
.content-wrapper {
    display: flex;
    margin: 0 auto; 
    align-items: center;
    flex: 1;
    min-width: 300px;
    width: 100%;
    height: 100vh; /* Adjust as needed */
    padding: 35px;

}

.greeting-container {
    display: flex;
    align-items: center; /* Vertically centers the image and text */
    background-color: white;
    padding: 10px; /* Adds space around the content */
    border-radius: 20px;
    width: 100%; /* Width adjusts to content */
    margin: 0 auto; /* Center horizontally */
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); /* Optional: Add a subtle shadow */
    margin-bottom: 10px;
    justify-content: space-between;
}

.profile-photo {
    width: 80px; /* Set the size for the profile image */
    height: 80px;
    border-radius: 50%; /* Make the image circular */
    margin-right: 10px; /* Space between photo and text */
}

.greeting-container span {
    font-size: 1.5rem; /* Adjust font size */
    color: #333333;
    font-weight: 600; /* Optional: Make the text a bit bolder */
}


.dot-menu {
    display: flex;
    flex-direction: column;
    margin-left: auto; 
    justify-content: center;
    align-items: center;
    margin-left: 10px;
    cursor: pointer;
    text-decoration: none;
}

.dot {
    width: 4px;
    height: 4px;
    background-color: #333;
    border-radius: 50%;
    margin: 2px 0;
}
.loan-history {
    display: flex;
    flex-direction: column;
    align-items: center;
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    margin: 20px auto;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    width: 90%;
    max-width: 1200px;
}

.loan-history h2 {
    text-align: center;
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
}

/* Table for Desktop View */
.loan-history table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.loan-history th, .loan-history td {
    padding: 12px;
    border: 1px solid #ddd;
    font-size: 14px;
    text-align: left;
}

.loan-history th {
    background-color: #f4f4f4;
    font-weight: bold;
    color: #555;
    text-align: center;
}

/* Row Coloring */
.loan-history tr:nth-child(even) {
    background-color: #f9f9f9;
}

.loan-history tr:hover {
    background-color: #f1f1f1;
    transition: background-color 0.3s;
}

.loan-history a.pay-now-btn {
    color: black;
    text-decoration: none;
    font-weight: bold;
    cursor: pointer;
}

.loan-history a.pay-now-btn:hover {
    text-decoration: underline;
    color: #0056b3;
}

#loantext {
    text-align: center;
}
.payment-details-container {
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin: 20px 0;
}

.payment-details-container h2 {
    font-size: 24px;
    color: #333;
    margin-bottom: 15px;
}

.payment-detail-card {
    padding: 15px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 15px;
}

.payment-detail-card p {
    font-size: 16px;
    margin: 5px 0;
    color: #555;
}

.payment-detail-card strong {
    color: #333;
}

.nav-right {
    display: flex; /* Align the buttons horizontally */
    gap: 10px; /* Add space between each item */
    align-items: center; /* Vertically center the items */
}
.terms-section {
    margin-top: 15px;
    font-size: 14px;
    color: #333;
}

.terms-section label {
    cursor: pointer;
}

.terms-section a {
    color: #007bff;
    text-decoration: none;
}

.terms-section a:hover {
    text-decoration: underline;
}
/* Mobile view */
@media (max-width: 768px) {
    .main-content {
        flex-direction: column; /* Stack the content vertically */
        gap: 20px; /* Add space between stacked items */
    }

    .loan-content, .loan-history {
        width: 100%; /* Full width for mobile */
        margin: 0 auto; /* Center horizontally */
    }

    .loan-content {
        margin-top: 20px; /* Add spacing between sections */
    }

    .loan-history {
        margin-top: 300px; /* Add spacing between sections */
        padding: 15px; /* Add padding for better spacing */
    }
}
/* Mobile view styling */
@media (max-width: 768px) {

    .loan-history tr {
        display: flex;
        flex-direction: column;
        border: 1px solid #ddd;
        margin-bottom: 10px;
        border-radius: 8px;
        padding: 10px;
        background-color: #fff;
    }

    .loan-history th {
        display: none; /* Hide the header row at the top */
    }

    .loan-history td {
        display: flex;
        justify-content: space-between;
        padding: 10px 5px;
        font-size: 14px;
        border: none;
        position: relative;
    }

    .loan-history td::before {
        content: attr(data-label); /* Dynamically show header as label */
        font-weight: bold;
        color: #555;
        flex-basis: 40%;
        text-align: left;
        margin-right: 10px;
    }

    .loan-history a.pay-now-btn {
        font-size: 14px;
        padding: 5px 10px;
        border: 1px solid black;
        border-radius: 5px;
        text-align: center;
    }

    .loan-history a.pay-now-btn:hover {
        background-color: black;
        color: #fff;
    }
}

    </style>
</head>
<body>
    <!-- Background Circles -->
    <div class="background-circle circle1"></div>
    <div class="background-circle circle2"></div>
    <div class="background-circle circle3"></div>

    <!-- Top Navbar -->
    <nav class="navbar">
        <!-- Left Side of Navbar -->
        <!-- Center of Navbar -->
        <div class="nav-left">
    <a href="Home.html" class="site-name">
        <img src="logo.jpg" alt="San Diego Logo" class="site-logo">
        San Diego
    </a>
</div>

        <!-- Right Side of Navbar -->
        <div class="nav-right">
        <a href="message.php" class="nav-btn" id= "msg-btn"> Messages
    </a>
            <a href="Home.html" class="nav-btn">Log Out</a> <!-- Updated to point to logout.php -->
        </div>
    </nav>

<div class="main-content">
    <!-- Main Content (left) -->
     <div class="content-wrapper">
    <div class="content">
        <div class="greeting-container">
    <img src="profilephoto.jpg" alt="Profile Photo" class="profile-photo">
    <span>  Kumusta <?php echo htmlspecialchars($_SESSION["username"]); ?> ! </span>
    <a href="updateprofile.php" class="dot-menu">
        <span class="dot"></span>
        <span class="dot"></span>
        <span class="dot"></span>
    </a>
  
</div>
        <div class="balance-section moved down" id="balance-section">
            <h3>Your Balance</h3>
            <div class="balance-amount">₱<?php echo $formatted_balance; ?></div>
            <div class="due-info">
    <p><strong>Amount After Due:</strong> ₱<?php echo number_format($amountAfterDue, 2); ?></p>
</div>
            <a href="pay.php" class="pay-now-btn">Full Pay</a>
            <a href="partialpay.php" class="pay-now-btn">Partial Pay</a>
        </div>
    </div>
    </div>

    <!-- Loan Content (right) -->
    <div class="loan-content">
    <div class="loan-box">
        <h2>Loan Amount</h2>
        <div class="slider-container">
            <span id="loan-amount">₱0.00</span>
            <input type="range" id="loan-slider" min="1000" max="20000" step="500" value="1000">
        </div>
        <h2>Installment</h2>
        <div class="installment-buttons">
            <button class="installment-btn active" data-months="1">1 Month</button>
            <button class="installment-btn" data-months="2">2 Months</button>
            <button class="installment-btn" data-months="3">3 Months</button>
            <button class="installment-btn" data-months="4">4 Months</button>
        </div>
        <div class="loan-result">
            <p>Total Amount: <span id="total-amount">₱0.00</span></p>
            <p>Daily Payment: <span id="daily-payment">₱0.00</span></p>
    <p>Weekly Payment: <span id="weekly-payment">₱0.00</span></p>
    <div class="terms-section">
    <input type="checkbox" id="terms-checkbox">
    <label for="terms-checkbox">
        I agree to the <a href="agreement.html" target="_blank">Terms and Conditions</a>.
    </label>
</div>

        </div>
        <a id="apply-loan-btn" class="apply-btn">APPLY LOAN</a>
    </div>
</div>

        <!-- Profile Section -->
        <div class="profile-section hidden" id="profile-section">
            <h3>Profile Information</h3>
            <div class="profile-info">
                <p><strong>Name:</strong> user</p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user_email); ?></p>
            </div>
            <div class="profile-buttons">
                <a href="Updateinfo.php" class="update-btn">Update Account Info</a>
                <a href="Home.html" class="logout-btn">Log Out</a> <!-- Updated to point to logout.php -->
            </div>
        </div>
    </div>
    <br><br>
    <div class="loan-history">
    <h2 id="loantext">Loan History</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Loan Amount</th>
                    <th>Total Amount</th>
                    <th>Months</th>
                    <th>Payment Method</th>
                    <th>Payment Date</th>
                    <th>First Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Loan Amount"><?php echo htmlspecialchars($row['loan_amount']); ?></td>
                        <td data-label="Total Amount"><?php echo htmlspecialchars($row['total_amount']); ?></td>
                        <td data-label="Months"><?php echo htmlspecialchars($row['months']); ?></td>
                        <td data-label="Payment Method"><?php echo htmlspecialchars($row['payment_method']); ?></td>
                        <td data-label="Payment Date"><?php echo htmlspecialchars($row['payment_date']); ?></td>
                        <td data-label="First Due Date"><?php echo htmlspecialchars($row['first_due_date']); ?></td>
                        <td data-label="Status"><?php echo htmlspecialchars($row['status']); ?></td>
                        <td data-label="Action">
                            <a href="moreinfo.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="pay-now-btn">View More Info</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No loan history found.</p>
    <?php endif; ?>
</div>


<div class="payment-details-container" style="width: 100%; margin: 20px 0;">
    <h2 style="text-align: center; margin-bottom: 20px;">Your Payment Details</h2>
    <?php
    if ($user_payment_result->num_rows > 0) {
        echo "<table style='width: 100%; border-collapse: collapse; font-size: 18px;'>
                <thead>
                    <tr style='background-color: #f2f2f2; text-align: left;'>
                        <th style='padding: 10px; border: 1px solid #ddd;'>Payment Mode</th>
                        <th style='padding: 10px; border: 1px solid #ddd;'>Date</th>
                        <th style='padding: 10px; border: 1px solid #ddd;'>Status</th>
                    </tr>
                </thead>
                <tbody>";
        while ($payment = $user_payment_result->fetch_assoc()) {
            echo "<tr>
                    <td style='padding: 10px; border: 1px solid #ddd;'>{$payment['payment_mode']}</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>" . date('F j, Y', strtotime($payment['date'])) . "</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>{$payment['status']}</td>
                  </tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p style='text-align: center; font-size: 18px;'>No pending or accepted payment records found.</p>";
    }
    ?>
</div>



<?php
// Close the statement and connection
$stmt->close();
$conn->close();
?>




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
            <a href="Mainpagetagalog.php" class="flag-link" aria-label="Philippines">
                <img src="philippines.png" alt="Philippines Flag" class="flag-icon">
            </a>
        </div>

    <!-- JavaScript for Navbar Functionality -->
    <script>
document.addEventListener("DOMContentLoaded", function() {
    const loanSlider = document.getElementById('loan-slider');
    const loanAmountDisplay = document.getElementById('loan-amount');
    const totalAmountDisplay = document.getElementById('total-amount');
    const dailyPaymentDisplay = document.getElementById('daily-payment');
    const weeklyPaymentDisplay = document.getElementById('weekly-payment');
    const installmentButtons = document.querySelectorAll('.installment-btn');
    const applyLoanBtn = document.getElementById('apply-loan-btn');
    const termsCheckbox = document.getElementById('terms-checkbox');

    let selectedMonths = 1; // Default selection
    let interestRates = {
        1: 0.03, // 3% for 1 month
        2: 0.05, // 5% for 2 months
        3: 0.08, // 8% for 3 months
        4: 0.10  // 10% for 4 months
    };

    // Function to format number as currency
    function formatCurrency(amount) {
        return amount.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
    }

    // Update loan amount display and calculate total
    function updateLoanAmount() {
        const loanValue = parseInt(loanSlider.value);
        loanAmountDisplay.textContent = formatCurrency(loanValue);

        // Use the selected month's interest rate
        const interestRate = interestRates[selectedMonths] || 0;
        const totalAmount = loanValue + (loanValue * interestRate);
        totalAmountDisplay.textContent = formatCurrency(totalAmount);

        // Calculate daily and weekly payments
        const dailyPayment = totalAmount / (30 * selectedMonths);
        const weeklyPayment = totalAmount / (4 * selectedMonths);

        // Update daily and weekly payment displays
        dailyPaymentDisplay.textContent = formatCurrency(dailyPayment);
        weeklyPaymentDisplay.textContent = formatCurrency(weeklyPayment);
    }

    // Disable/Enable Apply Loan button based on checkbox state
    function toggleApplyLoanButton() {
        if (termsCheckbox.checked) {
            applyLoanBtn.removeAttribute("disabled");
            applyLoanBtn.classList.remove("disabled"); // Optional: if you style disabled buttons
        } else {
            applyLoanBtn.setAttribute("disabled", "true");
            applyLoanBtn.classList.add("disabled"); // Optional: if you style disabled buttons
        }
    }

    // Initialize loan amount on page load
    updateLoanAmount();
    toggleApplyLoanButton(); // Ensure button is disabled on load if checkbox is unchecked

    // Event listener for slider
    loanSlider.addEventListener('input', updateLoanAmount);

    // Event listeners for installment buttons
    installmentButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove 'active' class from all buttons
            installmentButtons.forEach(btn => btn.classList.remove('active'));
            // Add 'active' class to the clicked button
            button.classList.add('active');
            // Update selected months
            selectedMonths = parseInt(button.getAttribute('data-months'));
            // Recalculate total amount
            updateLoanAmount();
        });
    });

    // Event listener for terms checkbox
    termsCheckbox.addEventListener('change', toggleApplyLoanButton);

    // Event listener for the APPLY LOAN button
    applyLoanBtn.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default navigation

        // Check again to prevent submission if checkbox is unchecked
        if (!termsCheckbox.checked) {
            alert("Please agree to the Terms and Conditions to proceed.");
            return;
        }

        // Get the loan amount, total amount, daily, and weekly payments
        const loanAmount = parseFloat(loanAmountDisplay.textContent.replace(/[^0-9.-]+/g, ""));
        const totalAmount = parseFloat(totalAmountDisplay.textContent.replace(/[^0-9.-]+/g, ""));
        const dailyPayment = parseFloat(dailyPaymentDisplay.textContent.replace(/[^0-9.-]+/g, ""));
        const weeklyPayment = parseFloat(weeklyPaymentDisplay.textContent.replace(/[^0-9.-]+/g, ""));

        // Redirect to confirmloan.php with parameters
        window.location.href = `confirmloan.php?loanAmount=${loanAmount}&totalAmount=${totalAmount}&months=${selectedMonths}&dailyPayment=${dailyPayment}&weeklyPayment=${weeklyPayment}`;
    });
});

</script>

</body>
</html>
