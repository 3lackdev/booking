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

// Get booking date from URL parameter if available
$booking_date = isset($_GET['booking_date']) ? sanitizeInput($_GET['booking_date']) : null;

// Initialize default start and end time values
$default_start_time = '';
$default_end_time = '';

// If booking date is provided, set default start and end times
if ($booking_date) {
    // Set default start time to 9:00 AM on the selected date
    $default_start_time = date('Y-m-d H:i:s', strtotime("$booking_date 09:00:00"));
    // Set default end time to 10:00 AM on the selected date
    $default_end_time = date('Y-m-d H:i:s', strtotime("$booking_date 10:00:00"));
}

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
    
    // Check if booking is in the past
    if (!empty($start_time) && strtotime($start_time) < time()) {
        $errors[] = "Cannot create bookings in the past. Please select a future date and time.";
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

<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="resources.php" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-1"></i> Back to Resources
        </a>
    </div>
    
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <?php if (!empty($resource['image_path'])): ?>
            <div class="w-full h-64 md:h-80 bg-gray-200 overflow-hidden">
                <img src="<?php echo $resource['image_path']; ?>" alt="<?php echo $resource['name']; ?>" class="w-full h-full object-cover">
            </div>
        <?php endif; ?>
        
        <div class="p-6">
            <div class="flex flex-wrap items-start justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900"><?php echo $resource['name']; ?></h1>
                    <p class="text-sm text-gray-500">Category: <?php echo $resource['category_name']; ?></p>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                    <?php
                    switch ($resource['status']) {
                        case 'available':
                            echo 'bg-green-100 text-green-800';
                            break;
                        case 'maintenance':
                            echo 'bg-yellow-100 text-yellow-800';
                            break;
                        case 'inactive':
                            echo 'bg-red-100 text-red-800';
                            break;
                    }
                    ?>
                ">
                    <?php echo ucfirst($resource['status']); ?>
                </span>
            </div>
            
            <!-- Resource Details -->
            <div class="mb-8">
                <h2 class="text-lg font-medium text-gray-900 mb-2">Resource Details</h2>
                
                <?php if (!empty($resource['description'])): ?>
                    <div class="mb-4">
                        <p class="text-gray-700"><?php echo nl2br($resource['description']); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <?php if (!empty($resource['location'])): ?>
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                            <span class="text-gray-700"><?php echo $resource['location']; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($resource['capacity'])): ?>
                        <div class="flex items-center">
                            <i class="fas fa-users text-gray-400 mr-2"></i>
                            <span class="text-gray-700">Capacity: <?php echo $resource['capacity']; ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Booking Form -->
            <?php if ($resource['status'] == 'available'): ?>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Book this resource</h3>
                    
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700">Booking Title</label>
                            <input type="text" name="title" id="title" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                            <input type="text" name="start_time" id="start_time" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md datetime-picker" value="<?php echo $default_start_time; ?>" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                            <input type="text" name="end_time" id="end_time" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md datetime-picker" value="<?php echo $default_end_time; ?>" required>
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
            <?php else: ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                This resource is currently not available for booking.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Upcoming Bookings section... -->
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
        
        // Add form validation
        const bookingForm = document.querySelector('form[method="POST"]');
        if (bookingForm) {
            bookingForm.addEventListener('submit', function(event) {
                const startTimeInput = document.getElementById('start_time');
                const endTimeInput = document.getElementById('end_time');
                const titleInput = document.getElementById('title');
                
                if (!startTimeInput || !endTimeInput || !titleInput) return;
                
                if (!titleInput.value.trim()) {
                    event.preventDefault();
                    alert('Please enter a booking title.');
                    return;
                }
                
                if (!startTimeInput.value || !endTimeInput.value) {
                    event.preventDefault();
                    alert('Start and end times are required.');
                    return;
                }
                
                const startTime = new Date(startTimeInput.value);
                const endTime = new Date(endTimeInput.value);
                const now = new Date();
                
                if (startTime < now) {
                    event.preventDefault();
                    alert('Cannot create bookings in the past. Please select a future date and time.');
                    return;
                }
                
                if (startTime >= endTime) {
                    event.preventDefault();
                    alert('End time must be after start time.');
                    return;
                }
            });
        }
    });
</script>

<?php include 'includes/footer.php'; ?> 