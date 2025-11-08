<?php
session_start();
include_once '../connection/connect.php';

// Check if the seller is logged in
if (!isset($_SESSION['unique_id']) || !isset($_SESSION['company_rep_id']) || !isset($_SESSION['company_email'])) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit;
}

// Get session variables
$rep_unique_id = $_SESSION['unique_id'];
$company_rep_id = $_SESSION['company_rep_id'];
$company_email = $_SESSION['company_email']; // Get the seller_email from the session

// Get session variables
$rep_unique_id = $_SESSION['unique_id'];

// Query to fetch company details including commission_rate based on unique_id from the session.
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

// Query to count total sellers for the company rep
$countQuery = "SELECT COUNT(seller_id) AS total_sellers FROM seller WHERE company_rep_id = ?";
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param("s", $company_rep_id);
$countStmt->execute();
$countResult = $countStmt->get_result();
if ($countResult) {
    $countRow = $countResult->fetch_assoc();
    $total_sellers = $countRow['total_sellers'];
} else {
    die("Error fetching count: " . $conn->error);
}

// Query to fetch seller details for the company rep
$sellersQuery = "SELECT seller_id, fname, lname, email, phone, status FROM seller WHERE company_rep_id = ?";
$sellersStmt = $conn->prepare($sellersQuery);
$sellersStmt->bind_param("s", $company_rep_id);
$sellersStmt->execute();
$sellersResult = $sellersStmt->get_result();


// Query to count total products for the company rep
$productCountQuery = "SELECT COUNT(product_id) AS total_products FROM product WHERE company_rep_id = ?";
$productCountStmt = $conn->prepare($productCountQuery);
$productCountStmt->bind_param("s", $company_rep_id);
$productCountStmt->execute();
$productCountResult = $productCountStmt->get_result();
if ($productCountResult) {
    $productCountRow = $productCountResult->fetch_assoc();
    $total_products = $productCountRow['total_products'];
} else {
    die("Error fetching product count: " . $conn->error);
}

// Query to fetch product details for the company rep
$productsQuery = "SELECT product_id, product_name, price, seller_name  FROM product WHERE company_rep_id = ?";
$productsStmt = $conn->prepare($productsQuery);
$productsStmt->bind_param("s", $company_rep_id);
$productsStmt->execute();
$productsResult = $productsStmt->get_result();


// Query to count total orders for the company rep
$orderCountQuery = "SELECT COUNT(order_id) AS total_orders FROM `order` WHERE company_rep_id = ?";
$orderCountStmt = $conn->prepare($orderCountQuery);
$orderCountStmt->bind_param("s", $company_rep_id);
$orderCountStmt->execute();
$orderCountResult = $orderCountStmt->get_result();
if ($orderCountResult) {
    $orderCountRow = $orderCountResult->fetch_assoc();
    $total_orders = $orderCountRow['total_orders'];
} else {
    die("Error fetching order count: " . $conn->error);
}

// Query to fetch order details for the company rep
$ordersQuery = "SELECT order_id, buyer_name, product_name, quantity, total_price, delivery_date, payment, gcash_reference, approval FROM `order` WHERE company_rep_id = ? ORDER BY created_at DESC LIMIT 10";
$ordersStmt = $conn->prepare($ordersQuery);
$ordersStmt->bind_param("s", $company_rep_id);
$ordersStmt->execute();
$ordersResult = $ordersStmt->get_result();

// Commission Calculations
// Total Commission for all time
$totalCommissionQuery = "SELECT SUM(total_price * ?) AS total_commission FROM `order` WHERE company_rep_id = ?";
$totalCommissionStmt = $conn->prepare($totalCommissionQuery);
$totalCommissionStmt->bind_param("ds", $commission_rate, $company_rep_id);
$totalCommissionStmt->execute();
$totalCommissionResult = $totalCommissionStmt->get_result();
$totalCommission = ($totalCommissionResult && $totalCommissionRow = $totalCommissionResult->fetch_assoc()) ? round($totalCommissionRow['total_commission'], 2) : 0;

// Daily Commission (last 24 hours)
$dailyCommissionQuery = "SELECT SUM(total_price * ?) AS daily_commission FROM `order` WHERE company_rep_id = ? AND created_at >= NOW() - INTERVAL 24 HOUR";
$dailyCommissionStmt = $conn->prepare($dailyCommissionQuery);
$dailyCommissionStmt->bind_param("ds", $commission_rate, $company_rep_id);
$dailyCommissionStmt->execute();
$dailyCommissionResult = $dailyCommissionStmt->get_result();
$dailyCommission = ($dailyCommissionResult && $dailyCommissionRow = $dailyCommissionResult->fetch_assoc()) ? round($dailyCommissionRow['daily_commission'], 2) : 0;

// Weekly Commission (last 7 days)
$weeklyCommissionQuery = "SELECT SUM(total_price * ?) AS weekly_commission FROM `order` WHERE company_rep_id = ? AND created_at >= NOW() - INTERVAL 7 DAY";
$weeklyCommissionStmt = $conn->prepare($weeklyCommissionQuery);
$weeklyCommissionStmt->bind_param("ds", $commission_rate, $company_rep_id);
$weeklyCommissionStmt->execute();
$weeklyCommissionResult = $weeklyCommissionStmt->get_result();
$weeklyCommission = ($weeklyCommissionResult && $weeklyCommissionRow = $weeklyCommissionResult->fetch_assoc()) ? round($weeklyCommissionRow['weekly_commission'], 2) : 0;

// Monthly Commission (last 30 days)
$monthlyCommissionQuery = "SELECT SUM(total_price * ?) AS monthly_commission FROM `order` WHERE company_rep_id = ? AND created_at >= NOW() - INTERVAL 30 DAY";
$monthlyCommissionStmt = $conn->prepare($monthlyCommissionQuery);
$monthlyCommissionStmt->bind_param("ds", $commission_rate, $company_rep_id);
$monthlyCommissionStmt->execute();
$monthlyCommissionResult = $monthlyCommissionStmt->get_result();
$monthlyCommission = ($monthlyCommissionResult && $monthlyCommissionRow = $monthlyCommissionResult->fetch_assoc()) ? round($monthlyCommissionRow['monthly_commission'], 2) : 0;


// --- Commission Data for Graph: Daily (Last 7 Days) ---
$dailyGraphQuery = "SELECT DATE(created_at) AS order_date, SUM(total_price * ?) AS daily_commission FROM `order` WHERE company_rep_id = ? AND created_at >= NOW() - INTERVAL 7 DAY GROUP BY DATE(created_at) ORDER BY DATE(created_at)";
$dailyGraphStmt = $conn->prepare($dailyGraphQuery);
$dailyGraphStmt->bind_param("ds", $commission_rate, $company_rep_id);
$dailyGraphStmt->execute();
$dailyGraphResult = $dailyGraphStmt->get_result();

$daily_labels = [];
$daily_data = [];
if ($dailyGraphResult) {
    while ($row = $dailyGraphResult->fetch_assoc()) {
        $daily_labels[] = $row['order_date'];
        $daily_data[] = round($row['daily_commission'], 2);
    }
}

// --- Commission Data for Graph: Weekly (Last 4 weeks) ---
$weeklyGraphQuery = "SELECT DATE_FORMAT(created_at, '%Y-%u') AS week_year, DATE_FORMAT(created_at, '%Y-%u week') AS week_label, SUM(total_price * ?) AS weekly_commission FROM `order` WHERE company_rep_id = ? AND created_at >= NOW() - INTERVAL 4 WEEK GROUP BY week_year, week_label ORDER BY week_year";
$weeklyGraphStmt = $conn->prepare($weeklyGraphQuery);
$weeklyGraphStmt->bind_param("ds", $commission_rate, $company_rep_id);
$weeklyGraphStmt->execute();
$weeklyGraphResult = $weeklyGraphStmt->get_result();

$weekly_labels = [];
$weekly_data = [];
if ($weeklyGraphResult) {
    while ($row = $weeklyGraphResult->fetch_assoc()) {
        $weekly_labels[] = $row['week_label'];
        $weekly_data[] = round($row['weekly_commission'], 2);
    }
}

// --- Commission Data for Graph: Monthly (Last 6 months) ---
$monthlyGraphQuery = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_year, DATE_FORMAT(created_at, '%b %Y') AS month_label, SUM(total_price * ?) AS monthly_commission FROM `order` WHERE company_rep_id = ? AND created_at >= NOW() - INTERVAL 6 MONTH GROUP BY month_year, month_label ORDER BY month_year";
$monthlyGraphStmt = $conn->prepare($monthlyGraphQuery);
$monthlyGraphStmt->bind_param("ds", $commission_rate, $company_rep_id);
$monthlyGraphStmt->execute();
$monthlyGraphResult = $monthlyGraphStmt->get_result();

$monthly_labels = [];
$monthly_data = [];
if ($monthlyGraphResult) {
    while ($row = $monthlyGraphResult->fetch_assoc()) {
        $monthly_labels[] = $row['month_label'];
        $monthly_data[] = round($row['monthly_commission'], 2);
    }
}

 // Handle Timeframe selection
  $timeframe = $_GET['timeframe'] ?? 'monthly'; // Get timeframe from the URL, default to monthly
  $dateGroup = '';
  $dateFormat = '';
  $interval = '';

  switch ($timeframe) {
      case 'daily':
          $dateGroup = "DATE(created_at)";
          $dateFormat = "DATE_FORMAT(created_at, '%b %d, %Y')";
            $interval = "12 MONTH";
          break;
      case 'weekly':
        $dateGroup = "YEARWEEK(created_at, 1)"; // Group by ISO week number
        $dateFormat = "CONCAT(
            DATE_FORMAT(STR_TO_DATE(CONCAT(YEAR(created_at), WEEK(created_at, 1), '1'), '%X%V%w'), '%b %d, %Y'), 
            ' - ', 
            DATE_FORMAT(STR_TO_DATE(CONCAT(YEAR(created_at), WEEK(created_at, 1), '1'), '%X%V%w') + INTERVAL 6 DAY, '%b %d, %Y')
        )";
         $interval = "12 MONTH";
        break;
      case 'monthly':
          $dateGroup = "DATE_FORMAT(created_at, '%Y-%m')";
          $dateFormat = "DATE_FORMAT(created_at, '%b %Y')";
             $interval = "12 MONTH";
           break;
           case 'yearly':
             $dateGroup = "YEAR(created_at)";
            $dateFormat = "DATE_FORMAT(created_at, '%Y')";
            $interval = "5 YEAR";
            break;
      default:
          $dateGroup = "DATE_FORMAT(created_at, '%Y-%m')";
          $dateFormat = "DATE_FORMAT(created_at, '%b %Y')";
           $interval = "12 MONTH";
  }

  // Earnings query using the dynamic date groupings
  $earningsQuery = "
      SELECT
          $dateGroup AS period,
          $dateFormat AS formatted_period,
          SUM(total_price * ?) AS total_earnings
      FROM `order`
      WHERE company_rep_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL $interval)
      GROUP BY period
      ORDER BY period ASC
  ";

  $earningsStmt = $conn->prepare($earningsQuery);
  $earningsStmt->bind_param("ds", $commission_rate, $company_rep_id);
  $earningsStmt->execute();
  $earningsResult = $earningsStmt->get_result();

  if (!$earningsResult) {
      die("Query error: " . $conn->error); // Error handling with database error
  }

  $earningsData = [];
  while ($row = $earningsResult->fetch_assoc()) {
     $earningsData[$row['formatted_period']] = round($row['total_earnings'], 2);
  }

// Fill missing periods with 0
$start = new DateTime();
$end = new DateTime();

 if ($timeframe === 'yearly') {
    $start->modify('-4 year');
 }
 else{
    $start->modify('-11 months');
 }
if ($timeframe === 'weekly'){
      $interval = new DateInterval('P1W');
}
else if ($timeframe === 'yearly'){
       $interval = new DateInterval('P1Y');
}
else{
     $interval = new DateInterval('P1M');
}

$periods = new DatePeriod($start, $interval, $end);

$formattedEarningsData = [];
foreach ($periods as $date) {
      if ($timeframe === 'weekly') {
       $weekYear = $date->format('Y-W');
        $formattedPeriodStart = $date->format('M d, Y');
         $formattedPeriodEnd = $date->modify('+6 days')->format('M d, Y');
        $formattedPeriod =  $formattedPeriodStart . ' - ' .  $formattedPeriodEnd;
           $date->modify('-6 days');
    }
     else if ($timeframe === 'yearly') {
         $formattedPeriod = $date->format('Y');
     }
    else{
        $formattedPeriod = $date->format('M Y');
        $monthYear = $date->format('Y-m');
    }
    $formattedEarningsData[$formattedPeriod] = $earningsData[$formattedPeriod] ?? 0;
}

// Fetch subscription plan from the 'company' table
$subscriptionPlanQuery = "SELECT subscription_plan FROM company WHERE company_rep_id = ?";
$subscriptionPlanStmt = $conn->prepare($subscriptionPlanQuery);
$subscriptionPlanStmt->bind_param('s', $company_rep_id);
$subscriptionPlanStmt->execute();
$subscriptionPlanResult = $subscriptionPlanStmt->get_result();
$subscription_plan = "Free";  // Default if not found


if ($subscriptionPlanResult && $subscriptionPlanResult->num_rows > 0) {
    $subscriptionPlanRow = $subscriptionPlanResult->fetch_assoc();
    $subscription_plan = $subscriptionPlanRow['subscription_plan'] ?? '14 days trial';
} else {
    echo "Error: Could not fetch subscription plan for company_rep_id: $company_rep_id<br/>";
    $subscription_plan = 'Free';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['action'])) {
    $order_id_to_update = $_POST['order_id'];
    $action = $_POST['action'];

    // Update the status
    $newApproval = ($action === 'approve') ? 'Confirm' : 'Rejected';
    $updateQuery = "UPDATE `order` SET approval = ? WHERE order_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ss", $newApproval, $order_id_to_update);

     if ($updateStmt->execute()) {
           echo "success";
    } else {
        echo "fail";
    }
      exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/design.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

    <script src="https://cdn.lordicon.com/lordicon.js"></script>
    <link rel="stylesheet" href="rep-dashboards.css">
     
    <!-- Chart.js cdn link -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"> </script>
    <style>
        .recent-transactions {
   margin-top: 0px;
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
					<span class="text">Company Dashboard</span>
				</a>
			</li>
			<li>
				<a href="description.php">
					<i class='bx bxs-shopping-bag-alt' ></i>
					<span class="text">Company Profile</span>
				</a>
			</li>
			<li>
				<a href="colleague.php">
					<i class='bx bxs-doughnut-chart' ></i>
					<span class="text">Company Colleague </span>
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
            <a href="#" class="profile">
    <img src="../uploaded_profile/CH-logo.png" alt="Profile Image">
</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Company Dashboard</h1>
					<ul class="breadcrumb">
						<li>
							<a href="ch-company.php">Company</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="dashboard.php">C Dashboard</a>
						</li>
					</ul>
				</div>

<a href="export_csv.php" class="btn-download add-btn">Download CSV Report</a>


			</div>
<main>

<div class="container">
<section class="products">

<div class="content">
    <div class="cards">
    <div class="card">
            <div class="box">
            <h1><?php echo $total_sellers; ?></h1>
                <h3>Sellers</h3>
            </div>
            <div class="icon-case">
            <lord-icon
    src="https://cdn.lordicon.com/mebvgwrs.json"
    trigger="hover"
    colors="primary:#121331,secondary:#D3AB7A,tertiary:#eee966,quaternary:#b26836,quinary:#ebe6ef"
    style="width:250px;height:250px">
</lord-icon>
            </div>
        </div>
        <div class="card">
            <div class="box">
                <h1><?php echo $total_orders; ?></h1>
                <h3>Orders</h3>
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
            <h3><?php echo $subscription_plan; ?></h3>
                <h3>Status</h3>
            </div>
            <div class="icon-case">
            <lord-icon
    src="https://cdn.lordicon.com/piolrlvu.json"
    trigger="hover"
    colors="primary:#121331,secondary:#ffc738,tertiary:#D3AB7A,quaternary:#faddd1"
    style="width:50px;height:50px">
</lord-icon>
            </div>
        </div>
        <div class="card">
            <div class="box">
            <h2>₱<?php echo $totalCommission; ?></h2>
                <h3>Total Commission</h3>
            </div>
            <div class="icon-case">
            <lord-icon
    src="https://cdn.lordicon.com/dhuliaty.json"
    trigger="hover"
     colors="primary:#121331,secondary:#ffc738,tertiary:#D3AB7A"
    style="width:50px;height:50px">
</lord-icon>
            </div>
        </div>
    </div>

    
    <div class="charts-container">
    <!-- Pie Chart for Subscription Status -->

    <!-- Bar Graph for Earnings -->
    <div class="chart-container2">
    <h3>Earning</h3>
    <div class="timeframe-buttons">
        <button class="timeframe-btn" onclick="setTimeframe('daily')">Daily</button>
        <button class="timeframe-btn" onclick="setTimeframe('weekly')">Weekly</button>
        <button class="timeframe-btn" onclick="setTimeframe('monthly')">Monthly</button>
        <button class="timeframe-btn" onclick="setTimeframe('yearly')">Yearly</button>
    </div>
    <canvas id="earningsChart"></canvas>
</div>
</div>

</div>

<section id="productTableContainer" class="recent-transactions">
  <h2>Recent Orders</h2>
  <table>
        <thead>
            <tr>
            <th>Buyer Name</th>
            <th>Product Name</th>
            <th>Total Price</th>
            <th>Payment</th>
            <th>Gcash Reference</th>
            <th>Approval</th>
            <th>Action</th>
            </tr>
        </thead>
        <tbody>
          <?php if ($ordersResult->num_rows > 0): ?>
                <?php while ($row = $ordersResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['buyer_name']; ?></td>
                                    <td><?php echo $row['product_name']; ?></td>
                                    <td><?php echo $row['total_price']; ?></td>
                                    <td><?php echo $row['payment']; ?></td>
                                    <td><?php echo $row['gcash_reference']; ?></td>
                                    <td><?php echo $row['approval']; ?></td>
                                    <td>
                                        <a href="javascript:void(0);" class="option-btn" data-id="<?php echo $row['order_id']; ?>" onclick="updateOrderStatus(this, 'approve')">
                                          <i class="fas fa-check"></i> Approve
                                          </a>
                                        <a href="javascript:void(0);" class="delete-btn" data-id="<?php echo $row['order_id']; ?>" onclick="updateOrderStatus(this, 'reject')">
                                          <i class="fas fa-trash"></i> Reject
                                        </a>
                                    </td>

                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">No Recent Orders found.</td>
                                </tr>
                            <?php endif; ?>
        </tbody>
</table>
<div id="inactivateModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Disable Account</h2>
            <span class="close" onclick="closeInactivateModal()">×</span>
        </div>
        <div class="modal-body">
            <form method="POST" action="dashboard.php">
                <input type="hidden" id="seller_id" name="seller_id">
                <div class="textarea-container">
    <textarea id="reason" name="reason" placeholder="Enter the reason" required></textarea>
                <button type="submit">Confirm</button>
                <button type="button" onclick="closeInactivateModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
</main>
 
  <script src="js/script.js"></script>
  <script>

    const earningsData = <?php echo json_encode($earningsData); ?>;

    // Format data for the earnings bar chart
    const earningsLabels = Object.keys(earningsData);
    const earningsValues = Object.values(earningsData);

    // Bar chart for earnings
    const earningsChartData = {
        labels: earningsLabels,
        datasets: [{
            label: 'Total Commission',
            data: earningsValues,
            backgroundColor: '#EE7E1A',
            borderColor: '#8E5923',
            borderWidth: 2,
            borderRadius: 6,
        }]
    };

    const earningsChartConfig = {
        type: 'bar',
        data: earningsChartData,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#8E5923',
                    },
                    grid: {
                        color: '#FEDFB1',
                    }
                },
                x: {
                    ticks: {
                        color: '#8E5923',
                    },
                    grid: {
                        color: '#FEDFB1',
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#8E5923',
                        font: {
                            family: 'Georgia',
                        }
                    }
                }
            }
        }
    };

    const earningsChart = new Chart(
        document.getElementById('earningsChart'),
        earningsChartConfig
    );

    function setTimeframe(timeframe) {
        const url = new URL(window.location.href);
        url.searchParams.set('timeframe', timeframe);
        window.location.href = url.toString(); // Refresh the page with the new timeframe
    }

    function updateOrderStatus(element, action) {
        const orderId = element.getAttribute('data-id');
        if (confirm('Are you sure you want to ' + action + ' this order?')) {
             fetch('dashboard.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'order_id=' + orderId + '&action=' + action,
            })
            .then(response => response.text())
             .then(data => {
                if (data.trim() === 'success') {
                    alert('Order ' + action + 'd successfully.');
                    window.location.reload();
                } else {
                   alert('Failed to ' + action + ' order.');
                }
            })
             .catch(error => {
                console.error('Error:', error);
               alert('An error occurred while ' + action + ' the order.');
            });
        }
    }


    function viewDetails(sellerId) {
    document.getElementById('addProductModal').style.display = 'block';

    // Hide all profile containers
    var allContainers = document.querySelectorAll('.profile-container');
    allContainers.forEach(function(container) {
        container.style.display = 'none';
    });

        // Find the profile container that matches the sellerId
        var sellerContainer = document.querySelector(`.profile-container[data-sellerid="${sellerId}"]`);
        if (sellerContainer) {
            sellerContainer.style.display = 'block';
        }
    }


    function disableAccount(element) {
        const sellerId = element.getAttribute('data-id');
        if (confirm('Are you sure you want to disable this account?')) {
          fetch('dashboard.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'seller_id=' + sellerId,
          })
            .then(response => response.text())
            .then(data => {
              if (data.trim() === 'success') {
                alert('Account disabled successfully.');
                 // Reload the current page
                window.location.reload();
              } else {
                alert('Failed to disable account.');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              alert('An error occurred while disabling the account.');
            });
        }
      }


</script>

</body>
</html>