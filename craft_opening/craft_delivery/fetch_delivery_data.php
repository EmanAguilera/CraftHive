<?php
session_start();
@include '../connection/connect.php';
$delivery_id = isset($_GET['delivery_id']) ? mysqli_real_escape_string($conn, $_GET['delivery_id']) : '';
$timeframe = isset($_GET['timeframe']) ? mysqli_real_escape_string($conn, $_GET['timeframe']) : 'all';

 function fetchOrderData($conn, $delivery_id, $timeframe = 'all') {
  $where_clause = "WHERE delivery_id = '$delivery_id'";
    $dateGroup = '';
    $dateFormat = '';
    $interval = '';

    if ($timeframe === 'yearly') {
        $where_clause .= " AND YEAR(created_at) = YEAR(CURDATE())";
         $dateGroup = "YEAR(created_at)";
        $dateFormat = 'Y';
         $interval = 'year';
    } elseif ($timeframe === 'monthly') {
         $where_clause .= " AND created_at >= DATE(NOW()) - INTERVAL 30 DAY";
          $dateGroup = "DATE(created_at)";
         $dateFormat = 'Y-m';
           $interval = 'month';
    } elseif ($timeframe === 'daily') {
         $where_clause .= " AND created_at >= CURDATE() - INTERVAL (DAYOFWEEK(CURDATE())-1) DAY AND created_at < CURDATE() + INTERVAL (7-DAYOFWEEK(CURDATE())) DAY ";
          $dateGroup = "DATE(created_at)";
         $dateFormat = 'Y-m-d';
           $interval = 'day';
    }


    $status_query = "SELECT status, COUNT(*) AS count FROM `order` {$where_clause} GROUP BY status";
    $status_result = mysqli_query($conn, $status_query);
    $status_data = [];
    while ($row = mysqli_fetch_assoc($status_result)) {
        $status_data[$row['status']] = $row['count'];
    }

    $revenue_query = "SELECT SUM(shipping_fee) AS total_revenue FROM `order` {$where_clause}";
    $revenue_result = mysqli_query($conn, $revenue_query);
    $revenue_row = mysqli_fetch_assoc($revenue_result);
    $total_revenue = $revenue_row['total_revenue'] ? $revenue_row['total_revenue'] : 0;

      $delivery_share_query = "SELECT 
        product_name,
        SUM(total_price) AS total_revenue,
          SUM(shipping_fee) AS total_delivery_share
    FROM `order` {$where_clause}
    GROUP BY product_name
        ORDER BY product_name ASC
    ";
     $delivery_share_result = mysqli_query($conn, $delivery_share_query);
     $delivery_share_data = [];
      while($row = mysqli_fetch_assoc($delivery_share_result)){
        $delivery_share_data[] = $row;
    }
    // Fetch the distinct created_at dates for this timeframe
       $date_query = "SELECT DISTINCT {$dateGroup} AS created_at FROM `order` {$where_clause} ORDER BY created_at";
        $date_result = mysqli_query($conn, $date_query);
        $date_labels = [];
        while($date_row = mysqli_fetch_assoc($date_result)) {
           $date_labels[] = date($dateFormat, strtotime($date_row['created_at']));
        }


    return [
        'status_data' => $status_data,
        'total_revenue' => $total_revenue,
        'delivery_share_data' => $delivery_share_data,
          'date_labels' => $date_labels,
    ];
}

    $allData = fetchOrderData($conn, $delivery_id, $timeframe);
    $status_data = $allData['status_data'];
    $total_revenue = $allData['total_revenue'];
    $delivery_share_data = $allData['delivery_share_data'];
       $date_labels = $allData['date_labels'];

   header('Content-Type: application/json');
  echo json_encode([
        'status_data' => $status_data,
        'total_revenue' => $total_revenue,
        'delivery_share_data' => $delivery_share_data,
          'date_labels' => $date_labels,
  ]);

?>