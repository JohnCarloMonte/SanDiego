<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semaphore SMS Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        form {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        input, textarea, button {
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #28a745;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h2>Semaphore SMS Test</h2>
    <form method="POST" action="">
        <label for="apikey">API Key:</label>
        <input type="text" id="apikey" name="apikey" placeholder="Enter your API key" required>

        <label for="number">Recipient's Number:</label>
        <input type="tel" id="number" name="number" placeholder="Enter recipient's mobile number (e.g., 639XXXXXXXXX)" required>

        <label for="message">Message:</label>
        <textarea id="message" name="message" rows="4" placeholder="Enter your message here" required></textarea>

        <label for="sendername">Sender Name:</label>
        <input type="text" id="sendername" name="sendername" placeholder="Enter sender name (default: SEMAPHORE)">

        <button type="submit">Send Message</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Initialize cURL
        $ch = curl_init();

        // Collect form data
        $apikey = trim($_POST['apikey']);
        $number = trim($_POST['number']);
        $message = trim($_POST['message']);
        $sendername = trim($_POST['sendername']) ?: 'SEMAPHORE';

        // Validate phone number format
        if (!preg_match('/^63\d{10}$/', $number)) {
            echo "<p style='color: red;'>Invalid phone number format. Use 63XXXXXXXXXX.</p>";
            exit;
        }

        // Define the parameters for the API request
        $parameters = array(
            'apikey' => $apikey,
            'number' => $number,
            'message' => $message,
            'sendername' => $sendername
        );

        // Set the cURL options
        curl_setopt($ch, CURLOPT_URL, 'https://semaphore.co/api/v4/messages'); // Semaphore API endpoint
        curl_setopt($ch, CURLOPT_POST, 1); // HTTP POST method

        // Send the parameters with the request
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));

        // Enable verbose output for debugging
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        // Receive the response from the server
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        fclose($verbose);
        $error = curl_error($ch);
        curl_close($ch);

        // Display debugging information
        echo "<h3>Debugging Information</h3>";
        if ($output === false) {
            echo "<p style='color: red;'>cURL Error: " . htmlspecialchars($error) . "</p>";
        } else {
            echo "<pre>Verbose information:\n" . htmlspecialchars($verboseLog) . "</pre>";
            echo "<pre>Raw Output: " . htmlspecialchars($output) . "</pre>";

            // Decode and display the response
            $response = json_decode($output, true);
            if (isset($response['status']) && $response['status'] === 'success') {
                echo "<p style='color: green;'>Message sent successfully!</p>";
            } else {
                $errorMessage = $response['message'] ?? 'Unknown error';
                echo "<p style='color: red;'>Error: " . htmlspecialchars($errorMessage) . "</p>";
            }
        }
    }
    ?>
</body>
</html>
