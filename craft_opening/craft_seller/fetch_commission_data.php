<?php
// fetch_commission_data.php
session_start();
@include '../connection/connect.php';

// This is CRUCIAL - MUST BE FIRST
header('Content-Type: application/json');

if (!isset($_GET['seller_id'])) {
    error_log("Error: seller_id not provided in fetch_commission_data.php");
    echo json_encode([]); // Return empty array if seller_id is not provided
    exit;
}

$seller_id = $_GET['seller_id'];
$timeframe = $_GET['timeframe'] ?? 'all';

$where_clause = "WHERE seller_id = '$seller_id'";
$date_labels = [];


if ($timeframe === 'yearly') {
     $where_clause .= " AND YEAR(created_at) = YEAR(CURDATE())";
        //Add some extra logging when the timeframe is 'yearly' and the seller_id is '159191419'
      if ($seller_id == 159191419) {
          error_log("Debugging Yearly - Seller ID: " . $seller_id);
      }
       $date_labels[] = date("Y");
} elseif ($timeframe === 'monthly') {
    $where_clause .= " AND created_at >= DATE(NOW()) - INTERVAL 30 DAY";
     $date_labels[] =  date('F Y');

} elseif ($timeframe === 'daily') {
     $where_clause .= " AND created_at >= CURDATE() - INTERVAL (DAYOFWEEK(CURDATE())-1) DAY AND created_at < CURDATE() + INTERVAL (7-DAYOFWEEK(CURDATE())) DAY";
       $start_date = date('Y-m-d', strtotime('this week',));
      $end_date = date('Y-m-d', strtotime('this week + 6 days'));
     $date_labels[] =  $start_date . ' - ' . $end_date;
}elseif($timeframe === 'weekly'){
     $where_clause .= " AND created_at >= CURDATE() - INTERVAL (DAYOFWEEK(CURDATE())-1) DAY AND created_at < CURDATE() + INTERVAL (7-DAYOFWEEK(CURDATE())) DAY ";
    $start_date = date('Y-m-d', strtotime('this week',));
    $end_date = date('Y-m-d', strtotime('this week + 6 days'));
    $date_labels[] =  $start_date . ' - ' . $end_date;
}

// Query to get the seller's share for each product
$query = "SELECT product_name,
                 SUM(total_price) AS total_revenue,
                 SUM(total_price * 0.8) AS total_seller_share,
                 SUM(total_price * 0.2) AS total_company_share
          FROM `order` $where_clause
          GROUP BY product_name
          ORDER BY product_name ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    // Log the error message
    error_log("Database error in fetch_commission_data.php: " . mysqli_error($conn));
    echo json_encode([]); // Return empty array and log error
    exit;
}

$seller_share_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $seller_share_data[] = $row;
}

if (empty($seller_share_data)) {
    error_log("Warning: No data returned for seller_id: $seller_id, timeframe: $timeframe");
}

$response = [
    'seller_share_data' => $seller_share_data,
     'date_labels' => $date_labels
    ];
echo json_encode($response); // Send back as JSON
?>