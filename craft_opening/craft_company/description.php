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

if (isset($_POST['add_description'])) {
    $c_location = mysqli_real_escape_string($conn, $_POST['c_location']);
    $c_payment = mysqli_real_escape_string($conn, $_POST['c_payment']);
    $c_policy = mysqli_real_escape_string($conn, $_POST['c_policy']);
    $c_image = $_FILES['c_image']['name'];
    $c_image_tmp_name = $_FILES['c_image']['tmp_name'];
    $c_image_folder = '../../uploaded_c_img/' . $c_image;
    $c_gimage = $_FILES['c_gimage']['name'];
    $c_gimage_tmp_name = $_FILES['c_gimage']['tmp_name'];
    $c_gimage_folder = '../../uploaded_c_gimg/' . $c_gimage;

    // Check if the description already exists for the given company
    $check_description_query = $conn->prepare("SELECT * FROM description WHERE company_unique_id = ? AND company_rep_id = ?");
    $check_description_query->bind_param("ss", $company_unique_id, $company_rep_id);
    $check_description_query->execute();
    $existing_description = $check_description_query->get_result();

    if ($existing_description->num_rows > 0) {
        // Notify the user that a description already exists
        $message[] = 'You have already added a description. Please update the existing description instead.';
    } else {
        // Fetch company details for insertion
        $fetch_company_query = $conn->prepare("SELECT company_name, phone FROM company WHERE unique_id = ?");
        $fetch_company_query->bind_param("s", $company_unique_id);
        $fetch_company_query->execute();
        $result = $fetch_company_query->get_result();

        if ($result->num_rows > 0) {
            $company_data = $result->fetch_assoc();
            $c_company_name = $company_data['company_name'];
            $c_phone = $company_data['phone'];

            // Insert the new description
            $insert_query = $conn->prepare("INSERT INTO description (company_unique_id, company_name, location, phone, payment, policy, image, gcash_qrcode, company_rep_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_query->bind_param("sssssssss", $company_unique_id, $c_company_name, $c_location, $c_phone, $c_payment, $c_policy, $c_image, $c_gimage, $company_rep_id);

            if ($insert_query->execute()) {
                if (move_uploaded_file($c_image_tmp_name, $c_image_folder) && move_uploaded_file($c_gimage_tmp_name, $c_gimage_folder)) {
                    $message[] = 'Company description added successfully';
                } else {
                    $message[] = 'Failed to upload company images';
                }
            } else {
                $message[] = 'Could not add the company description';
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete']);
    $delete_query = $conn->prepare("DELETE FROM description WHERE company_unique_id = ? AND company_rep_id = ?");
    $delete_query->bind_param("ss", $delete_id, $company_rep_id);

    if ($delete_query->execute()) {
        $message[] = 'Company description has been deleted';
    } else {
        $message[] = 'Company description could not be deleted';
    }

    header('Location: description.php');
    exit;
}

?>

<!-- Update Display Query -->
<?php
$select_description = $conn->prepare("
    SELECT d.*, c.company_name, c.phone 
    FROM description d
    JOIN company c ON d.company_unique_id = c.unique_id
    WHERE d.company_unique_id = ? AND d.company_rep_id = ?
");
$select_description->bind_param("ss", $company_unique_id, $company_rep_id);
$select_description->execute();
$result = $select_description->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title> Company's Profile</title>


  <!-- Font Awesome CDN link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <!-- Custom CSS file link -->
   <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

   <link rel="stylesheet" href="../../css/design.css">

   <style>
	@import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Poppins:wght@400;500;600;700&display=swap');

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

.option-btn{
    margin-bottom: 10px;

}

:root {
    --primary-color: #5D4037; /* Vintage dark brown */
    --primary-gradient: linear-gradient(135deg, #5D4037, #4A3229);
    --hover-gradient: linear-gradient(135deg, #6F5148, #4A3229);
    --input-bg: #FFFFFF; /* Clean white background for inputs */
    --input-border: #D3C4B1; /* Subtle beige for borders */
    --input-focus-border: #5D4037; /* Dark brown for focus */
    --font-color: #5D4037; /* Matching text color */
    --placeholder-color: #A69485; /* Subtle vintage placeholder */
    --background-color: #FDFBF8; /* Soft off-white for form background */
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

        .container h3 {
            font-size: 1.8em;
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 20px;
            text-transform: uppercase;
            border-bottom: 2px solid #8E5727;
            padding-bottom: 10px;
        }

        label {
            font-size: 1em;
            color: var(--font-color);
            display: block;
            margin-bottom: 5px;
        }

        .box {
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

        .box::placeholder {
            color: var(--placeholder-color);
            font-style: italic;
        }

        .box:focus {
            border-color: #D19A6B;
            outline: none;
            box-shadow: 0 0 8px rgba(93, 64, 55, 0.4);
        }

        .custom-file-input {
            position: relative;
            margin-bottom: 20px;
        }

        .custom-file-input label {
            display: block;
            background-color: #FFF5E1;
            border: 1px solid #8E5727;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 16px;
            color: #3E2723;
            text-align: left;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-style: italic;
        }

        .custom-file-input label:hover {
            background-color: #F7E0C0;
        }

        .custom-file-input input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            height: 100%;
            width: 100%;
            cursor: pointer;
        }

        .btn {
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

        .btn:hover {
            background: #7A3B0E;
            transform: scale(1.02);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

.message{
   position: sticky;
   top:0; left:0;
   z-index: 10000;
   border-radius: .5rem;
   background: #FFF4DE; /* Soft parchment gradient */
   padding:1.5rem 2rem;
   margin:0 auto;
   max-width: 1200px;
   display: flex;
   align-items: center;
   justify-content: space-between;
   gap:1.5rem;
   border: 1px solid #D2B48C; /* Light brown border for elegance */
    transition: opacity 0.3s ease; /* Smooth transition for hiding */
}

.message span{
   font-size: 2rem;
   color:var(--black);
}

.message i{
   font-size: 2.5rem;
   color:var(--black);
   cursor: pointer;
}

.message i:hover{
   color:var(--red);
}

#content main .head-title .left .breadcrumb li a {
	color: #5C2E0A;
	pointer-events: none;
    font-weight: 500;
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
   foreach ($message as $message) {
      echo '<div class="message"><span>'.$message.'</span> 
      <i class="fas fa-times" onclick="this.parentElement.style.display = `none`;"></i></div>';
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
			<li>
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

<!-- HTML to display the image -->
<a href="#" class="profile">
    <img src="../uploaded_profile/CH-logo.png" alt="Profile Image">
</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Company Profile</h1>
					<ul class="breadcrumb">
						<li>
							<a href="ch-company.php">Company</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="ch-company.php">C Profile</a>
						</li>
					</ul>
				</div>
			</div>

<section>

<div class="container">
        <form action="" method="post" class="add-product-form" enctype="multipart/form-data">
            <h3>Add Company Profile</h3>
            <input type="text" name="c_location" placeholder="Enter the company location" class="box" required>
            <input type="text" name="c_payment" placeholder="Enter the company payment" class="box" required>
            <input type="text" name="c_policy" placeholder="Enter the company policy" class="box" required>
            <div class="custom-file-input">
    <input type="file" id="c_image" name="c_image" accept="image/png, image/jpg, image/jpeg" class="box" required>
    <label id="label_c_image" for="c_image">Upload your company image</label>
</div>
<div class="custom-file-input">
    <input type="file" id="c_gimage" name="c_gimage" accept="image/png, image/jpg, image/jpeg" class="box" required>
    <label id="label_c_gimage" for="c_gimage">Upload your GCash QR code</label>
</div>
            <input type="submit" value="Add the company description" name="add_description" class="btn">
        </form>
</section>
</div>
<section class="recent-transactions">
   <table>
      <thead>
         <th>Company Image</th>
         <th>Company Name</th>
         <th>Company Location</th>
         <th>Company Contact</th>
         <th>Company Payment</th>
         <th>Company Policy</th>
         <th>Action</th>
      </thead>

      <tbody>
      <?php
// Fetch descriptions with company details using a JOIN
$select_description = mysqli_query($conn, "
    SELECT d.*, c.company_name, c.phone 
    FROM description d
    JOIN company c ON d.company_unique_id = c.unique_id
    WHERE d.company_unique_id = '$company_unique_id' AND d.company_rep_id = '$company_rep_id'
");

if (mysqli_num_rows($select_description) > 0) {
    while ($row = mysqli_fetch_assoc($select_description)) {
?>

         <tr>
            <td><img src="../../uploaded_c_img/<?php echo $row['image']; ?>" height="100" alt=""></td>
            <td><?php echo $row['company_name']; ?></td>
            <td><?php echo $row['location']; ?></td>
            <td> <?php $phone = $row['phone']; $masked_phone = preg_replace('/\d(?=\d{4})/', '*', $phone); echo htmlspecialchars($masked_phone); ?></td>
            <td><?php echo $row['payment']; ?></td>
            <td><?php echo $row['policy']; ?></td>
            <td>
            <a href="edit_description.php?edit=<?php echo $row['company_unique_id']; ?>" class="option-btn">
        <i class="fas fa-edit"></i> Update
    </a>
               <a href="description.php?delete=<?php echo $row['company_unique_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this?');"> <i class="fas fa-trash"></i> Erasure </a>
            </td>
         </tr>

         <?php
            };    
            }else{
               echo "<div class='empty'>no company description added</div>";
            };
         ?>
      </tbody>
   </table>

</section>

</div>

<script src="js/script.js"></script>

<script>
    // Function to update the label text when a file is selected
    document.getElementById('c_image').addEventListener('change', function () {
    const label = document.getElementById('label_c_image');
    const fileName = this.files[0]?.name || 'Upload your company image'; // Fallback if no file is selected
    label.textContent = fileName;
});

document.getElementById('c_gimage').addEventListener('change', function () {
    const label = document.getElementById('label_c_gimage');
    const fileName = this.files[0]?.name || 'Upload your GCash QR code'; // Fallback if no file is selected
    label.textContent = fileName;
});
</script>

</body>
</html>