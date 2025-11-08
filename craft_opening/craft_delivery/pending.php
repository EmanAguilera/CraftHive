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

// Check if the connection was successful before proceeding
if (!$conn) {
    $message[] = "Database connection failed. Please contact support.";
     //If the connection is bad, exit so that the rest of the page does not load.
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_proof'])) {
   $order_id = intval($_POST['order_id']);
    
     // Check order status before processing the upload.
     $check_order_query = "SELECT approval FROM `order` WHERE order_id = '$order_id' AND delivery_id = '$delivery_id'";
    $check_order_result = mysqli_query($conn, $check_order_query);

    if($check_order_result && mysqli_num_rows($check_order_result) > 0){
           $order_data = mysqli_fetch_assoc($check_order_result);
              if($order_data['approval'] == 'Confirm'){
               $image_name = $_FILES['proof_receipt']['name'];
               $image_tmp_name = $_FILES['proof_receipt']['tmp_name'];
               $image_folder = '../../uploaded_receipts/' . $image_name;

               // Move uploaded file to the target directory
               if (move_uploaded_file($image_tmp_name, $image_folder)) {
                   // Update order with proof receipt and mark as delivered
                   $update_query = "UPDATE `order` 
                                    SET proof_receipt = '$image_name', 
                                        approval = 'Delivered' 
                                    WHERE order_id = '$order_id' 
                                    AND delivery_id = '$delivery_id'";

                   // Execute the query and check for errors
                   if (mysqli_query($conn, $update_query)) {
                       // Update total_delivered in the delivery table
                       $update_delivery_query = "UPDATE delivery 
                                                SET total_delivered = COALESCE(total_delivered, 0) + 1 
                                                WHERE unique_id = '$delivery_id'";

                       // Execute the query for delivery table update
                       if (mysqli_query($conn, $update_delivery_query)) {
                           $message[] = "Proof of receipt uploaded successfully. The order has been marked as Delivered, and the delivery count has been updated.";
                       } else {
                           $message[] = "Failed to update delivery count. Please try again.";
                       }
                   } else {
                       $message[] = "Failed to update order. Please try again.";
                   }
               } else {
                   $message[] = "Failed to upload image. Please try again.";
               }
             } else{
                  $message[] = "Cannot provide a receipt, as the order is not confirmed.";
             }
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
  <!-- Font Awesome CDN link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <!-- Custom CSS file link -->
   <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

   <link rel="stylesheet" href="../../css/design.css">
   <link rel="stylesheet" href="additional.css">

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
					<span class="text">Delivery Dashboard</span>
				</a>
			</li>
			<li class="active">
				<a href="pending.php">
					<i class='bx bxs-shopping-bag-alt' ></i>
					<span class="text">Delivery Receipt</span>
				</a>
			</li>
			<li>
				<a href="delivered.php">
					<i class='bx bxs-doughnut-chart' ></i>
					<span class="text">Delivered Product </span>
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
					<h1>Delivery's Receipt</h1>
					<ul class="breadcrumb">
						<li>
							<a href="ch-company.php">Company</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="ch-company.php">Description</a>
						</li>
					</ul>
				</div>
			</div>



<section class="recent-transactions">

   <table>
      <thead>
         <th>Product Image</th>
         <th>Product Name</th>
         <th>Buyer Name</th>
         <th>Quantity</th>
         <th>Shipping Fee</th>
         <th>Total Price</th>
         <th>Shipping Address</th>
         <th>Proof Receipt</th>
      </thead>
      <tbody>
         <?php
         // SQL Query to fetch only non-delivered orders for the logged-in delivery person
         $query = "SELECT o.order_id, o.product_name, o.buyer_name, o.quantity, o.total_price, 
         o.shipping_address, o.approval, o.proof_receipt, p.image, o.shipping_fee
         FROM `order` o
         JOIN product p ON o.product_id = p.product_unique_id
         WHERE o.delivery_id = '$delivery_id'";


         // Execute query
         $select_orders = mysqli_query($conn, $query);

         // Check if orders exist
         if (mysqli_num_rows($select_orders) > 0) {
             while ($row = mysqli_fetch_assoc($select_orders)) {
         ?>
         <tr>
            <td><img src="../../uploaded_img/<?php echo htmlspecialchars($row['image']); ?>" height="100" alt="Product Image"></td>
            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
            <td><?php echo htmlspecialchars($row['buyer_name']); ?></td>
            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
              <td>₱<?php echo htmlspecialchars($row['shipping_fee']); ?></td>
            <td><?php echo htmlspecialchars($row['total_price']); ?></td>
            <td><?php echo htmlspecialchars($row['shipping_address']); ?></td>
            <td>
              <?php if ($row['approval'] === 'Confirm') : ?>
               <button type="button" onclick="openModal(<?php echo $row['order_id']; ?>)" class="upload-btn">
                  <i class="fas fa-upload"></i> Upload
               </button>
              <?php else : ?>
                <span class="alert">Pending Confirmation</span>
              <?php endif; ?>
            </td>
         </tr>
         <?php
             }
         } else {
             echo "<tr><td colspan='9' class='empty'>No pending orders found</td></tr>";
         }
         ?>
      </tbody>
   </table>
</section>

</div>

<!-- Modal -->
<div id="uploadModal" class="modal">
   <div class="modal-content">
      <div class = "modal-header">
      <h1>Upload Proof of Receipt</h1>
      <span class="close-btn">×</span>
      <form action="" method="POST" enctype="multipart/form-data">
      </div>
         <input type="hidden" name="order_id" id="order_id_modal">
         
         <!-- Square Placeholder Before Image Preview -->
         <div id="image_preview_placeholder" class="image-preview-placeholder">
            <span>Select an image</span>
         </div>

         <!-- Image Preview -->
         <img id="image_preview" class="image-preview" src="" alt="Image Preview">
         <div class="button-row">
         <label for="file-input" class="option-btn">Choose Image</label>
<input type="file" name="proof_receipt" id="file-input" accept="image/*" required>
<div class="file-name" id="file_name_display"></div>
<button type="submit" name="upload_proof" class="upload-btn">Upload Receipt</button>
      </div>

      </form>
   </div>
</div>


<script src="js/script.js"></script>

<script>
// Get modal and buttons
var modal = document.getElementById("uploadModal");
var closeBtn = document.getElementsByClassName("close-btn")[0];

// When the user clicks the upload button
function openModal(order_id) {
   // Set the order ID in the modal input
   document.getElementById("order_id_modal").value = order_id;
   modal.style.display = "block";
}

// When the user clicks the close button, close the modal
closeBtn.onclick = function() {
   modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
   if (event.target === modal) {
      modal.style.display = "none";
   }
}

// Show file name and image preview after selecting a file
document.getElementById("file-input").addEventListener("change", function(event) {
   var file = event.target.files[0];
   var fileName = file ? file.name : '';
   var fileReader = new FileReader();

   // Show the file name

   // Display image preview if the file is an image
   if (file && file.type.startsWith("image/")) {
      fileReader.onload = function(e) {
         var imagePreview = document.getElementById("image_preview");
         var previewPlaceholder = document.getElementById("image_preview_placeholder");

         // Hide placeholder and show the preview image
         previewPlaceholder.style.display = "none";
         imagePreview.style.display = "block";
         imagePreview.src = e.target.result; // Set the image source to the selected file
      };
      fileReader.readAsDataURL(file); // Read the file as data URL
   } else {
      // Hide the preview and show placeholder if not an image
      document.getElementById("image_preview").style.display = "none";
      document.getElementById("image_preview_placeholder").style.display = "flex";
   }
});
</script>

</body>
</html>