<?php
session_start(); // Start the session

include_once '../connection/connect.php';// Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $query = $conn->prepare("SELECT unique_id FROM company WHERE email = ?");
    $query->bind_param('s', $email);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $seller = $result->fetch_assoc();

        // Generate a reset code (OTP)
        $reset_code = rand(100000, 999999);

        // Store reset code and email in session
        $_SESSION['reset_code'] = $reset_code;
        $_SESSION['company_email'] = $email;

        // Prepare email content
        $subject = "Password Reset Code for CraftHive";
        $from_name = "CraftHive";
        $content = "Your password reset code is <strong>$reset_code</strong>. Please use this code to reset your password.";

        // Send email
        if (sendEmail($subject, $from_name, $email, $content)) {
            echo "<script>
                    alert('Reset code sent successfully! Redirecting to reset page.');
                    window.location.href = 'reset.php'; // Redirect to reset password page
                  </script>";
        } else {
            echo "<script>alert('Failed to send the reset code. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('No account found with this email. Please check and try again.');</script>";
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

            <li>
				<a href="login.php">
					<i class='bx bxs-log-in' ></i>
					<span class="text">Craft Login Process</span>
				</a>
			</li>

            <li class="active">
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
					<h1>Craft Forgot Pass</h1>
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
							<a class="active" href="../forgot.php">C Select  </a>
						</li>
                        <li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="forgot.php">C Forgot</a>
						</li>
					</ul>
				</div>
			</div>
<br>
    <!-- list item -->
    <div class="input-container">
        <form action="" method="POST">
        <h3> Forget Company Password </h3>    
        <div class = "button-container">
        <input type="email" name="email" id="email" required placeholder = "Enter your Email" class = "box">
        <button type="submit" class = "btn">Send Reset Code</button>
    </div>
        </form>
    </div>

<script src="../js/all_script.js"></script>   
</body>
</html>