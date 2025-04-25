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

// Initialize managers
$bookingManager = new BookingManager();
$resourceManager = new ResourceManager();

// Get status filter from URL parameter
$status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : null;

// Get bookings
$bookings = $bookingManager->getBookings(null, null, $status);

// Handle booking cancellation
if (isset($_POST['cancel_booking']) && isset($_POST['booking_id'])) {
    $booking_id = (int)$_POST['booking_id'];
    
    $result = $bookingManager->cancelBooking($booking_id);
    
    if ($result === true) {
        setFlashMessage("Booking cancelled successfully.", "success");
    } else {
        setFlashMessage($result, "error");
    }
    
    // Redirect to refresh the page and avoid form resubmission
    redirect('bookings.php' . ($status ? "?status=$status" : ''));
}

// Handle booking deletion
if (isset($_POST['delete_booking']) && isset($_POST['booking_id'])) {
    $booking_id = (int)$_POST['booking_id'];
    
    $result = $bookingManager->deleteBooking($booking_id);
    
    if ($result === true) {
        setFlashMessage("Booking deleted successfully.", "success");
    } else {
        setFlashMessage($result, "error");
    }
    
    // Redirect to refresh the page and avoid form resubmission
    redirect('bookings.php' . ($status ? "?status=$status" : ''));
}

$page_title = 'Manage Bookings';
$active_page = 'admin_bookings';
include '../includes/header.php';
?>

<div class="mb-6">
    <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
    </a>
</div>

<div class="flex justify-between items-center mb-6">
    <div>
        <div class="flex space-x-2 mb-4">
            <a href="bookings.php" class="<?php echo !$status ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?> px-3 py-2 rounded-md text-sm font-medium">
                All
            </a>
            <a href="bookings.php?status=pending" class="<?php echo $status == 'pending' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?> px-3 py-2 rounded-md text-sm font-medium">
                Pending
            </a>
            <a href="bookings.php?status=confirmed" class="<?php echo $status == 'confirmed' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?> px-3 py-2 rounded-md text-sm font-medium">
                Confirmed
            </a>
            <a href="bookings.php?status=cancelled" class="<?php echo $status == 'cancelled' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?> px-3 py-2 rounded-md text-sm font-medium">
                Cancelled
            </a>
            <a href="bookings.php?status=completed" class="<?php echo $status == 'completed' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?> px-3 py-2 rounded-md text-sm font-medium">
                Completed
            </a>
        </div>
    </div>
</div>

<?php if (empty($bookings)): ?>
    <div class="bg-white overflow-hidden shadow-md rounded-lg p-6 text-center">
        <p class="text-gray-500 mb-4">No<?php echo $status ? " $status" : ''; ?> bookings found.</p>
    </div>
<?php else: ?>
    <div class="bg-white overflow-hidden shadow-md rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        User
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Resource
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Date & Time
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo $booking['user_name']; ?></div>
                            <div class="text-sm text-gray-500"><?php echo $booking['username']; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo $booking['title']; ?></div>
                            <div class="text-sm text-gray-500">
                                <?php echo $booking['resource_name']; ?> (<?php echo $booking['category_name']; ?>)
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo formatDate($booking['start_time']); ?></div>
                            <div class="text-sm text-gray-500">
                                <?php echo date('H:i', strtotime($booking['start_time'])); ?> - 
                                <?php echo date('H:i', strtotime($booking['end_time'])); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
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
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="view_booking.php?id=<?php echo $booking['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                View
                            </a>
                            
                            <?php if ($booking['status'] == 'pending'): ?>
                                <a href="approve_booking.php?id=<?php echo $booking['id']; ?>" class="text-green-600 hover:text-green-900 mr-3">
                                    Approve
                                </a>
                                
                                <a href="reject_booking.php?id=<?php echo $booking['id']; ?>" class="text-red-600 hover:text-red-900 mr-3">
                                    Reject
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($booking['status'] == 'pending' || $booking['status'] == 'confirmed'): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" name="cancel_booking" class="text-orange-600 hover:text-orange-900 mr-3" onclick="return confirm('Are you sure you want to cancel this booking?')">
                                        Cancel
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="POST" class="inline">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                <button type="submit" name="delete_booking" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this booking? This action cannot be undone.')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?> 