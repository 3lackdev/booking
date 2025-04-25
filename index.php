<?php
require_once 'includes/auth.php';
require_once 'includes/utilities.php';

// Check if database is accessible, if not redirect to setup
function checkDatabaseConnection() {
    // Try connecting to the database
    try {
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check for connection errors
        if ($conn->connect_error) {
            return false;
        }
        
        // Check if essential tables exist
        $tables = ['users', 'resources', 'resource_categories', 'bookings', 'settings'];
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows == 0) {
                $conn->close();
                return false;
            }
        }
        
        $conn->close();
        return true;
    } catch (Throwable $e) {
        // Any exception means database connection failed
        return false;
    }
}

// Redirect to setup if database is not accessible
if (!checkDatabaseConnection()) {
    // Check if setup.php exists
    if (file_exists('setup.php')) {
        redirect('setup.php');
    } else {
        die('Database connection failed and setup file not found. Please check your database configuration or contact the administrator.');
    }
}

require_once 'classes/ResourceManager.php';
require_once 'classes/BookingManager.php';

$page_title = 'Home';
$active_page = 'home';
include 'includes/header.php';

// Initialize resource manager to display categories
$resourceManager = new ResourceManager();
$categories = $resourceManager->getCategories();

// If user is logged in, show upcoming bookings
$upcomingBookings = [];
if (isLoggedIn()) {
    $bookingManager = new BookingManager();
    $upcomingBookings = $bookingManager->getUpcomingBookingsForUser($_SESSION['user_id'], 3);
}
?>

<!-- Main content -->
<div class="flex flex-col space-y-8">
    <!-- Hero section -->
    <div class="bg-blue-600 rounded-lg shadow-lg p-8 text-white">
        <h2 class="text-3xl font-bold mb-3">Welcome to the Booking System</h2>
        <p class="text-xl mb-6">Book meeting rooms, vehicles, and more with our simple, flexible scheduling system.</p>
        
        <?php if (!isLoggedIn()): ?>
            <div class="mt-6">
                <a href="login.php" class="bg-white text-blue-600 hover:bg-blue-50 px-5 py-3 rounded-md text-sm font-medium inline-block mr-3">Login</a>
                <a href="register.php" class="bg-blue-700 text-white hover:bg-blue-800 px-5 py-3 rounded-md text-sm font-medium inline-block">Register</a>
            </div>
        <?php else: ?>
            <a href="resources.php" class="bg-white text-blue-600 hover:bg-blue-50 px-5 py-3 rounded-md text-sm font-medium inline-block">Browse Resources</a>
        <?php endif; ?>
    </div>
    
    <!-- Resource categories -->
    <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Resource Categories</h2>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($categories as $category): ?>
                <div class="bg-white overflow-hidden shadow-md rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900"><?php echo $category['name']; ?></h3>
                        <p class="mt-2 text-gray-600"><?php echo $category['description']; ?></p>
                        <div class="mt-4">
                            <a href="resources.php?category_id=<?php echo $category['id']; ?>" class="text-blue-600 hover:text-blue-800 font-medium">
                                Browse <?php echo $category['name']; ?> <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php if (isLoggedIn() && !empty($upcomingBookings)): ?>
        <!-- Upcoming bookings for logged in users -->
        <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Your Upcoming Bookings</h2>
            <div class="bg-white overflow-hidden shadow-md rounded-lg">
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($upcomingBookings as $booking): ?>
                        <li class="p-4 hover:bg-gray-50">
                            <div class="flex justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900"><?php echo $booking['title']; ?></h3>
                                    <p class="text-sm text-gray-500">
                                        <span class="font-medium"><?php echo $booking['resource_name']; ?></span> 
                                        (<?php echo $booking['category_name']; ?>)
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">
                                        <?php echo formatDate($booking['start_time']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <?php echo date('H:i', strtotime($booking['start_time'])); ?> - 
                                        <?php echo date('H:i', strtotime($booking['end_time'])); ?>
                                    </p>
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
                                            default:
                                                echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>
                                    ">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="bg-gray-50 px-4 py-3 text-right">
                    <a href="my_bookings.php" class="text-blue-600 hover:text-blue-800 font-medium">
                        View all bookings <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Features section -->
    <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Features</h2>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="bg-white overflow-hidden shadow-md rounded-lg">
                <div class="p-6">
                    <div class="text-blue-600 text-3xl mb-3">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Easy Scheduling</h3>
                    <p class="mt-2 text-gray-600">Book resources quickly and easily with our intuitive scheduling system.</p>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-md rounded-lg">
                <div class="p-6">
                    <div class="text-blue-600 text-3xl mb-3">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Multiple Resource Types</h3>
                    <p class="mt-2 text-gray-600">Book meeting rooms, vehicles, equipment, and more all in one place.</p>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-md rounded-lg">
                <div class="p-6">
                    <div class="text-blue-600 text-3xl mb-3">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Admin Controls</h3>
                    <p class="mt-2 text-gray-600">Administrators can manage resources, approve bookings, and view reports.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 