<?php
session_start();
include_once '../connection/connect.php';

// Check if the seller is logged in
if (!isset($_SESSION['unique_id']) || !isset($_SESSION['company_rep_id']) || !isset($_SESSION['company_email'])) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit;
}

// Get session variables
$rep_unique_id = $_SESSION['unique_id'];

// Query to fetch company details
$companyQuery = "SELECT company_id, company_name, subscription_plan, price, start_date, expiry_date, payment_status, gcash_reference_number, status, company_rep_id, img FROM company WHERE unique_id = ?";
$companyStmt = $conn->prepare($companyQuery);
$companyStmt->bind_param("s", $rep_unique_id);
$companyStmt->execute();
$companyResult = $companyStmt->get_result();

if ($companyResult->num_rows > 0) {
    $company = $companyResult->fetch_assoc();
    $company_rep_id = $company['company_rep_id'];
    $company_id = $company['company_id'];
    $img = $company['img'];

    // Set default values to avoid errors if null
    $subscription_plan = $company['subscription_plan'] ?? 'N/A';
    $price = $company['price'] ?? 0;
    $start_date = $company['start_date'] ?? 'N/A';
    $expiry_date = $company['expiry_date'] ?? 'N/A';
    $payment_status = $company['payment_status'] ?? 'N/A';
    $gcash_reference_number = $company['gcash_reference_number'] ?? 'N/A';
       $isExpired = (strtotime($expiry_date) < time());
         $isFree = ($subscription_plan === 'Free');
} else {
    die("Error: Company data not found.");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

     if (isset($_POST['payment_status']) && ($isExpired || $isFree)) {
        $payment_status_to_update = $_POST['payment_status'];
        $updateQuery = "UPDATE company SET payment_status = ? WHERE company_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("si", $payment_status_to_update, $company_id);
         if ($updateStmt->execute()) {
            echo "success";
            exit;
            } else {
                 echo "fail";
                 exit;
              }

    }

    if (isset($_POST['subscription_plan']) && isset($_POST['price']) && ($isExpired || $isFree)) {
         $subscription_plan_to_update = $_POST['subscription_plan'];
         $price_to_update = $_POST['price'];
          $start_date_to_update = date('Y-m-d');
          $expiry_date_to_update = date('Y-m-d', strtotime('+1 month'));
        $updateQuery = "UPDATE company SET subscription_plan = ?, price=?, start_date = ?, expiry_date=? WHERE company_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ssdsi", $subscription_plan_to_update, $price_to_update,$start_date_to_update,$expiry_date_to_update, $company_id);
        if ($updateStmt->execute()) {
             echo "success";
             exit;
         } else {
               echo "fail";
               exit;
        }
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Subscription</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/design.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
     <link rel="stylesheet" href="rep-dashboards.css">
    <script src="https://cdn.lordicon.com/lordicon.js"></script>
    <style>
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


        .subscription-container {
           width: 80%;
            margin: 20px auto;
            padding: 20px;
           background-color: white;
            border-radius: 10px;
             box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
           .form-container{
            display:flex;
            flex-wrap:wrap;
               justify-content: space-around;
        }
        .form-container  .input-form {
          margin-bottom:10px;
           display: flex;
          align-items: center;

        }

        .form-container  .input-form input, .form-container  .input-form select {
            padding: 10px;
             border: 1px solid #ddd;
            border-radius: 5px;
           margin-left:10px;
             width: 250px;
        }
         .form-container  .input-form label {
            width: 150px;
             text-align: right;
            display: inline-block;
           margin-right:10px;
        }
         .form-container  button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
             border-radius: 5px;
            cursor:pointer;
           margin-top: 10px;
             margin-left:10px;
        }
        .form-container button:hover {
            background-color: #0056b3;
        }
         .form-container button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
         }
        .status-box {
          padding: 15px;
          border-radius: 5px;
          width: 20%;
          text-align:center;
          margin: 20px auto;
          color:white;
          font-weight:bold;
        }
       .status-box.Paid {
          background-color: #28a745;
      }
    .status-box.Pending {
        background-color: #ffc107;
    }
    .status-box.Free{
         background-color: #17a2b8;
    }
    .status-box.Expired{
      background-color:#dc3545;
    }
        .status-box .lord-icon {
          display:block;
           margin:0 auto;
        }
       .subscription-details h2{
         margin-bottom:20px;
         text-align:center;
       }
     .form-container  .input-form p{
       margin-bottom: 0;
        }
     .text-container{
         margin-bottom: 10px;
     }
       .profile-container {
           display:flex;
           justify-content: space-between;
           flex-wrap: wrap;
       }
        .profile-container .subscription-info {
           flex:1;
           margin: 0 20px 10px 0;
       }
          .profile-container .subscription-form {
             flex:1;
            margin: 0 20px 10px 0;
       }
    </style>
</head>
<body>

<section id="sidebar">
		<a href="#" class="brand">
			<i class='bx bxs-smile'></i>
			<span class="text">CraftHive</span>
		</a>
		<ul class="side-menu top">
			<li>
				<a href="dashboard.php">
					<i class='bx bxs-dashboard' ></i>
					<span class="text">Company Dashboard</span>
				</a>
			</li>
			<li class="active">
				<a href="description.php">
					<i class='bx bxs-shopping-bag-alt' ></i>
					<span class="text">Company Profile</span>
				</a>
			</li>
			<li>
				<a href="colleague.php">
					<i class='bx bxs-doughnut-chart' ></i>
					<span class="text">Company Colleague </span>
				</a>
			</li>
		</ul>
		<ul class="side-menu">
			<li class="active">
            <a href="subscription.php">
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
            <?php
    // Query to fetch the image for the logged-in company's representative
    $query = "SELECT img FROM company WHERE company_rep_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $company_rep_id); // Ensure $company_rep_id is set with the session value
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the record exists
    if ($row = $result->fetch_assoc()) {
        $img = $row['img'];
    } else {
        $img = 'CH-logo.png'; // Default image
    }
?>
<!-- HTML to display the image -->
<a href="#" class="profile">
    <img src="../uploaded_profile/<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Image">
</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Company Subscription Management</h1>
					<ul class="breadcrumb">
						<li>
							<a href="#">Company</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="subscription.php">Subscription</a>
						</li>
					</ul>
				</div>

	</div>

    <div class="subscription-container">
        <div class="subscription-details">
            <h2>Subscription Details</h2>
           <?php
            $status = $payment_status;
            if($status === "Paid"){
                 echo  "<div class='status-box Paid'><lord-icon
                 src='https://cdn.lordicon.com/pithtmnx.json'
                    trigger='hover'
                  colors='primary:#fff'
                 style='width:50px;height:50px'>
                </lord-icon> Paid </div>";

            } else if($status === "Pending"){
                 echo  "<div class='status-box Pending'><lord-icon
    src='https://cdn.lordicon.com/nocovwne.json'
    trigger='hover'
    colors='primary:#fff'
    style='width:50px;height:50px'>
</lord-icon> Pending </div>";
            } else if($status === "Free"){
                 echo  "<div class='status-box Free'><lord-icon
            src='https://cdn.lordicon.com/zpxyvjxq.json'
    trigger='hover'
    colors='primary:#fff'
    style='width:50px;height:50px'>
</lord-icon>Free</div>";

            } else if ($isExpired) {
              echo  "<div class='status-box Expired'><lord-icon
    src='https://cdn.lordicon.com/gsqxdxog.json'
    trigger='hover'
     colors='primary:#fff'
    style='width:50px;height:50px'>
</lord-icon> Expired </div>";
          } else{
              echo  "<div class='status-box'>".$payment_status."</div>";
          }
        ?>
        </div>
         <div class="profile-container">
         <div class="subscription-info">
                <div class="text-container">
                 <p><b>Company Name:</b> <?php echo htmlspecialchars($company['company_name']); ?></p>
                   <p><b>Company Status:</b> <?php echo htmlspecialchars($company['status']); ?></p>
                  <p><b>Start Date:</b> <?php echo htmlspecialchars($start_date); ?></p>
                    <p><b>Expiry Date:</b> <?php echo htmlspecialchars($expiry_date); ?></p>
                     <p><b>Gcash Reference Number:</b> <?php echo htmlspecialchars($gcash_reference_number); ?></p>
                </div>
         </div>
            <div class="subscription-form">
                  <form method="post" class="form-container" action="subscription.php">
                      <div class="input-form">
                          <label for="subscription_plan">Subscription Plan:</label>
                         <select id="subscription_plan" name="subscription_plan"  <?php if(!($isExpired || $isFree)) echo "disabled";?>>
                            <option value="Affordable Plan" <?php if($subscription_plan == "Affordable Plan") echo "selected";?>>Affordable Plan</option>
                            <option value="Basic Plan" <?php if($subscription_plan == "Basic Plan") echo "selected";?>>Basic Plan</option>
                            <option value="Free" <?php if($subscription_plan == "Free") echo "selected";?>>Free</option>
                        </select>
                     </div>
                      <div class="input-form">
                            <label for="price">Price:</label>
                         <input type="number" value="<?php echo htmlspecialchars($price); ?>" name="price" <?php if(!($isExpired || $isFree)) echo "disabled";?>>
                      </div>
                     <button type="submit" name="action" value="update_subscription" <?php if(!($isExpired || $isFree)) echo "disabled";?>>Update Subscription</button>
                </form>
                <form method="post" class="form-container" action="subscription.php">
                     <div class="input-form">
                           <label for="payment_status">Payment Status:</label>
                            <select id="payment_status" name="payment_status"  <?php if(!($isExpired || $isFree)) echo "disabled";?>>
                             <option value="Paid" <?php if($payment_status == "Paid") echo "selected";?>>Paid</option>
                              <option value="Pending" <?php if($payment_status == "Pending") echo "selected";?>>Pending</option>
                             <option value="Free" <?php if($payment_status == "Free") echo "selected";?>>Free</option>
                           </select>
                      </div>
                      <button type="submit" name="action" value="update_payment_status" <?php if(!($isExpired || $isFree)) echo "disabled";?>>Update Payment</button>
                 </form>
            </div>
         </div>
    </div>
  </main>

  <script src="js/script.js"></script>

</body>
</html>