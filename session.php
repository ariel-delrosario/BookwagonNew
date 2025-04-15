<?php
session_start(); // Start session to access session variables

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to login page if not logged in
    header("Location: Index.html");
    exit();
}
?>