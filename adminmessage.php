<?php
session_start();
include('connection.php');

// Ensure admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header('Location: admin_login.php');
    exit();
}

$admin_username = $_SESSION['admin_username']; // Current admin's username

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['receiver'], $_POST['message'])) {
    $receiver = $_POST['receiver'];
    $message = $_POST['message'];

    // Insert the message into the database
    $stmt = $conn->prepare("INSERT INTO messages (sender_username, receiver_username, message) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $admin_username, $receiver, $message);
    $stmt->execute();
    $stmt->close();

    // Use session to pass the success message to avoid message duplication on page refresh
    $_SESSION['success_message'] = "Message sent!";
    header("Location: adminmessage.php"); // Redirect to prevent resubmission
    exit();
}

// Handle message deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Fetch the message before deleting it
    $stmt = $conn->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->bind_param('i', $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $message_data = $result->fetch_assoc();

    // Archive the message first
    $stmt = $conn->prepare("INSERT INTO message_archive (sender_username, receiver_username, message, sent_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $message_data['sender_username'], $message_data['receiver_username'], $message_data['message'], $message_data['sent_at']);
    $stmt->execute();
    $stmt->close();

    // Now delete the message from the 'messages' table
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param('i', $delete_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to refresh the page after deleting
    header("Location: adminmessage.php");
    exit();
}

// Fetch all messages exchanged with users
$stmt = $conn->prepare("SELECT * FROM messages WHERE sender_username = ? OR receiver_username = ? ORDER BY sent_at DESC");
$stmt->bind_param('ss', $admin_username, $admin_username);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Messages</title>
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
        .message {
            max-width: 600px;
            margin: 10px auto;
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            text-align: center;
            font-size: 14px;
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
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .delete-btn {
            background-color: #e74a3b;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .delete-btn:hover {
            background-color: #c0392b;
        }
    </style>
    <script>
        // Automatically hide the success message after 2 seconds
        window.addEventListener("DOMContentLoaded", function() {
            const messageDiv = document.querySelector(".message");
            if (messageDiv) {
                setTimeout(() => {
                    messageDiv.style.display = "none";
                }, 2000);
            }
        });
    </script>
</head>
<body>
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="message"><?php echo $_SESSION['success_message']; ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <h2>Admin Message System</h2>
    <form method="POST" action="">
        <label for="receiver">Send Message To:</label>
        <input type="text" name="receiver" id="receiver" placeholder="Enter user's username" required>
        <label for="message">Message:</label>
        <textarea name="message" id="message" rows="4" cols="50" required></textarea>
        <button type="submit">Send Message</button>
        <button type="button" onclick="window.location.href='admin.php';">Back</button>

    </form>
    <hr>
    <h3>Message History</h3>
    <table>
        <thead>
            <tr>
                <th>Sender</th>
                <th>Receiver</th>
                <th>Message</th>
                <th>Sent At</th>
                <th>Action</th> <!-- New Action Column for Delete -->
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
                        <a href="adminmessage.php?delete_id=<?php echo $msg['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this message?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <p style="text-align: center;">
    <a href="archive.php" style="text-decoration: none; color: #007bff;">View Archived Messages</a>
</p>

</body>
</html>
