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
    die("Error: Company Rep ID or Commission Rate not found.");
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
$ordersQuery = "SELECT order_id, buyer_name, product_name, quantity, total_price, delivery_date, status FROM `order` WHERE company_rep_id = ? ORDER BY created_at DESC LIMIT 10";
$ordersStmt = $conn->prepare($ordersQuery);
$ordersStmt->bind_param("s", $company_rep_id);
$ordersStmt->execute();
$ordersResult = $ordersStmt->get_result();

// New: Fetch delivery person data
$deliveryQuery = "SELECT deliver_id, fname, lname, phone, email, status FROM delivery WHERE company_rep_id = ?";
$deliveryStmt = $conn->prepare($deliveryQuery);
$deliveryStmt->bind_param("s", $company_rep_id);
$deliveryStmt->execute();
$deliveryResult = $deliveryStmt->get_result();

// Handle Disable/Activate Delivery Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deliver_id']) && isset($_POST['action'])) {
    $deliver_id_to_update = $_POST['deliver_id'];
    $action = $_POST['action'];
    $newStatus = ($action === 'activate') ? 'Active' : 'Inactive';

    // Update the status
    $updateQuery = "UPDATE delivery SET status = ? WHERE deliver_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ss", $newStatus, $deliver_id_to_update);


    if ($updateStmt->execute()) {
           echo "success";
    } else {
        echo "fail";
    }
     exit;
}

// Handle Disable/Activate Seller Action
 if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seller_id']) && isset($_POST['action'])) {
    $seller_id_to_update = $_POST['seller_id'];
    $action = $_POST['action'];
    $newStatus = ($action === 'activate') ? 'Active' : 'Inactive';

    // Update the status
    $updateQuery = "UPDATE seller SET status = ? WHERE seller_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ss", $newStatus, $seller_id_to_update);
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
        /* Recent Transactions Section */
.recent-transactions {
   margin-top: 0px;
}
        </style>

</head>
<body>

<section id="sidebar">
		<a href="#" class="brand">
			<i class='bx bxs-smile'></i>
			<span class="text">CraftHive</span>
		</a>
		<ul class="side-menu top">
			<li>
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
			<li class="active">
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
					<h1>Company Colleague</h1>
					<ul class="breadcrumb">
						<li>
							<a href="ch-company.php">Company</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="dashboard.php">C Colleague</a>
						</li>
					</ul>
				</div>

<a href="export_company_rep_id.php" class="btn-download add-btn">Download Company Rep ID</a>

	</div>
<main>
    
<section id="productTableContainer" class="recent-transactions">

    <h2>List of Delivery Persons</h2>
    <table>
        <thead>
            <tr>
                <th>Delivery ID</th>
                <th>Delivery Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($deliveryResult->num_rows > 0): ?>
                <?php while ($row = $deliveryResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['deliver_id']; ?></td>
                        <td><?php echo $row['fname'] . ' ' . $row['lname']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['phone']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                         <td>
                            <a href="javascript:void(0);" class="option-btn" data-id="<?php echo $row['deliver_id']; ?>" onclick="updateDeliveryStatus(this, 'activate')">
                                 <i class="fas fa-check"></i> Activate
                             </a>
                             <a href="javascript:void(0);" class="delete-btn" data-id="<?php echo $row['deliver_id']; ?>" onclick="updateDeliveryStatus(this, 'disable')">
                                <i class="fas fa-trash"></i> Disable
                            </a>
                         </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No delivery persons found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>List of Sellers</h2>
        <table>
            <thead>
                <tr>
                    <th>Seller ID</th>
                    <th>Seller Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
             <tbody>
                <?php if ($sellersResult->num_rows > 0): ?>
                    <?php while ($row = $sellersResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['seller_id']; ?></td>
                            <td><?php echo $row['fname'] . ' ' . $row['lname']; ?></td>
                             <td><?php echo $row['email']; ?></td>
                             <td><?php echo $row['phone']; ?></td>
                            <td><?php echo $row['status']; ?></td>
                           <td>
                                <a href="javascript:void(0);" class="option-btn" data-id="<?php echo $row['seller_id']; ?>" onclick="updateSellerStatus(this, 'activate')">
                                 <i class="fas fa-check"></i> Activate
                                </a>
                                  <a href="javascript:void(0);" class="delete-btn" data-id="<?php echo $row['seller_id']; ?>" onclick="updateSellerStatus(this, 'disable')">
                                  <i class="fas fa-trash"></i> Disable
                                  </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No sellers found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

      <h2>List of Products</h2>
         <table>
            <thead>
                <tr>
                  <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Price</th>
                     <th>Seller Name</th>
                </tr>
            </thead>
             <tbody>
                <?php if ($productsResult->num_rows > 0): ?>
                    <?php while ($row = $productsResult->fetch_assoc()): ?>
                        <tr>
                          <td><?php echo $row['product_id']; ?></td>
                            <td><?php echo $row['product_name']; ?></td>
                             <td><?php echo $row['price']; ?></td>
                             <td><?php echo $row['seller_name']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

  </main>

  <script src="js/script.js"></script>
      <script>
     function updateSellerStatus(element, action) {
        const sellerId = element.getAttribute('data-id');
        if (confirm('Are you sure you want to ' + action + ' this seller account?')) {
            fetch('colleague.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'seller_id=' + sellerId + '&action=' + action,
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === 'success') {
                    alert('Seller account ' + action + 'd successfully.');
                    window.location.reload();
                } else {
                    alert('Failed to ' + action + ' seller account.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while ' + action + ' the seller account.');
            });
        }
    }

    function updateDeliveryStatus(element, action) {
        const deliverId = element.getAttribute('data-id');
         if (confirm('Are you sure you want to ' + action + ' this delivery person account?')) {
            fetch('colleague.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'deliver_id=' + deliverId + '&action=' + action,
            })
            .then(response => response.text())
             .then(data => {
                if (data.trim() === 'success') {
                    alert('Delivery person account ' + action + 'd successfully.');
                    window.location.reload();
                } else {
                    alert('Failed to ' + action + ' delivery person account.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
               alert('An error occurred while ' + action + ' the delivery person account.');
            });
        }
    }
    </script>

</body>
</html>