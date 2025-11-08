<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer classes manually
require __DIR__ . '/../../phpmailer/src/Exception.php';
require __DIR__ . '/../../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../../phpmailer/src/SMTP.php';

$conn = mysqli_connect("localhost", "root", "", "local");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to send email
function sendEmail($subject, $from_name, $email, $content, $client = '') {
    $mail = new PHPMailer(true);
    $mail->CharSet = "utf-8";
    $mail->IsSMTP();
    $mail->SMTPAuth = true;
    if ($client == "" || $client == "gmail") {
        $mail->Host = 'smtp.gmail.com';
        $mail->Username = 'emanaguilera123@gmail.com';
        $mail->Password = 'vwbfxfgfsjzzfwhb';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls'; // Add this line
        $mail->setFrom('emanaguilera123@gmail.com', $from_name);
    }

    if (is_array($email)) {
        foreach ($email as $mail_address) {
            $mail->AddAddress($mail_address);
        }
    } else {
        $mail->AddAddress($email);
    }

    $mail->Subject = $subject;
    $mail->IsHTML(true);
    $mail->Body = $content;

    // Disable SSL certificate verification
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    try {
        $mail->send();
        return true; // Email sent successfully
    } catch (Exception $e) {
        return false; // Failed to send email
    }
}
?>