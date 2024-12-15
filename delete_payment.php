<?php
require 'connection.php'; // Ensure the database connection is included

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['payment_id'])) {
    $payment_id = intval($_POST['payment_id']);
    
    // Start transaction
    $conn->begin_transaction();

    try {
        // Fetch the payment record
        $payment_query = "SELECT * FROM payments WHERE id = ?";
        $stmt = $conn->prepare($payment_query);
        $stmt->bind_param('i', $payment_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $payment = $result->fetch_assoc();

            // Insert into payment_archive
            $archive_query = "INSERT INTO payment_archive (name, mobile, date, payment_mode, status)
                              VALUES (?, ?, ?, ?, ?)";
            $archive_stmt = $conn->prepare($archive_query);
            $archive_stmt->bind_param(
                'sssss',
                $payment['name'],
                $payment['mobile'],
                $payment['date'],
                $payment['payment_mode'],
                $payment['status']
            );
            $archive_stmt->execute();

            // Update the status in the payments table
            $update_query = "UPDATE payments SET status = 'Archived' WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param('i', $payment_id);
            $update_stmt->execute();

            // Commit the transaction
            $conn->commit();

            // Display success message and redirect
            echo "<p style='color: green; font-size: 16px;'>Payment successfully archived. Redirecting to admin page...</p>";
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'admin.php';
                    }, 2000); // Redirect after 2 seconds
                  </script>";
        } else {
            echo "<p style='color: red; font-size: 16px;'>Payment record not found.</p>";
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "<p style='color: red; font-size: 16px;'>Failed to archive payment: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red; font-size: 16px;'>Invalid request.</p>";
}
?>
