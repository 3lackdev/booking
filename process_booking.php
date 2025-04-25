<?php
require_once 'includes/auth.php';
require_once 'includes/utilities.php';
require_once 'classes/ResourceManager.php';
require_once 'classes/BookingManager.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage("You must be logged in to create bookings.", "error");
    redirect('login.php');
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage("Invalid request method.", "error");
    redirect('calendar_view.php');
}

// Get form data
$resource_id = isset($_POST['resource_id']) ? (int)$_POST['resource_id'] : 0;
$title = isset($_POST['title']) ? sanitizeInput($_POST['title']) : '';
$description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
$booking_date = isset($_POST['booking_date']) ? sanitizeInput($_POST['booking_date']) : '';
$start_time = isset($_POST['start_time']) ? sanitizeInput($_POST['start_time']) : '';
$end_time = isset($_POST['end_time']) ? sanitizeInput($_POST['end_time']) : '';

// Validate input
$errors = [];

if (empty($resource_id)) {
    $errors[] = "Please select a resource.";
}

if (empty($title)) {
    $errors[] = "Booking title is required.";
}

if (empty($booking_date)) {
    $errors[] = "Booking date is required.";
}

if (empty($start_time) || empty($end_time)) {
    $errors[] = "Start and end times are required.";
}

// Format date and times for database
$start_datetime = date('Y-m-d H:i:s', strtotime("$booking_date $start_time"));
$end_datetime = date('Y-m-d H:i:s', strtotime("$booking_date $end_time"));

// Validate start and end times
if (strtotime($end_datetime) <= strtotime($start_datetime)) {
    $errors[] = "End time must be after start time.";
}

// Prevent booking in the past
$current_time = time();
if (strtotime($start_datetime) < $current_time) {
    $errors[] = "Cannot create bookings in the past. Please select a future date and time.";
}

// If there are no errors, try to create the booking
if (empty($errors)) {
    // Initialize managers
    $resourceManager = new ResourceManager();
    $bookingManager = new BookingManager();
    
    // Check if resource exists and is available
    $resource = $resourceManager->getResourceById($resource_id);
    
    if (!$resource || $resource['status'] != 'available') {
        setFlashMessage("Resource not found or not available for booking.", "error");
        redirect('calendar_view.php');
    }
    
    // Create the booking
    $result = $bookingManager->createBooking(
        $_SESSION['user_id'],
        $resource_id,
        $title,
        $description,
        $start_datetime,
        $end_datetime
    );
    
    if ($result === true) {
        // Booking was successful
        setFlashMessage("Booking request submitted successfully.", "success");
        redirect('my_bookings.php');
    } else {
        // Booking failed with an error message
        setFlashMessage($result, "error");
        
        // Redirect back to calendar with the same month/year
        $month = date('m', strtotime($booking_date));
        $year = date('Y', strtotime($booking_date));
        redirect("calendar_view.php?month=$month&year=$year");
    }
} else {
    // There were validation errors
    setFlashMessage($errors[0], "error");
    
    // Redirect back to calendar with the same month/year
    $month = date('m', strtotime($booking_date));
    $year = date('Y', strtotime($booking_date));
    redirect("calendar_view.php?month=$month&year=$year");
} 