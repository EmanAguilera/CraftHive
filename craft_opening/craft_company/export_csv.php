<?php
session_start();
include_once '../connection/connect.php';

// Check if the seller is logged in
if (!isset($_SESSION['unique_id']) || !isset($_SESSION['company_rep_id']) || !isset($_SESSION['company_email'])) {
    header('Location: login.php');
    exit;
}

// Get session variables
$rep_unique_id = $_SESSION['unique_id'];
$company_rep_id = $_SESSION['company_rep_id'];
$company_email = $_SESSION['company_email'];

// Get the commission rate from the company table.
$repIdQuery = "SELECT company_rep_id, commission_rate FROM company WHERE unique_id = ?";
$repIdStmt = $conn->prepare($repIdQuery);
$repIdStmt->bind_param("s", $rep_unique_id);
$repIdStmt->execute();
$repIdResult = $repIdStmt->get_result();

if ($repIdResult->num_rows > 0) {
    $repIdRow = $repIdResult->fetch_assoc();
    $company_rep_id = $repIdRow['company_rep_id'];
     $commission_rate = $repIdRow['commission_rate'];
} else {
    die("Error: Company Rep ID or Commission Rate not found."); // Better error message
}
// Ensure commission_rate is set and not empty. Handle potential null cases
if(empty($commission_rate) || !is_numeric($commission_rate)){
    $commission_rate = 0.2; // Default if not found or not a number, 20%
}

// SQL query to fetch order data for the CSV export
$sql = "SELECT 
        o.order_id, o.buyer_name, o.product_name, o.quantity, o.total_price, 
        o.delivery_date, o.payment, o.gcash_reference, o.approval, o.created_at,
        p.seller_name,
        (o.total_price * ?) AS commission
    FROM `order` o 
    INNER JOIN product p ON o.product_id = p.product_unique_id
    WHERE o.company_rep_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ds", $commission_rate, $company_rep_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die("Error executing query: " . $conn->error);
}


// Define CSV filename
$filename = "orders_report_" . date('Ymd_His') . ".csv";

// Set headers to download the file as CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open file in write mode
$output = fopen('php://output', 'w');

// Write CSV header row
fputcsv($output, array('Order ID', 'Buyer Name', 'Product Name', 'Quantity', 'Total Price', 'Delivery Date', 'Payment', 'Gcash Reference', 'Approval', 'Order Date', 'Seller Name', 'Commission'));

// Loop through result set and write each row to CSV
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

// Close file handle
fclose($output);

exit;
?>