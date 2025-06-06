<?php
/**
 * Database Configuration
 */

// Define constants for database connection
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'booking_system');

// Create database connection
function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Close database connection
function closeDbConnection($conn) {
    // Only try to close if it's a valid object
    if ($conn && is_object($conn)) {
        try {
            // Suppress warnings with @ operator
            @$conn->close();
        } catch (Throwable $e) {
            // Silently catch any errors - connection likely already closed
        }
    }
} 