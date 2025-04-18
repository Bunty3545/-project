<?php
session_start();
$loggedin = false;
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
} else {
    $loggedin = true;
    
    // Check if user_id and username exist in session
    if(!isset($_SESSION['user_id'])) {
        error_log("User ID not found in session for user: " . ($_SESSION['username'] ?? 'unknown'));
        $_SESSION['user_id'] = 0; // Guest user ID as fallback
    }
    
    if(!isset($_SESSION['username'])) {
        error_log("Username not found in session for user ID: " . $_SESSION['user_id']);
        $_SESSION['username'] = 'guest'; // Default username as fallback
    }
    
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
}

// Include database connection
require_once 'dbconnect.php';

// Handle shipping form submission
if(isset($_POST['update_shipping'])) {
    // Store shipping info in session
    $_SESSION['shipping_address'] = $_POST['shipping_address'] ?? '';
    $_SESSION['shipping_city'] = $_POST['shipping_city'] ?? '';
    $_SESSION['shipping_state'] = $_POST['shipping_state'] ?? '';
    $_SESSION['shipping_zip'] = $_POST['shipping_zip'] ?? '';
    $_SESSION['shipping_phone'] = $_POST['shipping_phone'] ?? '';
    $_SESSION['user_email'] = $_POST['user_email'] ?? '';
    
    // Set success message
    
    
    // Set the checkout step to payment
    $_SESSION['checkout_step'] = 'payment';
    
    // Redirect to cart page
    header("Location: cart.php");
    exit;
}

// Handle checkout form submission
if(isset($_POST['process_checkout'])) {
    // Check if shipping info is provided
    if(empty($_SESSION['shipping_address']) || empty($_SESSION['shipping_city']) || 
       empty($_SESSION['shipping_state']) || empty($_SESSION['shipping_zip'])) {
        $_SESSION['error_message'] = "Please provide shipping information before checkout.";
        header("Location: cart.php");
        exit;
    }
    
    // Redirect to bill.php for checkout processing
    header("Location: bill.php");
    exit;
}

// Handle payment submission
if(isset($_POST['process_payment'])) {
    // Set the checkout step to confirmation
    $_SESSION['checkout_step'] = 'confirmation';
    
    // Redirect to bill.php for order creation
    header("Location: bill.php");
    exit;
}

// Handle quantity updates
if(isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['quantity'];
    
    if($quantity > 0) {
        $update_sql = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("iii", $quantity, $cart_id, $user_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // If quantity is 0 or negative, remove the item
        $delete_sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $cart_id, $user_id);
        $delete_stmt->execute();
        $delete_stmt->close();
    }
    
    // Redirect to avoid form resubmission
    header("Location: cart.php");
    exit;
}

// Handle item removal
if(isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];
    
    $delete_sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $cart_id, $user_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    
    // Redirect to avoid form resubmission
    header("Location: cart.php");
    exit;
}

// Handle cart reset post-checkout
if(isset($_POST['reset_cart'])) {
    // Clear cart table
    $clear_sql = "DELETE FROM cart WHERE user_id = ?";
    $clear_stmt = $conn->prepare($clear_sql);
    $clear_stmt->bind_param("i", $user_id);
    $clear_stmt->execute();
    $clear_stmt->close();
    
    // Reset session cart count
    $_SESSION['cart_count'] = 0;
    
    // Return success response
    echo json_encode(['success' => true, 'message' => 'Cart cleared successfully']);
    exit;
}

// Handle shipping reset
if(isset($_POST['reset_shipping'])) {
    // Clear shipping info in session
    $_SESSION['shipping_address'] = '';
    $_SESSION['shipping_city'] = '';
    $_SESSION['shipping_state'] = '';
    $_SESSION['shipping_zip'] = '';
    $_SESSION['shipping_phone'] = '';
    
    // Reset checkout step
    $_SESSION['checkout_step'] = 'cart';
    
    // Return success response
    echo json_encode(['success' => true, 'message' => 'Shipping details reset successfully']);
    exit;
}

// Get cart items
$cart_items = [];
$total_price = 0;

// Modified query to handle missing products table
$cart_sql = "SELECT * FROM cart WHERE user_id = ? ORDER BY date_added DESC";
$cart_stmt = $conn->prepare($cart_sql);
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$result = $cart_stmt->get_result();

while($item = $result->fetch_assoc()) {
    $cart_items[] = $item;
    $total_price += $item['product_price'] * $item['quantity'];
}
$cart_stmt->close();

// Update session cart count
$_SESSION['cart_count'] = count($cart_items);
$cart_count = $_SESSION['cart_count'];

// Clear cart
if(isset($_GET['clear_cart'])) {
    $clear_sql = "DELETE FROM cart WHERE user_id = ?";
    $clear_stmt = $conn->prepare($clear_sql);
    $clear_stmt->bind_param("i", $user_id);
    $clear_stmt->execute();
    $clear_stmt->close();
    
    // Update session cart count
    $_SESSION['cart_count'] = 0;
    
    // Redirect to avoid form resubmission
    header("Location: cart.php");
    exit;
}

// Apply coupon discount if any - simplified without coupon table
$discount_amount = 0;
$coupon_code = '';
$discount_type = '';

// Calculate subtotal after discount
$subtotal_after_discount = $total_price - $discount_amount;

// Calculate taxes (assuming 8% tax rate)
$tax_rate = 0.08;
$tax_amount = $subtotal_after_discount * $tax_rate;

// Calculate shipping cost (based on cart total)
$shipping_cost = 0;
if($subtotal_after_discount > 0) {
    if($subtotal_after_discount < 50) {
        $shipping_cost = 9.99;
    } else if($subtotal_after_discount < 100) {
        $shipping_cost = 5.99;
    } else {
        $shipping_cost = 0; // Free shipping for orders over $100
    }
}

// Calculate final total
$total_with_shipping = $subtotal_after_discount + $tax_amount + $shipping_cost;

// Get user information if available
$user_info = [
    'name' => $username,
    'email' => $_SESSION['user_email'] ?? '',
    'address' => $_SESSION['shipping_address'] ?? '',
    'city' => $_SESSION['shipping_city'] ?? '',
    'state' => $_SESSION['shipping_state'] ?? '',
    'zip' => $_SESSION['shipping_zip'] ?? '',
    'phone' => $_SESSION['shipping_phone'] ?? '',
    'country' => 'USA'
];

// Try to get additional user info from database if not available in session
if(empty($user_info['email']) || empty($user_info['address'])) {
    try {
        // Get form S.No. based on username
        $form_sno = null;
        $get_sno_sql = "SELECT `S.No.` FROM form WHERE Username = ?";
        $get_sno_stmt = $conn->prepare($get_sno_sql);
        $get_sno_stmt->bind_param("s", $username);
        $get_sno_stmt->execute();
        $sno_result = $get_sno_stmt->get_result();
        
        if ($sno_result->num_rows > 0) {
            $sno_row = $sno_result->fetch_assoc();
            $form_sno = $sno_row['S.No.'];
            
            // Try to get user details from the form table
            $user_sql = "SELECT * FROM form WHERE `S.No.` = ?";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bind_param("i", $form_sno);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            
            if ($user_result->num_rows > 0) {
                $user_data = $user_result->fetch_assoc();
                
                // Check for email
                if(empty($user_info['email']) && isset($user_data['Email'])) {
                    $user_info['email'] = $user_data['Email'];
                    $_SESSION['user_email'] = $user_data['Email'];
                }
                
                // Check for address
                if(empty($user_info['address']) && isset($user_data['Address'])) {
                    $user_info['address'] = $user_data['Address'];
                    $_SESSION['shipping_address'] = $user_data['Address'];
                } elseif(empty($user_info['address']) && isset($user_data['address'])) {
                    $user_info['address'] = $user_data['address'];
                    $_SESSION['shipping_address'] = $user_data['address'];
                }
                
                // Check for phone
                if(empty($user_info['phone']) && isset($user_data['Phone'])) {
                    $user_info['phone'] = $user_data['Phone'];
                    $_SESSION['shipping_phone'] = $user_data['Phone'];
                } elseif(empty($user_info['phone']) && isset($user_data['phone'])) {
                    $user_info['phone'] = $user_data['phone'];
                    $_SESSION['shipping_phone'] = $user_data['phone'];
                }
                
                // Check for city, state, zip
                if(empty($user_info['city']) && isset($user_data['City'])) {
                    $user_info['city'] = $user_data['City'];
                    $_SESSION['shipping_city'] = $user_data['City'];
                }
                if(empty($user_info['state']) && isset($user_data['State'])) {
                    $user_info['state'] = $user_data['State'];
                    $_SESSION['shipping_state'] = $user_data['State'];
                }
                if(empty($user_info['zip']) && isset($user_data['Zip'])) {
                    $user_info['zip'] = $user_data['Zip'];
                    $_SESSION['shipping_zip'] = $user_data['Zip'];
                }
            }
            $user_stmt->close();
        }
        $get_sno_stmt->close();
    } catch (Exception $e) {
        error_log("Error retrieving user data: " . $e->getMessage());
    }
}

// Set default checkout step if not set
if(!isset($_SESSION['checkout_step'])) {
    $_SESSION['checkout_step'] = 'cart';
}

// Simplify recommendation mechanism for now since products table may not exist yet
$recommended_products = [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart - ShopEase</title>
    <script src="https://cdn.tailwindcss.com/3.3.5"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Add QuaggaJS for barcode scanning -->
    <script src="https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#1E40AF',
                        accent: '#10B981',
                        danger: '#EF4444',
                        warning: '#F59E0B',
                        dark: '#111827',
                        light: '#F3F4F6'
                    }
                }
            }
        }
    </script>
    <style>
        /* Cart container layout */
        .cart-container {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        @media (min-width: 1024px) {
            .cart-container {
                flex-direction: row;
            }
            
            .cart-items-section {
                flex: 2;
            }
            
            .order-summary-section {
                flex: 1;
            }
        }
        
        /* Cart item styling */
        .cart-item {
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
            overflow: hidden;
        }
        
        .cart-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .product-image-container {
            aspect-ratio: 1/1;
            background-color: #f9fafb;
        }
        
        .product-image {
            transition: transform 0.3s ease;
            object-fit: contain;
            mix-blend-mode: multiply;
        }
        
        .product-image:hover {
            transform: scale(1.05);
        }
        
        /* Quantity input styling */
        .quantity-input {
            width: 60px;
            text-align: center;
            -moz-appearance: textfield;
            border-radius: 0;
            border-width: 1px 0;
        }
        
        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        /* Button animations */
        .btn-animated {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .btn-animated:after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-animated:hover:after {
            width: 300%;
            height: 300%;
        }
        
        /* Scanner modal styles */
        .scanner-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            overflow-y: auto;
        }
        
        .scanner-content {
            background-color: white;
            border-radius: 1rem;
            padding: 2rem;
            width: 90%;
            max-width: 600px;
            text-align: center;
            margin: 2rem auto;
        }
        
        /* Improved scanner container for better image display */
        #scanner-container {
            position: relative;
            width: 100%;
            height: auto;
            min-height: 300px;
            max-height: 450px;
            margin: 20px auto;
            border: 2px solid #3B82F6;
            border-radius: 10px;
            overflow: hidden;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        #scanner-container img {
            max-width: 100%;
            max-height: 400px;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
        }
        
        /* Summary styles */
        .order-summary-section {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .order-summary-section h3 {
            position: relative;
            padding-bottom: 12px;
        }
        
        .order-summary-section h3:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #3B82F6 0%, #10B981 100%);
            border-radius: 3px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed #e5e7eb;
        }
        
        .summary-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        
        .total-item {
            padding-top: 16px;
            margin-top: 8px;
            border-top: 2px solid #e5e7eb;
        }
        
        /* Enhanced Coupon Input */
        .coupon-container {
            display: flex;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .coupon-container:focus-within {
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        
        .coupon-input {
            flex: 1;
            padding: 12px 16px;
            border: none;
            outline: none;
            font-size: 15px;
        }
        
        .coupon-button {
            padding: 0 20px;
            background-color: #3B82F6;
            color: white;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .coupon-button:hover {
            background-color: #2563eb;
        }
        
        /* Shipping Form Styles - IMPROVED */
        .shipping-form-container {
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .shipping-form {
            width: 100%;
        }
        
        .form-field-container {
            position: relative;
            margin-bottom: 1rem;
        }
        
        .form-field-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            width: 20px;
            text-align: center;
        }
        
        .form-field {
            width: 100%;
            padding: 10px 10px 10px 42px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 16px;
            line-height: 1.5;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-field:focus {
            border-color: #3b82f6;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }
        
        .form-field-help {
            margin-top: 4px;
            font-size: 12px;
            color: #6b7280;
        }
        
        /* Scan line animation */
        @keyframes scanAnimation {
            0% { top: 0; }
            100% { top: 100%; }
        }
        
        .scan-line {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #10b981;
            z-index: 10;
            animation: scanAnimation 2s linear infinite;
        }
        
        /* Payment Form Styles */
        .payment-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }
        
        .payment-method-option {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .payment-method-option.active {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
        
        .payment-method-option i {
            font-size: 1.25rem;
            margin-right: 10px;
        }
        
        .payment-method-forms > div {
            display: none;
        }
        
        .payment-method-forms > div.active {
            display: block;
        }
        
        /* Checkout steps */
        .checkout-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .checkout-steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #e5e7eb;
            z-index: 1;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        
        .step-circle {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #e5e7eb;
            color: #6b7280;
            border-radius: 50%;
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }
        
        .step.active .step-circle {
            background-color: #3B82F6;
            color: white;
        }
        
        .step.completed .step-circle {
            background-color: #10B981;
            color: white;
        }
        
        .step-label {
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .step.active .step-label {
            color: #3B82F6;
            font-weight: 600;
        }
        
        .step.completed .step-label {
            color: #10B981;
        }
        
        /* Section visibility */
        .checkout-section {
            display: none;
        }
        
        .checkout-section.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Location button */
        .location-button {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            color: #4b5563;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .location-button:hover {
            background-color: #e5e7eb;
            color: #1f2937;
        }
        
        .location-button i {
            margin-right: 8px;
            color: #3B82F6;
        }
        
        /* Continue buttons */
        .continue-button {
            display: block;
            width: 100%;
            padding: 12px 20px;
            background-color: #3B82F6;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            margin-top: 20px;
        }
        
        .continue-button:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        
        .back-button {
            display: inline-block;
            padding: 10px 16px;
            background-color: #f3f4f6;
            color: #4b5563;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            margin-right: 10px;
        }
        
        .back-button:hover {
            background-color: #e5e7eb;
            color: #1f2937;
        }
        
        /* Shipping progress bar */
        .shipping-progress {
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .shipping-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #3B82F6 0%, #10B981 100%);
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        
        .shipping-milestone {
            color: #6b7280;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .shipping-milestone.reached {
            color: #10B981;
            font-weight: 600;
        }
        
        /* Confirmation button styling */
        .confirmation-button {
            display: block;
            width: 100%;
            padding: 14px 24px;
            background-color: #10B981;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);
        }
        
        .confirmation-button:hover {
            background-color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(16, 185, 129, 0.3);
        }
        
        .confirmation-button i {
            margin-right: 8px;
        }
        
        /* Continue shopping button - only shown after confirmation */
        .continue-shopping-button {
            display: none;
            width: 100%;
            padding: 12px 20px;
            background-color: #3B82F6;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            margin-top: 20px;
        }
        
        .continue-shopping-button.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'nav.php'; ?>

    <div class="container px-4 py-8 mx-auto max-w-7xl">
        <!-- Page Header -->
        <div class="flex items-center mb-8 space-x-4">
            <div class="p-3 rounded-full bg-primary/10">
                <i class="text-2xl text-primary fas fa-shopping-cart"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-800">Your Shopping Cart</h2>
            <span class="px-3 py-1 ml-auto text-sm font-medium rounded-full bg-primary/10 text-primary cart-count-badge">
                <?= $cart_count ?> <?= $cart_count === 1 ? 'item' : 'items' ?>
            </span>
        </div>
        
        <!-- Checkout Steps -->
        <div class="mb-8 checkout-steps">
            <div class="step <?= ($_SESSION['checkout_step'] == 'cart' ? 'active' : ($_SESSION['checkout_step'] == 'shipping' || $_SESSION['checkout_step'] == 'payment' || $_SESSION['checkout_step'] == 'confirmation' ? 'completed' : '')) ?>" id="cartStep">
                <div class="step-circle">
                    <?php if($_SESSION['checkout_step'] == 'shipping' || $_SESSION['checkout_step'] == 'payment' || $_SESSION['checkout_step'] == 'confirmation'): ?>
                        <i class="fas fa-check"></i>
                        1
                    <?php endif; ?>
                </div>
                <div class="step-label">Cart</div>
            </div>
            <div class="step <?= ($_SESSION['checkout_step'] == 'shipping' ? 'active' : ($_SESSION['checkout_step'] == 'payment' || $_SESSION['checkout_step'] == 'confirmation' ? 'completed' : '')) ?>" id="shippingStep">
                <div class="step-circle">
                    <?php if($_SESSION['checkout_step'] == 'payment' || $_SESSION['checkout_step'] == 'confirmation'): ?>
                        <i class="fas fa-check"></i>
                    <?php else: ?>
                        2
                    <?php endif; ?>
                </div>
                <div class="step-label">Shipping</div>
            </div>
            <div class="step <?= ($_SESSION['checkout_step'] == 'payment' ? 'active' : ($_SESSION['checkout_step'] == 'confirmation' ? 'completed' : '')) ?>" id="paymentStep">
                <div class="step-circle">
                    <?php if($_SESSION['checkout_step'] == 'confirmation'): ?>
                        <i class="fas fa-check"></i>
                    <?php else: ?>
                        3
                    <?php endif; ?>
                </div>
                <div class="step-label">Payment</div>
            </div>
            <div class="step <?= ($_SESSION['checkout_step'] == 'confirmation' ? 'active' : '') ?>" id="confirmStep">
                <div class="step-circle">4</div>
                <div class="step-label">Confirmation</div>
            </div>
        </div>
        
        <?php if(isset($_SESSION['error_message'])): ?>
            <div class="p-4 mb-6 text-red-800 bg-red-100 border-l-4 border-red-500 rounded-md cart-animation">
                <div class="flex items-center">
                    <i class="mr-3 text-xl fas fa-exclamation-circle"></i>
                    <p><?= $_SESSION['error_message'] ?></p>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(empty($cart_items)): ?>
            <!-- Empty Cart View -->
            <div class="p-8 my-4 text-center bg-white rounded-lg shadow-md">
                <img src="https://img.icons8.com/bubbles/200/000000/shopping-cart.png" alt="Empty Cart" class="mx-auto mb-6">
                <p class="mb-6 text-xl text-gray-600">Your cart is empty.</p>
                <a href="clothing.php" class="inline-block px-6 py-3 text-white transition-all duration-300 rounded-full btn-animated bg-primary hover:bg-secondary">
                    <i class="mr-2 fas fa-shopping-bag"></i> Start Shopping
                </a>
            </div>
        <?php else: ?>
            <!-- Cart Section -->
            <div id="cartSection" class="checkout-section <?= ($_SESSION['checkout_step'] == 'cart' ? 'active' : '') ?>">
                <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
                    <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-800">Shopping Cart</h3>
                        <a href="?clear_cart=1" class="flex items-center px-4 py-2 text-sm text-white transition-all duration-300 rounded-full btn-animated bg-danger hover:bg-red-600" 
                           onclick="return confirm('Are you sure you want to clear your cart?')">
                            <i class="mr-2 fas fa-trash"></i> Clear Cart
                        </a>
                    </div>
                    
                    <div class="hidden pb-4 mb-6 border-b border-gray-200 md:flex">
                        <div class="w-2/5 font-semibold text-gray-700">Product</div>
                        <div class="w-1/5 font-semibold text-center text-gray-700">Price</div>
                        <div class="w-1/5 font-semibold text-center text-gray-700">Quantity</div>
                        <div class="w-1/5 font-semibold text-right text-gray-700">Total</div>
                    </div>
                    
                    <div class="space-y-6">
                        <?php foreach($cart_items as $item): ?>
                            <div class="p-4 bg-white cart-item">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                                    <!-- Product Image and Info -->
                                    <div class="md:col-span-5">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-20 h-20 overflow-hidden rounded-md product-image-container">
                                                <img class="object-cover w-full h-full product-image" 
                                                     src="<?= htmlspecialchars($item['product_image']) ?>" 
                                                     alt="<?= htmlspecialchars($item['product_name']) ?>">
                                            </div>
                                            <div class="ml-4">
                                                <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($item['product_name']) ?></h3>
                                                <p class="text-sm text-gray-600"><?= htmlspecialchars($item['product_category']) ?></p>
                                                <?php if(isset($item['username']) && $item['username'] !== 'guest'): ?>
                                                    <p class="mt-1 text-xs text-gray-500">Added by: <?= htmlspecialchars($item['username']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Price -->
                                    <div class="flex items-center md:col-span-2 md:justify-center">
                                        <div>
                                            <span class="font-medium text-gray-600 md:hidden">Price: </span>
                                            <span class="text-lg font-semibold text-gray-800">$<?= number_format($item['product_price'], 2) ?></span>
                                        </div>
                                    </div>
                                    
                                    <!-- Quantity -->
                                    <div class="flex items-center md:col-span-3 md:justify-center">
                                        <form method="post" action="" class="flex items-center space-x-2 quantity-form">
                                            <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                            <div class="flex items-center border border-gray-300 rounded-lg quantity-selector">
                                                <button type="button" class="p-2 text-gray-500 quantity-btn decrement-quantity">
                                                    <i class="text-sm fas fa-minus"></i>
                                                </button>
                                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" 
                                                       class="w-12 py-2 text-center border-0 quantity-input focus:outline-none focus:ring-0">
                                                <button type="button" class="p-2 text-gray-500 quantity-btn increment-quantity">
                                                    <i class="text-sm fas fa-plus"></i>
                                                </button>
                                            </div>
                                            <button type="submit" name="update_quantity" class="p-2 text-sm rounded-full text-primary hover:bg-primary/10" title="Update Quantity">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <!-- Total and Remove -->
                                    <div class="flex items-center justify-end md:col-span-2">
                                        <div class="flex flex-col items-end space-y-2">
                                            <!-- Item total price -->
                                            <div class="text-lg font-bold text-gray-900 whitespace-nowrap">
                                                $<?= number_format($item['product_price'] * $item['quantity'], 2) ?>
                                            </div>
                                            
                                            <!-- Remove button -->
                                            <a href="?remove=<?= $item['id'] ?>" 
                                               class="flex items-center px-3 py-1 text-sm text-red-500 transition-colors duration-200 rounded-full hover:bg-red-100"
                                               onclick="return confirm('Are you sure you want to remove this item?')">
                                                <i class="mr-1 text-sm fas fa-trash"></i>
                                                <span>Remove</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Continue Shopping Button -->
                    <div class="flex justify-between mt-8">
                        <a href="clothing.php" class="flex items-center px-6 py-3 text-gray-700 transition-all duration-300 bg-gray-200 rounded-full btn-animated hover:bg-gray-300">
                            <i class="mr-2 fas fa-arrow-left"></i> Continue Shopping
                        </a>
                        <button type="button" class="flex items-center px-6 py-3 text-white transition-all duration-300 rounded-full btn-animated bg-primary hover:bg-secondary" onclick="goToShipping()">
                            Proceed to Shipping <i class="ml-2 fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Order Summary (Cart Step) -->
                <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
                    <h3 class="mb-4 text-xl font-semibold text-center text-gray-800">Order Summary</h3>
                    
                    <!-- Price Breakdown -->
                    <div class="p-4 mb-6 rounded-lg bg-gray-50">
                        <div class="mb-4 space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal (<?= $cart_count ?> items):</span>
                                <span class="font-medium">$<?= number_format($total_price, 2) ?></span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax (8%):</span>
                                <span class="font-medium">$<?= number_format($tax_amount, 2) ?></span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping:</span>
                                <span class="font-medium">
                                    <?php if($shipping_cost > 0): ?>
                                        $<?= number_format($shipping_cost, 2) ?>
                                    <?php else: ?>
                                        <span class="text-green-600">FREE</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            
                            <div class="flex justify-between pt-3 mt-3 text-lg font-bold border-t border-gray-200">
                                <span>Total:</span>
                                <span class="text-primary">$<?= number_format($total_with_shipping, 2) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Free Shipping Progress Bar (if applicable) -->
                    <?php if($subtotal_after_discount > 0 && $subtotal_after_discount < 100): ?>
                        <div class="p-4 mb-6 rounded-lg bg-gray-50">
                            <p class="mb-2 text-sm font-medium text-gray-700">
                                <?php
                                    $remaining_for_free_shipping = 100 - $subtotal_after_discount;
                                    $progress_percentage = min(100, ($subtotal_after_discount / 100) * 100);
                                ?>
                                Add <span class="font-bold text-primary">$<?= number_format($remaining_for_free_shipping, 2) ?></span> more to get <span class="font-bold text-green-600">FREE SHIPPING</span>
                            </p>
                            <div class="mt-3 shipping-progress">
                                <div class="shipping-progress-bar" style="width: <?= $progress_percentage ?>%"></div>
                            </div>
                            <div class="flex justify-between mt-2 text-xs">
                                <span class="shipping-milestone <?= $subtotal_after_discount >= 50 ? 'reached' : '' ?>">$50</span>
                                <span class="shipping-milestone <?= $subtotal_after_discount >= 100 ? 'reached' : '' ?>">$100</span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Coupon Code Section -->
                    <div class="mb-6">
                        <h4 class="mb-4 text-lg font-semibold text-gray-700">Apply Coupon Code</h4>
                        <form method="post" action="">
                            <div class="coupon-container">
                                <input type="text" name="coupon_code" placeholder="Enter coupon code" class="coupon-input">
                                <button type="submit" name="apply_coupon" class="coupon-button">
                                    Apply
                                </button>
                            </div>
                        </form>
                        <p class="mt-2 text-sm text-gray-500">Have a promo code? Enter it above.</p>
                    </div>
                </div>
            </div>
            
            <!-- Shipping Section -->
            <div id="shippingSection" class="checkout-section <?= ($_SESSION['checkout_step'] == 'shipping' ? 'active' : '') ?>">
                <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
                    <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-800">Shipping Information</h3>
                    </div>
                    
                    <div class="shipping-form-container">
                        <form method="post" action="" class="shipping-form">
                            <!-- Find My Location Button -->
                            <div class="mb-6">
                                <button type="button" id="findLocationBtn" class="w-full location-button">
                                    <i class="fas fa-map-marker-alt"></i> Find My Location
                                </button>
                                <p class="mt-2 text-xs text-gray-500">Click to auto-fill your address using your current location</p>
                            </div>
                            
                            <!-- Email Address -->
                            <div class="mb-4">
                                <label for="user_email" class="block mb-2 text-sm font-medium text-gray-700">Email Address</label>
                                <div class="form-field-container">
                                    <i class="form-field-icon fas fa-envelope"></i>
                                    <input type="email" id="user_email" name="user_email" class="form-field" 
                                        value="<?= htmlspecialchars($user_info['email']) ?>" 
                                        placeholder="email@example.com" required>
                                </div>
                                <p class="form-field-help">We'll send your order confirmation here</p>
                            </div>
                            
                            <!-- Address -->
                            <div class="mb-4">
                                <label for="shipping_address" class="block mb-2 text-sm font-medium text-gray-700">Address</label>
                                <div class="form-field-container">
                                    <i class="form-field-icon fas fa-home"></i>
                                    <input type="text" id="shipping_address" name="shipping_address" class="form-field"
                                        value="<?= htmlspecialchars($user_info['address']) ?>" 
                                        placeholder="Street address or P.O. Box" required>
                                </div>
                            </div>
                            
                            <!-- City and State -->
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="mb-4">
                                    <label for="shipping_city" class="block mb-2 text-sm font-medium text-gray-700">City</label>
                                    <div class="form-field-container">
                                        <i class="form-field-icon fas fa-city"></i>
                                        <input type="text" id="shipping_city" name="shipping_city" class="form-field"
                                            value="<?= htmlspecialchars($user_info['city']) ?>" 
                                            placeholder="City name" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="shipping_state" class="block mb-2 text-sm font-medium text-gray-700">State</label>
                                    <div class="form-field-container">
                                        <i class="form-field-icon fas fa-map-marker-alt"></i>
                                        <select id="shipping_state" name="shipping_state" class="form-field" required>
                                            <option value="" disabled <?= empty($user_info['state']) ? 'selected' : '' ?>>Select State</option>
                                            <?php
                                            $states = array(
                                                'AL'=>'Alabama', 'AK'=>'Alaska', 'AZ'=>'Arizona', 'AR'=>'Arkansas', 'CA'=>'California',
                                                'CO'=>'Colorado', 'CT'=>'Connecticut', 'DE'=>'Delaware', 'DC'=>'District of Columbia',
                                                'FL'=>'Florida', 'GA'=>'Georgia', 'HI'=>'Hawaii', 'ID'=>'Idaho', 'IL'=>'Illinois',
                                                'IN'=>'Indiana', 'IA'=>'Iowa', 'KS'=>'Kansas', 'KY'=>'Kentucky', 'LA'=>'Louisiana',
                                                'ME'=>'Maine', 'MD'=>'Maryland', 'MA'=>'Massachusetts', 'MI'=>'Michigan', 'MN'=>'Minnesota',
                                                'MS'=>'Mississippi', 'MO'=>'Missouri', 'MT'=>'Montana', 'NE'=>'Nebraska', 'NV'=>'Nevada',
                                                'NH'=>'New Hampshire', 'NJ'=>'New Jersey', 'NM'=>'New Mexico', 'NY'=>'New York',
                                                'NC'=>'North Carolina', 'ND'=>'North Dakota', 'OH'=>'Ohio', 'OK'=>'Oklahoma', 'OR'=>'Oregon',
                                                'PA'=>'Pennsylvania', 'RI'=>'Rhode Island', 'SC'=>'South Carolina', 'SD'=>'South Dakota',
                                                'TN'=>'Tennessee', 'TX'=>'Texas', 'UT'=>'Utah', 'VT'=>'Vermont', 'VA'=>'Virginia',
                                                'WA'=>'Washington', 'WV'=>'West Virginia', 'WI'=>'Wisconsin', 'WY'=>'Wyoming'
                                            );
                                            foreach($states as $code => $state) {
                                                $selected = ($code == $user_info['state']) ? 'selected' : '';
                                                echo "<option value=\"{$code}\" {$selected}>{$state}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ZIP and Phone -->
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="mb-4">
                                    <label for="shipping_zip" class="block mb-2 text-sm font-medium text-gray-700">ZIP Code</label>
                                    <div class="form-field-container">
                                        <i class="form-field-icon fas fa-map-pin"></i>
                                        <input type="text" id="shipping_zip" name="shipping_zip" class="form-field"
                                            value="<?= htmlspecialchars($user_info['zip']) ?>" 
                                            placeholder="123456" maxlength="10" pattern="[0-9]{5}(-[0-9]{4})?" required>
                                    </div>
                                    <p class="form-field-help">Format: 12345 or 12345-6789</p>
                                </div>
                                <div class="mb-4">
                                    <label for="shipping_phone" class="block mb-2 text-sm font-medium text-gray-700">Phone Number</label>
                                    <div class="form-field-container">
                                        <i class="form-field-icon fas fa-phone"></i>
                                        <input type="tel" id="shipping_phone" name="shipping_phone" class="form-field"
                                            value="<?= htmlspecialchars($user_info['phone']) ?>" 
                                            placeholder="(123) 456-7890" required>
                                    </div>
                                    <p class="form-field-help">For delivery questions only</p>
                                </div>
                            </div>
                            
                            <!-- Navigation Buttons -->
                            <div class="flex justify-between mt-8">
                                <button type="button" class="back-button" onclick="goToCart()">
                                    <i class="mr-2 fas fa-arrow-left"></i> Back to Cart
                                </button>
                                <button type="submit" name="update_shipping" class="continue-button">
                                    Continue to Payment <i class="ml-2 fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Order Summary (Shipping Step) -->
                <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
                    <h3 class="mb-4 text-xl font-semibold text-center text-gray-800">Order Summary</h3>
                    
                    <!-- Cart Items Preview -->
                    <div class="mb-4 space-y-3">
                        <?php foreach($cart_items as $item): ?>
                            <div class="flex items-center justify-between p-3 border-b border-gray-100">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 overflow-hidden rounded-md">
                                        <img class="object-cover w-full h-full" 
                                             src="<?= htmlspecialchars($item['product_image']) ?>" 
                                             alt="<?= htmlspecialchars($item['product_name']) ?>">
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($item['product_name']) ?></p>
                                        <p class="text-xs text-gray-500">Qty: <?= $item['quantity'] ?></p>
                                    </div>
                                </div>
                                <span class="font-medium text-gray-800">$<?= number_format($item['product_price'] * $item['quantity'], 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Price Breakdown -->
                    <div class="p-4 rounded-lg bg-gray-50">
                        <div class="mb-4 space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-medium">$<?= number_format($total_price, 2) ?></span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax (8%):</span>
                                <span class="font-medium">$<?= number_format($tax_amount, 2) ?></span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping:</span>
                                <span class="font-medium">
                                    <?php if($shipping_cost > 0): ?>
                                        $<?= number_format($shipping_cost, 2) ?>
                                    <?php else: ?>
                                        <span class="text-green-600">FREE</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            
                            <div class="flex justify-between pt-3 mt-3 text-lg font-bold border-t border-gray-200">
                                <span>Total:</span>
                                <span class="text-primary">$<?= number_format($total_with_shipping, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Section -->
            <div id="paymentSection" class="checkout-section <?= ($_SESSION['checkout_step'] == 'payment' ? 'active' : '') ?>">
                <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
                    <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-800">Payment Information</h3>
                    </div>
                    
                    <!-- Payment Methods -->
                    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
                        <div class="payment-method-option active" data-method="credit-card">
                            <div class="flex items-center">
                                <i class="fas fa-credit-card text-primary"></i>
                                <span class="ml-2">Credit Card</span>
                            </div>
                        </div>
                        <div class="payment-method-option" data-method="paypal">
                            <div class="flex items-center">
                                <i class="fab fa-paypal text-primary"></i>
                                <span class="ml-2">PayPal</span>
                            </div>
                        </div>
                        <div class="payment-method-option" data-method="apple-pay">
                            <div class="flex items-center">
                                <i class="fab fa-apple-pay text-primary"></i>
                                <span class="ml-2">Apple Pay</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Forms -->
                    <div class="mt-6 payment-method-forms">
                        <!-- Credit Card Form -->
                        <div id="credit-card-form" class="active">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="mb-4">
                                    <label for="card_name" class="block mb-2 text-sm font-medium text-gray-700">Name on Card</label>
                                    <div class="form-field-container">
                                        <i class="form-field-icon fas fa-user"></i>
                                        <input type="text" id="card_name" name="card_name" class="form-field" placeholder="John Smith" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="card_number" class="block mb-2 text-sm font-medium text-gray-700">Card Number</label>
                                    <div class="form-field-container">
                                        <i class="form-field-icon fas fa-credit-card"></i>
                                        <input type="text" id="card_number" name="card_number" class="form-field" placeholder="1234 5678 9012 3456" required>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="mb-4">
                                    <label for="expiry_date" class="block mb-2 text-sm font-medium text-gray-700">Expiry Date</label>
                                    <div class="form-field-container">
                                        <i class="form-field-icon fas fa-calendar"></i>
                                        <input type="text" id="expiry_date" name="expiry_date" class="form-field" placeholder="MM/YY" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="cvv" class="block mb-2 text-sm font-medium text-gray-700">CVV</label>
                                    <div class="form-field-container">
                                        <i class="form-field-icon fas fa-lock"></i>
                                        <input type="text" id="cvv" name="cvv" class="form-field" placeholder="123" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- PayPal Form -->
                        <div id="paypal-form">
                            <div class="p-6 text-center rounded-lg bg-gray-50">
                                <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_111x69.jpg" alt="PayPal" class="h-16 mx-auto mb-4">
                                <p class="mb-4 text-gray-700">You'll be redirected to PayPal to complete your payment securely.</p>
                                <div class="p-3 text-sm text-gray-600 border border-blue-100 rounded-md bg-blue-50">
                                    <p>Total: $<?= number_format($total_with_shipping, 2) ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Apple Pay Form -->
                        <div id="apple-pay-form">
                            <div class="p-6 text-center rounded-lg bg-gray-50">
                                <div class="flex items-center justify-center mb-4">
                                    <i class="text-4xl text-black fab fa-apple"></i>
                                    <span class="ml-2 text-2xl font-medium text-black">Pay</span>
                                </div>
                                <p class="mb-4 text-gray-700">Complete your purchase using Apple Pay.</p>
                                <div class="p-4 mb-4 text-white bg-black rounded-lg">
                                    <span class="block mb-2 text-sm">Apple Pay Payment</span>
                                    <span class="block text-lg font-bold">$<?= number_format($total_with_shipping, 2) ?></span>
                                    </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Billing Address Checkbox -->
                    <div class="mt-6 mb-6">
                        <div class="flex items-center">
                            <input type="checkbox" id="same_as_shipping" name="same_as_shipping" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                            <label for="same_as_shipping" class="block ml-2 text-sm text-gray-700">
                                Billing address same as shipping address
                            </label>
                        </div>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-8">
                        <button type="button" class="back-button" onclick="goToShipping()">
                            <i class="mr-2 fas fa-arrow-left"></i> Back to Shipping
                        </button>
                        <button type="button" id="complete-checkout-btn" class="continue-button">
                            Complete Order <i class="ml-2 fas fa-check"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Order Summary (Payment Step) -->
                <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
                    <h3 class="mb-4 text-xl font-semibold text-center text-gray-800">Order Summary</h3>
                    
                    <!-- Shipping Information Preview -->
                    <div class="p-4 mb-4 rounded-lg bg-gray-50">
                    <h4 class="mb-2 text-lg font-medium text-gray-700">Shipping Information</h4>
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                            <div>
                                <p class="text-sm text-gray-600">Address:</p>
                                <p class="text-sm font-medium" id="summary-address">
                                    <?= htmlspecialchars($user_info['address']) ?>
                                </p>
                                <p class="text-sm font-medium" id="summary-city-state-zip">
                                    <?= htmlspecialchars($user_info['city']) ?>, <?= htmlspecialchars($user_info['state']) ?> <?= htmlspecialchars($user_info['zip']) ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Contact:</p>
                                <p class="text-sm font-medium" id="summary-email">
                                    <?= htmlspecialchars($user_info['email']) ?>
                                </p>
                                <p class="text-sm font-medium" id="summary-phone">
                                    <?= htmlspecialchars($user_info['phone']) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cart Items Preview -->
                    <div class="mb-4 space-y-3">
                        <?php foreach($cart_items as $item): ?>
                            <div class="flex items-center justify-between p-3 border-b border-gray-100">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 overflow-hidden rounded-md">
                                        <img class="object-cover w-full h-full" 
                                             src="<?= htmlspecialchars($item['product_image']) ?>" 
                                             alt="<?= htmlspecialchars($item['product_name']) ?>">
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($item['product_name']) ?></p>
                                        <p class="text-xs text-gray-500">Qty: <?= $item['quantity'] ?></p>
                                    </div>
                                </div>
                                <span class="font-medium text-gray-800">$<?= number_format($item['product_price'] * $item['quantity'], 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Price Breakdown -->
                    <div class="p-4 rounded-lg bg-gray-50">
                        <div class="mb-4 space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-medium">$<?= number_format($total_price, 2) ?></span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax (8%):</span>
                                <span class="font-medium">$<?= number_format($tax_amount, 2) ?></span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping:</span>
                                <span class="font-medium">
                                    <?php if($shipping_cost > 0): ?>
                                        $<?= number_format($shipping_cost, 2) ?>
                                    <?php else: ?>
                                        <span class="text-green-600">FREE</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            
                            <div class="flex justify-between pt-3 mt-3 text-lg font-bold border-t border-gray-200">
                                <span>Total:</span>
                                <span class="text-primary">$<?= number_format($total_with_shipping, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Confirmation Section -->
            <div id="confirmationSection" class="checkout-section <?= ($_SESSION['checkout_step'] == 'confirmation' ? 'active' : '') ?>">
                <!-- This section will be populated by JavaScript when order is completed -->
            </div>

            <!-- Scanner Modal -->
            <div id="scannerModal" class="scanner-modal">
                <div class="scanner-content">
                    <h2 class="mb-4 text-2xl font-bold">Payment Verification</h2>
                    <p class="mb-6 text-gray-600">Please scan your payment code to complete your order.</p>
                    
                    <div id="scanner-container">
                        <!-- Scanner will be loaded here -->
                    </div>
                    
                    <div id="scanResult" class="hidden p-4 mt-6 text-center text-green-700 rounded-lg bg-green-50">
                        <i class="mr-2 text-2xl fas fa-check-circle"></i>
                        <span>Payment verification successful!</span>
                    </div>
                    
                    <div id="completePurchase" class="hidden mt-6">
                        <button onclick="completeOrder()" class="w-full px-4 py-3 text-white transition-all duration-300 bg-green-500 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                            <i class="mr-2 fas fa-check"></i> Complete Purchase
                        </button>
                    </div>
                    
                    <div class="flex justify-center mt-6">
                        <button id="closeScannerBtn" class="px-6 py-2 text-gray-700 transition-all duration-300 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none">
                            <i class="mr-2 fas fa-times"></i> Cancel
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="py-12 mt-12 text-white bg-gray-900">
        <div class="px-4 mx-auto max-w-7xl">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                <div>
                    <h3 class="flex items-center mb-4 text-xl font-bold">
                        <i class="mr-2 fas fa-shopping-bag text-primary"></i>
                        ShopEase
                    </h3>
                    <p class="mb-4 text-gray-400">Your one-stop shop for all your needs. Quality products at affordable prices.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="mb-4 text-lg font-semibold">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Shop</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-4 text-lg font-semibold">Customer Service</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">FAQs</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Shipping Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Returns & Refunds</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Track Order</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-4 text-lg font-semibold">Contact Us</h4>
                    <ul class="space-y-2">
                        <li class="flex items-center text-gray-400"><i class="mr-2 fas fa-map-marker-alt"></i> 123 Street, City, Country</li>
                        <li class="flex items-center text-gray-400"><i class="mr-2 fas fa-phone"></i> +1 234 567 890</li>
                        <li class="flex items-center text-gray-400"><i class="mr-2 fas fa-envelope"></i> info@shopease.com</li>
                    </ul>
                </div>
            </div>
            <div class="pt-8 mt-8 text-center text-gray-400 border-t border-gray-800">
                <p>&copy; <?= date('Y') ?> ShopEase. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM fully loaded");
            
            // Quantity increment/decrement functionality
            const incrementButtons = document.querySelectorAll('.increment-quantity');
            const decrementButtons = document.querySelectorAll('.decrement-quantity');
            
            incrementButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const input = this.parentElement.querySelector('input[type="number"]');
                    const currentVal = parseInt(input.value);
                    input.value = currentVal + 1;
                    this.closest('form').submit();
                });
            });
            
            decrementButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const input = this.parentElement.querySelector('input[type="number"]');
                    if (parseInt(input.value) > 1) {
                        input.value = parseInt(input.value) - 1;
                        this.closest('form').submit();
                    }
                });
            });
            
            // Payment method selection
            const paymentMethods = document.querySelectorAll('.payment-method-option');
            const paymentForms = document.querySelectorAll('.payment-method-forms > div');
            
            if (paymentMethods.length) {
                paymentMethods.forEach(method => {
                    method.addEventListener('click', function() {
                        // Update active class
                        paymentMethods.forEach(m => m.classList.remove('active'));
                        this.classList.add('active');
                        
                        // Show corresponding form
                        const methodValue = this.getAttribute('data-method');
                        paymentForms.forEach(form => form.classList.remove('active'));
                        document.getElementById(methodValue + '-form').classList.add('active');
                    });
                });
            }
            
            // Credit card formatting
            const cardNumberInput = document.getElementById('card_number');
            if (cardNumberInput) {
                cardNumberInput.addEventListener('input', function(e) {
                    let value = this.value.replace(/\D/g, '');
                    if (value.length > 0) {
                        value = value.match(/.{1,4}/g).join(' ');
                    }
                    this.value = value;
                });
            }
            
            // Expiry date formatting
            const expiryDateInput = document.getElementById('expiry_date');
            if (expiryDateInput) {
                expiryDateInput.addEventListener('input', function(e) {
                    let value = this.value.replace(/\D/g, '');
                    if (value.length > 2) {
                        value = value.substring(0, 2) + '/' + value.substring(2, 4);
                    }
                    this.value = value;
                });
            }
            
            // Complete checkout button
            const checkoutBtn = document.getElementById('complete-checkout-btn');
            if (checkoutBtn) {
                checkoutBtn.addEventListener('click', function() {
                    openScannerModal();
                });
            }
            
            // Find my location button
            const findLocationBtn = document.getElementById('findLocationBtn');
            if (findLocationBtn) {
                findLocationBtn.addEventListener('click', function() {
                    if (navigator.geolocation) {
                        findLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Getting your location...';
                        
                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                // Use the coordinates to fetch address via reverse geocoding
                                const latitude = position.coords.latitude;
                                const longitude = position.coords.longitude;
                                
                                // Use a geocoding service (using a mock for this example)
                                fetchAddressFromCoordinates(latitude, longitude);
                            },
                            function(error) {
                                // Handle errors
                                findLocationBtn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Find My Location';
                                
                                let errorMessage = 'Unable to retrieve your location.';
                                switch(error.code) {
                                    case error.PERMISSION_DENIED:
                                        errorMessage = "You denied the request for geolocation.";
                                        break;
                                    case error.POSITION_UNAVAILABLE:
                                        errorMessage = "Location information is unavailable.";
                                        break;
                                    case error.TIMEOUT:
                                        errorMessage = "The request to get your location timed out.";
                                        break;
                                    case error.UNKNOWN_ERROR:
                                        errorMessage = "An unknown error occurred.";
                                        break;
                                }
                                
                                alert(errorMessage);
                            }
                        );
                    } else {
                        alert("Geolocation is not supported by this browser.");
                    }
                });
            }
            
            // Scanner modal
            const scannerModal = document.getElementById('scannerModal');
            const closeScannerBtn = document.getElementById('closeScannerBtn');
            
            if (closeScannerBtn) {
                closeScannerBtn.addEventListener('click', function() {
                    if (scannerModal) scannerModal.style.display = 'none';
                });
            }
            
            // Update shipping summary when form changes
            const shippingFields = document.querySelectorAll('.shipping-form input, .shipping-form select');
            shippingFields.forEach(field => {
                field.addEventListener('change', updateShippingSummary);
            });
            
            // Initial update of shipping summary
            updateShippingSummary();
        });

        // Navigation functions
        function goToCart() {
            // Update step indicators
            document.getElementById('cartStep').classList.add('active');
            document.getElementById('cartStep').classList.remove('completed');
            document.getElementById('shippingStep').classList.remove('active', 'completed');
            document.getElementById('paymentStep').classList.remove('active', 'completed');
            document.getElementById('confirmStep').classList.remove('active', 'completed');
            
            // Show cart section, hide others
            document.getElementById('cartSection').classList.add('active');
            document.getElementById('shippingSection').classList.remove('active');
            document.getElementById('paymentSection').classList.remove('active');
            document.getElementById('confirmationSection').classList.remove('active');
            
            // Update session checkout step via AJAX
            updateCheckoutStep('cart');
        }

        function goToShipping() {
            // Update step indicators
            document.getElementById('cartStep').classList.remove('active');
            document.getElementById('cartStep').classList.add('completed');
            document.getElementById('shippingStep').classList.add('active');
            document.getElementById('paymentStep').classList.remove('active', 'completed');
            document.getElementById('confirmStep').classList.remove('active', 'completed');
            
            // Show shipping section, hide others
            document.getElementById('cartSection').classList.remove('active');
            document.getElementById('shippingSection').classList.add('active');
            document.getElementById('paymentSection').classList.remove('active');
            document.getElementById('confirmationSection').classList.remove('active');
            
            // Update session checkout step via AJAX
            updateCheckoutStep('shipping');
        }

        function goToPayment() {
            // Update step indicators
            document.getElementById('cartStep').classList.remove('active');
            document.getElementById('cartStep').classList.add('completed');
            document.getElementById('shippingStep').classList.remove('active');
            document.getElementById('shippingStep').classList.add('completed');
            document.getElementById('paymentStep').classList.add('active');
            document.getElementById('confirmStep').classList.remove('active', 'completed');
            
            // Show payment section, hide others
            document.getElementById('cartSection').classList.remove('active');
            document.getElementById('shippingSection').classList.remove('active');
            document.getElementById('paymentSection').classList.add('active');
            document.getElementById('confirmationSection').classList.remove('active');
            
            // Update session checkout step via AJAX
            updateCheckoutStep('payment');
        }

        function goToConfirmation() {
            // Update step indicators
            document.getElementById('cartStep').classList.remove('active');
            document.getElementById('cartStep').classList.add('completed');
            document.getElementById('shippingStep').classList.remove('active');
            document.getElementById('shippingStep').classList.add('completed');
            document.getElementById('paymentStep').classList.remove('active');
            document.getElementById('paymentStep').classList.add('completed');
            document.getElementById('confirmStep').classList.add('active');
            
            // Show confirmation section, hide others
            document.getElementById('cartSection').classList.remove('active');
            document.getElementById('shippingSection').classList.remove('active');
            document.getElementById('paymentSection').classList.remove('active');
            document.getElementById('confirmationSection').classList.add('active');
            
            // Update session checkout step via AJAX
            updateCheckoutStep('confirmation');
        }

        function updateCheckoutStep(step) {
            // Send AJAX request to update session
            fetch('step.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'step=' + step
            })
            .catch(error => {
                console.error('Error updating checkout step:', error);
            });
        }

        function updateShippingSummary() {
            // Get values from shipping form
            const address = document.getElementById('shipping_address').value;
            const city = document.getElementById('shipping_city').value;
            const state = document.getElementById('shipping_state').value;
            const zip = document.getElementById('shipping_zip').value;
            const email = document.getElementById('user_email').value;
            const phone = document.getElementById('shipping_phone').value;
            
            // Update summary elements if they exist
            const addressEl = document.getElementById('summary-address');
            const cityStateZipEl = document.getElementById('summary-city-state-zip');
            const emailEl = document.getElementById('summary-email');
            const phoneEl = document.getElementById('summary-phone');
            
            if (addressEl) addressEl.textContent = address;
            if (cityStateZipEl) cityStateZipEl.textContent = `${city}, ${state} ${zip}`;
            if (emailEl) emailEl.textContent = email;
            if (phoneEl) phoneEl.textContent = phone;
        }

        function fetchAddressFromCoordinates(latitude, longitude) {
            // In a real application, you would use a geocoding service API
            // For this example, we'll simulate with some mock data
            
            // Simulating API call delay
            setTimeout(() => {
                // Mock address data
                const mockAddress = {
                    street: "123 Main Street",
                    city: "New York",
                    state: "NY",
                    zip: "10001",
                    country: "USA"
                };
                
                // Fill the form with the address
                document.getElementById('shipping_address').value = mockAddress.street;
                document.getElementById('shipping_city').value = mockAddress.city;
                document.getElementById('shipping_state').value = mockAddress.state;
                document.getElementById('shipping_zip').value = mockAddress.zip;
                
                // Update the button text back to normal
                document.getElementById('findLocationBtn').innerHTML = '<i class="fas fa-map-marker-alt"></i> Find My Location';
                
                // Show success message
                alert("Address successfully retrieved!");
                
                // Update shipping summary
                updateShippingSummary();
            }, 1500);
        }

        function openScannerModal() {
            const scannerModal = document.getElementById('scannerModal');
            if (scannerModal) {
                scannerModal.style.display = 'flex';
                
                // Reset scanner
                const scanResult = document.getElementById('scanResult');
                const completePurchase = document.getElementById('completePurchase');
                if (scanResult) scanResult.classList.add('hidden');
                if (completePurchase) completePurchase.classList.add('hidden');
                
                // Load scanner
                simulateScannerWithImage();
            }
        }

        function simulateScannerWithImage() {
            const scannerContainer = document.getElementById('scanner-container');
            if (!scannerContainer) return;
            
            // Clear previous content
            scannerContainer.innerHTML = '';
            
            // Create and add scanner image
            const scannerImg = document.createElement('img');
            scannerImg.src = 'images/scanner.jpeg';
            scannerImg.alt = 'Payment Scanner';
            scannerImg.style.width = '100%';
            scannerImg.style.height = 'auto';
            scannerImg.style.objectFit = 'contain';
            
            scannerContainer.appendChild(scannerImg);
            
            // Add scanning animation line
            const scanLine = document.createElement('div');
            scanLine.className = 'scan-line';
            scannerContainer.appendChild(scanLine);
            
            // Simulate scanning
            setTimeout(() => {
                // Show success and complete purchase button
                const scanResult = document.getElementById('scanResult');
                const completePurchase = document.getElementById('completePurchase');
                
                if (scanResult) scanResult.classList.remove('hidden');
                if (completePurchase) completePurchase.classList.remove('hidden');
            }, 3000);
        }

        function completeOrder() {
            // Close scanner modal
            const scannerModal = document.getElementById('scannerModal');
            if (scannerModal) scannerModal.style.display = 'none';
            
            // Move to confirmation step
            goToConfirmation();
            
            // Reset cart count in the UI
            const cartCountBadge = document.querySelector('.cart-count-badge');
            if (cartCountBadge) {
                cartCountBadge.textContent = '0 items';
            }
            
            // Generate a random order number
            const orderNumber = Math.floor(100000 + Math.random() * 900000);
            
            // Create confirmation content with confirmation button instead of continue shopping
            const confirmationSection = document.getElementById('confirmationSection');
            if (confirmationSection) {
                confirmationSection.innerHTML = `
                    <div class="p-6 mb-6 text-center bg-white rounded-lg shadow-md">
                        <div class="flex justify-center mb-6">
                            <div class="flex items-center justify-center w-20 h-20 bg-green-100 rounded-full">
                                <i class="text-4xl text-green-500 fas fa-check-circle"></i>
                            </div>
                        </div>
                        <h2 class="mb-2 text-2xl font-bold text-gray-800">Thank You for Your Order!</h2>
                        <p class="mb-4 text-gray-600">Your order has been successfully placed.</p>
                        <p class="mb-6 text-lg font-medium text-primary">Order #${orderNumber}</p>
                        
                        <div class="p-4 mb-6 rounded-lg bg-gray-50">
                            <h3 class="mb-2 font-semibold text-gray-800">Order Details</h3>
                            <div class="flex justify-between mb-2">
                                <span>Order Date:</span>
                                <span>${new Date().toLocaleDateString()}</span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span>Payment Method:</span>
                                <span>${getSelectedPaymentMethod()}</span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span>Total Amount:</span>
                                <span class="font-bold">$${parseFloat(<?= $total_with_shipping ?>).toFixed(2)}</span>
                            </div>
                        </div>
                        
                        <div class="p-4 mb-6 text-left rounded-lg bg-blue-50">
                            <h3 class="mb-2 font-semibold text-gray-800">Shipping Information</h3>
                            <p>${document.getElementById('shipping_address').value}</p>
                            <p>${document.getElementById('shipping_city').value}, ${document.getElementById('shipping_state').value} ${document.getElementById('shipping_zip').value}</p>
                            <p>Phone: ${document.getElementById('shipping_phone').value}</p>
                            <p>Email: ${document.getElementById('user_email').value}</p>
                        </div>
                        
                        <p class="mb-6 text-gray-600">We've sent a confirmation email with all the details to your email address.</p>
                        
                        <div class="flex flex-col justify-center gap-4">
                            <button onclick="window.print()" class="flex items-center justify-center px-6 py-3 text-gray-700 transition-all bg-gray-200 rounded-md hover:bg-gray-300">
                                <i class="mr-2 fas fa-print"></i> Print Receipt
                            </button>
                            
                            <!-- Confirmation button replaces Continue Shopping -->
                            <button id="confirm-order-button" onclick="confirmAndContinueShopping()" class="confirmation-button">
                                <i class="fas fa-check-circle"></i> Confirm My Order
                            </button>
                        </div>
                    </div>
                `;
            }
            
            // Reset cart in database via ajax
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'reset_cart=1'
            })
            .then(response => response.json())
            .then(data => {
                console.log('Cart reset successful:', data);
            })
            .catch(error => {
                console.error('Error resetting cart:', error);
            });
        }

        // New function to handle confirmation and continue shopping
        function confirmAndContinueShopping() {
            // Get the confirmation button
            const confirmButton = document.getElementById('confirm-order-button');
            
            // Change button text to show loading state
            confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            confirmButton.disabled = true;
            
            // Reset shipping details via AJAX
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'reset_shipping=1'
            })
            .then(response => response.json())
            .then(data => {
                console.log('Shipping details reset successful:', data);
                
                // Replace the confirmation button with the continue shopping button
                confirmButton.outerHTML = `
                    <a href="clothing.php" class="continue-shopping-button active">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                `;
                
                // Show success message
                const confirmationSection = document.getElementById('confirmationSection');
                if (confirmationSection) {
                    // Add a success message above the continue shopping button
                    const successMessage = document.createElement('div');
                    successMessage.className = 'p-4 mb-4 text-green-700 bg-green-100 rounded-md';
                    successMessage.innerHTML = '<i class="mr-2 fas fa-check-circle"></i> Order confirmed! Your shipping details have been reset.';
                    
                    // Insert the message before the continue shopping button
                    const continueButton = confirmationSection.querySelector('.continue-shopping-button');
                    if (continueButton) {
                        continueButton.parentNode.insertBefore(successMessage, continueButton);
                    }
                }
            })
            .catch(error => {
                console.error('Error resetting shipping details:', error);
                
                // Show error and enable the button again
                confirmButton.innerHTML = '<i class="fas fa-check-circle"></i> Confirm My Order';
                confirmButton.disabled = false;
                
                // Display error message
                alert('There was an error processing your request. Please try again.');
            });
        }

        function getSelectedPaymentMethod() {
            const activeMethod = document.querySelector('.payment-method-option.active');
            if (activeMethod) {
                const method = activeMethod.getAttribute('data-method');
                if (method === 'credit-card') return 'Credit Card';
                if (method === 'paypal') return 'PayPal';
                if (method === 'apple-pay') return 'Apple Pay';
            }
            return 'Credit Card';
        }
    </script>
</body>
</html>
