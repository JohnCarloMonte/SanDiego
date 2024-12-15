<?php
// confirmloan.php
$loanAmount = isset($_GET['loanAmount']) ? $_GET['loanAmount'] : '0.00'; // Corrected parameter name
$totalAmount = isset($_GET['totalAmount']) ? $_GET['totalAmount'] : '0.00';
$months = isset($_GET['months']) ? $_GET['months'] : '1';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Loan</title>
    <link rel="stylesheet" href="styles.css"> <!-- Your CSS file -->
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7; /* Light background for the body */
            margin: 0;
            padding: 0;
        }

        .confirmation-container {
            background-color: #ffffff; /* White background for the confirmation box */
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px; /* Max width for the confirmation box */
            margin: 100px auto; /* Center the container vertically and horizontally */
            text-align: center; /* Center text */
        }

        h1 {
            margin-bottom: 20px; /* Space below the heading */
            color: #333; /* Dark color for headings */
        }

        p {
            margin: 10px 0; /* Space above and below paragraphs */
            font-size: 1.1em; /* Slightly larger text for readability */
            color: #555; /* Dark gray color for text */
        }

        .back-btn {
            background-color: #F5E071;
            margin: 10px;
            padding: 10px;
            border-radius: 50px;
            text-decoration: none;
            color: black;
            font-weight: bold;
            transition: background-color 0.3s ease;
            font-family: 'Open Sans', sans-serif;
        }

        .back-btn:hover {
            background-color: #e6d165;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <h1>Loan Confirmation</h1>
        <p>Loan Amount: <strong>₱<?php echo number_format($loanAmount, 2); ?></strong></p>
        <p>Interest Rate: <strong>3.49%</strong></p>
        <p>Total Amount (with Interest): <strong>₱<?php echo number_format($totalAmount, 2); ?></strong></p>
        <p>Installment Duration: <strong><?php echo $months; ?> Month(s)</strong></p>
        <a href="Mainpage.php" class="back-btn">Back to Main Page</a>
    </div>
</body>
</html>
