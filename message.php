<?php
session_start();
include('connection.php');

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username']; // Current user's username

// Handle message deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Transfer message to the archive table
    $conn->query("
        INSERT INTO message_archive (sender_username, receiver_username, message, sent_at)
        SELECT sender_username, receiver_username, message, sent_at
        FROM messages
        WHERE id = $delete_id
    ");

    // Delete message from the messages table
    $conn->query("DELETE FROM messages WHERE id = $delete_id");

    // Redirect to avoid refresh re-deletion
    header("Location: message.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['receiver'], $_POST['message'])) {
    $receiver = $_POST['receiver'];
    $message = $_POST['message'];

    // Check if the receiver exists in the database or is "admin"
    if ($receiver === 'admin') {
        // Bypass database check and send message directly to admin
        $stmt = $conn->prepare("INSERT INTO messages (sender_username, receiver_username, message) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $username, $receiver, $message);
        $stmt->execute();
        $stmt->close();

        // Redirect to avoid resubmission
        header("Location: message.php");
        exit();
    } else {
        // Check if the receiver exists in the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param('s', $receiver);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Receiver exists, send the message
            $stmt = $conn->prepare("INSERT INTO messages (sender_username, receiver_username, message) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $username, $receiver, $message);
            $stmt->execute();
            $stmt->close();

            // Redirect to avoid resubmission
            header("Location: message.php");
            exit();
        } else {
            // Receiver does not exist
            $_SESSION['error_message'] = "User does not exist.";
            header("Location: message.php");
            exit();
        }
    }
}

// Fetch messages involving the current user
$stmt = $conn->prepare("SELECT * FROM messages WHERE sender_username = ? OR receiver_username = ? ORDER BY sent_at DESC");
$stmt->bind_param('ss', $username, $username);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h2, h3 {
            text-align: center;
        }
        form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        form input, form textarea, form button {
            width: 97%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        form button {
            background-color: #F5E071;
            color: black;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        form button:hover {
            background-color: #e6d165;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .delete-button {
            background-color: #dc3545;
            color: white;
            border: none;

            cursor: pointer;

        }
        .delete-button:hover {
            background-color: #c82333;
        }
        .error-popup {
    background-color: #ffdddd;
    color: #d8000c;
    border: 1px solid #d8000c;
    padding: 10px;
    margin: 20px auto;
    width: 90%;
    max-width: 600px;
    border-radius: 5px;
    text-align: center;
    font-weight: bold;
    opacity: 1; /* Start fully visible */
    transition: opacity 0.5s ease-out; /* Smooth fade-out */
}
.error-popup.hidden {
    opacity: 0; }

    </style>
</head>
<body>
<?php if (isset($_SESSION['error_message'])): ?>
    <div id="error-popup" class="error-popup">
        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
    </div>
    <?php unset($_SESSION['error_message']); // Clear error message after displaying ?>
<?php endif; ?>


    <h2>Your Messages</h2>

    <!-- Message Input Form -->
    <form method="POST" action="">
    <label for="receiver">Username of the receiver:</label>
    <input type="text" name="receiver" id="receiver" placeholder="Enter username of the recipient" required>
    
    <label for="message">Message:</label>
    <textarea name="message" id="message" rows="4" placeholder="Enter your message here" required></textarea>
    
    <button type="submit">Send Message</button>
    
    <!-- New button to automatically message customer service (admin) -->
    <button type="button" onclick="messageCustomerService()">Message Customer Service</button>
    
    <!-- Back Button -->
    <button type="button" onclick="goBack()">Back</button>
</form>

<script>
    // Function to automatically fill in "admin" as the recipient
    function messageCustomerService() {
        document.getElementById('receiver').value = 'admin';  // Set the receiver to "admin"
    }

    // Function to navigate back to the main page
    function goBack() {
        window.location.href = 'mainpage.php';  // Redirects to mainpage.php
    }
</script>


    <!-- Messages Table -->
    <table>
    <h3>Message History</h3>
        <thead>
            <tr>
                <th>Sender</th>
                <th>Receiver</th>
                <th>Message</th>
                <th>Sent At</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($msg = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($msg['sender_username']); ?></td>
                    <td><?php echo htmlspecialchars($msg['receiver_username']); ?></td>
                    <td><?php echo htmlspecialchars($msg['message']); ?></td>
                    <td><?php echo htmlspecialchars($msg['sent_at']); ?></td>
                    <td>
                        <form method="GET" action="">
                            <button class="delete-button" type="submit" name="delete_id" value="<?php echo $msg['id']; ?>">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
<script>
    // Auto-hide the error popup after 2 seconds

    setTimeout(() => {
        const errorPopup = document.getElementById('error-popup');
        if (errorPopup) {
            errorPopup.classList.add('hidden'); // Add 'hidden' class for fade-out
            setTimeout(() => {
                errorPopup.style.display = 'none'; // Fully remove after fade-out
            }, 500); // Wait for the fade-out transition (500ms)
        }
    }, 2000); // Wait 2 seconds before starting fade-out
</script>

</html>
