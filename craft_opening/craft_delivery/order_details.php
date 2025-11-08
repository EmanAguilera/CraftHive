<?php
session_start();
@include '../connection/connect.php';

// Check if the delivery person is logged in
if (!isset($_SESSION['unique_id']) || !isset($_SESSION['delivery_name']) || !isset($_SESSION['company_rep_id'])) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit;
}

// Get session variables
$delivery_id = $_SESSION['unique_id'];

// Check if order_id is provided in the query string
if (!isset($_GET['order_id'])) {
    echo "Order ID not provided.";
    exit;
}

$order_id = intval($_GET['order_id']);

// Fetch order details
$query = "SELECT o.order_id, o.product_name, o.buyer_name, o.quantity, o.total_price, 
          o.shipping_address, o.status, p.image
          FROM `order` o
          JOIN `product` p ON o.product_id = p.product_unique_id
          WHERE o.order_id = '$order_id' AND o.delivery_id = '$delivery_id'";

$result = mysqli_query($conn, $query);

// Check if the order exists
if (mysqli_num_rows($result) == 0) {
    echo "No details found for this order.";
    exit;
}

$order = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Order Details</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="../../css/accounts_designs.css">
</head>
<body>


<?php include '../header/another_layer.php'; ?>

<div class="container">

<section class="order-details">
   <h1>Order Details</h1>
   <div class="container">
   <section>
      <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
      <p><strong>Product Name:</strong> <?php echo htmlspecialchars($order['product_name']); ?></p>
      <p><strong>Buyer Name:</strong> <?php echo htmlspecialchars($order['buyer_name']); ?></p>
      <p><strong>Quantity:</strong> <?php echo htmlspecialchars($order['quantity']); ?></p>
      <p><strong>Total Price:</strong> ₱<?php echo htmlspecialchars($order['total_price']); ?></p>
      <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
      <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
   </div>

   <div class="product-image">
      <h2>Product Image</h2>
      <img src="../../uploaded_img/<?php echo htmlspecialchars($order['image']); ?>" alt="Product Image" height="200">
   </div>

   <a href="orders.php" class="back-btn">Back to Orders</a>
</section>

</div>

</body>
</html>
