<?php

session_start();
@include '../connection/connect.php';

// Check if the seller is logged in
if (!isset($_SESSION['unique_id'])) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit;
}

// Get session variables
$admin_unique_id = $_SESSION['unique_id'];

$dsn = "mysql:host=localhost;dbname=local;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, "root", "", $options);  // Create PDO object
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Query to count total company representatives
$countQuery = "SELECT COUNT(company_id) AS total_company_rep FROM company";
$countResult = mysqli_query($conn, $countQuery);

// Fetch the result of the count query
if ($countResult) {
  $countRow = mysqli_fetch_assoc($countResult);
  $total_company_rep = $countRow['total_company_rep'];
} else {
  die("Error fetching count: " . mysqli_error($conn));
}

// Query to fetch company representatives
$sql = "SELECT company_id, fname, lname, email, company_name, phone, subscription_plan, price, start_date, expiry_date, total_sellers, status 
      FROM company";
$companyRepsResult = $conn->query($sql);

// Check if the company representatives query was successful
if (!$companyRepsResult) {
  die("Error executing query: " . $conn->error);
}

$sellersQuery = "SELECT SUM(total_sellers) AS total_sellers FROM company";
$sellersResult = mysqli_query($conn, $sellersQuery);

// Fetch the result of the total sellers query
if ($sellersResult) {
  $sellersRow = mysqli_fetch_assoc($sellersResult);
  $total_sellers = $sellersRow['total_sellers'] ?? 0; // Default to 0 if null
} else {
  die("Error fetching total sellers: " . mysqli_error($conn));
}

$earningsQuery = "SELECT SUM(price) AS price FROM company";
$earningsResult = mysqli_query($conn, $earningsQuery);

// Fetch the result of the total sellers query
if ($earningsResult) {
  $earningsRow = mysqli_fetch_assoc($earningsResult);
  $earnings = $earningsRow['price'] ?? 0; // Default to 0 if null
} else {
  die("Error fetching total earnings: " . mysqli_error($conn));
}

// Fetch subscription plan data
$subscriptionQuery = "SELECT subscription_plan, COUNT(*) AS count 
                    FROM company 
                    GROUP BY subscription_plan";
$subscriptionResult = mysqli_query($conn, $subscriptionQuery);

$subscriptionData = [];
while ($row = mysqli_fetch_assoc($subscriptionResult)) {
  $subscriptionData[$row['subscription_plan']] = $row['count'];
}
$timeframe = isset($_GET['timeframe']) ? $_GET['timeframe'] : 'monthly';

switch ($timeframe) {
  case 'daily':
  $dateGroup = "DATE(updated_at)"; // Group by day
  $dateFormat = "DATE_FORMAT(updated_at, '%b %d, %Y')"; // Format as readable date
  break;
      case 'weekly':
          $dateGroup = "YEARWEEK(updated_at, 1)"; // Group by ISO week number
          $dateFormat = "CONCAT(
              DATE_FORMAT(STR_TO_DATE(CONCAT(YEAR(updated_at), WEEK(updated_at, 1), '1'), '%X%V%w'), '%b %d, %Y'), 
              ' - ', 
              DATE_FORMAT(STR_TO_DATE(CONCAT(YEAR(updated_at), WEEK(updated_at, 1), '1'), '%X%V%w') + INTERVAL 6 DAY, '%b %d, %Y')
          )"; 
          break;
          case 'monthly':
              $dateGroup = "DATE_FORMAT(updated_at, '%Y-%m')"; // Group by month
              $dateFormat = "DATE_FORMAT(updated_at, '%b %Y')"; // Format as 'Nov 2024'
              break;
  case 'yearly':
      $dateGroup = "YEAR(updated_at)";
      $dateFormat = "DATE_FORMAT(updated_at, '%Y')";
      break;
  default:
      $dateGroup = "DATE_FORMAT(updated_at, '%Y-%m')";
      $dateFormat = "%b %Y";
}

$earningsQuery = "
  SELECT 
      $dateGroup AS period,
      $dateFormat AS formatted_period,
      SUM(price) AS total_earnings 
  FROM company
  WHERE updated_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
  GROUP BY period
  ORDER BY period ASC
";


$earningsResult = mysqli_query($conn, $earningsQuery);

if (!$earningsResult) {
  die("Query error: " . mysqli_error($conn));
}

$earningsData = [];
while ($row = mysqli_fetch_assoc($earningsResult)) {
  $earningsData[$row['formatted_period']] = $row['total_earnings'];
}


// Fill missing months with 0
$start = new DateTime('-11 months'); // Start from 11 months ago
$end = new DateTime(); // Current month
$interval = new DateInterval('P1M'); // Monthly interval
$periods = new DatePeriod($start, $interval, $end);

// Create formatted labels for display
$formattedEarningsData = [];
foreach ($periods as $date) {
  $monthYear = $date->format('Y-m'); // Format as YYYY-MM
  $formattedMonthYear = $date->format('M Y'); // Format as Jan 2024
  $formattedEarningsData[$formattedMonthYear] = $earningsData[$monthYear] ?? 0; // Use formatted month with fallback
}

// Convert the data for JavaScript
$labels = json_encode(array_keys($formattedEarningsData)); // Month names
$data = json_encode(array_values($formattedEarningsData)); // Earnings

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'addRepresentative') {
    $rep_fname = $_POST['rep_fname'];
    $rep_lname = $_POST['rep_lname'];
    $rep_email = $_POST['rep_email'];
    $rep_phone = $_POST['rep_phone'];
    $rep_company_name = $_POST['rep_company_name'];
    $rep_subscription_plan = $_POST['rep_subscription_plan'];
    $rep_price = $_POST['rep_price'];
    $rep_start_date = $_POST['rep_start_date'];
    $rep_expiry_date = $_POST['rep_expiry_date'];

    $unique_id = mt_rand(100000, 999999); // Format: 123456 (integer)

    // Generate company_rep_id in the format of 97C7AF22-2470
    $company_rep_id = strtoupper(bin2hex(random_bytes(4)) . '-' . bin2hex(random_bytes(2)));

    // File upload handling
    $target_dir = "../../uploaded_profile/";
    $rep_profile_img = $_FILES['rep_profile_img']['name'] ?? '';
    $target_file = $target_dir . basename($rep_profile_img);

    if (!empty($rep_profile_img) && move_uploaded_file($_FILES['rep_profile_img']['tmp_name'], $target_file)) {
        // Database connection
        $conn = new mysqli('localhost', 'root', '', 'local');
        if ($conn->connect_error) {
            die('Database connection failed: ' . $conn->connect_error);
        }

        // Insert data into the database
        $stmt = $conn->prepare("INSERT INTO company (unique_id, company_rep_id, fname, lname, email, phone, company_name, subscription_plan, price, start_date, expiry_date, img) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssssisss', $unique_id, $company_rep_id, $rep_fname, $rep_lname, $rep_email, $rep_phone, $rep_company_name, $rep_subscription_plan, $rep_price, $rep_start_date, $rep_expiry_date, $rep_profile_img);

        if ($stmt->execute()) {
            $message = "Representative added successfully!";
        } else {
            $message = "Database error: " . $stmt->error;
        }
    } else {
        $message = "Error uploading profile image.";
    }
    echo "<script>alert('$message');</script>";
}
$sql = "SELECT 
    cra.company_id AS rep_id,
    cra.fname AS rep_fname,
    cra.lname AS rep_lname,
    cra.email AS rep_email,
    cra.company_name AS rep_company_name,
    cra.phone AS rep_phone,
    cra.status AS rep_status,
    cra.subscription_plan AS rep_subscription_plan,
    cra.price AS rep_price,
    cra.total_sellers AS rep_total_sellers,
    cra.start_date AS rep_start_date,
    cra.expiry_date AS rep_expiry_date,
    sa.seller_id AS seller_id,
    sa.fname AS seller_fname,
    sa.lname AS seller_lname,
    sa.email AS seller_email,
    sa.phone AS seller_phone,
    sa.status AS seller_status,
    sa.company_rep_id AS seller_company_rep_id,
    da.deliver_id AS deliver_id,
    da.fname AS delivery_fname,
    da.lname AS delivery_lname,
    da.email AS delivery_email,
    da.phone AS delivery_phone,
    da.status AS delivery_status,
    da.total_delivered AS delivery_total_delivered,
    da.company_rep_id AS delivery_company_rep_id
FROM 
    company cra
LEFT JOIN 
    seller sa ON cra.company_rep_id = sa.company_rep_id
LEFT JOIN 
    delivery da ON cra.company_rep_id = da.company_rep_id
WHERE 
    cra.status = 'Active'";

// Execute the query
$result = $conn->query($sql);

$companyReps = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $repId = $row['rep_id'];

        // Initialize representative data
        if (!isset($companyReps[$repId])) {
            $companyReps[$repId] = [
                'rep_fname' => $row['rep_fname'],
                'rep_lname' => $row['rep_lname'],
                'rep_email' => $row['rep_email'],
                'rep_phone' => $row['rep_phone'],
                'rep_company_name' => $row['rep_company_name'],
                'rep_status' => $row['rep_status'],
                'rep_subscription_plan' => $row['rep_subscription_plan'],
                'rep_price' => $row['rep_price'],
                'rep_total_sellers' => $row['rep_total_sellers'],
                'rep_start_date' => $row['rep_start_date'],
                'rep_expiry_date' => $row['rep_expiry_date'],
                'rep_img' => $row['rep_img'],
                'sellers' => [],
                'deliveries' => []
            ];
        }

        // Add sellers under the correct representative
        if (!empty($row['seller_id'])) {
            $companyReps[$repId]['sellers'][] = [
                'seller_fname' => $row['seller_fname'],
                'seller_lname' => $row['seller_lname'],
                'seller_email' => $row['seller_email'],
                'seller_phone' => $row['seller_phone'],
                'seller_status' => $row['seller_status'],
                'seller_total_products' => $row['seller_total_products']
            ];
        }

        // Add deliveries under the correct representative
        if (!empty($row['deliver_id'])) {
            $companyReps[$repId]['deliveries'][] = [
                'delivery_fname' => $row['delivery_fname'],
                'delivery_lname' => $row['delivery_lname'],
                'delivery_email' => $row['delivery_email'],
                'delivery_phone' => $row['delivery_phone'],
                'delivery_status' => $row['delivery_status'],
                'delivery_total_delivered' => $row['delivery_total_delivered']
            ];
        }
    }
}

$deliveriesQuery = "SELECT SUM(total_delivered) AS total_delivered FROM delivery WHERE status = 'Active'";
$deliveriesResult = mysqli_query($conn, $deliveriesQuery);

$total_delivered = 0; // Default to 0
if ($deliveriesResult) {
    $deliveriesRow = mysqli_fetch_assoc($deliveriesResult);
    $total_delivered = $deliveriesRow['total_delivered'] ?? 0; // Use 0 if no rows
} else {
    die("Error fetching total delivered: " . mysqli_error($conn));
}



// Print or use $companyReps as needed

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['company_id'])) {
    $company_id = $_POST['company_id'];

    // Update the status for the account
    $stmt = $conn->prepare("UPDATE company SET status = 'Inactive' WHERE company_id = ?");
    $stmt->bind_param('i', $company_id);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}

// Output or use $companyReps as needed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Request</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/design.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

    <script src="https://cdn.lordicon.com/lordicon.js"></script>
     
    <!-- Chart.js cdn link -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"> </script>

    <style>
#content main .head-title .left .breadcrumb li a {
	color: #5C2E0A;
	pointer-events: none;
    font-weight: 500;
}
        .dropdown ul li {
           text-align: center;
        }

        
        .container .content .cards {
    padding: 20px 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
}

.container .content .cards .card {
    width: calc(25% - 10px); /* 4 cards per row with some spacing */
    height: 135px;
    background: linear-gradient(135deg, #8E5923, #FEDFB1);
    margin: 5px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    border: 2px solid #5C2E0A;
    position: relative;
    overflow: hidden;
}


.container .content .cards .card .box {
    padding-left: 10px;
    color: #5C2E0A; /* Text with vintage hue */
    z-index: 1;
}

.container .content .cards .card h1 {
    font-size: 36px;
    font-weight: bold;
    margin: 0;
}

.container .content .cards .card h3 {
    font-size: 18px;
    margin: 5px 0;
    text-transform: uppercase;
}

.container .content .cards .card .icon-case {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #EE7E1A;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    z-index: 1;
}

.container .content .cards .card::before {
    content: "";
    position: absolute;
    width: 150%;
    height: 150%;
    background: rgba(121, 42, 3, 0.15);
    clip-path: polygon(0 0, 60% 0, 30% 100%, 0% 100%);
    top: -20%;
    left: -20%;
    z-index: 0;
}

.container .content .cards .card:hover {
    background: linear-gradient(135deg, #792A03, #C69B68);
    transform: scale(1.05);
    transition: all 0.3s ease-in-out;
}

.container .content .cards .card:hover h1,
.container .content .cards .card:hover h3 {
    color: #FFF3DC; /* Text changes to a light vintage tone */
}

.container .content .cards .card:hover .icon-case {
    background: #5C2E0A; /* Icon background darkens */
}

@media (max-width: 768px) {
    .container .content .cards .card {
        width: calc(100% - 10px); /* 1 card per row */
        height: 180px; /* Adjust the card height for mobile */
    }

    .container .content .content-2 {
        justify-content: center; /* Center content for smaller screens */
    }
}

.container .content .content-2 {
    min-height: 60vh;
    display: flex;
    justify-content: space-around;
    align-items: flex-start;
    flex-wrap: wrap;
}
.container .content .content-2 .recent-payments {
    min-height: 50vh;
    flex: 5;
    background: #FEDFB1;
    margin: 0 25px 25px 25px;
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    display: flex;
    flex-direction: column;
}

.container .content .content-2 .new-students{
    flex: 2;
    background: #FEDFB1;
    min-height: 50vh;
    margin: 0 25px;
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    display: flex;
    flex-direction: column;
}
.container .content .content-2 .new-students table td:nth-child(1) img{
    height: 40px;
    width: 40px;
}


.btn{
    background: #5C2E0A;
    color: white;
    padding: 5px 10px;
    text-align: center;
}
.btn:hover{
    color: #f05462;  
    background: white;   
    padding: 3px 8px;
    border: 2px solid #5C2E0A;
 }


.btn{
    background: #5C2E0A;
    color: white;
    padding: 5px 10px;
    text-align: center;
}
.btn:hover{
    color: #f05462;  
    background: white;   
    padding: 3px 8px;
    border: 2px solid #5C2E0A;
 }
 .title{
    display: flex;
    align-items: center;
    justify-content: space-around;
    padding: 15px 10px;
    border-bottom: 2 solid #999;
 }
/* Flex container for charts */
.charts-container {
    display: flex;
    flex-direction: column; /* Stack charts vertically */
    gap: 20px; /* Space between the chart containers */
    margin-top: -10px;
    padding: 20px;
    margin-bottom: -20px;
}

/* Style for individual chart containers */
.chart-container2 {
    width: 100%; /* Make the width responsive */
    height: 550px; /* Set a base height for the chart container */
    max-width: 1000px; /* Limit the width for large screens */
    margin: 20px auto; /* Center-align the container */
    padding: 20px;
    background: linear-gradient(90deg, #8E5923, #FEDFB1);
    border: 2px solid #5C2E0A;
    border-radius: 12px 4px;
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden; /* Prevent overflow of content */
}

.chart-container2:hover {
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); /* Enhance shadow on hover */
}

/* Heading style */
.chart-container2 h3 {
    color: #5C2E0A;
    font-size: 1.9em;
    margin-bottom: 20px;
    text-transform: uppercase;
    font-weight: bold;
}

/* Chart canvas style */
.chart-container2 canvas {
    width: 100%; /* Make the canvas fill the container */
    height: 100%; /* Make the canvas fill the container */
    min-height: 200px; /* Ensure a minimum height for legibility */
    background: #FFF3DC;
    border-radius: 6px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow */
}

/* Timeframe button container */
.timeframe-buttons {
    display: flex;
    justify-content: center; /* Center-align buttons */
    gap: 15px; /* Space between buttons */
    margin: 15px 0 20px 0; /* Adjust spacing above and below */
}

/* Button styles */
.timeframe-btn {
    background-color: #C69B68; /* Warm vintage beige */
    color: #5C2E0A; /* Deep brown text */
    border: 2px solid #8E5923; /* Muted gold border */
    padding: 10px 20px;
    border-radius: 5px;
    font-size: 14px; /* Slightly larger text */
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.4s ease, transform 0.3s ease;
    box-shadow: -4px 4px 10px rgba(0, 0, 0, 0.2), inset -2px -2px 4px rgba(255, 255, 255, 0.3); /* Subtle shadow and highlight */
}

.timeframe-btn:hover {
    background-color: #FEDFB1; /* Soft peach tone for hover */
    box-shadow: -6px 6px 12px rgba(0, 0, 0, 0.3), inset -3px -3px 6px rgba(255, 255, 255, 0.4);
    transform: translateY(-3px); /* Slight hover lift effect */
}

.timeframe-btn:active {
    background-color: #EE7E1A; /* Bright orange for active state */
    transform: scale(1.05); /* Slight zoom-in effect on click */
}

.timeframe-btn:focus {
    outline: none;
    box-shadow: 0px 0px 12px rgba(136, 89, 35, 0.6), inset -3px -3px 6px rgba(255, 255, 255, 0.5); /* Golden glow for focus */
}

/* Media Queries */

/* For tablets (screens with a max-width of 1024px) */
@media (max-width: 1024px) {
    .chart-container2 {
        height: 550px; /* Adjust height for tablets */
        margin: 15px auto;
    }

    .chart-container2 h3 {
        font-size: 1.7em; /* Reduce heading size on tablets */
    }

    .timeframe-buttons {
        gap: 10px; /* Reduce gap between buttons */
    }

    .timeframe-btn {
        font-size: 13px; /* Adjust button font size */
        padding: 8px 16px; /* Adjust padding */
    }
}

/* For mobile devices (screens with a max-width of 768px) */
@media (max-width: 768px) {
    .chart-container2 {
        height: 540px; /* Adjust height for mobile */
        margin: 10px auto;
        padding: 15px;
    }

    .chart-container2 h3 {
        font-size: 1.5em; /* Adjust font size of heading */
        margin-bottom: 15px;
    }

    .timeframe-buttons {
        flex-direction: column; /* Stack buttons vertically */
        gap: 8px; /* Reduced gap between stacked buttons */
    }

    .timeframe-btn {
        font-size: 12px; /* Smaller font size for mobile */
        padding: 6px 12px; /* Reduced padding */
    }
}

/* For very small mobile screens (screens with a max-width of 480px) */
@media (max-width: 480px) {
    .chart-container2 {
        display: none; /* Hide the chart on mobile */
    }
}



td{
    text-transform: none;
}

.product-info {
    max-width: 200px; /* Adjust the maximum width as needed */
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.modal {
   display: none;
   position: fixed;
   z-index: 9999;
   left: 0;
   top: 0;
   width: 100%;
   height: 100%;
   overflow: auto; /* Allow vertical scrolling within the modal */
   background: rgba(0, 0, 0, 0.8);
}

.modal-content {
    max-width: 50rem;
    background-color: #F0CCAB;
    border-radius: .5rem;
    padding:2rem;
    margin:0 auto;
    margin-top: 2rem;
}

.close {
   position: absolute;
   top: 10px;
   right: 10px;
   font-size: 24px;
   color: #888;
   cursor: pointer;
}

.close:hover,
.close:focus {
   color: #000;
}

/* Modal Header */
.modal-header {
   padding-bottom: -50px;
   margin-bottom: 10px;
   position: relative;
   transform: translateY(-1%);
}


/* Form Fields */
.form-group {
   margin-bottom: 20px;      
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="tel"],
.form-group input[type="number"],
.form-group input[type="file"] {
   width: 100%;
   padding: 15px;
   font-size: 16px;
   border: 1px solid #ccc;
   border-radius: 5px;
   transition: border-color 0.3s;
   background-color: white;
}

.form-group input[type="text"]:focus,
.form-group input[type="email"]:focus,
.form-group input[type="tel"]:focus,
.form-group input[type="number"]:focus,
.form-group input[type="file"]:focus {
   outline: none;
   border-color: #5d3b27;
}

.grid-item input[type="text"],
.grid-item input[type="email"],
.grid-item input[type="tel"],
.grid-item input[type="number"],
.grid-item input[type="file"],
.grid-item textarea {
   text-transform: none;
}


/* Submit Button */
.btn-submit {
   display: flex;
   align-items: center;
   justify-content: center;
   width: 100%; /* Adjust width as needed */
   height: 55px;
   background-color: #5d3b27;
   color: #fff;
   border: none;
   border-radius: 5px;
   cursor: pointer;
   transition: background-color 0.3s;
   font-size: 1.7rem;
   margin-bottom: 5px;
}

.btn-submit:hover {
    background-color: #4d2813;
}

body.modal-open {
   overflow: hidden;
}

.grid-container {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 18px; /* Adjust the gap between grid items */
}

.grid-item {
    width: 100%; /* Ensure grid items take full width of their container */
}

/* Responsive design: switch to single column layout on smaller screens */
@media screen and (max-width: 600px) {
.grid-container {
grid-template-columns: 1fr; /* Switch to single column layout */
}
}


.select-wrapper {
    position: relative;
    display: inline-block;
    width: 100%;
}

.checkbox-container {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    padding: 5px;
    background-color: #fff;
    border: 1px solid #ccc;
    border-top: none;
    z-index: 1;
    font-size: 15px;
}

.checkbox-label {
    display: block;
    margin-bottom: 5px;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    margin-right: 5px;
}

.checkbox-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr); /* Two columns */
    gap: 10px; /* Adjust the gap between checkboxes */
}

.column {
    display: inline-block;
    vertical-align: top; /* Align columns to the top */
    width: 45%; /* Adjust column width as needed */
}

.product-action {
    width: 100px; /* Adjust the width as needed */
}

.product-image .image-container {
    width: 200px; /* Set the width of the container */
    height: 160px; /* Set the height of the container */
    display: flex; /* Use flexbox for centering */
    justify-content: center; /* Horizontally center the image */
    align-items: center; /* Vertically center the image */
    overflow: hidden; /* Hide any overflowing content */
    transform: translateX(13.5%);
}

.product-image .image-container img {
    max-width: 100%; /* Ensure the image does not exceed the container width */
    max-height: 100%; /* Ensure the image does not exceed the container height */
    display: block; /* Ensure image is displayed as a block element */
    margin: auto; /* Center the image within the container */
}

.file-label {
    display: inline-block;
    padding: 15px 20px;
    width: 100%;
    max-width: 460px;
    background-color: #f2f2f2;
    border: 1px solid #ccc;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
    text-align: left;
}

.file-label:hover {
    background-color: #e0e0e0;
}

.file-name {
    margin-top: 5px;
    font-size: 14px;
    color: #555;
    white-space: nowrap; 
    overflow: hidden; 
    text-overflow: ellipsis; 
    max-width: 460px; /* Adjust to match the file label width */
}

.option-btn{
    margin-bottom: 10px;

}

/* Modal container */
/* Modal container */
#addProductModal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgb(0,0,0);
    background-color: rgba(0,0,0,0.8); /* Dim background for focus */
    z-index: 9999;
    overflow: auto;
    padding-top: 60px;
}

/* Modal content */
.modal-content {
    background: linear-gradient(to bottom, #FFF5E1, #F7E0C0); /* Soft parchment gradient */
    border-radius: 15px;
    padding: 40px;
    max-width: 60%;
    margin: auto;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25); /* Luxurious shadow */
    border: 1px solid #8E5727; /* Muted gold border */
    font-family: 'Playfair Display', serif; /* Vintage typography */
    position: relative;
    overflow: hidden;
}

/* Modal header */
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #8E5727; /* Decorative border */
    padding-bottom: 15px;
}

.modal-header h1 {
    font-size: 2.5em;
    color: #3E2723; /* Rich brown */
    margin: 0;
    text-align: center;
    font-family: 'Playfair Display', serif;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.modal-header h2 {
    font-size: 2em;
    color: #3E2723; /* Rich brown */
    margin: 0;
    text-align: center;
    font-family: 'Playfair Display', serif;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.modal-header .close {
    color: #3E2723;
    font-size: 2.5em;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.3s ease, color 0.3s ease;
}

.modal-header .close:hover {
    color: #EE7E1A; /* Muted orange for a warm touch */
    transform: rotate(90deg); /* Elegant hover effect */
}

/* Modal body */
.modal-body {
    font-size: 1.2em;
    line-height: 1.8;
    color: #5C4033; /* Warm brown */
    margin-top: 20px;
    text-align: justify;
    font-family: 'Libre Baskerville', serif;
}

/* Section headers within the modal */
.modal-body h4 {
    font-size: 1.8em;
    color: #8E5727; /* Muted gold */
    margin: 30px 0 20px;
    padding-bottom: 10px;
    border-bottom: 2px dashed #8E5727; /* Subtle vintage divider */
    text-transform: capitalize;
    text-align: center;
}

/* Decorative profile section */
.profile-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    gap: 20px;
}

.profile-container .profile-img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 5px solid #8E5727; /* Muted gold frame */
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    margin-top: 90px;
}

.profile-container .profile-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.profile-container .profile-img img:hover {
    transform: scale(1.1); /* Slight zoom for elegance */
}

.profile-container .profile-info {
    flex: 1;
}

.profile-info h2 {
    font-size: 2.2em;
    color: #3E2723;
    margin: 0 0 10px;
    font-family: 'Playfair Display', serif;
}

.profile-info h3 {
    font-size: 1.5em;
    color: #8E5727; /* Muted gold */
    margin: 0 0 15px;
    font-family: 'Georgia', serif;
    font-style: italic;
}

.profile-info p {
    font-size: 16px;
    line-height: 1.6;
    background: linear-gradient(to right, #FFF5E1, #FEE7C3); /* Subtle gradient */
    padding: 15px;
    border-left: 4px solid #8E5727; /* Decorative border */
    text-indent: 25px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    border-radius: 8px;
    font-family: 'Libre Baskerville', serif;
    position: relative;
    margin-bottom: 10px;
    text-transform: none;
}

.profile-info p::before {
    content: "❧"; /* Decorative symbol */
    position: absolute;
    top: 10px;
    left: -20px;
    color: #8E5727;
    font-size: 1.5em;
    font-family: 'Georgia', serif;
}

/* Table Styling */
.sellers-table {
    width: 50%;
    text-align: center;
    border-collapse: collapse;
    margin-top: 20px;
    font-family: 'Lato', sans-serif; /* Modern, clean sans-serif */
}

.sellers-table thead th {
    padding: 10px;
    font-size: 1.2em;
    background-color: #4D2813;
    color: #FFF5E1; /* Creamy white */
    border: 1px solid #5C4033;
}

.sellers-table td {
    padding: 10px;
    font-size: 5px;
    color: #3E2723;
    border: 1px solid #8E5727;
}

.sellers-table tr:nth-child(even) {
    background-color: #FEE7C3; /* Light vintage beige */
}

.sellers-table .empty {
    font-size: 1.2em;
    color: #5C4033;
    background-color: #FFF5E1;
    padding: 15px;
}

/* Modal body */
.modal-body {
    margin-top: 20px;
}

.modal-body label {
    display: block;
    font-size: 14px;
    color: #5C2E0A;
    margin-bottom: 5px;
}

.input-container {
    position: relative; /* Position relative to allow absolute positioning of the pseudo-element */
}

.input-container::before {
    position: absolute;
    top: -59px;
    left: 10px;
    color: #8E5727;
    font-size: 1.8em;
    font-family: 'Georgia', serif; /* Elegant serif font */
    z-index: 1; /* Ensures it stays on top of the input text */
}

.modal-body input[type="text"],
.modal-body input[type="email"],
.modal-body input[type="number"],
.modal-body input[type="file"] {
    width: 100%;
    padding: 15px;
    margin-bottom: 15px;
    border: 1px solid #C69B68;
    border-radius: 4px;
    font-size: 14px;
    background: linear-gradient(to right, #FFF5E1, #FEE7C3); /* Subtle gradient */
    color: #5C2E0A;
    border-left: 4px solid #8E5727; /* Decorative border */
    text-indent: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    font-family: 'Libre Baskerville', serif;
    position: relative;
}

.modal-body input[type="date"]{
    width: 100%;
    padding: 15px;
    margin-bottom: 15px;
    border: 1px solid #C69B68;
    border-radius: 4px;
    font-size: 14px;
    background: linear-gradient(to right, #FFF5E1, #FEE7C3); /* Subtle gradient */
    color: #5C2E0A;
    border-left: 4px solid #8E5727; /* Decorative border */
    text-indent: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    font-family: 'Libre Baskerville', serif;
    position: relative;
    font-style: italic;   
}

/* Placeholder Styling */
.modal-body input[type="text"]::placeholder,
.modal-body input[type="email"]::placeholder,
.modal-body input[type="number"]::placeholder,
.modal-body input[type="date"]::placeholder,
.modal-body input[type="file"]::placeholder {
    color: #8E5727; /* Soft brown color for the placeholder */
    font-size: 14px;
    font-style: italic;
    letter-spacing: 1px;
    opacity: 0.7; /* Slightly transparent for a subtle effect */
    transition: color 0.3s ease-in-out; /* Smooth transition on focus */
}

/* Change the placeholder color on focus */
.modal-body input[type="text"]:focus::placeholder,
.modal-body input[type="email"]:focus::placeholder,
.modal-body input[type="number"]:focus::placeholder,
.modal-body input[type="date"]:focus::placeholder,
.modal-body input[type="file"]:focus::placeholder
{
    
    color: #5C2E0A; /* Darker color when focused */
    opacity: 1; /* Full opacity on focus */
}

/* Optional: Add focus effect to input fields */
.modal-body input[type="text"]:focus,
.modal-body input[type="email"]:focus,
.modal-body input[type="number"]:focus,
.modal-body input[type="date"]:focus,
.modal-body input[type="file"]:focus {
    border-color: #8E5727; /* Highlight border on focus */
    outline: none; /* Remove default focus outline */
}

.modal-body input[type="text"]:focus,
.modal-body input[type="email"]:focus,
.modal-body input[type="number"]:focus,
.modal-body input[type="date"]:focus,
.modal-body input[type="file"]:focus {
    border-color: #8E5923; /* Deep brown */
    outline: none;
}

.modal-body input[type="file"]::before {
    content: "❧"; /* Decorative symbol */
    position: absolute;
    left: -20px; /* Adjust position */
    top: 40%;
    transform: translateY(-50%);
    font-size: 20px; /* Adjust the size of the symbol */
    color: #8E5727; /* Symbol color */
    pointer-events: none; /* Ensure the symbol doesn't interfere with text input */
}

/* Buttons */
.modal-body button[type="submit"],
.modal-body button[type="button"] {
    padding: 10px 15px;
    font-size: 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s, color 0.3s; /* Added color transition */
}

.modal-body button[type="submit"] {
    background-color: #EE7E1A; /* Bright orange */
    color: #FFF;
}

.modal-body button[type="submit"]:hover {
    background-color: #792A03; /* Deep red-brown */
}

.modal-body button[type="button"] {
    background-color: #C69B68; /* Medium beige */
    color: #5C2E0A;
}

.modal-body button[type="button"]:hover {
    background-color: #8E5923; /* Deep brown */
    color: #FFF; /* Ensure text is readable on hover */
}

/* Optional: Add focus styles for accessibility */
.modal-body button[type="submit"]:focus,
.modal-body button[type="button"]:focus {
    outline: none; /* Remove default outline */
    box-shadow: 0 0 0 3px rgba(238, 126, 26, 0.5); /* Subtle focus ring for submit */
}

.modal-body button[type="button"]:focus {
    box-shadow: 0 0 0 3px rgba(198, 155, 104, 0.5); /* Subtle focus ring for button */
}

.button-container {
    display: flex;
    justify-content: space-between; /* Space between the buttons */
    align-items: center; /* Center vertically */
    margin-bottom: 20px; /* Space below the buttons */
}

/* Vintage elegance button */
.add-btn {
    background-color: #8B4513;
    color: #FFF3DC;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    border-radius: 20px;
    transition: background-color 0.2s;
    font-size: 15px;
    font-weight: 500;
    margin-bottom: 20px;

}

.add-btn:hover {
    background-color: #4D2813; /* Deep red-brown */
    color: #FEDFB1;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); /* Enhanced shadow on hover */
}

.custom-file-upload {
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
    cursor: pointer;
}

.custom-file-upload input[type="file"] {
    display: none; /* Hide the default file input */
}

.custom-file-upload .file-label {
    background: linear-gradient(to right, #FFF5E1, #FEE7C3);
    padding: 10px 15px;
    border-radius: 4px;
    border: 1px solid #C69B68;
    color: #5C2E0A;
    font-weight: bold;
    text-transform: uppercase;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.custom-file-upload .file-label:hover {
    background: linear-gradient(to right, #FEE7C3, #E4C097);
}

.custom-file-upload .file-name {
    font-style: italic;
    color: #8E5727;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding: -20px;
}


.custom-file-upload {
    width: 100%;
    padding: 5px;
    margin-bottom: 15px;
    border: 1px solid #C69B68;
    border-radius: 4px;
    font-size: 14px;
    background: linear-gradient(to right, #FFF5E1, #FEE7C3); /* Subtle gradient */
    color: #5C2E0A;
    border-left: 4px solid #8E5727; /* Decorative border */
    text-indent: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    font-family: 'Libre Baskerville', serif;
    position: relative;
}

.custom-file-upload .decorative-symbol {
    margin-right: -10px;
    font-size: 22px;
    color: #8E5727;
    
}

.custom-file-upload input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0; /* Fully hidden but clickable */
    cursor: pointer;
    z-index: 10; /* Ensure input is above other elements */
}

.custom-file-upload .file-name {
    flex-grow: 1;
    font-style: italic;
    color: #8E5727;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding-left: 25px; /* To align with the decorative symbol */
    text-indent: -17px;
}

.textarea-container {
    position: relative; /* Set relative positioning for the container */
    display: inline-block; /* Adjust layout to fit the textarea */
    width: 55%; /* Allow responsive width */
    max-width: 400px; /* Adjust as needed */
}

.textarea-container .decorative-symbol {
    position: absolute; /* Position the symbol inside the container */
    top: 5px; /* Adjust vertical alignment */
    left: 15px; /* Adjust horizontal alignment */
    font-size: 22px; /* Symbol size */
    color: #8E5727; /* Decorative color */
    pointer-events: none; /* Prevent symbol from interfering with text input */
}

.textarea-container textarea {
    font-size: 16px;
    line-height: 1.6;
    background: linear-gradient(to right, #FFF5E1, #FEE7C3); /* Subtle gradient */
    padding: 15px 15px 15px 40px; /* Add left padding for the symbol */
    border-left: 4px solid #8E5727; /* Decorative border */
    text-indent: 0; /* Ensure text input starts correctly */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    border-radius: 8px;
    font-family: 'Libre Baskerville', serif;
    resize: vertical; /* Allow vertical resizing */
    width: 182%; /* Full width */
    min-height: 200px; /* Set minimum height */
    max-height: 200px; /* Set maximum height */
    text-transform: none;
    color: #5C2E0A; /* Decorative color */
}

.textarea-container textarea::placeholder {
    color: #8E5727; /* Soft brown color for the placeholder */
    font-size: 14px;
    font-style: italic;
    letter-spacing: 1px;
    opacity: 0.7; /* Slightly transparent for a subtle effect */
    transition: color 0.3s ease-in-out; /* Smooth transition on focus */
}

.textarea-container textarea:focus::placeholder {
    color: #5C2E0A; /* Darker color when focused */
    opacity: 1; /* Full opacity on focus */
}

.textarea-container textarea:focus {
    border-color: #8E5727; /* Highlight border on focus */
    outline: none; /* Remove default focus outline */
}


.icon-container {
    display: flex;
    justify-content: space-around;
    align-items: center;
    margin-top: 20px;
}

.option {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.option i {
    font-size: 48px;
    color: #5C2E0A;
    margin-bottom: 10px;
}

.option button {
    background-color: #5C2E0A;
    color: #FFF;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-size: 18px;
    font-family: 'Roboto', sans-serif;
    cursor: pointer;
    transition: transform 0.2s ease, background-color 0.3s ease;
}

.option button:hover {
    transform: scale(1.05);
    background-color: #4D2813;
}

.option p {
    margin-top: 10px;
    font-size: 14px;
    color: #555;
}


/* Form Group */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    font-size: 16px;
    color: #333;
    display: block;
    margin-bottom: 10px;
}

.form-group input[type="file"] {
    border: 2px dashed #C69B68;
    background: #F9F5F0;
    border-radius: 6px;
    width: 100%;
    padding: 20px;
    font-size: 14px;
    color: #5C2E0A;
    cursor: pointer;
    outline: none;
    transition: border-color 0.3s ease;
}

.form-group input[type="file"]:hover {
    border-color: #5C2E0A;
}

/* Buttons */

.btn-import {
    background-color: #5C2E0A;
    color: #FFF;
}

.btn-import:hover {
    background-color: #4D2813;
    transform: scale(1.05);
}

.btn-cancel {
    background-color: #C69B68;
    color: #5C2E0A;
}

.btn-cancel:hover {
    background-color: #8E5923;
    color: #FFF;
    transform: scale(1.05);
}

/* Fade-in Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

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


    </style>
</head>
<body>


<!-- SIDEBAR -->
<section id="sidebar">
		<a href="#" class="brand">
			<i class='bx bxs-smile'></i>
			<span class="text">CraftHive</span>
		</a>
		<ul class="side-menu top">
			<li class="active">
				<a href="dashboard.php">
					<i class='bx bxs-dashboard' ></i>
					<span class="text">Dashboard</span>
				</a>
			</li>
			<li>
				<a href="subscription.php">
					<i class='bx bxs-shopping-bag-alt' ></i>
					<span class="text">Subscription</span>
				</a>
			</li>
			<li>
				<a href="ch-checkout.php">
					<i class='bx bxs-doughnut-chart' ></i>
					<span class="text">CheckOut </span>
				</a>
			</li>
			<li>
				<a href="ch-view.php">
					<i class='bx bxs-group' ></i>
					<span class="text">View Purchase</span>
				</a>
			</li>
		</ul>
		<ul class="side-menu">
			<li>
				<a href="#">
					<i class='bx bxs-cog' ></i>
					<span class="text">Settings</span>
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
				<img src="img/CH-logo.png">
			</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Admin Dashboard</h1>
					<ul class="breadcrumb">
						<li>
							<a href="ch-company.php">Admin</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="dashboard.php">Dashboard</a>
						</li>
					</ul>
				</div>

<a href="export_csv.php" class="btn-download add-btn">Download CSV Report</a>


			</div>
<main>

<div class="container">
<section class="products">

<div class="content">
    <div class="cards">
    <div class="card">
            <div class="box">
            <h1><?php echo $total_company_rep; ?></h1>
                <h3>Company Reps</h3>
            </div>
            <div class="icon-case">
            <lord-icon
    src="https://cdn.lordicon.com/thqfiauh.json"
    trigger="hover"
     colors="primary:#121331,secondary:#ffc738,tertiary:#D3AB7A"
    style="width:50px;height:50px">
</lord-icon>
            </div>
        </div>
        <div class="card">
            <div class="box">
                <h1><?php echo $total_sellers; ?></h1>
                <h3>Sellers</h3>
            </div>
            <div class="icon-case">
            <lord-icon
    src="https://cdn.lordicon.com/piolrlvu.json"
    trigger="hover"
    colors="primary:#121331,secondary:#ffc738,tertiary:#D3AB7A,quaternary:#faddd1"
    style="width:50px;height:50px">
</lord-icon>
            </div>
        </div>
        <div class="card">
            <div class="box">
            <h1><?php echo $total_delivered; ?></h1>
                <h3>Deliverers</h3>
            </div>
            <div class="icon-case">
            <lord-icon
    src="https://cdn.lordicon.com/amfpjnmb.json"
    trigger="hover"
     colors="primary:#121331,secondary:#D3AB7A,tertiary:#3a3347, quaternary:#ffc738,quinary:#C18F5A"
    style="width:50px;height:50px">
</lord-icon>
            </div>
        </div>
        <div class="card">
            <div class="box">
            <h1>₱<?php echo $earnings; ?></h1>
                <h3>Earning</h3>
            </div>
            <div class="icon-case">
            <lord-icon
    src="https://cdn.lordicon.com/thqfiauh.json"
    trigger="hover"
     colors="primary:#121331,secondary:#ffc738,tertiary:#D3AB7A"
    style="width:50px;height:50px">
</lord-icon>
            </div>
        </div>
    </div>

    
    <div class="charts-container">
    <!-- Pie Chart for Subscription Status -->

    <!-- Bar Graph for Earnings -->
    <div class="chart-container2">
    <h3>Earning</h3>
    <div class="timeframe-buttons">
        <button class="timeframe-btn" onclick="setTimeframe('daily')">Daily</button>
        <button class="timeframe-btn" onclick="setTimeframe('weekly')">Weekly</button>
        <button class="timeframe-btn" onclick="setTimeframe('monthly')">Monthly</button>
        <button class="timeframe-btn" onclick="setTimeframe('yearly')">Yearly</button>
    </div>
    <canvas id="earningsChart"></canvas>
</div>
</div>

</div>

<section id="productTableContainer" class="recent-transactions">

<table>
        <thead>
            <tr>
            <th>Company Rep</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Sub Plan</th>
            <th>Sub Status</th>
            <th>Start & Expiry</th>
            <th>Total Sellers</th>
            <th class="product-action">Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($companyRepsResult->num_rows > 0): ?>
            <?php while ($row = $companyRepsResult->fetch_assoc()): ?>
                            <tr>
                            <td><?php echo $row['fname']; ?> from <?php echo $row['company_name']; ?></td>
                            <td>
    <?php
    $email = $row['email'];
    $local_part = substr($email, 0, strpos($email, '@')); // Get the local part
    $masked_local = substr($local_part, 0, 4) . str_repeat('*', strlen($local_part) - 3); // Mask the local part after the first 3 characters
    echo $masked_local;
    ?>
</td>

                            <td><?php echo substr($row['phone'], 0, 4) . ' *** ****'; ?></td>
                                <td><?php echo $row['subscription_plan']; ?></td>
                                <td><?php echo $row['status']; ?></td>
                                <td><?php echo date('M j, Y', strtotime($row['start_date'])); ?> and <?php echo date('M j, Y', strtotime($row['expiry_date'])); ?></td>

                                <td><?php echo $row['total_sellers']; ?></td>
                                <td class="product-action">
                                <a href="javascript:void(0);" class="option-btn" onclick="viewDetails('<?php echo $row['company_id']; ?>')">
                        <i class="fas fa-edit"></i> Details
                    </a>

                    <a href="javascript:void(0);" class="delete-btn" data-id="<?php echo $row['company_id']; ?>" onclick="disableAccount(this)"><i class="fas fa-trash"></i> Disable</a>


                          </td>

                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No company representatives found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
        </tbody>
    </table>
</section>
<div id="addRepModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Representative</h2>
            <span class="close" onclick="closeAddModal()">×</span>
        </div>
        <div class="modal-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="addRepresentative">
                
                <label for="rep_fname">First Name:</label>
                <input type="text" id="rep_fname" name="rep_fname" placeholder="Enter first name" required>
          
                <div class="input-container">
                <label for="rep_lname">Last Name:</label>
                <input type="text" id="rep_lname" name="rep_lname" placeholder="Enter last name" required>
                </div>

                <div class="input-container">
                <label for="rep_email">Email:</label>
                <input type="email" id="rep_email" name="rep_email" placeholder="Enter email address" required>
                 </div>


                <div class="input-container">
                <label for="rep_phone">Phone:</label>
                <input type="text" id="rep_phone" name="rep_phone" placeholder="Enter phone number" required>
                </div>

                <div class="input-container">
                <label for="rep_company_name">Company Name:</label>
                <input type="text" id="rep_company_name" name="rep_company_name" placeholder="Enter company name" required>
                </div>

                <div class="input-container">
                <label for="rep_subscription_plan">Subscription Plan:</label>
                <input type="text" id="rep_subscription_plan" name="rep_subscription_plan" placeholder="Enter subscription plan" required>
                </div>

                <div class="input-container">
                <label for="rep_price">Price:</label>
                <input type="number" id="rep_price" name="rep_price" placeholder="Enter price" required>
                </div>

                <div class="input-container">
                <label for="rep_start_date">Start Date:</label>
                <input type="date" id="rep_start_date" name="rep_start_date" required>
                </div>

                <div class="input-container">
                <label for="rep_expiry_date">Expiry Date:</label>
                <input type="date" id="rep_expiry_date" name="rep_expiry_date" required>
                </div>

<div class="input-container">
    <label for="rep_profile_img">Profile Image:</label>
    <div class="custom-file-upload">
        <span class="file-name" id="file-name" class="upload-button">Enter Image</span>
        <input type="file" id="rep_profile_img" name="rep_profile_img" accept="image/*" hidden>
    </div>
</div>

                <button type="submit">Save</button>
                <button type="button" onclick="closeAddModal()">Cancel</button>
            </form>
        </div>
    </div>
</div>




<div id="addProductModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h1>View Details</h1>
            <span class="close" onclick="document.getElementById('addProductModal').style.display='none'">×</span>
        </div>
        <div class="modal-body">
            <?php if (!empty($companyReps)) : ?>
                <?php foreach ($companyReps as $repId => $rep) : ?>
                    <!-- Add data-repid here -->
                    <div class="company-rep" data-repid="<?php echo htmlspecialchars($repId); ?>">
                        <div class="profile-container">
                            <!-- Profile Image -->
                            <div class="profile-img">
    <img src="../../uploaded_profile/<?php echo !empty($rep['rep_img']) ? htmlspecialchars($rep['rep_img']) : 'default.png'; ?>" alt="Profile Image">
</div>

                            <!-- Profile Info -->
                            <div class="profile-info">
                                <h2><?php echo htmlspecialchars($rep['rep_fname'] . ' ' . $rep['rep_lname']); ?></h2>
                                <h3><?php echo htmlspecialchars($rep['rep_company_name']); ?></h3>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($rep['rep_email']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($rep['rep_phone']); ?></p>
                                <p><strong>Subscription:</strong> <?php echo htmlspecialchars($rep['rep_subscription_plan']); ?></p>
                                <p><strong>Price:</strong> ₱<?php echo htmlspecialchars($rep['rep_price']); ?></p>
                                <p><strong>Total Sellers:</strong> <?php echo htmlspecialchars($rep['rep_total_sellers']); ?></p>
                                <p><strong>Start Date:</strong> <?php echo htmlspecialchars($rep['rep_start_date']); ?></p>
                                <p><strong>Expiry Date:</strong> <?php echo htmlspecialchars($rep['rep_expiry_date']); ?></p>
                            </div>
                        </div>
<h4>Sellers</h4>
<div class="recent-transactions">
    <?php if (!empty($rep['sellers'])) : ?>
        <table class="sellers-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Total Products</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rep['sellers'] as $seller) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($seller['seller_fname'] . ' ' . $seller['seller_lname']); ?></td>
                        <td><?php echo htmlspecialchars($seller['seller_email']); ?></td>
                        <td><?php echo htmlspecialchars($seller['seller_phone']); ?></td>
                        <td><?php echo htmlspecialchars($seller['seller_status']); ?></td>
                        <td><?php echo htmlspecialchars($seller['seller_total_products']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <div class="empty">No sellers associated with this representative.</div>
    <?php endif; ?>
</div>

<h4>Deliveries</h4>
<div class="recent-transactions">
    <?php if (!empty($rep['deliveries'])) : ?>
        <table class="sellers-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Total Delivered</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rep['deliveries'] as $delivery) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($delivery['delivery_fname'] . ' ' . $delivery['delivery_lname']); ?></td>
                        <td><?php echo htmlspecialchars($delivery['delivery_email']); ?></td>
                        <td><?php echo htmlspecialchars($delivery['delivery_phone']); ?></td>
                        <td><?php echo htmlspecialchars($delivery['delivery_status']); ?></td>
                        <td><?php echo htmlspecialchars($delivery['delivery_total_delivered']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <div class="empty">No deliveries associated with this representative.</div>
    <?php endif; ?>
</div>
                    </div>
                    <hr>
                <?php endforeach; ?>
            <?php else : ?>
                <p>No active company representatives found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="inactivateModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Disable Account</h2>
            <span class="close" onclick="closeInactivateModal()">×</span>
        </div>
        <div class="modal-body">
            <form method="POST" action="dashboard.php">
                <input type="hidden" id="company_id" name="company_id">
                <div class="textarea-container">
    <textarea id="reason" name="reason" placeholder="Enter the reason" required></textarea>
                <button type="submit">Confirm</button>
                <button type="button" onclick="closeInactivateModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
  </main>
 
  <script src="js/script.js"></script>
  <script>

    const earningsData = <?php echo json_encode($earningsData); ?>;

    // Format data for the earnings bar chart
    const earningsLabels = Object.keys(earningsData);
    const earningsValues = Object.values(earningsData);

    // Bar chart for earnings
    const earningsChartData = {
        labels: earningsLabels,
        datasets: [{
            label: 'Earnings (in Subscription)',
            data: earningsValues,
            backgroundColor: '#EE7E1A',
            borderColor: '#8E5923',
            borderWidth: 2,
            borderRadius: 6,
        }]
    };

    const earningsChartConfig = {
        type: 'bar',
        data: earningsChartData,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#8E5923',
                    },
                    grid: {
                        color: '#FEDFB1',
                    }
                },
                x: {
                    ticks: {
                        color: '#8E5923',
                    },
                    grid: {
                        color: '#FEDFB1',
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#8E5923',
                        font: {
                            family: 'Georgia',
                        }
                    }
                }
            }
        }
    };

    const earningsChart = new Chart(
        document.getElementById('earningsChart'),
        earningsChartConfig
    );

    function setTimeframe(timeframe) {
        const url = new URL(window.location.href);
        url.searchParams.set('timeframe', timeframe);
        window.location.href = url.toString(); // Refresh the page with the new timeframe
    }


// Get modal and close elements
var addRepModal = document.getElementById('addRepModal');
var addProductModal = document.getElementById('addProductModal');
var inactivateModal = document.getElementById('inactivateModal'); // Add this line
var closeModal = document.querySelector('.close');
var productTableContainer = document.getElementById('productTableContainer');
var body = document.querySelector('body');

// Event listener for buttons with class 'stat'
document.querySelectorAll('.stat').forEach(function(btn) {
    btn.addEventListener('click', function(event) {
        event.preventDefault();

        // Close any open modals
        addRepModal.style.display = 'none';
        addProductModal.style.display = 'none';
        inactivateModal.style.display = 'none'; // Close inactivate modal if open

        // Toggle visibility of the table container
        if (btn.id === 'contactRequest') {
            productTableContainer.style.display = 'block';
        } else if (btn.id === 'addProduct') {
            productTableContainer.style.display = 'none';
        }

        // Disable scrolling when modal is open
        body.classList.add('modal-open');
        body.style.overflow = 'hidden';
    });
});

// Function to close Add Representative Modal
function closeAddModal() {
    addRepModal.style.display = 'none';
    body.classList.remove('modal-open');
    body.style.overflow = 'auto'; // Enable scrolling
}

// Event listener for the close button inside Add Representative Modal
document.querySelector('.modal-header .close').addEventListener('click', closeAddModal);

// Function to open Add Representative Modal
function openAddModal() {
    addRepModal.style.display = 'block';
    body.classList.add('modal-open');
    body.style.overflow = 'hidden'; // Disable scrolling
}
function disableAccount(element) {
    const companyId = element.getAttribute('data-id');
    if (confirm("Are you sure you want to disable this account?")) {
        // Make an AJAX request to update the status
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "inactive.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if (xhr.status === 200) {
                if (xhr.responseText === "success") {
                    alert("Account has been successfully disabled.");
                    // Optionally refresh the page or update the UI
                    location.reload();
                } else {
                    alert("An error occurred. Please try again.");
                }
            }
        };
        xhr.send("company_id=" + companyId);
    }
}

// Close the modal if clicked outside the modal content
window.onclick = function(event) {
    if (event.target == addRepModal) {
        closeAddModal();
    } else if (event.target == addProductModal) {
        addProductModal.style.display = 'none';
        body.classList.remove('modal-open');
        body.style.overflow = 'auto'; // Enable scrolling
    } else if (event.target == inactivateModal) { // Check for inactivate modal
        closeInactivateModal();
    }
};

// View details function (optional for product-specific actions)
function viewDetails(repId) {
    addProductModal.style.display = 'block';

    // Show only the matching representative
    document.querySelectorAll('.company-rep').forEach(rep => {
        if (rep.getAttribute('data-repid') === repId) {
            rep.style.display = 'block';
        } else {
            rep.style.display = 'none';
        }
    });
}

const fileInput = document.getElementById('rep_profile_img');
const fileNameDisplay = document.getElementById('file-name');

fileNameDisplay.addEventListener('click', function() {
    fileInput.click(); // Trigger the file input when the display is clicked
});

fileInput.addEventListener('change', function() {
    const files = fileInput.files;
    if (files.length > 0) {
        const fileName = files[0].name; // Get the name of the first selected file
        fileNameDisplay.textContent = fileName; // Update the display with the file name
    } else {
        fileNameDisplay.textContent = 'Enter Image'; // Reset if no file is selected
    }
});


</script>

</body>
</html>