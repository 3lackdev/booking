<?php
require_once '../includes/auth.php';
require_once '../includes/utilities.php';
require_once '../classes/BookingManager.php';
require_once '../classes/ResourceManager.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage("You must be an administrator to access this page.", "error");
    redirect('../index.php');
}

// Get booking ID from URL parameter
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Initialize managers
$bookingManager = new BookingManager();
$resourceManager = new ResourceManager();

// Get booking details
$booking = $bookingManager->getBookingById($booking_id);

// Check if booking exists
if (!$booking) {
    setFlashMessage("Invalid booking ID.", "error");
    redirect('bookings.php');
}

$page_title = 'View Booking';
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
            <p class="text-sm font-medium text-gray-500">Booking ID</p>
            <p class="mt-1 text-sm text-gray-900">#<?php echo $booking['id']; ?></p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Created At</p>
            <p class="mt-1 text-sm text-gray-900"><?php echo formatDateTime($booking['created_at']); ?></p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">User</p>
            <p class="mt-1 text-sm text-gray-900"><?php echo $booking['user_name']; ?> (<?php echo $booking['username']; ?>)</p>
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
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                    <?php
                    switch ($booking['status']) {
                        case 'pending':
                            echo 'bg-yellow-100 text-yellow-800';
                            break;
                        case 'confirmed':
                            echo 'bg-green-100 text-green-800';
                            break;
                        case 'cancelled':
                            echo 'bg-red-100 text-red-800';
                            break;
                        case 'completed':
                            echo 'bg-gray-100 text-gray-800';
                            break;
                    }
                    ?>
                ">
                    <?php echo ucfirst($booking['status']); ?>
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
        
        <div class="md:col-span-2">
            <p class="text-sm font-medium text-gray-500">Last Updated</p>
            <p class="mt-1 text-sm text-gray-900"><?php echo formatDateTime($booking['updated_at']); ?></p>
        </div>
    </div>
</div>

<div class="flex flex-wrap gap-3">
    <a href="bookings.php" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        Back to List
    </a>
    
    <?php if ($booking['status'] == 'pending'): ?>
        <a href="approve_booking.php?id=<?php echo $booking['id']; ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            Approve
        </a>
        
        <a href="reject_booking.php?id=<?php echo $booking['id']; ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            Reject
        </a>
    <?php endif; ?>
    
    <?php if ($booking['status'] == 'pending' || $booking['status'] == 'confirmed'): ?>
        <form method="POST" action="bookings.php" class="inline">
            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
            <button type="submit" name="cancel_booking" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500" onclick="return confirm('Are you sure you want to cancel this booking?')">
                Cancel
            </button>
        </form>
    <?php endif; ?>
    
    <form method="POST" action="bookings.php" class="inline">
        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
        <button type="submit" name="delete_booking" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500" onclick="return confirm('Are you sure you want to delete this booking? This action cannot be undone.')">
            Delete
        </button>
    </form>
</div>

<?php include '../includes/footer.php'; ?> 