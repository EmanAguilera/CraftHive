<?php
session_start(); // Start the session

include_once '../connection/connect.php'; // Include connection and PHPMailer setup

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : ''; // Get the entered password

    // Fetch seller data (including unique_id, password, and company_rep_id) from the database
    $query = $conn->prepare("SELECT unique_id, password, company_rep_id FROM seller WHERE email = ?");
    $query->bind_param('s', $email); // Bind the email parameter
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $seller = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $seller['password'])) {
            // Generate OTP
            $otp = rand(100000, 999999);

            // Store OTP, email, unique_id, and company_rep_id in the session
            $_SESSION['otp'] = $otp;
            $_SESSION['seller_email'] = $email;
            $_SESSION['unique_id'] = $seller['unique_id'];
            $_SESSION['company_rep_id'] = $seller['company_rep_id']; // Store the company_rep_id

            // Prepare email content
            $subject = "Your OTP for CraftHive Login";
            $from_name = "CraftHive";
            $content = "Your OTP is <strong>$otp</strong>. Please use this to complete your login.";

            // Send email using the included `sendEmail` function
            if (sendEmail($subject, $from_name, $email, $content)) {
                echo "OTP sent successfully!";
                // Redirect to OTP verification page
                header("Location: verify.php");
                exit();
            } else {
                echo "<script>alert('Failed to send OTP. Please try again.');</script>";
            }
        } else {
            // Incorrect password
            echo "<script>alert('Incorrect password or email. Please try again.');</script>";
        }
    } else { 
        // No seller found with the email
        echo "<script>alert('Incorrect password or email. Please try again.');</script>";
    }

    $query->close();
    $conn->close();
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
					<span class="text">Craft Introduction</span>
				</a>
			</li>
			<li>
				<a href="register.php">
					<i class='bx bxs-registered' ></i>
					<span class="text">Craft Registration</span>
				</a>
			</li>

            <li class="active">
				<a href="login.php">
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
				<img src="../uploaded_profile/CH-logo.png">
			</a>
		</nav>
		<!-- NAVBAR -->
		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Craft Login Process</h1>
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
							<a class="active" href="register.php">C Register</a>
						</li>
                        <li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="login.php">C Login</a>
						</li>
					</ul>
				</div>
			</div>
<br>
    <!-- list item -->
    <div class="input-container">
        <form action="login.php" method="POST">
            <h3> Login for Seller </h3>
        <div class = "button-container">
            <input type="email" name="email" id="email" required placeholder = "Enter your Email" class = "box">
  
            <input type="password" name="password" id="password" required placeholder = "Enter your Password" class = "box">

            <button type="submit" class = "btn">Login</button>
    </div>
        </form>
    </div>


    <script src="../js/all_script.js"></script>
   
</body>
</html>