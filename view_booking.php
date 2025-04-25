<?php
require_once 'includes/auth.php';
require_once 'includes/utilities.php';
require_once 'classes/BookingManager.php';
require_once 'classes/ResourceManager.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage("Please log in to view booking details.", "error");
    redirect('login.php');
}

// Get booking ID from URL parameter
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Initialize managers
$bookingManager = new BookingManager();
$resourceManager = new ResourceManager();

// Get booking details
$booking = $bookingManager->getBookingById($booking_id);

// Check if booking exists and belongs to current user (unless admin)
if (!$booking || (!isAdmin() && $booking['user_id'] != $_SESSION['user_id'])) {
    setFlashMessage("You don't have permission to view this booking.", "error");
    redirect('my_bookings.php');
}

// Handle booking cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_booking'])) {
    if ($booking['status'] == 'pending' || $booking['status'] == 'confirmed') {
        $result = $bookingManager->cancelBooking($booking_id);
        
        if ($result === true) {
            setFlashMessage("Booking cancelled successfully.", "success");
            redirect('my_bookings.php');
        } else {
            setFlashMessage($result, "error");
        }
    } else {
        setFlashMessage("This booking cannot be cancelled.", "error");
    }
}

// Get resource details
$resource = $resourceManager->getResourceById($booking['resource_id']);

$page_title = 'Booking Details';
$active_page = 'my_bookings';
include 'includes/header.php';
?>

<div class="mb-6">
    <a href="my_bookings.php" class="text-primary-600 hover:text-primary-800">
        <i class="fas fa-arrow-left mr-1"></i> Back to My Bookings
    </a>
</div>

<div class="bg-white overflow-hidden shadow-md rounded-lg gradient-border card-hover">
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Booking #<?php echo $booking['id']; ?></h2>
            
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                <?php
                switch ($booking['status']) {
                    case 'pending':
                        echo 'bg-yellow-100 text-yellow-800';
                        break;
                    case 'confirmed':
                        echo 'bg-gradient-success text-white';
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
        </div>
        
        <div class="flex flex-col md:flex-row md:space-x-6">
            <div class="w-full md:w-2/3">
                <div class="flex flex-col space-y-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900"><?php echo $booking['title']; ?></h3>
                        <p class="mt-1 text-sm text-gray-500"><?php echo $booking['description'] ?: 'No description provided.'; ?></p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div class="bg-gray-50 p-4 rounded-md">
                            <h4 class="text-sm font-medium text-gray-500">Resource</h4>
                            <p class="mt-1 text-sm text-gray-900"><?php echo $booking['resource_name']; ?></p>
                            <p class="text-xs text-gray-500"><?php echo $booking['category_name']; ?></p>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-md">
                            <h4 class="text-sm font-medium text-gray-500">Date & Time</h4>
                            <p class="mt-1 text-sm text-gray-900"><?php echo date('F j, Y', strtotime($booking['start_time'])); ?></p>
                            <p class="text-xs text-gray-500">
                                <?php echo date('H:i', strtotime($booking['start_time'])); ?> - <?php echo date('H:i', strtotime($booking['end_time'])); ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-md">
                            <h4 class="text-sm font-medium text-gray-500">Booking Created</h4>
                            <p class="mt-1 text-sm text-gray-900"><?php echo date('F j, Y', strtotime($booking['created_at'])); ?></p>
                            <p class="text-xs text-gray-500"><?php echo date('H:i', strtotime($booking['created_at'])); ?></p>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-md">
                            <h4 class="text-sm font-medium text-gray-500">Last Updated</h4>
                            <p class="mt-1 text-sm text-gray-900"><?php echo date('F j, Y', strtotime($booking['updated_at'])); ?></p>
                            <p class="text-xs text-gray-500"><?php echo date('H:i', strtotime($booking['updated_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="w-full md:w-1/3 mt-6 md:mt-0">
                <div class="glass-effect rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Resource Details</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Category</p>
                            <p class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                                    <?php echo $booking['category_name']; ?>
                                </span>
                            </p>
                        </div>
                        
                        <?php if (isset($resource['location']) && !empty($resource['location'])): ?>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Location</p>
                            <p class="mt-1 text-sm text-gray-900"><?php echo $resource['location']; ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($resource['capacity']) && !empty($resource['capacity'])): ?>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Capacity</p>
                            <p class="mt-1 text-sm text-gray-900"><?php echo $resource['capacity']; ?> people</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <div class="flex flex-col space-y-3">
                                <a href="view_resource.php?id=<?php echo $booking['resource_id']; ?>" class="inline-flex items-center px-4 py-2 border border-primary-300 shadow-sm text-sm font-medium rounded-md text-primary-700 bg-white hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <i class="fas fa-info-circle mr-2 text-primary-500"></i> View Resource Details
                                </a>
                                
                                <?php if ($booking['status'] == 'pending' || $booking['status'] == 'confirmed'): ?>
                                <form method="POST" action="" class="inline">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" name="cancel_booking" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Are you sure you want to cancel this booking?')">
                                        <i class="fas fa-times-circle mr-2"></i> Cancel Booking
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($booking['status_notes'])): ?>
<div class="mt-6 bg-white overflow-hidden shadow-md rounded-lg card-hover">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Status Notes</h3>
        <div class="bg-gray-50 p-4 rounded-md">
            <p class="text-sm text-gray-900"><?php echo $booking['status_notes']; ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($booking['status'] == 'pending'): ?>
<div class="mt-6 bg-yellow-50 overflow-hidden shadow-md rounded-lg border border-yellow-200">
    <div class="p-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-clock text-yellow-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-medium text-yellow-800">Pending Approval</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>Your booking is awaiting approval from an administrator. You will be notified once it's confirmed.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add any JavaScript functionality here if needed
    });
</script>

<?php include 'includes/footer.php'; ?> 