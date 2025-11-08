<?php
session_start();
@include '../connection/connect.php';

// Check if the seller is logged in
if (!isset($_SESSION['unique_id'])) {
    header('Location: login.php');
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'local');
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Fetch the data for the report
$sql = "
    SELECT 
        cra.company_id AS rep_id,
        cra.fname AS rep_fname,
        cra.lname AS rep_lname,
        cra.email AS rep_email,
        cra.company_name AS rep_company_name,
        cra.phone AS rep_phone,
        cra.status AS rep_status,
        cra.subscription_plan AS rep_subscription_plan,
        cra.price AS rep_price,
        cra.total_sellers AS rep_total_sellers,
        cra.start_date AS rep_start_date,
        cra.expiry_date AS rep_expiry_date,
        cra.img AS rep_img
    FROM 
        company cra
    WHERE 
        cra.status = 'Active'
";

$result = $conn->query($sql);

// Check if query was successful
if ($result === false) {
    die("Error fetching data: " . $conn->error);
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="company_representatives_report.csv"');

// Open PHP output stream for writing
$output = fopen('php://output', 'w');

// Write CSV headers
fputcsv($output, [
    'Representative ID', 'First Name', 'Last Name', 'Email', 'Company Name', 
    'Phone', 'Status', 'Subscription Plan', 'Price', 'Total Sellers', 
    'Start Date', 'Expiry Date', 'Profile Image'
]);

// Write data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['rep_id'], $row['rep_fname'], $row['rep_lname'], $row['rep_email'], $row['rep_company_name'], 
        $row['rep_phone'], $row['rep_status'], $row['rep_subscription_plan'], $row['rep_price'], 
        $row['rep_total_sellers'], $row['rep_start_date'], $row['rep_expiry_date'], $row['rep_img']
    ]);
}

// Close the output stream
fclose($output);

// Close the database connection
$conn->close();
exit;
?>
