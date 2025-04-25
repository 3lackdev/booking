<?php
// Define variables with default values
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'booking_system';
$success_message = '';
$error_message = '';
$installation_complete = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_user = $_POST['db_user'] ?? 'root';
    $db_pass = $_POST['db_pass'] ?? '';
    $db_name = $_POST['db_name'] ?? 'booking_system';
    $admin_email = $_POST['admin_email'] ?? 'admin@example.com';
    $site_name = $_POST['site_name'] ?? 'Booking System';
    
    try {
        // Connect to MySQL server without selecting database
        $conn = new mysqli($db_host, $db_user, $db_pass);

        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Create database if it doesn't exist
        $sql = "CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
        if (!$conn->query($sql)) {
            throw new Exception("Error creating database: " . $conn->error);
        }

        // Select the database
        $conn->select_db($db_name);

        // Read SQL file
        $schema_file = file_get_contents('database/schema.sql');
        if (!$schema_file) {
            throw new Exception("Could not read schema.sql file");
        }

        // Modify SQL to skip database creation since we already did that
        $schema_file = preg_replace('/CREATE DATABASE.*?;/s', '', $schema_file);
        $schema_file = preg_replace('/USE.*?;/s', '', $schema_file);

        // Update admin email in SQL if provided
        if (!empty($admin_email) && $admin_email !== 'admin@example.com') {
            $schema_file = preg_replace(
                "/'admin@example.com'/", 
                "'".addslashes($admin_email)."'", 
                $schema_file
            );
        }

        // Update site name in SQL if provided
        if (!empty($site_name) && $site_name !== 'Booking System') {
            $schema_file = preg_replace(
                "/'site_name', 'Booking System'/", 
                "'site_name', '".addslashes($site_name)."'", 
                $schema_file
            );
        }

        // Split SQL by semicolons to run multiple queries
        $queries = preg_split('/;\s*$/m', $schema_file);
        
        // Execute each query
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                if (!$conn->query($query)) {
                    throw new Exception("Error executing query: " . $conn->error . "<br>Query: " . $query);
                }
            }
        }

        // Update config file
        $config_file = 'config/database.php';
        $config_content = file_get_contents($config_file);
        
        if ($config_content) {
            $config_content = preg_replace(
                "/define\('DB_HOST', '.*?'\);/", 
                "define('DB_HOST', '$db_host');", 
                $config_content
            );
            $config_content = preg_replace(
                "/define\('DB_USER', '.*?'\);/", 
                "define('DB_USER', '$db_user');", 
                $config_content
            );
            $config_content = preg_replace(
                "/define\('DB_PASS', '.*?'\);/", 
                "define('DB_PASS', '$db_pass');", 
                $config_content
            );
            $config_content = preg_replace(
                "/define\('DB_NAME', '.*?'\);/", 
                "define('DB_NAME', '$db_name');", 
                $config_content
            );
            
            if (!file_put_contents($config_file, $config_content)) {
                throw new Exception("Could not update config file");
            }
        } else {
            throw new Exception("Could not read config file");
        }

        // Close the connection
        $conn->close();
        
        // Set success message
        $success_message = "Installation completed successfully!";
        $installation_complete = true;
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking System Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #0ea5e9, #6d28d9);
        }
        
        .btn-gradient-primary {
            background: linear-gradient(135deg, #0ea5e9, #6d28d9);
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-gradient-primary:hover {
            background: linear-gradient(135deg, #0284c7, #5b21b6);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Booking System Setup</h1>
                <p class="mt-2 text-lg text-gray-600">Initialize your database and configure your system</p>
            </div>
            
            <?php if (!empty($error_message)): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                <?php echo $error_message; ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                <?php echo $success_message; ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($installation_complete): ?>
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Installation Complete</h2>
                    <p class="mb-4 text-gray-600">Your booking system has been successfully installed. You can now proceed to use the system.</p>
                    
                    <div class="bg-gray-50 p-4 rounded-md mb-4">
                        <h3 class="text-md font-medium text-gray-900 mb-2">Default Admin Login</h3>
                        <p class="text-sm text-gray-700"><strong>Username:</strong> admin</p>
                        <p class="text-sm text-gray-700"><strong>Password:</strong> admin123</p>
                        <p class="mt-2 text-xs text-red-600"><i class="fas fa-exclamation-triangle mr-1"></i> Important: Change the admin password after first login for security reasons.</p>
                    </div>
                    
                    <div class="flex space-x-4">
                        <a href="index.php" class="btn-gradient-primary inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm focus:outline-none">
                            <i class="fas fa-home mr-2"></i> Go to Homepage
                        </a>
                        <a href="login.php" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                            <i class="fas fa-sign-in-alt mr-2"></i> Go to Login
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <form action="setup.php" method="post" class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Database Configuration</h2>
                    
                    <div class="grid grid-cols-1 gap-6 mb-6">
                        <div>
                            <label for="db_host" class="block text-sm font-medium text-gray-700">Database Host</label>
                            <input type="text" name="db_host" id="db_host" value="<?php echo htmlspecialchars($db_host); ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            <p class="mt-1 text-xs text-gray-500">Usually 'localhost' or an IP address</p>
                        </div>
                        
                        <div>
                            <label for="db_name" class="block text-sm font-medium text-gray-700">Database Name</label>
                            <input type="text" name="db_name" id="db_name" value="<?php echo htmlspecialchars($db_name); ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" readonly>
                            <p class="mt-1 text-xs text-gray-500">The database will be created if it doesn't exist</p>
                        </div>
                        
                        <div>
                            <label for="db_user" class="block text-sm font-medium text-gray-700">Database Username</label>
                            <input type="text" name="db_user" id="db_user" value="<?php echo htmlspecialchars($db_user); ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label for="db_pass" class="block text-sm font-medium text-gray-700">Database Password</label>
                            <input type="password" name="db_pass" id="db_pass" value="<?php echo htmlspecialchars($db_pass); ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Site Configuration</h2>
                    
                    <div class="grid grid-cols-1 gap-6 mb-6">
                        <div>
                            <label for="site_name" class="block text-sm font-medium text-gray-700">Site Name</label>
                            <input type="text" name="site_name" id="site_name" value="Booking System" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label for="admin_email" class="block text-sm font-medium text-gray-700">Admin Email</label>
                            <input type="email" name="admin_email" id="admin_email" value="admin@example.com" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-md mb-6">
                        <h3 class="text-md font-medium text-gray-900 mb-2">Pre-installation Checklist</h3>
                        <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                            <li>Make sure PHP 7.4 or higher is installed</li>
                            <li>MySQL 5.7 or higher is required</li>
                            <li>The web server user needs permission to write to the config directory</li>
                            <li>Enable mysqli extension in PHP</li>
                        </ul>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="btn-gradient-primary inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm focus:outline-none">
                            <i class="fas fa-database mr-2"></i> Install Database
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 