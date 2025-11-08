<?php
session_start();
include_once "../../connection/connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    if (!empty($fname) && !empty($lname) && !empty($email) && !empty($phone) && !empty($password)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $sql = mysqli_query($conn, "SELECT email FROM admin WHERE email = '{$email}'");

            if (mysqli_num_rows($sql) > 0) {
                echo "$email - This email address already exists!";
            } else {
                if (isset($_FILES['img'])) {
                    $img_name = $_FILES['img']['name']; 
                    $tmp_name = $_FILES['img']['tmp_name']; 
                    
                    $img_explode = explode('.', $img_name);
                    $img_ext = strtolower(end($img_explode)); 
                    
                    $extensions = ['png', 'jpeg', 'jpg']; 
                    
                    if (in_array($img_ext, $extensions)) {
                        $time = time(); 
                        $new_img_name = $time . $img_name;

                        // Check if the directory exists and create it if it doesn't
                        $upload_dir = "images/";
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }

                        // Move the uploaded file to the directory
                        if (move_uploaded_file($tmp_name, $upload_dir . $new_img_name)) {
                            $status = "Active"; 
                            $unique_id = rand(1, 999999999); // Adjusted to fit within INT range
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                            $sql2 = mysqli_query($conn, "INSERT INTO admin(unique_id, fname, lname, phone, email, password, img, status) VALUES('{$unique_id}', '{$fname}', '{$lname}', '{$phone}', '{$email}', '{$hashed_password}', '{$new_img_name}', '{$status}')");

                            if ($sql2) {
                               $sql3 = mysqli_query($conn, "SELECT * FROM admin WHERE email = '{$email}'");
                               if (mysqli_num_rows($sql3) > 0) {
                                  $row = mysqli_fetch_assoc($sql3);
                                  $_SESSION['unique_id'] = $row['unique_id'];
                                  echo "success";
                               }
                            } else {
                               echo "Something went wrong!";
                            }             
                        } else {
                            echo "Failed to upload image.";
                        }
                    } else {
                        echo "Invalid file format! Use jpeg, jpg, or png.";
                    }
                } else {
                    echo "Please select an Image file!";
                }
            }
        } else {
            echo "$email - This is not a valid email address!";
        }
    } else {
        echo "All input fields are required!";
    }
} else {
    echo "Invalid request!";
}
?>