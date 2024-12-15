<?php
// Start the session
session_start();
include 'connection.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Variable to store validation error messages
$message = "";

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    die("User is not logged in.");
}

$username = $_SESSION['username'];

// Retrieve balance from the database or session
if (!isset($_SESSION['balance'])) {
    $balanceQuery = "SELECT balance FROM users WHERE username = ?";
    $stmt = $conn->prepare($balanceQuery);
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($user_balance);
        $stmt->fetch();
        $stmt->close();
        $_SESSION['balance'] = $user_balance;
    } else {
        die("Error fetching balance: " . htmlspecialchars($conn->error));
    }
} else {
    $user_balance = $_SESSION['balance'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMode = $_POST['payment-mode'];

    if ($paymentMode === 'gcash' && isset($_FILES['receipt'])) {
        $receipt = $_FILES['receipt'];
        $uploadDir = 'uploads/';
        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf']; // Restrict file types
        $maxFileSize = 2 * 1024 * 1024; // 2 MB limit

        // Validate receipt file
        if (in_array($receipt['type'], $allowedTypes) && $receipt['size'] <= $maxFileSize) {
            $uniqueName = uniqid('receipt_', true) . '.' . pathinfo($receipt['name'], PATHINFO_EXTENSION);
            $receiptPath = $uploadDir . $uniqueName;

            if (move_uploaded_file($receipt['tmp_name'], $receiptPath)) {
                $status = "Pending";
                $sql = "INSERT INTO payments (name, payment_mode, receipt, status, balance) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("ssssi", $username, $paymentMode, $receiptPath, $status, $user_balance);
                    if ($stmt->execute()) {
                        echo "<script>alert('GCash payment request submitted successfully.');</script>";
                    } else {
                        $message = "Failed to submit payment request.";
                    }
                    $stmt->close();
                } else {
                    $message = "Error preparing statement: " . htmlspecialchars($conn->error);
                }
            } else {
                $message = "Failed to upload receipt.";
            }
        } else {
            $message = "Invalid file type or size. Only JPEG, PNG, and PDF files under 2MB are allowed.";
        }
    } elseif ($paymentMode === 'cash') {
        // Handle cash payment logic
        $name = trim($_POST['name']);
        $mobile = trim($_POST['mobile']);
        $date = trim($_POST['date']);
        $status = "Pending";

        // Basic input validation
        if (!empty($name) && !empty($mobile) && !empty($date)) {
            $sql = "INSERT INTO payments (id, name, mobile, date, payment_mode, status, balance) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssssssi", $id, $name, $mobile, $date, $paymentMode, $status, $user_balance);
                if ($stmt->execute()) {
                    echo "<script>alert('Cash payment request submitted successfully.');</script>";
                } else {
                    $message = "Failed to submit payment request.";
                }
                $stmt->close();
            } else {
                $message = "Error preparing statement: " . htmlspecialchars($conn->error);
            }
        } else {
            $message = "All fields are required for cash payment.";
        }
    } else {
        $message = "Invalid payment mode.";
    }
}

// Output error message if any
if (!empty($message)) {
    echo "<script>alert('$message');</script>";
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>San Diego - Pay</title>
    <!-- Link to Google Fonts for Open Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
    <style>
        /* Reusing mainpage styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            position: relative;
            min-height: 100vh;
            overflow-x: hidden;
            background-color: #ffffff;
        }

        /* Background Circles */
        .background-circle {
            position: absolute;
            border-radius: 50%;
            opacity: 0.6;
            z-index: -1;
        }

        .circle1 { background-color: #f9dd4e; /* Yellow */ bottom: 720px; left: 50%; width: 150vw; height: 700px; }
        .circle2 { background-color: #f7e96c; /* Gold */ bottom: 770px; left: 50%; width: 150vw; height: 700px; }
        .circle3 { background-color: #FFFCC9; /* Light Yellow */ bottom: 820px; left: 50%; width: 150vw; height: 700px; }

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

        /* Content Container */
        .content {
            padding: 100px 20px;
            min-height: 100vh;
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Main Container for Payment */
        .payment-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
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

        .cash-form {
            display: none;
            flex-direction: column;
            gap: 15px;
            margin-top: 15px;
        }

        .cash-form input {
            padding: 10px;
            border: 2px solid #F5E071;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            width: 100%;
        }

        .cash-form input:focus {
            border-color: #e6d165;
            outline: none;
        }
        #payment-mode {
    width: 100%; /* Full width of its container */
    max-width: 400px; /* Restrict max width to prevent it from being too wide on larger screens */
    padding: 8px 10px; /* Adjust padding for a balanced look */
    border: 1px solid #ddd; /* Subtle border */
    border-radius: 5px; /* Rounded corners */
    font-size: 14px; /* Adjust font size for better readability */
    box-sizing: border-box; /* Include padding and border in width calculation */
    appearance: none; /* Ensures uniform appearance across browsers */
}

/* Responsive Adjustment for Mobile */
@media (max-width: 768px) {
    #payment-mode {
        font-size: 5px; /* Slightly smaller font size for smaller screens */
        padding: 6px 8px; /* Adjust padding to maintain balance */
        max-width: 50%; /* Make sure it doesn't exceed the screen width */
    }
    .nav-btn {
        padding: 10px 10px;
    }
}

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .payment-container {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Background Circles -->


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

    <!-- Main Content -->
    <div class="content">
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
        <div style="margin-bottom: 15px;">
            <label for="name" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Username:</label>
            <input type="text" id="name" name="name" placeholder="Enter your username" required
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="mobile" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Mobile Number:</label>
            <input type="tel" id="mobile" name="mobile" placeholder="Enter your mobile number" required
                   maxlength="13" minlength="13" title="Please enter exactly 11 digits, starting with +63."
                   value="+63"
                   oninput="this.value = this.value.replace(/[^0-9+]/g, '').replace(/^(\+63)([0-9]{0,10})$/, '+63$2')"
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="date" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Select Date:</label>
            <input type="date" id="date" name="date" required
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px;">
        </div>
    </div>

    <!-- GCash Payment Details -->
    <div id="gcash-fields" style="display: none;">
        <div style="margin-bottom: 15px; text-align: center;">
            <img src="gcash.jpg" alt="GCash QR Code" style="max-width: 200px; margin-bottom: 10px;">
            <p style="font-weight: bold; color: #333;">Upload your payment receipt screenshot here:</p>
        </div>
        <div style="margin-bottom: 15px;">
            <input type="file" id="receipt" name="receipt" accept="image/*" required
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px;">
        </div>
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

    </div>

    <script>
    // Function to toggle the payment fields based on selection
    function togglePaymentFields() {
    const paymentMode = document.getElementById('payment-mode').value;
    const cashFields = document.getElementById('cash-fields');
    const gcashFields = document.getElementById('gcash-fields');
    const confirmButton = document.getElementById('confirm-button');
    const submitButton = document.getElementById('submit-button');
    const receiptField = document.getElementById('receipt');
    const cashInputs = cashFields.querySelectorAll('input[required]');

    // Reset visibility and required attributes
    cashFields.style.display = 'none';
    gcashFields.style.display = 'none';
    confirmButton.style.display = 'none';
    submitButton.style.display = 'none';
    receiptField.removeAttribute('required');
    cashInputs.forEach(input => input.removeAttribute('required'));

    if (paymentMode === 'cash') {
        cashFields.style.display = 'block';
        confirmButton.style.display = 'block';
        cashInputs.forEach(input => input.setAttribute('required', 'required'));
    } else if (paymentMode === 'gcash') {
        gcashFields.style.display = 'block';
        receiptField.setAttribute('required', 'required');
        if (submitButton) submitButton.style.display = 'block';
    }
}

function confirmSelection() {
    const submitButton = document.getElementById('submit-button');
    const confirmButton = document.getElementById('confirm-button');
    
    if (confirmButton.style.display === 'block') {
        confirmButton.style.display = 'none';
        submitButton.style.display = 'block';
    }
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

    // Disable weekends and holidays for the date input field
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

    // Disable specific unavailable dates for the date input field
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
</script>

</body>
</html>
