<?php
session_start();

// Unset all of the session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Set a success message in a new temporary session
session_start();
$_SESSION['success_message'] = "You have been successfully logged out.";

// Redirect to the login page
header("Location: login.php");
exit;
