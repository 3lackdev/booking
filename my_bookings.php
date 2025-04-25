<?php
require_once 'includes/auth.php';
require_once 'includes/utilities.php';
require_once 'classes/BookingManager.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage("You must be logged in to view your bookings.", "error");
    redirect('login.php');
}

// Initialize booking manager
$bookingManager = new BookingManager();

// Get status filter from URL parameter
$status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : null;

// Get user bookings
$bookings = $bookingManager->getBookings($_SESSION['user_id'], null, $status);

// Handle booking cancellation
if (isset($_POST['cancel_booking']) && isset($_POST['booking_id'])) {
    $booking_id = (int)$_POST['booking_id'];
    
    // Check if booking belongs to user
    $booking = $bookingManager->getBookingById($booking_id);
    if ($booking && $booking['user_id'] == $_SESSION['user_id']) {
        $result = $bookingManager->cancelBooking($booking_id);
        
        if ($result === true) {
            setFlashMessage("Booking cancelled successfully.", "success");
        } else {
            setFlashMessage($result, "error");
        }
    } else {
        setFlashMessage("Invalid booking or not authorized to cancel.", "error");
    }
    
    // Redirect to refresh the page and avoid form resubmission
    redirect('my_bookings.php' . ($status ? "?status=$status" : ''));
}

$page_title = 'My Bookings';
$active_page = 'my_bookings';
include 'includes/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <div class="flex space-x-2 mb-4">
            <a href="my_bookings.php" class="<?php echo !$status ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?> px-3 py-2 rounded-md text-sm font-medium">
                All
            </a>
            <a href="my_bookings.php?status=pending" class="<?php echo $status == 'pending' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?> px-3 py-2 rounded-md text-sm font-medium">
                Pending
            </a>
            <a href="my_bookings.php?status=confirmed" class="<?php echo $status == 'confirmed' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?> px-3 py-2 rounded-md text-sm font-medium">
                Confirmed
            </a>
            <a href="my_bookings.php?status=cancelled" class="<?php echo $status == 'cancelled' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?> px-3 py-2 rounded-md text-sm font-medium">
                Cancelled
            </a>
        </div>
    </div>
    
    <div>
        <a href="resources.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Book New Resource
        </a>
    </div>
</div>

<?php if (empty($bookings)): ?>
    <div class="bg-white overflow-hidden shadow-md rounded-lg p-6 text-center">
        <p class="text-gray-500 mb-4">You don't have any<?php echo $status ? " $status" : ''; ?> bookings yet.</p>
        <a href="resources.php" class="text-blue-600 hover:text-blue-800 font-medium">
            Browse resources to make a booking
        </a>
    </div>
<?php else: ?>
    <div class="bg-white overflow-hidden shadow-md rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
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
                            
                            <?php if ($booking['status'] == 'pending' || $booking['status'] == 'confirmed'): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" name="cancel_booking" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to cancel this booking?')">
                                        Cancel
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?> 