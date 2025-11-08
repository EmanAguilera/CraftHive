<?php
session_start();
@include '../connection/connect.php';

// Check if the buyer is logged in (require unique_id only)
if (!isset($_SESSION['unique_id'])) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit;
}

$company_name = '';
$search_term = ''; // Initialize search term

// Check if `company_name` is set in POST or preserve it for subsequent requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['company_name'])) {
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
} elseif (isset($_SESSION['company_name'])) {
    $company_name = $_SESSION['company_name'];
} else {
    $message[] = "No company selected."; // Store message in array
    exit;
}

// Store `company_name` in session for subsequent requests
$_SESSION['company_name'] = $company_name;

$buyer_id = $_SESSION['unique_id'];
$buyer_query = mysqli_query($conn, "SELECT fname, lname FROM `buyer` WHERE unique_id = '$buyer_id'");
if ($buyer_query && mysqli_num_rows($buyer_query) > 0) {
    $buyer_data = mysqli_fetch_assoc($buyer_query);
    $buyer_name = $buyer_data['fname'] . ' ' . $buyer_data['lname'];
} else {
    $message[] = "Buyer not found."; // Store message in array
    exit;
}
if (isset($_POST['add_to_cart'])) {
    // Gather product details from the form
    $product_id = $_POST['product_unique_id'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];
    $product_quantity = 1;
    $size = $_POST['size'] ?? ''; // Fetch size from form
    $shape = $_POST['shape'] ?? ''; // Fetch shape from form
    $color = $_POST['color'] ?? ''; // Fetch color from form

    // Additional info based on the product_id
    $select_product = mysqli_query($conn, "SELECT seller_id, company_rep_id, delivery_id, delivery_name, seller_name, stocks, shipping_fee FROM `product` WHERE product_unique_id = '$product_id'");
    if ($select_product && mysqli_num_rows($select_product) > 0) {
        $product_data = mysqli_fetch_assoc($select_product);

        $seller_id = $product_data['seller_id'];
        $company_rep_id = $product_data['company_rep_id'];
        $delivery_id = $product_data['delivery_id'];
        $delivery_name = $product_data['delivery_name'];
        $seller_name = $product_data['seller_name'];
        $buyer_id = $_SESSION['unique_id']; // Buyer's unique ID
        $stocks = $product_data['stocks'];
        $shipping_fee = $product_data['shipping_fee'];

        // Check if the product with the same options is already in the cart
        $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE product_id = '$product_id' AND buyer_id = '$buyer_id' AND size = '$size' AND shape = '$shape' AND color = '$color'");

        if (mysqli_num_rows($select_cart) > 0) {
            $message[] = "Product with selected options already added to cart."; // Store message in array
        } else {
            // Safely generate a unique ID
            do {
                $cart_unique_id = rand(100000000, 999999999); // Generates a random 9-digit number
                $unique_check = mysqli_query($conn, "SELECT cart_unique_id FROM `cart` WHERE cart_unique_id = '$cart_unique_id'");
            } while (mysqli_num_rows($unique_check) > 0);

            // Insert the product into the cart table
            $insert_product = mysqli_query(
                $conn,
                "INSERT INTO `cart` (cart_unique_id, buyer_id, product_id, seller_id, company_rep_id, product_name, buyer_name, price, image, quantity, delivery_id, delivery_name, seller_name, size, shape, color, stocks, shipping_fee) 
                VALUES ('$cart_unique_id', '$buyer_id', '$product_id', '$seller_id', '$company_rep_id', '$product_name', '$buyer_name', '$product_price', '$product_image', '$product_quantity', '$delivery_id', '$delivery_name', '$seller_name', '$size', '$shape', '$color', '$stocks', '$shipping_fee')"
            );

            if ($insert_product) {
                $message[] = "Product added to cart successfully."; // Store success message in array
            } else {
                $message[] = "Failed to add product to cart."; // Store failure message in array
            }
        }
    } else {
        $message[] = "Product not found."; // Store message in array
    }
}
if (isset($_GET['search'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['search']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Products</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <!-- Custom CSS file link -->
   <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

   <link rel="stylesheet" href="../../css/design.css">
   <link rel="stylesheet" href="additionals.css">
   <script src="https://cdn.jsdelivr.net/npm/fuse.js@6.6.2"></script>
   <style>
/* Styling remains the same */


.box p {
   font-size: 15px;
   color: #5e4632;
   margin-bottom: 15px;
   line-height: 1.8;
   text-align: left;
   background: #fdf5e6;
   padding: 10px 15px;
   border-radius: 8px;
   box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
   border: 1px solid #d2b89c;
   transition: background 0.3s ease, transform 0.3s ease;
}

.box p:hover {
   background: #fde2b8;
   transform: scale(1.02);
   box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
   color: #5c2e0a;
}

.box p strong {
   color: #7a3b0e;
   font-weight: bold;
   text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

/* Search input field */
#searchInput {
    display: block;
    width: 80%;
    max-width: 500px;
    margin: 20px auto;
    padding: 10px 15px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}


/* Modal styling */
#voiceModal {
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

.modal-content-voice {
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

.assistant-container {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-bottom: 10px;
}

.assistant-container .avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-left: 10px;
}

/* Add some spacing for better usability */
.message {
    padding: 5px 0;
}

.buyer-container,
.assistant-container {
    margin-bottom: 10px;
}

.close-modal, .close-modal-voice {
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    color: #aaa;
}
.close-modal:hover, .close-modal:focus,
.close-modal-voice:hover, .close-modal-voice:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

.message {
    margin-bottom: 10px; /* space between each message */
    display: flex;
    clear: both; /* Important for ensuring that flexbox container won't overlap */
}

.buyer-container {
    display: flex;
    align-items: flex-start; /* Aligns items at the top */
    justify-content: flex-end;
    flex-direction: row-reverse; /* Puts the avatar on the right */
     width: 80%;
    margin-left: auto;  /* Push the buyer's message to the right*/
}

.assistant-container {
    display: flex;
    align-items: flex-start; /* Aligns items at the top */
    justify-content: flex-start;
    flex-direction: row; /* Keeps avatar on the left */
    width: 80%; /* Keeps assistant message to the left*/
}

/* Modal Body */
.modal-body {
    font-size: 1.2em;
    line-height: 1.8;
    color: #5C4033; /* Warm brown */
    margin-top: 20px;
    text-align: justify;
    font-family: 'Libre Baskerville', serif;
}

.modal-body p {
  margin-bottom: 10px;
  font-size: 1.5em;
  color: #5C4033; /* Warm brown tone for consistency */
  font-family: 'Libre Baskerville', serif;
  line-height: 1.6;
}


/* Close Button */
.close-modal-voice {
    color: #3E2723;
    font-size: 2.5em;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.3s ease, color 0.3s ease;
}

/* Close Button */
.close-modal {
    color: #3E2723;
    font-size: 2.5em;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.3s ease, color 0.3s ease;
}

.close-modal-voice:hover,
.close-modal-voice:focus {
    color: #EE7E1A; /* Muted orange for a warm touch */
    transform: rotate(90deg); /* Elegant hover effect */
}

.close-modal:hover,
.close-modal:focus {
    color: #EE7E1A; /* Muted orange for a warm touch */
    transform: rotate(90deg); /* Elegant hover effect */
}


.avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin: 0 5px; /* Keeps space between the avatar and the text */
}

.buyer-message {
    display: flex;
    align-items: center;
    background-color: #F4E3C1; /* Soft beige for the background */
    color: #6B4226; /* Rich coffee brown for text */
    padding: 12px 15px; /* Comfortable spacing */
    border-radius: 12px;
    max-width: 80%;
    margin-left: auto; /* Align buyer messages to the right */
    box-shadow: 0 4px 8px rgba(107, 66, 38, 0.2); /* Elegant shadow with brown tones */
    transition: background-color 0.3s ease, box-shadow 0.3s ease; /* Smooth transitions */
    font-size: 14px; /* Slightly larger font for readability */
    font-family: 'Libre Baskerville', serif; /* Elegant serif font */
}

/* Hover effect for interactivity */
.buyer-message:hover {
    background-color: #E9D7B8; /* Slightly darker beige on hover */
    box-shadow: 0 6px 12px rgba(107, 66, 38, 0.3); /* Enhanced shadow on hover */
}

.assistant-message {
display: flex;
  align-items: center;
  background-color: #D9B68A; /* Softer light background for assistant */
  color: #792A03; /* Darker text for contrast */
  padding: 12px 15px; /* Increased padding for better spacing */
  border-radius: 12px;
  max-width: 80%;
  margin-right: auto; /* Align assistant messages to the left */
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow */
  transition: background-color 0.3s ease; /* Smooth background transition */
}
/* Optional - Container styles for the chat log*/


.assistant-message:hover {
  background-color: #C69B68; /* Slightly darker on hover */
}
.buyer-message, .assistant-message {
  line-height: 1.5; /* Increased line height for readability */
  word-wrap: break-word;
}

/* Voice search button within the search bar */
.form-input {
    display: flex;
    align-items: center;
    position: relative;
}

.form-input #voiceSearchButton {
     position: absolute;
     right: 40px; /* Adjust position as needed */
     background-color: transparent;
     border: none;
     font-size: 20px; /* Adjust icon size */
     cursor: pointer;
     color: #777;
}

.form-input #voiceSearchButton:hover {
    color: #333;
}

.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown .category-link {
   cursor: pointer;
    text-decoration: none; /* Remove underline from the link */
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #f9f9f9;
    min-width: 160px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1;
    border-radius: 8px;
    overflow: hidden; /* Ensure rounded corners are applied correctly */
    top: 100%; /* Position below the link */
    left: 0; /* Align with the left of the link */
}

.dropdown-content a {
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    transition: background-color 0.3s; /* Add transition for hover effect */
}


.dropdown-content a:hover {
    background-color: #e0e0e0; /* Light gray on hover for visual feedback */
}


.dropdown:hover .dropdown-content {
    display: block;
}

@media (max-width: 768px) { /* Small screens like tablets and phones */
    .modal {
        background-color: rgba(0, 0, 0, 0.8); /* Adjust opacity as needed */
    }
    .modal-content{
        max-width: 100%; /* Make modal wider */
        padding: 30px; /* Adjust padding if needed */
    }

     .modal-header h1 {
    font-size: 2em;
     }

   .modal-body p {
    font-size: 20px;
   }

     .modal-options select {
        padding: 10px 15px;
        font-size: 0.9em;
    }
}

@media (max-width: 480px) { /* Even smaller screens like phones */
 .modal-content {
    max-width: 100%;
    padding: 20px;
   }
 .modal-header h1{
      font-size: 1.8em;
 }
 .modal-body p {
    font-size: 18px;
   }
 .modal-options select {
   font-size: 0.85em;
 }
 .add-to-cart-btn {
        font-size: 14px;
        padding: 8px;
    }
}

@media (max-width: 320px) { /* Very small screens */
    .modal-header h1{
      font-size: 1.5em;
    }
     .modal-body p {
    font-size: 16px;
   }
     .modal-options select {
    font-size: 0.75em;
    }
    .add-to-cart-btn {
        font-size: 14px;
        padding: 8px;
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

<section id="sidebar">
    <a href="#" class="brand">
        <i class='bx bxs-smile'></i>
        <span class="text">CraftHive</span>
    </a>
    <ul class="side-menu top">
        <li class="active">
            <a href="company.php">
                <i class='bx bx-list-ol' ></i>
                <span class="text">Company List</span>
            </a>
        </li>
        <li>
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
    <div class="dropdown">
        <a href="#" class="nav-link category-link">Categories</a>
        <div class="dropdown-content" id="categoryDropdown">
        <!-- Categories will go here -->
        <a href="#">All</a>
        <a href="#">Basketry</a>
        <a href="#">Hats</a>
        <a href="#">Food Cover</a>
        <a href="#">Serving ware</a>
        <a href="#">Lighting</a>
        <a href="#">Picnic Essentials</a>
         <a href="#">Personal Care</a>
          <a href="#">Dining Accessories</a>
           <a href="#">Clocks</a>
            <a href="#">Wall Decor</a>
            <a href="#">Toys</a>
            <a href="#">Souvenirs</a>
             <a href="#">Folding Fan</a>
              <a href="#">Tissue Box Covers</a>
        </div>
    </div>
    <form method="get">
        <div class="form-input">
            <input type="search" id="searchInput" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search_term); ?>">
            <button id="voiceSearchButton" type="button" ><i class='bx bxs-microphone' ></i></button>
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

    <!-- MAIN -->
    <main>
        <div class="head-title">
            <div class="left">
            <h1>Products by <?php echo htmlspecialchars($company_name); ?></h1>
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
                </ul>
            </div>
        </div>


        <div id="voiceModal" class="modal">
            <div class="modal-content-voice">
                <div class="modal-header">
                    <h1>Voice Recognition</h1>
                    <span class="close-modal-voice">×</span>
                 </div>
                <div id="conversationLog"></div>
             </div>
         </div>

        <section class="products">

            <div class="box-container">

    <?php
    // Fetch products based on the company name, including search
    $sql = "SELECT * FROM `product` WHERE company_rep_id = (SELECT company_rep_id FROM `description` WHERE company_name = '$company_name')";
    if (!empty($search_term)) {
        $sql .= " AND product_name LIKE '%" . $search_term . "%'";
    }
    $select_products = mysqli_query($conn, $sql);
    if ($select_products && mysqli_num_rows($select_products) > 0) {
        while ($fetch_product = mysqli_fetch_assoc($select_products)) {
            $productId = htmlspecialchars($fetch_product['product_unique_id']);
            $productName = htmlspecialchars($fetch_product['product_name']);
            $productImage = htmlspecialchars($fetch_product['image']);
            $productPrice = htmlspecialchars($fetch_product['price']);
            $productStocks = htmlspecialchars($fetch_product['stocks']); // Display product stock
             $productShippingFee = htmlspecialchars($fetch_product['shipping_fee']); // Display product shipping fee
    ?>

<div class="box" data-product-id="<?php echo $productId; ?>">
    <img src="../../uploaded_img/<?php echo $productImage; ?>" alt="Product Image">
    <h3><?php echo $productName; ?></h3>
       <p> <strong>Price:</strong> ₱<?php echo $productPrice; ?>/-<br> </p>
        <p> <strong>Shipping Fee:</strong> ₱<?php echo $productShippingFee; ?>/-<br> </p>
       <p> <strong>Available Stock:</strong> <?php echo $productStocks; ?> </p>

    <!-- Button that is always visible -->
    <button class="view-details-button" data-product-id="<?php echo $productId; ?>">View Details</button>
</div>


    <!-- Modal for displaying product details -->
    <div id="product-info-modal" class="modal">
        <div class="modal-content">
        <div class="modal-header">
            <img id="product-image" src="" alt="Product Image" class="modal-product-image">
            <h1 id="product-name"></h1>
            <span class="close-modal">×</span>
        </div>
        <form id="product-options-form">
            <div class="modal-body">
                <div>
                    <p><strong>Description:</strong></p>
                    <p id="product-description"></p>
                 </div>
                 <div>
                     <p><strong>Shipping Fee:</strong> ₱<span id="product-shipping-fee"></span></p>
                 </div>
                <div class="modal-options-row">
                <div class="modal-options">
                    <select id="size" name="size">
                        <!-- Size options will be populated dynamically -->
                    </select>
                </div>
                <div class="modal-options">
                    <select id="color" name="color">
                        <!-- Color options will be populated dynamically -->
                    </select>
                </div>
                <div class="modal-options">
                    <select id="shape" name="shape">
                        <!-- Shape options will be populated dynamically -->
                    </select>
                </div>
                </div>
                <input type="hidden" name="product_id" id="product_unique_id">
                <input type="hidden" name="product_name" id="product_name_input">
                <input type="hidden" name="product_price" id="product_price_input">
                <input type="hidden" name="product_image" id="product_image_input">
                <input type="hidden" name="stocks" id="product_stocks_input">
                <input type="hidden" name="shipping_fee" id="shipping_fee_input">
                <button type="submit" class="add-to-cart-btn">Add to Cart</button>
            </div>
        </form>
        </div>
    </div>

    <?php
        }
    } else {
        echo "<p>No products found for this company" . (!empty($search_term) ? " matching your search term." : ".") . "</p>";
    }
    ?>
    </div>

</section>
</div>

<!-- Custom JS file link -->
<script src="../js/all_script.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Define all major elements at the top for easy access ---
    const voiceModal = document.getElementById("voiceModal");
    const searchInput = document.getElementById('searchInput');
    const voiceSearchButton = document.getElementById('voiceSearchButton');
    const categoryLink = document.querySelector('.category-link');
    const categoryDropdown = document.getElementById('categoryDropdown');
    const conversationLog = document.getElementById('conversationLog');

    // --- SETUP FOR UI ELEMENTS (Category Dropdown, etc.) ---
    categoryLink.addEventListener('click', function(event){
       event.preventDefault();
       categoryDropdown.style.display = categoryDropdown.style.display === "block" ? "none" : "block";
    });

    document.addEventListener('click', function(event) {
        if (!categoryLink.contains(event.target) && !categoryDropdown.contains(event.target)) {
            categoryDropdown.style.display = 'none';
        }
    });

    categoryDropdown.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            const category = link.textContent.trim();
            searchInput.value = (category === 'All') ? '' : category;
            const searchForm = searchInput.closest('form');
            if(searchForm) {
                searchForm.submit();
            }
            categoryDropdown.style.display = 'none';
       });
    });

    function updateSearchInput(term) {
        searchInput.value = term;
        const searchForm = searchInput.closest('form');
        if(searchForm) {
            searchForm.submit();
        }
    }

    // --- VOICE RECOGNITION LOGIC (Self-contained module) ---
    (function() {
        let state = "initial";
        let currentProduct = {};
        let currentMatchedProducts = [];
        let hasIntroduced = false;
        let isListening = false;
        const closeModalVoice = document.querySelector(".close-modal-voice");
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (!SpeechRecognition) {
            console.error("Speech Recognition not supported.");
            voiceSearchButton.style.display = 'none';
            return;
        }

        const recognition = new SpeechRecognition();
        recognition.continuous = true;
        recognition.interimResults = false;

        function speak(text) {
            if (isListening) {
                recognition.stop();
            }
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.rate = 1;
            utterance.volume = 1;
            utterance.pitch = 1;
            logConversation("Assistant", text);
            utterance.onend = () => {
                if (voiceModal.style.display === 'block' && !isListening) {
                    recognition.start();
                }
            };
            window.speechSynthesis.speak(utterance);
        }

        function wishMe() {
            const hour = new Date().getHours();
            const greeting = hour < 12 ? "Good morning!" : hour < 17 ? "Good afternoon!" : "Good evening!";
            const introMessage = `${greeting} I’m Craftra, your voice assistant. You can add items by saying the quantity and product name, like, 'two basket'. You can also search for items. How can I help?`;
            speak(introMessage);
            hasIntroduced = true;
        }

        voiceSearchButton.addEventListener('click', () => {
            searchInput.placeholder = "Listening...";
            toggleVoiceModal(true);
            if (!hasIntroduced) {
                wishMe();
            }
            recognition.start();
        });

        recognition.onstart = () => { isListening = true; };
        recognition.onend = () => {
            isListening = false;
            searchInput.placeholder = "Click to speak";
            if (voiceModal.style.display === 'block' && !window.speechSynthesis.speaking) {
                setTimeout(() => { if (!isListening) recognition.start(); }, 1000);
            }
        };
        recognition.onresult = (event) => {
            const transcript = event.results[event.resultIndex][0].transcript.trim().toLowerCase();
            if (transcript) {
                searchInput.value = transcript;
                logConversation("Buyer", transcript);
                takeCommand(transcript);
            }
        };

        function takeCommand(message) {
            const searchKeywords = ["search for", "find", "show"];
            for (const keyword of searchKeywords) {
                if (message.startsWith(keyword)) {
                    const searchTerm = message.substring(keyword.length).trim();
                    if (searchTerm) {
                        updateSearchInput(searchTerm);
                        speak(`Searching for ${searchTerm}`);
                        return;
                    }
                }
            }
            const quantity = detectQuantity(message);
            const products = [
                { name: "Cutting Board: Slice & Season!", normalized: "cutting board slice season", keywords: ["cutting board", "kitchenware", "wood craft", "wood", "craft", "cutting"], price: 50, product_id: "193022355", category: "Serving ware" },
                { name: "Rattan Lamps: Harmony Ambience!", normalized: "rattan lamps harmony ambience", keywords: ["rattan lamp", "basketry", "home deco", "lighting", "wood craft", "wood", "craft", "lamp", "rattan"], price: 100, product_id: "131907948", category: "Lighting" },
                { name: "Tropical Handwoven Fruit Basket", normalized: "tropical handwoven fruit basket", keywords: ["fruit basket", "basket", "handwoven", "basketry", "serving ware"], price: 400, product_id: "864443688", category: "Basketry" },
                { name: "Sun-Kissed Woven Hat Accessory", normalized: "sun kissed woven hat accessory", keywords: ["woven hat", "hat", "hats", "accessories"], price: 200, product_id: "588049611", category: "Hats" },
                { name: "Nito Food Cover Keeper - Preserve", normalized: "nito food cover keeper preserve", keywords: ["nito food cover", "food cover", "serving ware", "kitchen"], price: 400, product_id: "864350842", category: "Food Cover" },
                { name: "Woven Rattan Tray with Fabric Liner", normalized: "woven rattan tray with fabric liner", keywords: ["rattan tray", "tray", "serving ware", "rattan", "woven"], price: 350, product_id: "552709668", category: "Serving ware" },
                { name: "Vibrant Colorful String Light Balls", normalized: "vibrant colorful string light balls", keywords: ["string lights", "lights", "lighting"], price: 400, product_id: "595501250", category: "Lighting" },
                { name: "Capiz Shell Lanterns - Elegant Lighting", normalized: "capiz shell lanterns elegant lighting", keywords: ["capiz shell lantern", "lanterns", "lighting"], price: 500, product_id: "49968623", category: "Lighting" },
                { name: "Vintage-Style Wicker Picnic Basket", normalized: "vintage style wicker picnic basket", keywords: ["wicker basket", "picnic basket", "basketry", "picnic essentials"], price: 750, product_id: "829028322", category: "Picnic Essentials" },
                { name: "Sunflower-Themed Paper Lanterns", normalized: "sunflower themed paper lanterns", keywords: ["paper lanterns", "lanterns", "lighting", "sunflower"], price: 500, product_id: "465752516", category: "Lighting" },
                { name: "Natural Bamboo Back Scratcher Tool", normalized: "natural bamboo back scratcher tool", keywords: ["back scratcher", "bamboo", "personal care", "tool"], price: 250, product_id: "7002258", category: "Personal Care" },
                { name: "Compartmented Wooden Platter", normalized: "compartmented wooden platter", keywords: ["wooden platter", "platter", "serving ware", "wood craft", "wood"], price: 350, product_id: "632141238", category: "Serving ware" },
                { name: "Exquisite Decorative Chopstick Set", normalized: "exquisite decorative chopstick set", keywords: ["chopstick set", "chopsticks", "dining accessories"], price: 400, product_id: "480234474", category: "Dining Accessories" },
                { name: "Refined Clam-Style Mantel Clock", normalized: "refined clam style mantel clock", keywords: ["mantel clock", "clocks", "clock", "home decor", "time"], price: 350, product_id: "365894078", category: "Clocks" },
                { name: "Wooden Serving Tray with Handle", normalized: "wooden serving tray with handle", keywords: ["serving tray", "tray", "serving ware", "wood", "wood craft"], price: 400, product_id: "385747453", category: "Serving ware" },
                { name: "Wooden Condiment Serving Container", normalized: "wooden condiment serving container", keywords: ["condiment container", "serving ware", "wood", "wood craft", "container"], price: 450, product_id: "347517801", category: "Serving ware" },
                { name: "Mini Seashell Bag Hanging Decor", normalized: "mini seashell bag hanging decor", keywords: ["seashell decor", "wall decor", "hanging decor", "decor"], price: 400, product_id: "220553137", category: "Wall Decor" },
                { name: "Wooden Serving Spoon and Fork Set", normalized: "wooden serving spoon and fork set", keywords: ["serving spoon", "fork set", "serving ware", "wood", "wood craft"], price: 450, product_id: "231659643", category: "Serving ware" },
                { name: "Handcrafted Wooden Cups Set", normalized: "handcrafted wooden cups set", keywords: ["wooden cups", "serving ware", "cups", "wood", "wood craft"], price: 180, product_id: "93794801", category: "Serving ware" },
                { name: "Philippine Jeepney Toy Car", normalized: "philippine jeepney toy car", keywords: ["jeepney", "toy car", "toys"], price: 500, product_id: "320195862", category: "Toys" },
                { name: "Wooden Mortar and Pestle Set", normalized: "wooden mortar and pestle set", keywords: ["mortar and pestle", "serving ware", "wood", "wood craft"], price: 300, product_id: "590197729", category: "Serving ware" },
                { name: "Capiz Shell Coasters Filipino Designs", normalized: "capiz shell coasters filipino designs", keywords: ["capiz shell coasters", "souvenirs"], price: 300, product_id: "725952186", category: "Souvenirs" },
                { name: "Elegant Shell Chandelier Pendants", normalized: "elegant shell chandelier pendants", keywords: ["shell chandelier", "lighting", "chandelier"], price: 1500, product_id: "649934950", category: "Lighting" },
                { name: "Organic Coconut Shell Spoon", normalized: "organic coconut shell spoon", keywords: ["coconut shell spoon", "serving ware", "spoon"], price: 300, product_id: "506687386", category: "Serving ware" },
                { name: "Charming Kitchen Wall Decor", normalized: "charming kitchen wall decor", keywords: ["kitchen wall decor", "home decor", "wall decor"], price: 350, product_id: "174067199", category: "Home Decor" },
                { name: "Handwoven Rattan Table Lamp", normalized: "handwoven rattan table lamp", keywords: ["rattan table lamp", "lighting", "lamp", "rattan"], price: 700, product_id: "138879626", category: "Lighting" },
                { name: "Eco-Friendly Colorful Woven Bag", normalized: "eco friendly colorful woven bag", keywords: ["woven bag", "bag", "eco friendly"], price: 350, product_id: "111387388", category: "Bag" },
                { name: "Seashell Tissue Box Cover", normalized: "seashell tissue box cover", keywords: ["seashell tissue box", "tissue box covers"], price: 400, product_id: "774439342", category: "Tissue Box Covers" },
                { name: "Capiz Shell Tissue Box Cover", normalized: "capiz shell tissue box cover", keywords: ["capiz shell tissue box", "tissue box covers"], price: 400, product_id: "90424263", category: "Tissue Box Covers" },
                { name: "Handcrafted Woven Bamboo Placemats", normalized: "handcrafted woven bamboo placemats", keywords: ["bamboo placemats", "serving ware", "placemats", "bamboo"], price: 300, product_id: "976886705", category: "Serving ware" },
                { name: "Luxurious Elegant Folding Fan", normalized: "luxurious elegant folding fan", keywords: ["folding fan", "fan"], price: 167, product_id: "565462224", category: "Folding Fan" }
            ];
            const fuse = new Fuse(products, { keys: ["name", "normalized", "keywords"], threshold: 0.4 });
            const results = fuse.search(message);
            const matchedProducts = results.map(result => result.item);

            switch (state) {
                case "initial":
                    if (matchedProducts.length > 0) {
                        if (quantity) {
                            const matchedProduct = matchedProducts[0];
                            const totalPrice = matchedProduct.price * quantity;
                            speak(`You selected ${quantity} ${matchedProduct.name}(s), totaling ${totalPrice} pesos. Add to cart?`);
                            currentProduct = { ...matchedProduct, quantity };
                            state = "confirm-add-to-cart";
                        } else {
                            speak("I found these: " + matchedProducts.map(p => p.name).join(', ') + ". Please specify which one.");
                            currentMatchedProducts = matchedProducts;
                            state = "waiting-for-product-selection";
                        }
                    } else {
                        speak("I couldn't find that product. Please try again.");
                    }
                    break;
                case "waiting-for-product-selection":
                    const productMatch = matchedProducts[0];
                    if (productMatch) {
                        speak(`You selected "${productMatch.name}". How many would you like?`);
                        currentProduct = productMatch;
                        state = "waiting-for-quantity";
                    } else {
                        speak("Sorry, I didn't catch that. Please repeat the product name.");
                    }
                    break;
                case "waiting-for-quantity":
                    if (quantity) {
                        currentProduct.quantity = quantity;
                        const totalPrice = currentProduct.price * quantity;
                        speak(`${quantity} ${currentProduct.name}(s), totaling ${totalPrice} pesos. Confirm to add to cart?`);
                        state = "confirm-add-to-cart";
                    } else {
                        speak("Please specify a quantity.");
                    }
                    break;
                case "confirm-add-to-cart":
                    if (message.includes("yes") || message.includes("confirm")) {
                        speak(`Adding ${currentProduct.quantity} ${currentProduct.name}(s) to your cart.`);
                        addToCartVoice(currentProduct.product_id, currentProduct.quantity);
                        state = "initial";
                    } else if (message.includes("no") || message.includes("cancel")) {
                        speak("Order canceled. How else can I help?");
                        state = "initial";
                    } else {
                        speak("Please say 'yes' to confirm or 'no' to cancel.");
                    }
                    break;
            }
        }

        function detectQuantity(message) {
            const numberWords = { "one": 1, "two": 2, "to": 2, "three": 3, "four": 4, "for": 4, "five": 5, "six": 6, "seven": 7, "eight": 8, "nine": 9, "ten": 10 };
            const numericMatch = message.match(/\b\d+\b/);
            if (numericMatch) return parseInt(numericMatch[0]);
            const wordMatch = Object.keys(numberWords).find(word => message.startsWith(word + " "));
            if (wordMatch) return numberWords[wordMatch];
            return null;
        }

        function addToCartVoice(productId, quantity) {
            fetch('fetch_product_details.php?product_id=' + productId)
                .then(response => response.json())
                .then(details => {
                    if (details.error) {
                        speak("Sorry, I couldn't get the details for that product.");
                        return;
                    }
                    fetch('add_to_cart.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            product_id: productId,
                            quantity: quantity,
                            size: details.sizes.length > 0 ? details.sizes[0] : '',
                            color: details.colors.length > 0 ? details.colors[0] : '',
                            shape: details.shapes.length > 0 ? details.shapes[0] : '',
                            buyer_id: <?php echo json_encode($_SESSION['unique_id']); ?>,
                            stocks: details.stocks || 0,
                            shipping_fee: details.shipping_fee || 0
                        })
                    }).then(response => response.json())
                    .then(cartData => {
                        if (cartData.success) {
                            speak("It's added to your cart.");
                            toggleVoiceModal(false);
                        } else {
                            speak("Sorry, there was an error: " + cartData.message);
                        }
                    }).catch(error => speak("A technical error occurred."));
                }).catch(error => speak("An error occurred while getting product details."));
        }

        function toggleVoiceModal(show) {
            voiceModal.style.display = show ? "block" : "none";
            if (!show) recognition.stop();
        }

        function logConversation(sender, message) {
            const messageElement = document.createElement("div");
            messageElement.classList.add("message");
            const avatarSrc = "../uploaded_profile/CH-logo.png";
            if (sender === "Buyer") {
                messageElement.innerHTML = `<div class="buyer-container"><img class="avatar" src="${avatarSrc}" alt="Buyer Avatar" /><span class="buyer-message">${message}</span></div>`;
            } else {
                messageElement.innerHTML = `<div class="assistant-container"><img class="avatar" src="${avatarSrc}" alt="Assistant Avatar" /><span class="assistant-message">${message}</span></div>`;
            }
            conversationLog.appendChild(messageElement);
            conversationLog.scrollTop = conversationLog.scrollHeight;
        }

        closeModalVoice.addEventListener('click', () => toggleVoiceModal(false));
    })();

    // --- PRODUCT DETAIL MODAL LOGIC (Self-contained module) ---
    (function(){
        const modal = document.getElementById("product-info-modal");
        const closeModal = document.querySelector(".close-modal");
        const form = document.getElementById("product-options-form");

        function modalClose() {
            modal.style.display = "none";
        }

        closeModal.addEventListener('click', modalClose);
        window.addEventListener('click', (event) => {
            if (event.target === modal) modalClose();
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            data.quantity = 1;
            data.buyer_id = <?php echo json_encode($_SESSION['unique_id']); ?>;

            fetch('add_to_cart.php', {
                method: 'POST',
                body: JSON.stringify(data),
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product added to cart!');
                    modalClose();
                } else {
                    alert('Failed to add product to cart: ' + data.message);
                }
            })
            .catch(error => console.error('Error adding product to cart:', error));
        });

        function fetchProductDetails(productId) {
            fetch('fetch_product_details.php?product_id=' + productId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    document.getElementById("product-name").innerText = data.product_name;
                    document.getElementById("product-image").src = `../../uploaded_img/${data.image}`;
                    document.getElementById("product-description").innerText = data.seller_description;
                    document.getElementById("product-shipping-fee").innerText = data.shipping_fee;
                    document.getElementById("product_unique_id").value = data.product_unique_id;
                    document.getElementById("product_name_input").value = data.product_name;
                    document.getElementById("product_price_input").value = data.price;
                    document.getElementById("product_image_input").value = data.image;
                    document.getElementById("product_stocks_input").value = data.stocks;
                    document.getElementById("shipping_fee_input").value = data.shipping_fee;

                    populateDropdown('size', data.sizes);
                    populateDropdown('color', data.colors);
                    populateDropdown('shape', data.shapes);
                    modal.style.display = "block";
                })
                .catch(error => console.error('Error fetching product details:', error));
        }

        function populateDropdown(dropdownId, options) {
            const dropdown = document.getElementById(dropdownId);
            const dropdownContainer = dropdown.parentElement;
            dropdown.innerHTML = '';
            if (options && options.length > 0) {
                options.forEach(option => {
                    const optElement = document.createElement('option');
                    optElement.value = option.trim();
                    optElement.textContent = option.trim();
                    dropdown.appendChild(optElement);
                });
                dropdownContainer.style.display = 'block';
            } else {
                dropdownContainer.style.display = 'none';
            }
        }
        
        // This is the crucial part: The "View Details" button listener is now inside the same
        // self-contained module as the `fetchProductDetails` function it needs to call.
        document.querySelectorAll('.view-details-button').forEach(button => {
            button.addEventListener('click', function () {
                const productId = this.getAttribute('data-product-id');
                // This call is now guaranteed to work in all browsers because it's in the same scope.
                fetchProductDetails(productId);
            });
        });
    })();
});
</script>

</body>
</html>