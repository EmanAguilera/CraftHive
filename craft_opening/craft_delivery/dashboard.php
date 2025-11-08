<?php
session_start();
@include '../connection/connect.php';

// Check if the delivery person is logged in
if (!isset($_SESSION['unique_id']) || !isset($_SESSION['company_rep_id']) || !isset($_SESSION['delivery_email']) ) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit;
}

// Get session variables
$delivery_id = $_SESSION['unique_id'];

$message = [];
// Total Shipping Fees
$totalShippingQuery = "SELECT SUM(shipping_fee) AS total_shipping_fee FROM `order` WHERE delivery_id = ?";
$totalShippingStmt = $conn->prepare($totalShippingQuery);
$totalShippingStmt->bind_param("s", $delivery_id);
$totalShippingStmt->execute();
$totalShippingResult = $totalShippingStmt->get_result();
$total_shipping_fee = ($totalShippingResult && $totalShippingRow = $totalShippingResult->fetch_assoc()) ? round($totalShippingRow['total_shipping_fee'], 2) : 0;

// Total Delivered Orders
$totalDeliveredQuery = "SELECT COUNT(order_id) AS total_delivered FROM `order` WHERE delivery_id = ? AND status = 'Delivered'";
$totalDeliveredStmt = $conn->prepare($totalDeliveredQuery);
$totalDeliveredStmt->bind_param("s", $delivery_id);
$totalDeliveredStmt->execute();
$totalDeliveredResult = $totalDeliveredStmt->get_result();
$total_delivered = ($totalDeliveredResult && $totalDeliveredRow = $totalDeliveredResult->fetch_assoc()) ? $totalDeliveredRow['total_delivered'] : 0;

// Total Pending Orders
$totalPendingQuery = "SELECT COUNT(order_id) AS total_pending FROM `order` WHERE delivery_id = ? AND status != 'Delivered'";
$totalPendingStmt = $conn->prepare($totalPendingQuery);
$totalPendingStmt->bind_param("s", $delivery_id);
$totalPendingStmt->execute();
$totalPendingResult = $totalPendingStmt->get_result();
$total_pending = ($totalPendingResult && $totalPendingRow = $totalPendingResult->fetch_assoc()) ? $totalPendingRow['total_pending'] : 0;

// Query to fetch order details for the company rep
$ordersQuery = "SELECT order_id, buyer_name, product_name, quantity, total_price, delivery_date, payment, gcash_reference, approval, created_at, shipping_fee FROM `order` WHERE delivery_id = ? ORDER BY created_at DESC LIMIT 10";
$ordersStmt = $conn->prepare($ordersQuery);
$ordersStmt->bind_param("s", $delivery_id);
$ordersStmt->execute();
$ordersResult = $ordersStmt->get_result();

 // Function to fetch order data (Modified to include delivery share breakdown)
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
        $where_clause .= " AND created_at >= CURDATE() - INTERVAL (DAYOFWEEK(CURDATE())-1) DAY AND created_at < CURDATE() + INTERVAL (7-DAYOFWEEK(CURDATE())) DAY";
        $dateGroup = "DATE(created_at)";
        $dateFormat = 'Y-m-d';
        $interval = 'day';
    } else {
        $dateGroup = "created_at"; // Fallback for 'all'
    }
    
    $date_query = "SELECT DISTINCT {$dateGroup} AS date_group FROM `order` {$where_clause} ORDER BY date_group ASC";
    $date_result = mysqli_query($conn, $date_query);
    
    $date_labels = [];
    while ($date_row = mysqli_fetch_assoc($date_result)) {
        $date_labels[] = date($dateFormat, strtotime($date_row['date_group']));
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
// Calculate average delivery time
$averageTimeQuery = "
    SELECT
        o.product_name,
        AVG(TIMESTAMPDIFF(MINUTE, o.created_at, o.updated_at)) AS avg_delivery_time_minutes
    FROM `order` o
    WHERE o.delivery_id = ? AND o.status = 'Delivered'
    GROUP BY o.product_name
    ORDER BY avg_delivery_time_minutes DESC
     LIMIT 5
";
$averageTimeStmt = $conn->prepare($averageTimeQuery);
$averageTimeStmt->bind_param("s", $delivery_id);
$averageTimeStmt->execute();
$averageTimeResult = $averageTimeStmt->get_result();
$average_delivery_times = [];
if ($averageTimeResult && mysqli_num_rows($averageTimeResult) > 0){
     while ($row = mysqli_fetch_assoc($averageTimeResult)) {
        $average_delivery_times[] = $row;
    }
}
// Initial data load (all time)
$allData = fetchOrderData($conn, $delivery_id);
$status_data = $allData['status_data'];
$total_revenue = $allData['total_revenue'];
$delivery_share_data = $allData['delivery_share_data'];
 $date_labels = $allData['date_labels'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/design.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

    <script src="https://cdn.lordicon.com/lordicon.js"></script>
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="rep-dashboards.css">
    <link rel="stylesheet" href="style.css">
    <!-- Chart.js cdn link -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
      <style>
    .recent-transactions {
   margin-top: 0px;
}

.container .content .cards .card h3 {
    font-size: 15px;
    margin: 5px 0;
    text-transform: uppercase;
}

.container .content .cards .card h1 {
    font-size: 30px;
    font-weight: bold;
    margin: 0;
}
        </style>
</head>
<body>

<!-- SIDEBAR -->

<section id="sidebar">
		<a href="#" class="brand">
			<i class='bx bxs-smile'></i>
			<span class="text">CraftHive</span>
		</a>
		<ul class="side-menu top">
			<li class="active">
				<a href="dashboard.php">
					<i class='bx bxs-dashboard' ></i>
					<span class="text">Dashboard</span>
				</a>
			</li>
			<li>
				<a href="pending.php">
					<i class='bx bxs-shopping-bag-alt' ></i>
					<span class="text">Proof Receipt</span>
				</a>
			</li>
			<li>
				<a href="delivered.php">
					<i class='bx bxs-doughnut-chart' ></i>
					<span class="text">Management </span>
				</a>
			</li>
			<li>
				<a href="commission.php">
					<i class='bx bxs-group' ></i>
					<span class="text">Commission</span>
				</a>
			</li>
		</ul>
        <ul class="side-menu">
			<li>
				<a href="subscription.php">
					<i class='bx bxs-cog' ></i>
					<span class="text">Subscription</span>
				</a>
			</li>
			<li>
				<a href="logout.php" class="logout">
					<i class='bx bxs-log-out-circle' ></i>
					<span class="text">Logout</span>
				</a>
			</li>
		</ul>
	</section>
	<!-- SIDEBAR -->

	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu' ></i>
			<a href="#" class="nav-link">Categories</a>
			<form action="#">
				<div class="form-input">
					<input type="search" placeholder="Search...">
					<button type="submit" class="search-btn"><i class='bx bx-search' ></i></button>
				</div>
			</form>
			<a href="#" class="notification">
				<i class='bx bxs-bell' ></i>
				<span class="num">8</span>
			</a>
<!-- HTML to display the image -->
<a href="#" class="profile">
    <img src="../uploaded_profile/CH-logo.png" alt="Profile Image">
</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Delivery Dashboard</h1>
					<ul class="breadcrumb">
						<li>
							<a href="ch-company.php">Delivery</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="dashboard.php">Dashboard</a>
						</li>
					</ul>
				</div>

</div>
<main>
<div class="container">
   <section class="products">
        <div class="content">
             <div class="cards">
                <div class="card">
                    <div class="box">
                        <h1>₱<?php echo $total_shipping_fee; ?></h1>
                        <h3>Total Shipping Fee</h3>
                    </div>
                    <div class="icon-case">
                       <lord-icon
                            src="https://cdn.lordicon.com/jtiihjyw.json"
                           trigger="hover"
                            style="width:250px;height:250px">
                         </lord-icon>
                  </div>
                </div>

                 <div class="card">
                     <div class="box">
                         <h1><?php echo $total_delivered; ?></h1>
                         <h3>Delivered Orders</h3>
                     </div>
                     <div class="icon-case">
            <lord-icon
                src="https://cdn.lordicon.com/amfpjnmb.json"
                trigger="hover"
                colors="primary:#121331,secondary:#D3AB7A,tertiary:#3a3347, quaternary:#ffc738,quinary:#C18F5A"
                style="width:50px;height:50px">
            </lord-icon>
            </div>
  
                  </div>
                   <div class="card">
                       <div class="box">
                           <h1><?php echo $total_pending; ?></h1>
                            <h3>Pending Orders</h3>
                       </div>
                       <div class="icon-case">
                    <lord-icon
                        src="https://cdn.lordicon.com/okdadkfx.json"
                          trigger="hover"
                             colors="primary:#242424,secondary:#e8b730"
                            style="width:50px;height:50px">
                        </lord-icon>
                      </div>
                 </div>
                   <div class="card">
                    <div class="box">
                         <h1>₱<?php echo number_format($total_revenue,2); ?></h1>
                        <h3>Total Revenue</h3>
                    </div>
                     <div class="icon-case">
                        <lord-icon
                              src="https://cdn.lordicon.com/dhuliaty.json"
                            trigger="hover"
                             colors="primary:#242424,secondary:#ffc738"
                            style="width:50px;height:50px">
                        </lord-icon>
                    </div>
                  </div>

            </div>

      <div class="charts-container">
         <div class="chart-container2">
              <h3>Delivery Commission</h3>
               <div class="timeframe-buttons">
                   <button class="timeframe-btn" onclick="setTimeframe('daily', <?php echo json_encode($delivery_id); ?>)">Daily</button>
                  <button class="timeframe-btn" onclick="setTimeframe('weekly', <?php echo json_encode($delivery_id); ?>)">Weekly</button>
                  <button class="timeframe-btn" onclick="setTimeframe('monthly', <?php echo json_encode($delivery_id); ?>)">Monthly</button>
                  <button class="timeframe-btn" onclick="setTimeframe('yearly', <?php echo json_encode($delivery_id); ?>)">Yearly</button>
                 </div>
            <canvas id="deliveryChart"></canvas>
          </div>
     </div>
<div class = "recent-transactions">
        <table id="deliveryShareTable">
            <thead>
                <tr>
                   <th>Product Name</th>
                   <th>Total Revenue</th>
                   <th>Delivery Share</th>
                </tr>
            </thead>
           <tbody>
              <?php if (!empty($delivery_share_data)): ?>
                <?php foreach($delivery_share_data as $share): ?>
                 <tr>
                   <td><?php echo htmlspecialchars($share['product_name']); ?></td>
                    <td>₱<?php echo htmlspecialchars(number_format($share['total_revenue'], 2)); ?></td>
                    <td>₱<?php echo htmlspecialchars(number_format($share['total_delivery_share'], 2)); ?></td>
                 </tr>
               <?php endforeach; ?>
                 <?php else: ?>
                  <tr>
                     <td colspan="3">No delivery share data available.</td>
                  </tr>
                 <?php endif; ?>
           </tbody>
        </table>
   <section id="orderTableContainer" class="recent-transactions">
  <h2>Average Delivery Times</h2>
  <table>
        <thead>
            <tr>
            <th>Product Name</th>
            <th>Average Delivery Time (minutes)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($average_delivery_times)): ?>
                <?php foreach ($average_delivery_times as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars(round($row['avg_delivery_time_minutes'], 2)); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
              <tr>
                 <td colspan="2">No average delivery time data available.</td>
              </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  <section id="orderTableContainer" class="recent-transactions">
  <h2>Recent Orders</h2>
  <table>
        <thead>
            <tr>
            <th>Buyer Name</th>
            <th>Product Name</th>
            <th>Total Price</th>
              <th>Shipping Fee</th>
            <th>Payment</th>
             <th>Gcash Reference</th>
             <th>Approval</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($ordersResult->num_rows > 0): ?>
                <?php while ($row = $ordersResult->fetch_assoc()): ?>
                     <tr>
                         <td><?php echo htmlspecialchars($row['buyer_name']); ?></td>
                         <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                         <td>₱<?php echo htmlspecialchars($row['total_price']); ?></td>
                          <td>₱<?php echo htmlspecialchars($row['shipping_fee']); ?></td>
                        <td><?php echo htmlspecialchars($row['payment']); ?></td>
                         <td><?php echo htmlspecialchars($row['gcash_reference']); ?></td>
                        <td><?php echo htmlspecialchars($row['approval']); ?></td>
                    </tr>
                 <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                         <td colspan="7">No Recent Orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
   </section>
        </div>
    </div>
</main>
    <script src="../js/all_script.js"></script>
     <script>
// Function to generate lighter shades of a base color
function generateColorShades(count, baseColor) {
    const hexToRgb = (hex) => {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    };
     const baseRgb = hexToRgb(baseColor);
     if (!baseRgb) {
        console.error("Invalid base color provided");
        return [];
    }
    const colorShades = [];
    for (let i = 0; i < count; i++) {
        // Create a brighter shade by increasing the RGB values towards 255
        const shadeFactor = 0.2 + (0.8 * i / count);
        const shadeRed = Math.round(baseRgb.r + (255 - baseRgb.r) * shadeFactor);
        const shadeGreen = Math.round(baseRgb.g + (255 - baseRgb.g) * shadeFactor);
        const shadeBlue = Math.round(baseRgb.b + (255 - baseRgb.b) * shadeFactor);

        colorShades.push(`rgba(${shadeRed}, ${shadeGreen}, ${shadeBlue}, 0.7)`);
    }
    return colorShades;
}
    function renderDeliveryChart(deliveryShareData, dateLabels) {
       console.log("renderDeliveryChart called with data: ", deliveryShareData, " and date labels: ", dateLabels);
        const labels = deliveryShareData.map(item => item.product_name);
       const deliveryShares = deliveryShareData.map(item => parseFloat(item.total_delivery_share));

           const baseColor = "#EE7E1A";
        const colorShades = generateColorShades(labels.length, baseColor);
     // Check if chart already exists then destroy it to prevent memory leak.
    if (Chart.getChart('deliveryChart')){
           Chart.getChart('deliveryChart').destroy();
       }
    const ctx = document.getElementById('deliveryChart').getContext('2d');
    const deliveryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Delivery Share',
                data: deliveryShares,
                 backgroundColor: colorShades,
                  borderColor: 'rgba(238, 126, 26, 1)', // Default border color
                borderWidth: 1
            }]
        },
        options: {
           responsive: true,
             scales: {
                x: {
                    title:{
                         display: true,
                         text: dateLabels.join(', ')
                       }
                },
                y: {
                    beginAtZero: true,
                     ticks: {
                         callback: function(value, index, values) {
                              return value.toLocaleString("en-US", {
                                 style: "currency",
                                   currency: "PHP"
                               });
                           }
                     }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Delivery Share Breakdown'
                }
            }
        }
    });
}
    function setTimeframe(timeframe, delivery_id) {
      console.log('setTimeframe called with timeframe: ' + timeframe + " and delivery_id: " + delivery_id);
       // Fetch the data for the selected timeframe
       $.ajax({
          url: 'fetch_delivery_data.php',
          method: 'GET',
           data: { delivery_id: delivery_id, timeframe: timeframe },
           success: function(response) {
              // Assuming response contains the updated seller share data
              console.log("AJAX success response: ", response);
               try {
                     console.log("Parsed JSON Data:", response);
                    renderDeliveryChart(response.delivery_share_data, response.date_labels);
                 } catch (e) {
                      console.error("Error parsing JSON:", response);
                 }
           },
           error: function(xhr, status, error) {
                console.error("AJAX error:", status, error, xhr.responseText);
            }
      });
   }
     // Initial chart rendering with delivery_share_data from PHP
   try{
        const initialData = <?php echo json_encode($delivery_share_data); ?>;
        const initialDates = <?php echo json_encode($date_labels); ?>;
        console.log("Initial Data from PHP: ", initialData);
        renderDeliveryChart(initialData, initialDates);
     }catch(e){
        console.error("Error rendering chart with PHP data", e);
     }
</script>
</body>
</html>