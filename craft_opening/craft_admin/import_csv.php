<?php
session_start();
@include '../connection/connect.php';

// Check if the seller is logged in
if (!isset($_SESSION['unique_id'])) {
    header('Location: login.php');
    exit;
}

// Handle the file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];

    // Open the uploaded file
    if (($handle = fopen($file, 'r')) !== false) {
        // Read the first row (headers) and skip it
        $headers = fgetcsv($handle);

        // Loop through the rest of the file and insert into the database
        while (($data = fgetcsv($handle)) !== false) {
            // Map data to variables
            $company_id = $data[0];
            $fname = $data[1];
            $lname = $data[2];
            $email = $data[3];
            $company_name = $data[4];
            $phone = $data[5];
            $status = $data[6];
            $subscription_plan = $data[7];
            $price = $data[8];
            $total_sellers = $data[9];
            $start_date = $data[10];
            $expiry_date = $data[11];
            $img = $data[12];

            // Insert the data into the database
            $stmt = $conn->prepare("
                INSERT INTO company (company_id, fname, lname, email, company_name, phone, status, subscription_plan, price, total_sellers, start_date, expiry_date, img) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                fname = VALUES(fname), lname = VALUES(lname), email = VALUES(email), company_name = VALUES(company_name), 
                phone = VALUES(phone), status = VALUES(status), subscription_plan = VALUES(subscription_plan), 
                price = VALUES(price), total_sellers = VALUES(total_sellers), start_date = VALUES(start_date), 
                expiry_date = VALUES(expiry_date), img = VALUES(img)
            ");
            $stmt->bind_param(
                'isssssssdssss',
                $company_id,
                $fname,
                $lname,
                $email,
                $company_name,
                $phone,
                $status,
                $subscription_plan,
                $price,
                $total_sellers,
                $start_date,
                $expiry_date,
                $img
            );

            if (!$stmt->execute()) {
                echo "Error inserting data: " . $stmt->error;
            }
        }

        fclose($handle);
        echo "CSV imported successfully.";
    } else {
        echo "Error opening the file.";
    }
    $conn->close();
    exit;
}
?>
