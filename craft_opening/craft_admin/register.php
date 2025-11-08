<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/login_register.css">
    <style>


.error-txt, .success-txt {
    padding: 10px;
    border-radius: 5px;
    margin-top: 10px;
    font-weight: 400;
}

.error-txt {
    border: 1px solid #792A03; /* Dark vintage red for the border */
    background-color: #C69B68; /* A soft, vintage beige for the background */
    color: #5C2E0A; /* A deep vintage red for text */
}

.success-txt {
    border: 1px solid #5C2E0A; /* Deep vintage brown for the border */
    background-color: #EE7E1A; /* A warm vintage orange with a hint of red */
    color: #FFF3DC; /* A muted, vintage green for text */
}

/* Style for visible password (show-password) */
input[type="tel"] {
width: 100%; /* Full width for visible password */
padding: 15px; /* Inner padding */
margin-bottom: 10px; /* Space below each input */
border: 2px solid #792A03; /* Vintage border color */
border-radius: 8px; /* Softer corners */
box-sizing: border-box; /* Include padding in width */
background-color: #FEDFB1; /* Light background for warmth */
color: #5C2E0A; /* Dark text for readability */
text-transform: none; /* Normal text transformation */
transition: border-color 0.3s, background-color 0.3s; /* Smooth transition */
}

input[type="tel"]::placeholder {
color: #8E5923; /* Vintage placeholder color */
opacity: 0.8; /* Slightly transparent for a softer look */
}

input:-webkit-autofill {
    background-color: #FEDFB1 !important; /* Your desired background */
    color: #5C2E0A !important; /* Your desired text color */
    transition: background-color 5000s ease-in-out 0s; /* Fixes background overwrite */
}



        </style>
    
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">

<div class="background-overlay" style="background-image: url('../../img/img4.jpg');"></div>
<?php include '../header/layer.php'; ?>
<div class="container mx-auto px-4 relative z-10">
    <section class="form signup form-container relative bg-white p-10 rounded-lg shadow-lg w-full max-w-md mx-auto">
    <h1 class="text-2xl sm:text-3xl md:text-4xl craft-h1 font-bold text-center mb-6">CraftRegister</h1>
        <form id="signupForm" method="post" enctype="multipart/form-data" class="space-y-4">
            <div class="error-txt bg-red-100 border border-red-400 text-red-700 text-center px-4 py-3 rounded relative hidden" role="alert"> </div>
            <div class="form-grid grid grid-cols-2 gap-4">
                <input type="text" name="fname" placeholder="First Name" required class="w-full p-3 border fill-out border-gray-300 rounded">
                <input type="text" name="lname" placeholder="Last Name" required class="w-full p-3 border fill-out border-gray-300 rounded">
            </div>
            <input type="tel" name="phone" placeholder="Phone Number" required class="w-full p-3 border fill-out border-gray-300 rounded">
                <input type="email" name="email" placeholder="Enter Your Email" required class="w-full p-3 border fill-out border-gray-300 rounded">
            <div class="password-container relative">
                <input type="password" name="password" placeholder="Enter Your Password" required class="w-full p-3 border fill-out border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#AC7952]">
                <i class="password-toggle fas fa-eye-slash absolute right-3 top-4 cursor-pointer"></i>
            </div>
            <div class="custom-file relative">
                <input type="file" class="absolute inset-0 opacity-0 w-full h-full cursor-pointer" id="img" name="img" required onchange="updateFileName()">
                <label id="fileLabel" class="block w-full p-3 border border-gray-300 rounded bg-white" for="img">Select Upload Pic</label>
            </div>
            <button type="submit" name="submit" class="custom-button">Sign Up</button>

            <div class="register-forget text-center mt-4">
                Already have an account? <a href="login.php" class="ls-in hover:underline">Sign In</a>
            </div>
</div>
        </form>
    </section>
</div>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="../js/show_hide.js"></script>
<script src="js/register.js"></script>
</body>
</html>