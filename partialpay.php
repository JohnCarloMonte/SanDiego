<?php
session_start();
include("connection.php");

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    die("User not logged in.");
}

// Retrieve the user's balance
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT balance FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($user_balance);
$stmt->fetch();
$stmt->close(); // Close the statement after fetching the balance

// Initialize messages
$success_message = $error_message = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['partial_amount'], $_POST['payment-mode'])) {
    // Sanitize and validate the payment amount
    $partial_amount = floatval(trim($_POST['partial_amount']));
    $payment_mode = $_POST['payment-mode'];

    if (!is_numeric($partial_amount) || $partial_amount < 1000 || $partial_amount > $user_balance) {
        $error_message = "Invalid payment amount. Please enter a value of ₱1000 or more and not exceeding your balance.";
    } elseif ($payment_mode === 'cash') {
        // Handle Cash Payment
        $name = trim($_POST['name']);
        $mobile = trim($_POST['mobile']);
        $date = $_POST['date'];

        // Validate cash payment fields
        if (empty($name) || empty($mobile) || empty($date)) {
            $error_message = "Please fill in all Cash payment fields.";
        } else {
            $stmt = $conn->prepare("INSERT INTO payment_requests (username, amount, payment_mode, name, mobile, payment_date) VALUES (?, ?, 'cash', ?, ?, ?)");
            $stmt->bind_param("sdsss", $username, $partial_amount, $name, $mobile, $date);
            if ($stmt->execute()) {
                $success_message = "Cash payment of ₱" . number_format($partial_amount, 2) . " submitted for admin approval.";
            } else {
                $error_message = "There was an error processing your cash payment. Please try again.";
            }
            $stmt->close();
        }
    } elseif ($payment_mode === 'gcash') {
        // Handle GCash Payment
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
            // File upload handling
            $receipt_name = basename($_FILES['receipt']['name']);
            $receipt_tmp_path = $_FILES['receipt']['tmp_name'];
            $upload_dir = "uploads/";
            $receipt_path = $upload_dir . time() . "_" . $receipt_name; // Unique file name

            if (move_uploaded_file($receipt_tmp_path, $receipt_path)) {
                $stmt = $conn->prepare("INSERT INTO payment_requests (username, amount, payment_mode, receipt_path) VALUES (?, ?, 'gcash', ?)");
                $stmt->bind_param("sds", $username, $partial_amount, $receipt_path);
                if ($stmt->execute()) {
                    $success_message = "GCash payment of ₱" . number_format($partial_amount, 2) . " submitted with receipt for admin approval.";
                } else {
                    $error_message = "There was an error processing your GCash payment. Please try again.";
                }
                $stmt->close();
            } else {
                $error_message = "Failed to upload GCash receipt. Please try again.";
            }
        } else {
            $error_message = "Please upload a valid GCash receipt.";
        }
    } else {
        $error_message = "Invalid payment mode selected.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partial Payment</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            color: #333;
        }
        .container {
            padding: 100px 20px;
            min-height: 70vh;
            position: relative;
            z-index: 1;
            justify-content: center;
            align-items: center;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .success-message {
            background-color: #4CAF50;
            color: white;
        }
        label, input, button {
            display: block;
            width: 100%;
            margin-bottom: 15px;
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ddd;
            box-sizing: border-box;
        }
        input[type="number"] {
            -webkit-appearance: none;
            -moz-appearance: textfield;
        }
        button {
            background-color: #F5E071;
            color: black;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #e6d165;
        }
        .cancel-btn {
            background-color: #f44336;
            color: white;
            display: inline-block;
            text-align: center;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 10px;
        }
        .cancel-btn:hover {
            background-color: #d32f2f;
        }
        .form-container {
            padding: 20px;
        }
        .form-container input, .form-container button {
            font-size: 18px;
        }
        .form-container a {
            display: inline-block;
            text-align: center;
            margin-top: 20px;
            font-size: 16px;
        }

        /* Custom validation styles */
        input:invalid {
            border-color: #e74c3c;
        }

        input:valid {
            border-color: #2ecc71;
        }
        @media (max-width: 768px) {

    .nav-btn {
        padding: 10px 10px;
    }
}
        /* Navbar container */
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .nav-left { flex: 1; text-align: left; }
        .nav-center { flex: 1; text-align: center; }
        .nav-right { flex: 1; text-align: right; }
        .site-name { font-size: 24px; font-weight: bold; cursor: pointer; text-decoration: none; color: black; }
        .nav-btn {
            background-color: #F5E071; padding: 10px 15px; border-radius: 50px; text-decoration: none;
            color: black; font-weight: bold; transition: background-color 0.3s ease; display: inline-block;
        }
        .nav-btn:hover { background-color: #e6d165; }
        .payment-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 600px;

            text-align: center;
        }

        .payment-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
        }

        /* Form Elements */
        .payment-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .payment-form label {
            font-size: 1rem;
            color: #333333;
        }

        .payment-form select,
        .payment-form input {
            padding: 10px;
            border: 2px solid #F5E071;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            width: 100%;
        }

        .payment-form select:focus,
        .payment-form input:focus {
            border-color: #e6d165;
            outline: none;
        }
        .gcash-btn,
        .submit-btn {
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

        .gcash-btn:hover,
        .submit-btn:hover {
            background-color: #e6d165;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid;
            border-radius: 5px;
        }
        .success {
            color: green;
            border-color: green;
        }
        .error {
            color: red;
            border-color: red;
        }
        .hidden {
            display: none;
        }

    </style>
</head>
<body>
        <!-- Navbar -->
        <nav class="navbar">
        <div class="nav-left">
        <a href="Mainpage.php" class="nav-btn">Back</a>
        </div>
        <div class="nav-center">
        <a href="index.html" class="site-name">San Diego</a>
        </div>
        <div class="nav-right"></div>
    </nav>
<div class="container">
<?php if (!empty($success_message)): ?>
        <div class="message success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="message error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>


    <div class="payment-container">
    <h2>Select Mode of Payment</h2>

    <!-- Display validation message if exists -->
    <?php if (!empty($message)): ?>
        <p style="color: red; font-weight: bold;"><?= $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" style="max-width: 600px; margin: 0 auto; padding: 20px; background: #ffffff; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); font-family: 'Open Sans', sans-serif;">
    <!-- Payment Mode Selection -->
    <div style="margin-bottom: 15px;">
        <label for="payment-mode" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Payment Mode:</label>
        <select id="payment-mode" name="payment-mode" required onchange="togglePaymentFields()" 
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px;">
            <option value="">-- Select --</option>
            <option value="gcash">GCash</option>
            <option value="cash">Cash</option>
        </select>
        
    </div>
        <!-- Cash Payment Details -->
        <div id="cash-fields" style="display: none;">
            <label for="name">Username:</label>
            <input type="text" id="name" name="name" placeholder="Enter your username">

            <label for="mobile">Mobile Number:</label>
            <input type="tel" id="mobile" name="mobile" placeholder="Enter your mobile number" maxlength="11" minlength="11" title="Please enter exactly 11 digits." oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            
            <label for="partial_amount">Enter Amount to Pay (₱):</label>
            <input 
                type="number" 
                id="partial_amount" 
                name="partial_amount" 
                min="1000" 
                max="<?= htmlspecialchars($user_balance) ?>" 
                step="1" 
                required
            >
            
            <label for="date">Select Date:</label>
            <input type="date" id="date" name="date" min="<?= date('Y-m-d') ?>">
        </div>

        <!-- GCash Payment Details -->
        <div id="gcash-fields" style="display: none;">
            <div style="margin-bottom: 15px; text-align: center;">
                <img src="gcash.jpg" alt="GCash QR Code" style="max-width: 200px; margin-bottom: 10px;">
                <p>Upload your payment receipt screenshot here:</p>
            </div>
            <input type="file" id="receipt" name="receipt" accept="image/*">
        </div>


    <!-- Confirm Button -->
    <button type="button" id="confirm-button" onclick="confirmSelection()" style="width: 100%; padding: 10px; background: #f5e071; border: none; border-radius: 5px; font-size: 18px; font-weight: bold; color: #333; cursor: pointer; transition: background-color 0.3s ease; display: none;">
        Confirm Selection
    </button>

    <!-- Submit Button -->
    <button type="submit" id="submit-button" href="Mainpage.php" style="width: 100%; padding: 10px; background: #f5e071; border: none; border-radius: 5px; font-size: 18px; font-weight: bold; color: #333; cursor: pointer; transition: background-color 0.3s ease; display: none; margin-top: 10px;">
        Submit
    </button>

    </form>
</div>

<script>
         
    // Function to toggle the payment fields based on selection
    function togglePaymentFields() {
        const paymentMode = document.getElementById('payment-mode').value;
        const cashFields = document.getElementById('cash-fields');
        const gcashFields = document.getElementById('gcash-fields');
        const confirmButton = document.getElementById('confirm-button');
        const submitButton = document.getElementById('submit-button');
        
        // Show appropriate fields based on the selected payment mode
        if (paymentMode === 'cash') {
            cashFields.style.display = 'block';
            gcashFields.style.display = 'none';
            confirmButton.style.display = 'block'; // Show confirm button for cash payment
        } else if (paymentMode === 'gcash') {
            cashFields.style.display = 'none';
            gcashFields.style.display = 'block';
            confirmButton.style.display = 'none'; // Hide confirm button for GCash payment
        } else {
            cashFields.style.display = 'none';
            gcashFields.style.display = 'none';
            confirmButton.style.display = 'none';
            submitButton.style.display = 'none'; // Hide submit button if no payment method is selected
        }

        checkFormCompletion(); // Ensure button visibility based on form completion
    }

    // Function to ensure all fields are filled before showing the submit button
    function checkFormCompletion() {
        const paymentMode = document.getElementById('payment-mode').value;
        const submitButton = document.getElementById('submit-button');
        const confirmButton = document.getElementById('confirm-button');
        const requiredFields = document.querySelectorAll('input[required], select[required]');
        
        let allFilled = true;

        // Check if all required fields are filled
        requiredFields.forEach(field => {
            if (field.value.trim() === '') {
                allFilled = false; // If any field is empty, form is not complete
            }
        });

        // Show or hide the submit button based on form completion
        if (allFilled && paymentMode !== '') {
            submitButton.style.display = 'block'; // Show submit button if all fields are filled
        } else {
            submitButton.style.display = 'none'; // Hide submit button if any field is empty
        }

        // Also, hide the submit button until the confirm button is clicked for cash payment
        if (paymentMode === 'cash' && !confirmButton.style.display === 'block') {
            submitButton.style.display = 'none';
        }
    }

    // Function to show submit button after confirm for cash payment
    function confirmSelection() {
        const submitButton = document.getElementById('submit-button');
        const confirmButton = document.getElementById('confirm-button');
        
        confirmButton.style.display = 'none'; // Hide confirm button after selection
        submitButton.style.display = 'block'; // Show submit button after confirmation
    }

    // Initialize form checking and set up event listener for form fields
    document.addEventListener('DOMContentLoaded', function () {
        checkFormCompletion();
        document.getElementById('payment-mode').addEventListener('change', togglePaymentFields);
    });
           // List of holiday dates (format: 'YYYY-MM-DD')
    const holidays = [
        '2024-12-25', // Example: Christmas
        '2025-01-01',
        '2025-01-02',
        '2024-12-24',
        '2024-12-31', // Example: New Year's Day
        '2024-07-04'  // Example: Independence Day
    ];

    // Disable weekends and holidays
    const dateInput = document.getElementById('date');
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');

    // Set minimum date to today
    dateInput.min = `${yyyy}-${mm}-${dd}`;

    dateInput.addEventListener('input', function () {
        const selectedDate = new Date(this.value);
        const day = selectedDate.getDay(); // 0 (Sunday) to 6 (Saturday)
        const formattedDate = this.value;

        // Check if the selected date is a weekend or a holiday
        if (day === 0 || day === 6 || holidays.includes(formattedDate)) {
            alert('The selected date is a holiday or a weekend. Please choose another date.');
            this.value = ''; // Reset the value
        }
    });
        function togglePaymentDivs() {
            const paymentMode = document.getElementById('payment-mode').value;
            const gcashSection = document.getElementById('gcash-section');
            const cashSection = document.getElementById('cash-section');

            // Show or hide sections based on the selected payment mode
            if (paymentMode === 'gcash') {
                gcashSection.style.display = 'block';
                cashSection.style.display = 'none';
            } else if (paymentMode === 'cash') {
                gcashSection.style.display = 'none';
                cashSection.style.display = 'flex'; // Flex to match form layout
            } else {
                gcashSection.style.display = 'none';
                cashSection.style.display = 'none';
            }
        }
        document.getElementById('date').addEventListener('focus', function () {
    const unavailableDates = ['2024-11-25', '2024-12-01']; // Example dates
    const datePicker = this;

    datePicker.addEventListener('input', function () {
        if (unavailableDates.includes(this.value)) {
            alert('This date is unavailable. Please choose another date.');
            this.value = ''; // Clear the invalid date
        }
    });
});

function togglePaymentFields() {
        const paymentMode = document.getElementById('payment-mode').value;
        const cashFields = document.getElementById('cash-fields');
        const gcashFields = document.getElementById('gcash-fields');
        const confirmButton = document.getElementById('confirm-button');
        const submitButton = document.getElementById('submit-button');

        // Reset fields and buttons
        cashFields.style.display = 'none';
        gcashFields.style.display = 'none';
        confirmButton.style.display = paymentMode ? 'block' : 'none'; // Show confirm button only if an option is selected
        submitButton.style.display = 'none';

        if (paymentMode === 'cash') {
            cashFields.style.display = 'block';
        } else if (paymentMode === 'gcash') {
            gcashFields.style.display = 'block';
        }
    }

    function confirmSelection() {
        const submitButton = document.getElementById('submit-button');
        submitButton.style.display = 'block'; // Show submit button after confirmation
        alert('Your selection has been confirmed. Please proceed with submission.');
    }
    function validateCashFields() {
            const name = document.getElementById('name').value.trim();
            const mobile = document.getElementById('mobile').value.trim();
            const partialAmount = document.getElementById('partial_amount').value.trim();
            const date = document.getElementById('date').value.trim();
            const submitButton = document.getElementById('submit-button');

            // Enable the submit button if all fields are filled, otherwise disable
            submitButton.disabled = !(name && mobile && partialAmount && date);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const cashFields = document.querySelectorAll('#cash-fields input');
            cashFields.forEach(input => input.addEventListener('input', validateCashFields));
        });

        document.addEventListener('DOMContentLoaded', () => {
            const messages = document.querySelectorAll('.message');
            setTimeout(() => {
                messages.forEach(message => {
                    message.classList.add('hidden');
                });
            }, 2000);
        });
    </script>
</script>
</body>
</html>
