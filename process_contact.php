<?php
session_start();
require_once 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['contact_message'] = "Please enter a valid email address.";
        header("Location: contact.php");
        exit();
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $subject, $message, $ip_address);
    
    if ($stmt->execute()) {
        $_SESSION['contact_message'] = "Thank you for your message! We'll get back to you soon.";
    } else {
        $_SESSION['contact_message'] = "There was an error submitting your message. Please try again later.";
    }
    
    $stmt->close();
    header("Location: contact.php");
    exit();
} else {
    header("Location: contact.php");
    exit();
}
?>