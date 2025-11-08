<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/design.css">
	<link rel="stylesheet" href="css/additional.css">
    <link rel="stylesheet" href="css/openings.css">

	<!-- My CSS -->
	<title>CraftHive</title>
</head>
<body>

	<!-- SIDEBAR -->
	<section id="sidebar">
		<a href="#" class="brand">
			<i class='bx bxs-smile'></i>
			<span class="text">CraftHive</span>
		</a>
		<ul class="side-menu top">
        <li>
				<a href="opening.php">
					<i class='bx bxs-dashboard' ></i>
					<span class="text">Craft Introduction</span>
				</a>
			</li>
			<li>
				<a href="register_role.php">
					<i class='bx bxs-registered' ></i>
					<span class="text">Craft Registration</span>
				</a>
			</li>

            <li class="active">
				<a href="login_role.php">
					<i class='bx bxs-log-in' ></i>
					<span class="text">Craft Login Process</span>
				</a>
			</li>

            <li>
				<a href="forgot.php">
					<i class='bx bxs-lock' ></i>
					<span class="text">Craft Forgot Pass</span>
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
				<span class="num">0</span>
			</a>
			<a href="#" class="profile">
				<img src="uploaded_profile/CH-logo.png">
			</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Craft Selection</h1>
					<ul class="breadcrumb">
                        <li>
							<a href="#">CraftHive</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="opening.php">C Intro</a>
						</li>
                        <li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="login_role.php">C Select</a>
						</li>
					</ul>
				</div>
			</div>

    <!-- list item -->
    <body>
        <br> <br>
        <div class="container">
    <section class="form login form-container">
    <h3>Select your Role</h3>
        <div class="text-center">
            <div class="button-container">
                <button type="button" onclick="redirectToPage('company-rep')" class="custom-button">Company Rep</button>
                <button type="button" onclick="redirectToPage('seller')" class="custom-button">Seller</button>
                <button type="button" onclick="redirectToPage('buyer')" class="custom-button">Buyer</button>
                <button type="button" onclick="redirectToPage('deliver-personnel')" class="custom-button">Delivery Person</button>
            </div>
        </div>
    </section>
</div>

</div>
<script src="js/all_script.js"></script>

<script>
        // Function to redirect to the corresponding page
        function redirectToPage(accType) {
            if (accType === 'company-rep') {
                window.location.href = '../craft_opening/craft_company/login.php'; // Replace with the actual client page
            } else if (accType === 'seller') {
                window.location.href = '../craft_opening/craft_seller/login.php'; // Replace with the actual seller page
            } else if (accType === 'buyer') {
                window.location.href = '../craft_opening/craft_buyer/login.php'; // Replace with the actual buyer page
            }
            else if (accType === 'deliver-personnel') {
                window.location.href = '../craft_opening/craft_delivery/login.php'; // Replace with the actual buyer page
            }
        }
    </script>
   
</body>
</html>