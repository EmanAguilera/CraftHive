<?php
session_start();
include_once '../connection/connect.php';

// Check if the seller is logged in
if (!isset($_SESSION['unique_id']) || !isset($_SESSION['company_rep_id']) || !isset($_SESSION['company_email'])) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit;
}

// Get the company_rep_id from session
$rep_unique_id = $_SESSION['unique_id'];


// Fetch company_rep_id from the database
$repIdQuery = "SELECT company_rep_id FROM company WHERE unique_id = ?";
$repIdStmt = $conn->prepare($repIdQuery);
$repIdStmt->bind_param("s", $rep_unique_id);
$repIdStmt->execute();
$repIdResult = $repIdStmt->get_result();

if ($repIdResult->num_rows > 0) {
    $repIdRow = $repIdResult->fetch_assoc();
    $company_rep_id = $repIdRow['company_rep_id'];

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="company_rep_id.csv"');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Add CSV header
    fputcsv($output, ['Company Rep ID']);

    // Add data row
    fputcsv($output, [$company_rep_id]);

    // Close the output stream
    fclose($output);
}
else{
    die("Company Rep ID not found in the database.");
}
exit;
?>