<?php
session_start();
@include '../connection/connect.php';

if (!isset($_SESSION['unique_id']) || !isset($_SESSION['company_rep_id'])) {
    header('Location: login.php');
    exit;
}

$seller_id = $_SESSION['unique_id'];

// Function to fetch order data (Modified to include seller share breakdown)
function fetchOrderData($conn, $seller_id, $timeframe = 'all') {
    $where_clause = "WHERE seller_id = '$seller_id'";

    if ($timeframe === 'yearly') {
        $where_clause .= " AND YEAR(created_at) = YEAR(CURDATE())";
    } elseif ($timeframe === 'monthly') {
         $where_clause .= " AND created_at >= DATE(NOW()) - INTERVAL 30 DAY";
    } elseif ($timeframe === 'daily') {
         $where_clause .= " AND created_at >= CURDATE() - INTERVAL (DAYOFWEEK(CURDATE())-1) DAY AND created_at < CURDATE() + INTERVAL (7-DAYOFWEEK(CURDATE())) DAY ";
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

   $seller_share_query = "SELECT 
        product_name,
        SUM(total_price) AS total_revenue,
        SUM(seller) AS total_seller_share,
        SUM(company) AS total_company_share
    FROM `order` {$where_clause}
    GROUP BY product_name
    ORDER BY product_name ASC";
    $seller_share_result = mysqli_query($conn, $seller_share_query);
     $seller_share_data = [];
      while($row = mysqli_fetch_assoc($seller_share_result)){
        $seller_share_data[] = $row;
    }

    return [
        'status_data' => $status_data,
        'product_labels' => $product_labels,
        'product_counts' => $product_counts,
        'total_revenue' => $total_revenue,
        'seller_share_data' => $seller_share_data,
    ];
}

// Fetch all product reviews for the seller
$review_query = "SELECT
                    p.product_name,
                    AVG(r.rating) AS average_rating,
                    COUNT(r.review_id) AS review_count
                FROM
                    `product` p
                LEFT JOIN
                    `reviews` r ON p.product_id = r.product_id
                WHERE
                    p.seller_id = '$seller_id'
                GROUP BY
                    p.product_name
                ORDER BY
                    p.product_name ASC";

$review_result = mysqli_query($conn, $review_query);
$product_reviews = [];

if($review_result && mysqli_num_rows($review_result) > 0){
   while($row = mysqli_fetch_assoc($review_result)){
    $product_reviews[] = $row;
  }
}
// Initial data load (all time)
$allData = fetchOrderData($conn, $seller_id);
$status_data = $allData['status_data'];
$product_labels = $allData['product_labels'];
$product_counts = $allData['product_counts'];
$total_revenue = $allData['total_revenue'];
$seller_share_data = $allData['seller_share_data'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
     <style>
         body {
             font-family: Arial, sans-serif;
             margin: 0;
             padding: 0;
             background-color: #f4f4f4;
         }

         .container {
             max-width: 1200px;
             margin: 20px auto;
             padding: 20px;
             background: white;
             border-radius: 8px;
             box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
         }

         h1 {
             text-align: center;
             color: #3498db;
         }

         .info {
             text-align: center;
             margin-bottom: 20px;
             font-size: 18px;
             color: #333;
         }

         .chart-container {
             margin: 20px 0;
             position: relative;
             text-align: center;
         }

         .pie-chart-container canvas {
             width: 300px;
             height: 300px;
             margin: 0 auto;
         }

         .chart-container canvas {
             display: block;
             max-width: 100%;
             height: auto;
         }

         .back-btn {
             display: block;
             width: 150px;
             margin: 20px auto;
             padding: 10px;
             text-align: center;
             background-color: #3498db;
             color: white;
             text-decoration: none;
             border-radius: 5px;
             font-size: 16px;
             transition: background-color 0.3s;
         }

         .back-btn:hover {
             background-color: #2980b9;
         }

          /* Buttons Styles */
        .report-buttons {
            text-align: center;
            margin-bottom: 20px;
        }

        .report-buttons button {
            padding: 10px 15px;
            margin: 0 5px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .report-buttons button:hover {
            background-color: #2980b9;
        }

        .export-buttons {
              text-align: center;
              margin-bottom: 20px;
          }

          .export-buttons button {
             padding: 10px 15px;
             margin: 0 5px;
             background-color: #27ae60;
              color: white;
              border: none;
             border-radius: 5px;
             cursor: pointer;
             transition: background-color 0.3s;
          }

          .export-buttons button:hover {
            background-color: #219050;
          }

         @media (max-width: 768px) {
             .info {
                 font-size: 16px;
             }

             .back-btn {
                 width: 100%;
             }
         }

         /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
     </style>
</head>
<body>
    <div class="container">
        <h1>Sales Report</h1>
          <div class="report-buttons">
            <button onclick="updateReport('yearly')">Yearly</button>
            <button onclick="updateReport('monthly')">Monthly</button>
            <button onclick="updateReport('daily')">Daily</button>
             <button onclick="updateReport('all')">All Time</button>
        </div>
        <p class="info"><strong>Total Revenue:</strong> $<span id="totalRevenue"><?php echo htmlspecialchars($total_revenue); ?></span></p>
        <div class="export-buttons">
          <button onclick="exportReport('yearly')">Export Yearly</button>
          <button onclick="exportReport('monthly')">Export Monthly</button>
          <button onclick="exportReport('daily')">Export This Week</button>
        </div>

        <div class="chart-container pie-chart-container">
            <canvas id="statusChart"></canvas>
        </div>

        <div class="chart-container">
            <canvas id="productChart"></canvas>
        </div>

          <?php if(!empty($product_reviews)): ?>
              <table>
                  <thead>
                      <tr>
                         <th>Product Name</th>
                         <th>Average Rating</th>
                         <th>Review Count</th>
                     </tr>
                   </thead>
                  <tbody>
                  <?php foreach($product_reviews as $review): ?>
                   <tr>
                     <td><?php echo htmlspecialchars($review['product_name']) ?></td>
                       <td><?php echo number_format((float)$review['average_rating'], 2, '.', ''); ?></td>
                       <td><?php echo htmlspecialchars($review['review_count']) ?></td>
                     </tr>
                   <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <table id="sellerShareTable">
            <thead>
                <tr>
                   <th>Product Name</th>
                    <th>Total Revenue</th>
                    <th>Seller Share</th>
                    <th>Company Share</th>
                </tr>
            </thead>
           <tbody>
             <?php if (!empty($seller_share_data)): ?>
              <?php foreach($seller_share_data as $share): ?>
                 <tr>
                   <td><?php echo htmlspecialchars($share['product_name']); ?></td>
                     <td>₱<?php echo htmlspecialchars(number_format($share['total_revenue'], 2)); ?></td>
                    <td>₱<?php echo htmlspecialchars(number_format($share['total_seller_share'], 2)); ?></td>
                    <td>₱<?php echo htmlspecialchars(number_format($share['total_company_share'], 2)); ?></td>
                  </tr>
               <?php endforeach; ?>
              <?php else: ?>
                <tr>
                 <td colspan="4">No seller share data available.</td>
               </tr>
              <?php endif; ?>
           </tbody>
        </table>

        <a href="ch-view.php" class="back-btn">Back to Orders</a>
    </div>
    <script src="../js/all_script.js"></script>
    <script>
         let statusChart;
         let productChart;
         let sellerShareTableBody = document.querySelector('#sellerShareTable tbody');

        // Function to initialize or update chart data
        function initializeCharts(statusData, productLabels, productCounts) {
            if (statusChart) {
                statusChart.destroy();
            }
            if (productChart) {
                productChart.destroy();
            }
            // Order Status Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            statusChart = new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: Object.keys(statusData),
                    datasets: [{
                        data: Object.values(statusData),
                        backgroundColor: ['#3498db', '#e74c3c', '#2ecc71', '#f1c40f', '#9b59b6'],
                        borderColor: '#fff',
                        borderWidth: 1,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            // Top 5 Products Chart
            const productCtx = document.getElementById('productChart').getContext('2d');
            productChart = new Chart(productCtx, {
                type: 'bar',
                data: {
                    labels: productLabels,
                    datasets: [{
                        label: 'Top 5 Products',
                        data: productCounts,
                        backgroundColor: ['#3498db', '#e74c3c', '#2ecc71', '#f1c40f', '#9b59b6'],
                        borderColor: '#fff',
                        borderWidth: 1,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

           initializeCharts(<?php echo json_encode($status_data); ?>, <?php echo json_encode($product_labels); ?>, <?php echo json_encode($product_counts); ?>);


         async function updateReport(timeframe) {
           try {
                const response = await fetch(`get_report_data.php?timeframe=${timeframe}`, {
                     method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                             'X-Requested-With': 'XMLHttpRequest'
                        },
                });

                if(!response.ok){
                    throw new Error(`HTTP error! status: ${response.status}`)
                 }

              const data = await response.json();

             // Update chart and total revenue
               initializeCharts(data.status_data, data.product_labels, data.product_counts);
             document.getElementById('totalRevenue').textContent = data.total_revenue;
            
            //update the table with seller share data
             updateSellerShareTable(data.seller_share_data);


           } catch(error) {
              console.error('Error fetching report data:', error);
               alert('Failed to update the report. Please try again.');
           }
         }

           // Function to update the seller share table
        function updateSellerShareTable(sellerShareData) {
           // Clear existing table rows
            sellerShareTableBody.innerHTML = '';

           if(sellerShareData && sellerShareData.length > 0){
             sellerShareData.forEach(share => {
                  const row = document.createElement('tr');
                    row.innerHTML = `
                       <td>${share.product_name}</td>
                       <td>$${parseFloat(share.total_revenue).toFixed(2)}</td>
                       <td>$${parseFloat(share.total_seller_share).toFixed(2)}</td>
                        <td>$${parseFloat(share.total_company_share).toFixed(2)}</td>
                    `;
                  sellerShareTableBody.appendChild(row);
            });
          } else {
                const row = document.createElement('tr');
               row.innerHTML = `<td colspan="4">No seller share data available.</td>`;
              sellerShareTableBody.appendChild(row);
          }

        }

         function exportReport(timeframe) {
            window.location.href = `export_report.php?timeframe=${timeframe}`;
          }
    </script>
</body>
</html>