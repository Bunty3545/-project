<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['loggedin'])) {
    header("location: login.php");
    exit;
}

// Include database connection for cart functionality
require_once 'dbconnect.php';

// Get cart count for the user
$cart_count = 0;
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_sql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->bind_param("i", $user_id);
    $cart_stmt->execute();
    $result = $cart_stmt->get_result();
    $row = $result->fetch_assoc();
    $cart_count = $row['total'] ? $row['total'] : 0;
    $cart_stmt->close();
}

// Flash Sale products data
$flash_sale_products = [
    [
        'id' => 101,
        'title' => "Premium Wireless Earbuds",
        'category' => "Electronics",
        'price' => 59.99,
        'old_price' => 119.99,
        'discount' => 50,
        'rating' => 4.8,
        'review_count' => 312,
        'image' => "https://images.unsplash.com/photo-1590658268037-6bf12165a8df?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80",
        'ends_in' => "00:16:43" // Hours:Minutes:Seconds
    ],
    [
        'id' => 102,
        'title' => "Designer Leather Jacket",
        'category' => "Clothing",
        'price' => 129.99,
        'old_price' => 299.99,
        'discount' => 57,
        'rating' => 4.6,
        'review_count' => 189,
        'image' => "https://images.unsplash.com/photo-1551028719-00167b16eac5?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80",
        'ends_in' => "00:16:43"
    ],
    [
        'id' => 103,
        'title' => "Smart Home Security Camera",
        'category' => "Electronics",
        'price' => 79.99,
        'old_price' => 159.99,
        'discount' => 50,
        'rating' => 4.7,
        'review_count' => 243,
        'image' => "https://images.unsplash.com/photo-1557438159-51eec7a6c9e8?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80",
        'ends_in' => "00:16:43"
    ],
    [
        'id' => 104,
        'title' => "Premium Yoga Mat",
        'category' => "Sports",
        'price' => 24.99,
        'old_price' => 49.99,
        'discount' => 50,
        'rating' => 4.9,
        'review_count' => 167,
        'image' => "https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80",
        'ends_in' => "00:16:43"
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - <?php echo htmlspecialchars($_SESSION['username']); ?></title>
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
                        danger: '#EF4444',
                        warning: '#F59E0B',
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'bounce-slow': 'bounce 2s infinite',
                    },
                    boxShadow: {
                        'glow': '0 0 15px rgba(59, 130, 246, 0.5)',
                    }
                }
            }
        }
    </script> 
    <style>
        .hero {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1441986300917-64674bd600d8');
            background-size: cover;
            background-position: center;
        }
        .product-card {
            transition: all 0.3s ease;
            border-radius: 1rem;
            overflow: hidden;
        }
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.1);
        }
        .add-to-cart-btn {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        .add-to-cart-btn::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            z-index: -1;
            transform: scale(0);
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .add-to-cart-btn:hover::after {
            transform: scale(2);
        }
        .add-to-cart-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .flash-sale-bg {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);

    position: relative;
    overflow: hidden;
    border-radius: 0.5rem;
    box-shadow: 0 20px 40px rgba(59, 130, 246, 0.2);
    margin: 2rem 0;
}


        .flash-sale-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://www.transparenttextures.com/patterns/cubes.png');
            opacity: 0.07;
            animation: backgroundShift 15s infinite alternate ease-in-out;
        }

        @keyframes backgroundShift {
            0% { background-position: 0% 0%; }
            100% { background-position: 100% 100%; }
        }

        .flash-sale-title {
            display: flex;
            align-items: center;
            color: white;
            position: relative;
        }

        .flash-icon {
            margin-right: 1rem;
            font-size: 2.5rem;
            animation: flashIcon 2s infinite;
            color: #93c5fd;
            text-shadow: 0 0 15px rgba(59, 130, 246, 0.7);
        }

        @keyframes flashIcon {
            0%, 100% { transform: rotate(-5deg); opacity: 1; }
            50% { transform: rotate(5deg); opacity: 0.9; }
        }

        .timer-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border-radius: 1rem;
            padding: 0.75rem 1.5rem;
            color: white;
            font-weight: bold;
            letter-spacing: 1px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transform: translateY(0);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .timer-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }
        
        .flash-badge {
            animation: pulseBadge 2s infinite;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        @keyframes pulseBadge {
            0% { transform: scale(1); box-shadow: 0 0 5px rgba(255, 255, 255, 0.5); }
            50% { transform: scale(1.05); box-shadow: 0 0 15px rgba(255, 255, 255, 0.7); }
            100% { transform: scale(1); box-shadow: 0 0 5px rgba(255, 255, 255, 0.5); }
        }
        
        .countdown-timer {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            justify-content: center;
        }
        
        .time-unit {
            background: rgba(0, 0, 0, 0.3);
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            font-variant-numeric: tabular-nums;
            min-width: 3rem;
            text-align: center;
            position: relative;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2), inset 0 -4px 0 rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 1.25rem;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        
        .time-unit::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 50%;
            background: rgba(255, 255, 255, 0.1);
            pointer-events: none;
        }
        
        .time-unit-label {
            display: block;
            font-size: 0.65rem;
            font-weight: normal;
            text-transform: uppercase;
            opacity: 0.8;
            margin-top: 0.25rem;
        }
        
        .time-separator {
            font-size: 1.5rem;
            font-weight: bold;
            color: rgba(255, 255, 255, 0.7);
        }
        
        /* Square flash sale product cards with glowing effect */
        .flash-sale-product {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border-radius: 0.5rem;
            overflow: hidden;
            background: white;
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.15);
            transform: translateY(0);
            position: relative;
        }
        
        .flash-sale-product:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.25), 0 0 20px rgba(59, 130, 246, 0.4);
        }
        
        .flash-sale-product::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            box-shadow: 0 0 30px rgba(59, 130, 246, 0);
            transition: all 0.4s ease;
            border-radius: 0.5rem;
            pointer-events: none;
        }
        
        .flash-sale-product:hover::after {
            box-shadow: 0 0 30px rgba(59, 130, 246, 0.6);
        }
        
        .flash-sale-image-wrapper {
            position: relative;
            overflow: hidden;
            height: 240px;
            background: #f5f8ff;
        }
        
        .flash-sale-image {
            transition: transform 0.7s ease;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform-origin: center;
        }
        
        .flash-sale-product:hover .flash-sale-image {
            transform: scale(1.1);
        }
        
        /* Enhanced Sale Badge */
        .sale-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            font-weight: bold;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
            z-index: 10;
            animation: pulsateBadge 2s infinite;
        }
        
        @keyframes pulsateBadge {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* Icon-only Flash Cart Button */
        .flash-cart-btn {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
            transform: translateY(0);
        }
        
        .flash-cart-btn:hover {
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4), 0 0 15px rgba(59, 130, 246, 0.6);
        }
        
        .flash-cart-btn i {
            font-size: 1.2rem;
        }
        
        /* Sale badge shimmer effect */
        .sale-badge::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.3) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            transform: rotate(30deg);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: rotate(30deg) translateX(-100%); }
            100% { transform: rotate(30deg) translateX(100%); }
        }
        
        /* Enhanced Category Cards */
        .category-section {
            padding: 4rem 0;
            background-color: #f9fafb;
            position: relative;
            overflow: hidden;
        }
        
        .category-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://www.transparenttextures.com/patterns/subtle-dots.png');
            opacity: 0.4;
        }
        
        .category-title {
            font-size: 2.5rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 3rem;
            color: #1f2937;
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .category-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, #3b82f6, #93c5fd);
            border-radius: 2px;
        }
        
        .category-card {
            transition: all 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            position: relative;
            background: white;
        }
        
        .category-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .category-bg {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 60%;
            background: linear-gradient(to top, rgba(59, 130, 246, 0.08), transparent);
            transition: height 0.5s ease;
            z-index: 1;
            border-radius: 1.5rem;
        }
        
        .category-card:hover .category-bg {
            height: 75%;
        }
        
        .category-icon {
            transition: all 0.5s ease;
            filter: drop-shadow(0 10px 15px rgba(59, 130, 246, 0.3));
        }
        
        .category-card:hover .category-icon {
            transform: scale(1.1) translateY(-10px);
            color: #2563eb;
        }
        
        .category-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, #3b82f6, #93c5fd);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.5s ease;
        }
        
        .category-card:hover::after {
            transform: scaleX(1);
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background-color: #10B981;
            color: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            z-index: 1000;
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 0.4s, transform 0.4s;
            display: flex;
            align-items: center;
        }
        .notification.show {
            opacity: 1;
            transform: translateY(0);
        }
        .notification.error {
            background-color: #EF4444;
        }
        
        .product-image {
            transition: transform 0.5s ease;
        }
        .product-card:hover .product-image {
            transform: scale(1.05);
        }
        .new-badge {
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
            animation: pulse 2s infinite;
        }
        .featured-section {
            background: linear-gradient(to right, #f7fafc, #edf2f7);
            position: relative;
        }
        .featured-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://www.transparenttextures.com/patterns/subtle-white-feathers.png');
            opacity: 0.5;
        }
        .banner-gradient {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            position: relative;
            overflow: hidden;
        }
        .banner-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://www.transparenttextures.com/patterns/diagonal-waves.png');
            opacity: 0.05;
        }

        /* Critical fixes for form interaction */
        form input[type="email"],
        form button[type="submit"] {
            pointer-events: auto !important;
            position: relative;
            z-index: 20;
        }

        form input[type="email"] {
            cursor: text !important;
        }

        form button[type="submit"] {
            cursor: pointer !important;
        }

        /* Fix any potential overlay issues */
        .banner-gradient::before {
            pointer-events: none !important;
        }

        /* Ensure forms are always interactive */
        form {
            position: relative;
            z-index: 10;
        }
        
        /* Animation for timer attention pulse */
        @keyframes attentionPulse {
            0%, 100% { box-shadow: 0 0 0 rgba(59, 130, 246, 0); }
            50% { box-shadow: 0 0 50px rgba(59, 130, 246, 0.5); }
        }

        .attention-pulse {
            animation: attentionPulse 1.5s ease;
        }
        .flash-icon {
    margin-right: 1rem;
    font-size: 2.5rem;
    animation: flameIcon 2s infinite;
    color: #f87171;
    text-shadow: 0 0 15px rgba(239, 68, 68, 0.7);
}

@keyframes flameIcon {
    0% { transform: scale(1); opacity: 0.9; }
    50% { transform: scale(1.2); opacity: 1; }
    100% { transform: scale(1); opacity: 0.9; }
}
.hot-deals-heading {
    position: relative;
    display: inline-block;
}

.hot-deals-heading::after {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(to right, #f87171, #ef4444);
    border-radius: 2px;
}

    </style>
</head>
<body class="bg-gray-50">
    <!-- Notification -->
    <div id="notification" class="notification">
        <i class="mr-3 text-xl fas fa-check-circle"></i>
        <div>
            <span id="notification-message" class="block font-medium">Item added to cart!</span>
            <span class="text-xs opacity-80" id="notification-details"></span>
        </div>
    </div>

    <?php include 'nav.php'; ?>

    <!-- Hero Section -->
    <section class="py-20 text-white hero md:py-32">
        <div class="px-4 mx-auto text-center max-w-7xl">
            <h1 class="mb-6 text-4xl font-bold md:text-6xl">Summer Collection 2023</h1>
            <p class="max-w-2xl mx-auto mb-8 text-xl md:text-2xl">Discover our new arrivals with up to 40% discount on selected items</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="clothing.php" class="px-8 py-3 font-semibold transition duration-300 transform rounded-full bg-primary hover:bg-secondary hover:scale-105 hover:shadow-lg">Shop Now</a>
                <a href="#flash-sale" class="px-8 py-3 font-semibold transition duration-300 transform bg-transparent border-2 border-white rounded-full hover:bg-white hover:text-gray-900 hover:scale-105 hover:shadow-lg">View Flash Sale</a>
            </div>
        </div>
    </section>

    <!-- Enhanced Shop by Category Section -->
    <section class="category-section">
        <h2 class="mx-auto category-title">Shop by Category</h2>
        <div class="grid grid-cols-2 gap-6 px-4 mx-auto max-w-7xl md:grid-cols-4">
            <a href="clothing.php" class="category-card">
                <div class="category-bg"></div>
                <div class="flex items-center justify-center h-40 bg-gradient-to-br from-blue-50 to-indigo-50">
                    <i class="text-5xl category-icon fas fa-tshirt text-primary"></i>
                </div>
                <div class="p-4 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Clothing</h3>
                    <p class="mt-1 text-gray-600">200+ items</p>
                </div>
            </a>
            <a href="electronics.php" class="category-card">
                <div class="category-bg"></div>
                <div class="flex items-center justify-center h-40 bg-gradient-to-br from-blue-50 to-indigo-50">
                    <i class="text-5xl category-icon fas fa-laptop text-primary"></i>
                </div>
                <div class="p-4 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Electronics</h3>
                    <p class="mt-1 text-gray-600">150+ items</p>
                </div>
            </a>
            <a href="home&living.php" class="category-card">
                <div class="category-bg"></div>
                <div class="flex items-center justify-center h-40 bg-gradient-to-br from-blue-50 to-indigo-50">
                    <i class="text-5xl category-icon fas fa-home text-primary"></i>
                </div>
                <div class="p-4 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Home & Living</h3>
                    <p class="mt-1 text-gray-600">300+ items</p>
                </div>
            </a>
            <a href="sports.php" class="category-card">
                <div class="category-bg"></div>
                <div class="flex items-center justify-center h-40 bg-gradient-to-br from-blue-50 to-indigo-50">
                    <i class="text-5xl category-icon fas fa-dumbbell text-primary"></i>
                </div>
                <div class="p-4 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Sports</h3>
                    <p class="mt-1 text-gray-600">120+ items</p>
                </div>
            </a>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="px-4 py-16 featured-section">
        <div class="relative z-10 mx-auto max-w-7xl">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800">Featured Products</h2>
                    <p class="mt-2 text-gray-600">Handpicked favorites just for you</p>
                </div>
                <a href="#" class="flex items-center px-4 py-2 text-primary hover:text-secondary">
                    View All 
                    <i class="ml-2 fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                <!-- Product 1 -->
                <div class="overflow-hidden transition duration-300 bg-white shadow-md rounded-xl product-card hover:shadow-xl">
                    <div class="relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30" alt="Product" class="object-cover w-full h-64 product-image">
                        <div class="absolute px-3 py-1 text-xs font-bold text-white bg-red-500 rounded-full sale-badge top-3 right-3">SALE</div>
                    </div>
                    <div class="p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Wireless Headphones</h3>
                                <p class="text-gray-600">Electronics</p>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold text-gray-900">$99.99</span>
                                <span class="block text-sm text-gray-500 line-through">$129.99</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-4">
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span class="ml-1 text-sm text-gray-600">(42)</span>
                            </div>
                            <button 
                                class="p-3 text-white transition duration-300 rounded-full shadow-md add-to-cart-btn bg-primary hover:bg-secondary" 
                                data-id="1"
                                data-name="Wireless Headphones"
                                data-price="99.99"
                                data-category="Electronics"
                                data-image="https://images.unsplash.com/photo-1523275335684-37898b6baf30">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Product 2 -->
                <div class="overflow-hidden transition duration-300 bg-white shadow-md rounded-xl product-card hover:shadow-xl">
                    <div class="relative overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1546868871-7041f2a55e12" alt="Product" class="object-cover w-full h-64 product-image">
                    </div>
                    <div class="p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Smart Watch</h3>
                                <p class="text-gray-600">Electronics</p>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold text-gray-900">$199.99</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-4">
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                <span class="ml-1 text-sm text-gray-600">(28)</span>
                            </div>
                            <button 
                                class="p-3 text-white transition duration-300 rounded-full shadow-md add-to-cart-btn bg-primary hover:bg-secondary" 
                                data-id="2"
                                data-name="Smart Watch"
                                data-price="199.99"
                                data-category="Electronics"
                                data-image="https://images.unsplash.com/photo-1546868871-7041f2a55e12">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Product 3 -->
                <div class="overflow-hidden transition duration-300 bg-white shadow-md rounded-xl product-card hover:shadow-xl">
                    <div class="relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1591047139829-d91aecb6caea" alt="Product" class="object-cover w-full h-64 product-image">
                        <div class="absolute px-3 py-1 text-xs font-bold text-white bg-green-500 rounded-full new-badge top-3 right-3">NEW</div>
                    </div>
                    <div class="p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Running Shoes</h3>
                                <p class="text-gray-600">Sports</p>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold text-gray-900">$79.99</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-4">
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <span class="ml-1 text-sm text-gray-600">(56)</span>
                            </div>
                            <button 
                                class="p-3 text-white transition duration-300 rounded-full shadow-md add-to-cart-btn bg-primary hover:bg-secondary" 
                                data-id="3"
                                data-name="Running Shoes"
                                data-price="79.99"
                                data-category="Sports"
                                data-image="https://images.unsplash.com/photo-1591047139829-d91aecb6caea">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Product 4 -->
                <div class="overflow-hidden transition duration-300 bg-white shadow-md rounded-xl product-card hover:shadow-xl">
                    <div class="relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1585386959984-a4155224a1ad" alt="Product" class="object-cover w-full h-64 product-image">
                    </div>
                    <div class="p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Men's Perfume</h3>
                                <p class="text-gray-600">Beauty</p>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold text-gray-900">$49.99</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-4">
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span class="ml-1 text-sm text-gray-600">(34)</span>
                            </div>
                            <button 
                                class="p-3 text-white transition duration-300 rounded-full shadow-md add-to-cart-btn bg-primary hover:bg-secondary" 
                                data-id="4"
                                data-name="Men's Perfume"
                                data-price="49.99"
                                data-category="Beauty"
                                data-image="https://images.unsplash.com/photo-1585386959984-a4155224a1ad">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Enhanced Flash Sale Section with Blue Theme and Square Box Products -->
    <section id="flash-sale" class="py-16 flash-sale-bg">
        <div class="relative z-10 px-4 mx-auto max-w-7xl">
            <div class="flex flex-col items-center justify-between mb-10 md:flex-row">
            <div class="flash-sale-title">
    <i class="flash-icon fas fa-fire"></i>
    <div>
        <h2 class="text-3xl font-bold text-white hot-deals-heading">Hot Deals</h2>
    </div>
</div>


                <div class="timer-box">
                    <span class="mr-3">Ends in:</span>
                    <div class="countdown-timer">
                        <div class="time-unit">
                            <span id="hours">00</span>
                            <span class="time-unit-label">HRS</span>
                        </div>
                        <span class="time-separator">:</span>
                        <div class="time-unit">
                            <span id="minutes">16</span>
                            <span class="time-unit-label">MIN</span>
                        </div>
                        <span class="time-separator">:</span>
                        <div class="time-unit">
                            <span id="seconds">43</span>
                            <span class="time-unit-label">SEC</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 md:grid-cols-4">
                <?php foreach($flash_sale_products as $product): ?>
                <div class="flash-sale-product">
                    <div class="flash-sale-image-wrapper">
                        <img src="<?= htmlspecialchars($product['image']) ?>" 
                             alt="<?= htmlspecialchars($product['title']) ?>" 
                             class="flash-sale-image" 
                             onerror="this.src='https://via.placeholder.com/600x400?text=Image+Not+Found'">
                        
                        <div class="sale-badge">
                            -<?= $product['discount'] ?>%
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($product['title']) ?></h3>
                                <p class="text-gray-600"><?= htmlspecialchars($product['category']) ?></p>
                            </div>
                            <div class="text-right">
                            <span class="text-lg font-bold text-primary">$<?= number_format($product['price'], 2) ?></span>
                                <span class="block text-sm text-gray-500 line-through">$<?= number_format($product['old_price'], 2) ?></span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-4">
                            <div class="flex text-yellow-400">
                                <?php
                                $full_stars = floor($product['rating']);
                                $half_star = ($product['rating'] - $full_stars) >= 0.5;
                                
                                for ($i = 0; $i < $full_stars; $i++) {
                                    echo '<i class="fas fa-star"></i>';
                                }
                                
                                if ($half_star) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                    $full_stars++;
                                }
                                
                                for ($i = $full_stars; $i < 5; $i++) {
                                    echo '<i class="far fa-star"></i>';
                                }
                                ?>
                                <span class="ml-1 text-sm text-gray-600">(<?= $product['review_count'] ?>)</span>
                            </div>
                            <button 
                                class="flash-cart-btn add-to-cart-btn" 
                                data-id="<?= $product['id'] ?>"
                                data-name="<?= htmlspecialchars($product['title']) ?>"
                                data-price="<?= $product['price'] ?>"
                                data-category="<?= htmlspecialchars($product['category']) ?>"
                                data-image="<?= htmlspecialchars($product['image']) ?>">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Banner -->
    <section class="px-4 py-16 mx-auto max-w-7xl">
    <div class="p-8 text-white rounded-2xl banner-gradient md:p-12">
        <div class="flex flex-col items-center justify-between md:flex-row">
            <div class="mb-6 md:w-1/2 md:mb-0">
                <h2 class="mb-4 text-3xl font-bold md:text-4xl">Get 20% Off Your First Order</h2>
                <p class="mb-6 text-lg">Subscribe to our newsletter and get exclusive deals delivered straight to your inbox.</p>
                
                <!-- Fixed Subscription Form -->
                <form action="subscribe.php" method="POST" class="relative z-10 flex w-full">
                    <!-- Important: added z-10 to ensure form is above any background elements -->
                    <input type="email" name="email" placeholder="Your email address" required
                           class="w-full px-5 py-3 text-gray-800 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-accent"
                           style="pointer-events: auto !important; cursor: text !important; z-index: 20; position: relative;">
                    <button type="submit" 
                            class="px-6 py-3 font-semibold transition duration-300 transform rounded-r-lg bg-accent hover:bg-green-600 hover:scale-105"
                            style="pointer-events: auto !important; cursor: pointer !important; z-index: 20; position: relative;">
                        Subscribe
                    </button>
                </form>
            </div>
            <div class="md:w-1/3">
                <img src="https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da" alt="Discount" class="rounded-lg shadow-2xl">
            </div>
        </div>
    </div>
</section>

    <!-- Testimonials -->
    <section class="px-4 py-16 mx-auto max-w-7xl">
        <h2 class="mb-12 text-3xl font-bold text-center text-gray-800">What Our Customers Say</h2>
        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
            <div class="p-6 transition-all duration-300 bg-white shadow-md rounded-xl hover:shadow-xl">
                <div class="flex items-center mb-4">
                    <div class="flex mr-2 text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <span class="text-gray-600">5/5</span>
                </div>
                <p class="mb-4 text-gray-700">"The quality of products is amazing and the delivery was super fast. Will definitely shop here again!"</p>
                <div class="flex items-center">
                    <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="Customer" class="w-10 h-10 mr-3 rounded-full">
                    <div>
                        <h4 class="font-semibold text-gray-800">Sarah Johnson</h4>
                        <p class="text-sm text-gray-600">Verified Buyer</p>
                    </div>
                </div>
            </div>
            <div class="p-6 transition-all duration-300 bg-white shadow-md rounded-xl hover:shadow-xl">
                <div class="flex items-center mb-4">
                    <div class="flex mr-2 text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <span class="text-gray-600">5/5</span>
                </div>
                <p class="mb-4 text-gray-700">"Excellent customer service! They helped me choose the perfect gift for my wife's birthday."</p>
                <div class="flex items-center">
                    <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Customer" class="w-10 h-10 mr-3 rounded-full">
                    <div>
                        <h4 class="font-semibold text-gray-800">Michael Chen</h4>
                        <p class="text-sm text-gray-600">Verified Buyer</p>
                    </div>
                </div>
            </div>
            <div class="p-6 transition-all duration-300 bg-white shadow-md rounded-xl hover:shadow-xl">
                <div class="flex items-center mb-4">
                    <div class="flex mr-2 text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <span class="text-gray-600">4.5/5</span>
                </div>
                <p class="mb-4 text-gray-700">"Great selection of products at competitive prices. The mobile app makes shopping so convenient."</p>
                <div class="flex items-center">
                    <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Customer" class="w-10 h-10 mr-3 rounded-full">
                    <div>
                        <h4 class="font-semibold text-gray-800">Emma Rodriguez</h4>
                        <p class="text-sm text-gray-600">Verified Buyer</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-16 text-white bg-gray-900">
        <div class="px-4 mx-auto max-w-7xl">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                <div>
                    <h3 class="flex items-center mb-4 text-xl font-bold">
                        <i class="mr-2 fas fa-shopping-bag text-primary"></i>
                        ShopEase
                    </h3>
                    <p class="mb-4 text-gray-400">Your one-stop shop for all your needs. Quality products at affordable prices.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="p-2 text-gray-400 transition-colors duration-300 rounded-full hover:text-white hover:bg-gray-800"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="p-2 text-gray-400 transition-colors duration-300 rounded-full hover:text-white hover:bg-gray-800"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="p-2 text-gray-400 transition-colors duration-300 rounded-full hover:text-white hover:bg-gray-800"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="p-2 text-gray-400 transition-colors duration-300 rounded-full hover:text-white hover:bg-gray-800"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="mb-4 text-lg font-semibold">Shop</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 transition-colors duration-300 hover:text-white">All Products</a></li>
                        <li><a href="#" class="text-gray-400 transition-colors duration-300 hover:text-white">Featured</a></li>
                        <li><a href="#" class="text-gray-400 transition-colors duration-300 hover:text-white">New Arrivals</a></li>
                        <li><a href="#flash-sale" class="text-gray-400 transition-colors duration-300 hover:text-white">Flash Sale</a></li>
                        <li><a href="#" class="text-gray-400 transition-colors duration-300 hover:text-white">Gift Cards</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-4 text-lg font-semibold">Customer Service</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 transition-colors duration-300 hover:text-white">Contact Us</a></li>
                        <li><a href="#" class="text-gray-400 transition-colors duration-300 hover:text-white">FAQs</a></li>
                        <li><a href="#" class="text-gray-400 transition-colors duration-300 hover:text-white">Shipping Policy</a></li>
                        <li><a href="#" class="text-gray-400 transition-colors duration-300 hover:text-white">Returns & Refunds</a></li>
                        <li><a href="#" class="text-gray-400 transition-colors duration-300 hover:text-white">Track Order</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-4 text-lg font-semibold">About</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 transition-colors duration-300 hover:text-white">Our Story</a></li>
                        <li><a href="#" class="text-gray-400 transition-colors duration-300 hover:text-white">Careers</a></li>
                        <li><a href="#" class="text-gray-400 transition-colors duration-300 hover:text-white">Terms & Conditions</a></li>
                        <li><a href="#" class="text-gray-400 transition-colors duration-300 hover:text-white">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 transition-colors duration-300 hover:text-white">Blog</a></li>
                    </ul>
                </div>
            </div>
            <div class="pt-8 mt-8 text-center text-gray-400 border-t border-gray-800">
                <p>&copy; <?php echo date('Y'); ?> ShopEase. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Mobile Menu (Hidden by default) -->
    <div class="fixed inset-0 z-50 hidden bg-black bg-opacity-75 backdrop-blur-sm md:hidden" id="mobileMenu">
        <div class="w-4/5 h-full max-w-sm p-6 overflow-y-auto bg-white">
            <div class="flex items-center justify-between mb-8">
                <a href="index.php" class="flex items-center">
                    <i class="text-2xl fas fa-shopping-bag text-primary"></i>
                    <span class="ml-2 text-xl font-bold text-gray-800">ShopEase</span>
                </a>
                <button id="closeMenu" class="p-2 text-gray-700 rounded-full hover:bg-gray-100 focus:outline-none">
                    <i class="text-xl fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-6">
                <div class="relative">
                    <input type="text" placeholder="Search for products..." 
                        class="w-full px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <button class="absolute top-0 right-0 h-full px-4 text-gray-600 hover:text-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="space-y-4">
                    <a href="index.php" class="block font-semibold text-primary">Home</a>
                    <a href="clothing.php" class="block text-gray-700 hover:text-primary">Clothing</a>
                    <a href="electronics.php" class="block text-gray-700 hover:text-primary">Electronics</a>
                    <a href="home&living.php" class="block text-gray-700 hover:text-primary">Home & Living</a>
                    <a href="sports.php" class="block text-gray-700 hover:text-primary">Sports & Fitness</a>
                    <a href="#flash-sale" class="block text-gray-700 hover:text-primary">Flash Sale</a>
                    <a href="#" class="block text-gray-700 hover:text-primary">About</a>
                    <a href="#" class="block text-gray-700 hover:text-primary">Account</a>
                    <a href="cart.php" class="flex items-center justify-between block px-3 py-2 text-gray-700 rounded-lg hover:text-primary hover:bg-gray-100">
                        <span>Cart</span>
                        <span class="flex items-center justify-center w-6 h-6 text-xs text-white rounded-full bg-primary cart-count"><?= $cart_count ?></span>
                    </a>
                    <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin']): ?>
                        <a href="logout.php" class="block text-gray-700 hover:text-primary">Logout</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobileMenuButton');
            const closeMenuButton = document.getElementById('closeMenu');
            const mobileMenu = document.getElementById('mobileMenu');
            const notification = document.getElementById('notification');
            
            if (mobileMenuButton && closeMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                });
                
                closeMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                });
                
                // Close menu when clicking outside
                mobileMenu.addEventListener('click', function(e) {
                    if (e.target === mobileMenu) {
                        mobileMenu.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    }
                });
            }
            
            // Enhanced countdown timer with animation
            const updateTimer = () => {
                const timerHours = document.getElementById('hours');
                const timerMinutes = document.getElementById('minutes');
                const timerSeconds = document.getElementById('seconds');
                
                // Using the static values from PHP
                const hours = "00";
                const minutes = "16";
                const seconds = "43";
                
                // Set the timer values with animation
                if(timerHours) {
                    timerHours.textContent = hours;
                    animateTimerUpdate(timerHours);
                }
                
                if(timerMinutes) {
                    timerMinutes.textContent = minutes;
                    animateTimerUpdate(timerMinutes);
                }
                
                if(timerSeconds) {
                    timerSeconds.textContent = seconds;
                    animateTimerUpdate(timerSeconds);
                }
            };
            
            // Function to animate the timer update
            const animateTimerUpdate = (element) => {
                element.style.transform = 'translateY(-10px)';
                element.style.opacity = '0';
                
                setTimeout(() => {
                    element.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
                    element.style.transform = 'translateY(0)';
                    element.style.opacity = '1';
                }, 100);
                
                setTimeout(() => {
                    element.style.transition = '';
                }, 600);
            };
            
            // Initialize timer
            updateTimer();
            
            // Add pulsing effect to the flash sale section
            const flashSaleSection = document.getElementById('flash-sale');
            if(flashSaleSection) {
                setTimeout(() => {
                    flashSaleSection.classList.add('attention-pulse');
                    setTimeout(() => {
                        flashSaleSection.classList.remove('attention-pulse');
                    }, 1500);
                }, 3000);
            }
            
            // Additional animation for flash sale products
            const flashSaleProducts = document.querySelectorAll('.flash-sale-product');
            if(flashSaleProducts.length) {
                flashSaleProducts.forEach((product, index) => {
                    setTimeout(() => {
                        product.classList.add('attention-pulse');
                        setTimeout(() => {
                            product.classList.remove('attention-pulse');
                        }, 1000);
                    }, 3000 + (index * 300));
                });
            }
            
            // Show notification function
            function showNotification(message, details = '', isError = false) {
                if (notification) {
                    document.getElementById('notification-message').textContent = message;
                    document.getElementById('notification-details').textContent = details;
                    
                    // Add or remove error class
                    if (isError) {
                        notification.classList.add('error');
                    } else {
                        notification.classList.remove('error');
                    }
                    
                    notification.classList.add('show');
                    
                    // Hide notification after 3 seconds
                    setTimeout(function() {
                        notification.classList.remove('show');
                    }, 3000);
                }
            }

            // Updated Add to cart functionality using AJAX
            const addToCartButtons = document.querySelectorAll('.add-to-cart-btn, .flash-cart-btn');
            
            addToCartButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Get product data from data attributes
                    const productId = this.dataset.id;
                    const productName = this.dataset.name;
                    const productPrice = this.dataset.price;
                    const productCategory = this.dataset.category;
                    const productImage = this.dataset.image;
                    
                    // Add visual feedback to show the button is processing
                    this.classList.add('animate-pulse');
                    this.disabled = true;
                    
                    // Create form data for the request
                    const formData = new FormData();
                    formData.append('product_id', productId);
                    formData.append('product_name', productName);
                    formData.append('product_price', productPrice);
                    formData.append('product_category', productCategory);
                    formData.append('product_image', productImage);
                    
                    // Create AJAX request using fetch API
                    fetch('add_to_cart.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Remove visual feedback
                        this.classList.remove('animate-pulse');
                        this.disabled = false;
                        
                        if (data.success) {
                            // Update all cart count elements
                            const cartCountElements = document.querySelectorAll('.cart-count');
                            cartCountElements.forEach(element => {
                                element.textContent = data.cart_count;
                            });
                                                       // Show success notification
                            showNotification(`${productName} added to cart!`, `$${productPrice} • ${productCategory}`, false);
                            
                            // Add success animation
                            this.classList.add('shadow-glow');
                            setTimeout(() => {
                                this.classList.remove('shadow-glow');
                            }, 1000);
                        } else {
                            // Show error notification
                            showNotification(data.message || 'Error adding item to cart. Please try again.', '', true);
                        }
                    })
                    .catch(error => {
                        // Remove visual feedback
                        this.classList.remove('animate-pulse');
                        this.disabled = false;
                        
                        console.error('Error:', error);
                        showNotification('Error adding item to cart. Please try again.', '', true);
                        
                        // If session expired, redirect to login
                        if (error.message && error.message.includes('login.php')) {
                            window.location.href = 'login.php?redirect=index.php';
                        }
                    });
                });
            });
            
            // Check for success messages
            <?php if(isset($_SESSION['success_message'])): ?>
                showNotification("<?= htmlspecialchars($_SESSION['success_message']) ?>");
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            // Check for error messages
            <?php if(isset($_SESSION['error_message'])): ?>
                showNotification("<?= htmlspecialchars($_SESSION['error_message']) ?>", '', true);
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            // Smooth scroll to flash sale section
            document.querySelectorAll('a[href="#flash-sale"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    
                    const flashSaleSection = document.getElementById('flash-sale');
                    if (flashSaleSection) {
                        // Close mobile menu if open
                        if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                            mobileMenu.classList.add('hidden');
                            document.body.classList.remove('overflow-hidden');
                        }
                        
                        // Scroll to flash sale section
                        flashSaleSection.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });
            
            // Animated timer countdown effect - just for visual effect
            function simulateTimerAnimation() {
                const secondsEl = document.getElementById('seconds');
                const minutesEl = document.getElementById('minutes');
                const hoursEl = document.getElementById('hours');
                
                if (secondsEl && minutesEl && hoursEl) {
                    setInterval(() => {
                        // Apply pulse effect to seconds unit
                        secondsEl.parentElement.classList.add('attention-pulse');
                        setTimeout(() => {
                            secondsEl.parentElement.classList.remove('attention-pulse');
                        }, 800);
                    }, 5000);
                }
            }
            
            // Initialize timer animation
            simulateTimerAnimation();
        });
    </script>
</body>
</html>