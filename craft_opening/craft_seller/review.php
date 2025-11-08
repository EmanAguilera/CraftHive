<?php
session_start();
@include '../connection/connect.php';

// Check if the seller is logged in
if (!isset($_SESSION['unique_id']) || !isset($_SESSION['company_rep_id'])) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit;
}

// Get session variables
$seller_id = $_SESSION['unique_id'];
$seller_company_rep_id = $_SESSION['company_rep_id'];

// Function to fetch order data (Modified to include seller share breakdown)
function fetchOrderData($conn, $seller_id, $timeframe = 'all') {
    $where_clause = "WHERE seller_id = '$seller_id'";

    if ($timeframe === 'yearly') {
        $where_clause .= " AND YEAR(created_at) = YEAR(CURDATE())";
    } elseif ($timeframe === 'monthly') {
         $where_clause .= " AND created_at >= DATE(NOW()) - INTERVAL 30 DAY";
    } elseif ($timeframe === 'daily') {
         $where_clause .= " AND created_at >= CURDATE() - INTERVAL (DAYOFWEEK(CURDATE())-1) DAY AND created_at < CURDATE() + INTERVAL (7-DAYOFWEEK(CURDATE())) DAY ";
    }

    $product_query = "SELECT product_name, COUNT(*) AS count FROM `order` {$where_clause} GROUP BY product_name ORDER BY COUNT(*) DESC LIMIT 5";
    $product_result = mysqli_query($conn, $product_query);
    $product_labels = [];
    $product_counts = [];
    while ($row = mysqli_fetch_assoc($product_result)) {
        $product_labels[] = $row['product_name'];
        $product_counts[] = $row['count'];
    }


    return [
        'product_labels' => $product_labels,
        'product_counts' => $product_counts,
    ];
}

// Fetch all product reviews for the seller
$review_query = "SELECT
                    p.product_name, p.product_unique_id,
                    AVG(r.rating) AS average_rating,
                    COUNT(r.review_id) AS review_count
                FROM
                    `product` p
                LEFT JOIN
                    `reviews` r ON p.product_id = r.product_id
                WHERE
                    p.seller_id = '$seller_id'
                GROUP BY
                    p.product_name
                ORDER BY
                    p.product_name ASC";

$review_result = mysqli_query($conn, $review_query);
$product_reviews = [];

if($review_result && mysqli_num_rows($review_result) > 0){
   while($row = mysqli_fetch_assoc($review_result)){
    $product_reviews[] = $row;
  }
}
// Initial data load (all time)
$allData = fetchOrderData($conn, $seller_id);
$product_labels = $allData['product_labels'];
$product_counts = $allData['product_counts'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <!-- Custom CSS file link -->
   <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

   <link rel="stylesheet" href="../../css/design.css">
   <link rel="stylesheet" href="additional.css">
   <style>
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
    width: 85%; /* Make the width responsive */
    height: 450px; /* Set a base height for the chart container */
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
    .modal {
        display: none;
         position: fixed;
        z-index: 9999;
         left: 0;
        top: 0;
        width: 100%;
        height: 100%;
         overflow: auto; /* Enable scroll if modal content exceeds view */
        background-color: rgb(0,0,0); /* Fallback color */
         background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
      }
  .modal-content {
        background-color: #fefefe;
        margin: 10% auto; /* Center the modal vertically and add top margin*/
         padding: 20px;
         border: 1px solid #888;
        width: 80%; /* Responsive width of the modal */
        max-width: 600px; /* Maximum width of the modal */
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  }
     .modal-review-table {
        width: 100%; /* Make the table responsive */
        border-collapse: collapse;
        margin: 20px 0; /* Spacing above and below table */
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* Subtle shadow for definition */
        background-color: #FFF; /* White background to match overall theme */
    }

    .modal-review-table th,
    .modal-review-table td {
        border: 1px solid #ddd; /* Light grey borders for each cell */
        padding: 8px; /* Space around cell content */
        text-align: left; /* Align content to the left */
        font-size: 14px;
         color: #5C2E0A; /* Dark text color for readability */
    }

    .modal-review-table th {
        background-color: #f9f9f9; /* Slight grey background for header cells */
        font-weight: bold;
    }
        .close-btn {
         color: #aaa;
          float: right;
        font-size: 28px;
          font-weight: bold;
           cursor: pointer;
   }
.close-btn:hover, .close-btn:focus {
    color: #000;
     text-decoration: none;
    cursor: pointer;
}


/* General shared styles */
.delete-btn, .option-btn {
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
.delete-btn {
    background: linear-gradient(135deg, #8E3A15, #5C1E09); /* Dark reddish-brown gradient */
    border: 2px solid #4A1608; /* Deep red-brown border */
}

/* Specific styling for Option Button */
.option-btn {
    background: linear-gradient(135deg, #C69B68, #A16D37); /* Warm, lighter brown gradient */
    border: 2px solid #774816; /* Medium brown border */
}

/* Icon styling inside buttons */
.delete-btn i, a.option-btn i {
    margin-right: 8px; /* Space between icon and text */
    font-size: 16px; /* Icon size */
}

/* Hover effects for Delete Button */
.delete-btn:hover {
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
.delete-btn:active {
    background: #5C1E09; /* Dark solid brown for active state */
    transform: scale(0.98); /* Slightly shrink on click */
    box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.3); /* Inset shadow for click effect */
}

/* Active state for Option Button */
.option-btn:active {
    background: #7A4F22; /* Medium brown solid for active state */
    transform: scale(0.98); /* Slightly shrink on click */
    box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.3); /* Inset shadow for click effect */
}

/* Focus state for both buttons */
.delete-btn:focus, a.option-btn:focus {
    outline: none; /* Remove default outline */
    box-shadow: 0 0 8px rgba(182, 125, 91, 0.8); /* Soft brown glow effect */
}

.option-btn{
    margin-bottom: 10px;

}

.close-btn {
    color: #3E2723;
    font-size: 2.5em;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.3s ease, color 0.3s ease;
}

 .close-btn:hover {
    color: #EE7E1A; /* Muted orange for a warm touch */
    transform: rotate(90deg); /* Elegant hover effect */
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

<section id="sidebar">
		<a href="#" class="brand">
			<i class='bx bxs-smile'></i>
			<span class="text">CraftHive</span>
		</a>
		<ul class="side-menu top">
			<li>
				<a href="dashboard.php">
					<i class='bx bxs-dashboard' ></i>
					<span class="text">Seller's Dashboard</span>
				</a>
			</li>
			<li>
				<a href="product.php">
					<i class='bx bxl-product-hunt' ></i>
					<span class="text">Seller's Product</span>
				</a>
			</li>
			<li>
				<a href="delivered.php">
					<i class='bx bxl-magento' ></i>
					<span class="text">Seller's Management </span>
				</a>
			</li>
            <li class="active">
				<a href="review.php">
					<i class='bx bx-message' ></i>
					<span class="text">Seller's Review </span>
				</a>
			</li>
		</ul>
        <ul class="side-menu">
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
    <img src="../uploaded_profile/CH-logo.png" alt="Profile Image">
    </a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1> Seller's Review</h1>
					<ul class="breadcrumb">
						<li>
							<a href="ch-company.php">Seller</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="ch-company.php">S Review</a>
						</li>
					</ul>
				</div>
			</div>
		
<div class="charts-container">
        <div class="chart-container2">
             <h3>Top 5 Product</h3>
            <canvas id="productChart"></canvas>
        </div>
</div>        

<div class = "recent-transactions">
          <?php if(!empty($product_reviews)): ?>
              <table>
                  <thead>
                      <tr>
                         <th>Product Name</th>
                         <th>Average Rating</th>
                         <th>Review Count</th>
                          <th>Action</th>
                     </tr>
                   </thead>
                  <tbody>
                  <?php foreach($product_reviews as $review): ?>
                   <tr>
                     <td><?php echo htmlspecialchars($review['product_name']) ?></td>
                       <td><?php echo number_format((float)$review['average_rating'], 2, '.', ''); ?></td>
                       <td><?php echo htmlspecialchars($review['review_count']) ?></td>
                     <td>
                           <button type="button" onclick="openModal('reviewModal', '<?php echo $review['product_unique_id']; ?>')" class="option-btn">
                             <i class='bx bxs-message-square-dots'></i> View
                           </button>
                       </td>
                     </tr>
                   <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
</div>
</main>
 <div id="reviewModal" class="modal">
     <div class="modal-content">
        <div class = "modal-header">
            <h1>User Reviews</h1>
            <span class="close-btn" onclick="closeModal('reviewModal')">×</span>
         </div>
         
<div class = "recent-transactions">
        <table class="modal-review-table">
            <thead>
             <tr>
               <th>Rating</th>
                <th>Review</th>
            </tr>
          </thead>
         <tbody>
            <!-- Review content will go here -->
         </tbody>
        </table>
                  </div>        
     </div>
  </div>
<script src="../js/all_script.js"></script>

<script>
    let productChart;
    let sellerShareTableBody = document.querySelector('#sellerShareTable tbody');
  // Function to generate lighter shades of a base color
function generateColorShades(count, baseColor) {
    const hexToRgb = (hex) => {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    };
     const baseRgb = hexToRgb(baseColor);
     if (!baseRgb) {
        console.error("Invalid base color provided");
        return [];
    }
    const colorShades = [];
    for (let i = 0; i < count; i++) {
          const shadeFactor = 0.8 - (0.6 * i / count);  // Make the shades darker by subtracting a value from base color
        const shadeRed = Math.round(baseRgb.r * shadeFactor);
        const shadeGreen = Math.round(baseRgb.g  * shadeFactor);
         const shadeBlue = Math.round(baseRgb.b  * shadeFactor);
         colorShades.push(`rgba(${shadeRed}, ${shadeGreen}, ${shadeBlue}, 0.7)`);
    }
    return colorShades;
}

    // Function to initialize or update chart data
    function initializeCharts(productLabels, productCounts) {
         if (productChart) {
           productChart.destroy();
        }
         const baseColor = "#EE7E1A";
         const colorShades = generateColorShades(productLabels.length, baseColor);
        // Top 5 Products Chart
        const productCtx = document.getElementById('productChart');
        if (productCtx) {
                productChart = new Chart(productCtx, {
                    type: 'bar',
                    data: {
                        labels: productLabels,
                        datasets: [{
                            label: 'Top 5 Products',
                            data: productCounts,
                            backgroundColor: colorShades,
                            borderColor: '#fff',
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        scales: {
                            y: { beginAtZero: true }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
    }

    initializeCharts(<?php echo json_encode($product_labels); ?>, <?php echo json_encode($product_counts); ?>);
     // Function to open a modal
         async function openModal(modalId, productId){
              document.getElementById(modalId).style.display = 'block';
             await fetchProductReviews(productId, modalId);
         }

         //function to close modal
         function closeModal(modalId){
            document.getElementById(modalId).style.display = 'none';
             const reviewTableBody = document.querySelector(`#${modalId} .modal-review-table tbody`);
             reviewTableBody.innerHTML = '';
         }

          //close modal when click outside the modal content
        window.onclick = function(event){
           if(event.target.classList.contains('modal')){
             event.target.style.display = 'none';
           const reviewTableBody = event.target.querySelector('.modal-review-table tbody');
              reviewTableBody.innerHTML = '';
            }
         }

        async function fetchProductReviews(productId, modalId){
          try{
               const response = await fetch(`get_product_reviews.php?product_id=${productId}`,{
                  method: 'GET',
                   headers: {
                       'Content-Type': 'application/json',
                       'X-Requested-With': 'XMLHttpRequest'
                   }
               });

                if(!response.ok){
                  throw new Error(`HTTP error! status: ${response.status}`)
                 }
                const reviews = await response.json();

               const reviewTableBody = document.querySelector(`#${modalId} .modal-review-table tbody`);
            
                if(reviews && reviews.length > 0){
                      reviews.forEach(review => {
                        const row = document.createElement('tr');
                          row.innerHTML = `
                              <td>${review.rating}</td>
                             <td>${review.review_text ? review.review_text : 'No Review' }</td>
                          `;
                      reviewTableBody.appendChild(row);
                   });
                 }else {
                     const row = document.createElement('tr');
                    row.innerHTML = `<td colspan="2"> No reviews for this product yet</td>`
                    reviewTableBody.appendChild(row);
                  }


          }catch(error){
              console.error('Error fetching reviews:', error);
              alert('Failed to fetch reviews. Please try again.');
           }

        }
</script>

</body>
</html>