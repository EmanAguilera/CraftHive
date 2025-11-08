<?php
session_start();

// Handle form submission for registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $company_rep_id = $_POST['company_rep_id'];

    // Function to generate a unique seller ID (integer)
    function generateUniqueId() {
        // Generate a random number between 100000000 and 999999999
        return mt_rand(100000000, 999999999);
    }

    $unique_id = generateUniqueId();

    // Database connection
    $conn = new mysqli('localhost:3307', 'root', '', 'local');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the provided `company_rep_id` exists in `company_acc`
    $check_query = "SELECT company_rep_id FROM company WHERE company_rep_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param('s', $company_rep_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "<script type='text/javascript'>alert('Invalid Company Rep ID. Please enter a valid ID.');</script>";
    } else {
        // Check if email or phone already exists in `seller`
        $query = "SELECT email, phone FROM delivery WHERE email = ? OR phone = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ss', $email, $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script type='text/javascript'>alert('The email or phone number already exists. Please use a different one.');</script>";
        } else {
            // Insert new record into `seller`
            $insert_query = "INSERT INTO delivery (unique_id, company_rep_id, fname, lname, phone, email, password, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'Active', now())";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param('issssss', $unique_id, $company_rep_id, $fname, $lname, $phone, $email, $password);

            if ($stmt->execute()) {
                $_SESSION['unique_id'] = $unique_id;
                $_SESSION['company_rep_id'] = $company_rep_id;
                header("Location: login.php");
                exit();
            } else {
                echo "<script type='text/javascript'>alert('Error: " . $stmt->error . "');</script>";
            }
        }
    }
    $stmt->close();
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
			<li class="active">
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
					<h1>Craft Registration</h1>
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
							<a class="active" href="../register_role.php">C Select</a>
						</li>
                        <li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="register.php">C Register</a>
						</li>
					</ul>
				</div>
			</div>
<br>
    <!-- list item -->
    <div class="input-container">
    <form action="register.php" method="POST">
        <h3>Register for Delivery Person</h3>
        <div class="form-grid">
            <div class="form-group">
                <input type="text" name="fname" id="fname" required placeholder="Enter your First Name" class="box">
            </div>
            <div class="form-group">
                <input type="text" name="lname" id="lname" required placeholder="Enter your Last Name" class="box">
            </div>
            <div class="form-group">
                <input type="text" id="company_rep_id" name="company_rep_id" required placeholder="Enter your Company Rep ID" class="box">
            </div>
            <div class="form-group">
                <input type="tel" name="phone" id="phone" required placeholder="Enter your Phone" class="box">
            </div>
        </div>
        <input type="email" name="email" id="email" required placeholder="Enter your Email" class="box">
        <input type="password" name="password" id="password" required placeholder="Enter your Password" class="box">
        <button type="submit" class="btn">Register</button>
    </form>
</div>

<script src="../js/all_script.js"></script>
   
</body>
</html>