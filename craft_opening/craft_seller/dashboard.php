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

// Query to fetch seller details for the company rep
$sellersQuery = "SELECT seller_id, fname, lname, email, phone, status, commission_rate FROM seller WHERE company_rep_id = ?";
$sellersStmt = $conn->prepare($sellersQuery);
$sellersStmt->bind_param("s", $company_rep_id);
$sellersStmt->execute();
$sellersResult = $sellersStmt->get_result();

// Fetch Commission rate
$sellerDataQuery = "SELECT commission_rate FROM seller WHERE company_rep_id = ?";
$sellerDataStmt = $conn->prepare($sellerDataQuery);
$sellerDataStmt->bind_param("s", $company_rep_id);
$sellerDataStmt->execute();
$sellerDataResult = $sellerDataStmt->get_result();
$commission_rate = ($sellerDataResult && $sellerDataRow = $sellerDataResult->fetch_assoc()) ? $sellerDataRow['commission_rate'] : 0;

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

 // Handle Timeframe selection
  $timeframe = $_GET['timeframe'] ?? 'monthly'; // Get timeframe from the URL, default to monthly
  $dateGroup = '';
  $dateFormat = '';
  $interval = '';



// Calculate average rating for products by this company
$averageRatingQuery = "
    SELECT AVG(r.rating) AS avg_rating
    FROM reviews r
    JOIN product p ON r.product_id = p.product_id
    WHERE p.company_rep_id = ?
";

$averageRatingStmt = $conn->prepare($averageRatingQuery);
$averageRatingStmt->bind_param("s", $company_rep_id);
$averageRatingStmt->execute();
$averageRatingResult = $averageRatingStmt->get_result();
$average_rating = ($averageRatingResult && $averageRatingRow = $averageRatingResult->fetch_assoc()) ? round($averageRatingRow['avg_rating'], 2) : 0;

// Average rating per seller
$avgRatingPerSellerQuery = "
    SELECT
        s.fname,
        s.lname,
        AVG(r.rating) AS avg_rating
    FROM seller s
    LEFT JOIN product p ON s.unique_id = p.seller_id
    LEFT JOIN reviews r ON p.product_id = r.product_id
    WHERE s.company_rep_id = ?
    GROUP BY s.seller_id
";
$avgRatingPerSellerStmt = $conn->prepare($avgRatingPerSellerQuery);
$avgRatingPerSellerStmt->bind_param("s", $company_rep_id);
$avgRatingPerSellerStmt->execute();
$avgRatingPerSellerResult = $avgRatingPerSellerStmt->get_result();

// Commission per seller
$commissionPerSellerQuery = "
  SELECT
    s.fname,
    s.lname,
    SUM(o.total_price * s.commission_rate) AS total_commission
FROM
    seller s
JOIN
    `order` o ON s.unique_id = o.seller_id
WHERE s.company_rep_id = ?
GROUP BY s.seller_id
";

$commissionPerSellerStmt = $conn->prepare($commissionPerSellerQuery);
$commissionPerSellerStmt->bind_param("s", $company_rep_id);
$commissionPerSellerStmt->execute();
$commissionPerSellerResult = $commissionPerSellerStmt->get_result();

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
    SUM(total_price * 0.8) AS total_seller_share, -- Assuming 80% for the seller
    SUM(total_price * 0.2) AS total_company_share  -- Assuming 20% for the company
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
    <title>Seller Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/design.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

    <script src="https://cdn.lordicon.com/lordicon.js"></script>
    <link rel="stylesheet" href="rep-dashboards.css">
    <link rel="stylesheet" href="style.css">
    <!-- Chart.js cdn link -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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
					<span class="text">Seller's Dashboard</span>
				</a>
			</li>
			<li>
				<a href="product.php">
					<i class='bx bxl-product-hunt' ></i>
					<span class="text">Seller's Product</span>
				</a>
			</li>
			<li>
				<a href="delivered.php">
					<i class='bx bxl-magento' ></i>
					<span class="text">Seller's Management </span>
				</a>
			</li>
            <li>
				<a href="review.php">
					<i class='bx bx-message' ></i>
					<span class="text">Seller's Review </span>
				</a>
			</li>
		</ul>
        <ul class="side-menu">
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
					<h1>Seller Dashboard</h1>
					<ul class="breadcrumb">
						<li>
							<a href="ch-company.php">Seller</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="dashboard.php">S Dashboard</a>
						</li>
					</ul>
				</div>

<a href="export_csv" class="btn-download add-btn">Download CSV Report</a>
</div>
<main>

<div class="container">
<section class="products">

<div class="content">
    <div class="cards">
        <div class="card">
            <div class="box">
                <h1><?php echo $total_products; ?></h1>
                <h3>Products</h3>
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
                <h2><?php echo $average_rating; ?></h2>
                <h3>Average Rating</h3>
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
    <div class="charts-container">
        <div class="chart-container2">
            <h3>Seller's Commission</h3>
            <div class="timeframe-buttons">
      <button class="timeframe-btn" onclick="setTimeframe('daily', <?php echo json_encode($seller_id); ?>)">Daily</button>
      <button class="timeframe-btn" onclick="setTimeframe('weekly', <?php echo json_encode($seller_id); ?>)">Weekly</button>
      <button class="timeframe-btn" onclick="setTimeframe('monthly', <?php echo json_encode($seller_id); ?>)">Monthly</button>
      <button class="timeframe-btn" onclick="setTimeframe('yearly', <?php echo json_encode($seller_id); ?>)">Yearly</button>
</div>
            <canvas id="commissionChart"></canvas>
        </div>
    </div>
</div>

<div class = "recent-transactions">
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
<section id="orderTableContainer" class="recent-transactions">
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
                                    <td><?php echo htmlspecialchars($row['buyer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['total_price']); ?></td>
                                    <td><?php echo htmlspecialchars($row['payment']); ?></td>
                                    <td><?php echo htmlspecialchars($row['gcash_reference']); ?></td>
                                    <td><?php echo htmlspecialchars($row['approval']); ?></td>
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
// Function to render the commission chart
function renderCommissionChart(sellerShareData, dateLabels) {
    console.log("renderCommissionChart called with data: ", sellerShareData, " and date labels: ", dateLabels);
    // Prepare the data for the chart
    const labels = sellerShareData.map(item => item.product_name);
    const sellerShares = sellerShareData.map(item => parseFloat(item.total_seller_share));

    // Generate the lighter color shades
      const baseColor = "#EE7E1A";
     const colorShades = generateColorShades(labels.length, baseColor);

      // Check if chart already exists then destroy it to prevent memory leak.
      if (Chart.getChart('commissionChart')){
           Chart.getChart('commissionChart').destroy();
       }
    // Create the chart using Chart.js
    const ctx = document.getElementById('commissionChart').getContext('2d');
    const commissionChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Seller Commission',
                data: sellerShares,
                backgroundColor: colorShades, // Use the generated shades
                borderColor:  'rgba(238, 126, 26, 1)', // Keep border as a default color
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
                    text: 'Seller Commission Breakdown'
                }
            }
        }
    });
}

 function setTimeframe(timeframe, seller_id) {
       console.log('setTimeframe called with timeframe: ' + timeframe + " and seller_id: " + seller_id);
       // Fetch the data for the selected timeframe
       $.ajax({
          url: 'fetch_commission_data.php',
          method: 'GET',
           data: { seller_id: seller_id, timeframe: timeframe },
           success: function(response) {
              // Assuming response contains the updated seller share data
              console.log("AJAX success response: ", response);
               try {
                     console.log("Parsed JSON Data:", response);
                    renderCommissionChart(response.seller_share_data, response.date_labels);
                 } catch (e) {
                      console.error("Error parsing JSON:", response);
                 }
           },
           error: function(xhr, status, error) {
                console.error("AJAX error:", status, error, xhr.responseText);
            }
      });
   }
   // Initial chart rendering with seller_share_data from PHP
  try{
      const initialData = <?php echo json_encode($seller_share_data); ?>;
       const initialDates = <?php echo json_encode([date('F Y')])?>;
        console.log("Initial Data from PHP: ", initialData);
        renderCommissionChart(initialData, initialDates);
     }catch(e){
        console.error("Error rendering chart with PHP data", e);
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