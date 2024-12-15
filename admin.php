<?php
session_start();
include 'connection.php';

// Display success or error messages
if (isset($_SESSION['message'])) {
    echo "<div class='message'>{$_SESSION['message']}</div>";
    unset($_SESSION['message']);
}

if (!isset($_SESSION['admin_username'])) {
    header('Location: admin_login.php');
    exit();
}

// Fetch total loan amount and profit
$total_loan_amount = 0;
$profit = 0;

try {
    $loan_query = $conn->prepare("SELECT SUM(loan_amount) AS total_loan, SUM(total_amount - loan_amount) AS profit FROM loans");
    $loan_query->execute();
    $loan_query->bind_result($total_loan_amount, $profit);
    $loan_query->fetch();
    $loan_query->close();
} catch (Exception $e) {
    $_SESSION['message'] = 'Error fetching loan data: ' . $e->getMessage();
}

// Fetch pending loans
$pendingLoans = [];
try {
    $pending_query = $conn->prepare("SELECT * FROM loans WHERE status = 'pending' ORDER BY payment_date DESC");
    $pending_query->execute();
    $result = $pending_query->get_result();

    // Fetch loans and store them in $pendingLoans array
    while ($loan = $result->fetch_assoc()) {
        $pendingLoans[] = $loan;
    }
    $pending_query->close();
} catch (Exception $e) {
    $_SESSION['message'] = 'Error fetching pending loan data: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deduct'])) {
    $userId = intval($_POST['user_id']);
    $deductAmount = floatval($_POST['deduct_amount']);

    // Fetch the current balance
    $result = $conn->query("SELECT balance FROM users WHERE id = $userId");

    if ($result) {
        $user = $result->fetch_assoc();
        $currentBalance = $user['balance'];

        if ($deductAmount > 0 && $deductAmount <= $currentBalance) {
            $newBalance = $currentBalance - $deductAmount;

            // Update user balance
            $conn->query("UPDATE users SET balance = $newBalance WHERE id = $userId");

            // Log the transaction
            $conn->query("INSERT INTO transactions (user_id, amount, remaining_balance) VALUES ($userId, $deductAmount, $newBalance)");

            $_SESSION['message'] = "Deduction successful!";
            header("Location: admin.php");
            exit();
        } else {
            $_SESSION['message'] = "Invalid deduction amount.";
        }
    } else {
        $_SESSION['message'] = "User not found.";
    }
}

function displayLoansByStatus($conn, $status) {
    // Prepare the query to fetch loans by status
    $loan_query = $conn->prepare("SELECT * FROM loans WHERE status = ? ORDER BY payment_date DESC");
    $loan_query->bind_param("s", $status);
    $loan_query->execute();
    $result = $loan_query->get_result();

    // Fetch and display the loans
    while ($loan = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$loan['username']}</td>
                <td>₱" . number_format($loan['total_amount'], 2) . "</td>
                <td>" . date('F j, Y', strtotime($loan['payment_date'])) . "</td>
              </tr>";
    }
    $loan_query->close();
}

$stmt = $conn->prepare("SELECT id, username, amount, created_at FROM payment_requests WHERE status = 'pending'");
$stmt->execute();
$result = $stmt->get_result();
$requests = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle payment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accept_payment'])) {
        // Accept payment logic
        $payment_id = intval($_POST['payment_id']);
        
        // Prepare the query to update the payment status to 'accepted'
        $stmt = $conn->prepare("UPDATE payments SET status = 'accepted' WHERE id = ?");
        $stmt->bind_param("i", $payment_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Payment accepted successfully!';
        } else {
            $_SESSION['message'] = 'Error accepting payment: ' . $stmt->error;
        }
        $stmt->close();
        header("Location: admin.php");
        exit();
    }

    if (isset($_POST['delete_payment'])) {
        // Archive payment logic
        $payment_id = intval($_POST['payment_id']);
        $stmt = $conn->prepare("INSERT INTO payment_archive (name, mobile, date, payment_mode, status)
                                 SELECT name, mobile, date, payment_mode, status FROM payments WHERE id = ?");
        $stmt->bind_param("i", $payment_id);
        if ($stmt->execute()) {
            $delete_stmt = $conn->prepare("DELETE FROM payments WHERE id = ?");
            $delete_stmt->bind_param("i", $payment_id);
            if ($delete_stmt->execute()) {
                $_SESSION['message'] = 'Payment archived and deleted successfully!';
            } else {
                $_SESSION['message'] = 'Error deleting payment: ' . $delete_stmt->error;
            }
            $delete_stmt->close();
        } else {
            $_SESSION['message'] = 'Error archiving payment: ' . $stmt->error;
        }
        $stmt->close();
        header("Location: admin.php");
        exit();
    }
}

// Fetch all users data for display in the admin panel
$users = [];
try {
    $user_query = $conn->prepare("SELECT id, first_name, last_name, username, email, balance, source_of_income, average_income, gender, province, city, barangay, role,id_upload_path, selfie FROM users");
    $user_query->execute();
    $result = $user_query->get_result();

    // Fetch users and store them in $users array
    while ($user = $result->fetch_assoc()) {
        $users[] = $user;
    }
    $user_query->close();
} catch (Exception $e) {
    $_SESSION['message'] = 'Error fetching user data: ' . $e->getMessage();
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
        /* Add your existing styles here */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* Navbar Styles */
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .nav-left, .nav-center, .nav-right {
            flex: 1;
        }

        .site-name {
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
            color: #333;
        }

        .nav-left {
            flex: 1;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px; /* Space between buttons */
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

        /* Container and Section Styles */
        .container {
            display: flex;
            gap: 20px;
            margin-top: 80px;
            padding: 20px;
        }

        .section {
            border: 1px solid #ddd;
            padding: 20px;
            width: 50%;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .section:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        h2 {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #f5e071;
            padding-bottom: 5px;
        }

        .loan-item, .user-item {
            padding: 15px;
            margin: 10px 0;
            background-color: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
        }

        .loan-item:hover, .user-item:hover {
            background-color: #f5f5f5;
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .buttons button {
            padding: 8px 12px;
            font-size: 14px;
            background-color: #f5e071;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .buttons button:hover {
            background-color: #e6d165;
        }

        /* Totals Section */
        #totals div {
            width: 100%;
            font-size: 16px;
            margin: 10px 0;
            color: #333;
            display: flex;
            justify-content: space-between;
        }

        #totals span {
            font-weight: bold;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                padding: 10px;
            }

            .section {
                width: 100%;
            }
        }
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
        .amount-adjustment {
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.amount-adjustment input {
    width: 120px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.amount-adjustment button {
    padding: 8px 12px;
    background-color: #f5e071;
    color: black;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
}



table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        thead {
            background-color: #f5e071;
            color: black;
        }

        th, td {
            padding: 10px 15px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            font-size: 16px;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tbody tr:hover {
            background-color: #f1f1f1;
        }

        button {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        button[name="update_status"][value="Accepted"] {
            background-color: #f5e071;
            color: black;
        }

        button[name="update_status"][value="Accepted"]:hover {
            background-color: #45a049;
        }

        button[name="update_status"][value="Declined"] {
            background-color: #f44336;
            color: white;
        }

        button[name="update_status"][value="Declined"]:hover {
            background-color: #d32f2f;
        }

       
        form {
            margin: 0;
            display: inline;
        }
        .navbar {
            display: flex;
            justify-content: space-between; /* Space between left, center, and right */
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
            display: flex;
            align-items: center;
            gap: 10px; /* Space between buttons */
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
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        /* Website name */
        .site-name {
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            color: black;
        }

        th {
            background-color: #F5E071;
        }

        /* Navigation Button Styling */
        .nav-btn {
            background-color: #F5E071; /* Same as login button */
            padding: 8px 12px;
            border-radius: 50px;
            text-decoration: none;
            color: black;
            font-weight: bold;
            transition: background-color 0.3s ease;
            display: inline-block;
            font-size: 0.9rem;
            margin-right: 30px;
        }

        .nav-btn:hover {
            background-color: #e6d165;
        }


    </style>
</head>
<body>

<nav class="navbar">
        <!-- Left Side of Navbar -->
        <!-- Center of Navbar -->
        <div class="nav-left">
            <a href="Home.html" class="site-name">San Diego</a>
        </div>

        <!-- Right Side of Navbar -->
        <div class="nav-right">
        <a href="adminmessage.php" class="nav-btn" id= "msg-btn"> Messages
    </a>
           
        </div>
    </nav>

<div class="container">
    <!-- User Section -->
    <div class="section" id="users">
        <h2>Users</h2>
        <?php
// Fetch users and display their balance and transaction history
$user_result = $conn->query("SELECT * FROM users");

while ($user = $user_result->fetch_assoc()) {
    $userId = $user['id'];
    $transactions = $conn->query("SELECT * FROM transactions WHERE user_id = $userId ORDER BY transaction_date DESC");

    echo "<div class='user-item'>
    <p>
        <strong>{$user['username']}</strong> - Balance: ₱
        <span id='user-balance-{$user['id']}'>{$user['balance']}</span>
    </p>
    <div id='transaction-history-{$user['id']}'>";

    while ($transaction = $transactions->fetch_assoc()) {
        $date = date('Y-m-d H:i:s', strtotime($transaction['transaction_date']));
        $amount = number_format($transaction['amount'], 2);
        $remaining = number_format($transaction['remaining_balance'], 2);

        echo "<p>On $date: Deducted ₱$amount. Remaining balance: ₱$remaining.</p>";
    }

    echo "</div>
    <div class='buttons'>
        <button onclick='deleteUser({$user['id']})'>Delete</button>
        <button onclick='clearBalanceAndLoans({$user['id']})'>Clear Balance</button>
    </div>
    <div class='amount-adjustment'>
        <form method='POST'>
            <input type='hidden' name='user_id' value='{$user['id']}' />
            <input type='number' name='deduct_amount' placeholder='Enter amount' />
            <button type='submit' name='deduct'>Deduct</button>
        </form>
    </div>
</div>";
}
?>

    </div>

    <!-- Loan Section -->
    <div class="section" id="loans">
    <h2>Loans</h2>
    <div>
        <h3>Pending Loans</h3>
        <?php 
    if (!empty($pendingLoans)): 
        foreach ($pendingLoans as $loan):
            // Fetch user email related to the loan
            $user_result = $conn->query("SELECT email FROM users WHERE username = '{$loan['username']}'");
            $user = $user_result->fetch_assoc();
?>
            <div class="loan-status">
                <p><strong>Username:</strong> <?php echo $loan['username']; ?></p>
                <p><strong>Mobile number:</strong> <?php echo $user['email']; ?></p> <!-- Display user's email -->
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

        <h3>Accepted Loans</h3>
    <table class="loan-table">
        <thead>
            <tr>
                <th>Username</th>
                <th>Total Amount</th>
                <th>Loan Date</th>
            </tr>
        </thead>
        <tbody>
            <?php displayLoansByStatus($conn, 'accepted'); ?>
        </tbody>
    </table>
</div>

<div>
    <h3>Rejected Loans</h3>
    <table class="loan-table">
        <thead>
            <tr>
                <th>Username</th>
                <th>Total Amount</th>
                <th>Loan Date</th>
            </tr>
        </thead>
        <tbody>
            <?php displayLoansByStatus($conn, 'rejected'); ?>
        </tbody>
    </table>
    </div>

</div>



  
        

     

    <!-- Totals Section -->
    <div class="section" id="totals">
        <h2>Total</h2>
        <div>Total Loan Amount: <span id="total-loan-amount">₱<?= number_format($total_loan_amount, 2) ?></span></div>
        <div>Profit: <span id="profit">₱<?= number_format($profit, 2) ?></span></div>
    </div>
</div>
<h2>GCash Payments</h2>
<table border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            <th>Name</th>

            <th>Receipt</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Fetch GCash payments
        $gcash_query = "SELECT * FROM payments WHERE payment_mode = 'gcash' ORDER BY date DESC";
        $gcash_result = $conn->query($gcash_query);

        if ($gcash_result->num_rows > 0) {
            while ($payment = $gcash_result->fetch_assoc()) {
                echo "<tr>
                        <td>{$payment['name']}</td>
 
                        <td><a href='{$payment['receipt']}' target='_blank'>View Receipt</a></td>
                        <td>{$payment['status']}</td>
                        <td>
                            <form method='POST' action='accept_payment.php' style='display: inline; margin-right: 5px;'>
                                <input type='hidden' name='payment_id' value='{$payment['id']}'>
                                <button type='submit' style='background-color: green; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer;'>Accept</button>
                            </form>
                            <form method='POST' action='delete_payment.php' style='display: inline;'>
                                <input type='hidden' name='payment_id' value='{$payment['id']}'>
                                <button type='submit' style='background-color: red; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer;'>Delete</button>
                            </form>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No GCash payment requests found.</td></tr>";
        }
        ?>
    </tbody>
</table>

<h2>Payments</h2>
<table border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            <th>Name</th>
            
            <th>Mobile</th>
            <th>Balance</th>
            <th>Date</th>
            <th>Payment Mode</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Fetch other payments
        $other_query = "SELECT * FROM payments WHERE payment_mode != 'gcash' ORDER BY date DESC";
        $other_result = $conn->query($other_query);

        if ($other_result->num_rows > 0) {
            while ($payment = $other_result->fetch_assoc()) {
                echo "<tr>
                        <td>{$payment['name']}</td>
                        <td>{$payment['mobile']}</td>
                        <td>{$payment['balance']}</td>
                        <td>" . date('F j, Y', strtotime($payment['date'])) . "</td>
                        <td>{$payment['payment_mode']}</td>
                        <td>{$payment['status']}</td>
                        <td>
                            <form method='POST' action='admin.php' style='display: inline; margin-right: 5px;'>
                                <input type='hidden' name='payment_id' value='{$payment['id']}'>
                                <input type='hidden' name='accept_payment' value='1'>
                                <button type='submit' style='background-color: green; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer;'>Accept</button>
                            </form>
                            <form method='POST' action='admin.php' style='display: inline;'>
                                <input type='hidden' name='payment_id' value='{$payment['id']}'>
                                <button type='submit' name='delete_payment' style='background-color: red; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer;'>Delete</button>
                            </form>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No payment requests found.</td></tr>";
        }
        ?>
    </tbody>
</table>
<h1>All Users</h1>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Selfie</th>
            <th>ID Upload</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Balance</th>
            <th>Source of Income</th>
            <th>Average Income</th>
            <th>Gender</th>
            <th>Province</th>
            <th>City</th>
            <th>Barangay</th>
            <th>Role</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (count($users) > 0) {
            // Loop through the $users array
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . $user['id'] . "</td>"; // ID column
                
                // Selfie: Display if exists
                if (!empty($user['selfie'])) {
                    echo "<td><img src='" . $user['selfie'] . "' alt='Selfie' class='clickable-img' style='width: 50px; height: 50px; cursor: pointer;'></td>";
                } else {
                    echo "<td>No selfie available</td>";
                }
                
                if (!empty($user['id_upload_path'])) {
                    // Prepend the directory to the path
                    $idUploadPath = 'uploads/' . $user['id_upload_path'];
                    echo "<td><a href='" . $idUploadPath . "' target='_blank'>View ID</a></td>";
                } else {
                    echo "<td>No ID uploaded</td>";
                }

                echo "<td>" . $user['first_name'] . "</td>";
                echo "<td>" . $user['last_name'] . "</td>";
                echo "<td>" . $user['username'] . "</td>";
                echo "<td>" . $user['email'] . "</td>";
                echo "<td>₱" . number_format($user['balance'], 2) . "</td>";
                echo "<td>" . $user['source_of_income'] . "</td>";
                echo "<td>₱" . number_format($user['average_income'], 2) . "</td>";
                echo "<td>" . $user['gender'] . "</td>";
                echo "<td>" . $user['province'] . "</td>";
                echo "<td>" . $user['city'] . "</td>";
                echo "<td>" . $user['barangay'] . "</td>";
                echo "<td>" . $user['role'] . "</td>";

                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='15'>No users found</td></tr>";
        }
        ?>
    </tbody>
</table>

<!-- Modal for image preview -->
<div id="imageModal" class="modal">
    <span class="close" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImg">
    <button class="back-button">Back</button>
</div>

<style>
/* Modal styles */
.modal {
    display: none; /* Hide modal by default */
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.7);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.modal-content {
    margin: auto;
    display: block;
    width: 50%; /* Half the screen width */
    max-width: 600px; /* You can adjust this value if needed */
    height: auto;
    max-height: 80%; /* Prevents image from growing too large vertically */
}

.back-button {
    margin-top: 20px;
    padding: 10px 20px;
    font-size: 16px;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.back-button:hover {
    background-color: #0056b3;
}

.close {
    position: absolute;
    top: 15px;
    right: 35px;
    color: #fff;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
}
</style>

<script>
// Get the modal and image element
// Function to refresh the page when the Back button is clicked
function refreshPage() {
    window.location.reload(); // This will refresh the page
}

// Function to open the modal
function openModal(imgSrc) {
    modal.style.display = "flex"; // Show the modal
    modalImg.src = imgSrc; // Set the modal image source to the clicked image
}

// Function to close the modal
function closeModal() {
    modal.style.display = "none"; // Hide the modal
}

// Add click event to all clickable images (Selfie and ID Upload)
document.addEventListener("DOMContentLoaded", function() {
    // Ensure the modal doesn't open by default
    modal.style.display = "none"; // Confirm modal is hidden initially

    document.querySelectorAll('.clickable-img').forEach(function(img) {
        img.addEventListener('click', function() {
            openModal(this.src); // Only open modal when image is clicked
        });
    });

    // Bind refreshPage function to the back button click
    document.querySelector('.back-button').addEventListener('click', refreshPage);
});
</script>


<script>
// Get the modal and image element
var modal = document.getElementById("imageModal");
var modalImg = document.getElementById("modalImg");

// Function to open the modal
function openModal(imgSrc) {
    modal.style.display = "flex";
    modalImg.src = imgSrc;
}

// Function to close the modal
function closeModal() {
    modal.style.display = "none";
}

// Add click event to all clickable images (Selfie and ID Upload)
document.querySelectorAll('.clickable-img').forEach(function(img) {
    img.onclick = function() {
        openModal(this.src);
    };
});
</script>
<script>
    function showUserLoans(userId) {
        fetch('get_user_loans.php?user_id=' + userId)
            .then(response => response.text())
            .then(data => {
                document.getElementById('user-loans-' + userId).innerHTML = data;
                document.getElementById('user-loans-' + userId).style.display = 'block';
            });
    }

    function clearBalanceAndLoans(userId) {
        if (confirm('Are you sure you want to clear this user\'s balance and loan data?')) {
            fetch('clear_balance_and_loans.php?user_id=' + userId)
                .then(() => location.reload());
        }
    }

    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            fetch('delete_user.php?user_id=' + userId).then(() => location.reload());
        }
    }

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
    function deductAmount(userId) {
    const amountInput = document.getElementById(`amount-to-minus-${userId}`);
    const amount = parseFloat(amountInput.value);

    if (isNaN(amount) || amount <= 0) {
        alert("Please enter a valid amount greater than zero.");
        return;
    }

    if (confirm(`Are you sure you want to deduct ₱${amount.toFixed(2)} from this user's balance?`)) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "deduct_balance.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Update the balance in the DOM
                    document.getElementById(`user-balance-${userId}`).innerText = response.new_balance.toFixed(2);
                    amountInput.value = ""; // Clear the input field
                    alert("Balance updated successfully!");
                } else {
                    alert("Failed to update balance: " + response.message);
                }
            }
        };
        xhr.send(`user_id=${userId}&amount=${amount}`);
    }
}
function showDeclineModal(paymentId) {
            document.getElementById('declineModal').style.display = 'flex';
            document.getElementById('submitDecline').onclick = function() {
                submitDecline(paymentId);
            };
        }

        // Close the decline modal
        function closeModal() {
            document.getElementById('declineModal').style.display = 'none';
        }

        // Submit the decline reason and update the status
        function submitDecline(paymentId) {
            const reason = document.getElementById('declineReason').value;

            if (reason.trim() === '') {
                alert('Please enter a reason for declining.');
                return;
            }

            // Send the reason and update the status in the backend
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_payment_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Handle success (You could update the row in the table or reload the page)
                    alert('Request declined successfully.');
                    location.reload();  // Reload to reflect the status change
                }
            };
            xhr.send('id=' + paymentId + '&status=Declined&reason=' + encodeURIComponent(reason));
            
            closeModal();
        }
        function deductAmount(userId) {
    const balanceElement = document.getElementById(`user-balance-${userId}`);
    const amountInput = document.getElementById(`amount-to-minus-${userId}`);
    const historyContainer = document.getElementById(`transaction-history-${userId}`);

    const currentBalance = parseFloat(balanceElement.textContent);
    const deductionAmount = parseFloat(amountInput.value);

    if (isNaN(deductionAmount) || deductionAmount <= 0) {
        alert("Please enter a valid amount.");
        return;
    }

    if (deductionAmount > currentBalance) {
        alert("Deduction amount exceeds current balance.");
        return;
    }

    // Calculate the new balance
    const newBalance = currentBalance - deductionAmount;

    // Update the balance on the page
    balanceElement.textContent = newBalance.toFixed(2);

    // Record the transaction
    const date = new Date().toLocaleString();
    const transactionRecord = `
        <p>On ${date}: Deducted ₱${deductionAmount.toFixed(2)}. Remaining balance: ₱${newBalance.toFixed(2)}</p>
    `;
    historyContainer.innerHTML += transactionRecord;

    // Clear the input field
    amountInput.value = '';
}

    </script>

</script>

</body>
</html>
