<?php
// Initialize necessary variables to prevent errors
if (!isset($cart_count)) {
    $cart_count = 0;
    if (isset($_SESSION['user_id']) && isset($conn)) {
        try {
            $user_id = $_SESSION['user_id'];
            $count_sql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
            $count_stmt = $conn->prepare($count_sql);
            $count_stmt->bind_param("i", $user_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            if ($count_row = $count_result->fetch_assoc()) {
                $cart_count = $count_row['total'] ? intval($count_row['total']) : 0;
            }
            $count_stmt->close();
        } catch (Exception $e) {
            error_log("Error in nav.php: " . $e->getMessage());
        }
    } else if (isset($_SESSION['cart_count'])) {
        $cart_count = intval($_SESSION['cart_count']);
    }
}

$loggedin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$username = $loggedin ? $_SESSION['username'] : '';
?>

<!-- Navigation -->
<nav class="sticky top-0 z-40 bg-white shadow-lg">
    <div class="px-4 mx-auto max-w-7xl">
        <div class="flex items-center justify-between py-4">
            <!-- Logo -->
            <a href="index.php" class="flex items-center space-x-2">
                <i class="text-3xl fas fa-shopping-bag text-primary"></i>
                <span class="text-2xl font-bold text-gray-800">ShopEase</span>
            </a>
            
            <!-- Main Navigation Links (Desktop) -->
            <div class="items-center hidden md:flex">
                <a href="index.php" class="flex items-center px-3 py-2 text-gray-700 transition-colors rounded-md hover:bg-primary hover:text-white">
                    <i class="mr-1 fas fa-home"></i>
                    <span>Home</span>
                </a>
                
                <!-- Shop Dropdown -->
                <div class="relative group">
                    <button class="flex items-center px-3 py-2 text-gray-700 transition-colors rounded-md group-hover:bg-primary group-hover:text-white">
                        <i class="mr-1 fas fa-store"></i>
                        <span>Shop</span>
                        <i class="ml-1 text-xs fas fa-chevron-down"></i>
                    </button>
                    <div class="absolute left-0 z-50 hidden w-48 py-2 mt-1 bg-white rounded-md shadow-xl group-hover:block hover:block">
                        <a href="clothing.php" class="block px-4 py-2 text-gray-700 transition-colors duration-200 hover:bg-gray-100">
                            <i class="mr-2 fas fa-tshirt text-primary"></i> Clothing
                        </a>
                        <a href="electronics.php" class="block px-4 py-2 text-gray-700 transition-colors duration-200 hover:bg-gray-100">
                            <i class="mr-2 fas fa-laptop text-primary"></i> Electronics
                        </a>
                        <a href="home&living.php" class="block px-4 py-2 text-gray-700 transition-colors duration-200 hover:bg-gray-100">
                            <i class="mr-2 fas fa-home text-primary"></i> Home & Living
                        </a>
                        <a href="sports.php" class="block px-4 py-2 text-gray-700 transition-colors duration-200 hover:bg-gray-100">
                            <i class="mr-2 fas fa-dumbbell text-primary"></i> Sports
                        </a>
                    </div>
                </div>
                
                <!-- Flash Sale Link (direct to section) -->
                <a href="index.php#flash-sale" class="flex items-center px-3 py-2 text-gray-700 transition-colors rounded-md hover:bg-primary hover:text-white">
                    <i class="mr-1 text-red-500 fas fa-bolt"></i>
                    <span>Flash Sale</span>
                </a>
                
                <!-- About -->
                <a href="about.php" class="flex items-center px-3 py-2 text-gray-700 transition-colors rounded-md hover:bg-primary hover:text-white">
                    <i class="mr-1 fas fa-info-circle"></i>
                    <span>About</span>
                </a>
                
                <!-- Contact -->
                <a href="contact.php" class="flex items-center px-3 py-2 text-gray-700 transition-colors rounded-md hover:bg-primary hover:text-white">
                    <i class="mr-1 fas fa-envelope"></i>
                    <span>Contact</span>
                </a>
            </div>
            
            <!-- User Actions -->
            <div class="flex items-center space-x-4">
                <!-- Wishlist Icon -->
                <a href="#" class="hidden text-gray-700 transition-colors md:block hover:text-primary" title="Wishlist">
                    <i class="text-xl fas fa-heart"></i>
                </a>
                
                <!-- Account Dropdown -->
                <div class="relative hidden md:block group">
                    <button class="flex items-center px-3 py-2 text-gray-700 transition-colors rounded-md group-hover:bg-primary group-hover:text-white">
                        <i class="mr-1 fas fa-user"></i>
                        <span><?php echo $loggedin ? htmlspecialchars(ucfirst($username)) : 'Account'; ?></span>
                        <i class="ml-1 text-xs fas fa-chevron-down"></i>
                    </button>
                    <div class="absolute right-0 z-50 hidden w-48 py-2 mt-1 bg-white rounded-md shadow-xl group-hover:block hover:block">
                        <?php if ($loggedin): ?>
                            <a href="#" class="block px-4 py-2 text-gray-700 transition-colors duration-200 hover:bg-gray-100">
                                <i class="mr-2 fas fa-user-circle text-primary"></i> My Profile
                            </a>
                            <a href="#" class="block px-4 py-2 text-gray-700 transition-colors duration-200 hover:bg-gray-100">
                                <i class="mr-2 fas fa-box text-primary"></i> My Orders
                            </a>
                            <a href="#" class="block px-4 py-2 text-gray-700 transition-colors duration-200 hover:bg-gray-100">
                                <i class="mr-2 fas fa-heart text-primary"></i> Wishlist
                            </a>
                          
                            <div class="my-1 border-t border-gray-200"></div>
                            <a href="logout.php" class="block px-4 py-2 text-gray-700 transition-colors duration-200 hover:bg-red-100">
                                <i class="mr-2 text-red-500 fas fa-sign-out-alt"></i> Logout
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="block px-4 py-2 text-gray-700 transition-colors duration-200 hover:bg-gray-100">
                                <i class="mr-2 fas fa-sign-in-alt text-primary"></i> Login
                            </a>
                            <a href="register.php" class="block px-4 py-2 text-gray-700 transition-colors duration-200 hover:bg-gray-100">
                                <i class="mr-2 fas fa-user-plus text-primary"></i> Register
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- View Cart Button with Counter -->
                <div class="relative hidden md:block">
                    <a href="cart.php" class="flex items-center px-3 py-2 text-white transition-colors rounded-md bg-primary hover:bg-secondary">
                        <i class="mr-1 fas fa-shopping-cart"></i>
                        <span>View Cart</span>
                        <span class="flex items-center justify-center w-5 h-5 ml-2 text-xs font-bold bg-white rounded-full text-primary"><?= $cart_count ?></span>
                    </a>
                </div>
                
                <!-- Mobile Menu Button -->
                <button id="mobileMenuButton" class="text-gray-700 focus:outline-none md:hidden">
                    <i class="text-2xl fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
</nav>

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
        
        <!-- Mobile Navigation Links -->
        <div class="space-y-2">
            <!-- User Account Section (Mobile) -->
            <?php if ($loggedin): ?>
                <div class="p-4 mb-4 bg-gray-100 rounded-lg">
                    <div class="flex items-center mb-3">
                        <div class="p-2 mr-3 text-white rounded-full bg-primary">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <p class="font-medium">Welcome back,</p>
                            <p class="text-lg font-semibold"><?= htmlspecialchars($username) ?></p>
                        </div>
                    </div>
                    <a href="logout.php" class="flex items-center justify-center w-full px-4 py-2 mt-2 text-white rounded-md bg-primary hover:bg-secondary">
                        <i class="mr-2 fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            <?php else: ?>
                <div class="p-4 mb-4 bg-gray-100 rounded-lg">
                    <div class="grid grid-cols-2 gap-2">
                        <a href="login.php" class="flex items-center justify-center px-4 py-2 text-white rounded-md bg-primary hover:bg-secondary">
                            <i class="mr-2 fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="register.php" class="flex items-center justify-center px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100">
                            <i class="mr-2 fas fa-user-plus"></i> Register
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Main Navigation (Mobile) -->
            <a href="index.php" class="flex items-center px-4 py-3 text-gray-700 rounded-md hover:bg-gray-100">
                <i class="w-6 mr-2 text-center fas fa-home text-primary"></i>
                <span>Home</span>
            </a>
            
            <!-- Shop Section (Mobile) -->
            <div class="mobile-dropdown">
                <button class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-md hover:bg-gray-100 mobile-dropdown-toggle">
                    <div class="flex items-center">
                        <i class="w-6 mr-2 text-center fas fa-store text-primary"></i>
                        <span>Shop</span>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="hidden pl-12 mobile-dropdown-content">
                    <a href="clothing.php" class="flex items-center py-2 text-gray-700 transition-colors duration-200 hover:text-primary">
                        <i class="w-6 mr-2 text-center fas fa-tshirt"></i>
                        Clothing
                    </a>
                    <a href="electronics.php" class="flex items-center py-2 text-gray-700 transition-colors duration-200 hover:text-primary">
                        <i class="w-6 mr-2 text-center fas fa-laptop"></i>
                        Electronics
                    </a>
                    <a href="home&living.php" class="flex items-center py-2 text-gray-700 transition-colors duration-200 hover:text-primary">
                        <i class="w-6 mr-2 text-center fas fa-home"></i>
                        Home & Living
                    </a>
                    <a href="sports.php" class="flex items-center py-2 text-gray-700 transition-colors duration-200 hover:text-primary">
                        <i class="w-6 mr-2 text-center fas fa-dumbbell"></i>
                        Sports
                    </a>
                </div>
            </div>
            
            <!-- Flash Sale Link (Mobile) -->
            <a href="index.php#flash-sale" class="flex items-center px-4 py-3 text-gray-700 rounded-md hover:bg-gray-100">
                <i class="w-6 mr-2 text-center text-red-500 fas fa-bolt"></i>
                <span>Flash Sale</span>
            </a>
            
            <!-- About (Mobile) -->
            <a href="#" class="flex items-center px-4 py-3 text-gray-700 rounded-md hover:bg-gray-100">
                <i class="w-6 mr-2 text-center fas fa-info-circle text-primary"></i>
                <span>About</span>
            </a>
            
            <!-- Contact (Mobile) -->
            <a href="#" class="flex items-center px-4 py-3 text-gray-700 rounded-md hover:bg-gray-100">
                <i class="w-6 mr-2 text-center fas fa-envelope text-primary"></i>
                <span>Contact</span>
            </a>
            
            <!-- Wishlist (Mobile) -->
            <a href="#" class="flex items-center px-4 py-3 text-gray-700 rounded-md hover:bg-gray-100">
                <i class="w-6 mr-2 text-center fas fa-heart text-primary"></i>
                <span>Wishlist</span>
            </a>
            
            <!-- View Cart Button (Mobile) -->
            <a href="cart.php" class="flex items-center justify-between px-4 py-3 text-white rounded-md bg-primary hover:bg-secondary">
                <div class="flex items-center">
                    <i class="w-6 mr-2 text-center fas fa-shopping-cart"></i>
                    <span>View Cart</span>
                </div>
                <span class="flex items-center justify-center w-6 h-6 text-xs font-bold bg-white rounded-full text-primary"><?= $cart_count ?></span>
            </a>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const closeMenuButton = document.getElementById('closeMenu');
        const mobileMenu = document.getElementById('mobileMenu');
        
        if (mobileMenuButton && closeMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            });
            
            closeMenuButton.addEventListener('click', function() {
                mobileMenu.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            });
        }
        
        // Mobile dropdowns with simpler, more reliable functionality
        const mobileDropdownToggles = document.querySelectorAll('.mobile-dropdown-toggle');
        
        // Function to close all dropdowns
        function closeAllDropdowns() {
            document.querySelectorAll('.mobile-dropdown-content').forEach(content => {
                content.classList.add('hidden');
                
                const toggle = content.closest('.mobile-dropdown').querySelector('.mobile-dropdown-toggle i');
                if (toggle) {
                    toggle.classList.add('fa-chevron-down');
                    toggle.classList.remove('fa-chevron-up');
                }
            });
        }
        
        // Handle toggle clicks
        mobileDropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const dropdown = this.closest('.mobile-dropdown');
                const content = dropdown.querySelector('.mobile-dropdown-content');
                const chevron = this.querySelector('i');
                const isOpen = !content.classList.contains('hidden');
                
                // Close all other dropdowns first
                document.querySelectorAll('.mobile-dropdown-content').forEach(otherContent => {
                    if (otherContent !== content) {
                        otherContent.classList.add('hidden');
                        
                        const otherChevron = otherContent.closest('.mobile-dropdown').querySelector('.mobile-dropdown-toggle i');
                        if (otherChevron) {
                            otherChevron.classList.add('fa-chevron-down');
                            otherChevron.classList.remove('fa-chevron-up');
                        }
                    }
                });
                
                // Toggle current dropdown
                if (isOpen) {
                    content.classList.add('hidden');
                    if (chevron) {
                        chevron.classList.add('fa-chevron-down');
                        chevron.classList.remove('fa-chevron-up');
                    }
                } else {
                    content.classList.remove('hidden');
                    if (chevron) {
                        chevron.classList.remove('fa-chevron-down');
                        chevron.classList.add('fa-chevron-up');
                    }
                }
            });
        });
        
        // Prevent clicks inside dropdown content from closing the dropdown
        document.querySelectorAll('.mobile-dropdown-content').forEach(content => {
            content.addEventListener('click', function(e) {
                // Only prevent propagation, but allow the actual click action
                e.stopPropagation();
            });
        });
        
        // Only close dropdowns when clicking outside both dropdown toggles and content
        document.addEventListener('click', function(e) {
            // Check if click is outside dropdown elements
            if (!e.target.closest('.mobile-dropdown-toggle') && 
                !e.target.closest('.mobile-dropdown-content')) {
                closeAllDropdowns();
            }
        });
        
        // Handle dropdown item clicks - don't close when hovering, only when selecting
        document.querySelectorAll('.mobile-dropdown-content a').forEach(link => {
            link.addEventListener('click', function(e) {
                // Allow the link to work as normal
                // The dropdown will close when the page navigates
                
                // Optional: manually close the dropdown
                // setTimeout(() => {
                //     closeAllDropdowns();
                // }, 100);
            });
        });
    });
</script>


