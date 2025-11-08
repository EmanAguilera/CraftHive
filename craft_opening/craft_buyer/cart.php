<?php
session_start();
@include '../connection/connect.php';

// Check if the buyer is logged in (require unique_id only)
if (!isset($_SESSION['unique_id'])) {
    header('Location: login.php');
    exit;
}

$buyer_id = $_SESSION['unique_id'];
$message = [];

if (isset($_POST['update_update_btn'])) {
  $update_value = $_POST['update_quantity'];
  $update_id = $_POST['update_quantity_id'];

   // Fetch current quantity and stock from cart using cart_unique_id
    $select_cart_item = mysqli_query($conn, "SELECT quantity, stocks FROM `cart` WHERE cart_unique_id = '$update_id' AND buyer_id = '$buyer_id'");
    if ($select_cart_item && mysqli_num_rows($select_cart_item) > 0) {
        $cart_item = mysqli_fetch_assoc($select_cart_item);
        $current_quantity = $cart_item['quantity'];
        $available_stock = $cart_item['stocks'];
        
         if ($update_value > $available_stock) {
            $message[] = "Requested quantity exceeds available stock.";
          } else {
            $update_quantity_query = mysqli_query($conn, "UPDATE `cart` SET quantity = '$update_value' WHERE cart_unique_id = '$update_id' AND buyer_id = '$buyer_id'");
             if ($update_quantity_query) {
                 header('location:cart.php');
            } else {
                $message[] = "Failed to update quantity.";
            }
        }
    } else {
        $message[] = "Product not found in cart.";
    }
}

if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    mysqli_query($conn, "DELETE FROM `cart` WHERE cart_unique_id = '$remove_id' AND buyer_id = '$buyer_id'");
    header('location:cart.php');
}

if (isset($_GET['delete_all'])) {
    mysqli_query($conn, "DELETE FROM `cart` WHERE buyer_id = '$buyer_id'");
    header('location:cart.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS file link -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

    <link rel="stylesheet" href="../../css/design.css">
    <link rel ="stylesheet" href = "additionals.css">

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
                <a href="company.php">
                    <i class='bx bx-list-ol' ></i>
                    <span class="text">Company List</span>
                </a>
            </li>
            <li class="active">
                <a href="cart.php">
                    <i class='bx bxs-cart-add' ></i>
                    <span class="text">Customer Cart</span>
                </a>
            </li>
            <li>
                <a href="checkout.php">
                    <i class='bx bxs-purchase-tag-alt' ></i>
                    <span class="text">Complete Purchase </span>
                </a>
            </li>
            <li>
                <a href="order.php">
                    <i class='bx bxs-detail' ></i>
                    <span class="text">Check Details</span>
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
                <h1>Customer Cart</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="ch-company.php">Crafthive</a>
                        </li>
                        <li><i class='bx bx-chevron-right' ></i></li>
                        <li>
                            <a class="active" href="company.php">C List</a>
                        </li>
                        <li><i class='bx bx-chevron-right' ></i></li>
                        <li>
                            <a class="active" href="company_product.php">C Product</a>
                        </li>
                        <li><i class='bx bx-chevron-right' ></i></li>
                        <li>
                            <a class="active" href="cart.php">C Cart</a>
                        </li>
                    </ul>
                </div>
            </div>

<section class="recent-transactions">

    <table>

        <thead>
            <th>Image</th>
            <th>Name</th>
            <th>Price</th>
            <th>Shipping Fee</th>
            <th>Quantity</th>
             <th>Stocks</th>
            <th>Total Price</th>
            <th>Action</th>
             <th>Checkout</th>
        </thead>

        <tbody>
            <?php
            // Fetch cart items for the logged-in buyer
            $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE buyer_id = '$buyer_id'");
            $grand_total = 0;
             $processed_companies = []; // Array to keep track of processed company_rep_ids


            if (mysqli_num_rows($select_cart) > 0) {
                while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                      $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']) + $fetch_cart['shipping_fee'] ;
            ?>
            <tr>
                <td><img src="../../uploaded_img/<?php echo htmlspecialchars($fetch_cart['image']); ?>" height="100" alt=""></td>
                <td><?php echo htmlspecialchars($fetch_cart['product_name']); ?></td>
                <td>₱<?php echo number_format($fetch_cart['price'], 2); ?>/-</td>
                <td>₱<?php echo number_format($fetch_cart['shipping_fee'], 2); ?>/-</td>
                <td>
                    <form class="update-quantity-form" action="" method="post">
                       <input type="hidden" name="update_quantity_id" value="<?php echo htmlspecialchars($fetch_cart['cart_unique_id']); ?>">
                       <input type="number" name="update_quantity" min="1" value="<?php echo htmlspecialchars($fetch_cart['quantity']); ?>">
                      <input type="submit" value="Update" name="update_update_btn">
                    </form>
                </td>
                 <td><?php echo htmlspecialchars($fetch_cart['stocks']); ?></td>
                <td>₱<?php echo number_format($sub_total, 2); ?>/-</td>
                <td><a href="cart.php?remove=<?php echo htmlspecialchars($fetch_cart['cart_unique_id']); ?>" onclick="return confirm('Remove item from cart?')" class="delete-btn"><i class="fas fa-trash"></i> Remove</a></td>
                <td>
                    <?php
                        // Check if the company_rep_id has already been processed
                         if (!isset($processed_companies[$fetch_cart['company_rep_id']])) {
                            ?>
                                <a href="checkout.php?company_rep_id=<?php echo $fetch_cart['company_rep_id']; ?>" class="option-btn">Proceed to Checkout</a>
                            <?php
                            $processed_companies[$fetch_cart['company_rep_id']] = true;
                           }
                       ?>
                 </td>
            </tr>
            <?php
                  $grand_total += $sub_total;
                }
            } else {
                echo '<tr><td colspan="9" style="text-align: center;">Your cart is empty!</td></tr>';
            }
            ?>
            <tr class="table-bottom">
                <td><a href="company.php" class="option-btn" style="margin-top: 0;">Continue Shopping</a></td>
                <td colspan="6">Grand Total</td>
                <td>₱<?php echo number_format($grand_total, 2); ?>/-</td>
                <td><a href="cart.php?delete_all" onclick="return confirm('Are you sure you want to delete all?');" class="delete-btn"><i class="fas fa-trash"></i> Delete All</a></td>
            </tr>
        </tbody>
    </table>
<br>
</section>
</main>
</section>
<!-- Custom JS file link -->
<script src="../js/all_script.js"></script>
</body>
</html>