<?php
session_start();
@include '../connection/connect.php';

header('Content-Type: application/json'); // Set header for JSON

$response = ['success' => false, 'message' => '']; // Initialize response array

// Check if the request is AJAX and POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(file_get_contents("php://input"))) {
    $inputData = json_decode(file_get_contents("php://input"), true);

    // Check if JSON decoding was successful
    if ($inputData === null && json_last_error() !== JSON_ERROR_NONE) {
        $response['message'] = 'Invalid JSON format';
        echo json_encode($response);
        exit;
    }
    
    // Validate required fields
    $requiredFields = ['product_id', 'size', 'color', 'shape', 'quantity', 'buyer_id', 'stocks', 'shipping_fee'];
    foreach ($requiredFields as $field) {
        if (!isset($inputData[$field])) {
             $response['message'] = 'Missing field: ' . $field;
            echo json_encode($response);
            exit;
        }
    }

   try{
        // Fetch the product and user data from the request
        $productId = mysqli_real_escape_string($conn, $inputData['product_id']);
        $size = mysqli_real_escape_string($conn, $inputData['size']);
        $color = mysqli_real_escape_string($conn, $inputData['color']);
        $shape = mysqli_real_escape_string($conn, $inputData['shape']);
        $quantity = intval($inputData['quantity']);
        $buyerId = mysqli_real_escape_string($conn, $inputData['buyer_id']);
        $stocks = mysqli_real_escape_string($conn, $inputData['stocks']); // Get stocks
        $shippingFee = mysqli_real_escape_string($conn, $inputData['shipping_fee']);
        // Retrieve product details
        $productQuery = mysqli_query($conn, "SELECT * FROM `product` WHERE product_unique_id = '$productId'");
        if ($productQuery && mysqli_num_rows($productQuery) > 0) {
            $product = mysqli_fetch_assoc($productQuery);

            $productName = $product['product_name'];
            $productPrice = $product['price'];
            $productImage = $product['image'];
            $sellerId = $product['seller_id'];
            $companyRepId = $product['company_rep_id'];
            $deliveryId = $product['delivery_id'];
            $deliveryName = $product['delivery_name'];
            $sellerName = $product['seller_name'];

            // Check if the product already exists in the cart
            $checkCartQuery = mysqli_query($conn, "SELECT * FROM `cart` WHERE product_id = '$productId' AND buyer_id = '$buyerId' AND size = '$size' AND shape = '$shape' AND color = '$color'");

             if (mysqli_num_rows($checkCartQuery) > 0) {
                $response['message'] = 'Product already in the cart.';
            } else {
                // Safely generate a unique cart ID
                do {
                    $cartUniqueId = rand(100000000, 999999999); // Generate a random 9-digit number
                    $uniqueCheck = mysqli_query($conn, "SELECT cart_unique_id FROM `cart` WHERE cart_unique_id = '$cartUniqueId'");
                } while (mysqli_num_rows($uniqueCheck) > 0);

                // Insert into the cart
                $insertQuery = "INSERT INTO `cart` (cart_unique_id, buyer_id, product_id, seller_id, company_rep_id, product_name, price, image, quantity, size, shape, color, delivery_id, delivery_name, seller_name, stocks, shipping_fee)
                VALUES ('$cartUniqueId', '$buyerId', '$productId', '$sellerId', '$companyRepId', '$productName', '$productPrice', '$productImage', '$quantity', '$size', '$shape', '$color', '$deliveryId', '$deliveryName', '$sellerName', '$stocks', '$shippingFee')";


                if (mysqli_query($conn, $insertQuery)) {
                    $response['success'] = true;
                     $response['message'] = 'Product added to cart successfully.';
                } else {
                    $response['message'] = 'Failed to add product to cart.';
                }
            }
        } else {
            $response['message'] = 'Product not found.';
        }
    }
    catch(Exception $e){
        $response['message'] = 'Error: ' . $e->getMessage();
    }
}else{
   $response['message'] = 'Invalid request.';
}
echo json_encode($response);
?>