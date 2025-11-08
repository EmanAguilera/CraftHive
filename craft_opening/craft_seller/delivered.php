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

// Initialize the status filter (default is all orders)
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_proof'])) {
    $order_id = intval($_POST['order_id']);
    $image_name = $_FILES['proof_receipt']['name'];
    $image_tmp_name = $_FILES['proof_receipt']['tmp_name'];
    $image_folder = '../../uploaded_receipts/' . $image_name;

    // Move uploaded file to the target directory
    if (move_uploaded_file($image_tmp_name, $image_folder)) {
        // Update order with proof receipt and mark as delivered
        $update_query = "UPDATE `order` 
                         SET proof_receipt = '$image_name', 
                             status = 'Delivered' 
                         WHERE order_id = '$order_id' 
                         AND seller_id = '$seller_id'";
        
        // Execute the query and check for errors
        if (mysqli_query($conn, $update_query)) {
            $message[] = "Proof of receipt uploaded successfully. The order has been marked as Delivered.";
        } else {
            $message[] = "Failed to update order. Please try again.";
        }
    } else {
        $message[] = "Failed to upload image. Please try again.";
    }
}

// Approve the proof of receipt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_proof'])) {
    $order_id = intval($_POST['order_id']);
    
    // Update order status to approved
    $approve_query = "UPDATE `order` SET status = 'Approved' WHERE order_id = '$order_id' AND seller_id = '$seller_id'";
    if (mysqli_query($conn, $approve_query)) {
        $message[] = "Proof of receipt approved successfully.";
    } else {
        $message[] = "Failed to approve the proof of receipt.";
    }
}

// Reject the proof of receipt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_proof'])) {
    $order_id = intval($_POST['order_id']);
    
    // Update order status to rejected
    $reject_query = "UPDATE `order` SET status = 'Cancelled' WHERE order_id = '$order_id' AND seller_id = '$seller_id'";
    if (mysqli_query($conn, $reject_query)) {
        $message[] = "Proof of receipt rejected successfully.";
    } else {
        $message[] = "Failed to reject the proof of receipt.";
    }
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
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Order's Product</title>
   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <!-- Custom CSS file link -->
   <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

   <link rel="stylesheet" href="../../css/design.css">
   <link rel="stylesheet" href="additional.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* Flex container for charts */
.charts-container {
    display: flex;
    flex-direction: column; /* Stack charts vertically */
    gap: 20px; /* Space between the chart containers */
    margin-top: -10px;
    padding: 20px;
    margin-bottom: -20px;
}

/* Style for individual chart containers */
.chart-container2 {
    width: 70%; /* Make the width responsive */
    height: 450px; /* Set a base height for the chart container */
    max-width: 1000px; /* Limit the width for large screens */
    margin: 20px auto; /* Center-align the container */
    padding: 20px;
    background: linear-gradient(90deg, #8E5923, #FEDFB1);
    border: 2px solid #5C2E0A;
    border-radius: 12px 4px;
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden; /* Prevent overflow of content */
}

.chart-container2:hover {
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); /* Enhance shadow on hover */
}

/* Heading style */
.chart-container2 h3 {
    color: #5C2E0A;
    font-size: 1.9em;
    margin-bottom: 20px;
    text-transform: uppercase;
    font-weight: bold;
}

/* Chart canvas style */
.chart-container2 canvas {
    width: 100%; /* Make the canvas fill the container */
    height: 100%; /* Make the canvas fill the container */
    min-height: 200px; /* Ensure a minimum height for legibility */
    background: #FFF3DC;
    border-radius: 6px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow */
}

    </style>

</head>
<body>

<?php
if (isset($message)) {
   foreach ($message as $message) {
      echo '<div class="message"><span>'.$message.'</span> 
      <i class="fas fa-times" onclick="this.parentElement.style.display = `none`;"></i></div>';
   }
}
?>

<!-- SIDEBAR -->

<section id="sidebar">
		<a href="#" class="brand">
			<i class='bx bxs-smile'></i>
			<span class="text">CraftHive</span>
		</a>
		<ul class="side-menu top">
			<li>
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
			<li class="active">
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
					<h1>Seller's Management</h1>
					<ul class="breadcrumb">
						<li>
							<a href="ch-company.php">Seller</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="edit.php">S Management</a>
						</li>
					</ul>
				</div>
			</div>
<br>

<div class="charts-container">

<div class="chart-container2 pie-chart-container">
            <canvas id="statusChart"></canvas>
</div>
</div>

<div class="container">
   <section class="filter-container">
      <form action="" method="GET">
         <select name="status" id="statusFilter">
            <option value="">All Status</option>
            <option value="Pending" <?php echo ($status_filter === 'Pending') ? 'selected' : ''; ?>>Pending</option>
            <option value="Delivered" <?php echo ($status_filter === 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
            <option value="Approved" <?php echo ($status_filter === 'Approved') ? 'selected' : ''; ?>>Approved</option>
            <option value="Cancelled" <?php echo ($status_filter === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
         </select>
         <button type="submit">Filter</button>
      </form>
   </section>

   <section class="recent-transactions">
      <table>
         <thead>
            <th>Product Image</th>
            <th>Product Name</th>
            <th>Buyer Name</th>
            <th>Quantity</th>
            <th>Total Price</th>
            <th>Status</th>
            <th>Proof Receipt</th>
         </thead>
         <tbody>
         <?php
         // SQL Query to filter orders based on selected status
         $query = "SELECT o.order_id, o.product_name, o.buyer_name, o.quantity, o.total_price, 
                  o.status, o.proof_receipt, p.image
               FROM `order` o
               JOIN product p ON o.product_id = p.product_unique_id
               WHERE o.seller_id = '$seller_id'";

         if ($status_filter) {
             $query .= " AND o.status = '$status_filter'";
         }

         // Execute query
         $select_orders = mysqli_query($conn, $query);

         // Check if any orders exist
         if (mysqli_num_rows($select_orders) > 0) {
            while ($row = mysqli_fetch_assoc($select_orders)) {
         ?>
         
         <tr>
            <td><img src="../../uploaded_img/<?php echo htmlspecialchars($row['image']); ?>" height="100" alt="Product Image"></td>
            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
            <td><?php echo htmlspecialchars($row['buyer_name']); ?></td>
            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
            <td><?php echo htmlspecialchars($row['total_price']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td>
               <?php if (!empty($row['proof_receipt'])): ?>
                  <div class="proof-container">
                     <img src="../../uploaded_receipts/<?php echo htmlspecialchars($row['proof_receipt']); ?>" height="100" alt="Proof Receipt">
                     <div class="view-proof-text" onclick="viewProof('<?php echo htmlspecialchars($row['proof_receipt']); ?>', '<?php echo $row['order_id']; ?>')">View Proof</div>
                  </div>
               <?php else: ?>
                  <img src="../../uploaded_receipts/not_done_yet.jpg" height="100" alt="No Proof Yet">
               <?php endif; ?>
            </td>
         </tr>
         <?php
            }
         } else {
            echo "<tr><td colspan='7' class='empty'>No orders found</td></tr>";
         }
         ?>
         </tbody>
      </table>
   </section>

</div>

<!-- Modal for viewing proof receipt -->
<div id="viewProofModal" class="modal">
   <div class="modal-content">
      <div class="modal-header">
      <h2>Proof of Receipt</h2>
	   <span class="close" onclick="document.getElementById('viewProofModal').style.display='none'">×</span>
      </div>
	  <div class="modal-body">
      <img id="proofImage" src="" alt="Proof Image" class="image-preview">
      <form action="" method="POST">
         <input type="hidden" name="order_id" id="order_id">
         <button type="submit" name="approve_proof" class="approve-btn">Approve</button>
         <button type="submit" name="reject_proof" class="reject-btn">Reject</button>
      </form>
	  </div>
   </div>
</div>

<script src="../js/all_script.js"></script>
<script>
   function viewProof(proof, orderId) {
      var modal = document.getElementById("viewProofModal");
      var img = document.getElementById("proofImage");
      var orderIdInput = document.getElementById("order_id");

      img.src = "../../uploaded_receipts/" + proof;
      orderIdInput.value = orderId;

      modal.style.display = "block";
   }

   // Close modal when clicked on "x"
   document.querySelector(".close").onclick = function() {
      document.getElementById("viewProofModal").style.display = "none";
   }

   // Close modal if clicked outside the modal content
   window.onclick = function(event) {
      if (event.target == document.getElementById("viewProofModal")) {
         document.getElementById("viewProofModal").style.display = "none";
      }
   }
    // Function to generate shades of a base color
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
          const shadeFactor = 0.8 - (0.6 * i / count);  // Make the shades darker by subtracting a value from base color
        const shadeRed = Math.round(baseRgb.r * shadeFactor);
        const shadeGreen = Math.round(baseRgb.g  * shadeFactor);
         const shadeBlue = Math.round(baseRgb.b  * shadeFactor);
         colorShades.push(`rgba(${shadeRed}, ${shadeGreen}, ${shadeBlue}, 0.7)`);
    }
    return colorShades;
}
const statusData = <?php echo json_encode($status_data); ?>;
 const baseColor = "#EE7E1A";
 const colorShades = generateColorShades(Object.keys(statusData).length, baseColor);
  const statusCtx = document.getElementById('statusChart').getContext('2d');
        statusChart = new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: Object.keys(statusData),
                datasets: [{
                    data: Object.values(statusData),
                   backgroundColor: colorShades,
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
</script>

</body>
</html>