<?php
require_once 'includes/auth.php';
require_once 'includes/utilities.php';
require_once 'classes/ResourceManager.php';
require_once 'classes/BookingManager.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage("Please log in to view resources.", "error");
    redirect('login.php');
}

// Get resource ID from URL parameter
$resource_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Initialize managers
$resourceManager = new ResourceManager();
$bookingManager = new BookingManager();

// Get resource details
$resource = $resourceManager->getResourceById($resource_id);

// Check if resource exists
if (!$resource) {
    setFlashMessage("Invalid resource ID.", "error");
    redirect('resources.php');
}

// Get upcoming bookings for this resource
$today = date('Y-m-d');
$end_date = date('Y-m-d', strtotime('+30 days'));
$upcomingBookings = $bookingManager->getBookingsByDateRange($resource_id, $today, $end_date);

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_booking'])) {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $start_time = sanitizeInput($_POST['start_time']);
    $end_time = sanitizeInput($_POST['end_time']);
    
    // Validate input
    $errors = [];
    
    if (empty($title)) {
        $errors[] = "Booking title is required.";
    }
    
    if (empty($start_time) || empty($end_time)) {
        $errors[] = "Start and end times are required.";
    }
    
    // If no errors, create booking
    if (empty($errors)) {
        $result = $bookingManager->createBooking($_SESSION['user_id'], $resource_id, $title, $description, $start_time, $end_time);
        
        if ($result === true) {
            setFlashMessage("Booking request submitted successfully.", "success");
            redirect('my_bookings.php');
        } else {
            setFlashMessage($result, "error");
        }
    } else {
        setFlashMessage($errors[0], "error");
    }
}

$page_title = $resource['name'];
$active_page = 'resources';
include 'includes/header.php';
?>

<div class="mb-6">
    <a href="resources.php" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-1"></i> Back to Resources
    </a>
</div>

<div class="bg-white overflow-hidden shadow-md rounded-lg">
    <div class="p-6">
        <div class="flex flex-col md:flex-row">
            <div class="w-full md:w-2/3 pr-0 md:pr-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4"><?php echo $resource['name']; ?></h2>
                
                <div class="mb-6">
                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <?php echo $resource['category_name']; ?>
                    </div>
                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $resource['status'] == 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> ml-2">
                        <?php echo ucfirst($resource['status']); ?>
                    </div>
                </div>
                
                <div class="prose max-w-none mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Description</h3>
                    <p><?php echo $resource['description'] ?: 'No description available.'; ?></p>
                </div>
                
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Location</p>
                            <p class="mt-1 text-sm text-gray-900"><?php echo $resource['location'] ?: 'N/A'; ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Capacity</p>
                            <p class="mt-1 text-sm text-gray-900"><?php echo $resource['capacity'] ? $resource['capacity'] . ' people' : 'N/A'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="w-full md:w-1/3 mt-6 md:mt-0">
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Book this resource</h3>
                    
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700">Booking Title</label>
                            <input type="text" name="title" id="title" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                            <input type="text" name="start_time" id="start_time" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md datetime-picker" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                            <input type="text" name="end_time" id="end_time" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md datetime-picker" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" name="create_booking" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-calendar-plus mr-2"></i> Book Now
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Upcoming bookings -->
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Upcoming Bookings</h3>
            
            <?php if (empty($upcomingBookings)): ?>
                <p class="text-gray-500">No upcoming bookings for this resource.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booked By</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($upcomingBookings as $booking): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('Y-m-d', strtotime($booking['start_time'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('H:i', strtotime($booking['start_time'])); ?> - <?php echo date('H:i', strtotime($booking['end_time'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $booking['title']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $booking['user_name']; ?>
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
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Initialize the datetime pickers with additional settings
    document.addEventListener('DOMContentLoaded', function() {
        const datetimeInputs = document.querySelectorAll('.datetime-picker');
        const today = new Date();
        
        if (datetimeInputs.length > 0) {
            datetimeInputs.forEach(function(input) {
                flatpickr(input, {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    time_24hr: true,
                    minDate: today,
                    minuteIncrement: 15
                });
            });
        }
    });
</script>

<?php include 'includes/footer.php'; ?> 