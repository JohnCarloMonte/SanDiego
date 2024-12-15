<?php
session_start();



include('connection.php');

// Fetch pending loans for display
$query = "SELECT id, username,mobile_number, total_amount, months, payment_method, first_due_date FROM loans WHERE status = 'pending'";
$result = $conn->query($query);

$pendingLoans = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pendingLoans[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            padding: 10px 20px;
            background-color: rgba(248, 249, 250, 0.9);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .site-name {
            font-size: 24px;
            font-weight: bold;
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
        }
        .nav-btn:hover {
            background-color: #e6d165;
        }
        /* Dashboard Styles */
        .container {
            padding: 100px 20px 20px 20px;
        }
        .dashboard {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .card {
            flex: 1;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 300px;
            text-align: center;
        }
        .card h2 {
            font-size: 1.5em;
        }
        /* Loan Status Styles */
        .loan-status {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .loan-status p {
            margin: 5px 0;
            font-size: 1em;
            color: #333;
        }
        .card-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            margin: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .card-button:hover {
            background-color: #45a049;
        }
        .reject-button {
            background-color: #f44336;
        }
        .reject-button:hover {
            background-color: #e53935;
        }
    </style>
</head>
<body>
<nav class="navbar">
    <div><a href="Mainpage.php" class="nav-btn">Back to Home</a></div>
    <div><a href="Home.php" class="site-name">San Diego</a></div>
</nav>

<div class="container">
    <h1>Admin Dashboard</h1>
    <div class="dashboard">
        <!-- Dashboard Cards Here -->
        <div class="card">
            <h2>Payments Today</h2>
            <p><strong>View Payments</strong></p>
            <button class="card-button">View Payments</button>
        </div>
        <div class="card">
            <h2>Users</h2>
            <p>Number of Users: 5</p>
            <button class="card-button">View Users</button>
        </div>
        <div class="card">
            <h2>Loans</h2>
            <p>Loans with Balance: 3</p>
            <button class="card-button">View Loans</button>
        </div>
        <div class="card">
            <h2>Total Receivable</h2>
            <p>Total: ₱20,000</p>
        </div>
    </div>

    <h2>Pending Loans</h2>
    <?php if (!empty($pendingLoans)): ?>
        <?php foreach ($pendingLoans as $loan): ?>
            <div class="loan-status">
                <p><strong>Username:</strong> <?php echo $loan['username']; ?></p>
                <p><strong>Mobile_number:</strong> <?php echo $loan['mobile_number']; ?></p>
                <p>Loan Amount: ₱<?php echo number_format($loan['total_amount'], 2); ?></p>
                <p>Duration: <?php echo $loan['months']; ?> month(s)</p>
                <p>Payment Method: <?php echo ucfirst($loan['payment_method']); ?></p>
                <?php if ($loan['payment_method'] === 'cash'): ?>
                    <p>First Due Date: <?php echo date('F j, Y', strtotime($loan['first_due_date'])); ?></p>
                <?php endif; ?>
                <p>Status: <span style="color: orange;">Pending</span></p>
                <button class="card-button" onclick="updateLoanStatus('<?php echo $loan['id']; ?>', 'accepted')">Accept</button>
                <button class="card-button reject-button" onclick="updateLoanStatus('<?php echo $loan['id']; ?>', 'rejected')">Reject</button>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No pending loans at the moment.</p>
    <?php endif; ?>
</div>

<script>
function updateLoanStatus(loanId, status) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "update_loan_status.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            alert(xhr.responseText);
            location.reload();
        }
    };
    xhr.send("loan_id=" + loanId + "&status=" + status);
}
</script>

</body>
</html>
