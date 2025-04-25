<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/utilities.php';

// Get site name from settings if available
$conn = getDbConnection();
$site_name = 'Booking System'; // Default value

// Check if settings table exists and get site name
try {
    $result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'site_name'");
    if ($result && $result->num_rows > 0) {
        $site_name = $result->fetch_assoc()['setting_value'];
    }
} catch (Exception $e) {
    // Table might not exist yet, use default
}
closeDbConnection($conn);

// Set default page title if not set
if (!isset($page_title)) {
    $page_title = $site_name;
}

// Set active page if not set
if (!isset($active_page)) {
    $active_page = '';
}

// Determine if we're in admin section
$is_admin_section = strpos($active_page, 'admin_') === 0;

// Define base paths for links
$base_path = $is_admin_section ? '../' : '';
$admin_path = $is_admin_section ? '' : 'admin/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | <?php echo $site_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        secondary: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        },
                    },
                },
            },
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #0ea5e9, #6d28d9);
        }
        
        .bg-gradient-secondary {
            background: linear-gradient(135deg, #7c3aed, #0ea5e9);
        }
        
        .bg-gradient-success {
            background: linear-gradient(135deg, #10b981, #3b82f6);
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
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .gradient-border {
            position: relative;
        }
        
        .gradient-border::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(to right, #0ea5e9, #6d28d9);
            border-radius: 3px 3px 0 0;
        }
        
        /* Sticky footer styles */
        html {
            height: 100%;
        }
        
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        main {
            flex: 1 0 auto;
            display: block;
            width: 100%;
        }
        
        main > .max-w-7xl {
            width: 100%;
        }
        
        footer {
            flex-shrink: 0;
            margin-top: auto;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-gradient-primary shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="<?php echo $base_path; ?>index.php" class="text-white text-xl font-bold">
                            <?php echo $site_name; ?>
                        </a>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:space-x-4 items-center">
                        <a href="<?php echo $base_path; ?>index.php" 
                           class="<?php echo $active_page == 'home' ? 'bg-white/20 text-white' : 'text-white hover:bg-white/10'; ?> px-3 py-2 rounded-md text-sm font-medium">
                            Home
                        </a>
                        
                        <?php if (isLoggedIn()): ?>
                            <a href="<?php echo $base_path; ?>resources.php" 
                               class="<?php echo $active_page == 'resources' ? 'bg-white/20 text-white' : 'text-white hover:bg-white/10'; ?> px-3 py-2 rounded-md text-sm font-medium">
                                Resources
                            </a>
                            <a href="<?php echo $base_path; ?>my_bookings.php" 
                               class="<?php echo $active_page == 'my_bookings' ? 'bg-white/20 text-white' : 'text-white hover:bg-white/10'; ?> px-3 py-2 rounded-md text-sm font-medium">
                                My Bookings
                            </a>
                            
                            <?php if (isAdmin()): ?>
                                <a href="<?php echo $admin_path; ?>dashboard.php" 
                                   class="<?php echo $is_admin_section ? 'bg-white/20 text-white' : 'text-white hover:bg-white/10'; ?> px-3 py-2 rounded-md text-sm font-medium">
                                    Admin
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="hidden md:flex md:items-center">
                    <?php if (isLoggedIn()): ?>
                        <div class="ml-3 relative">
                            <div>
                                <button type="button" 
                                        class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-600 focus:ring-white" 
                                        id="user-menu-button" 
                                        aria-expanded="false" 
                                        aria-haspopup="true">
                                    <span class="sr-only">Open user menu</span>
                                    <div class="h-8 w-8 rounded-full bg-gradient-secondary flex items-center justify-center text-white font-bold">
                                        <?php echo substr($_SESSION['full_name'], 0, 1); ?>
                                    </div>
                                    <span class="ml-2 text-white"><?php echo $_SESSION['username']; ?></span>
                                    <svg class="ml-1 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            <div class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10 glass-effect" 
                                 id="user-menu-dropdown" 
                                 role="menu" 
                                 aria-orientation="vertical" 
                                 aria-labelledby="user-menu-button" 
                                 tabindex="-1">
                                <div class="block px-4 py-2 text-sm text-gray-700 border-b">
                                    <div class="font-bold"><?php echo $_SESSION['full_name']; ?></div>
                                    <div class="text-gray-500"><?php echo $_SESSION['username']; ?></div>
                                </div>
                                <a href="<?php echo $base_path; ?>profile.php" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" 
                                   role="menuitem" 
                                   tabindex="-1">
                                    <i class="fas fa-user-circle mr-2 text-primary-500"></i> Profile
                                </a>
                                <a href="<?php echo $base_path; ?>logout.php" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" 
                                   role="menuitem" 
                                   tabindex="-1">
                                    <i class="fas fa-sign-out-alt mr-2 text-primary-500"></i> Sign out
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="text-white hover:text-white/80 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                        </a>
                        <a href="register.php" class="bg-white text-primary-600 hover:bg-white/90 ml-2 px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                            <i class="fas fa-user-plus mr-1"></i> Register
                        </a>
                    <?php endif; ?>
                </div>
                <div class="flex items-center md:hidden">
                    <button type="button" 
                            class="inline-flex items-center justify-center p-2 rounded-md text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-600 focus:ring-white" 
                            id="mobile-menu-button">
                        <span class="sr-only">Open main menu</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div class="hidden md:hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="<?php echo $base_path; ?>index.php" 
                   class="<?php echo $active_page == 'home' ? 'bg-white/20 text-white' : 'text-white hover:bg-white/10'; ?> block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-home mr-2"></i> Home
                </a>
                
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo $base_path; ?>resources.php" 
                       class="<?php echo $active_page == 'resources' ? 'bg-white/20 text-white' : 'text-white hover:bg-white/10'; ?> block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-list mr-2"></i> Resources
                    </a>
                    <a href="<?php echo $base_path; ?>my_bookings.php" 
                       class="<?php echo $active_page == 'my_bookings' ? 'bg-white/20 text-white' : 'text-white hover:bg-white/10'; ?> block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-calendar-check mr-2"></i> My Bookings
                    </a>
                    
                    <?php if (isAdmin()): ?>
                        <a href="<?php echo $admin_path; ?>dashboard.php" 
                           class="<?php echo $is_admin_section ? 'bg-white/20 text-white' : 'text-white hover:bg-white/10'; ?> block px-3 py-2 rounded-md text-base font-medium">
                            <i class="fas fa-tachometer-alt mr-2"></i> Admin
                        </a>
                    <?php endif; ?>
                    
                    <div class="border-t border-white/20 pt-2 mt-2">
                        <a href="<?php echo $base_path; ?>profile.php" 
                           class="text-white hover:bg-white/10 block px-3 py-2 rounded-md text-base font-medium">
                            <i class="fas fa-user-circle mr-2"></i> Profile
                        </a>
                        <a href="<?php echo $base_path; ?>logout.php" 
                           class="text-white hover:bg-white/10 block px-3 py-2 rounded-md text-base font-medium">
                            <i class="fas fa-sign-out-alt mr-2"></i> Sign out
                        </a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="text-white hover:bg-white/10 block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login
                    </a>
                    <a href="register.php" class="text-white hover:bg-white/10 block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-user-plus mr-2"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <?php displayFlashMessage(); ?>
            
            <div class="bg-white overflow-hidden shadow-md sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $page_title; ?></h1> 