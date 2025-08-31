<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Log out the user
logout_user();

// Set success message
$_SESSION['message'] = "You have been successfully logged out.";
$_SESSION['message_type'] = "success";

// Redirect to homepage
redirect('index.php');
?>