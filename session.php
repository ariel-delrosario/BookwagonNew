<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Debug information
error_log("Session data: " . print_r($_SESSION, true));
error_log("Session ID: " . session_id());

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    error_log("User not logged in");
} else {
    error_log("User logged in with ID: " . $_SESSION['id']);
}

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['id'])) {
    // Store the requested page in session to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}
?>