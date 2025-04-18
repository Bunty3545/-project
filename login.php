<?php
session_start();
$login = false;
$showerror = false;
include 'dbconnect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        $showerror = "Username and password are required";
    } else {
        // Using prepared statement to prevent SQL injection
        $sql = "SELECT * FROM form WHERE Username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) { 
            $row = $result->fetch_assoc();
            
            // Check if the password matches
            if ($password == $row['Password']) {
                $login = true;
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $row['Username'];
                $_SESSION['user_id'] = $row['S.No.']; // Using 'S.No.' as the ID column
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Check if there's a redirect parameter
                if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
                    $redirect = filter_var($_GET['redirect'], FILTER_SANITIZE_URL);
                    // Prevent open redirect vulnerabilities by checking if it's a local URL
                    if (strpos($redirect, '/') === 0 || strpos($redirect, './') === 0 || !preg_match('/^https?:\/\//', $redirect)) {
                        header("location: $redirect");
                        exit;
                    }
                }
                
                header("location: index.php");
                exit;
            } else {
                $showerror = "Invalid username or password";
            }
        } else {
            $showerror = "Invalid username or password";
        } 
        
        $stmt->close();
    }
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ShopEase</title>
    <script src="https://cdn.tailwindcss.com/3.3.5"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3B82F6;
            --primary-hover: #2563EB;
            --error-color: #EF4444;
            --success-color: #10B981;
        }
        
        body {
            background: linear-gradient(135deg, #F9FAFB 0%, #E5E7EB 100%);
            min-height: 100vh;
        }
        
        .login-container {
            max-width: 420px;
            width: 100%;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 
                        0 10px 10px -5px rgba(0, 0, 0, 0.02);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .login-container:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 
                        0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .form-input {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        
        .form-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }
        
        .btn-login {
            letter-spacing: 0.5px;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 0.5rem 1rem;
        }
        
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 
                        0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .error-message {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .error-message.active {
            max-height: 100px;
            margin-top: 0.5rem;
        }
        
        .logo-circle {
            transition: all 0.3s ease;
        }
        
        .logo-circle:hover {
            transform: rotate(15deg);
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="p-8 bg-white login-container">
        <!-- Logo and Site Name -->
        <div class="flex justify-center mb-8">
            <div class="flex items-center justify-center text-white bg-blue-500 rounded-lg w-14 h-14 logo-circle">
                <i class="text-2xl fas fa-shopping-bag"></i>
            </div>
            <span class="self-center ml-3 text-3xl font-bold text-gray-800">ShopEase</span>
        </div>
        
        <?php if ($login): ?>
            <!-- Success State -->
            <div class="p-6 text-center">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full animate-bounce">
                    <i class="text-2xl text-green-600 fas fa-check"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800">Login Successful!</h2>
                <p class="mt-2 text-gray-600">You are being redirected to the homepage.</p>
                <div class="mt-6">
                    <a href="index.php" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm btn-login hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="mr-2 fas fa-home"></i> Go to Home
                    </a>
                </div>
            </div>
            
            <script>
                // Redirection with animation completion
                setTimeout(function() {
                    document.querySelector('.login-container').classList.add('opacity-0', 'scale-95');
                    setTimeout(function() {
                        window.location.href = "index.php";
                    }, 300);
                }, 2500);
            </script>
            
        <?php else: ?>
            <!-- Login Form -->
            <h2 class="mb-8 text-2xl font-bold text-center text-gray-800">Login to your account</h2>
            
            <?php if (isset($_GET['error']) && $_GET['error'] === 'session_expired'): ?>
                <div class="p-4 mb-6 text-yellow-800 bg-yellow-100 border-l-4 border-yellow-500 rounded-md">
                    <div class="flex items-center">
                        <i class="mr-3 text-yellow-600 fas fa-exclamation-triangle"></i>
                        <p>Your session has expired. Please log in again.</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="post" action="" class="space-y-6" id="loginForm">
                <div class="form-group">
                    <label for="username" class="block mb-1 text-sm font-medium text-gray-700">Username</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="text-gray-400 fas fa-user"></i>
                        </div>
                        <input id="username" name="username" type="text" required 
                               class="block w-full py-2 pl-10 border border-gray-300 rounded-md form-input focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Enter your username"
                               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="block mb-1 text-sm font-medium text-gray-700">Password</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="text-gray-400 fas fa-lock"></i>
                        </div>
                        <input id="password" name="password" type="password" required 
                               class="block w-full py-2 pl-10 border border-gray-300 rounded-md form-input focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Enter your password">
                    </div>
                    <?php if ($showerror): ?>
                        <div id="passwordError" class="error-message active">
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($showerror) ?></p>
                        </div>
                    <?php else: ?>
                        <div id="passwordError" class="error-message"></div>
                    <?php endif; ?>
                </div>
                
                <div class="flex items-center justify-between mt-1">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" 
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="remember-me" class="block ml-2 text-sm text-gray-700">
                            Remember me
                        </label>
                    </div>
                    
                    <div class="text-sm">
                        <a href="#" class="font-medium text-blue-600 hover:text-blue-500">
                            Forgot password?
                        </a>
                    </div>
                </div>
                
                <div class="pt-2">
                    <button type="submit" 
                            class="flex justify-center w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm btn-login hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="mr-2 fas fa-sign-in-alt"></i> Sign in
                    </button>
                </div>
            </form>
            
            <div class="mt-6 text-sm text-center text-gray-600">
                Don't have an account? 
                <a href="signup.php" class="font-medium text-blue-600 hover:text-blue-500">
                    Sign up
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const passwordInput = document.getElementById('password');
            const passwordError = document.getElementById('passwordError');
            
            // Focus the username field if empty, otherwise the password field
            const usernameInput = document.getElementById('username');
            if (usernameInput.value === '') {
                usernameInput.focus();
            } else {
                passwordInput.focus();
            }
            
            // Add input validation on form submission
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    let isValid = true;
                    
                    // Clear previous errors
                    if (passwordError) {
                        passwordError.innerHTML = '';
                        passwordError.classList.remove('active');
                    }
                    
                    // Validate username
                    if (usernameInput.value.trim() === '') {
                        isValid = false;
                        usernameInput.classList.add('border-red-300', 'focus:border-red-300', 'focus:ring-red-200');
                    } else {
                        usernameInput.classList.remove('border-red-300', 'focus:border-red-300', 'focus:ring-red-200');
                    }
                    
                    // Validate password
                    if (passwordInput.value === '') {
                        isValid = false;
                        passwordInput.classList.add('border-red-300', 'focus:border-red-300', 'focus:ring-red-200');
                        passwordError.innerHTML = '<p class="mt-1 text-sm text-red-600">Password is required</p>';
                        passwordError.classList.add('active');
                    } else {
                        passwordInput.classList.remove('border-red-300', 'focus:border-red-300', 'focus:ring-red-200');
                    }
                    
                    if (!isValid) {
                        e.preventDefault();
                    }
                });
            }
            
            // Add smooth transitions for error messages
            if (passwordError) {
                setTimeout(() => {
                    passwordError.classList.add('transition-all', 'duration-300');
                }, 100);
            }
            
            // Add floating label effect on focus
            const inputs = document.querySelectorAll('.form-input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    const label = document.querySelector(`label[for="${this.id}"]`);
                    if (label) {
                        label.classList.add('text-blue-600');
                    }
                });
                
                input.addEventListener('blur', function() {
                    const label = document.querySelector(`label[for="${this.id}"]`);
                    if (label) {
                        label.classList.remove('text-blue-600');
                    }
                });
            });
        });
    </script>
</body>
</html>
