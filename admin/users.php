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

// Get users from database
$sql = "SELECT * FROM users ORDER BY id ASC";
$result = $conn->query($sql);
$users = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Handle add user form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = sanitizeInput($_POST['email'] ?? '');
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $role = sanitizeInput($_POST['role'] ?? 'user');
    
    // Validate input
    $errors = [];
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Username must be between 3 and 50 characters.";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    if (empty($full_name)) {
        $errors[] = "Full name is required.";
    }
    
    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $existingResult = $stmt->get_result();
    
    if ($existingResult->num_rows > 0) {
        $errors[] = "Username or email already exists.";
    }
    
    // If no validation errors, attempt to add user
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $hashed_password, $email, $full_name, $role);
        
        if ($stmt->execute()) {
            setFlashMessage("User added successfully.", "success");
            redirect('users.php');
        } else {
            setFlashMessage("Error adding user: " . $conn->error, "error");
        }
    } else {
        setFlashMessage($errors[0], "error");
    }
}

// Handle update user form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $id = (int)$_POST['id'];
    $email = sanitizeInput($_POST['email'] ?? '');
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $role = sanitizeInput($_POST['role'] ?? 'user');
    
    // Validate input
    $errors = [];
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    if (empty($full_name)) {
        $errors[] = "Full name is required.";
    }
    
    // Check if email already exists for other users
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $existingResult = $stmt->get_result();
    
    if ($existingResult->num_rows > 0) {
        $errors[] = "Email already exists for another user.";
    }
    
    // If no validation errors, attempt to update user
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET email = ?, full_name = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $email, $full_name, $role, $id);
        
        if ($stmt->execute()) {
            setFlashMessage("User updated successfully.", "success");
            redirect('users.php');
        } else {
            setFlashMessage("Error updating user: " . $conn->error, "error");
        }
    } else {
        setFlashMessage($errors[0], "error");
    }
}

// Handle reset password form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $id = (int)$_POST['id'];
    $password = $_POST['password'] ?? '';
    
    // Validate input
    $errors = [];
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    
    // If no validation errors, attempt to reset password
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $id);
        
        if ($stmt->execute()) {
            setFlashMessage("Password reset successfully.", "success");
            redirect('users.php');
        } else {
            setFlashMessage("Error resetting password: " . $conn->error, "error");
        }
    } else {
        setFlashMessage($errors[0], "error");
    }
}

// Handle delete user form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $id = (int)$_POST['id'];
    
    // Prevent admin from deleting themselves
    if ($id == $_SESSION['user_id']) {
        setFlashMessage("You cannot delete your own account.", "error");
        redirect('users.php');
    }
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        setFlashMessage("User deleted successfully.", "success");
    } else {
        setFlashMessage("Error deleting user: " . $conn->error, "error");
    }
    redirect('users.php');
}

$page_title = 'Manage Users';
$active_page = 'admin_users';
include '../includes/header.php';
?>

<div class="mb-6">
    <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
    </a>
</div>

<div class="flex flex-col md:flex-row gap-6">
    <!-- Add User Form -->
    <div class="w-full md:w-1/3">
        <div class="bg-white overflow-hidden shadow-md rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add New User</h3>
            
            <form method="POST">
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" name="username" id="username" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" name="full_name" id="full_name" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                    <select name="role" id="role" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="user">User</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                
                <div>
                    <button type="submit" name="add_user" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Users List -->
    <div class="w-full md:w-2/3">
        <div class="bg-white overflow-hidden shadow-md rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Users</h3>
            </div>
            
            <?php if (empty($users)): ?>
                <div class="p-6 text-center">
                    <p class="text-gray-500">No users found. Please add a new user.</p>
                </div>
            <?php else: ?>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Username
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Full Name
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Role
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">#<?php echo $user['id']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $user['username']; ?></div>
                                    <div class="text-xs text-gray-500"><?php echo $user['email']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $user['full_name']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        <?php echo $user['role'] == 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>
                                    ">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button type="button" class="text-blue-600 hover:text-blue-900 mr-3" 
                                            onclick="openEditModal(<?php echo $user['id']; ?>, '<?php echo addslashes($user['email']); ?>', '<?php echo addslashes($user['full_name']); ?>', '<?php echo $user['role']; ?>')">
                                        Edit
                                    </button>
                                    
                                    <button type="button" class="text-orange-600 hover:text-orange-900 mr-3" 
                                            onclick="openPasswordModal(<?php echo $user['id']; ?>, '<?php echo addslashes($user['username']); ?>')">
                                        Reset Password
                                    </button>
                                    
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user? This will also delete all bookings for this user.');">
                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" name="delete_user" class="text-red-600 hover:text-red-900">
                                                Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
        <form method="POST">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit User</h3>
                
                <div class="mb-4">
                    <label for="edit_email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="edit_email" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="edit_full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" name="full_name" id="edit_full_name" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="edit_role" class="block text-sm font-medium text-gray-700">Role</label>
                    <select name="role" id="edit_role" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="user">User</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="submit" name="update_user" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Save Changes
                </button>
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeEditModal()">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="passwordModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
        <form method="POST">
            <input type="hidden" name="id" id="password_id">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Reset Password</h3>
                <p class="text-sm text-gray-600 mb-4">Enter new password for user <span id="password_username" class="font-medium"></span></p>
                
                <div class="mb-4">
                    <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <input type="password" name="password" id="new_password" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="submit" name="reset_password" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Reset Password
                </button>
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closePasswordModal()">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, email, fullName, role) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_full_name').value = fullName;
        document.getElementById('edit_role').value = role;
        document.getElementById('editModal').classList.remove('hidden');
    }
    
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
    
    function openPasswordModal(id, username) {
        document.getElementById('password_id').value = id;
        document.getElementById('password_username').textContent = username;
        document.getElementById('passwordModal').classList.remove('hidden');
    }
    
    function closePasswordModal() {
        document.getElementById('passwordModal').classList.add('hidden');
    }
    
    // Close modals when clicking outside
    document.getElementById('editModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeEditModal();
        }
    });
    
    document.getElementById('passwordModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closePasswordModal();
        }
    });
</script>

<?php 
// Close database connection before including the footer to prevent multiple closings
closeDbConnection($conn);
// Make sure we don't accidentally close the connection again
$conn = null;

include '../includes/footer.php'; 
?> 