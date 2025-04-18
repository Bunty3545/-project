<?php
session_start();

// Add error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log the request for debugging
error_log("Bill.php: Order processing request received");

$loggedin = false;
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
} else {
    $loggedin = true;
    
    // Check if user_id and username exist in session
    if(!isset($_SESSION['user_id'])) {
        error_log("User ID not found in session for user: " . ($_SESSION['Username'] ?? 'unknown'));
        $_SESSION['user_id'] = 0; // Guest user ID as fallback
    }
    
    if(!isset($_SESSION['Username'])) {
        error_log("Username not found in session for user ID: " . $_SESSION['user_id']);
        $_SESSION['Username'] = 'guest'; // Default username as fallback
    }
    
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['Username']; // Note the capital U to match login.php
}

// Include database connection
require_once 'dbconnect.php';

// Handle payment process and order status update
if(isset($_POST['process_payment']) || $_SESSION['checkout_step'] == 'confirmation' || true) {  // Always process for testing
    // Check if shipping info is complete
    if(empty($_SESSION['shipping_address']) || empty($_SESSION['shipping_city']) || 
       empty($_SESSION['shipping_state']) || empty($_SESSION['shipping_zip'])) {
        $_SESSION['error_message'] = "Please provide complete shipping information before checkout.";
        header("Location: cart.php");
        exit;
    }
    
    // Log the user info for debugging
    error_log("Bill.php: Processing order for user_id: {$user_id}, Username: {$username}");
    
    // Get cart items
    $cart_items = [];
    $total_price = 0;
    
    // Query to get pending cart items - note capital S in Status
    $cart_sql = "SELECT * FROM cart WHERE user_id = ? AND Status = 'Pending'";
    $cart_stmt = $conn->prepare($cart_sql);
    
    // Check for errors in prepare statement
    if (!$cart_stmt) {
        // Check if Status column doesn't exist, try with lowercase status
        $columns_query = "SHOW COLUMNS FROM cart LIKE 'Status'";
        $columns_result = $conn->query($columns_query);
        
        if ($columns_result->num_rows == 0) {
            // Try with lowercase status
            $cart_sql = "SELECT * FROM cart WHERE user_id = ?";
            $cart_stmt = $conn->prepare($cart_sql);
            if (!$cart_stmt) {
                error_log("Bill.php: Error preparing cart query: " . $conn->error);
                $_SESSION['error_message'] = "Database error. Please try again later.";
                header("Location: cart.php");
                exit;
            }
            $cart_stmt->bind_param("i", $user_id);
        } else {
            error_log("Bill.php: Error preparing cart query: " . $conn->error);
            $_SESSION['error_message'] = "Database error. Please try again later.";
            header("Location: cart.php");
            exit;
        }
    } else {
        $cart_stmt->bind_param("i", $user_id);
    }
    
    if(!$cart_stmt->execute()) {
        error_log("Bill.php: Error executing cart query: " . $cart_stmt->error);
        $_SESSION['error_message'] = "Error fetching cart items. Please try again.";
        header("Location: cart.php");
        exit;
    }
    
    $result = $cart_stmt->get_result();
    
    // Log the SQL query for debugging
    error_log("Bill.php: SQL query executed to fetch cart items");
    
    while($item = $result->fetch_assoc()) {
        $cart_items[] = $item;
        $total_price += $item['product_price'] * $item['quantity'];
    }
    $cart_stmt->close();
    
    // Log the number of cart items found
    error_log("Bill.php: Found " . count($cart_items) . " items in cart");
    
    if(empty($cart_items)) {
        $_SESSION['error_message'] = "Your cart is empty. Please add items before checkout.";
        header("Location: cart.php");
        exit;
    }
    
    // Calculate order totals (we still need this for the confirmation page)
    $discount_amount = 0;
    $subtotal = $total_price;
    $tax_rate = 0.08;
    $tax_amount = $subtotal * $tax_rate;
    
    // Calculate shipping
    $shipping_cost = 0;
    if($subtotal < 50) {
        $shipping_cost = 9.99;
    } else if($subtotal < 100) {
        $shipping_cost = 5.99;
    } else {
        $shipping_cost = 0; // Free shipping for orders over $100
    }
    
    $order_total = $subtotal + $tax_amount + $shipping_cost;
    
    // Format shipping address
    $shipping_address = $_SESSION['shipping_address'] . ', ' . 
                       $_SESSION['shipping_city'] . ', ' . 
                       $_SESSION['shipping_state'] . ' ' . 
                       $_SESSION['shipping_zip'];
    
    // Generate unique order ID
    $order_id = 'ORD' . time() . rand(1000, 9999);
    
    // Get payment method if available or default to "Credit Card"
    $payment_method = $_SESSION['payment_method'] ?? "Credit Card";
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // First check if orders table exists
        $check_orders_table = "SHOW TABLES LIKE 'orders'";
        $orders_table_exists = $conn->query($check_orders_table)->num_rows > 0;
        
        // If orders table doesn't exist, create it
        if (!$orders_table_exists) {
            $create_orders_table = "CREATE TABLE orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id VARCHAR(50) NOT NULL,
                user_id INT NOT NULL,
                username VARCHAR(100) NOT NULL,
                order_date DATETIME NOT NULL,
                shipping_address TEXT NOT NULL,
                payment_method VARCHAR(50) NOT NULL,
                subtotal DECIMAL(10,2) NOT NULL,
                tax DECIMAL(10,2) NOT NULL,
                shipping DECIMAL(10,2) NOT NULL,
                total DECIMAL(10,2) NOT NULL,
                status VARCHAR(50) DEFAULT 'Completed'
            )";
            
            if(!$conn->query($create_orders_table)) {
                throw new Exception("Error creating orders table: " . $conn->error);
            }
            
            error_log("Bill.php: Created orders table");
        }
        
        // Check if order_items table exists
        $check_order_items_table = "SHOW TABLES LIKE 'order_items'";
        $order_items_table_exists = $conn->query($check_order_items_table)->num_rows > 0;
        
        // If order_items table doesn't exist, create it
        if (!$order_items_table_exists) {
            $create_order_items_table = "CREATE TABLE order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id VARCHAR(50) NOT NULL,
                product_id INT NOT NULL,
                product_name VARCHAR(255) NOT NULL,
                product_price DECIMAL(10,2) NOT NULL,
                quantity INT NOT NULL,
                product_image VARCHAR(255) NOT NULL,
                product_category VARCHAR(100) NOT NULL
            )";
            
            if(!$conn->query($create_order_items_table)) {
                throw new Exception("Error creating order_items table: " . $conn->error);
            }
            
            error_log("Bill.php: Created order_items table");
        }
        
        // Insert order into orders table
        $insert_order_sql = "INSERT INTO orders (order_id, user_id, username, order_date, shipping_address, payment_method, 
                                               subtotal, tax, shipping, total, status) 
                           VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, 'Completed')";
        
        $insert_order_stmt = $conn->prepare($insert_order_sql);
        
        if (!$insert_order_stmt) {
            throw new Exception("Error preparing order insert statement: " . $conn->error);
        }
        
        $insert_order_stmt->bind_param("sisssdddd", 
            $order_id, 
            $user_id, 
            $username, 
            $shipping_address, 
            $payment_method, 
            $subtotal, 
            $tax_amount, 
            $shipping_cost, 
            $order_total
        );
        
        if(!$insert_order_stmt->execute()) {
            throw new Exception("Error inserting order: " . $insert_order_stmt->error);
        }
        
        $insert_order_stmt->close();
        
        // Insert order items into order_items table
        $insert_item_sql = "INSERT INTO order_items (order_id, product_id, product_name, product_price, 
                                                   quantity, product_image, product_category) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $insert_item_stmt = $conn->prepare($insert_item_sql);
        
        if (!$insert_item_stmt) {
            throw new Exception("Error preparing order item insert statement: " . $conn->error);
        }
        
        foreach($cart_items as $item) {
            $product_id = $item['product_id'] ?? 0;
            $insert_item_stmt->bind_param("sisdiss", 
                $order_id,
                $product_id,
                $item['product_name'],
                $item['product_price'],
                $item['quantity'],
                $item['product_image'],
                $item['product_category']
            );
            
            if(!$insert_item_stmt->execute()) {
                throw new Exception("Error inserting order item: " . $insert_item_stmt->error);
            }
        }
        
        $insert_item_stmt->close();
        
        // Now check if the cart table has a Status column
        $check_status_column = "SHOW COLUMNS FROM cart LIKE 'Status'";
        $status_column_exists = $conn->query($check_status_column)->num_rows > 0;
        
        if($status_column_exists) {
            // Use UPDATE with Status column
            $update_sql = "UPDATE cart SET Status = 'Completed' WHERE user_id = ? AND Status = 'Pending'";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $user_id);
        } else {
            // Check if status column exists (lowercase)
            $check_lowercase_status = "SHOW COLUMNS FROM cart LIKE 'status'";
            $lowercase_status_exists = $conn->query($check_lowercase_status)->num_rows > 0;
            
            if($lowercase_status_exists) {
                // Use UPDATE with lowercase status column
                $update_sql = "UPDATE cart SET status = 'Completed' WHERE user_id = ? AND status = 'Pending'";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("i", $user_id);
            } else {
                // If no status column, we'll add one
                $add_status_column = "ALTER TABLE cart ADD COLUMN status VARCHAR(50) DEFAULT 'Pending'";
                if($conn->query($add_status_column)) {
                    error_log("Bill.php: Added status column to cart table");
                    
                    // Now update with the new column
                    $update_sql = "UPDATE cart SET status = 'Completed' WHERE user_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("i", $user_id);
                } else {
                    // If we can't add the column, just delete the cart items
                    $update_sql = "DELETE FROM cart WHERE user_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("i", $user_id);
                }
            }
        }
        
        if (!$update_stmt) {
            error_log("Bill.php: Error preparing update statement: " . $conn->error);
            throw new Exception("Error preparing update statement: " . $conn->error);
        }
        
        if(!$update_stmt->execute()) {
            error_log("Bill.php: Error executing update: " . $update_stmt->error);
            throw new Exception("Error updating cart items: " . $update_stmt->error);
        }
        
        // If update didn't work, just delete the items
        if ($update_stmt->affected_rows <= 0) {
            error_log("Bill.php: Update didn't affect any rows, trying DELETE instead");
            $delete_sql = "DELETE FROM cart WHERE user_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $user_id);
            
            if($delete_stmt->execute()) {
                error_log("Bill.php: Successfully cleared cart via DELETE");
            } else {
                error_log("Bill.php: Failed to clear cart: " . $delete_stmt->error);
            }
            
            $delete_stmt->close();
        }
        
        $update_stmt->close();
        
        // Store order information in the session for the confirmation page
        $_SESSION['last_order_id'] = $order_id;
        $_SESSION['last_order_total'] = $order_total;
        $_SESSION['last_order_date'] = date('Y-m-d H:i:s');
        $_SESSION['last_order_tax'] = $tax_amount;
        $_SESSION['last_order_shipping'] = $shipping_cost;
        $_SESSION['last_order_items_count'] = count($cart_items);
        $_SESSION['last_order_items'] = $cart_items;
        
        // If we got here, everything was successful
        $conn->commit();
        
        // Log successful order creation
        error_log("Bill.php: Order processed successfully: $order_id for user $username ($user_id)");
        
        // Set checkout step to confirmation
        $_SESSION['checkout_step'] = 'confirmation';
        
        // Redirect to cart page to show confirmation
        header("Location: cart.php");
        exit;
        
    } catch(Exception $e) {
        // Something went wrong, rollback the transaction
        $conn->rollback();
        
        error_log("Bill.php: Order processing failed: " . $e->getMessage());
        
        $_SESSION['error_message'] = "We couldn't process your order. Please try again. Error: " . $e->getMessage();
        header("Location: cart.php");
        exit;
    }
}

// If someone lands on this page directly without the right parameters,
// redirect them back to the cart
header("Location: cart.php");
exit;
?>