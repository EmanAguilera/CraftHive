<?php
session_start();
@include '../connection/connect.php';

// Check if the buyer is logged in
if (!isset($_SESSION['unique_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company</title>

    <!-- External CSS and Font Links -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../../css/design.css">

    <style>
        /* Your existing styles remain here */
        @import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Poppins:wght@400;500;600;700&display=swap');
      :root {
          --dark: #5D4037;
          --blue: rgb(133, 92, 77);
          --grey-light: #EFEBE9;
          --main-bg: #E0E0E0;
          --light: #FAF0E6;
          --yellow: #FFD54F;
          --orange: #FF8A65;
          --white: #FFFFFF;
          --grey: #EAE3D2;
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
         background-color: var(--light);
         box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      }

      .recent-transactions table th, .recent-transactions table td {
         padding: 12px;
         text-align: center;
         font-size: 14px;
      }

      .recent-transactions table th {
         background-color: var(--blue);
         color: white;
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

      /* Sidebar */
      #sidebar {
         background-color: #5D4037;
      }

      #sidebar a {
         color: pink;
      }

      #sidebar .active a {
         background-color: #3E2723;
      }

  
      /* Product Styles */
     

      body {
         font-family: Arial, sans-serif;
         line-height: 1.6;
      }

      .container {
         max-width: 1200px;
         margin: 20px auto;
         padding: 20px;
      }

      .products {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
         gap: 20px;
      }

      .box {
         background: #fff;
         border: 1px solid #ddd;
         border-radius: 10px;
         box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
         overflow: hidden;
         text-align: center;
         padding: 15px;
         transition: transform 0.3s, box-shadow 0.3s;
      }

      .box:hover {
         transform: translateY(-10px);
         box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
      }

      .box img {
         width: 100%;
         height: 300px;
         object-fit: cover;
         border-radius: 8px;
         margin-bottom: 15px;
      }

      .box h3 {
         font-size: 1.5rem;
         color: #333;
         margin-bottom: 10px;
      }

      .box p {
         font-size: 1rem;
         color: #555;
         margin-bottom: 8px;
      }

      .box .btn {
         display: inline-block;
         padding: 10px 15px;
         font-size: 1rem;
         color: #fff;
         background: #007bff;
         border: none;
         border-radius: 5px;
         cursor: pointer;
         text-decoration: none;
         transition: background 0.3s;
      }

      .box .btn:hover {
         background: #0056b3;
      }
        
        /* New Voice Assistant Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 10px;
        }

        .buyer-container,
        .assistant-container {
            display: flex;
            align-items: start;
            gap: 10px;
        }

        .avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }

        .message-text {
            padding: 8px 12px;
            border-radius: 15px;
            max-width: 80%;
        }

        .buyer-message .message-text {
            background-color: var(--blue);
            color: white;
        }

        .assistant-message .message-text {
            background-color: var(--grey-light);
        }

        #conversationLog {
            max-height: 400px;
            overflow-y: auto;
            padding: 15px;
        }

        .voice-btn {
            background-color: var(--blue);
            color: var(--white);
            padding: 10px 15px;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            cursor: pointer;
            margin-left: 20px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .voice-btn.listening {
            background-color: var(--yellow);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        

        /* Your other existing styles */
        
    </style>
</head>
<body>

<?php
// Your existing PHP message handling code
if (isset($message)) {
    foreach ($message as $message) {
        echo '<div class="message"><span>'.$message.'</span> 
        <i class="fas fa-times" onclick="this.parentElement.style.display = `none`;"></i></div>';
    }
}
?>

<!-- SIDEBAR -->

   <!-- SIDEBAR -->
   <section id="sidebar">
		<a href="#" class="brand">
			<i class='bx bxs-smile'></i>
			<span class="text">CraftHive</span>
		</a>
		<ul class="side-menu top">
        <li class="active">
				<a href="company.php">
					<i class='bx bxs-dashboard' ></i>
					<span class="text">Dashboard</span>
				</a>
			</li>
			<li>
				<a href="order.php">
					<i class='bx bxs-dashboard' ></i>
					<span class="text">Orders</span>
				</a>
			</li>
			<li>
				<a href="cart.php">
					<i class='bx bxs-shopping-bag-alt' ></i>
					<span class="text">Cart</span>
				</a>
			</li>
			<li>
				<a href="checkout.php">
					<i class='bx bxs-doughnut-chart' ></i>
					<span class="text">CheckOut </span>
				</a>
			</li>
			
		</ul>
		<ul class="side-menu">
			<li>
				<a href="#">
					<i class='bx bxs-cog' ></i>
					<span class="text">Settings</span>
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
        <i class='bx bx-menu'></i>
        <a href="#" class="nav-link">Categories</a>
        <form action="#">
            <div class="form-input">
                <input type="search" id="searchInput" placeholder="Search...">
                <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                <button type="button" id="voiceSearchButton" class="voice-btn">
                    <i class='bx bx-microphone'></i>
                </button>
            </div>
        </form>

        <!-- Voice Modal -->
        <div id="voiceModal" class="modal">
            <div class="modal-content">
                <div class="VA">
                    <span class="close">&times;</span>
                    <h2 style="color: #5C2E0A;">Voice Assistant</h2>
                </div>
                <div id="conversationLog"></div>
            </div>
        </div>

        
      <input type="checkbox" id="switch-mode" hidden>
      <label for="switch-mode" class="switch-mode"></label>
      <a href="#" class="notification">
         <i class='bx bxs-bell'></i>
         <span class="num">8</span>
      </a>
      <a href="#" class="profile">
         <img src="img/logo.png">
      </a>
   </nav>
     </section>   
    </nav>

    
   <!-- MAIN -->
    <br>
<main style="display: flex; margin-left: 315px; margin-top:15px; ">
   <div class="head-title">
      <div class="left">
         <h1 style="color:#5D4037;">Dashboard</h1>
         <ul class="breadcrumb" style="list-style: none; padding: 0; margin: 10px 0; display: inline-flex; justify-content: center; align-items: center;">
            <li style="margin: 0 5px;">
               <a href="company.php" style="text-decoration: none;  font-size: 16px; color: #AAAAAA;">Company</a>
            </li>
            <li style="font-size: 16px; color: #888;">
               <i class='bx bx-chevron-right'></i>
            </li>
            <li style="margin: 0 5px;">
               <a class="active" href="company.php" style="text-decoration: none; color: #333; font-size: 16px;  color: #5D4037;">Dashboard</a>
            </li>
         </ul>
      </div>
   </div>
</main>


      <div class="container">
         <section class="products">
            <?php
            // Fetch the required columns only
            $select_companys = mysqli_query($conn, "SELECT company_name, location, phone, payment, policy, image FROM `description`");
            
            if ($select_companys && mysqli_num_rows($select_companys) > 0) {
               while ($fetch_company = mysqli_fetch_assoc($select_companys)) {
            ?>
            <div class="box">
               <img src="../../uploaded_c_img/<?php echo htmlspecialchars($fetch_company['image']); ?>" alt="Company Image">
               <h3><?php echo htmlspecialchars($fetch_company['company_name']); ?></h3>
               <p><strong>Location:</strong> <?php echo htmlspecialchars($fetch_company['location']); ?></p>
               <p><strong>Phone:</strong> <?php echo htmlspecialchars($fetch_company['phone']); ?></p>
               <p><strong>Payment:</strong> <?php echo htmlspecialchars($fetch_company['payment']); ?></p>
               <p><strong>Policy:</strong> <?php echo htmlspecialchars($fetch_company['policy']); ?></p>
               <form action="company_product.php" method="post">
                  <input type="hidden" name="company_name" value="<?php echo htmlspecialchars($fetch_company['company_name']); ?>">
                  <input type="submit" class="btn" value="View Products">
               </form>
            </div>
            <?php
               }
            } else {
               echo "<p>No companies found.</p>";
            }
            ?>
         </section>
      </div>
   </main>
</section>
    

<script>
// Voice Assistant State Management
const VoiceAssistantState = {
    IDLE: 'idle',
    LISTENING: 'listening',
    PROCESSING: 'processing',
    SPEAKING: 'speaking',
    ERROR: 'error'
};

let assistantState = {
    current: VoiceAssistantState.IDLE,
    lastCommand: null,
    context: {},
    
    setState(newState) {
        this.current = newState;
        this.updateUI();
    },

    updateUI() {
        const voiceButton = document.getElementById('voiceSearchButton');
        if (this.current === VoiceAssistantState.LISTENING) {
            voiceButton.classList.add('listening');
        } else {
            voiceButton.classList.remove('listening');
        }
    }
};

// Function to search products
function searchProducts(searchTerm) {
    const productBoxes = document.querySelectorAll('.box');
    let found = false;
    
    productBoxes.forEach(box => {
        const productName = box.querySelector('h3').textContent.toLowerCase();
        const price = box.querySelector('.price').textContent.toLowerCase();
        
        if (productName.includes(searchTerm.toLowerCase()) || price.includes(searchTerm.toLowerCase())) {
            box.style.display = 'block';
            found = true;
            // Scroll to the first matching product
            if (!document.querySelector('.box[style="display: block;"]')) {
                box.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } else {
            box.style.display = 'none';
        }
    });
    
    if (!found) {
        speak(`I couldn't find any products matching "${searchTerm}"`);
    }
}

// Function to add product to cart
function addToCart(productName) {
    const productBoxes = document.querySelectorAll('.box');
    let found = false;
    
    productBoxes.forEach(box => {
        const name = box.querySelector('h3').textContent.toLowerCase();
        if (name.includes(productName.toLowerCase())) {
            const addToCartButton = box.querySelector('input[name="add_to_cart"]');
            if (addToCartButton) {
                addToCartButton.click();
                found = true;
                speak(`Adding ${name} to your cart`);
            }
        }
    });
    
    if (!found) {
        speak(`I couldn't find the product "${productName}" to add to cart`);
    }
}

// Enhanced Command Patterns
const COMMANDS = {
    SEARCH: {
        patterns: [/search for|find|look for|show me/i, /where is|locate|spot/i],
        handler: (command) => {
            const searchTerm = command.replace(/^.*?(search for|find|look for|show me|where is|locate|spot)/i, '').trim();
            searchProducts(searchTerm);
            return searchTerm;
        }
    },
    CART: {
        patterns: [/add to cart|buy|purchase/i, /get|order/i],
        handler: (command) => {
            const item = command.replace(/^.*?(add to cart|buy|purchase|get|order)/i, '').trim();
            return item;
        }
    },
    VIEW_CART: {
        patterns: [/show cart|view cart|what's in my cart|what is in my cart|check cart/i],
        handler: () => {
            speak("Opening your shopping cart");
            window.location.href = 'cart.php';
            return null;
        }
    },
    ORDER_STATUS: {
        patterns: [/order status|my orders|check order|view orders/i],
        handler: () => {
            speak("Taking you to your orders page");
            window.location.href = 'order.php';
            return null;
        }
    },
    HELP: {
        patterns: [/help|what can you do|commands/i],
        handler: () => {
            return null;
        }
    },
    CATEGORY: {
        patterns: [/show category|browse category|what categories/i],
        handler: () => {
            return null;
        }
    },
    PRICE: {
        patterns: [/how much|price of|cost of/i],
        handler: (command) => {
            const item = command.replace(/^.*?(how much|price of|cost of)/i, '').trim();
            return item;
        }
    },
    NAVIGATION: {
        patterns: [/go to|open|navigate to/i],
        handler: (command) => {
            const destination = command.replace(/^.*?(go to|open|navigate to)/i, '').trim();
            return destination;
        }
    },
    CHECKOUT: {
        patterns: [/checkout|payment|proceed to pay|pay now|place order/i],
        handler: () => {
            speak("Taking you to checkout");
            window.location.href = 'checkout.php';
            return null;
        }
    }
};

// Voice Assistant Configuration
const VOICE_CONFIG = {
    rate: 1,
    pitch: 1,
    volume: 1,
    lang: 'en-US'
};

let recognition;
let isListening = false;
let hasIntroduced = false;

function initVoiceAssistant() {
    if (!('webkitSpeechRecognition' in window)) {
        alert('Voice recognition is not supported in this browser. Please use Chrome.');
        return;
    }

    recognition = new webkitSpeechRecognition();
    recognition.continuous = true;
    recognition.interimResults = false;
    recognition.lang = VOICE_CONFIG.lang;

    setupRecognitionHandlers();
}

function setupRecognitionHandlers() {
    recognition.onstart = () => {
        isListening = true;
        assistantState.setState(VoiceAssistantState.LISTENING);
        updateUI('Listening...', 'assistant');
    };

    recognition.onend = handleRecognitionEnd;
    recognition.onresult = handleRecognitionResult;
    recognition.onerror = handleRecognitionError;
}

function handleRecognitionEnd() {
    isListening = false;
    assistantState.setState(VoiceAssistantState.IDLE);
}

function handleRecognitionError(event) {
    const errorMessages = {
        'network': 'Please check your internet connection',
        'no-speech': 'No speech was detected. Please try again',
        'not-allowed': 'Microphone access is required for voice commands',
        'default': 'I encountered an error. Please try again'
    };
    
    console.error('Speech recognition error:', event.error);
    const message = errorMessages[event.error] || errorMessages.default;
    speak(message);
    updateUI('Error: ' + message, 'assistant');
    assistantState.setState(VoiceAssistantState.ERROR);
}

function handleRecognitionResult(event) {
    const last = event.results.length - 1;
    const command = event.results[last][0].transcript.trim().toLowerCase();
    
    updateUI(command, 'buyer');
    assistantState.setState(VoiceAssistantState.PROCESSING);
    processCommand(command);
}

function processCommand(command) {
    assistantState.lastCommand = command;

    // Check each command type
    for (const [commandType, commandConfig] of Object.entries(COMMANDS)) {
        for (const pattern of commandConfig.patterns) {
            if (pattern.test(command)) {
                const param = commandConfig.handler(command);
                handleCommandExecution(commandType, param);
                return;
            }
        }
    }

    // Default response if no command matches
    speak("I'm not sure how to help with that. Try asking for help to see what I can do!");
}

function handleCommandExecution(commandType, param) {
    switch (commandType) {
        case 'SEARCH':
            if (param) {
                document.getElementById('searchInput').value = param;
                searchProducts(param);
            }
            break;
            
        case 'CART':
            if (param) {
                addToCart(param);
            } else {
                speak("Please specify which product you'd like to add to your cart");
            }
            break;
            
        case 'HELP':
            const helpMessage = `
                I can help you with:
                - Searching for products
                - Adding items to cart
                - Checking prices
                - Navigating categories
                - Processing checkout
                Just speak naturally and tell me what you need!
            `;
            speak(helpMessage);
            break;
            
        case 'NAVIGATION':
            if (param) {
                handleNavigation(param);
            }
            break;
            
        case 'PRICE':
            if (param) {
                speak(`Checking price for ${param}`);
                searchProducts(param);
            }
            break;
            
        case 'CHECKOUT':
            speak("Taking you to checkout");
            window.location.href = 'checkout.php';
            break;
    }
}

function speak(text) {
    assistantState.setState(VoiceAssistantState.SPEAKING);
    const utterance = new SpeechSynthesisUtterance(text);
    Object.assign(utterance, VOICE_CONFIG);
    
    utterance.onend = () => {
        assistantState.setState(VoiceAssistantState.IDLE);
    };
    
    speechSynthesis.speak(utterance);
    updateUI(text, 'assistant');
}

function updateUI(message, sender) {
    const conversationLog = document.getElementById('conversationLog');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}-message`;
    
    const containerDiv = document.createElement('div');
    containerDiv.className = `${sender}-container`;
    
    const avatar = document.createElement('img');
    avatar.className = 'avatar';
    avatar.src = sender === 'buyer' ? 'path/to/user-avatar.png' : 'path/to/assistant-avatar.png';
    avatar.alt = `${sender} avatar`;
    
    const messageText = document.createElement('div');
    messageText.className = 'message-text';
    messageText.textContent = message;
    
    containerDiv.appendChild(avatar);
    containerDiv.appendChild(messageText);
    messageDiv.appendChild(containerDiv);
    conversationLog.appendChild(messageDiv);
    conversationLog.scrollTop = conversationLog.scrollHeight;
}

function handleNavigation(destination) {
    const pages = {
        'cart': 'cart.php',
        'checkout': 'checkout.php',
        'products': 'products.php',
        'purchases': 'view.php'
    };

    const page = Object.entries(pages).find(([key]) => 
        destination.includes(key)
    );

    if (page) {
        speak(`Navigating to ${page[0]} page`);
        window.location.href = page[1];
    } else {
        speak("I'm not sure which page you want to visit. Please try again.");
    }
}

function toggleModal(show) {
    const modal = document.getElementById('voiceModal');
    modal.style.display = show ? 'block' : 'none';
    
    if (!show && isListening) {
        recognition.stop();
    }
}

function wishMe() {
    const day = new Date();
    const hour = day.getHours();
    const greeting =
        hour >= 0 && hour < 12 ? "Good morning!" :
        hour >= 12 && hour < 17 ? "Good afternoon!" :
        "Good evening!";
    
    const introMessage = `${greeting} I'm Craftra, your voice assistant. How can I help you today?`;
    speak(introMessage);
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    initVoiceAssistant();
    
    // Voice button click handler
    document.getElementById('voiceSearchButton').addEventListener('click', function() {
        if (!recognition) {
            initVoiceAssistant();
            return;
        }
        
        if (!isListening) {
            toggleModal(true);
            if (!hasIntroduced) {
                wishMe();
                hasIntroduced = true;
            }
            recognition.start();
        } else {
            recognition.stop();
            toggleModal(false);
        }
    });

    // Modal close handler
    document.getElementsByClassName('close')[0].onclick = () => toggleModal(false);
    
    // Close modal when clicking outside
    window.onclick = (event) => {
        if (event.target === document.getElementById('voiceModal')) {
            toggleModal(false);
        }
    };
});
</script>
<script src="js/script-.js"></script>
</body>
</html>