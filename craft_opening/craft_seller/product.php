<?php
session_start();
@include '../connection/connect.php';

// Check if the seller is logged in
if (!isset($_SESSION['unique_id']) || !isset($_SESSION['company_rep_id'])) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit;
}

// Get session variables
$seller_id = $_SESSION['unique_id'];
$seller_company_rep_id = $_SESSION['company_rep_id'];

if (isset($_POST['add_product'])) {
   $p_name = mysqli_real_escape_string($conn, $_POST['p_name']);
   $p_price = mysqli_real_escape_string($conn, $_POST['p_price']);
   $p_image = $_FILES['p_image']['name'];
   $p_image_tmp_name = $_FILES['p_image']['tmp_name'];
   $p_image_folder = '../../uploaded_img/' . $p_image;
   $p_category = mysqli_real_escape_string($conn, $_POST['p_category']); // Capture category
    // Check if the selected category is 'Other', if so, use the value entered into the other category text field
     if($p_category == 'Other') {
       $p_category = mysqli_real_escape_string($conn, $_POST['p_other_category']);
      }
   $p_shipping_fee = mysqli_real_escape_string($conn, $_POST['p_shipping_fee']);
    $p_stocks = mysqli_real_escape_string($conn, $_POST['p_stocks']);
    $p_seller_description = mysqli_real_escape_string($conn, $_POST['p_seller_description']);


    // Capture and format size, color, and shape
    $p_size = isset($_POST['p_size']) ? "Size: " . $_POST['p_size'] : "";
    $p_color = isset($_POST['p_color']) ? "Color: " . $_POST['p_color'] : "";
    $p_shape = isset($_POST['p_shape']) ? "Shape: " . $_POST['p_shape'] : "";

    $p_status = 'Active';


   // Generate a unique ID for the product
   $product_unique_id = rand(1, 999999999); // Adjust range based on database INT limit

    // Validate product name length
  
   // Fetch seller's full name from the seller table (combination of fname and lname)
   $seller_query = mysqli_query($conn, "SELECT CONCAT(fname, ' ', lname) AS seller_name FROM seller WHERE unique_id = '$seller_id'");
   if (mysqli_num_rows($seller_query) > 0) {
      $seller_data = mysqli_fetch_assoc($seller_query);
      $seller_name = $seller_data['seller_name'];
   } else {
      $message[] = 'Seller information not found';
      return; // Exit if no seller information is found
   }

   // Fetch delivery information based on the company representative
   $delivery_query = mysqli_query($conn, "
        SELECT unique_id, CONCAT(fname, ' ', lname) AS delivery_name 
        FROM `delivery` 
        WHERE company_rep_id = '$seller_company_rep_id' 
        LIMIT 1
    ") or die('Query failed: ' . mysqli_error($conn));

   if (mysqli_num_rows($delivery_query) > 0) {
      $delivery_data = mysqli_fetch_assoc($delivery_query);
      $delivery_id = $delivery_data['unique_id'];
      $delivery_name = $delivery_data['delivery_name'];
   } else {
      $message[] = 'Delivery information not found for this company representative';
      return; // Exit if no delivery information is found
   }

   // Insert the product into the database
     $insert_query = mysqli_query($conn, "INSERT INTO `product` (product_unique_id, product_name, price, image, seller_id, company_rep_id, delivery_id, delivery_name, seller_name, size, color, shape, category, shipping_fee, stocks, seller_description, status) 
       VALUES ('$product_unique_id', '$p_name', '$p_price', '$p_image', '$seller_id', '$seller_company_rep_id', '$delivery_id', '$delivery_name', '$seller_name', '$p_size', '$p_color', '$p_shape', '$p_category', '$p_shipping_fee', '$p_stocks', '$p_seller_description', '$p_status')") or die('Query failed: ' . mysqli_error($conn));


   if ($insert_query) {
       // Move uploaded image to the folder
       if (move_uploaded_file($p_image_tmp_name, $p_image_folder)) {
           $message[] = 'Product added successfully';
       } else {
           $message[] = 'Failed to upload product image';
       }
   } else {
       $message[] = 'Could not add the product';
   }

   header('Location: product.php');
   exit;
}

if (isset($_GET['delete'])) {
   $delete_id = mysqli_real_escape_string($conn, $_GET['delete']);  // Sanitize the input

   // Ensure the product belongs to the logged-in seller and matches the correct delivery_id
    $delete_query = mysqli_query($conn, "DELETE FROM `product` 
        WHERE product_unique_id = '$delete_id' 
        AND seller_id = '$seller_id' 
        AND company_rep_id = '$seller_company_rep_id'
        AND delivery_id = (SELECT unique_id FROM delivery WHERE company_rep_id = '$seller_company_rep_id' LIMIT 1)")
        or die('Query failed: ' . mysqli_error($conn));


   // Check if the deletion was successful
   if ($delete_query) {
       $message[] = 'Product has been deleted';  // Store success message
   } else {
       $message[] = 'Product could not be deleted';  // Store failure message
   }

   header('Location: product.php');  // Redirect to the product page after deletion attempt
   exit;  // Ensure no further code is executed after redirect
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title> Seller's Product</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <!-- Custom CSS file link -->
   <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

   <link rel="stylesheet" href="../../css/design.css">
   <link rel="stylesheet" href="additional.css">

<style>
    .box-textarea {
    width: 100%;
     max-width: 100%;
     min-width: 100%;
    padding: 8px 12px;
    border: 1px solid #8E5727;
    border-radius: 8px;
        background-color: #FFF5E1;
    font-size: 16px;
        color: #3E2723;
    margin-bottom: 20px;
    box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.1);
       transition: all 0.3s ease;
    resize: vertical;
    box-sizing: border-box; /* Include padding and border in the element's total width and height */
}
.box-textarea::placeholder {
    color: var(--placeholder-color);
    font-style: italic;
}

    .box-textarea:focus {
    border-color: #D19A6B;
    outline: none;
    box-shadow: 0 0 8px rgba(93, 64, 55, 0.4);
}
.ellipsis {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px; /* Adjust this value as needed */
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
			<li class="active">
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
    <img src="../uploaded_profile/CH-Logo.png" alt="Profile Image">
</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1> Seller's Product</h1>
					<ul class="breadcrumb">
						<li>
							<a href="ch-company.php">Seller</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="ch-company.php">S Product</a>
						</li>
					</ul>
				</div>
			</div>
<section>

<div class="input-container">

<section>

<form action="" method="post" class="add-product-form" enctype="multipart/form-data">
   <h3>Add a new product</h3>
   <input type="text" name="p_name" placeholder="Enter the product name" class="box" required>
   <input type="number" name="p_price" min="0" placeholder="Enter the product price" class="box" required>
   <div class="custom-file-input">
    <input type="file" id="p_image" name="p_image" accept="image/png, image/jpg, image/jpeg" class="box" required>
    <label for="p_image" id="label_p_image">Upload your Product image</label>
</div>
    <!-- Single text input field for size -->
    <label>Enter Product Sizes (comma-separated):</label>
    <input type="text" name="p_size" placeholder="e.g., 1 inch, 2 inch, 3 inch" class="box">
    <!-- Single text input field for color -->
    <label>Enter Product Colors (comma-separated):</label>
    <input type="text" name="p_color" placeholder="e.g., Red, Blue, Green" class="box">
    <!-- Single text input field for shape -->
    <label>Enter Product Shapes (comma-separated):</label>
    <input type="text" name="p_shape" placeholder="e.g., Square, Circle, Triangle" class="box">

      <!-- Product Category Dropdown -->
        <label>Select Product Category:</label>
        <select name="p_category" id="p_category" class="box" required>
            <option value="">Select a category</option>
            <option value="Clothing">Clothing</option>
            <option value="Accessories">Accessories</option>
            <option value="Home Decor">Home Decor</option>
              <option value="Art">Art</option>
              <option value="Other">Other</option>
        </select>
         <!-- Text input for "Other" category -->
        <div id="otherCategoryInput" style="display: none;">
         <label>Enter Custom Category:</label>
         <input type="text" name="p_other_category"  placeholder = "Enter your Category" class = "box">
       </div>
        <!-- Shipping Fee Text Input -->
        <label>Enter Shipping Fee:</label>
        <input type="number" name="p_shipping_fee" min="0" placeholder="Enter shipping fee" class="box" required>
          <!-- Product Stocks Text Input -->
        <label>Enter Product Stocks:</label>
        <input type="number" name="p_stocks" min="0" placeholder="Enter product stock" class="box" required>
       <!-- Textarea input for product description -->
       <label>Enter Seller Description:</label>
         <textarea name="p_seller_description" placeholder="Enter the product description" class = "box-textarea"  required></textarea>

   <!-- Delivery Name Dropdown -->
   <select name="delivery_id" class="box" required>
      <option value="">Select a delivery person</option>
      <?php
      // Fetch delivery persons assigned to the seller's company
      $delivery_query = mysqli_query($conn, "SELECT unique_id, CONCAT(fname, ' ', lname) AS delivery_name 
                                             FROM `delivery` 
                                             WHERE company_rep_id = '$seller_company_rep_id'");

      while ($delivery_data = mysqli_fetch_assoc($delivery_query)) {
          echo "<option value='" . $delivery_data['unique_id'] . "'>" . $delivery_data['delivery_name'] . "</option>";
      }
      ?>
   </select>

   <input type="submit" value="Add the product" name="add_product" class="btn">
</form>

   </div>

</section>

<section class="recent-transactions">

   <table>

      <thead>
         <th>Product image</th>
         <th>Product name</th>
            <th>Product Price</th>
              <th>Product Shipping Fee</th>
              <th>Product Stocks</th>
              <th>Seller Description</th>
         <th>Delivery person </th>
         <th>Action</th>
      </thead>

      <tbody>
         <?php
            // Fetch only the products that belong to the logged-in seller
           $select_products = mysqli_query($conn, "SELECT * FROM `product` WHERE seller_id = '$seller_id' AND company_rep_id = '$seller_company_rep_id'");
            if(mysqli_num_rows($select_products) > 0){
               while($row = mysqli_fetch_assoc($select_products)){
         ?>

         <tr>
            <td><img src="../../uploaded_img/<?php echo $row['image']; ?>" height="100" alt=""></td>
            <td><?php echo $row['product_name']; ?></td>
            <td>₱<?php echo $row['price']; ?>/-</td>
               <td>₱<?php echo isset($row['shipping_fee']) ? $row['shipping_fee'] : 'N/A'; ?></td>
            <td><?php echo $row['stocks']; ?></td>
               <td class="ellipsis"><?php echo $row['seller_description']; ?></td>
            <td><?php echo $row['delivery_name']; ?></td>
            <td>
              <a href="edit_product.php?product_id=<?php echo $row['product_unique_id']; ?>" class="option-btn"> <i class="fas fa-edit"></i> Update </a>
               <a href="product.php?delete=<?php echo $row['product_unique_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this?');"> <i class="fas fa-trash"></i> Erasure </a>
            </td>
         </tr>

         <?php
            };    
            }else{
               echo "<div class='empty'>no product added</div>";
            };
         ?>
      </tbody>
   </table>

</section>

</div>

<script src="../js/all_script.js"></script>
<script>
    // Function to update the label text when a file is selected
    document.getElementById('p_image').addEventListener('change', function () {
        const label = document.getElementById('label_p_image');
        const fileName = this.files[0]?.name || 'Upload your company image'; // Fallback if no file is selected
        label.textContent = fileName;
    });

     document.getElementById('p_category').addEventListener('change', function() {
        const otherCategoryInput = document.getElementById('otherCategoryInput');
        if(this.value === 'Other') {
            otherCategoryInput.style.display = 'block';
        } else {
            otherCategoryInput.style.display = 'none';
        }
    });
   </script>

</body>
</html>