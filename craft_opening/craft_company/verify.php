<?php
session_start(); // Start the session

// Check if OTP, seller_email, and unique_id are set in the session
if (!isset($_SESSION['otp']) || !isset($_SESSION['company_email']) || !isset($_SESSION['unique_id']) || !isset($_SESSION['company_rep_id'])  ) {
    echo "<script>
            alert('Session expired or unauthorized access.');
            window.location.href = 'login.php'; // Redirect to login page or appropriate location
          </script>";
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = $_POST['otp'];

    // Validate OTP
    if ($entered_otp == $_SESSION['otp']) {
        // OTP verified successfully
        echo "<script>
                alert('OTP verified successfully! Redirecting to your dashboard.');
                window.location.href = 'dashboard.php'; // Redirect to the dashboard or homepage
              </script>";

        // Optional: Clear OTP from session after verification
        unset($_SESSION['otp']);
        exit();
    } else {
        // Incorrect OTP
        echo "<script>
                alert('Invalid OTP. Please try again.');
              </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/design.css">
	<link rel="stylesheet" href="../css/additional.css">
    <link rel="stylesheet" href="../css/openings.css">

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
				<a href="../opening.php">
					<i class='bx bxs-dashboard' ></i>
					<span class="text">Introduction</span>
				</a>
			</li>
			<li>
				<a href="register.php">
					<i class='bx bxs-registered' ></i>
					<span class="text">Registration</span>
				</a>
			</li>

            <li class="active">
				<a href="login.php">
					<i class='bx bxs-log-in' ></i>
					<span class="text">Login</span>
				</a>
			</li>

            <li>
				<a href="forgot.php">
					<i class='bx bxs-lock' ></i>
					<span class="text">Forgot Password</span>
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
				<img src="../uploaded_profile/CH-logo.png">
			</a>
		</nav>
		<!-- NAVBAR -->
		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Craft Verify OTP</h1>
					<ul class="breadcrumb">
                        <li>
							<a href="#">CraftHive</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="../opening.php">C Intro</a>
						</li>
                        <li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="../login_role.php">C Select</a>
						</li>
                        <li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="login.php">C Login</a>
						</li>
                        <li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="verify.php">C Verify</a>
						</li>
					</ul>
				</div>
			</div>
<br>
    <!-- list item -->
    <div class="input-container">
        <h3> Verify Company OTP </h3>
        <form action="" method="POST">
        <input type="number" name="otp" id="otp" required placeholder = "Enter your OTP" class = "box">
        <button type="submit" class = "btn">Verify OTP</button>
        </form>
    </div>


    <script src="../js/all_script.js"></script>
   
</body>
</html>