<?php
session_start();
if (isset($_SESSION['unique_id'])) {
    include_once "config.php";
    if (isset($_GET['logout_id']) && !empty($_GET['logout_id']) && is_numeric($_GET['logout_id'])) {
        $logout_id = mysqli_real_escape_string($conn, $_GET['logout_id']);
        $status = "Offline now";

        // Use a prepared statement to prevent SQL injection
        $sql = "UPDATE buyer SET status = ? WHERE unique_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $logout_id);

        if ($stmt->execute()) {
            session_unset();
            session_destroy();
            header("location: ../login.php");
        }
         else {
            echo "Error updating status" . $conn->error;
           // header("location: ../users.php"); // Or handle the error as needed
        }
        $stmt->close();
    } else {
        header("location: ../users.php");
    }
} else {
    header("location: ../login.php");
}
?>