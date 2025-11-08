<?php
session_start();
@include '../connection/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['company_id'])) {
    $company_id = $_POST['company_id'];

    // Update the status for the account
    $stmt = $conn->prepare("UPDATE company SET status = 'Inactive' WHERE company_id = ?");
    $stmt->bind_param('i', $company_id);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>
