<?php
session_start();
@include '../connection/connect.php';

header('Content-Type: application/json'); // Set response type to JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data, true); // Decode JSON data

    // Validate JSON decoding
    if (is_null($data)) {
        echo json_encode(['error' => 'Invalid JSON received.']);
        exit;
    }
      if (!isset($_SESSION['unique_id'])) {
        echo json_encode(['error' => 'User not logged in.']);
        exit;
    }
    $buyer_id = $_SESSION['unique_id'];
    $buyer_query = mysqli_query($conn, "SELECT fname, lname FROM `buyer` WHERE unique_id = '$buyer_id'");
    if ($buyer_query && mysqli_num_rows($buyer_query) > 0) {
        $buyer_data = mysqli_fetch_assoc($buyer_query);
         $buyer_name = $buyer_data['fname'] . ' ' . $buyer_data['lname'];
     } else {
        echo json_encode(['error' => 'Buyer not found.']);
        exit;
     }
    
        // Sanitize and gather product details from the JSON
    $product_id = mysqli_real_escape_string($conn, $data['product_id']);
      $product_name = mysqli_real_escape_string($conn, $data['product_name']);
    $quantity = isset($data['product_quantity']) ? intval($data['product_quantity']) : 1;

        // Fetch additional product details
        $select_product = mysqli_query($conn, "SELECT product_unique_id, price, image, seller_id, company_rep_id, delivery_id, delivery_name, seller_name FROM `product` WHERE product_unique_id = '$product_id'");
        
        if ($select_product && mysqli_num_rows($select_product) > 0) {
             $product_data = mysqli_fetch_assoc($select_product);
              $product_id = $product_data['product_unique_id'];
            $product_price = $product_data['price'];
            $product_image = $product_data['image'];
             $seller_id = $product_data['seller_id'];
            $company_rep_id = $product_data['company_rep_id'];
             $delivery_id = $product_data['delivery_id'];
           $delivery_name = $product_data['delivery_name'];
           $seller_name = $product_data['seller_name'];
        
         // Check if the product with the same options is already in the cart
            $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE product_id = '$product_id' AND buyer_id = '$buyer_id'");

            if (mysqli_num_rows($select_cart) > 0) {
                
                //Update Product quantity
                 $update_quantity = mysqli_query($conn, "UPDATE `cart` SET quantity = quantity + $quantity WHERE product_id = '$product_id' AND buyer_id = '$buyer_id'");
                    if($update_quantity){
                       echo json_encode(['success' => 'Product quantity updated in cart.']);
                   }
                     else {
                        echo json_encode(['error' => 'Failed to update product quantity in cart.']);
                     }
             } else {
               // Safely generate a unique ID
                 do {
                        $cart_unique_id = rand(100000000, 999999999); // Generates a random 9-digit number
                        $unique_check = mysqli_query($conn, "SELECT cart_unique_id FROM `cart` WHERE cart_unique_id = '$cart_unique_id'");
                } while (mysqli_num_rows($unique_check) > 0);
                // Insert the product into the cart table
                 $insert_product = mysqli_query($conn, "INSERT INTO `cart` (cart_unique_id, buyer_id, product_id, seller_id, company_rep_id, product_name, buyer_name, price, image, quantity, delivery_id, delivery_name, seller_name) 
                            VALUES ('$cart_unique_id', '$buyer_id', '$product_id', '$seller_id', '$company_rep_id', '$product_name', '$buyer_name', '$product_price', '$product_image', '$quantity', '$delivery_id', '$delivery_name', '$seller_name')");

                if ($insert_product) {
                     echo json_encode(['success' => 'Product added to cart successfully.']);
                  } else {
                      echo json_encode(['error' => 'Failed to add product to cart.']);
                   }
                }
        } else {
            echo json_encode(['error' => 'Product not found.']);
        }
    }
    ?>