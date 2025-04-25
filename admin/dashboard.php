<?php
require_once '../includes/auth.php';
require_once '../includes/utilities.php';
require_once '../classes/ResourceManager.php';
require_once '../classes/BookingManager.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage("You must be an administrator to access this page.", "error");
    redirect('../index.php');
}

// Initialize managers
$resourceManager = new ResourceManager();
$bookingManager = new BookingManager();

// Get resource categories
$categories = $resourceManager->getCategories(false);

// Get pending bookings for approval
$pendingBookings = $bookingManager->getPendingBookings();

$page_title = 'Admin Dashboard';
$active_page = 'admin_dashboard';
include '../includes/header.php';
?>

<div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
    <!-- Resource Categories -->
    <div class="bg-white overflow-hidden shadow-md rounded-lg">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="text-blue-600 text-3xl mr-3">
                    <i class="fas fa-th-large"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Resource Categories</h3>
            </div>
            <p class="mb-3 text-gray-600">Manage resource categories like meeting rooms, vehicles, etc.</p>
            <div class="mt-4">
                <a href="categories.php" class="text-blue-600 hover:text-blue-800 font-medium">
                    Manage Categories <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Resources -->
    <div class="bg-white overflow-hidden shadow-md rounded-lg">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="text-blue-600 text-3xl mr-3">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Resources</h3>
            </div>
            <p class="mb-3 text-gray-600">Manage all resources available for booking.</p>
            <div class="mt-4">
                <a href="resources.php" class="text-blue-600 hover:text-blue-800 font-medium">
                    Manage Resources <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Bookings -->
    <div class="bg-white overflow-hidden shadow-md rounded-lg">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="text-blue-600 text-3xl mr-3">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Bookings</h3>
            </div>
            <p class="mb-3 text-gray-600">View and manage all bookings in the system.</p>
            <div class="mt-4">
                <a href="bookings.php" class="text-blue-600 hover:text-blue-800 font-medium">
                    Manage Bookings <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Users -->
    <div class="bg-white overflow-hidden shadow-md rounded-lg">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="text-blue-600 text-3xl mr-3">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Users</h3>
            </div>
            <p class="mb-3 text-gray-600">Manage user accounts and permissions.</p>
            <div class="mt-4">
                <a href="users.php" class="text-blue-600 hover:text-blue-800 font-medium">
                    Manage Users <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Reports -->
    <div class="bg-white overflow-hidden shadow-md rounded-lg">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="text-blue-600 text-3xl mr-3">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Reports</h3>
            </div>
            <p class="mb-3 text-gray-600">View usage reports and statistics.</p>
            <div class="mt-4">
                <a href="reports.php" class="text-blue-600 hover:text-blue-800 font-medium">
                    View Reports <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- System Settings -->
    <div class="bg-white overflow-hidden shadow-md rounded-lg">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="text-blue-600 text-3xl mr-3">
                    <i class="fas fa-cogs"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Settings</h3>
            </div>
            <p class="mb-3 text-gray-600">Configure system settings and preferences.</p>
            <div class="mt-4">
                <a href="settings.php" class="text-blue-600 hover:text-blue-800 font-medium">
                    Manage Settings <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Pending Bookings Section -->
<div class="mt-10">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Pending Approval Requests</h2>
    
    <?php if (empty($pendingBookings)): ?>
        <div class="bg-white overflow-hidden shadow-md rounded-lg p-6 text-center">
            <p class="text-gray-500">There are no pending bookings requiring approval.</p>
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
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($pendingBookings as $booking): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo $booking['user_name']; ?></div>
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
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="view_booking.php?id=<?php echo $booking['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                    View
                                </a>
                                
                                <a href="approve_booking.php?id=<?php echo $booking['id']; ?>" class="text-green-600 hover:text-green-900 mr-3">
                                    Approve
                                </a>
                                
                                <a href="reject_booking.php?id=<?php echo $booking['id']; ?>" class="text-red-600 hover:text-red-900">
                                    Reject
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (count($pendingBookings) >= 10): ?>
                <div class="bg-gray-50 px-6 py-3 text-right">
                    <a href="bookings.php?status=pending" class="text-blue-600 hover:text-blue-800 font-medium">
                        View all pending bookings <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?> 