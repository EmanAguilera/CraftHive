<?php
session_start();
include_once '../connection/connect.php';

// Check if the seller is logged in
if (!isset($_SESSION['unique_id']) || !isset($_SESSION['company_rep_id']) || !isset($_SESSION['seller_email'])) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit;
}

// Get session variables
$seller_id = $_SESSION['unique_id'];
$company_rep_id = $_SESSION['company_rep_id'];
$seller_email = $_SESSION['seller_email']; // Get the seller_email from the session

if (isset($_GET['timeframe'])) {
    $timeframe = $_GET['timeframe'];

    $filename = "sales_report_" . $timeframe . "_" . date("Ymd") . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    $output = fopen('php://output', 'w');

    if ($timeframe === 'yearly') {
        fputcsv($output, ['Year', 'Total Sales']);

        $sql = "SELECT YEAR(created_at) AS sales_year, SUM(total_price) AS total_sales 
                FROM `order` 
                WHERE seller_id = '$seller_id'
                GROUP BY sales_year
                ORDER BY sales_year DESC";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                fputcsv($output, [$row['sales_year'], $row['total_sales']]);
            }
        }

    } elseif ($timeframe === 'monthly') {
        fputcsv($output, ['Month Year', 'Total Sales']);

         $sql = "SELECT DATE_FORMAT(created_at, '%M %Y') AS sales_month_year, SUM(total_price) AS total_sales
                FROM `order`
                WHERE seller_id = '$seller_id'
                GROUP BY sales_month_year
                ORDER BY STR_TO_DATE(sales_month_year, '%M %Y') DESC";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                fputcsv($output, [$row['sales_month_year'], $row['total_sales']]);
            }
        }

    }  elseif ($timeframe === 'daily') {
         fputcsv($output, ['Product Name', 'Quantity', 'Total Price', 'Order Date', 'Status']);

          $where_clause = "WHERE seller_id = '$seller_id' AND created_at >= CURDATE() - INTERVAL (DAYOFWEEK(CURDATE())-1) DAY AND created_at < CURDATE() + INTERVAL (7-DAYOFWEEK(CURDATE())) DAY ";


            $sql = "SELECT product_name, quantity, total_price, created_at, status FROM `order` $where_clause";
            $result = mysqli_query($conn, $sql);

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                  fputcsv($output, [
                       $row['product_name'],
                       $row['quantity'],
                       $row['total_price'],
                       $row['created_at'],
                       $row['status']
                   ]);
            }
        }

    }

    fclose($output);
    exit;

} else {
    echo "Invalid request";
    exit;
}
?>