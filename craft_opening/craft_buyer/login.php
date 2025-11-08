<?php
session_start(); // Start the session

$error = ''; // Initialize error variable

// Handle form submission for login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'local');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Use prepared statements to prevent SQL injection
    $query = $conn->prepare("SELECT unique_id, password FROM buyer WHERE email = ?");
    $query->bind_param('s', $email); // Bind the email parameter
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Store user information in the session
            $_SESSION['unique_id'] = $user['unique_id']; // Correct session key
            header("Location: company.php"); // Redirect to the dashboard
            exit();
        } else {
            $error = "Invalid password or user."; // Incorrect password
        }
    } else {
        $error = "No user found with this email."; // No user found with the provided email
    }

    $conn->close();
}

if (!empty($error)) {
    echo "<script type='text/javascript'>alert('$error');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

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
					<h1>Buyer's Login</h1>
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
							<a class="active" href="register.php">C Register </a>
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
            <h3> Login for Buyer </h3>
            <input type="email" name="email" id="email" required placeholder = "Enter your Email" class ="box" >
  
            <input type="password" name="password" id="password" required placeholder = "Enter your Password" class = "box">

            <button type="submit" class = "btn">Login</button>
        </form>
    </div>

<script src="../js/all_script.js"></script>
   
</body>
</html>