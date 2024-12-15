<?php
session_start();

include('connection.php'); // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loan_id'], $_POST['status'])) {
    $loan_id = $_POST['loan_id'];
    $status = $_POST['status'];

    if ($status === 'accepted' || $status === 'rejected') {
        $query = "UPDATE loans SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            die("Prepare failed: " . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("si", $status, $loan_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "Status updated successfully. ";
                
                if ($status === 'accepted') {
                    // Retrieve the loan amount and username associated with this loan
                    $loanAmountQuery = "SELECT username, total_amount FROM loans WHERE id = ?";
                    $loanStmt = $conn->prepare($loanAmountQuery);
                    $loanStmt->bind_param("i", $loan_id);
                    $loanStmt->execute();
                    $loanResult = $loanStmt->get_result();

                    if ($loanResult->num_rows > 0) {
                        $loanData = $loanResult->fetch_assoc();
                        $username = $loanData['username'];
                        $totalAmount = (float)$loanData['total_amount'];

                        // Retrieve the current balance of the user
                        $balanceQuery = "SELECT balance FROM users WHERE username = ?";
                        $balanceStmt = $conn->prepare($balanceQuery);
                        $balanceStmt->bind_param("s", $username);
                        $balanceStmt->execute();
                        $balanceStmt->bind_result($currentBalance);
                        $balanceStmt->fetch();
                        $balanceStmt->close();

                        // Calculate the new balance by adding the loan amount to the current balance
                        $newBalance = $currentBalance + $totalAmount;

                        // Update the user's balance with the new balance
                        $updateBalanceQuery = "UPDATE users SET balance = ? WHERE username = ?";
                        $updateStmt = $conn->prepare($updateBalanceQuery);
                        $updateStmt->bind_param("ds", $newBalance, $username);
                        
                        if ($updateStmt->execute()) {
                            echo "User balance updated successfully.";

                            // Update the session balance as well
                            $_SESSION['balance'] = $newBalance;
                        } else {
                            echo "Error updating balance: " . htmlspecialchars($updateStmt->error);
                        }
                        $updateStmt->close();
                    } else {
                        echo "Loan data not found.";
                    }
                    $loanStmt->close();
                }
            } else {
                echo "No rows were updated. Please check if the loan ID is correct.";
            }
        } else {
            echo "Error executing update: " . htmlspecialchars($stmt->error);
        }

        $stmt->close();
    } else {
        echo "Invalid status.";
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
