<?php
require_once '../includes/auth.php';
require_once '../includes/utilities.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage("You must be an administrator to access this page.", "error");
    redirect('../index.php');
}

// Initialize database connection
$conn = getDbConnection();

// Define default settings if they don't exist
$defaultSettings = [
    'site_name' => 'Booking System',
    'site_description' => 'Resource booking management system',
    'contact_email' => 'admin@example.com',
    'booking_approval_required' => '1',
    'max_booking_days_ahead' => '30',
    'max_booking_duration_hours' => '8',
    'notification_emails_enabled' => '1',
    'cancellation_policy' => 'Bookings can be cancelled up to 24 hours before the scheduled time.'
];

// Check if settings table exists
$tableCheckSql = "SELECT 1 FROM information_schema.tables WHERE table_schema = 'booking_system' AND table_name = 'settings' LIMIT 1";
$tableExists = $conn->query($tableCheckSql)->num_rows > 0;

// Create settings table if it doesn't exist
if (!$tableExists) {
    $createTableSql = "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) NOT NULL UNIQUE,
        setting_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createTableSql) === TRUE) {
        // Insert default settings
        foreach ($defaultSettings as $key => $value) {
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }
    } else {
        die("Error creating settings table: " . $conn->error);
    }
}

// Get current settings from database
$settings = [];
$sql = "SELECT setting_key, setting_value FROM settings";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} else {
    // Use default settings if no settings in database
    $settings = $defaultSettings;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_settings'])) {
    $updatedSettings = [
        'site_name' => sanitizeInput($_POST['site_name'] ?? $settings['site_name']),
        'site_description' => sanitizeInput($_POST['site_description'] ?? $settings['site_description']),
        'contact_email' => sanitizeInput($_POST['contact_email'] ?? $settings['contact_email']),
        'booking_approval_required' => isset($_POST['booking_approval_required']) ? '1' : '0',
        'max_booking_days_ahead' => (int)$_POST['max_booking_days_ahead'],
        'max_booking_duration_hours' => (int)$_POST['max_booking_duration_hours'],
        'notification_emails_enabled' => isset($_POST['notification_emails_enabled']) ? '1' : '0',
        'cancellation_policy' => sanitizeInput($_POST['cancellation_policy'] ?? $settings['cancellation_policy'])
    ];
    
    // Validate input
    $errors = [];
    if (empty($updatedSettings['site_name'])) {
        $errors[] = "Site name is required.";
    }
    
    if (!filter_var($updatedSettings['contact_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid contact email format.";
    }
    
    if ((int)$updatedSettings['max_booking_days_ahead'] < 1) {
        $errors[] = "Maximum booking days ahead must be at least 1.";
    }
    
    if ((int)$updatedSettings['max_booking_duration_hours'] < 1) {
        $errors[] = "Maximum booking duration must be at least 1 hour.";
    }
    
    // If no validation errors, update settings
    if (empty($errors)) {
        foreach ($updatedSettings as $key => $value) {
            // Check if setting exists
            $checkSql = "SELECT id FROM settings WHERE setting_key = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("s", $key);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                // Update existing setting
                $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->bind_param("ss", $value, $key);
            } else {
                // Insert new setting
                $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
                $stmt->bind_param("ss", $key, $value);
            }
            
            $stmt->execute();
        }
        
        setFlashMessage("Settings updated successfully.", "success");
        redirect('settings.php');
    } else {
        setFlashMessage($errors[0], "error");
    }
}

// Close database connection
closeDbConnection($conn);

$page_title = 'System Settings';
$active_page = 'admin_settings';
include '../includes/header.php';
?>

<div class="mb-6">
    <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
    </a>
</div>

<div class="mb-6">
    <h2 class="text-xl font-semibold text-gray-800">System Settings</h2>
    <p class="text-gray-600 mt-1">Configure general settings for the booking system.</p>
</div>

<div class="bg-white overflow-hidden shadow-md rounded-lg">
    <div class="p-6">
        <form method="POST">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b">General Settings</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="site_name" class="block text-sm font-medium text-gray-700">Site Name</label>
                        <input type="text" name="site_name" id="site_name" value="<?php echo $settings['site_name']; ?>" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-gray-700">Contact Email</label>
                        <input type="email" name="contact_email" id="contact_email" value="<?php echo $settings['contact_email']; ?>" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="site_description" class="block text-sm font-medium text-gray-700">Site Description</label>
                        <textarea name="site_description" id="site_description" rows="2" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"><?php echo $settings['site_description']; ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b">Booking Settings</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="flex items-center">
                            <input type="checkbox" name="booking_approval_required" id="booking_approval_required" <?php echo $settings['booking_approval_required'] == '1' ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="booking_approval_required" class="ml-2 block text-sm text-gray-700">Require admin approval for bookings</label>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">If checked, all bookings will require administrator approval before being confirmed.</p>
                    </div>
                    
                    <div>
                        <div class="flex items-center">
                            <input type="checkbox" name="notification_emails_enabled" id="notification_emails_enabled" <?php echo $settings['notification_emails_enabled'] == '1' ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="notification_emails_enabled" class="ml-2 block text-sm text-gray-700">Enable email notifications</label>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Send emails for booking confirmations, rejections, and other important updates.</p>
                    </div>
                    
                    <div>
                        <label for="max_booking_days_ahead" class="block text-sm font-medium text-gray-700">Maximum Days in Advance</label>
                        <input type="number" name="max_booking_days_ahead" id="max_booking_days_ahead" value="<?php echo $settings['max_booking_days_ahead']; ?>" min="1" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <p class="mt-1 text-sm text-gray-500">How many days in advance users can make bookings.</p>
                    </div>
                    
                    <div>
                        <label for="max_booking_duration_hours" class="block text-sm font-medium text-gray-700">Maximum Booking Duration (hours)</label>
                        <input type="number" name="max_booking_duration_hours" id="max_booking_duration_hours" value="<?php echo $settings['max_booking_duration_hours']; ?>" min="1" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <p class="mt-1 text-sm text-gray-500">Maximum duration for a single booking in hours.</p>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="cancellation_policy" class="block text-sm font-medium text-gray-700">Cancellation Policy</label>
                        <textarea name="cancellation_policy" id="cancellation_policy" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"><?php echo $settings['cancellation_policy']; ?></textarea>
                        <p class="mt-1 text-sm text-gray-500">Policy displayed to users regarding booking cancellations.</p>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" name="update_settings" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 