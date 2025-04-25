<?php
require_once '../includes/auth.php';
require_once '../includes/utilities.php';
require_once '../classes/BookingManager.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage("You must be an administrator to access this page.", "error");
    redirect('../index.php');
}

// Get booking ID from URL parameter
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Initialize booking manager
$bookingManager = new BookingManager();

// Get booking details
$booking = $bookingManager->getBookingById($booking_id);

// Check if booking exists and is pending
if (!$booking || $booking['status'] != 'pending') {
    setFlashMessage("Invalid booking or booking is not in pending status.", "error");
    redirect('bookings.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $result = $bookingManager->cancelBooking($booking_id);
    
    if ($result === true) {
        setFlashMessage("Booking rejected successfully.", "success");
    } else {
        setFlashMessage($result, "error");
    }
    
    redirect('bookings.php');
}

$page_title = 'Reject Booking';
$active_page = 'admin_bookings';
include '../includes/header.php';
?>

<div class="mb-6">
    <a href="bookings.php" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-1"></i> Back to Bookings
    </a>
</div>

<div class="bg-white overflow-hidden shadow-md rounded-lg p-6 mb-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Booking Details</h3>
    
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <p class="text-sm font-medium text-gray-500">User</p>
            <p class="mt-1 text-sm text-gray-900"><?php echo $booking['user_name']; ?></p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Resource</p>
            <p class="mt-1 text-sm text-gray-900"><?php echo $booking['resource_name']; ?> (<?php echo $booking['category_name']; ?>)</p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Title</p>
            <p class="mt-1 text-sm text-gray-900"><?php echo $booking['title']; ?></p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Status</p>
            <p class="mt-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    Pending
                </span>
            </p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Start Time</p>
            <p class="mt-1 text-sm text-gray-900"><?php echo formatDateTime($booking['start_time']); ?></p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">End Time</p>
            <p class="mt-1 text-sm text-gray-900"><?php echo formatDateTime($booking['end_time']); ?></p>
        </div>
        
        <div class="md:col-span-2">
            <p class="text-sm font-medium text-gray-500">Description</p>
            <p class="mt-1 text-sm text-gray-900"><?php echo $booking['description'] ?: 'No description provided.'; ?></p>
        </div>
    </div>
</div>

<div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="ml-3">
            <p class="text-sm text-red-700">
                Are you sure you want to reject this booking? This will cancel the reservation and notify the user.
            </p>
        </div>
    </div>
</div>

<div class="flex justify-between">
    <a href="bookings.php" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        Cancel
    </a>
    
    <form method="POST">
        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            Reject Booking
        </button>
    </form>
</div>

<?php include '../includes/footer.php'; ?> 