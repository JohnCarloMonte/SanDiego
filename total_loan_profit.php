<?php
// Establish database connection
$servername = "localhost";
$username = "root"; // Adjust your username
$password = ""; // Adjust your password
$dbname = "sandiegodatabase"; // Ensure this matches your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to calculate total loan amount and profit
$total_loan_query = "SELECT SUM(loan_amount) as total_loan, SUM(total_amount - loan_amount) as profit FROM loans";
$total_loan_result = $conn->query($total_loan_query);
$total_loan_data = $total_loan_result->fetch_assoc();

$total_loan_amount = $total_loan_data['total_loan'];
$profit = $total_loan_data['profit'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total Loan and Profit</title>
    <style>
        /* Styles for the display containers */
        .totals-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        .totals-container div {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 200px;
            text-align: center;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <h1>Total Loan Amount and Profit</h1>
    
    <!-- Displaying Total Loan Amount and Profit -->
    <div class="totals-container">
        <div id="total-loan-amount">Total Loan Amount: <?= $total_loan_amount ?></div>
        <div id="profit">Profit: <?= $profit ?></div>
    </div>

</body>
</html>

<?php
// Close database connection
$conn->close();
?>
