<?php
session_start();

// Semaphore API credentials
$apikey = '8b6a9b09e0fb0c84ce7e9881d455ee07';
$mobile = $_SESSION['mobile'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = $_POST['otp'];
    $sent_otp = $_SESSION['otp'];

    if ($entered_otp == $sent_otp) {
        // OTP is correct, proceed to create password page
        header('Location: cpassword.php');
        exit();
    } else {
        $error = "Invalid OTP. Please try again.";
    }
} else {
    // Generate and send OTP
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;

    $message = "Your One Time Password is: {otp}. Please use it within 5 minutes.";
    $url = "https://api.semaphore.co/api/v4/otp";

    $payload = [
        'apikey' => $apikey,
        'number' => $mobile,
        'message' => $message
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    // Decode response (optional for debugging)
    $response_data = json_decode($response, true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>OTP Verification</title>
</head>
<body>
    <h2>Verify Your Mobile Number</h2>
    <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
    <form method="POST" action="">
        <label for="otp">Enter the OTP sent to your number:</label>
        <input type="text" id="otp" name="otp" required>
        <button type="submit">Verify</button>
    </form>
</body>
</html>
