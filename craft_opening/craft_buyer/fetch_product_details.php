<?php
@include '../connection/connect.php';
header('Content-Type: application/json');

if (isset($_GET['product_id'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['product_id']);

    $query = mysqli_query($conn, "SELECT * FROM `product` WHERE product_unique_id = '$product_id'");

    if (mysqli_num_rows($query) > 0) {
        $product_data = mysqli_fetch_assoc($query);
         // Split sizes, colors, and shapes
        $sizes = explode(',', str_replace("Size: ", "", $product_data['size']));
        $colors = explode(',', str_replace("Color: ", "", $product_data['color']));
        $shapes = explode(',', str_replace("Shape: ", "", $product_data['shape']));
         
        // Remove empty strings or spaces
        $sizes = array_filter(array_map('trim', $sizes));
        $colors = array_filter(array_map('trim', $colors));
        $shapes = array_filter(array_map('trim', $shapes));

        echo json_encode([
            'product_unique_id' => $product_data['product_unique_id'],
             'product_name' => $product_data['product_name'],
             'image' => $product_data['image'],
             'price' => $product_data['price'],
              'seller_description' => $product_data['seller_description'],
            'sizes' => $sizes,
            'colors' => $colors,
            'shapes' => $shapes,
            'stocks' => $product_data['stocks'],
             'shipping_fee' => $product_data['shipping_fee']
        ]);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
} else {
    echo json_encode(['error' => 'Product ID not provided']);
}
?>