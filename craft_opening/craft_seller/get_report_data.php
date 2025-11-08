<?php
session_start();
@include '../connection/connect.php';

if (!isset($_SESSION['unique_id']) || !isset($_SESSION['company_rep_id']) || !isset($_SESSION['seller_email'])) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit;
}

// Get session variables
$seller_id = $_SESSION['unique_id'];
$company_rep_id = $_SESSION['company_rep_id'];
$seller_email = $_SESSION['seller_email']; // Get the seller_email from the session

$seller_id = $_SESSION['unique_id'];
if (isset($_GET['timeframe']) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $timeframe = $_GET['timeframe'];

    function fetchOrderData($conn, $seller_id, $timeframe = 'all') {
        $where_clause = "WHERE seller_id = '$seller_id'";

        if ($timeframe === 'yearly') {
            $where_clause .= " AND YEAR(created_at) = YEAR(CURDATE())";
        } elseif ($timeframe === 'monthly') {
             $where_clause .= " AND created_at >= DATE(NOW()) - INTERVAL 30 DAY"; // Last 30 days
        } elseif ($timeframe === 'daily') {
            $where_clause .= " AND DATE(created_at) = CURDATE()";
        }

        $status_query = "SELECT status, COUNT(*) AS count FROM `order` {$where_clause} GROUP BY status";
        $status_result = mysqli_query($conn, $status_query);
        $status_data = [];
        while ($row = mysqli_fetch_assoc($status_result)) {
            $status_data[$row['status']] = $row['count'];
        }

        $product_query = "SELECT product_name, COUNT(*) AS count FROM `order` {$where_clause} GROUP BY product_name ORDER BY COUNT(*) DESC LIMIT 5";
        $product_result = mysqli_query($conn, $product_query);
        $product_labels = [];
        $product_counts = [];
        while ($row = mysqli_fetch_assoc($product_result)) {
            $product_labels[] = $row['product_name'];
            $product_counts[] = $row['count'];
        }

        $revenue_query = "SELECT SUM(total_price) AS total_revenue FROM `order` {$where_clause}";
        $revenue_result = mysqli_query($conn, $revenue_query);
        $revenue_row = mysqli_fetch_assoc($revenue_result);
        $total_revenue = $revenue_row['total_revenue'] ? $revenue_row['total_revenue'] : 0;

        return [
            'status_data' => $status_data,
            'product_labels' => $product_labels,
            'product_counts' => $product_counts,
            'total_revenue' => $total_revenue,
        ];
    }

    $reportData = fetchOrderData($conn, $seller_id, $timeframe);
    echo json_encode($reportData);

} else {
    echo json_encode(['error' => 'Timeframe parameter is missing or not an XMLHttpRequest']);
    exit;
}
?>