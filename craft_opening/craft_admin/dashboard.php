<?php

session_start();
@include '../connection/connect.php';

// Check if the seller is logged in
if (!isset($_SESSION['unique_id'])) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit;
}

// Get session variables
$admin_unique_id = $_SESSION['unique_id'];

$dsn = "mysql:host=localhost;dbname=local;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, "root", "", $options);  // Create PDO object
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Query to count total company representatives
$countQuery = "SELECT COUNT(company_id) AS total_company_rep FROM company";
$countResult = mysqli_query($conn, $countQuery);

// Fetch the result of the count query
if ($countResult) {
  $countRow = mysqli_fetch_assoc($countResult);
  $total_company_rep = $countRow['total_company_rep'];
} else {
  die("Error fetching count: " . mysqli_error($conn));
}

// Query to fetch company representatives
$sql = "SELECT company_id, fname, lname, email, company_name, phone, subscription_plan, price, start_date, expiry_date, total_sellers, status, payment_status, gcash_reference_number
      FROM company";
$companyRepsResult = $conn->query($sql);

// Check if the company representatives query was successful
if (!$companyRepsResult) {
  die("Error executing query: " . $conn->error);
}

/*
$sellersQuery = "SELECT SUM(total_sellers) AS total_sellers FROM company";
$sellersResult = mysqli_query($conn, $sellersQuery);

// Fetch the result of the total sellers query
if ($sellersResult) {
  $sellersRow = mysqli_fetch_assoc($sellersResult);
  $total_sellers = $sellersRow['total_sellers'] ?? 0; // Default to 0 if null
} else {
  die("Error fetching total sellers: " . mysqli_error($conn));
}
*/

$earningsQuery = "SELECT SUM(price) AS price FROM company";
$earningsResult = mysqli_query($conn, $earningsQuery);

// Fetch the result of the total sellers query
if ($earningsResult) {
  $earningsRow = mysqli_fetch_assoc($earningsResult);
  $earnings = $earningsRow['price'] ?? 0; // Default to 0 if null
} else {
  die("Error fetching total earnings: " . mysqli_error($conn));
}

// Fetch subscription plan data
$subscriptionQuery = "SELECT subscription_plan, COUNT(*) AS count 
                    FROM company 
                    GROUP BY subscription_plan";
$subscriptionResult = mysqli_query($conn, $subscriptionQuery);

$subscriptionData = [];
while ($row = mysqli_fetch_assoc($subscriptionResult)) {
  $subscriptionData[$row['subscription_plan']] = $row['count'];
}
$timeframe = isset($_GET['timeframe']) ? $_GET['timeframe'] : 'monthly';

switch ($timeframe) {
  case 'daily':
  $dateGroup = "DATE(updated_at)"; // Group by day
  $dateFormat = "DATE_FORMAT(updated_at, '%b %d, %Y')"; // Format as readable date
  break;
      case 'weekly':
          $dateGroup = "YEARWEEK(updated_at, 1)"; // Group by ISO week number
          $dateFormat = "CONCAT(
              DATE_FORMAT(STR_TO_DATE(CONCAT(YEAR(updated_at), WEEK(updated_at, 1), '1'), '%X%V%w'), '%b %d, %Y'), 
              ' - ', 
              DATE_FORMAT(STR_TO_DATE(CONCAT(YEAR(updated_at), WEEK(updated_at, 1), '1'), '%X%V%w') + INTERVAL 6 DAY, '%b %d, %Y')
          )"; 
          break;
          case 'monthly':
              $dateGroup = "DATE_FORMAT(updated_at, '%Y-%m')"; // Group by month
              $dateFormat = "DATE_FORMAT(updated_at, '%b %Y')"; // Format as 'Nov 2024'
              break;
  case 'yearly':
      $dateGroup = "YEAR(updated_at)";
      $dateFormat = "DATE_FORMAT(updated_at, '%Y')";
      break;
  default:
      $dateGroup = "DATE_FORMAT(updated_at, '%Y-%m')";
      $dateFormat = "%b %Y";
}

$earningsQuery = "
  SELECT 
      $dateGroup AS period,
      $dateFormat AS formatted_period,
      SUM(price) AS total_earnings 
  FROM company
  WHERE updated_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
  GROUP BY period
  ORDER BY period ASC
";

$earningsResult = mysqli_query($conn, $earningsQuery);

if (!$earningsResult) {
  die("Query error: " . mysqli_error($conn));
}

$earningsData = [];
while ($row = mysqli_fetch_assoc($earningsResult)) {
  $earningsData[$row['formatted_period']] = $row['total_earnings'];
}


// Fill missing months with 0
$start = new DateTime('-11 months'); // Start from 11 months ago
$end = new DateTime(); // Current month
$interval = new DateInterval('P1M'); // Monthly interval
$periods = new DatePeriod($start, $interval, $end);

// Create formatted labels for display
$formattedEarningsData = [];
foreach ($periods as $date) {
  $monthYear = $date->format('Y-m'); // Format as YYYY-MM
  $formattedMonthYear = $date->format('M Y'); // Format as Jan 2024
  $formattedEarningsData[$formattedMonthYear] = $earningsData[$monthYear] ?? 0; // Use formatted month with fallback
}

$deliveriesQuery = "SELECT SUM(total_delivered) AS total_delivered FROM delivery WHERE status = 'Active'";
$deliveriesResult = mysqli_query($conn, $deliveriesQuery);

$total_delivered = 0; // Default to 0
if ($deliveriesResult) {
    $deliveriesRow = mysqli_fetch_assoc($deliveriesResult);
    $total_delivered = $deliveriesRow['total_delivered'] ?? 0; // Use 0 if no rows
} else {
    die("Error fetching total delivered: " . mysqli_error($conn));
}
// New Company Reps This Month
$newRepsQuery = "SELECT COUNT(company_id) AS new_reps 
                 FROM company 
                 WHERE start_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"; // Start of current month
$newRepsResult = mysqli_query($conn, $newRepsQuery);
$newRepsRow = mysqli_fetch_assoc($newRepsResult);
$new_company_reps = $newRepsRow['new_reps'] ?? 0;

// Expiring Subscriptions This Month
$expiringQuery = "SELECT COUNT(company_id) AS expiring_count 
                FROM company 
                WHERE expiry_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') 
                AND expiry_date <= LAST_DAY(CURDATE())";
$expiringResult = mysqli_query($conn, $expiringQuery);
$expiringRow = mysqli_fetch_assoc($expiringResult);
$expiring_subscriptions = $expiringRow['expiring_count'] ?? 0;

// Active Subscriptions
$activeQuery = "SELECT COUNT(company_id) AS active_count FROM company WHERE status = 'Active'";
$activeResult = mysqli_query($conn, $activeQuery);
$activeRow = mysqli_fetch_assoc($activeResult);
$active_subscriptions = $activeRow['active_count'] ?? 0;

// Pending Payments
$pendingQuery = "SELECT COUNT(company_id) AS pending_count FROM company WHERE payment_status <> 'Paid'";
$pendingResult = mysqli_query($conn, $pendingQuery);
$pendingRow = mysqli_fetch_assoc($pendingResult);
$pending_payments = $pendingRow['pending_count'] ?? 0;
// Print or use $companyReps as needed

// Output or use $companyReps as needed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Request</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/design.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

    <script src="https://cdn.lordicon.com/lordicon.js"></script>
     
    <!-- Chart.js cdn link -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"> </script>
    <link rel="stylesheet" href="dashboard.css">
     <style>

.filter-container {
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.filter-container select {
    padding: 12px 15px;
    font-size: 1.1em;
    cursor: pointer;
    border-radius: 8px;
    border: 2px solid #5D4037; /* light tan color */
    background-color: #FEE8C5;
    color: #4d3424; /* dark brown color */
    transition: border-color 0.3s ease;
    flex: 1;
    max-width: 60%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);

}

.filter-container select:focus {
   outline: none;
   border-color: #d0b079; /* gold color */
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
}

.filter-container select:hover {
    border-color: #d0b079; /* gold color */
}

.filter-container button {
    padding: 12px 25px;
    font-size: 1.1em;
    cursor: pointer;
    border-radius: 8px;
    background-color: #8B4513; /* chocolate brown */
    color: white;
    border: none;
    transition: background-color 0.3s ease, transform 0.3s ease;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
    font-weight: 500;
    border: 2px solid #5D4037; /* light tan color */

}

.filter-container button:hover {
     background-color: #653411; /* darker chocolate brown */
    transform: translateY(-2px);
}

.filter-container button:active {
    transform: translateY(1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}


.filter-section {
    display: none;
    margin-top: 10px;
    padding: 10px;
    border: 1px solid #8B4513; /* Saddle Brown border for elegance */
    background-color: #F5DEB3; /* Wheat color for a warm vintage feel */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    border-radius: 5px; /* Slight rounding for elegance */
}

.filter-section label {
    margin-right: 5px;
    color: #5D4037; /* Saddle Brown text for labels */
    font-weight: bold;
}

.filter-section select {
    margin-right: 10px;
    padding: 5px;
    border: 1px solid #8B4513; /* Saddle Brown border */
    background-color: #D2B48C; /* Tan background */
    color: #4B2E2E; /* Deep brown text for vintage contrast */
    border-radius: 3px;
}

.filter-btn {
    padding: 10px 15px;
    background-color: #A0522D; /* Sienna for a rich brown tone */
    color: #FFF8DC; /* Cornsilk color for vintage elegance */
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease; /* Smooth hover effect */
}

.filter-btn:hover {
    background-color: #8B4513; /* Saddle Brown for hover effect */
    transform: scale(1.05); /* Slight zoom for interaction feedback */
}

/* For small devices (phones, less than 600px wide) */
@media (max-width: 600px) {
    .filter-container {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }

    .filter-container select {
        max-width: 100%; /* Full width */
        font-size: 1em; /* Slightly smaller font for better fit */
    }

    .filter-container button {
        font-size: 1em; /* Match smaller screen size */
        padding: 10px;
    }

    .filter-section {
        padding: 8px; /* Reduce padding for smaller screens */
        font-size: 0.9em; /* Slightly smaller text */
    }

    .filter-section label {
        margin-right: 3px; /* Less margin for compactness */
    }

    .filter-section select {
        margin-right: 5px;
        padding: 5px; /* Compact padding */
        font-size: 0.9em;
    }
}

/* For tablets (600px to 900px wide) */
@media (min-width: 600px) and (max-width: 900px) {
    .filter-container {
        gap: 12px; /* Slightly smaller gap */
    }

    .filter-container select {
        max-width: 80%; /* Slightly reduced width for balance */
        font-size: 1em; /* Normal size */
    }

    .filter-container button {
        font-size: 1.05em; /* Balanced font size */
    }

    .filter-section {
        padding: 10px; /* Maintain a clean design */
    }
}

/* For large devices (desktops, 1200px and above) */
@media (min-width: 1200px) {
    .filter-container {
        gap: 20px; /* More space for larger screens */
    }

    .filter-container select {
        max-width: 50%; /* Align with a wider layout */
        font-size: 1.2em; /* Slightly larger for readability */
    }

    .filter-container button {
        font-size: 1.2em; /* Enhance the button's visual weight */
        padding: 15px 30px; /* Larger padding for desktop */
    }

    .filter-section {
        padding: 15px; /* Ample space for larger screens */
    }
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
				<a href="subscription.php">
					<i class='bx bxs-shopping-bag-alt' ></i>
					<span class="text">Management</span>
				</a>
			</li>
		</ul>
		<ul class="side-menu">
			<li>
				<a href="#">
					<i class='bx bxs-cog' ></i>
					<span class="text">Settings</span>
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
					<h1>Admin Dashboard</h1>
					<ul class="breadcrumb">
						<li>
							<a href="ch-company.php">Admin</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="dashboard.php">Dashboard</a>
						</li>
					</ul>
				</div>

<a href="export_csv.php" class="btn-download add-btn">Download CSV Report</a>


			</div>
<main>

<div class="container">
<section class="products">
<div class="container">
<section class="products">

<div class="content">
    <div class="cards">
    <div class="card">
        <div class="box">
            <h1><?php echo $total_company_rep; ?></h1>
            <h3>Company Reps</h3>
        </div>
        <div class="icon-case">
            <lord-icon
            src="https://cdn.lordicon.com/jzvoyjzb.json"
            trigger="hover"
            colors="primary:#121331,secondary:#ffc738,tertiary:#D3AB7A"
            style="width:50px;height:50px">
            </lord-icon>
        </div>
    </div>
        <div class="card">
            <div class="box">
                <h1><?php echo $new_company_reps; ?></h1>
                <h3>New Reps This Month</h3>
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
                   <h1><?php echo $expiring_subscriptions; ?></h1>
                   <h3>Expiring This Month</h3>
               </div>
               <div class="icon-case">
                <lord-icon
    src="https://cdn.lordicon.com/yqgsjpsy.json"
    trigger="hover"
    colors="primary:#121331,secondary:#ffc738,tertiary:#D3AB7A"
    style="width:50px;height:50px">
</lord-icon>
               </div>
           </div>

           <div class="card">
        <div class="box">
            <h1>₱<?php echo $earnings; ?></h1>
            <h3>Total Earnings</h3>
        </div>
        <div class="icon-case">
          <lord-icon
            src="https://cdn.lordicon.com/thqfiauh.json"
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
<div class="filter-container">
    <button class="filter-btn" onclick="toggleFilter()">Filter</button>
    <div class="filter-section" style="display: none;">
        <label for="status-filter">Sub Status:</label>
        <select id="status-filter">
            <option value="">All</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>

        <label for="payment-filter">Payment Status:</label>
        <select id="payment-filter">
            <option value="">All</option>
            <option value="Paid">Paid</option>
             <option value="Unpaid">Unpaid</option>
        </select>
      <button onclick="applyFilters()">Apply Filters</button>
    </div>
</div>

<table>
        <thead>
            <tr>
            <th>Company Rep</th>
            <th>Sub Status</th>
            <th>Payment Status</th>
            <th>Gcash Reference Number</th>
            <th>Start & Expiry</th>
            <th class="product-action">Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($companyRepsResult->num_rows > 0): ?>
            <?php while ($row = $companyRepsResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['fname']; ?> from <?php echo $row['company_name']; ?></td>
                                <td><?php echo $row['status']; ?></td>
                                <td><?php echo $row['payment_status']; ?></td>
                                <td><?php echo $row['gcash_reference_number']; ?></td>
                                <td><?php echo date('M j, Y', strtotime($row['start_date'])); ?> and <?php echo date('M j, Y', strtotime($row['expiry_date'])); ?></td>
                                <td class="product-action">
                                <a href="javascript:void(0);" class="option-btn" onclick="markAsPaid(<?php echo $row['company_id']; ?>)">
                        <i class="fas fa-check"></i> Covered
                    </a>
                    <a href="javascript:void(0);" class="delete-btn" data-id="<?php echo $row['company_id']; ?>" onclick="disableAccount(this)"><i class="fas fa-trash"></i> Disable</a>
                          </td>

                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No company representatives found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
        </tbody>
    </table>
</section>

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
        label: 'Earnings (in Subscription)',
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


document.addEventListener('DOMContentLoaded', function() {
function markAsPaid(companyId) {
    if (confirm("Are you sure you want to confirm that the payment is received?")) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "update_payment.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (xhr.status === 200) {
                if (xhr.responseText === "success") {
                    alert("Payment status updated to Paid.");
                    location.reload();
                } else {
                    alert("An error occurred. Please try again.");
                }
            }
        };
        xhr.send("company_id=" + companyId);
    }
}
window.markAsPaid = markAsPaid; // Make it globally accessible
});

function disableAccount(element) {
const companyId = element.getAttribute('data-id');
if (confirm("Are you sure you want to disable this account?")) {
    // Make an AJAX request to update the status
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "inactive.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (xhr.status === 200) {
            if (xhr.responseText === "success") {
                alert("Account has been successfully disabled.");
                // Optionally refresh the page or update the UI
                location.reload();
            } else {
                alert("An error occurred. Please try again.");
            }
        }
    };
    xhr.send("company_id=" + companyId);
}
}
function toggleFilter() {
         const filterSection = document.querySelector('.filter-section');
         filterSection.style.display = filterSection.style.display === 'none' ? 'block' : 'none';
     }

     function applyFilters() {
         const statusFilter = document.getElementById('status-filter').value;
         const paymentFilter = document.getElementById('payment-filter').value;

         const rows = document.querySelectorAll('#productTableContainer tbody tr');

         rows.forEach(row => {
             const statusCell = row.cells[1].textContent;
             const paymentCell = row.cells[2].textContent;

             let statusMatch = true;
             let paymentMatch = true;

             if (statusFilter && statusFilter !== 'All') {
                 statusMatch = statusCell === statusFilter;
             }
             if (paymentFilter && paymentFilter !== 'All') {
                paymentMatch = paymentCell === paymentFilter;
             }
            row.style.display = (statusMatch && paymentMatch) ? '' : 'none';
         });
     }
     
</script>

</body>
</html>