<?php
session_start(); // Start the session

include_once '../connection/connect.php'; // Include connection and PHPMailer setup

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_code = $_POST['reset_code'];
    $new_password = $_POST['new_password'];

    // Verify reset code and session variables
    if (isset($_SESSION['reset_code']) && isset($_SESSION['delivery_email'])) {
        if ($entered_code == $_SESSION['reset_code']) {
            $email = $_SESSION['delivery_email'];
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the password in the database
            $query = $conn->prepare("UPDATE delivery SET password = ? WHERE email = ?");
            if ($query) { // Ensure the query preparation was successful
                $query->bind_param('ss', $hashed_password, $email);

                if ($query->execute()) {
                    // Clear session variables
                    unset($_SESSION['reset_code'], $_SESSION['delivery_email']);

                    // Redirect with success message
                    echo "<script>
                            alert('Password successfully reset! Redirecting to login page.');
                            window.location.href = 'login.php';
                          </script>";
                    exit();
                } else {
                    // Database update failed
                    echo "<script>alert('Failed to update password. Please try again.');</script>";
                }
                $query->close(); // Close the query if it was created
            } else {
                echo "<script>alert('Failed to prepare the database query. Please contact support.');</script>";
            }
        } else {
            // Invalid reset code
            echo "<script>alert('Invalid reset code. Please check your email and try again.');</script>";
        }
    } else {
        // Session expired or invalid request
        echo "<script>alert('Session expired or invalid request. Please start again.');</script>";
    }

    // Close the database connection
    if ($conn) {
        $conn->close();
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
				<a href="../ch-opening.php">
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

            <li>
				<a href="login.php">
					<i class='bx bxs-log-in' ></i>
					<span class="text">Login</span>
				</a>
			</li>

            <li class="active">
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
					<h1>Craft Reset Pass</h1>
					<ul class="breadcrumb">
                        <li>
							<a href="#">CraftHive</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="../opening.php">Intro</a>
						</li>
                        <li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="../forgot.php"> C Select  </a>
						</li>
                        <li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="forgot.php">C Forgot</a>
						</li>
                        <li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="reset.php">C Reset</a>
						</li>
					</ul>
				</div>
			</div>
<br>
    <!-- list item -->
    <div class="input-container">
        <form action="" method="POST">
        <h3> Reset Delivery Password</h3>    
        <div class = "button-container">
        <input type="text" name="reset_code" id="reset_code" required placeholder= "Enter your Reset Code" class = "box">
        <input type="password" name="new_password" id="new_password" required placeholder= "Enter your new Password" class = "box">

        <button type="submit" class = "btn">Reset Password</button>
    </div>
        </form>
    </div>


    <script src="../js/all_script.js"></script>
   
</body>
</html>