<?php
require_once 'includes/auth.php';
require_once 'includes/utilities.php';
require_once 'classes/BookingManager.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage("Please log in to view your profile.", "error");
    redirect('login.php');
}

// Get current user details
$conn = getDbConnection();
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Initialize BookingManager
$bookingManager = new BookingManager();

// Get user's booking history
$bookings = $bookingManager->getBookings($user_id);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    
    // Validate input
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = "Full name is required.";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif ($email != $user['email']) {
        // Check if email is already in use by another user
        $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $email, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Email is already in use by another account.";
        }
        
        $check_stmt->close();
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        $update_sql = "UPDATE users SET full_name = ?, email = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $full_name, $email, $user_id);
        
        if ($update_stmt->execute()) {
            // Update session data
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;
            
            setFlashMessage("Profile updated successfully.", "success");
            redirect('profile.php');
        } else {
            setFlashMessage("Error updating profile: " . $conn->error, "error");
        }
        
        $update_stmt->close();
    } else {
        setFlashMessage($errors[0], "error");
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    $errors = [];
    
    if (empty($current_password)) {
        $errors[] = "Current password is required.";
    } elseif (!password_verify($current_password, $user['password'])) {
        $errors[] = "Current password is incorrect.";
    }
    
    if (empty($new_password)) {
        $errors[] = "New password is required.";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "New password must be at least 8 characters long.";
    }
    
    if ($new_password != $confirm_password) {
        $errors[] = "New passwords do not match.";
    }
    
    // If no errors, change password
    if (empty($errors)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $update_sql = "UPDATE users SET password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($update_stmt->execute()) {
            setFlashMessage("Password changed successfully.", "success");
            redirect('profile.php');
        } else {
            setFlashMessage("Error changing password: " . $conn->error, "error");
        }
        
        $update_stmt->close();
    } else {
        setFlashMessage($errors[0], "error");
    }
}

closeDbConnection($conn);

$page_title = 'My Profile';
$active_page = 'profile';
include 'includes/header.php';
?>

<div class="mb-6">
    <h2 class="text-xl font-semibold text-gray-800">My Profile</h2>
    <p class="text-gray-600 mt-1">View and update your account information.</p>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <!-- Account information -->
    <div class="lg:col-span-2">
        <div class="bg-white overflow-hidden shadow-md rounded-lg gradient-border card-hover">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Account Information</h3>
                
                <form method="POST" action="">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                            <input type="text" id="username" value="<?php echo $user['username']; ?>" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-50" readonly>
                            <p class="mt-1 text-sm text-gray-500">Username cannot be changed.</p>
                        </div>
                        
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" name="full_name" id="full_name" value="<?php echo $user['full_name']; ?>" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                        </div>
                        
                        <div class="sm:col-span-2">
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" name="email" id="email" value="<?php echo $user['email']; ?>" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                        </div>
                        
                        <div class="sm:col-span-2">
                            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                            <input type="text" id="role" value="<?php echo ucfirst($user['role']); ?>" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-50" readonly>
                        </div>
                    </div>
                    
                    <div class="flex justify-end mt-6">
                        <button type="submit" name="update_profile" class="btn-gradient-primary inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow-md rounded-lg mt-6 gradient-border card-hover">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
                
                <form method="POST" action="">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                            <input type="password" name="current_password" id="current_password" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                        </div>
                        
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                            <input type="password" name="new_password" id="new_password" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required minlength="8">
                            <p class="mt-1 text-sm text-gray-500">Password must be at least 8 characters long.</p>
                        </div>
                        
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                        </div>
                    </div>
                    
                    <div class="flex justify-end mt-6">
                        <button type="submit" name="change_password" class="btn-gradient-primary inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <i class="fas fa-key mr-2"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Account summary -->
    <div>
        <div class="bg-white overflow-hidden shadow-md rounded-lg card-hover glass-effect">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Account Summary</h3>
                
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-gradient-secondary flex items-center justify-center text-white font-bold">
                        <i class="fas fa-user text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900"><?php echo $user['full_name']; ?></p>
                        <p class="text-sm text-gray-500"><?php echo $user['username']; ?></p>
                    </div>
                </div>
                
                <dl class="mt-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="text-sm text-gray-900"><?php echo $user['email']; ?></dd>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <dt class="text-sm font-medium text-gray-500">Role</dt>
                        <dd class="text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gradient-primary text-white">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </dd>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <dt class="text-sm font-medium text-gray-500">Joined</dt>
                        <dd class="text-sm text-gray-900"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></dd>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <dt class="text-sm font-medium text-gray-500">Total Bookings</dt>
                        <dd class="text-sm text-primary-600 font-semibold"><?php echo count($bookings); ?></dd>
                    </div>
                </dl>
                
                <div class="mt-6">
                    <a href="my_bookings.php" class="inline-flex items-center px-4 py-2 border border-primary-300 shadow-sm text-sm font-medium rounded-md text-primary-700 bg-white hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-calendar-alt mr-2 text-primary-500"></i> View My Bookings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent bookings -->
<div class="mt-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Bookings</h3>
    
    <?php if (empty($bookings)): ?>
        <div class="bg-white overflow-hidden shadow-md rounded-lg card-hover">
            <div class="p-6">
                <p class="text-gray-500">You haven't made any bookings yet.</p>
                
                <div class="mt-4">
                    <a href="resources.php" class="btn-gradient-primary inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-search mr-2"></i> Browse Resources
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white overflow-hidden shadow-md rounded-lg card-hover">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-primary text-white">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Resource</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Date & Time</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Title</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php 
                        // Display only the 5 most recent bookings
                        $recent_bookings = array_slice($bookings, 0, 5);
                        foreach ($recent_bookings as $booking): 
                        ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $booking['resource_name']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $booking['category_name']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo date('Y-m-d', strtotime($booking['start_time'])); ?></div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo date('H:i', strtotime($booking['start_time'])); ?> - <?php echo date('H:i', strtotime($booking['end_time'])); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $booking['title']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php
                                        switch ($booking['status']) {
                                            case 'pending':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'confirmed':
                                                echo 'bg-gradient-success text-white';
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="view_booking.php?id=<?php echo $booking['id']; ?>" class="text-primary-600 hover:text-primary-900 inline-flex items-center">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (count($bookings) > 5): ?>
                <div class="p-4 border-t border-gray-200 bg-gray-50">
                    <a href="my_bookings.php" class="text-primary-600 hover:text-primary-900 inline-flex items-center font-medium">
                        View all <?php echo count($bookings); ?> bookings <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?> 