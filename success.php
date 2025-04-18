<?php
session_start();
$loggedin = false;
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
} else {
    $loggedin = true;
}

// Include database connection
require_once 'dbconnect.php';

// Clear the cart after successful checkout
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    $clear_sql = "DELETE FROM cart WHERE user_id = ?";
    $clear_stmt = $conn->prepare($clear_sql);
    $clear_stmt->bind_param("i", $user_id);
    $clear_stmt->execute();
    $clear_stmt->close();
}

// Generate a random order number
$order_number = 'ORD-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - ShopEase</title>
    <script src="https://cdn.tailwindcss.com/3.3.5"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#1E40AF',
                        accent: '#10B981',
                    }
                }
            }
        }
    </script>
    <style>
        .success-animation {
            animation: success-pulse 2s infinite;
        }
        
        @keyframes success-pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex flex-col items-center justify-center min-h-screen px-4 py-12">
        <div class="w-full max-w-xl p-8 text-center bg-white rounded-lg shadow-lg md:p-12">
            <div class="mb-6 text-6xl text-green-500 success-animation">
                <i class="far fa-check-circle"></i>
            </div>
            
            <h1 class="mb-4 text-3xl font-bold text-gray-800">Order Successful!</h1>
            <p class="mb-8 text-lg text-gray-600">Thank you for your purchase. Your order has been placed and is being processed.</p>
            
            <div class="p-6 mb-6 bg-gray-100 rounded-lg">
                <h2 class="mb-4 text-xl font-semibold">Order Details</h2>
                <div class="flex justify-between mb-2">
                    <span class="text-gray-600">Order Number:</span>
                    <span class="font-medium"><?= $order_number ?></span>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-gray-600">Date:</span>
                    <span class="font-medium"><?= date('F j, Y') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Payment Method:</span>
                    <span class="font-medium">QR Code Payment</span>
                </div>
            </div>
            
            <p class="mb-8 text-gray-600">We've sent a confirmation email with all the details to your registered email address.</p>
            
            <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4">
                <a href="index.php" class="px-6 py-3 font-medium text-white transition-all duration-300 rounded-full bg-primary hover:bg-secondary">
                    <i class="mr-2 fas fa-home"></i> Return to Home
                </a>
                <a href="track.php" class="px-6 py-3 font-medium text-gray-700 transition-all duration-300 border border-gray-300 rounded-full hover:bg-gray-100">
                    <i class="mr-2 fas fa-truck"></i> Track Order
                </a>
            </div>
        </div>
    </div>
</body>
</html>
