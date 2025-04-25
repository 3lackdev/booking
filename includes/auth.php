<?php
/**
 * Authentication Functions
 */

session_start();

require_once __DIR__ . '/../config/database.php';

/**
 * Authenticate user
 * 
 * @param string $username
 * @param string $password
 * @return bool
 */
function login($username, $password) {
    $conn = getDbConnection();
    
    $username = $conn->real_escape_string($username);
    
    $sql = "SELECT id, username, password, full_name, role FROM users WHERE username = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            closeDbConnection($conn);
            return true;
        }
    }
    
    closeDbConnection($conn);
    return false;
}

/**
 * Register new user
 * 
 * @param string $username
 * @param string $password
 * @param string $email
 * @param string $full_name
 * @return bool|string True on success, error message on failure
 */
function register($username, $password, $email, $full_name) {
    $conn = getDbConnection();
    
    // Sanitize inputs
    $username = $conn->real_escape_string($username);
    $email = $conn->real_escape_string($email);
    $full_name = $conn->real_escape_string($full_name);
    
    // Check if username or email already exists
    $sql = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        closeDbConnection($conn);
        return "Username or email already exists.";
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $sql = "INSERT INTO users (username, password, email, full_name, role) 
            VALUES ('$username', '$hashed_password', '$email', '$full_name', 'user')";
    
    if ($conn->query($sql) === TRUE) {
        closeDbConnection($conn);
        return true;
    } else {
        closeDbConnection($conn);
        return "Error: " . $conn->error;
    }
}

/**
 * Check if user is logged in
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * 
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] == 'admin';
}

/**
 * Logout user
 */
function logout() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
}

/**
 * Get current user data
 * 
 * @return array|null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $conn = getDbConnection();
    
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT id, username, email, full_name, role FROM users WHERE id = $user_id";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        closeDbConnection($conn);
        return $user;
    }
    
    closeDbConnection($conn);
    return null;
} 