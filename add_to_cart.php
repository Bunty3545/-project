<?php
session_start();
header('Content-Type: application/json');

// Add error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log the request for debugging
error_log("Add to cart request received: " . json_encode($_POST));

// Check if user is logged in
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to add items to cart']);
    exit;
}

// Check for user_id and username in session
if(!isset($_SESSION['user_id'])) {
    error_log("User ID not found in session for user: " . ($_SESSION['Username'] ?? 'unknown'));
    echo json_encode(['success' => false, 'message' => 'Session error. Please try logging in again']);
    exit;
}

// Set user variables from session
$user_id = $_SESSION['user_id']; 
$username = $_SESSION['Username'] ?? 'guest'; // Use actual username from session

// Include database connection
require_once 'dbconnect.php';

// Handle AJAX POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    try {
        $product_id = $_POST['product_id'];
        $product_name = $_POST['product_name'] ?? 'Unknown Product';
        $product_price = $_POST['product_price'] ?? 0;
        $product_image = $_POST['product_image'] ?? '';
        $product_category = $_POST['product_category'] ?? 'Uncategorized';
        
        // Log the product data
        error_log("Adding product to cart: " . json_encode([
            'user_id' => $user_id,
            'product_id' => $product_id,
            'product_name' => $product_name,
            'product_price' => $product_price,
            'product_category' => $product_category
        ]));
        
        // Check if product already exists in cart
        $check_sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $user_id, $product_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Product already in cart, update quantity and ensure username is correct
            $cart_item = $result->fetch_assoc();
            $new_quantity = $cart_item['quantity'] + 1;
            
            $update_sql = "UPDATE cart SET quantity = ?, username = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("isi", $new_quantity, $username, $cart_item['id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            error_log("Updated cart item quantity: id={$cart_item['id']}, new_quantity={$new_quantity}");
        } else {
            // Add new product to cart with the correct username
            $insert_sql = "INSERT INTO cart (user_id, product_id, product_name, product_price, product_image, product_category, quantity, username) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $quantity = 1;
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iisdssis", $user_id, $product_id, $product_name, $product_price, $product_image, $product_category, $quantity, $username);
            $success = $insert_stmt->execute();
            
            if (!$success) {
                error_log("Error executing insert: " . $insert_stmt->error);
                throw new Exception("Database error: " . $insert_stmt->error);
            }
            
            error_log("Inserted new cart item, product_id: {$product_id}");
            $insert_stmt->close();
        }
        
        $check_stmt->close();
        
        // Get updated cart count
        $count_sql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param("i", $user_id);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $count_row = $count_result->fetch_assoc();
        $cart_count = $count_row['total'] ? intval($count_row['total']) : 0;
        $count_stmt->close();
        
        // Store cart count in session for easy access across pages
        $_SESSION['cart_count'] = $cart_count;
        
        error_log("Cart updated successfully. New cart count: {$cart_count}");
        
        // Return success response with new cart count
        echo json_encode([
            'success' => true, 
            'message' => 'Item added to cart successfully', 
            'cart_count' => $cart_count,
            'product_name' => $product_name
        ]);
    } catch (Exception $e) {
        error_log("Error adding to cart: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error adding item to cart: ' . $e->getMessage()]);
    }
} else { 
    // Handle non-AJAX direct form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $product_id = $_POST['product_id'] ?? 0;
            $product_name = $_POST['product_name'] ?? 'Unknown Product';
            $product_price = $_POST['product_price'] ?? 0;
            $product_image = $_POST['product_image'] ?? '';
            $product_category = $_POST['product_category'] ?? 'Uncategorized';
            
            error_log("Direct form submission - adding product to cart: " . $product_name);
            
            // Check if product already exists in cart
            $check_sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $user_id, $product_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Product already in cart, update quantity and username
                $cart_item = $result->fetch_assoc();
                $new_quantity = $cart_item['quantity'] + 1;
                
                $update_sql = "UPDATE cart SET quantity = ?, username = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("isi", $new_quantity, $username, $cart_item['id']);
                $update_stmt->execute();
                $update_stmt->close();
            } else {
                // Add new product to cart with username
                $insert_sql = "INSERT INTO cart (user_id, product_id, product_name, product_price, product_image, product_category, quantity, username) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $quantity = 1;
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iisdssis", $user_id, $product_id, $product_name, $product_price, $product_image, $product_category, $quantity, $username);
                $success = $insert_stmt->execute();
                
                if (!$success) {
                    error_log("Error executing direct insert: " . $insert_stmt->error);
                    throw new Exception("Database error: " . $insert_stmt->error);
                }
                
                $insert_stmt->close();
            }
            
            $check_stmt->close();
            
            // Get updated cart count
            $count_sql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
            $count_stmt = $conn->prepare($count_sql);
            $count_stmt->bind_param("i", $user_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $count_row = $count_result->fetch_assoc();
            $cart_count = $count_row['total'] ? intval($count_row['total']) : 0;
            $count_stmt->close();
            
            // Store cart count in session for easy access across pages
            $_SESSION['cart_count'] = $cart_count;
            
            // Set a success message
            $_SESSION['success_message'] = "Product added to cart successfully!";
            
            // Redirect back to referring page or fallback to index
            $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
            error_log("Redirecting to: " . $redirect);
            header("Location: $redirect");
            exit;
        } catch (Exception $e) {
            error_log("Error in direct form submission: " . $e->getMessage());
            $_SESSION['error_message'] = "Error adding item to cart. Please try again.";
            header("Location: index.php");
            exit;
        }
    } else {
        // If it's not a POST request, return error JSON
        error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
}
// Don't include a closing PHP tag to prevent accidental whitespace
