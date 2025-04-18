<?php
$showalert = false; 
$showerror = false;
$exists = false;
include 'dbconnect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Use trim to remove whitespace and prepare_string to prevent SQL injection
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm = $_POST["cnfpass"];
    
    // Use prepared statements to prevent SQL injection
    $sql_exit = "SELECT * FROM `form` WHERE Username = ?";
    $stmt = $conn->prepare($sql_exit);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $numrows = $result->num_rows;
    $stmt->close();
    
    if ($numrows > 0) {
        $exists = true;
    } else {
        $exists = false;
        if ($confirm == $password) {
            // Use prepared statements for insertion
            $sql = "INSERT INTO `form` (`Username`, `Password`, `Date`) VALUES (?, ?, current_timestamp())";
            $stmt = $conn->prepare($sql);
            
            // Store the password directly as shown in your database structure
            // Note: In a production environment, you should hash passwords
            $stmt->bind_param("ss", $username, $password);
            
            if ($stmt->execute()) {
                $showalert = true;
            } else {
                $showerror = true;
            }
            $stmt->close();
        } else {
            $showerror = true;
        }
    }
}  
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - ShopEase</title>
    <script src="https://cdn.tailwindcss.com/3.3.5"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .signup-container {
            max-width: 400px;
            width: 100%;
        }
        .form-input {
            transition: all 0.3s ease;
        }
        .form-input:focus {
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        .btn-signup {
            transition: all 0.3s ease;
        }
        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="flex flex-col items-center justify-center min-h-screen bg-gray-50">
    <div class="p-8 bg-white shadow-md signup-container rounded-xl">
        <div class="flex justify-center mb-6">
            <i class="text-4xl text-blue-500 fas fa-shopping-bag"></i>
            <span class="ml-2 text-3xl font-bold text-gray-800">ShopEase</span>
        </div>
        
        <?php if ($showalert): ?>
            <div class="p-6 text-center">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-green-100 rounded-full">
                    <i class="text-green-600 fas fa-check"></i>
                </div>
                <h2 class="mt-3 text-xl font-bold text-gray-800">Success!</h2>
                <p class="mt-2 text-gray-600">Your account has been created successfully.</p>
                <div class="mt-6">
                    <a href="login.php" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm btn-signup hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="mr-2 fas fa-sign-in-alt"></i> Go to Login
                    </a>
                </div>
            </div>
            
        <?php elseif ($showerror): ?>
            <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
                <div class="flex items-center">
                    <i class="mr-2 fas fa-exclamation-circle"></i>
                    <span class="font-medium">Error!</span> Passwords don't match.
                </div>
            </div>
            
            <h2 class="mb-6 text-2xl font-bold text-center text-gray-800">Create an Account</h2>
            <form method="post" action="" class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="text-gray-400 fas fa-user"></i>
                        </div>
                        <input id="username" name="username" type="text" required 
                               class="block w-full py-2 pl-10 border border-gray-300 rounded-md form-input focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Choose a username">
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="text-gray-400 fas fa-lock"></i>
                        </div>
                        <input id="password" name="password" type="password" required 
                               class="block w-full py-2 pl-10 border border-gray-300 rounded-md form-input focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Create a password">
                    </div>
                </div>
                
                <div>
                    <label for="cnfpass" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="text-gray-400 fas fa-lock"></i>
                        </div>
                        <input id="cnfpass" name="cnfpass" type="password" required 
                               class="block w-full py-2 pl-10 border border-gray-300 rounded-md form-input focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Confirm your password">
                    </div>
                </div>
                
                <div>
                    <button type="submit" 
                            class="flex justify-center w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm btn-signup hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="mr-2 fas fa-user-plus"></i> Sign Up
                    </button>
                </div>
            </form>
            
        <?php elseif ($exists): ?>
            <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
                <div class="flex items-center">
                    <i class="mr-2 fas fa-exclamation-circle"></i>
                    <span class="font-medium">Error!</span> Username already exists.
                </div>
            </div>
            
            <h2 class="mb-6 text-2xl font-bold text-center text-gray-800">Create an Account</h2>
            <form method="post" action="" class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="text-gray-400 fas fa-user"></i>
                        </div>
                        <input id="username" name="username" type="text" required 
                               class="block w-full py-2 pl-10 border border-gray-300 rounded-md form-input focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Choose a username">
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="text-gray-400 fas fa-lock"></i>
                        </div>
                        <input id="password" name="password" type="password" required 
                               class="block w-full py-2 pl-10 border border-gray-300 rounded-md form-input focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Create a password">
                    </div>
                </div>
                
                <div>
                    <label for="cnfpass" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="text-gray-400 fas fa-lock"></i>
                        </div>
                        <input id="cnfpass" name="cnfpass" type="password" required 
                               class="block w-full py-2 pl-10 border border-gray-300 rounded-md form-input focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Confirm your password">
                    </div>
                </div>
                
                <div>
                    <button type="submit" 
                            class="flex justify-center w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm btn-signup hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="mr-2 fas fa-user-plus"></i> Sign Up
                    </button>
                </div>
            </form>
            
        <?php else: ?>
            <h2 class="mb-6 text-2xl font-bold text-center text-gray-800">Create an Account</h2>
            <form method="post" action="" class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="text-gray-400 fas fa-user"></i>
                        </div>
                        <input id="username" name="username" type="text" required 
                               class="block w-full py-2 pl-10 border border-gray-300 rounded-md form-input focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Choose a username">
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="text-gray-400 fas fa-lock"></i>
                        </div>
                        <input id="password" name="password" type="password" required 
                               class="block w-full py-2 pl-10 border border-gray-300 rounded-md form-input focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Create a password">
                    </div>
                </div>
                
                <div>
                    <label for="cnfpass" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="text-gray-400 fas fa-lock"></i>
                        </div>
                        <input id="cnfpass" name="cnfpass" type="password" required 
                               class="block w-full py-2 pl-10 border border-gray-300 rounded-md form-input focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Confirm your password">
                    </div>
                </div>
                
                <div>
                    <button type="submit" 
                            class="flex justify-center w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm btn-signup hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="mr-2 fas fa-user-plus"></i> Sign Up
                    </button>
                </div>
            </form>
        <?php endif; ?>
        
        <div class="mt-4 text-sm text-center text-gray-600">
            Already have an account? 
            <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">
                Log in
            </a>
        </div>
    </div>
</body>
</html>
