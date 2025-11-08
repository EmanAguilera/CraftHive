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

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // Fetch product details for the given product ID
    $product_query = mysqli_query($conn, "SELECT * FROM `product` WHERE product_unique_id = '$product_id' AND seller_id = '$seller_id' AND company_rep_id = '$company_rep_id'");

    if (mysqli_num_rows($product_query) > 0) {
        $product = mysqli_fetch_assoc($product_query);
    } else {
        // Redirect if no product found
        header('Location: product.php');
        exit;
    }
}

if (isset($_POST['update_product'])) {
    $updated_name = mysqli_real_escape_string($conn, $_POST['p_name']);
    $updated_price = mysqli_real_escape_string($conn, $_POST['p_price']);
    $updated_image = $_FILES['p_image']['name'];
    $updated_image_tmp_name = $_FILES['p_image']['tmp_name'];
    $updated_image_folder = '../../uploaded_img/' . $updated_image;
    $updated_category = mysqli_real_escape_string($conn, $_POST['p_category']);
     if($updated_category == 'Other') {
       $updated_category = mysqli_real_escape_string($conn, $_POST['p_other_category']);
      }
    $updated_shipping_fee = mysqli_real_escape_string($conn, $_POST['p_shipping_fee']);
    $updated_stocks = mysqli_real_escape_string($conn, $_POST['p_stocks']);
     $updated_seller_description = mysqli_real_escape_string($conn, $_POST['p_seller_description']);


      // Capture and format size, color, and shape
    $updated_size = isset($_POST['p_size']) ? "Size: " . $_POST['p_size'] : "";
     $updated_color = isset($_POST['p_color']) ? "Color: " . $_POST['p_color'] : "";
    $updated_shape = isset($_POST['p_shape']) ? "Shape: " . $_POST['p_shape'] : "";

    // Check if a new image is uploaded
    if (!empty($updated_image)) {
        // If a new image is uploaded, update the image and move the file
        move_uploaded_file($updated_image_tmp_name, $updated_image_folder);
    } else {
        // If no new image is uploaded, keep the existing image
        $updated_image = $product['image'];
    }

    // Update product in the database
      $update_query = mysqli_query($conn, "UPDATE `product` SET 
        product_name = '$updated_name', 
        price = '$updated_price', 
        image = '$updated_image', 
        size = '$updated_size',
        color = '$updated_color',
        shape = '$updated_shape',
        category = '$updated_category',
        shipping_fee = '$updated_shipping_fee',
        stocks = '$updated_stocks',
        seller_description = '$updated_seller_description'
        WHERE product_unique_id = '$product_id' AND seller_id = '$seller_id'");

    if ($update_query) {
        header('Location: product.php'); // Redirect back to the product listing page
        exit;
    } else {
        $message[] = 'Product could not be updated.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Edit Product</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <!-- Custom CSS file link -->
   <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

   <link rel="stylesheet" href="../../css/design.css">
<style>
	@import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Poppins:wght@400;500;600;700&display=swap');
:root {
       --dark: #5D4037; /* Brown shade for sidebar and navbar */
       --blue: #8E5727; /* Muted brown for accents */
       --grey-light: #E8C595; /* Very light brown for alternating rows */
       --main-bg: #E0E0E0; /* Updated gray for the main container */
       --light: #FEE8C5; /* Light background color */
       --yellow: #FFD54F; /* Yellow for status badges */
       --orange: #FF8A65; /* Orange for status badges */
       --white: #FEE8C5; /* White for text or backgrounds */
       --grey:   #E8C595; /* Neutral grey for hover effects */
   }

/* Recent Transactions Section */
.recent-transactions {
   margin-top: 48px;
}

.recent-transactions h2 {
   font-size: 24px;
   font-weight: 600;
   margin-bottom: 16px;
   color: var(--dark);
}

.recent-transactions table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 10px;
    overflow: hidden;
    background-color: #5C2E0A;
    border: 2px solid #5C2E0A; /* Add border */
}


.recent-transactions table th, .recent-transactions table td {
   padding: 12px;
   text-align: center;
   font-size: 14px;
}

.recent-transactions table th {
   background-color: var(--blue);
   color: #FFF3DC;
   font-weight: bold;
}

.recent-transactions table td {
   background-color: var(--white);
}

.recent-transactions table tr:nth-child(even) td {
   background-color: var(--grey-light);
}

.recent-transactions table tr:hover {
   background-color: var(--grey);
}

   /* Sidebar background color */
   #sidebar {
   background-color: #8E5727;
}

/* Sidebar link color */
#sidebar a {
   color: pink;
}

/* Sidebar active link color */
#sidebar .active a {
   background-color: #3E2723;
}
/* MAIN */
/* CONTENT */

/* General styles */

/* General shared styles */
a.delete-btn, a.option-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #FFF; /* White text */
    border-radius: 8px; /* Rounded corners */
    padding: 10px 20px; /* Button padding */
    font-size: 14px; /* Text size */
    font-weight: bold; /* Bold text */
    text-decoration: none; /* Remove underline */
    cursor: pointer; /* Pointer cursor on hover */
    transition: all 0.3s ease-in-out; /* Smooth hover effects */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2), inset 0 -3px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
}

/* Specific styling for Delete Button */
a.delete-btn {
    background: linear-gradient(135deg, #8E3A15, #5C1E09); /* Dark reddish-brown gradient */
    border: 2px solid #4A1608; /* Deep red-brown border */
}

/* Specific styling for Option Button */
a.option-btn {
    background: linear-gradient(135deg, #C69B68, #A16D37); /* Warm, lighter brown gradient */
    border: 2px solid #774816; /* Medium brown border */
}

/* Icon styling inside buttons */
a.delete-btn i, a.option-btn i {
    margin-right: 8px; /* Space between icon and text */
    font-size: 16px; /* Icon size */
}

/* Hover effects for Delete Button */
a.delete-btn:hover {
    background: linear-gradient(135deg, #A64520, #742312); /* Slightly brighter reddish-brown */
    border-color: #5A1F0A; /* Brighter border */
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3), inset 0 -3px 4px rgba(255, 255, 255, 0.2); /* Enhanced hover shadow */
    transform: scale(1.05); /* Slightly enlarge */
}

/* Hover effects for Option Button */
a.option-btn:hover {
    background: linear-gradient(135deg, #E3B482, #B2774A); /* Brighter light brown gradient */
    border-color: #8A5A23; /* Lighter border */
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3), inset 0 -3px 4px rgba(255, 255, 255, 0.2); /* Enhanced hover shadow */
    transform: scale(1.05); /* Slightly enlarge */
}

/* Active state for Delete Button */
a.delete-btn:active {
    background: #5C1E09; /* Dark solid brown for active state */
    transform: scale(0.98); /* Slightly shrink on click */
    box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.3); /* Inset shadow for click effect */
}

/* Active state for Option Button */
a.option-btn:active {
    background: #7A4F22; /* Medium brown solid for active state */
    transform: scale(0.98); /* Slightly shrink on click */
    box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.3); /* Inset shadow for click effect */
}

/* Focus state for both buttons */
a.delete-btn:focus, a.option-btn:focus {
    outline: none; /* Remove default outline */
    box-shadow: 0 0 8px rgba(182, 125, 91, 0.8); /* Soft brown glow effect */
}

.option-btn{
    margin-bottom: 10px;

}

:root {
    --primary-color: #5D4037; /* Vintage dark brown */
    --primary-gradient: linear-gradient(135deg, #5D4037, #4A3229);
    --hover-gradient: linear-gradient(135deg, #6F5148, #4A3229);
    --input-bg: #FFFFFF; /* Clean white background for inputs */
    --input-border: #D3C4B1; /* Subtle beige for borders */
    --input-focus-border: #5D4037; /* Dark brown for focus */
    --font-color: #5D4037; /* Matching text color */
    --placeholder-color: #A69485; /* Subtle vintage placeholder */
    --background-color: #FDFBF8; /* Soft off-white for form background */
}
.container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: linear-gradient(to bottom, #FFF5E1, #F7E0C0);
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
            border: 1px solid #8E5727;
        }

        .container h3 {
            font-size: 1.8em;
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 20px;
            text-transform: uppercase;
            border-bottom: 2px solid #8E5727;
            padding-bottom: 10px;
        }
          label {
            font-size: 1em;
            color: var(--font-color);
            display: block;
            margin-bottom: 5px;
        }

        .box {
            width: 100%;
            height: 40px;
            padding: 8px 12px;
            border: 1px solid #8E5727;
            border-radius: 8px;
            background-color: #FFF5E1;
            font-size: 16px;
            color: #3E2723;
            margin-bottom: 20px;
            box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .box::placeholder {
            color: var(--placeholder-color);
            font-style: italic;
        }

        .box:focus {
            border-color: #D19A6B;
            outline: none;
            box-shadow: 0 0 8px rgba(93, 64, 55, 0.4);
        }

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

        .custom-file-input {
            position: relative;
            margin-bottom: 20px;
        }

        .custom-file-input label {
            display: block;
            background-color: #FFF5E1;
            border: 1px solid #8E5727;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 16px;
            color: #3E2723;
            text-align: left;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-style: italic;
        }

        .custom-file-input label:hover {
            background-color: #F7E0C0;
        }

        .custom-file-input input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            height: 100%;
            width: 100%;
            cursor: pointer;
        }

        .btn {
            width: 100%;
            padding: 12px 20px;
            background: #5C2E0A;
            border: none;
            border-radius: 25px;
            color: #FFFFFF;
            font-size: 1.1em;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: #7A3B0E;
            transform: scale(1.02);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
.message{
   position: sticky;
   top:0; left:0;
   z-index: 10000;
   border-radius: .5rem;
   background: #FFF4DE; /* Soft parchment gradient */
   padding:1.5rem 2rem;
   margin:0 auto;
   max-width: 1200px;
   display: flex;
   align-items: center;
   justify-content: space-between;
   gap:1.5rem;
   border: 1px solid #D2B48C; /* Light brown border for elegance */
    transition: opacity 0.3s ease; /* Smooth transition for hiding */
}

.message span{
   font-size: 2rem;
   color:var(--black);
}

.message i{
   font-size: 2.5rem;
   color:var(--black);
   cursor: pointer;
}

.message i:hover{
   color:var(--red);
}

#content main .head-title .left .breadcrumb li a {
	color: #5C2E0A;
	pointer-events: none;
    font-weight: 500;
}

@media screen and (max-width: 768px) {
   #sidebar {
       width: 200px;
   }

   #content {
       width: calc(100% - 60px);
       left: 200px;
   }

   #content nav .nav-link {
       display: none;
   }

   #content nav form .form-input input {
       display: none;
   }

   #content nav form .form-input button {
       width: auto;
       height: auto;
       background: transparent;
       border-radius: none;
       color: var(--dark);
   }

   #content nav form.show .form-input input {
       display: block;
       width: 100%;
   }
   #content nav form.show .form-input button {
       width: 36px;
       height: 100%;
       border-radius: 0 36px 36px 0;
       color: var(--light);
       background: var(--red);
   }

   #content nav form.show ~ .notification,
   #content nav form.show ~ .profile {
       display: none;
   }

   #content main .box-info {
       grid-template-columns: 1fr;
   }

   #content main .table-data .head {
       min-width: 420px;
   }
   #content main .table-data .order table {
       min-width: 420px;
   }
   #content main .table-data .todo .todo-list {
       min-width: 420px;
   }
}

.button-group {
    display: flex; /* Use flexbox for alignment */
    justify-content: space-between; /* Space between buttons */
    gap: 10px;
    margin-top: 10px; /* Space above the button group */
}

.button-group .btn {
    flex: 1; /* Allow buttons to grow equally */
    text-align: center;
   
}

.button-group .btn:last-child {
    margin-right: 0; /* Remove margin from the last button */
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
					<h1> Edit Product</h1>
					<ul class="breadcrumb">
						<li>
							<a href="ch-company.php">Seller</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="ch-company.php">Edit Product</a>
						</li>
					</ul>
				</div>
			</div>

<div class="container">
<section>
    <form action="" method="post" class="add-product-form" enctype="multipart/form-data">
        <h3>Edit Product</h3>
        <input type="hidden" name="product_unique_id" value="<?php echo $product['product_unique_id']; ?>">
        <label>Product Name</label>
        <input type="text" name="p_name"  value="<?php echo $product['product_name']; ?>" class="box" required>
        <label>Product Price</label>
        <input type="number" name="p_price" value="<?php echo $product['price']; ?>" min="0" placeholder="Enter the product price" class="box" required>
        <div class="custom-file-input">
            <input type="file" id="p_image" name="p_image" accept="image/png, image/jpg, image/jpeg" class="box">
            <label for="p_image" id="label_p_image">Update your Product image</label>
        </div>

        <!-- Single text input field for size -->
        <label>Enter Product Sizes (comma-separated):</label>
         <input type="text" name="p_size"  value="<?php echo isset($product['size']) ? substr($product['size'], strlen('Size: ')) : ''; ?>" placeholder="e.g., 1 inch, 2 inch, 3 inch" class="box">

        <!-- Single text input field for color -->
       <label>Enter Product Colors (comma-separated):</label>
        <input type="text" name="p_color" value="<?php echo isset($product['color']) ? substr($product['color'], strlen('Color: ')) : ''; ?>" placeholder="e.g., Red, Blue, Green" class="box">

        <!-- Single text input field for shape -->
        <label>Enter Product Shapes (comma-separated):</label>
       <input type="text" name="p_shape" value="<?php echo isset($product['shape']) ? substr($product['shape'], strlen('Shape: ')) : ''; ?>" placeholder="e.g., Square, Circle, Triangle" class="box">

         <!-- Product Category Dropdown -->
        <label>Select Product Category:</label>
        <select name="p_category" id="p_category" class="box" required>
            <option value="">Select a category</option>
            <option value="Clothing" <?php if(isset($product['category']) && $product['category'] === 'Clothing') echo 'selected'; ?>>Clothing</option>
            <option value="Accessories" <?php if(isset($product['category']) && $product['category'] === 'Accessories') echo 'selected'; ?>>Accessories</option>
           <option value="Home Decor" <?php if(isset($product['category']) && $product['category'] === 'Home Decor') echo 'selected'; ?>>Home Decor</option>
            <option value="Art" <?php if(isset($product['category']) && $product['category'] === 'Art') echo 'selected'; ?>>Art</option>
            <option value="Other" <?php if(isset($product['category']) && $product['category'] === 'Other') echo 'selected'; ?>>Other</option>
        </select>
          <!-- Text input for "Other" category -->
         <div id="otherCategoryInput" style="display: none;">
             <label>Enter Custom Category:</label>
           <input type="text" name="p_other_category" placeholder = "Enter your Category" class = "box" value ="<?php echo isset($product['category']) && !in_array($product['category'],['Clothing','Accessories','Home Decor','Art']) ? $product['category'] : ''; ?>">
         </div>

           <!-- Shipping Fee Text Input -->
          <label>Enter Shipping Fee:</label>
          <input type="number" name="p_shipping_fee" min="0" value="<?php echo $product['shipping_fee']; ?>" placeholder="Enter shipping fee" class="box" required>

           <!-- Product Stocks Text Input -->
            <label>Enter Product Stocks:</label>
           <input type="number" name="p_stocks" min="0"  value="<?php echo $product['stocks']; ?>" placeholder="Enter product stock" class="box" required>
              <!-- Textarea input for product description -->
       <label>Enter Seller Description:</label>
       <textarea name="p_seller_description" placeholder="Enter the product description" class = "box-textarea"   required><?php echo $product['seller_description']; ?></textarea>


     <div class="button-group">
    <input type="submit" value="Update the product" name="update_product" class="btn">
    <a href="product.php" class="btn back-btn"> <i class="fas fa-arrow-left"></i> Back </a>
</div>
    </form>
</section>

</div>
<script src="../js/all_script.js"></script>
 <script>
    // Function to update the label text when a file is selected
    document.getElementById('p_image').addEventListener('change', function () {
        const label = document.getElementById('label_p_image');
        const fileName = this.files[0]?.name || 'Update your product image'; // Fallback if no file is selected
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