<?php
session_start();
@include '../connection/connect.php';

// Check if the buyer is logged in (require unique_id only)
if (!isset($_SESSION['unique_id'])) {
    header('Location: login.php');
    exit;
}

$buyer_id = $_SESSION['unique_id'];
$filter_company_name = isset($_GET['filter_company']) ? mysqli_real_escape_string($conn, $_GET['filter_company']) : '';
$filter_start_date = isset($_GET['start_date']) ? mysqli_real_escape_string($conn, $_GET['start_date']) : '';
$filter_end_date = isset($_GET['end_date']) ? mysqli_real_escape_string($conn, $_GET['end_date']) : '';
$message = [];
// Fetch previous orders for the buyer with optional filtering
$order_query = "SELECT
                    o.order_id,
                    o.product_name,
                    o.quantity,
                    o.total_price,
                    o.created_at,
                    o.status,
                    o.shipping_address,
                    o.shipping_fee,
                    o.company_rep_id,
                    o.size,
                    o.color,
                    o.shape
                FROM
                    `order` o
                     INNER JOIN `description` d ON o.company_rep_id = d.company_rep_id
                WHERE
                    o.buyer_id = '$buyer_id'
                    " . ($filter_company_name ? " AND d.company_name = '$filter_company_name' " : "") . "
                    " . ($filter_start_date ? " AND o.created_at >= '$filter_start_date 00:00:00' " : "") . "
                    " . ($filter_end_date ? " AND o.created_at <= '$filter_end_date 23:59:59' " : "") . "
                ORDER BY
                    o.created_at DESC";

$order_result = mysqli_query($conn, $order_query);

$orders = [];
if($order_result && mysqli_num_rows($order_result) > 0) {
    while($row = mysqli_fetch_assoc($order_result)) {
        $orders[] = $row;
    }
}

// Fetch all existing reviews by the current user grouped by order id.
$review_check_query = "SELECT order_id FROM `reviews` WHERE user_id = '$buyer_id'";
$review_check_result = mysqli_query($conn, $review_check_query);

$reviewed_order_ids = [];
if ($review_check_result && mysqli_num_rows($review_check_result) > 0) {
  while($row = mysqli_fetch_assoc($review_check_result)){
    $reviewed_order_ids[] = $row['order_id'];
  }
}

// Fetch list of all company_names's for the filter dropdown
$company_query = mysqli_query($conn, "SELECT DISTINCT d.company_name FROM `order` o INNER JOIN `description` d ON o.company_rep_id = d.company_rep_id WHERE o.buyer_id = '$buyer_id'");
$company_names = [];
if($company_query && mysqli_num_rows($company_query) > 0) {
    while($row = mysqli_fetch_assoc($company_query)) {
        $company_names[] = $row['company_name'];
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Order's Product</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <!-- Custom CSS file link -->
   <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

   <link rel="stylesheet" href="../../css/design.css">
   <link rel="stylesheet" href="additionals.css">

<style>
 
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
				<a href="company.php">
					<i class='bx bx-list-ol' ></i>
					<span class="text">Company List</span>
				</a>
			</li>
			<li>

				<a href="cart.php">
					<i class='bx bxs-cart-add' ></i>
					<span class="text">Customer Cart</span>
				</a>
			</li>
			<li>
				<a href="checkout.php">
					<i class='bx bxs-purchase-tag-alt' ></i>
					<span class="text">Complete Purchase </span>
				</a>
			</li>
			<li class="active">
				<a href="order.php">
					<i class='bx bxs-detail' ></i>
					<span class="text">Check Details</span>
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
            <a href="#" class="profile">
    <img src="../uploaded_profile/CH-logo.png" alt="Profile Image">
</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
                <h1>Check Details</h1>
					<ul class="breadcrumb">
						<li>
							<a href="ch-company.php">Crafthive</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="company.php">C Details</a>
						</li>
					</ul>
				</div>
			</div>
           <div class="filter-container">
              <form action="" method="get">
                   <select name="filter_company">
                       <option value="" >All Companies</option>
                         <?php foreach ($company_names as $name) : ?>
                            <option value="<?php echo htmlspecialchars($name); ?>"  <?php if($filter_company_name == $name) echo 'selected'; ?>><?php echo htmlspecialchars($name); ?></option>
                          <?php endforeach; ?>
                    </select>
                      <label for="start_date">Start Date:</label>
                   <input type="date" name="start_date" value="<?php echo htmlspecialchars($filter_start_date); ?>">
                  <label for="end_date">End Date:</label>
                  <input type="date" name="end_date" value="<?php echo htmlspecialchars($filter_end_date); ?>">
                    <button type="submit">Filter</button>
                </form>
           </div>

<section class="recent-transactions">

   <table>
      <thead>
         <th>Order ID</th>
          <th>Product Name</th>
          <th>Quantity</th>
          <th>Shipping Fee</th>
          <th>Total Price</th>
          <th>Order Date</th>
          <th>Status</th>
          <th>Shipping Address</th>
          <th>Actions</th>
      </thead>
      <tbody> 
    <?php foreach ($orders as $order) :
       ?>
        <tr>
            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
             <td><?php echo htmlspecialchars($order['product_name']); ?>'s <?php echo htmlspecialchars($order['size']); ?>, <?php echo htmlspecialchars($order['color']); ?>, <?php echo htmlspecialchars($order['shape']); ?> </td>
            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
             <td>₱<?php echo htmlspecialchars($order['shipping_fee']); ?></td>
            <td>₱<?php echo htmlspecialchars($order['total_price']); ?></td>
            <td><?php echo htmlspecialchars($order['created_at']); ?></td>
            <td><?php echo htmlspecialchars($order['status']); ?></td>
            <td><?php echo htmlspecialchars($order['shipping_address']); ?></td>
            <td>
                <?php
                    $isReviewed = in_array($order['order_id'], $reviewed_order_ids);
                    if ($order['status'] === 'Delivered') :
                        if ($isReviewed) : ?>
                            <!-- Show "Already Reviewed" alert if the review exists -->
                            <span class="alert">Already Reviewed</span>
                        <?php else : ?>
                            <!-- Show the "Add a Review" button if the order hasn't been reviewed yet -->
                            <a href="add_review.php?order_id=<?php echo $order['order_id']; ?>" class="approve-btn">Add a Review</a>
                        <?php endif; ?>
                    <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

   </table>
</section>

</div>

<script src="../js/all_script.js"></script>
</body>
</html>

