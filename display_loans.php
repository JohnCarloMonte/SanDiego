<?php
function displayLoansByStatus($conn, $status) {
    $loan_query = "SELECT * FROM loans WHERE status = '$status'";
    $loan_result = $conn->query($loan_query);
    while ($loan = $loan_result->fetch_assoc()) {
        echo "<div class='loan-item'>{$loan['username']} - Amount: {$loan['loan_amount']}, Total: {$loan['total_amount']}</div>";
    }
}
?>