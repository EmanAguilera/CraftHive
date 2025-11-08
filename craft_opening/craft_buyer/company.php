<?php

session_start();
@include '../connection/connect.php';

// Check if the buyer is logged in (require unique_id only)
if (!isset($_SESSION['unique_id'])) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Company</title>

   <!-- font awesome cdn link -->
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
* {
   margin: 0;
   padding: 0;
   box-sizing: border-box;
}

.container {
   max-width: 1200px;
   margin: 20px auto;
   padding: 20px;
}

.products {
   display: grid;
   grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
   gap: 20px;
}

.box {
   background: linear-gradient(to bottom, #FFF5E1, #F7E0C0); /* Soft parchment gradient */
   border: 1px solid #d2b89c; /* Subtle brown border */
   border-radius: 15px;
   overflow: hidden;
   text-align: center;
   padding: 15px;
   transition: transform 0.3s, box-shadow 0.3s;
   box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25); /* Luxurious shadow */
   border: 1px solid #8E5727; /* Muted gold border */
}

.box:hover {
   transform: translateY(-10px);
   box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
}

.box img {
   width: 100%;
   height: 300px;
   object-fit: cover;
   border-radius: 8px;
   margin-bottom: 15px;
   border: 1px solid #d2b89c; /* Matches card border */
}

.box h3 {
   font-size: 1.5rem;
   color: #5e4632; /* Dark brown for headings */
   margin-bottom: 10px;
}

.box p {
   font-size: 1rem;
   color: #7a5c45; /* Medium brown for descriptions */
   margin-bottom: 8px;
}

.box .btn {
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

.box .btn:hover {
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

.box p {
   font-size: 15px; /* Balanced font size */
   color: #5e4632; /* Elegant dark brown for text */
   margin-bottom: 15px; /* Increased spacing for better separation */
   line-height: 1.8; /* Improve readability */
   text-align: left; /* Structured alignment */
   background: #fdf5e6; /* Soft parchment-like background */
   padding: 10px 15px; /* Add padding for a polished look */
   border-radius: 8px; /* Rounded corners for elegance */
   box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
   border: 1px solid #d2b89c; /* Light border for structure */
   transition: background 0.3s ease, transform 0.3s ease; /* Smooth transitions */
}

.box p:hover {
   background: #fde2b8; /* Highlighted background on hover */
   transform: scale(1.02); /* Slight scaling effect for interactivity */
   box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2); /* Enhanced shadow on hover */
   color: #5c2e0a; /* Slightly darker text color on hover */
}

.box p strong {
   color: #7a3b0e; /* Slightly richer tone for emphasis */
   font-weight: bold;
   text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1); /* Subtle text shadow for depth */
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
         <a href="#" class="profile">
    <img src="../uploaded_profile/CH-logo.png" alt="Profile Image">
</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
      <div class="head-title">
				<div class="left">
                <h1> Company List</h1>
					<ul class="breadcrumb">
						<li>
							<a href="company.php">Crafthive</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="company.php">C List</a>
						</li>
					</ul>
				</div>
			</div>

         <div class="container">
      <section class="products">
         <?php
         // Fetch the required columns only
         $select_companys = mysqli_query($conn, "SELECT company_name, location, phone, payment, policy, image FROM `description`");
         
         if ($select_companys && mysqli_num_rows($select_companys) > 0) {
            while ($fetch_company = mysqli_fetch_assoc($select_companys)) {
         ?>
         <div class="box">
            <img src="../../uploaded_c_img/<?php echo htmlspecialchars($fetch_company['image']); ?>" alt="Company Image">
            <h3><?php echo htmlspecialchars($fetch_company['company_name']); ?></h3>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($fetch_company['location']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($fetch_company['phone']); ?></p>
            <p><strong>Payment:</strong> <?php echo htmlspecialchars($fetch_company['payment']); ?></p>
            <p><strong>Policy:</strong> <?php echo htmlspecialchars($fetch_company['policy']); ?></p>
            <form action="company_product.php" method="post">
               <input type="hidden" name="company_name" value="<?php echo htmlspecialchars($fetch_company['company_name']); ?>">
               <input type="submit" class="btn" value="View Products">
            </form>
         </div>
         <?php
            }
         } else {
            echo "<p>No companies found.</p>";
         }
         ?>
      </section>

</div>

<!-- custom js file link -->
<script src="../js/all_script.js"></script>

</body>
</html>
