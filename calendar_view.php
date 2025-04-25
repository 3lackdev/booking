<?php
require_once 'includes/auth.php';
require_once 'includes/utilities.php';
require_once 'classes/BookingManager.php';
require_once 'classes/ResourceManager.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage("Please log in to view the calendar.", "error");
    redirect('login.php');
}

// Initialize managers
$bookingManager = new BookingManager();
$resourceManager = new ResourceManager();

// Get current month/year
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Validate month/year
if ($month < 1 || $month > 12) {
    $month = (int)date('m');
}

// Get selected resource_id (optional)
$resource_id = isset($_GET['resource_id']) ? (int)$_GET['resource_id'] : null;

// Get all resources for filter dropdown
$resources = $resourceManager->getAllResources();

// Calculate next and previous month
$nextMonth = $month == 12 ? 1 : $month + 1;
$nextYear = $month == 12 ? $year + 1 : $year;
$prevMonth = $month == 1 ? 12 : $month - 1;
$prevYear = $month == 1 ? $year - 1 : $year;

// Get first day of the month
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDayOfMonth);
$firstDayOfWeek = date('w', $firstDayOfMonth);

// Get start and end date for bookings
$start_date = date('Y-m-d', $firstDayOfMonth);
$end_date = date('Y-m-d', mktime(0, 0, 0, $month, $daysInMonth, $year));

// Get bookings for this month
$bookings = $bookingManager->getBookings(isAdmin() ? null : $_SESSION['user_id'], $resource_id, null, $start_date, $end_date);

// Organize bookings by date
$bookingsByDate = [];
foreach ($bookings as $booking) {
    $bookingDate = date('Y-m-d', strtotime($booking['start_time']));
    if (!isset($bookingsByDate[$bookingDate])) {
        $bookingsByDate[$bookingDate] = [];
    }
    $bookingsByDate[$bookingDate][] = $booking;
}

$page_title = 'Calendar View';
$active_page = 'calendar';
include 'includes/header.php';
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-800"><?php echo date('F Y', $firstDayOfMonth); ?></h1>
    
    <div class="mt-4 md:mt-0 flex items-center space-x-4">
        <a href="calendar_view.php?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?><?php echo $resource_id ? "&resource_id=$resource_id" : ''; ?>" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-chevron-left"></i> Previous Month
        </a>
        
        <a href="calendar_view.php" class="text-blue-600 hover:text-blue-800">
            Today
        </a>
        
        <a href="calendar_view.php?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?><?php echo $resource_id ? "&resource_id=$resource_id" : ''; ?>" class="text-blue-600 hover:text-blue-800">
            Next Month <i class="fas fa-chevron-right"></i>
        </a>
    </div>
</div>

<div class="mb-6">
    <div class="flex flex-col md:flex-row justify-between items-center">
        <a href="my_bookings.php" class="text-blue-600 hover:text-blue-800 mb-3 md:mb-0">
            <i class="fas fa-list mr-1"></i> Switch to List View
        </a>
        
        <form action="calendar_view.php" method="GET" class="flex items-center">
            <input type="hidden" name="month" value="<?php echo $month; ?>">
            <input type="hidden" name="year" value="<?php echo $year; ?>">
            
            <label for="resource_id" class="mr-2">Filter by Resource:</label>
            <select name="resource_id" id="resource_id" class="form-select rounded-md shadow-sm mr-2">
                <option value="">All Resources</option>
                <?php foreach ($resources as $resource): ?>
                    <option value="<?php echo $resource['id']; ?>" <?php echo $resource_id == $resource['id'] ? 'selected' : ''; ?>>
                        <?php echo $resource['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                Apply Filter
            </button>
        </form>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="grid grid-cols-7 bg-gray-50">
        <div class="text-center py-2 font-medium text-gray-500">Sunday</div>
        <div class="text-center py-2 font-medium text-gray-500">Monday</div>
        <div class="text-center py-2 font-medium text-gray-500">Tuesday</div>
        <div class="text-center py-2 font-medium text-gray-500">Wednesday</div>
        <div class="text-center py-2 font-medium text-gray-500">Thursday</div>
        <div class="text-center py-2 font-medium text-gray-500">Friday</div>
        <div class="text-center py-2 font-medium text-gray-500">Saturday</div>
    </div>
    
    <div class="grid grid-cols-7 border-t">
        <?php 
        // Fill in the blanks for the first week
        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            echo '<div class="min-h-[120px] border-b border-r p-1 bg-gray-100"></div>';
        }
        
        // Display days of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $isToday = $date == date('Y-m-d');
            $isPast = $date < date('Y-m-d');
            $dayClass = $isToday ? 'bg-blue-50' : ($isPast ? 'bg-gray-50' : '');
            
            echo '<div class="min-h-[120px] border-b border-r p-1 ' . $dayClass . '">';
            echo '<div class="flex justify-between items-center mb-2">';
            echo '<span class="' . ($isToday ? 'font-bold text-blue-600' : ($isPast ? 'text-gray-400' : '')) . '">' . $day . '</span>';
            
            // Add button to create a booking directly for this day (disable for past dates)
            if (!$isPast) {
                echo '<button type="button" onclick="openBookingModal(\'' . $date . '\')" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded">';
                echo '<i class="fas fa-plus"></i>';
                echo '</button>';
            }
            
            echo '</div>';
            
            // Display bookings for this day
            if (isset($bookingsByDate[$date])) {
                echo '<div class="space-y-1 overflow-y-auto max-h-[90px]">';
                foreach ($bookingsByDate[$date] as $booking) {
                    $statusColorClass = '';
                    switch ($booking['status']) {
                        case 'pending':
                            $statusColorClass = 'bg-yellow-200 border-yellow-300';
                            break;
                        case 'confirmed':
                            $statusColorClass = 'bg-green-200 border-green-300';
                            break;
                        case 'cancelled':
                            $statusColorClass = 'bg-red-200 border-red-300';
                            break;
                        case 'completed':
                            $statusColorClass = 'bg-gray-200 border-gray-300';
                            break;
                    }
                    
                    echo '<a href="view_booking.php?id=' . $booking['id'] . '" class="block text-xs p-1 rounded border ' . $statusColorClass . '">';
                    echo '<div class="font-semibold truncate">' . $booking['title'] . '</div>';
                    echo '<div class="truncate">' . date('H:i', strtotime($booking['start_time'])) . ' - ' . date('H:i', strtotime($booking['end_time'])) . '</div>';
                    echo '<div class="truncate">' . $booking['resource_name'] . '</div>';
                    echo '</a>';
                }
                echo '</div>';
            }
            
            echo '</div>';
            
            // Start a new row when reaching Saturday
            if (($day + $firstDayOfWeek) % 7 == 0) {
                echo '';
            }
        }
        
        // Fill in the blanks for the last week
        $remainingCells = 7 - (($daysInMonth + $firstDayOfWeek) % 7);
        if ($remainingCells < 7) {
            for ($i = 0; $i < $remainingCells; $i++) {
                echo '<div class="min-h-[120px] border-b border-r p-1 bg-gray-100"></div>';
            }
        }
        ?>
    </div>
</div>

<div class="mt-6">
    <h2 class="text-lg font-medium text-gray-800 mb-2">Legend</h2>
    <div class="flex flex-wrap gap-4">
        <div class="flex items-center">
            <div class="w-4 h-4 bg-yellow-200 border border-yellow-300 rounded mr-2"></div>
            <span class="text-sm">Pending</span>
        </div>
        <div class="flex items-center">
            <div class="w-4 h-4 bg-green-200 border border-green-300 rounded mr-2"></div>
            <span class="text-sm">Confirmed</span>
        </div>
        <div class="flex items-center">
            <div class="w-4 h-4 bg-red-200 border border-red-300 rounded mr-2"></div>
            <span class="text-sm">Cancelled</span>
        </div>
        <div class="flex items-center">
            <div class="w-4 h-4 bg-gray-200 border border-gray-300 rounded mr-2"></div>
            <span class="text-sm">Completed</span>
        </div>
    </div>
</div>

<!-- Booking Modal -->
<div id="bookingModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Create New Booking</h3>
            <button type="button" onclick="closeBookingModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="bookingForm" method="POST" action="process_booking.php" onsubmit="return validateBookingForm()">
            <input type="hidden" id="booking_date" name="booking_date" value="">
            
            <div class="mb-4">
                <label for="resource_id" class="block text-sm font-medium text-gray-700">Resource</label>
                <select id="resource_id" name="resource_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    <option value="">Select a resource</option>
                    <?php foreach ($resources as $resource): ?>
                        <option value="<?php echo $resource['id']; ?>"><?php echo $resource['name']; ?> (<?php echo $resource['category_name']; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Booking Title</label>
                <input type="text" id="title" name="title" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                    <input type="text" id="start_time" name="start_time" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md modal-time-picker" required>
                </div>
                
                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                    <input type="text" id="end_time" name="end_time" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md modal-time-picker" required>
                </div>
            </div>
            
            <div class="mb-4 mt-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                <textarea id="description" name="description" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
            </div>
            
            <div class="mt-5 sm:mt-6 flex justify-end">
                <button type="button" onclick="closeBookingModal()" class="mr-3 inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </button>
                <button type="submit" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Create Booking
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize flatpickr for time-only inputs in the modal
        const timeInputs = document.querySelectorAll('.modal-time-picker');
        if (timeInputs.length > 0) {
            timeInputs.forEach(function(input) {
                flatpickr(input, {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    time_24hr: true,
                    minuteIncrement: 15
                });
            });
        }
    });
    
    function openBookingModal(date) {
        // Set the booking date in the hidden field
        document.getElementById('booking_date').value = date;
        
        // Set default start and end times (9:00 AM to 10:00 AM)
        const today = new Date();
        const selectedDate = new Date(date);
        
        // Format today without time component for comparison
        const todayDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        const selectedDateOnly = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate());
        
        // Ensure the date is not in the past
        if (selectedDateOnly < todayDate) {
            alert("Cannot create bookings for past dates.");
            return;
        }
        
        // Set default times
        let defaultStartHour = '09:00';
        let defaultEndHour = '10:00';
        
        // If booking is for today, ensure default times are in the future
        if (selectedDateOnly.getTime() === todayDate.getTime()) {
            const currentHour = today.getHours();
            const currentMinute = today.getMinutes();
            
            // If current time is past 9:00 AM, set start time to next hour
            if (currentHour >= 9) {
                const nextHour = currentHour + 1;
                defaultStartHour = (nextHour < 10 ? '0' : '') + nextHour + ':00';
                defaultEndHour = (nextHour + 1 < 10 ? '0' : '') + (nextHour + 1) + ':00';
            }
        }
        
        document.getElementById('start_time').value = defaultStartHour;
        document.getElementById('end_time').value = defaultEndHour;
        
        // Show the modal
        document.getElementById('bookingModal').classList.remove('hidden');
    }
    
    function closeBookingModal() {
        // Hide the modal and reset form
        document.getElementById('bookingModal').classList.add('hidden');
        document.getElementById('bookingForm').reset();
    }
    
    function validateBookingForm() {
        const resourceId = document.getElementById('resource_id').value;
        const title = document.getElementById('title').value;
        const bookingDate = document.getElementById('booking_date').value;
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;
        
        // Basic validation
        if (!resourceId) {
            alert("Please select a resource.");
            return false;
        }
        
        if (!title) {
            alert("Please enter a booking title.");
            return false;
        }
        
        if (!startTime || !endTime) {
            alert("Start and end times are required.");
            return false;
        }
        
        // Check if end time is after start time
        if (startTime >= endTime) {
            alert("End time must be after start time.");
            return false;
        }
        
        // Check if booking is in the past
        const now = new Date();
        const bookingStart = new Date(`${bookingDate}T${startTime}`);
        
        if (bookingStart < now) {
            alert("Cannot create bookings in the past. Please select a future time.");
            return false;
        }
        
        return true;
    }
</script>

<?php include 'includes/footer.php'; ?> 