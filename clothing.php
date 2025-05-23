<?php
session_start();
$loggedin = false;
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
} else {
    $loggedin = true;
    
    // FIX: Check if user_id exists in session
    if(!isset($_SESSION['user_id'])) {
        // Log the error for debugging
        error_log("User ID not found in session for user: " . ($_SESSION['username'] ?? 'unknown'));
        
        // Set a temporary user ID for this session only (not ideal but prevents errors)
        $_SESSION['user_id'] = 0; // Guest user ID
    }
    
    // Check if username exists in session
    if(!isset($_SESSION['username'])) {
        error_log("Username not found in session for user ID: " . $_SESSION['user_id']);
        $_SESSION['username'] = 'guest'; // Default username as fallback
    }
    
    // Now safely get the user_id and username
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
}

// CRITICAL FIX: Include database connection BEFORE using $conn
require_once 'dbconnect.php';

// CRITICAL FIX: Check if connection is established
if (!isset($conn) || $conn->connect_error) {
    error_log("Database connection failed: " . ($conn->connect_error ?? "Connection variable not set"));
    die("Connection failed. Please try again later.");
}

// Product data organized by page
$products = [
    'page1' => [
        [
            'id' => 1,
            'title' => "Men's Casual T-Shirt",
            'category' => "Men",
            'price' => 24.99,
            'old_price' => 34.99,
            'rating' => 4.5,
            'review_count' => 127,
            'image' => "https://images.unsplash.com/photo-1529374255404-311a2a4f1fd9?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80",
            'badge' => "SALE",
            'badge_color' => "bg-red-500"
        ],
        [
            'id' => 2,
            'title' => "Women's Summer Dress",
            'category' => "Women",
            'price' => 49.99,
            'rating' => 4,
            'review_count' => 89,
            'image' => "https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80",
            'badge' => "NEW",
            'badge_color' => "bg-green-500"
        ],
        [
            'id' => 3,
            'title' => "Men's Jeans",
            'category' => "Men",
            'price' => 59.99,
            'rating' => 5,
            'review_count' => 156,
            'image' => "https://images.unsplash.com/photo-1591047139829-d91aecb6caea?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80"
        ],
        [
            'id' => 4,
            'title' => "Kids T-Shirt Set",
            'category' => "Kids",
            'price' => 29.99,
            'rating' => 4.5,
            'review_count' => 72,
            'image' => "images/Kids_T-Shirt.png",
        ]
    ],
    'page2' => [
        [
            'id' => 5,
            'title' => "Women's Silk Blouse",
            'category' => "Women",
            'price' => 39.99,
            'rating' => 4,
            'review_count' => 64,
            'image' => "images/womens_silk_blouse.png"
        ],
        [
            'id' => 6,
            'title' => "Men's Winter Jacket",
            'category' => "Men",
            'price' => 89.99,
            'old_price' => 129.99,
            'rating' => 5,
            'review_count' => 203,
            'image' => "https://images.unsplash.com/photo-1527719327859-c6ce80353573?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80",
            'badge' => "-30%",
            'badge_color' => "bg-red-500"
        ],
        [
            'id' => 7,
            'title' => "Women's Cashmere Sweater",
            'category' => "Women",
            'price' => 79.99,
            'rating' => 4.5,
            'review_count' => 98,
            'image' => "images/womens_cashmere_sweater.png"
        ],
        [
            'id' => 8,
            'title' => "Kids Winter Jacket",
            'category' => "Kids",
            'price' => 45.99,
            'rating' => 4,
            'review_count' => 42,
            'image' => "https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80",
            'badge' => "NEW",
            'badge_color' => "bg-green-500"
        ]
    ],
    'page3' => [
        [
            'id' => 9,
            'title' => "Men's Formal Shirt",
            'category' => "Men",
            'price' => 45.99,
            'rating' => 4.5,
            'review_count' => 112,
            'image' => "https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80"
        ],
        [
            'id' => 10,
            'title' => "Women's Denim Jacket",
            'category' => "Women",
            'price' => 65.99,
            'rating' => 4,
            'review_count' => 87,
            'image' => "https://images.unsplash.com/photo-1551232864-3f0890e580d9?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80",
            'badge' => "NEW",
            'badge_color' => "bg-green-500"
        ],
        [
            'id' => 11,
            'title' => "Men's Polo Shirt",
            'category' => "Men",
            'price' => 29.99,
            'rating' => 4,
            'review_count' => 76,
            'image' => "https://images.unsplash.com/photo-1576566588028-4147f3842f27?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80"
        ],
        [
            'id' => 12,
            'title' => "Women's Skirt",
            'category' => "Women",
            'price' => 35.99,
            'rating' => 4.5,
            'review_count' => 93,
            'image' => "https://images.unsplash.com/photo-1598033129183-c4f50c736f10?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80"
        ]
    ]
];

// Handle "Add to Cart" action - direct from parameters (when coming from another page)
if(isset($_GET['add_to_cart'])) {
    $product_id = $_GET['add_to_cart'];
    $product_name = isset($_GET['name']) ? $_GET['name'] : '';
    $product_price = isset($_GET['price']) ? $_GET['price'] : 0;
    $product_category = isset($_GET['category']) ? $_GET['category'] : '';
    $product_image = isset($_GET['image']) ? $_GET['image'] : '';
    
    // Check if we have all required data AND a valid user_id
    if(!empty($product_id) && !empty($product_name) && !empty($product_price) && $user_id > 0) {
        try {
            // First check if product already exists in cart
            $check_sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $user_id, $product_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if($result->num_rows > 0) {
                // Product already in cart, update quantity
                $cart_item = $result->fetch_assoc();
                $new_quantity = $cart_item['quantity'] + 1;
                
                $update_sql = "UPDATE cart SET quantity = ?, username = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("isi", $new_quantity, $username, $cart_item['id']);
                $update_stmt->execute();
                $update_stmt->close();
            } else {
                // Add new product to cart
                $insert_sql = "INSERT INTO cart (user_id, product_id, product_name, product_price, product_image, product_category, quantity, username) 
                              VALUES (?, ?, ?, ?, ?, ?, 1, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iisdsss", $user_id, $product_id, $product_name, $product_price, $product_image, $product_category, $username);
                $insert_stmt->execute();
                $insert_stmt->close();
            }
            
            // Redirect to cart page
            header("Location: cart.php");
            exit;
        } catch (Exception $e) {
            error_log("Database error in add to cart: " . $e->getMessage());
            // Display user-friendly error message
            echo "<div class='p-4 mb-4 text-red-700 bg-red-100 rounded-md'>Error adding item to cart. Please try again later.</div>";
        }
    } elseif ($user_id <= 0) {
        // User ID is invalid, redirect to login
        header("Location: login.php?error=session_expired");
        exit;
    }
} 

// Handle filtering
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$current_page = isset($_GET['page']) ? $_GET['page'] : 'page1';
$current_products = $products[$current_page] ?? $products['page1'];

// Apply filter if set
if (!empty($filter) && $filter !== 'Filter') {
    $filtered_products = [];
    foreach ($current_products as $product) {
        if ($product['category'] === $filter) {
            $filtered_products[] = $product;
        }
    }
    $current_products = $filtered_products;
}

// Apply sorting if set
if (!empty($sort) && $sort !== 'Sort by') {
    switch ($sort) {
        case 'price_low_high':
            usort($current_products, function($a, $b) {
                return $a['price'] - $b['price'];
            });
            break;
        case 'price_high_low':
            usort($current_products, function($a, $b) {
                return $b['price'] - $a['price'];
            });
            break;
        case 'newest':
            // For demo purposes, we're assuming ID correlates with newness
            usort($current_products, function($a, $b) {
                return $b['id'] - $a['id'];
            });
            break;
        case 'popular':
            usort($current_products, function($a, $b) {
                return $b['review_count'] - $a['review_count'];
            });
            break;
    }
}

// Get cart count for display in header - WRAP WITH TRY/CATCH FOR ERROR HANDLING
$cart_count = 0;
try {
    if (isset($conn) && $conn) {
        $count_sql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param("i", $user_id);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        if($count_row = $count_result->fetch_assoc()) {
            $cart_count = $count_row['total'] ? $count_row['total'] : 0;
        }
        $count_stmt->close();
    }
} catch (Exception $e) {
    error_log("Error getting cart count: " . $e->getMessage());
    $cart_count = 0; // Default to 0 on error
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clothing - ShopEase</title>
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
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .product-image {
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .product-image:hover {
            transform: scale(1.05);
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background-color: #10B981;
            color: white;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 1000;
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 0.3s, transform 0.3s;
        }
        .notification.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Notification -->
    <div id="notification" class="notification">
        <i class="mr-2 fas fa-check-circle"></i>
        <span id="notification-message">Item added to cart!</span>
    </div>

    <!-- Include the navigation bar -->
    <?php include 'nav.php'; ?>

    <!-- Clothing Products Section -->
    <section class="px-4 py-12 mx-auto max-w-7xl">
        <div class="flex flex-col items-start justify-between mb-8 md:flex-row md:items-center">
            <h2 class="mb-4 text-3xl font-bold md:mb-0">Clothing Collection</h2>
            <div class="flex flex-col w-full space-y-3 md:w-auto md:flex-row md:space-y-0 md:space-x-4">
                <form id="filterForm" method="get" action="" class="flex flex-col w-full space-y-3 md:w-auto md:flex-row md:space-y-0 md:space-x-4">
                    <input type="hidden" name="page" value="<?= htmlspecialchars($current_page) ?>">
                    <select name="sort" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">Sort by</option>
                        <option value="price_low_high" <?= $sort === 'price_low_high' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_high_low" <?= $sort === 'price_high_low' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                        <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Popular</option>
                    </select>
                    <select name="filter" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">Filter</option>
                        <option value="Men" <?= $filter === 'Men' ? 'selected' : '' ?>>Men</option>
                        <option value="Women" <?= $filter === 'Women' ? 'selected' : '' ?>>Women</option>
                        <option value="Kids" <?= $filter === 'Kids' ? 'selected' : '' ?>>Kids</option>
                    </select>
                </form>
            </div>
        </div>
        
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4" id="productsGrid">
            <?php foreach ($current_products as $product): ?>
            <div class="overflow-hidden transition duration-300 bg-white rounded-lg shadow-md product-card">
                <div class="relative">
                    <!-- Modified image container - made clickable with product data as form -->
                    <form id="product-form-<?= $product['id'] ?>" action="clothing.php" method="get" class="p-0 m-0">
                        <input type="hidden" name="add_to_cart" value="<?= $product['id'] ?>">
                        <input type="hidden" name="name" value="<?= htmlspecialchars($product['title']) ?>">
                        <input type="hidden" name="price" value="<?= $product['price'] ?>">
                        <input type="hidden" name="category" value="<?= htmlspecialchars($product['category']) ?>">
                        <input type="hidden" name="image" value="<?= htmlspecialchars($product['image']) ?>">
                        
                        <div class="cursor-pointer product-image-container" onclick="document.getElementById('product-form-<?= $product['id'] ?>').submit();">
                            <img src="<?= htmlspecialchars($product['image']) ?>" 
                                alt="<?= htmlspecialchars($product['title']) ?>" 
                                class="object-cover w-full h-64 product-image">
                        </div>
                    </form>
                    
                    <?php if (isset($product['badge'])): ?>
                    <div class="absolute px-2 py-1 text-xs font-bold text-white <?= $product['badge_color'] ?> rounded-full top-2 right-2">
                        <?= $product['badge'] ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold"><?= htmlspecialchars($product['title']) ?></h3>
                            <p class="text-gray-600"><?= htmlspecialchars($product['category']) ?></p>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-bold">$<?= number_format($product['price'], 2) ?></span>
                            <?php if (isset($product['old_price'])): ?>
                            <span class="block text-sm text-gray-500 line-through">$<?= number_format($product['old_price'], 2) ?></span>
                            <?php endif; ?>
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
                            class="px-3 py-1 text-white transition duration-300 rounded-full bg-primary hover:bg-secondary add-to-cart" 
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
        
        <?php if (empty($current_products)): ?>
        <div class="p-8 mt-4 text-center bg-white rounded-lg shadow-md">
            <p class="text-xl text-gray-600">No products found matching your criteria.</p>
            <a href="?page=<?= $current_page ?>" class="inline-block px-6 py-2 mt-4 text-white rounded-full bg-primary hover:bg-secondary">
                Clear Filters
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Pagination -->
        <div class="flex justify-center mt-12">
            <nav class="flex items-center space-x-2">
                <a href="?page=page1<?= !empty($filter) ? '&filter=' . urlencode($filter) : '' ?><?= !empty($sort) ? '&sort=' . urlencode($sort) : '' ?>" 
                   class="px-3 py-1 rounded-md hover:bg-gray-200 <?= ($current_page === 'page1') ? 'bg-primary text-white' : '' ?>">
                    1
                </a>
                <a href="?page=page2<?= !empty($filter) ? '&filter=' . urlencode($filter) : '' ?><?= !empty($sort) ? '&sort=' . urlencode($sort) : '' ?>" 
                   class="px-3 py-1 rounded-md hover:bg-gray-200 <?= ($current_page === 'page2') ? 'bg-primary text-white' : '' ?>">
                    2
                </a>
                <a href="?page=page3<?= !empty($filter) ? '&filter=' . urlencode($filter) : '' ?><?= !empty($sort) ? '&sort=' . urlencode($sort) : '' ?>" 
                   class="px-3 py-1 rounded-md hover:bg-gray-200 <?= ($current_page === 'page3') ? 'bg-primary text-white' : '' ?>">
                    3
                </a>
            </nav>
        </div>
    </section>

    <!-- Mobile Menu (Hidden by default) -->
    <div class="fixed inset-0 z-50 hidden bg-black bg-opacity-75 md:hidden" id="mobileMenu">
        <div class="w-4/5 h-full max-w-sm p-6 overflow-y-auto bg-white">
            <div class="flex items-center justify-between mb-8">
                <a href="index.php" class="flex items-center">
                    <i class="text-2xl fas fa-shopping-bag text-primary"></i>
                    <span class="ml-2 text-xl font-bold text-gray-800">ShopEase</span>
                </a>
                <button id="closeMenu" class="text-gray-700 focus:outline-none">
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
                    <a href="index.php" class="block text-gray-700 hover:text-primary">Home</a>
                    <a href="clothing.php" class="block text-gray-700 hover:text-primary">Clothing</a>
                    <a href="#" class="block text-gray-700 hover:text-primary">Categories</a>
                    <a href="#" class="block text-gray-700 hover:text-primary">About</a>
                    <a href="#" class="block text-gray-700 hover:text-primary">Account</a>
                    <a href="#" class="block text-gray-700 hover:text-primary">Wishlist</a>
                    <a href="cart.php" class="block text-gray-700 hover:text-primary">Cart (<?= $cart_count ?>)</a>
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
                });
                
                closeMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.add('hidden');
                });
            }

            // Show notification function
            function showNotification(message) {
                if (notification) {
                    document.getElementById('notification-message').textContent = message;
                    notification.classList.add('show');
                    
                    // Hide notification after 3 seconds
                    setTimeout(function() {
                        notification.classList.remove('show');
                    }, 3000);
                }
            }

            // Add to cart functionality using AJAX
            document.addEventListener('click', function(e) {
                if (e.target.closest('.add-to-cart')) {
                    e.preventDefault();
                    
                    const button = e.target.closest('.add-to-cart');
                    const productId = button.dataset.id;
                    const productName = button.dataset.name;
                    const productPrice = button.dataset.price;
                    const productCategory = button.dataset.category;
                    const productImage = button.dataset.image;
                    
                    // Create AJAX request to add item to cart
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'add_to_cart.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    
                    xhr.onload = function() {
                        if (this.status === 200) {
                            try {
                                const response = JSON.parse(this.responseText);
                                if (response.success) {
                                    // Update cart count in the header
                                    const cartCountElements = document.querySelectorAll('.fa-shopping-cart + span');
                                    cartCountElements.forEach(element => {
                                        element.textContent = response.cart_count;
                                    });
                                    
                                    // Show notification
                                    showNotification(`${productName} added to cart!`);
                                } else {
                                    showNotification(response.message || 'Error adding item to cart. Please try again.');
                                }
                            } catch (e) {
                                console.error('Error parsing JSON response:', e);
                                showNotification('Error adding item to cart. Please try again.');
                            }
                        } else {
                            showNotification('Error communicating with server. Please try again.');
                        }
                    };
                    
                    xhr.onerror = function() {
                        showNotification('Network error. Please check your connection and try again.');
                    };
                    
                    // Include username in the AJAX request
                    xhr.send(`product_id=${productId}&product_name=${encodeURIComponent(productName)}&product_price=${productPrice}&product_category=${encodeURIComponent(productCategory)}&product_image=${encodeURIComponent(productImage)}&username=<?= urlencode($username) ?>`);
                }
            });
        });
    </script>
    
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
                    <h4 class="mb-4 text-lg font-semibold">Shop</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">All Products</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Featured</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">New Arrivals</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Sale Items</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Gift Cards</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-4 text-lg font-semibold">Customer Service</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Contact Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">FAQs</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Shipping Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Returns & Refunds</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Track Order</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-4 text-lg font-semibold">About</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Our Story</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Careers</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Terms & Conditions</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Blog</a></li>
                    </ul>
                </div>
            </div>
            <div class="pt-8 mt-8 text-center text-gray-400 border-t border-gray-800">
                <p>&copy; <?php echo date('Y'); ?> ShopEase. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
