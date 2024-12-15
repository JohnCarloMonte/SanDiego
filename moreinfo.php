<?php
include("connection.php");

if (isset($_GET['id'])) {
    $loan_id = intval($_GET['id']); // Retrieve the ID safely

    // Get loan details
    $stmt = $conn->prepare("SELECT * FROM loans WHERE id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("i", $loan_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $loan = $result->fetch_assoc();

        // Calculate the interest amount
        $loan_amount = floatval($loan['loan_amount']);
        $total_amount = floatval($loan['total_amount']);
        $interest_amount = $total_amount - $loan_amount;

        // Determine the interest percentage based on months
        $interest_percentage = "";
        if ($loan['months'] == 1) {
            $interest_percentage = "3%";
        } elseif ($loan['months'] == 2) {
            $interest_percentage = "5%";
        } elseif ($loan['months'] == 3) {
            $interest_percentage = "8%";
        } elseif ($loan['months'] == 4) {
            $interest_percentage = "10%";
        }

        // Fetch user details based on username
        $username = $loan['username'];
        $user_stmt = $conn->prepare("SELECT first_name, last_name, selfie, source_of_income, average_income FROM users WHERE username = ?");
        $user_stmt->bind_param("s", $username);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();

        if ($user_result->num_rows > 0) {
            $user = $user_result->fetch_assoc();
            $first_name = $user['first_name'];
            $last_name = $user['last_name'];
            $selfie = $user['selfie']; // Path to the selfie
            $source_of_income = $user['source_of_income'];
            $average_income = $user['average_income'];
        } else {
            $first_name = "Unknown";
            $last_name = "User";
            $selfie = "";
            $source_of_income = "Not Available";
            $average_income = "Not Available";
        }

        // Calculate daily and weekly payments
        $days_in_month = 30; // Approximate days in a month
        $weeks_in_month = 4; // Approximate weeks in a month

        // Total days and weeks based on loan months
        $total_days = $loan['months'] * $days_in_month;
        $total_weeks = $loan['months'] * $weeks_in_month;

        // Calculate the daily and weekly payments
        $daily_payment = $total_amount / $total_days;
        $weekly_payment = $total_amount / $total_weeks;

    } else {
        echo "Loan not found.";
        exit;
    }
    $stmt->close();
    $user_stmt->close();
} else {
    echo "ID is missing from the URL.";
    exit; // Stop further execution
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Details</title>
  <style>  body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color: #f8f9fa;
    color: #333;
}
.logo-print {
    display: block; /* Ensures it acts as a block-level element */
    margin: 0 auto; /* Centers horizontally */
    width: 150px;   /* Adjust size as needed */
}

.container {
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    background-color: white;
}

h2 {
    text-align: center;
    margin-bottom: 20px;
}

.loan-details {
    margin-bottom: 20px;
}

.loan-details p {
    font-size: 18px;
    margin: 10px 0;
}

.error-message {
    color: red;
    text-align: center;
    margin-bottom: 20px;
}

.back-btn, .print-btn {
    display: block;
    text-align: center;
    padding: 10px 20px;
    background-color: #F5E071;
    color: black;
    text-decoration: none;
    border-radius: 5px;
    margin-top: 20px;
}

.back-btn:hover, .print-btn:hover {
    background-color: #e6d165;
}

/* Hide these buttons when printing */
@media print {
    .back-btn, .print-btn {
        display: none;
    }

    /* Add logo and selfie to top center when printing */
    .logo-print {
        display: inline-block;
        margin: 0 auto;
        width: 150px; /* Adjust size as needed */
    }

    .selfie-print {
        display: inline-block;
        margin: 0 10px;
        width: 100px; /* Adjust size as needed */
        height: 100px;
        border-radius: 50%;
    }

    .status {
        display: none;
    }

    /* Custom footer styling */
    .footer {
        display: flex;
        justify-content: space-between;
        padding-top: 20px;
        border-top: 1px solid #ccc;
        margin-top: 20px;
    }

    .footer div {
        text-align: center;
    }

    .footer div p {
        text-decoration: underline;
        margin: 5px 0;
    }
}

.name-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
}

.name-left, .name-right {
    position: relative;
    text-align: center;
}

.name-left p, .name-right p {
    margin: 0;
    font-weight: bold;
    position: relative;
    text-align: center;
}

.name-left p::before, .name-right p::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 1px;
    background-color: #000;
    /* Center the border above the text */
    margin-left: auto;
    margin-right: auto;
}

.name-left p {
    text-align: left;
}

.name-right p {
    text-align: right;
}

/* Hide elements by default */
.selfie-print, .name-container {
    display: none;
}

/* Display elements only when printing */
@media print {
    .selfie-print, .name-container {
        display: block;
    }

    /* You can adjust the size of the selfie in print */
    .selfie-print {
        width: 100px;
        height: 100px;
    }

    .logo-print {
        display: inline-block;
        margin: 0 auto;
        width: 150px; /* Adjust size as needed */
        float: right; /* Align logo to the right */
    }

    .selfie-print {
        display: inline-block;
        margin: 0 10px;
        width: 100px; /* Adjust size as needed */
        height: 100px;
        border-radius: 50%;
        float: left; /* Align selfie to the left */
    }

    /* Adjust other styles as needed for print */
}
</style>
    <script>
        function printPage() {
            window.print();
        }
    </script>
</head>
<body>
    <div class="container">
        <!-- Logo and Selfie for print -->
        <div style="overflow: hidden;">
            <img src="logo.jpg" class="logo-print" alt="Logo">
            <?php if ($selfie) : ?>
                <img src="<?php echo htmlspecialchars($selfie); ?>" class="selfie-print" alt="Selfie" style="width: 100px; height: 100px;">
            <?php endif; ?>
        </div>

        <h2>Loan Details</h2>
        <div class="loan-details">
            <p><strong>Borrower Name:</strong> <?php echo htmlspecialchars($first_name) . ' ' . htmlspecialchars($last_name); ?></p>
            <p><strong>Loan Amount:</strong> ₱<?php echo htmlspecialchars($loan['loan_amount']); ?></p>
            <p><strong>Daily Payment:</strong> ₱<?php echo number_format($daily_payment, 2); ?></p>
            <p><strong>Weekly Payment:</strong> ₱<?php echo number_format($weekly_payment, 2); ?></p>
            <p><strong>Interest Amount:</strong> ₱<?php echo number_format($interest_amount, 2); ?> (<?php echo htmlspecialchars($interest_percentage); ?>)</p>
            <p><strong>Total Amount:</strong> ₱<?php echo htmlspecialchars($loan['total_amount']); ?></p>
            <p><strong>Months:</strong> <?php echo htmlspecialchars($loan['months']); ?> months</p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($loan['payment_method']); ?></p>
            <p><strong>Payment Date:</strong> <?php echo htmlspecialchars($loan['payment_date']); ?></p>
            <p><strong>First Due Date:</strong> <?php echo htmlspecialchars($loan['first_due_date']); ?></p>
            <p><strong>Source of Income:</strong> <?php echo htmlspecialchars($source_of_income); ?></p>
            <p><strong>Average Income:</strong> ₱<?php echo htmlspecialchars($average_income); ?></p>
        </div>

        <!-- Print Button -->
        <button class="print-btn" onclick="printPage()">Print Details</button>
        
        <!-- Back Button -->
        <a href="mainpage.php" class="back-btn">Back to Main Page</a>
    </div>
    
    <br><br><br><br>
    
    <!-- Footer Section for Print -->
    <div class="name-container">
        <div class="name-left">
            <span class="line"></span>
            <p><?php echo htmlspecialchars($first_name) . ' ' . htmlspecialchars($last_name); ?></p>
            <p>Client</p>
        </div>
        <div class="name-right">
            <p>Mike San Diego</p>
            <p>SanDiego</p>
            <span class="line"></span>
        </div>
    </div>

</body>
</html>