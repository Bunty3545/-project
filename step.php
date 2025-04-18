<?php
session_start();

// Handle checkout step updates
if(isset($_POST['step'])) {
    $allowed_steps = ['cart', 'shipping', 'payment', 'confirmation'];
    $step = $_POST['step'];
    
    if(in_array($step, $allowed_steps)) {
        // Update session checkout step
        $_SESSION['checkout_step'] = $step;
        
        // If it's the payment step and payment method is provided
        if($step == 'payment' && isset($_POST['payment_method'])) {
            $_SESSION['payment_method'] = $_POST['payment_method'];
        }
        
        // Return success response
        echo json_encode(['success' => true, 'message' => 'Checkout step updated to ' . $step]);
    } else {
        // Return error if invalid step
        echo json_encode(['success' => false, 'message' => 'Invalid checkout step']);
    }
    exit;
}

// Default response for direct access
echo json_encode(['success' => false, 'message' => 'No action specified']);
?>
