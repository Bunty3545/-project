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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - ShopEase</title>
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
                    }
                }
            }
        }
    </script>
    <style>
        .hero {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1522071820081-009f0129c71c');
            background-size: cover;
            background-position: center;
        }
        .team-member {
            transition: all 0.3s ease;
            border-radius: 1rem;
            overflow: hidden;
        }
        .team-member:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.1);
        }
        .feature-card {
            transition: all 0.3s ease;
            border-radius: 1rem;
            overflow: hidden;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .timeline-item {
            position: relative;
            padding-left: 3rem;
            margin-bottom: 2.5rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0.75rem;
            top: 0;
            height: 100%;
            width: 2px;
            background: #3B82F6;
        }
        .timeline-dot {
            position: absolute;
            left: 0;
            top: 0;
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            background: #3B82F6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .values-section {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            position: relative;
        }
        .values-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://www.transparenttextures.com/patterns/subtle-white-feathers.png');
            opacity: 0.5;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'nav.php'; ?>

    <!-- Hero Section -->
    <section class="py-20 text-white hero md:py-32">
        <div class="px-4 mx-auto text-center max-w-7xl">
            <h1 class="mb-6 text-4xl font-bold md:text-6xl">About ShopEase</h1>
            <p class="max-w-2xl mx-auto mb-8 text-xl md:text-2xl">Your trusted online shopping destination since 2015</p>
        </div>
    </section>

    <!-- Our Story -->
    <section class="px-4 py-16 mx-auto max-w-7xl">
        <div class="grid grid-cols-1 gap-12 md:grid-cols-2">
            <div>
                <h2 class="mb-6 text-3xl font-bold text-gray-800">Our Story</h2>
                <p class="mb-4 text-lg text-gray-600">Founded in 2015, ShopEase began as a small startup with a big vision: to revolutionize online shopping by making it simpler, faster, and more enjoyable.</p>
                <p class="mb-4 text-lg text-gray-600">What started as a modest e-commerce platform has grown into one of the most trusted online shopping destinations, serving millions of customers worldwide.</p>
                <p class="mb-6 text-lg text-gray-600">Our journey has been fueled by innovation, customer satisfaction, and a commitment to providing the best products at competitive prices.</p>
            </div>
            <div class="flex items-center justify-center">
                <div class="w-full p-8 bg-white rounded-lg shadow-xl">
                    <div class="flex items-center justify-center w-32 h-32 mx-auto mb-6 text-white rounded-full bg-primary">
                        <i class="text-5xl fas fa-store"></i>
                    </div>
                    <h3 class="text-xl font-bold text-center text-gray-800">From Humble Beginnings</h3>
                    <p class="mt-2 text-center text-gray-600">Started with just 5 employees and a passion for e-commerce</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Mission -->
    <section class="px-4 py-16 mx-auto text-white bg-primary max-w-7xl rounded-2xl">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="mb-6 text-3xl font-bold">Our Mission</h2>
            <p class="mb-6 text-xl">To provide an exceptional online shopping experience by offering high-quality products, competitive prices, and outstanding customer service.</p>
            <div class="grid grid-cols-1 gap-6 mt-12 md:grid-cols-3">
                <div class="p-6 bg-white bg-opacity-10 rounded-xl backdrop-blur-sm">
                    <i class="mb-4 text-4xl fas fa-shipping-fast"></i>
                    <h3 class="mb-2 text-xl font-bold">Fast Delivery</h3>
                    <p class="text-gray-200">Get your orders delivered in record time with our efficient logistics network.</p>
                </div>
                <div class="p-6 bg-white bg-opacity-10 rounded-xl backdrop-blur-sm">
                    <i class="mb-4 text-4xl fas fa-hand-holding-usd"></i>
                    <h3 class="mb-2 text-xl font-bold">Best Prices</h3>
                    <p class="text-gray-200">We guarantee the best prices or we'll match it with our price match policy.</p>
                </div>
                <div class="p-6 bg-white bg-opacity-10 rounded-xl backdrop-blur-sm">
                    <i class="mb-4 text-4xl fas fa-headset"></i>
                    <h3 class="mb-2 text-xl font-bold">24/7 Support</h3>
                    <p class="text-gray-200">Our customer service team is always ready to assist you with any questions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values -->
    <section class="px-4 py-16 mx-auto max-w-7xl values-section">
        <div class="text-center">
            <h2 class="mb-12 text-3xl font-bold text-gray-800">Our Core Values</h2>
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <div class="p-8 bg-white rounded-lg shadow-md feature-card">
                    <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 text-white rounded-full bg-primary">
                        <i class="text-2xl fas fa-users"></i>
                    </div>
                    <h3 class="mb-4 text-xl font-bold text-gray-800">Customer First</h3>
                    <p class="text-gray-600">We put our customers at the heart of everything we do, ensuring their satisfaction is our top priority.</p>
                </div>
                <div class="p-8 bg-white rounded-lg shadow-md feature-card">
                    <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 text-white rounded-full bg-primary">
                        <i class="text-2xl fas fa-lightbulb"></i>
                    </div>
                    <h3 class="mb-4 text-xl font-bold text-gray-800">Innovation</h3>
                    <p class="text-gray-600">We continuously seek new ways to improve and enhance your shopping experience.</p>
                </div>
                <div class="p-8 bg-white rounded-lg shadow-md feature-card">
                    <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 text-white rounded-full bg-primary">
                        <i class="text-2xl fas fa-shield-alt"></i>
                    </div>
                    <h3 class="mb-4 text-xl font-bold text-gray-800">Integrity</h3>
                    <p class="text-gray-600">We conduct our business with honesty, transparency, and ethical practices.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Timeline -->
    <section class="px-4 py-16 mx-auto max-w-7xl">
        <h2 class="mb-12 text-3xl font-bold text-center text-gray-800">Our Journey</h2>
        <div class="max-w-3xl mx-auto">
            <div class="timeline-item">
                <div class="timeline-dot">
                    <i class="fas fa-rocket"></i>
                </div>
                <div class="pl-6">
                    <h3 class="mb-2 text-xl font-bold text-gray-800">2015 - Founded</h3>
                    <p class="text-gray-600">ShopEase was launched with just 5 employees and a small warehouse in San Francisco.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-dot">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="pl-6">
                    <h3 class="mb-2 text-xl font-bold text-gray-800">2017 - First Milestone</h3>
                    <p class="text-gray-600">Reached 100,000 customers and expanded our product catalog to 10,000 items.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-dot">
                    <i class="fas fa-globe"></i>
                </div>
                <div class="pl-6">
                    <h3 class="mb-2 text-xl font-bold text-gray-800">2019 - International Expansion</h3>
                    <p class="text-gray-600">Began shipping to customers in Europe and Asia, opening new fulfillment centers.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-dot">
                    <i class="fas fa-award"></i>
                </div>
                <div class="pl-6">
                    <h3 class="mb-2 text-xl font-bold text-gray-800">2021 - Award Winning</h3>
                    <p class="text-gray-600">Recognized as "Best E-commerce Platform" by the Retail Excellence Awards.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-dot">
                    <i class="fas fa-star"></i>
                </div>
                <div class="pl-6">
                    <h3 class="mb-2 text-xl font-bold text-gray-800">2023 - Today</h3>
                    <p class="text-gray-600">Serving over 5 million happy customers worldwide with 500+ employees.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Team -->
    <section class="px-4 py-16 mx-auto max-w-7xl">
        <h2 class="mb-12 text-3xl font-bold text-center text-gray-800">Meet Our Leadership</h2>
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <div class="p-8 bg-white rounded-lg shadow-md team-member">
                <div class="flex items-center justify-center w-32 h-32 mx-auto mb-6 text-white rounded-full bg-primary">
                    <i class="text-4xl fas fa-user-tie"></i>
                </div>
                <div class="text-center">
                    <h3 class="text-xl font-bold text-gray-800">John Smith</h3>
                    <p class="mb-2 text-primary">Founder & CEO</p>
                    <p class="mb-4 text-gray-600">Visionary leader with 15+ years in e-commerce and technology.</p>
                    <div class="flex justify-center space-x-3">
                        <a href="#" class="text-gray-500 hover:text-primary"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-gray-500 hover:text-primary"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <div class="p-8 bg-white rounded-lg shadow-md team-member">
                <div class="flex items-center justify-center w-32 h-32 mx-auto mb-6 text-white rounded-full bg-primary">
                    <i class="text-4xl fas fa-user-graduate"></i>
                </div>
                <div class="text-center">
                    <h3 class="text-xl font-bold text-gray-800">Sarah Johnson</h3>
                    <p class="mb-2 text-primary">Chief Operating Officer</p>
                    <p class="mb-4 text-gray-600">Operations expert focused on efficiency and customer satisfaction.</p>
                    <div class="flex justify-center space-x-3">
                        <a href="#" class="text-gray-500 hover:text-primary"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-gray-500 hover:text-primary"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <div class="p-8 bg-white rounded-lg shadow-md team-member">
                <div class="flex items-center justify-center w-32 h-32 mx-auto mb-6 text-white rounded-full bg-primary">
                    <i class="text-4xl fas fa-laptop-code"></i>
                </div>
                <div class="text-center">
                    <h3 class="text-xl font-bold text-gray-800">Michael Chen</h3>
                    <p class="mb-2 text-primary">Chief Technology Officer</p>
                    <p class="mb-4 text-gray-600">Tech innovator driving our platform's cutting-edge features.</p>
                    <div class="flex justify-center space-x-3">
                        <a href="#" class="text-gray-500 hover:text-primary"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-gray-500 hover:text-primary"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <div class="p-8 bg-white rounded-lg shadow-md team-member">
                <div class="flex items-center justify-center w-32 h-32 mx-auto mb-6 text-white rounded-full bg-primary">
                    <i class="text-4xl fas fa-bullhorn"></i>
                </div>
                <div class="text-center">
                    <h3 class="text-xl font-bold text-gray-800">Emma Rodriguez</h3>
                    <p class="mb-2 text-primary">Chief Marketing Officer</p>
                    <p class="mb-4 text-gray-600">Brand strategist connecting us with customers worldwide.</p>
                    <div class="flex justify-center space-x-3">
                        <a href="#" class="text-gray-500 hover:text-primary"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-gray-500 hover:text-primary"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="px-4 py-16 mx-auto max-w-7xl">
        <div class="p-8 text-center bg-white shadow-xl rounded-xl">
            <h2 class="mb-4 text-3xl font-bold text-gray-800">Ready to Shop With Us?</h2>
            <p class="max-w-2xl mx-auto mb-8 text-gray-600">Join millions of happy customers who trust ShopEase for their online shopping needs.</p>
            <a href="index.php" class="inline-block px-8 py-3 font-semibold text-white transition duration-300 transform rounded-full bg-primary hover:bg-secondary hover:scale-105">Start Shopping</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 text-white bg-gray-900">
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
                        <li><a href="index.php" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="about.php" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="index.php#flash-sale" class="text-gray-400 hover:text-white">Flash Sale</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact</a></li>
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
                        <li class="flex items-center text-gray-400"><i class="mr-2 fas fa-map-marker-alt"></i> 123 Street, San Francisco, CA</li>
                        <li class="flex items-center text-gray-400"><i class="mr-2 fas fa-phone"></i> +1 234 567 890</li>
                        <li class="flex items-center text-gray-400"><i class="mr-2 fas fa-envelope"></i> info@shopease.com</li>
                    </ul>
                </div>
            </div>
            <div class="pt-8 mt-8 text-center text-gray-400 border-t border-gray-800">
                <p>&copy; <?php echo date('Y'); ?> ShopEase. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle functionality is handled by nav.php
        });
    </script>
</body>
</html>