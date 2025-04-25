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

// Get database connection
$conn = getDbConnection();

// Set default period if not specified
$period = isset($_GET['period']) ? sanitizeInput($_GET['period']) : 'monthly';

// Set date range based on period
$today = date('Y-m-d');
$endDate = $today;

switch($period) {
    case 'daily':
        $startDate = $today;
        $groupBy = "DATE_FORMAT(start_time, '%H:00')";
        $groupByLabel = "Hour";
        break;
    case 'weekly':
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $groupBy = "DATE(start_time)";
        $groupByLabel = "Day";
        break;
    case 'monthly':
    default:
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $groupBy = "DATE(start_time)";
        $groupByLabel = "Day";
        break;
}

// Get booking statistics
$sql = "SELECT COUNT(*) AS total_bookings FROM bookings";
$result = $conn->query($sql);
$totalBookings = $result->fetch_assoc()['total_bookings'];

$sql = "SELECT status, COUNT(*) AS count FROM bookings GROUP BY status";
$result = $conn->query($sql);
$bookingsByStatus = [];
while ($row = $result->fetch_assoc()) {
    $bookingsByStatus[$row['status']] = $row['count'];
}

// Get booking trend data
$sql = "SELECT $groupBy AS date_group, COUNT(*) AS count 
        FROM bookings 
        WHERE start_time BETWEEN ? AND ? 
        GROUP BY date_group 
        ORDER BY date_group";
$stmt = $conn->prepare($sql);
$startDateTime = $startDate . ' 00:00:00';
$endDateTime = $endDate . ' 23:59:59';
$stmt->bind_param("ss", $startDateTime, $endDateTime);
$stmt->execute();
$result = $stmt->get_result();
$bookingTrend = [];
while ($row = $result->fetch_assoc()) {
    $bookingTrend[$row['date_group']] = $row['count'];
}

// Get most booked resources
$sql = "SELECT r.id, r.name, COUNT(b.id) AS booking_count
        FROM resources r
        JOIN bookings b ON r.id = b.resource_id
        GROUP BY r.id
        ORDER BY booking_count DESC
        LIMIT 5";
$result = $conn->query($sql);
$mostBookedResources = [];
while ($row = $result->fetch_assoc()) {
    $mostBookedResources[] = $row;
}

// Get most active users
$sql = "SELECT u.id, u.username, u.full_name, COUNT(b.id) AS booking_count
        FROM users u
        JOIN bookings b ON u.id = b.user_id
        GROUP BY u.id
        ORDER BY booking_count DESC
        LIMIT 5";
$result = $conn->query($sql);
$mostActiveUsers = [];
while ($row = $result->fetch_assoc()) {
    $mostActiveUsers[] = $row;
}

// Get resource categories with booking counts
$sql = "SELECT rc.id, rc.name, COUNT(b.id) AS booking_count
        FROM resource_categories rc
        JOIN resources r ON rc.id = r.category_id
        JOIN bookings b ON r.id = b.resource_id
        GROUP BY rc.id
        ORDER BY booking_count DESC";
$result = $conn->query($sql);
$categoriesWithBookings = [];
while ($row = $result->fetch_assoc()) {
    $categoriesWithBookings[] = $row;
}

// Close database connection
closeDbConnection($conn);

$page_title = 'Reports & Analytics';
$active_page = 'admin_reports';
include '../includes/header.php';
?>

<div class="mb-6">
    <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
    </a>
</div>

<div class="mb-6 flex justify-between items-center">
    <h2 class="text-xl font-semibold text-gray-800">System Reports & Analytics</h2>
    
    <div class="flex space-x-2">
        <a href="reports.php?period=daily" class="<?php echo $period == 'daily' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?> px-3 py-2 rounded-md text-sm font-medium">
            Daily
        </a>
        <a href="reports.php?period=weekly" class="<?php echo $period == 'weekly' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?> px-3 py-2 rounded-md text-sm font-medium">
            Weekly
        </a>
        <a href="reports.php?period=monthly" class="<?php echo $period == 'monthly' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?> px-3 py-2 rounded-md text-sm font-medium">
            Monthly
        </a>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white overflow-hidden shadow-md rounded-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                <i class="fas fa-calendar-check text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">Total Bookings</p>
                <p class="text-xl font-semibold text-gray-900"><?php echo $totalBookings; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white overflow-hidden shadow-md rounded-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-500 mr-4">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">Pending</p>
                <p class="text-xl font-semibold text-gray-900"><?php echo $bookingsByStatus['pending'] ?? 0; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white overflow-hidden shadow-md rounded-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                <i class="fas fa-check text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">Confirmed</p>
                <p class="text-xl font-semibold text-gray-900"><?php echo $bookingsByStatus['confirmed'] ?? 0; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white overflow-hidden shadow-md rounded-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-100 text-red-500 mr-4">
                <i class="fas fa-times text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">Cancelled</p>
                <p class="text-xl font-semibold text-gray-900"><?php echo $bookingsByStatus['cancelled'] ?? 0; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="flex flex-col md:flex-row gap-6">
    <!-- Booking Trend -->
    <div class="w-full md:w-2/3 bg-white overflow-hidden shadow-md rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Booking Trend (<?php echo ucfirst($period); ?>)</h3>
        
        <div class="h-64">
            <canvas id="bookingTrendChart"></canvas>
        </div>
    </div>
    
    <!-- Most Booked Resources -->
    <div class="w-full md:w-1/3 bg-white overflow-hidden shadow-md rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Most Booked Resources</h3>
        
        <?php if (empty($mostBookedResources)): ?>
            <p class="text-gray-500">No booking data available.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($mostBookedResources as $resource): ?>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700"><?php echo $resource['name']; ?></span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <?php echo $resource['booking_count']; ?> bookings
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo min(100, ($resource['booking_count'] / ($mostBookedResources[0]['booking_count'] ?: 1)) * 100); ?>%"></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Most Active Users -->
    <div class="bg-white overflow-hidden shadow-md rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Most Active Users</h3>
        
        <?php if (empty($mostActiveUsers)): ?>
            <p class="text-gray-500">No user activity data available.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Bookings
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($mostActiveUsers as $user): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $user['full_name']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $user['username']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo $user['booking_count']; ?> bookings
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Category Breakdown -->
    <div class="bg-white overflow-hidden shadow-md rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Bookings by Category</h3>
        
        <?php if (empty($categoriesWithBookings)): ?>
            <p class="text-gray-500">No category data available.</p>
        <?php else: ?>
            <div class="h-64">
                <canvas id="categoryChart"></canvas>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Booking Trend Chart
    const bookingTrendCtx = document.getElementById('bookingTrendChart').getContext('2d');
    const bookingTrendChart = new Chart(bookingTrendCtx, {
        type: 'line',
        data: {
            labels: [<?php echo "'" . implode("', '", array_keys($bookingTrend)) . "'"; ?>],
            datasets: [{
                label: 'Number of Bookings',
                data: [<?php echo implode(", ", array_values($bookingTrend)); ?>],
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        title: function(tooltipItems) {
                            return '<?php echo $groupByLabel; ?>: ' + tooltipItems[0].label;
                        }
                    }
                }
            }
        }
    });
    
    // Category Breakdown Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(categoryCtx, {
        type: 'pie',
        data: {
            labels: [<?php 
                $categoryNames = array_map(function($category) { 
                    return "'".$category['name']."'"; 
                }, $categoriesWithBookings);
                echo implode(", ", $categoryNames); 
            ?>],
            datasets: [{
                data: [<?php 
                    $categoryCounts = array_map(function($category) { 
                        return $category['booking_count']; 
                    }, $categoriesWithBookings);
                    echo implode(", ", $categoryCounts); 
                ?>],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(16, 185, 129, 0.7)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(239, 68, 68, 0.7)',
                    'rgba(139, 92, 246, 0.7)',
                    'rgba(236, 72, 153, 0.7)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.raw + ' bookings';
                        }
                    }
                }
            }
        }
    });
</script>

<?php include '../includes/footer.php'; ?> 