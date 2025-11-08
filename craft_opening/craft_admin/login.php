<?php
session_start();
include_once "../connection/connect.php";

$message = "";
$type = "";

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        // Using prepared statements to prevent SQL injection
        $stmt = mysqli_prepare($conn, "SELECT * FROM admin WHERE email=?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 0) {
            $type = "danger";
            $message = "Incorrect username or password.";
        } else {
            $userData = mysqli_fetch_assoc($result);

            // Verify the password
            if (password_verify($password, $userData['password'])) {
                // Password is correct, log the user in using buyer_generate_id
                $_SESSION['unique_id'] = $userData['unique_id']; // Store generate ID in session
                $_SESSION['admin_email'] = $email; // Store email in session
                $_SESSION['admin_name'] = $userData['fname']; // Store name in session

                $type = "success";
                $message = "Login successful. Redirecting...";
            } else {
                $type = "danger";
                $message = "Incorrect username or password.";
            }
        }
    } else {
        $type = "danger";
        $message = "All input fields are required!";
    }

    echo json_encode(['type' => $type, 'message' => $message]);
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CraftLogin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/login_register.css">
    <style>

.header {
    background-color: #5C2E0A;
    color: #FEDFB1;
    padding: 10px 20px;
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 2000;
}

.header .flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header .logo {
    font-size: 24px;
    color: #FEDFB1;
    text-decoration: none;
    font-family: 'Poppins', sans-serif;
}

.header .navbar {
    display: flex;
    gap: 30px;
    
}

.header .navbar a {
    color: #FFF3DC;
    text-decoration: none;
    font-size: 20px;
    transition: color 0.3s;
}

.header .navbar a:hover {
    color: #EFDEAE; /* Tomato color for hover effect */
}

body {
    padding-top: 60px;
}

.alert-success {
    border: 1px solid #3B5D3A; /* A vibrant dark green for the border */
    background-color: #A8D8A0; /* A soft, muted green for the background */
    color: #2C3E50; /* A darker color for the text for good contrast */
}
.alert-error {
    border: 1px solid #A24A3D; /* A vibrant red-brown for the border */
    background-color: #F2B2B1; /* A soft, muted pinkish-red for the background */
    color: #4A2C2C; /* A darker brownish color for the text for better contrast */
}

.error-txt, .success-txt {
    padding: 10px;
    border-radius: 5px;
    margin-top: 10px;
    font-weight: 600;
    font-family: 'Georgia', serif; /* Classic serif font for a vintage feel */
}

.error-txt {
    background-color: #FEDFB1; /* Soft vintage yellow background */
    color: #792A03; /* Dark vintage red text */
}

.success-txt {
    background-color: #C69B68; /* Vintage beige background */
    color: #5C2E0A; /* Deep brownish-green for text */
}

input:-webkit-autofill {
    background-color: #FEDFB1 !important; /* Your desired background */
    color: #5C2E0A !important; /* Your desired text color */
    transition: background-color 5000s ease-in-out 0s; /* Fixes background overwrite */
}

        </style>

</head>

<body class="flex items-center justify-center min-h-screen bg-gray-100">
    <?php include '../header/layer.php'; ?>
    <div class="container mx-auto px-4 relative z-10">
        <section class="form login form-container relative bg-white p-10 rounded-lg shadow-lg w-full max-w-md mx-auto">
            <div class="background-overlay" style="background-image: url('../../img/img4.jpg');"></div>
            <h1 class="text-2xl sm:text-3xl md:text-4xl craft-h1 font-bold text-center mb-2">CraftLogin</h1>
            <form id="login-form" class="space-y-4">
                <div id="alert" class="px-4 py-3 rounded text-center relative hidden" role="alert"></div>
                <input type="email" name="email" placeholder="Enter Your Email" class="w-full p-3 border fill-out rounded">
                <div class="password-container relative">
                    <input type="password" name="password" placeholder="Enter Your Password" class="w-full p-3 border fill-out rounded">
                    <i class="password-toggle fas fa-eye-slash absolute right-4 top-5 cursor-pointer"></i>
                </div>
                <button type="submit" name="submit" class="custom-button">Login</button>
            </form>
            <div class="register-forget text-center mt-4">
                Don't have an account? <a href="register.php" class="ls-in hover:underline">Sign Up</a>
            </div>
</div>


        </section>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/show_hide.js"></script>
    <script>
        $(document).ready(function() {
            $('#login-form').on('submit', function(event) {
                event.preventDefault();
                var formData = $(this).serialize();

                $.ajax({
                    url: '', // Add the correct URL to handle the login request
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        var alertClass = response.type === 'success' ? 'alert-success' : 'alert-error';

                        $('#alert').removeClass('hidden alert-success alert-error')
                                   .addClass(alertClass)
                                   .text(response.message)
                                   .show();

                        if (response.type === 'success') {
                            setTimeout(function() {
                                window.location.href = "dashboard.php";
                            }, 2000);
                        }

                        setTimeout(function() {
                            $('#alert').hide();
                        }, 60000);
                    },
                    error: function(xhr, status, error) {
                        $('#alert').removeClass('hidden alert-success')
                                   .addClass('alert-error')
                                   .text('An error occurred. Please try again.')
                                   .show();
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php