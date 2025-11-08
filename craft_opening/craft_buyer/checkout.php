<?php
session_start();
@include '../connection/connect.php';

// Check if the buyer is logged in (require unique_id only)
if (!isset($_SESSION['unique_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_POST['order_btn'])) {
    $buyer_id = $_SESSION['unique_id'];
    $payment = $_POST['payment'];
    $flat = $_POST['flat'];
    $street = $_POST['street'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $country = $_POST['country'];
      $gcash_reference = ($payment === 'g cash') ? mysqli_real_escape_string($conn, $_POST['gcash_reference']) : null;

    // Combine shipping address
    $shipping_address = "$flat, $street, $city, $state, $country";
     // Get company_rep_id from URL parameter, if it exists
     $company_rep_id = isset($_GET['company_rep_id']) ? mysqli_real_escape_string($conn, $_GET['company_rep_id']) : null;


    // Query to fetch cart items specific to the buyer and fetch buyer's name, also filter by company_rep_id
    $cart_query = mysqli_query($conn, "
        SELECT c.*, b.fname, b.lname, c.company_rep_id, c.seller_name
        FROM `cart` c
        JOIN `buyer` b ON c.buyer_id = b.unique_id 
        WHERE c.buyer_id = '$buyer_id'
          ".($company_rep_id ? " AND c.company_rep_id = '$company_rep_id' " : "" )."
    ") or die('Query failed');
  
       if (mysqli_num_rows($cart_query) > 0) {
         while ($product_item = mysqli_fetch_assoc($cart_query)) {
            $product_id = $product_item['product_id'];
             $product_name = $product_item['product_name'];
            $quantity = $product_item['quantity'];
            $price = $product_item['price'];
             $shipping_fee = $product_item['shipping_fee'];
            $total_price = ($price * $quantity) + $shipping_fee;
            $seller_id = $product_item['seller_id'];
            $delivery_id = $product_item['delivery_id'];
            $delivery_name = $product_item['delivery_name'];
             $company_rep_id = $product_item['company_rep_id'];
               $seller_name = $product_item['seller_name'];
              $size = $product_item['size'];
             $color = $product_item['color'];
              $shape = $product_item['shape'];
               $buyer_name = $product_item['fname'] . ' ' . $product_item['lname'];
                  // Fetch current stock from product table
                   $product_check_query = mysqli_query($conn, "SELECT stocks FROM `product` WHERE product_unique_id = '$product_id'") or die('product fetch query failed');

                    if ($product_check_query && mysqli_num_rows($product_check_query) > 0) {
                        $product_data = mysqli_fetch_assoc($product_check_query);
                           $current_stock = $product_data['stocks'];

                           if($current_stock >= $quantity){
                              $new_stock = $current_stock - $quantity;
                              mysqli_query($conn,"UPDATE `product` SET stocks = '$new_stock' WHERE product_unique_id = '$product_id'");
                                  $order_query = mysqli_query($conn, "INSERT INTO `order` 
                                    (buyer_id, product_id, product_name, seller_id, buyer_name, quantity, total_price, shipping_address, payment, status, gcash_reference, delivery_id, delivery_name, seller_name, company_rep_id, size, color, shape, shipping_fee) 
                                     VALUES 
                                  ('$buyer_id', '$product_id', '$product_name', '$seller_id', '$buyer_name', '$quantity', '$total_price', '$shipping_address', '$payment', 'Pending', '$gcash_reference', '$delivery_id', '$delivery_name', '$seller_name', '$company_rep_id', '$size', '$color', '$shape', '$shipping_fee')") 
                                   or die('Query failed: ' . mysqli_error($conn));
                           }
                           else{
                               $message[] = "Not enough stocks";
                            }
                   }
         }

         // Clear the cart for this company_rep_id after processing
         $clear_cart = mysqli_query($conn, "DELETE FROM `cart` WHERE buyer_id = '$buyer_id' ".($company_rep_id ? " AND company_rep_id = '$company_rep_id' " : "" )." ") or die('Query failed');
        echo "
            <div class='order-message-container'>
               <div class='message-container'>
                    <h3>Thank you for your order!</h3>
                    <p>Your order has been placed successfully. Track your orders in your account.</p>
                   <a href='company.php' class='btn'>Continue shopping</a>
                </div>
            </div>";
      } else {
              echo "
                   <div class='order-message-container'>
                      <div class='message-container'>
                        <h3>Your cart is empty!</h3>
                       <p>Looks like you haven't added anything to your cart yet. Start shopping now to add items.</p>
                       <a href='company.php' class='btn'>Continue shopping</a>
                     </div>
                  </div>";
       }

}
     // Get company_rep_id from URL parameter, if it exists
       $company_rep_id = isset($_GET['company_rep_id']) ? mysqli_real_escape_string($conn, $_GET['company_rep_id']) : null;
       // Fetch the gcash_qrcode based on the company_rep_id
       $gcash_query = mysqli_query($conn, "SELECT gcash_qrcode FROM `description` WHERE company_rep_id = '$company_rep_id'");
      $gcash_qrcode_path = '';
      if($gcash_query && mysqli_num_rows($gcash_query) > 0) {
          $gcash_data = mysqli_fetch_assoc($gcash_query);
           $gcash_qrcode_path = '../../uploaded_c_gimg/'. $gcash_data['gcash_qrcode'];
       }
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout</title>

 
  <!-- Font Awesome CDN link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <!-- Custom CSS file link -->
   <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

   <link rel="stylesheet" href="../../css/design.css">
<link rel="stylesheet" href="checkout.css">
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
			<li class="active">
				<a href="checkout.php">
					<i class='bx bxs-purchase-tag-alt' ></i>
					<span class="text">Complete Purchase </span>
				</a>
			</li>
			<li>
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
                <h1>Complete Purchase</h1>
					<ul class="breadcrumb">
						<li>
							<a href="company.php">Crafthive</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="company.php"> C List</a>
						</li>
                        <li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="company_product.php">C Product</a>
						</li>
                        <li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="cart.php">C Cart</a>
						</li>
                        <li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="checkout.php"> C Purchase</a>
						</li>
					</ul>
				</div>
			</div>
<section>

<div class="container">

<section class="checkout-form">

<h3>Complete your Purchase</h3>

   <form action="" method="post">
   <div class="display-order">
   <?php
      // Assuming buyer_id is stored in the session
      $buyer_id = $_SESSION['unique_id'];
       // Get company_rep_id from URL parameter, if it exists
       $company_rep_id = isset($_GET['company_rep_id']) ? mysqli_real_escape_string($conn, $_GET['company_rep_id']) : null;
      // Fetch cart items for the specific buyer_id
        $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE buyer_id = '$buyer_id' " . ($company_rep_id ? " AND company_rep_id = '$company_rep_id' " : "") . "");
      $total = 0;
      $grand_total = 0;

      if (mysqli_num_rows($select_cart) > 0) {
         while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
            $total_price = ($fetch_cart['price'] * $fetch_cart['quantity']) + $fetch_cart['shipping_fee'];
             $grand_total += $total_price;
     ?>
             <span><?= $fetch_cart['product_name']; ?>(Quantity: <?= $fetch_cart['quantity']; ?>, <?= $fetch_cart['color']; ?>, <?= $fetch_cart['shape']; ?>, <?= $fetch_cart['shape']; ?> Shipping Fee: ₱<?= number_format($fetch_cart['shipping_fee'], 2)?> )</span>
     <?php
         }
     } else {
         echo "<div class='display-order'><span>Your cart is empty!</span></div>";
     }
   ?>
   <div class="grand-total-container">
    <span class="grand-total">Grand Total: ₱<?= number_format($grand_total, 2) ?>/-</span>
</div>
</div>

      <div class="flex">
         <div class="inputBox">
            <span>Address Line 1</span>
            <input type="text" placeholder="e.g. Flat No." name="flat" class=box required>
         </div>

         <div class="inputBox">
            <span>Address Line 2</span>
            <input type="text" placeholder="e.g. Street Name" name="street"  class=box required>
         </div>

         <div class="inputBox">
            <span>City</span>
            <input type="text" placeholder="e.g. Manila" name="city"  class=box  class=box required>
         </div>

         <div class="inputBox">
            <span>State</span>
            <input type="text" placeholder="e.g. NCR" name="state"  class=box required>
         </div>

         <div class="inputBox">
            <span>Country</span>
            <input type="text" placeholder="e.g. Philippines" name="country"  class=box required>
         </div>

         <div class="inputBox">
    <span>Payment Method</span>
    <select name="payment" id="paymentMethod" onchange="toggleGcashReference()" required>
        <option value="cash on delivery" selected>Cash on Delivery</option>
        <option value="g cash">G cash</option>
    </select>
</div>

<div class="inputBox" id="gcashReferenceBox" style="display: none;">
    <span>GCash Reference</span>
    <input type="text" placeholder="Enter GCash reference" name="gcash_reference" id="gcashReference" />
    <div id="gcashQrCodeContainer" style="display: none; margin-top: 10px;">
        <p>Scan the QR Code to Pay:</p>
        <img src="<?php echo $gcash_qrcode_path; ?>" alt="GCash QR Code" />
    </div>
</div>
        
      </div>
      <br>
      <input type="submit" value="Purchase now" name="order_btn" class="btn">
   </form>

</section>

</div>

<!-- custom js file link  -->
<script src="../js/all_script.js"></script>

<script>
   function toggleGcashReference() {
    const paymentMethod = document.getElementById('paymentMethod').value;
    const gcashReferenceBox = document.getElementById('gcashReferenceBox');
    const gcashQrCodeContainer = document.getElementById('gcashQrCodeContainer');
    const gcashReference = document.getElementById('gcashReference');

    if (paymentMethod === 'g cash') {
        gcashReferenceBox.style.display = 'block';
        gcashQrCodeContainer.style.display = 'block';
        gcashReference.required = true;
    } else {
        gcashReferenceBox.style.display = 'none';
        gcashQrCodeContainer.style.display = 'none';
        gcashReference.required = false;
    }
}
</script>
</body>
</html>