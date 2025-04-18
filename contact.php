<?php
session_start();

// Include database connection
require_once 'dbconnect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - ShopEase</title>
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
                        'glow': 'glow 2s ease-in-out infinite alternate',
                    },
                    boxShadow: {
                        'glow': '0 0 15px rgba(59, 130, 246, 0.5)',
                        'glow-md': '0 0 20px rgba(59, 130, 246, 0.7)',
                        'glow-lg': '0 0 25px rgba(59, 130, 246, 0.9)',
                    },
                    keyframes: {
                        glow: {
                            '0%': { 'box-shadow': '0 0 5px rgba(59, 130, 246, 0.5)' },
                            '100%': { 'box-shadow': '0 0 20px rgba(59, 130, 246, 0.9)' }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .contact-bg {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .contact-card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
        }
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.2);
        }
        .input-field {
            transition: all 0.3s ease;
            border: 1px solid #d1d5db;
        }
        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2), 0 0 15px rgba(59, 130, 246, 0.3);
        }
        .contact-icon {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }
        .form-label {
            color: #374151;
            font-weight: 500;
        }
        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.6);
        }
        .text-primary {
            color: #2563eb;
        }
        .border-primary {
            border-color: #2563eb;
        }
        /* Navbar hover effect */
        .nav-link {
            position: relative;
            padding-bottom: 4px;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: #3B82F6;
            transition: width 0.3s ease;
        }
        .nav-link:hover::after {
            width: 100%;
        }
        .nav-link:hover {
            color: #3B82F6;
        }
        /* Glowing border effect */
        .glow-border {
            position: relative;
        }
        .glow-border::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            z-index: -1;
            background: linear-gradient(45deg, #3B82F6, #10B981, #3B82F6);
            background-size: 200% 200%;
            border-radius: inherit;
            opacity: 0;
            transition: opacity 0.3s, box-shadow 0.3s;
            animation: gradientBG 5s ease infinite;
        }
        .glow-border:hover::before {
            opacity: 0.7;
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.6);
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Include global navigation -->
    <?php include 'nav.php'; ?>

    <!-- Contact Hero Section -->
    <section class="py-20 text-white contact-bg md:py-32">
        <div class="px-4 mx-auto text-center max-w-7xl">
            <h1 class="mb-6 text-4xl font-bold md:text-6xl">Get In Touch</h1>
            <p class="max-w-2xl mx-auto mb-8 text-xl md:text-2xl">We're here to help and answer any questions you might have.</p>
            <a href="#contact-form" class="inline-flex items-center px-6 py-3 font-semibold text-white transition duration-300 rounded-lg btn-primary hover:shadow-lg glow-border">
                <i class="mr-2 fas fa-paper-plane"></i> Send Us a Message
            </a>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section id="contact-form" class="px-4 py-16 mx-auto max-w-7xl">
        <div class="grid grid-cols-1 gap-12 md:grid-cols-2">
            <div class="p-8 bg-white rounded-lg contact-card glow-border">
                <h2 class="mb-6 text-2xl font-bold text-gray-800">Send us a message</h2>
                
                <?php if(isset($_SESSION['contact_message'])): ?>
                    <div class="p-4 mb-6 text-green-800 bg-green-100 border border-green-200 rounded-lg">
                        <?= htmlspecialchars($_SESSION['contact_message']) ?>
                        <?php unset($_SESSION['contact_message']); ?>
                    </div>
                <?php endif; ?>
                
                <form action="process_contact.php" method="POST" class="space-y-6">
                    <div>
                        <label for="name" class="block mb-2 form-label">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative glow-border">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="text-lg text-gray-400 transition-colors duration-300 fas fa-user group-focus-within:text-primary"></i>
                            </div>
                            <input type="text" id="name" name="name" required 
                                   class="w-full py-3 pl-10 pr-4 rounded-lg input-field focus:outline-none focus:ring-2 focus:ring-blue-100 group"
                                   placeholder="Enter your full name">
                        </div>
                    </div>
                    
                    <div>
                        <label for="email" class="block mb-2 form-label">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <div class="relative glow-border">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="text-lg text-gray-400 transition-colors duration-300 fas fa-envelope group-focus-within:text-primary"></i>
                            </div>
                            <input type="email" id="email" name="email" required 
                                   class="w-full py-3 pl-10 pr-4 rounded-lg input-field focus:outline-none focus:ring-2 focus:ring-blue-100 group"
                                   placeholder="your.email@example.com">
                        </div>
                    </div>
                    
                    <div>
                        <label for="subject" class="block mb-2 form-label">
                            Subject <span class="text-red-500">*</span>
                        </label>
                        <div class="relative glow-border">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="text-lg text-gray-400 transition-colors duration-300 fas fa-tag group-focus-within:text-primary"></i>
                            </div>
                            <input type="text" id="subject" name="subject" required 
                                   class="w-full py-3 pl-10 pr-4 rounded-lg input-field focus:outline-none focus:ring-2 focus:ring-blue-100 group"
                                   placeholder="What's this about?">
                        </div>
                    </div>
                    
                    <div>
                        <label for="message" class="block mb-2 form-label">
                            Your Message <span class="text-red-500">*</span>
                        </label>
                        <div class="relative glow-border">
                            <div class="absolute top-3 left-3">
                                <i class="text-lg text-gray-400 transition-colors duration-300 fas fa-comment-dots group-focus-within:text-primary"></i>
                            </div>
                            <textarea id="message" name="message" rows="5" required 
                                   class="w-full py-3 pl-10 pr-4 rounded-lg input-field focus:outline-none focus:ring-2 focus:ring-blue-100 group"
                                   placeholder="Type your message here..."></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full px-6 py-3 font-semibold text-white rounded-lg btn-primary hover:shadow-lg glow-border">
                        <i class="mr-2 fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
            
            <div class="space-y-8">
                <div class="p-8 bg-white rounded-lg contact-card glow-border">
                    <h2 class="mb-6 text-2xl font-bold text-gray-800">Contact Information</h2>
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="flex items-center justify-center w-12 h-12 mr-4 text-white rounded-full contact-icon">
                                <i class="text-xl fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Our Location</h3>
                                <p class="text-gray-600">123 Shopping Street, Retail District</p>
                                <p class="text-gray-600">Cityville, CV 12345</p>
                                <a href="#" class="inline-flex items-center mt-2 text-primary hover:underline">
                                    <i class="mr-1 fas fa-directions"></i> Get Directions
                                </a>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex items-center justify-center w-12 h-12 mr-4 text-white rounded-full contact-icon">
                                <i class="text-xl fas fa-phone"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Phone Numbers</h3>
                                <p class="text-gray-600">+1 (555) 123-4567 (Sales)</p>
                                <p class="text-gray-600">+1 (555) 987-6543 (Support)</p>
                                <p class="mt-2 text-sm text-gray-500">
                                    <i class="mr-1 fas fa-clock text-primary"></i> Mon-Fri: 9am-6pm EST
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex items-center justify-center w-12 h-12 mr-4 text-white rounded-full contact-icon">
                                <i class="text-xl fas fa-envelope"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Email Addresses</h3>
                                <p class="text-gray-600">support@shopease.com</p>
                                <p class="text-gray-600">sales@shopease.com</p>
                                <p class="text-gray-600">help@shopease.com</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="p-8 bg-white rounded-lg contact-card glow-border">
                    <h2 class="mb-6 text-2xl font-bold text-gray-800">Business Hours</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-3 border-b border-gray-200">
                            <span class="flex items-center font-medium text-gray-700">
                                <i class="mr-2 fas fa-calendar-day text-primary"></i> Monday - Friday
                            </span>
                            <span class="font-medium text-gray-600">9:00 AM - 6:00 PM</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-gray-200">
                            <span class="flex items-center font-medium text-gray-700">
                                <i class="mr-2 fas fa-calendar-week text-primary"></i> Saturday
                            </span>
                            <span class="font-medium text-gray-600">10:00 AM - 4:00 PM</span>
                        </div>
                        <div class="flex items-center justify-between py-3">
                            <span class="flex items-center font-medium text-gray-700">
                                <i class="mr-2 fas fa-calendar-times text-primary"></i> Sunday
                            </span>
                            <span class="font-medium text-gray-600">Closed</span>
                        </div>
                    </div>
                    <div class="p-4 mt-6 text-center text-white rounded-lg bg-gradient-to-r from-primary to-secondary glow-border">
                        <i class="mr-2 fas fa-exclamation-circle"></i> Closed on major holidays
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="px-4 py-8 mx-auto max-w-7xl">
        <div class="overflow-hidden rounded-lg shadow-xl glow-border">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.215573291234!2d-73.98784492416463!3d40.74844097138988!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c259a9b3117469%3A0xd134e199a405a163!2sEmpire%20State%20Building!5e0!3m2!1sen!2sus!4v1689876543210!5m2!1sen!2sus" 
                    width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="px-4 py-16 mx-auto max-w-7xl">
        <div class="p-8 text-center text-white rounded-xl bg-gradient-to-r from-primary to-secondary glow-border">
            <h2 class="mb-4 text-3xl font-bold">Need Immediate Assistance?</h2>
            <p class="mb-6 text-xl">Our customer service team is ready to help you!</p>
            <div class="flex flex-col justify-center space-y-4 md:flex-row md:space-y-0 md:space-x-4">
                <a href="tel:+15551234567" class="flex items-center justify-center px-6 py-3 font-semibold text-white transition duration-300 bg-white rounded-lg bg-opacity-20 hover:bg-opacity-30 hover:shadow-md glow-border">
                    <i class="mr-2 fas fa-phone"></i> Call Now: (555) 123-4567
                </a>
                <a href="mailto:support@shopease.com" class="flex items-center justify-center px-6 py-3 font-semibold text-white transition duration-300 bg-white rounded-lg bg-opacity-20 hover:bg-opacity-30 hover:shadow-md glow-border">
                    <i class="mr-2 fas fa-envelope"></i> Email Us
                </a>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="px-4 py-16 mx-auto max-w-7xl">
        <div class="text-center">
            <h2 class="mb-2 text-3xl font-bold text-gray-800">Frequently Asked Questions</h2>
            <p class="mb-12 text-gray-600">Find quick answers to common questions below</p>
        </div>
        
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
            <div class="p-6 bg-white rounded-lg contact-card glow-border">
                <h3 class="mb-4 text-xl font-semibold text-gray-800">
                    <i class="mr-2 fas fa-shipping-fast text-primary"></i> Shipping & Delivery
                </h3>
                <div class="space-y-4">
                    <div class="border-b border-gray-200">
                        <button class="flex items-center justify-between w-full py-3 font-medium text-left text-gray-800 focus:outline-none group">
                            <span class="transition-colors duration-300 group-hover:text-primary">
                                <i class="mr-2 fas fa-question-circle text-primary"></i> How long does shipping take?
                            </span>
                            <i class="text-gray-400 transition-colors duration-300 fas fa-chevron-down group-hover:text-primary"></i>
                        </button>
                        <div class="hidden pb-4 text-gray-600">
                            Standard shipping typically takes 3-5 business days within the continental US. Expedited options are available at checkout.
                        </div>
                    </div>
                    
                    <div class="border-b border-gray-200">
                        <button class="flex items-center justify-between w-full py-3 font-medium text-left text-gray-800 focus:outline-none group">
                            <span class="transition-colors duration-300 group-hover:text-primary">
                                <i class="mr-2 fas fa-globe text-primary"></i> Do you offer international shipping?
                            </span>
                            <i class="text-gray-400 transition-colors duration-300 fas fa-chevron-down group-hover:text-primary"></i>
                        </button>
                        <div class="hidden pb-4 text-gray-600">
                            Yes, we ship to most countries worldwide. Shipping costs and delivery times vary by destination and will be calculated at checkout.
                        </div>
                    </div>
                    
                    <div class="border-b border-gray-200">
                        <button class="flex items-center justify-between w-full py-3 font-medium text-left text-gray-800 focus:outline-none group">
                            <span class="transition-colors duration-300 group-hover:text-primary">
                                <i class="mr-2 fas fa-map-marked-alt text-primary"></i> How can I track my order?
                            </span>
                            <i class="text-gray-400 transition-colors duration-300 fas fa-chevron-down group-hover:text-primary"></i>
                        </button>
                        <div class="hidden pb-4 text-gray-600">
                            Once your order ships, you'll receive a tracking number via email. You can also check order status in your account dashboard.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-6 bg-white rounded-lg contact-card glow-border">
                <h3 class="mb-4 text-xl font-semibold text-gray-800">
                    <i class="mr-2 fas fa-exchange-alt text-primary"></i> Returns & Exchanges
                </h3>
                <div class="space-y-4">
                    <div class="border-b border-gray-200">
                        <button class="flex items-center justify-between w-full py-3 font-medium text-left text-gray-800 focus:outline-none group">
                            <span class="transition-colors duration-300 group-hover:text-primary">
                                <i class="mr-2 fas fa-undo-alt text-primary"></i> What is your return policy?
                            </span>
                            <i class="text-gray-400 transition-colors duration-300 fas fa-chevron-down group-hover:text-primary"></i>
                        </button>
                        <div class="hidden pb-4 text-gray-600">
                            We accept returns within 30 days of purchase. Items must be unused, in original packaging with all tags attached. Some exclusions apply.
                        </div>
                    </div>
                    
                    <div class="border-b border-gray-200">
                        <button class="flex items-center justify-between w-full py-3 font-medium text-left text-gray-800 focus:outline-none group">
                            <span class="transition-colors duration-300 group-hover:text-primary">
                                <i class="mr-2 fas fa-arrow-circle-right text-primary"></i> How do I initiate a return?
                            </span>
                            <i class="text-gray-400 transition-colors duration-300 fas fa-chevron-down group-hover:text-primary"></i>
                        </button>
                        <div class="hidden pb-4 text-gray-600">
                            Log into your account to start the return process or contact our customer service team for assistance with guest orders.
                        </div>
                    </div>
                    
                    <div class="border-b border-gray-200">
                        <button class="flex items-center justify-between w-full py-3 font-medium text-left text-gray-800 focus:outline-none group">
                            <span class="transition-colors duration-300 group-hover:text-primary">
                                <i class="mr-2 fas fa-money-bill-wave text-primary"></i> When will I get my refund?
                            </span>
                            <i class="text-gray-400 transition-colors duration-300 fas fa-chevron-down group-hover:text-primary"></i>
                        </button>
                        <div class="hidden pb-4 text-gray-600">
                            Refunds are processed within 3-5 business days after we receive your return. It may take additional time for your bank to post the credit.
                        </div>
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
                        <a href="#" class="p-2 text-gray-400 transition-colors duration-300 rounded-full hover:text-white hover:bg-gray-800 glow-border">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="p-2 text-gray-400 transition-colors duration-300 rounded-full hover:text-white hover:bg-gray-800 glow-border">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="p-2 text-gray-400 transition-colors duration-300 rounded-full hover:text-white hover:bg-gray-800 glow-border">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="mb-4 text-lg font-semibold">Shop</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="flex items-center text-gray-400 transition-colors duration-300 hover:text-white">
                            <i class="w-4 mr-2 fas fa-chevron-right text-primary"></i> All Products
                        </a></li>
                        <li><a href="#" class="flex items-center text-gray-400 transition-colors duration-300 hover:text-white">
                            <i class="w-4 mr-2 fas fa-chevron-right text-primary"></i> Featured
                        </a></li>
                        <li><a href="#" class="flex items-center text-gray-400 transition-colors duration-300 hover:text-white">
                            <i class="w-4 mr-2 fas fa-chevron-right text-primary"></i> New Arrivals
                        </a></li>
                        <li><a href="#flash-sale" class="flex items-center text-gray-400 transition-colors duration-300 hover:text-white">
                            <i class="w-4 mr-2 fas fa-chevron-right text-primary"></i> Flash Sale
                        </a></li>
                        <li><a href="#" class="flex items-center text-gray-400 transition-colors duration-300 hover:text-white">
                            <i class="w-4 mr-2 fas fa-chevron-right text-primary"></i> Gift Cards
                        </a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-4 text-lg font-semibold">Customer Service</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="flex items-center text-gray-400 transition-colors duration-300 hover:text-white">
                            <i class="w-4 mr-2 fas fa-chevron-right text-primary"></i> Contact Us
                        </a></li>
                        <li><a href="#" class="flex items-center text-gray-400 transition-colors duration-300 hover:text-white">
                            <i class="w-4 mr-2 fas fa-chevron-right text-primary"></i> FAQs
                        </a></li>
                        <li><a href="#" class="flex items-center text-gray-400 transition-colors duration-300 hover:text-white">
                            <i class="w-4 mr-2 fas fa-chevron-right text-primary"></i> Shipping Policy
                        </a></li>
                        <li><a href="#" class="flex items-center text-gray-400 transition-colors duration-300 hover:text-white">
                            <i class="w-4 mr-2 fas fa-chevron-right text-primary"></i> Returns & Refunds
                        </a></li>
                        <li><a href="#" class="flex items-center text-gray-400 transition-colors duration-300 hover:text-white">
                            <i class="w-4 mr-2 fas fa-chevron-right text-primary"></i> Track Order
                        </a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-4 text-lg font-semibold">About</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="flex items-center text-gray-400 transition-colors duration-300 hover:text-white">
                            <i class="w-4 mr-2 fas fa-chevron-right text-primary"></i> Our Story
                        </a></li>
                        <li><a href="#" class="flex items-center text-gray-400 transition-colors duration-300 hover:text-white">
                            <i class="w-4 mr-2 fas fa-chevron-right text-primary"></i> Careers
                        </a></li>
                        <li><a href="#" class="flex items-center text-gray-400 transition-colors duration-300 hover:text-white">
                            <i class="w-4 mr-2 fas fa-chevron-right text-primary"></i> Terms & Conditions
                        </a></li>
                        <li><a href="#" class="flex items-center text-gray-400 transition-colors duration-300 hover:text-white">
                            <i class="w-4 mr-2 fas fa-chevron-right text-primary"></i> Privacy Policy
                        </a></li>
                        <li><a href="#" class="flex items-center text-gray-400 transition-colors duration-300 hover:text-white">
                            <i class="w-4 mr-2 fas fa-chevron-right text-primary"></i> Blog
                        </a></li>
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
            // FAQ accordion functionality
            const faqButtons = document.querySelectorAll('.border-b button');
            
            faqButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const answer = this.nextElementSibling;
                    const icon = this.querySelector('i.fa-chevron-down, i.fa-chevron-up');
                    
                    // Toggle answer visibility
                    answer.classList.toggle('hidden');
                    
                    // Toggle icon
                    if (icon.classList.contains('fa-chevron-down')) {
                        icon.classList.remove('fa-chevron-down');
                        icon.classList.add('fa-chevron-up');
                    } else {
                        icon.classList.remove('fa-chevron-up');
                        icon.classList.add('fa-chevron-down');
                    }
                });
            });

            // Form input interactions
            const inputs = document.querySelectorAll('.input-field');
            inputs.forEach(input => {
                const icon = input.parentElement.querySelector('i');
                
                input.addEventListener('focus', function() {
                    icon.classList.add('text-primary');
                    this.parentElement.classList.add('glow-active');
                });
                
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        icon.classList.remove('text-primary');
                    }
                    this.parentElement.classList.remove('glow-active');
                });
            });
            
            // Add glow effect on hover for all glow-border elements
            const glowElements = document.querySelectorAll('.glow-border');
            glowElements.forEach(el => {
                el.addEventListener('mouseenter', function() {
                    this.classList.add('glow-active');
                });
                el.addEventListener('mouseleave', function() {
                    this.classList.remove('glow-active');
                });
            });
        });
    </script>
</body>
</html>