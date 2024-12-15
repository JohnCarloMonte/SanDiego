<?php
// Start session to check if admin is logged in
session_start();

// Placeholder for checking if admin is logged in
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: login.php');
//     exit();
// }

// Include database connection file
require 'connection.php';

// Fetch data for dashboard summary
// Today's Payments, Users, Loans, and Total Receivable calculations
$paymentsToday = 0; // Placeholder for now
$usersCount = 0;
$loansCount = 0;
$totalReceivable = 0;

// Count registered users
$result = $conn->query("SELECT COUNT(*) AS count FROM users");
if ($result) {
    $usersCount = $result->fetch_assoc()['count'];
}

// Count users with a balance (Loans)
$result = $conn->query("SELECT COUNT(*) AS count FROM users WHERE balance > 0");
if ($result) {
    $loansCount = $result->fetch_assoc()['count'];
}

// Calculate total receivable balance
$result = $conn->query("SELECT SUM(balance) AS total FROM users");
if ($result) {
    $totalReceivable = $result->fetch_assoc()['total'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - San Diego</title>
    <style>
        /* Basic Styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
        }
        .site-name {
            font-size: 24px;
            font-weight: bold;
        }
        .logout-btn {
            background-color: #e6d165;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            color: black;
            font-weight: bold;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }
        /* Sidebar */
        .sidebar {
            width: 200px;
            height: 100vh;
            background-color: #333;
            color: white;
            padding-top: 70px;
            position: fixed;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            cursor: pointer;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #444;
        }
        /* Main Content */
        .main-content {
            margin-left: 200px;
            padding: 80px 20px;
            width: calc(100% - 200px);
        }
        /* Dashboard Squares */
        .dashboard {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .dashboard-card {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: calc(25% - 20px);
            padding: 20px;
            text-align: center;
        }
        .dashboard-card h3 {
            font-size: 1.2em;
            margin: 10px 0;
        }
        .dashboard-card .count {
            font-size: 2em;
            margin: 10px 0;
        }
        .view-btn {
            background-color: #F5E071;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        /* Calendar */
        .calendar {
            margin-top: 10px;
            cursor: pointer;
        }
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
                /* Website name */
                .site-name {
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            color: black;
        }
        

    </style>
</head>
<body>


<nav class="navbar">
        <!-- Left Side of Navbar -->
        <div class="nav-left">
            <a href="Home.html" class="nav-btn">Back to Home</a>
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

<!-- Sidebar -->
<div class="sidebar">
    <a href="#home" class="active">Home</a>
    <a href="#loans">Loans</a>
    <a href="#payments">Payments</a>
    <a href="#users">Users</a>
    <a href="#loanplan">Loan Plan</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Home Content -->
    <div id="home-content">
        <h2>Welcome, Admin!</h2>
        <div class="dashboard">
            <!-- Payments Today Card -->
            <div class="dashboard-card">
                <h3>Payments Today</h3>
                <div class="count"><?php echo $paymentsToday; ?></div>
                <div class="calendar">📅</div>
                <button class="view-btn" onclick="showPayments()">View Payments</button>
            </div>
            <!-- Users Card -->
            <div class="dashboard-card">
                <h3>Users</h3>
                <div class="count"><?php echo $usersCount; ?></div>
                <button class="view-btn" onclick="showUsers()">View Users</button>
            </div>
            <!-- Loans Card -->
            <div class="dashboard-card">
                <h3>Loans</h3>
                <div class="count"><?php echo $loansCount; ?></div>
                <button class="view-btn" onclick="showLoans()">View Loans</button>
            </div>
            <!-- Total Receivable Card -->
            <div class="dashboard-card">
                <h3>Total Receivable</h3>
                <div class="count"><?php echo $totalReceivable; ?></div>
            </div>
        </div>
    </div>
</div>

<script>
    // Navigation Handlers
    function showPayments() {
        window.location.href = '#payments';
    }
    function showUsers() {
        window.location.href = '#users';
    }
    function showLoans() {
        window.location.href = '#loans';
    }
</script>

</body>
</html>
