<?php
session_start();
@include '../connection/connect.php';

// Check if the user is logged in (require unique_id only)
if (!isset($_SESSION['unique_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['unique_id'];

// Check if order_id is set in GET parameter
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    echo "Invalid request.";
    exit;
}

$order_id = $_GET['order_id'];

// Fetch product details (product name and picture from the order table)
$product_query = "SELECT
                        o.product_name,
                        p.image,
                        p.product_id,
                         o.seller_id
                    FROM
                        `order` o
                    INNER JOIN
                         `product` p ON o.product_name = p.product_name
                    WHERE
                        o.order_id = '$order_id'";
$product_result = mysqli_query($conn, $product_query);

if (!$product_result || mysqli_num_rows($product_result) == 0) {
    echo "Product not found.";
    exit;
}

$product_data = mysqli_fetch_assoc($product_result);
$product_image_path = $product_data['image'];
$product_name = $product_data['product_name'];
$product_id = $product_data['product_id'];
$seller_id = $product_data['seller_id'];

// Construct the full image URL
$product_image_url = '../../uploaded_img/' . $product_image_path;

// Check if the user has already submitted a review for this order
$review_check_query = "SELECT * FROM `reviews` WHERE order_id = '$order_id' AND user_id = '$user_id'";
$review_check_result = mysqli_query($conn, $review_check_query);
if (mysqli_num_rows($review_check_result) > 0) {
    $already_reviewed = true; // Set a flag to indicate that a review exists
} else {
    $already_reviewed = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_reviewed) {
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $review_text = isset($_POST['review_text']) ? $_POST['review_text'] : '';

    if ($rating < 1 || $rating > 5) {
        $error = "Please provide a valid rating (1-5).";
    } else {
        $insert_query = "INSERT INTO `reviews` (order_id, user_id, rating, review_text, seller_id, product_id)
                         VALUES ('$order_id', '$user_id', '$rating', '$review_text', '$seller_id', '$product_id')";
        if (mysqli_query($conn, $insert_query)) {
            $success = "Review submitted successfully.";
            $already_reviewed = true; // Update the flag after successful submission
        } else {
            $error = "Error submitting review: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add a Review</title>

  <!-- Font Awesome CDN link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <!-- Custom CSS file link -->
   <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

   <link rel="stylesheet" href="../../css/design.css">

   <style>
	@import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Poppins:wght@400;500;600;700&display=swap');

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
}

@media screen and (max-width: 576px) {
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

/* General Container Styling */
.container {
    max-width: 600px;
    margin: 50px auto;
    padding: 30px;
    background: linear-gradient(to bottom, #FFF5E1, #F7E0C0);
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    border: 1px solid #8E5727;
}

/* Header Styling */
.header h3 {
    font-size: 1.8em;
    text-align: center;
    color: #8E5727; /* Primary color */
    margin-bottom: 20px;
    text-transform: uppercase;
    border-bottom: 2px solid #8E5727;
    padding-bottom: 10px;
}

/* Error and Success Messages */
.error {
    color: #B71C1C;
    font-weight: bold;
    margin-bottom: 15px;
    text-align: center;
}

.success {
    color: #1B5E20;
    font-weight: bold;
    margin-bottom: 15px;
    text-align: center;
}

/* Image Styling */
.image-container img {
    display: block;
    max-width: 50%;
    height: auto;
    margin: 20px auto;
    border: 3px solid #8E5727;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

/* Form Fields */
.form-group label {
    font-size: 1em;
    color: #8E5727;
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
    color: #A1887F;
    font-style: italic;
}

.box:focus {
    border-color: #D19A6B;
    outline: none;
    box-shadow: 0 0 8px rgba(93, 64, 55, 0.4);
}

/* Submit Button */
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

#review_text {
        resize: none; /* Disables resizing */
        height: 150px; /* Adjust height as needed */
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
			<li class="active">
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
                <h1>Critique Review</h1>
					<ul class="breadcrumb">
						<li>
							<a href="ch-company.php">Crafthive</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="company.php">C Details</a>
						</li>
                        <li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="add_review.php">C Review</a>
						</li>
					</ul>
				</div>
			</div>

<section>
<div class="container">
    <div class="header">
        <h3>Review for <?php echo htmlspecialchars($product_name); ?></h3>
    </div>

    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>

    <div class="image-container">
        <img src="<?php echo htmlspecialchars($product_image_url); ?>" alt="Product Image">
    </div>

    <form action="" method="post">
        <div class="form-group">
            <input type="number" class="box" name="rating" id="rating" min="1" max="5" placeholder="Enter a rating(1-5)" required>
        </div>
        <div class="form-group">
            <textarea class="box" name="review_text" id="review_text" placeholder="Write your review here" required></textarea>
        </div>

        <button type="submit" class="btn">Submit Review</button>
    </form>
</div>
<script src="../js/all_script.js"></script>
<script>
    <?php if (isset($already_reviewed) && $already_reviewed): ?>
        alert("You have already submitted a review for this order.");
        <?php endif; ?>
    </script>
</body>
</html>