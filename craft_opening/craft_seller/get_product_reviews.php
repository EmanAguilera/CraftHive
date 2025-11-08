<?php
@include '../connection/connect.php';
header('Content-Type: application/json');

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    $review_query = "SELECT rating, review_text FROM `reviews` WHERE product_id = '$product_id'";
    $review_result = mysqli_query($conn, $review_query);

    $reviews = [];
    if ($review_result && mysqli_num_rows($review_result) > 0) {
        while ($row = mysqli_fetch_assoc($review_result)) {
            $reviews[] = $row;
        }
    }
    echo json_encode($reviews);
} else {
    echo json_encode(['error' => 'Product ID is missing']);
}
?>