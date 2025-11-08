<?php
session_start();
include_once '../connection/connect.php';

// Check if the seller is logged in
if (!isset($_SESSION['unique_id']) || !isset($_SESSION['company_rep_id']) || !isset($_SESSION['company_email'])) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit;
}

// Get session variables
$company_unique_id = $_SESSION['unique_id'];
$company_rep_id = $_SESSION['company_rep_id'];

// Database connection
$host = 'localhost'; // Adjust as needed
$dbname = 'local'; // Replace with your database name
$username = 'root'; // Replace with your DB username
$password = ''; // Replace with your DB password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get values from the form
    $price = trim($_POST['price']); // Changed from amount_paid to price
    $gcash_reference_number = trim($_POST['gcash_reference_number']);

    // Validate input
    if (empty($price) || empty($gcash_reference_number)) {
        echo "<script>
                alert('All fields are required.');
                window.history.back();
              </script>";
        exit;
    }

    // Set current date as start_date
    $start_date = date('Y-m-d'); // Automatically sets current date in YYYY-MM-DD format
    $expiry_date = date('Y-m-d', strtotime('+1 month')); // Set expiry date to one month from start_date

    // Query to get the company details based on the session's unique_id
    $query = "SELECT unique_id, company_name FROM company WHERE unique_id = :unique_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['unique_id' => $company_unique_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Assign the company name to the 'company_name' column
        $company_name = $result['company_name'];

        // Update data in the company table
        $query = "UPDATE company 
                  SET subscription_plan = 'Affordable Plan', 
                      price = :price, 
                      start_date = :start_date, 
                      expiry_date = :expiry_date, 
                      status = 'Active', 
                      payment_status = 'Pending', 
                      gcash_reference_number = :gcash_reference_number
                  WHERE unique_id = :unique_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'price' => $price, // Using 'price' instead of 'amount_paid'
            'start_date' => $start_date,
            'expiry_date' => $expiry_date,
            'gcash_reference_number' => $gcash_reference_number,
            'unique_id' => $company_unique_id
        ]);

        echo "<script>
                alert('Subscription successful for " . htmlspecialchars($company_name) . "!');
                window.location.href = 'dashboard.php';
              </script>";
    } else {
        echo "<script>
                alert('Company not found.');
                window.location.href = 'company_subscribe.php';
              </script>";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Representative Form</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- custom css file link  -->
 
  <!-- Font Awesome CDN link -->
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
            --primary-color: #5D4037;
            --primary-gradient: linear-gradient(135deg, #5D4037, #4A3229);
            --hover-gradient: linear-gradient(135deg, #6F5148, #4A3229);
            --input-bg: #FFFFFF;
            --input-border: #D3C4B1;
            --input-focus-border: #5D4037;
            --font-color: #5D4037;
            --placeholder-color: #A69485;
            --background-color: #FDFBF8;
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

        .container h1 {
            font-size: 2em;
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 20px;
            text-transform: uppercase;
            border-bottom: 2px solid #8E5727;
            padding-bottom: 10px;
        }

        .tooltip {
            background: var(--primary-gradient);
            color: #FFFFFF;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-style: italic;
        }

        label {
            display: block;
            font-size: 1.2em;
            color: var(--font-color);
            margin-bottom: 5px;
        }

        input[type="number"],
        input[type="text"] {
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

        input::placeholder {
            color: var(--placeholder-color);
            font-style: italic;
        }

        input:focus {
            border-color: #D19A6B;
            outline: none;
            box-shadow: 0 0 8px rgba(93, 64, 55, 0.4);
        }

        button {
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

        button:hover {
            background: #7A3B0E;
            transform: scale(1.02);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        img {
            max-width: 500px;
            height: auto;
            border: 1px solid #ccc;
            border-radius: 8px;
        }


.message {
    position: sticky;
    top: 0; 
    left: 0;
    z-index: 10000;
    border-radius: 0.5rem;
    background: #FFF4DE; /* Soft parchment gradient */
    padding: 1.5rem 2rem;
    margin: 1rem auto; /* Added margin for spacing between messages */
    max-width: 1200px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Soft shadow for depth */
    border: 1px solid #D2B48C; /* Light brown border for elegance */
    transition: opacity 0.3s ease; /* Smooth transition for hiding */
}

.message span {
    font-size: 1.2rem; /* Slightly larger for better readability */
    color: #3E2723; /* Rich brown for text */
    font-family: 'Georgia', serif; /* Elegant serif font */
    font-weight: 500; /* Medium weight for sophistication */
}

.message i {
    font-size: 1.5rem; /* Slightly smaller for better proportion */
    color: #3E2723; /* Rich brown for icon */
    cursor: pointer;
    transition: color 0.3s ease, transform 0.3s ease; /* Smooth transition for hover effects */
}

.message i:hover {
    color: #FF5733; /* Elegant red for hover effect */
    transform: scale(1.1); /* Slightly enlarge on hover for emphasis */
}

#content main .head-title .left .breadcrumb li a {
	color: #5C2E0A;
	pointer-events: none;
    font-weight: 500;
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
	</style>


</head>
<body>

<?php
if (isset($message)) {
    foreach ($message as $msg) {
        echo '<div class="message">
                <span>' . htmlspecialchars($msg) . '</span>
                <i class="fas fa-times" onclick="this.parentElement.style.display=\'none\';" aria-label="Close message"></i>
              </div>';
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
					<span class="text">Dashboard</span>
				</a>
			</li>
			<li>
				<a href="description.php">
					<i class='bx bxs-shopping-bag-alt' ></i>
					<span class="text">Description </span>
				</a>
			</li>
			<li>
				<a href="ch-checkout.php">
					<i class='bx bxs-doughnut-chart' ></i>
					<span class="text">Report</span>
				</a>
			</li>
			<li>
				<a href="ch-view.php">
					<i class='bx bxs-group' ></i>
					<span class="text">Management</span>
				</a>
			</li>
		</ul>

		<ul class="side-menu">
			<li class="active">
				<a href="#">
					<i class='bx bxs-cog' ></i>
					<span class="text">Subscription</span>
				</a>
			</li>

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
				<img src="../uploaded_profile/CH-logo.png">
			</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Company Subscription</h1>
					<ul class="breadcrumb">
						<li>
							<a href="ch-company.php">Company</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="ch-company.php">Subscription</a>
						</li>
					</ul>
				</div>
			</div>

     <div class="container">
        <h1>Affordable Plan Subscription</h1>
        <form action="" method="POST">
            <!-- Tooltip for guidance -->
            <div class="tooltip">
                <strong>Affordable Plan Details:</strong><br>
                - <b>₱500/month</b><br>
            </div>

            <!-- Plan Information -->
            <label for="price">Amount Paid (₱500)</label>
            <input type="number" id="price" name="price" value="500" readonly>

            <!-- Payment Details -->
            <label for="gcash_reference_number">GCash Reference Number</label>
            <input type="text" id="gcash_reference_number" name="gcash_reference_number" placeholder="Enter GCash Reference Number" required>

            <div style="text-align: center; margin-top: 15px;">
                <p><strong>Scan this GCash QR Code to Pay:</strong></p>
                <img src="../../uploaded_qr/467824442_1004924294776102_7055858594120192350_n.jpg" alt="GCash QR Code">
            </div>
            <br>
            <!-- Submit Button -->
            <button type="submit">Subscribe</button>
        </form>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
