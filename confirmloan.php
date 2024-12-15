<?php
session_start();
include('connection.php'); // Include your database connection file

// Initialize variables
$loanAmount = '0.00';
$totalAmount = '0.00';
$months = '1';
$paymentMethod = '';
$paymentDate = '';
$firstDueDate = '';

$email_query = "SELECT email FROM users WHERE username = ?";
$stmt = $conn->prepare($email_query);
$stmt->bind_param("s", $username);
$stmt->execute();
$email_result = $stmt->get_result();
$user_data = $email_result->fetch_assoc();

$mobile_number = '09111111111';

// Handle POST request (Form Submission)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve loan details from session
    $loanAmount = isset($_SESSION['loanAmount']) ? $_SESSION['loanAmount'] : '0.00';
    $totalAmount = isset($_SESSION['totalAmount']) ? $_SESSION['totalAmount'] : '0.00';
    $months = isset($_SESSION['months']) ? $_SESSION['months'] : '1';
    $paymentMethod = isset($_POST['payment']) ? $_POST['payment'] : '';
    $paymentDate = isset($_POST['paymentDate']) ? $_POST['paymentDate'] : '';

    // Calculate due date only if 'cash' payment method is selected
    if ($paymentMethod === 'cash' && !empty($paymentDate)) {
        $firstDueDate = date('Y-m-d', strtotime($paymentDate . ' + 30 days'));
    } else {
        $paymentDate = null;
        $firstDueDate = null;
    }

    // Insert loan details into the database
    $query = "INSERT INTO loans (username, mobile_number, loan_amount, total_amount, months, payment_method, payment_date, first_due_date, status)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    // Bind parameters
    $stmt->bind_param(
        "ssddisss",
        $_SESSION['username'],
        $mobile_number,
        $loanAmount,
        $totalAmount,
        $months,
        $paymentMethod,
        $paymentDate,
        $firstDueDate

    );

    // Execute the statement
    if ($stmt->execute()) {
        $_SESSION['loan_success'] = true; // Set a success flag in session
    } else {
        $_SESSION['loan_error'] = "There was an error processing your loan request. Please try again.";
    }

    // Close the statement
    $stmt->close();

    // Redirect to Mainpage.php to prevent form resubmission
    header("Location: Mainpage.php");
    exit();
}

// Handle GET request (Display the form)
else {
    // Retrieve loan details from GET parameters
    $loanAmount = isset($_GET['loanAmount']) ? $_GET['loanAmount'] : '0.00';
    $totalAmount = isset($_GET['totalAmount']) ? $_GET['totalAmount'] : '0.00';
    $months = isset($_GET['months']) ? $_GET['months'] : '1';

    // Store loan details in session variables for later use
    $_SESSION['loanAmount'] = $loanAmount;
    $_SESSION['totalAmount'] = $totalAmount;
    $_SESSION['months'] = $months;

    // Set default payment method
    $paymentMethod = isset($_POST['payment']) ? $_POST['payment'] : 'gcash';
}
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

        .confirmation-item {
            display: flex; /* Use flexbox for alignment */
            justify-content: space-between; /* Space between label and value */
            margin: 10px 0; /* Space above and below each item */
        }

        .label {
            flex: 1; /* Allow label to take available space */
            text-align: right; /* Align label text to the right */
            color: #555; /* Dark gray color for text */
        }

        .value {
            flex: 1; /* Allow value to take available space */
            text-align: left; /* Align value text to the left */
            font-weight: bold; /* Bold text for values */
            color: #333; /* Darker color for emphasis */
        }

        .payment-method {
            margin-top: 20px; /* Space above payment method section */
        }

        .date-picker {
            display: none; /* Hide by default */
            margin: 10px 0; /* Space above and below the date picker */
        }

        .back-btn, .request-loan-btn {
            background-color: #F5E071;
            margin: 10px;
            padding: 10px;
            border-radius: 50px;
            text-decoration: none;
            color: black;
            font-weight: bold;
            transition: background-color 0.3s ease;
            font-family: 'Open Sans', sans-serif;
            display: inline-block; /* Ensures the button doesn't take full width */
        }

        .back-btn:hover, .request-loan-btn:hover {
            background-color: #e6d165;
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
    </style>
<script>
    function toggleFields() {
        const paymentMethod = document.querySelector('input[name="payment"]:checked').value;
        const datePicker = document.getElementById('datePicker');
        const dueDateText = document.getElementById('dueDateText');
        const gcashPhoneField = document.getElementById('gcashPhoneField');
        const gcashPhone = document.getElementById('gcashPhone');

        // Toggle visibility and required attribute based on selected payment method
        if (paymentMethod === 'cash') {
            datePicker.style.display = 'block';
            gcashPhoneField.style.display = 'none';  // Hide GCash phone field
            gcashPhone.removeAttribute('required');  // Remove 'required' attribute when hidden
        } else if (paymentMethod === 'gcash') {
            datePicker.style.display = 'none';  // Hide date picker
            gcashPhoneField.style.display = 'block';  // Show GCash phone field
            gcashPhone.setAttribute('required', 'required');  // Add 'required' attribute when shown
        }
    }

    // Function to calculate and display due date based on the selected payment date
    function calculateDueDate() {
        const paymentDate = document.getElementById('paymentDate').value;
        const dueDateDisplay = document.getElementById('dueDateDisplay');
        const dueDateText = document.getElementById('dueDateText');

        if (paymentDate) {
            const selectedDate = new Date(paymentDate);
            selectedDate.setDate(selectedDate.getDate() + 30); // Add 30 days for due date

            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            dueDateDisplay.textContent = selectedDate.toLocaleDateString(undefined, options);
            dueDateText.style.display = 'block';
        } else {
            dueDateText.style.display = 'none';
        }
    }

    // Ensure the correct payment method field is displayed when the page loads
    window.onload = function() {
        const paymentMethod = '<?php echo $paymentMethod; ?>';
        if (paymentMethod === 'cash') {
            document.getElementById('datePicker').style.display = 'block';
            document.getElementById('gcashPhoneField').style.display = 'none';
        } else if (paymentMethod === 'gcash') {
            document.getElementById('gcashPhoneField').style.display = 'block';
            document.getElementById('datePicker').style.display = 'none';
        }

        // Call toggleFields to initialize the correct field visibility on page load
        toggleFields();
    }
</script>

</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <a href="Mainpage.php" class="nav-btn">Back to Home</a>
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

    <div class="confirmation-container">
        <h1>Loan Confirmation</h1>
        <div class="confirmation-item">
            <p class="label">Loan Amount:</p>
            <p class="value">₱<?php echo number_format($loanAmount, 2); ?></p>
        </div>
        <div class="confirmation-item">
            <p class="label">Interest Rate:</p>
            <p class="value">3%</p>
        </div>
        <div class="confirmation-item">
            <p class="label">Total Amount (with Interest):</p>
            <p class="value">₱<?php echo number_format($totalAmount, 2); ?></p>
        </div>
        <div class="confirmation-item">
            <p class="label">Installment Duration:</p>
            <p class="value"><?php echo $months; ?> Month(s)</p>
        </div>

        <!-- Payment Method Section -->
        <form method="POST" action="confirmloan.php">
    <div class="payment-method">
        <p>Please select a payment method:</p>
        <label>
            <input type="radio" name="payment" value="gcash" onclick="toggleFields()" <?php echo ($paymentMethod === 'gcash') ? 'checked' : ''; ?>>
            GCash
        </label>
        <label>
            <input type="radio" name="payment" value="cash" onclick="toggleFields()" <?php echo ($paymentMethod === 'cash') ? 'checked' : ''; ?>>
            Cash
        </label>
    </div>

    <!-- GCash Phone Field -->
    <div id="gcashPhoneField" style="<?php echo ($paymentMethod === 'gcash') ? 'display:block;' : 'display:none;'; ?>">
        <label for="gcashPhone">Enter your GCash phone number:</label>
        <input type="text" id="gcashPhone" name="gcashPhone" placeholder="09XXXXXXXXX" 
               maxlength="11" pattern="[0-9]{11}" 
               oninput="validatePhoneNumber(this)"
               value="<?php echo isset($_SESSION['gcashPhone']) ? $_SESSION['gcashPhone'] : ''; ?>" 
               >
    </div>

    <!-- Date picker for Cash -->
    <div id="datePicker" class="date-picker" style="<?php echo ($paymentMethod === 'cash') ? 'display:block;' : 'display:none;'; ?>">
        <label for="paymentDate">Select an office appointment date:</label>
        <input type="date" id="paymentDate" name="paymentDate" onchange="calculateDueDate()" value="<?php echo isset($_SESSION['paymentDate']) ? $_SESSION['paymentDate'] : ''; ?>">
    </div>

    <div id="dueDateText" style="<?php echo ($paymentMethod === 'cash' && !empty($paymentDate)) ? 'display:block;' : 'display:none;'; ?>">
        <p>Your first due date is: <span id="dueDateDisplay">
            <?php
                if ($paymentMethod === 'cash' && !empty($paymentDate)) {
                    echo date('F j, Y', strtotime($firstDueDate));
                }
            ?>
        </span></p>
    </div>

    <button type="submit" class="request-loan-btn">Request Loan</button>
</form>

        <a href="Mainpage.php" class="back-btn">Back</a>
    </div>
    <script>function toggleFields() {
    const paymentMethod = document.querySelector('input[name="payment"]:checked').value;
    const gcashPhoneField = document.getElementById('gcashPhoneField');
    const datePicker = document.getElementById('datePicker');
    const dueDateText = document.getElementById('dueDateText');

    // Reset visibility for all fields
    gcashPhoneField.style.display = 'none';
    datePicker.style.display = 'none';
    dueDateText.style.display = 'none';

    if (paymentMethod === 'gcash') {
        gcashPhoneField.style.display = 'block'; // Show GCash phone field
    } else if (paymentMethod === 'cash') {
        datePicker.style.display = 'block'; // Show date picker
        dueDateText.style.display = 'block'; // Show due date text if applicable
    }
}

// Ensure fields are toggled on page load based on selected payment method
document.addEventListener('DOMContentLoaded', function () {
    toggleFields();
});
function validatePhoneNumber(input) {
    // Allow only numbers and limit to 11 characters
    input.value = input.value.replace(/[^0-9]/g, '').slice(0, 11);
}
</script>
</body>
</html>
