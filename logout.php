<?php
require_once 'includes/auth.php';
require_once 'includes/utilities.php';

// Log the user out
logout();

// Redirect to the login page with a message
setFlashMessage('You have been logged out successfully.', 'success');
redirect('login.php'); 