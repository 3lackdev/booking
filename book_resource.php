<?php
require_once 'includes/auth.php';
require_once 'includes/utilities.php';
require_once 'classes/ResourceManager.php';
require_once 'classes/BookingManager.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage("You must be logged in to book resources.", "error");
    redirect('login.php');
}

// Get resource ID from URL parameter
$resource_id = isset($_GET['resource_id']) ? (int)$_GET['resource_id'] : 0;

// Initialize managers
$resourceManager = new ResourceManager();
$bookingManager = new BookingManager();

// Get resource details
$resource = $resourceManager->getResourceById($resource_id);

// Check if resource exists and is available
if (!$resource || $resource['status'] != 'available') {
    setFlashMessage("Resource not found or not available for booking.", "error");
    redirect('resources.php');
}

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $start_time = sanitizeInput($_POST['start_time'] ?? '');
    $end_time = sanitizeInput($_POST['end_time'] ?? '');
    
    // Validate input
    if (empty($title)) {
        $errors[] = "Title is required.";
    }
    
    if (empty($start_time)) {
        $errors[] = "Start time is required.";
    }
    
    if (empty($end_time)) {
        $errors[] = "End time is required.";
    }
    
    if (!empty($start_time) && !empty($end_time)) {
        $start_timestamp = strtotime($start_time);
        $end_timestamp = strtotime($end_time);
        
        // Check if start time is in the past
        if ($start_timestamp < time()) {
            $errors[] = "Start time cannot be in the past.";
        }
        
        // Check if end time is after start time
        if ($end_timestamp <= $start_timestamp) {
            $errors[] = "End time must be after start time.";
        }
    }
    
    // If no validation errors, attempt to create booking
    if (empty($errors)) {
        $result = $bookingManager->createBooking(
            $_SESSION['user_id'],
            $resource_id,
            $title,
            $description,
            $start_time,
            $end_time
        );
        
        if ($result === true) {
            // Successful booking
            setFlashMessage("Booking request submitted successfully.", "success");
            redirect('my_bookings.php');
        } else {
            // Failed booking
            $errors[] = $result;
        }
    }
}

$page_title = 'Book ' . $resource['name'];
$active_page = 'resources';
include 'includes/header.php';
?>

<div class="mb-6">
    <a href="resources.php" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-1"></i> Back to Resources
    </a>
</div>

<div class="bg-white overflow-hidden shadow-md rounded-lg p-6 mb-6">
    <div class="flex flex-col md:flex-row">
        <div class="md:w-2/3 pr-0 md:pr-8">
            <div class="flex justify-between items-start">
                <h2 class="text-2xl font-bold text-gray-800"><?php echo $resource['name']; ?></h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Available
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1"><?php echo $resource['category_name']; ?></p>
            <p class="mt-4 text-gray-600"><?php echo $resource['description']; ?></p>
            
            <div class="mt-6 text-sm text-gray-500">
                <?php if ($resource['location']): ?>
                    <p class="mb-2">
                        <span class="font-medium">Location:</span> 
                        <?php echo $resource['location']; ?>
                    </p>
                <?php endif; ?>
                
                <?php if ($resource['capacity']): ?>
                    <p>
                        <span class="font-medium">Capacity:</span> 
                        <?php echo $resource['capacity']; ?> people
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="bg-white overflow-hidden shadow-md rounded-lg p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Book This Resource</h3>
    
    <form method="POST">
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p class="font-bold">Error</p>
                <ul class="mt-2">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700">Booking Title</label>
                <input type="text" name="title" id="title" value="<?php echo isset($title) ? $title : ''; ?>" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>
            
            <div class="col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                <textarea name="description" id="description" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"><?php echo isset($description) ? $description : ''; ?></textarea>
            </div>
            
            <div>
                <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                <input type="text" name="start_time" id="start_time" value="<?php echo isset($start_time) ? $start_time : ''; ?>" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md datetime-picker">
            </div>
            
            <div>
                <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                <input type="text" name="end_time" id="end_time" value="<?php echo isset($end_time) ? $end_time : ''; ?>" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md datetime-picker">
            </div>
        </div>
        
        <div class="mt-6">
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Submit Booking Request
            </button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?> 