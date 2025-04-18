<?php
session_start();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the email from the form (this is the recipient)
    $to_email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    // Validate email
    if (!empty($to_email) && filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
        // Fixed sender email (make sure this is correct)
        $from_email = "kushmehta124@gmail.com";
        
        // Email content
        $subject = "Welcome to ShopEase - 20% Off Your First Order";
        $message = "Thank you for subscribing to ShopEase newsletter!\n\n";
        $message .= "As a welcome gift, please use code WELCOME20 at checkout to get 20% off your first order.\n\n";
        $message .= "Happy Shopping!\n";
        $message .= "The ShopEase Team";
        
        // Email headers with properly formatted From field
        $headers = "From: ShopEase <{$from_email}>\r\n";
        $headers .= "Reply-To: {$from_email}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // Send email to the user-provided address
        $check = mail($to_email, $subject, $message, $headers);
        
        if ($check) {
            $_SESSION['success_message'] = "Thanks for subscribing! Check your email for your 20% off code.";
        } else {
            $_SESSION['error_message'] = "There was a problem sending the email. Please try again.";
        }
    } else {
        $_SESSION['error_message'] = "Please enter a valid email address.";
    }
    
    // Redirect back to the index page
    header("Location: index.php");
    exit;
}
?>