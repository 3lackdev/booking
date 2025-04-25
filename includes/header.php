<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/utilities.php';

// Set default page title if not set
if (!isset($page_title)) {
    $page_title = 'Booking System';
}

// Set active page if not set
if (!isset($active_page)) {
    $active_page = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="index.php" class="text-white text-xl font-bold">Booking System</a>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:space-x-4">
                        <a href="index.php" class="<?php echo $active_page == 'home' ? 'bg-blue-700 text-white' : 'text-white hover:bg-blue-500'; ?> px-3 py-2 rounded-md text-sm font-medium">Home</a>
                        
                        <?php if (isLoggedIn()): ?>
                            <a href="resources.php" class="<?php echo $active_page == 'resources' ? 'bg-blue-700 text-white' : 'text-white hover:bg-blue-500'; ?> px-3 py-2 rounded-md text-sm font-medium">Resources</a>
                            <a href="my_bookings.php" class="<?php echo $active_page == 'my_bookings' ? 'bg-blue-700 text-white' : 'text-white hover:bg-blue-500'; ?> px-3 py-2 rounded-md text-sm font-medium">My Bookings</a>
                            
                            <?php if (isAdmin()): ?>
                                <a href="admin/dashboard.php" class="<?php echo strpos($active_page, 'admin_') === 0 ? 'bg-blue-700 text-white' : 'text-white hover:bg-blue-500'; ?> px-3 py-2 rounded-md text-sm font-medium">Admin</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="hidden md:ml-6 md:flex md:items-center">
                    <?php if (isLoggedIn()): ?>
                        <div class="ml-3 relative group">
                            <div>
                                <button type="button" class="flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-600 focus:ring-white" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                    <span class="sr-only">Open user menu</span>
                                    <div class="h-8 w-8 rounded-full bg-blue-300 flex items-center justify-center text-white font-bold">
                                        <?php echo substr($_SESSION['full_name'], 0, 1); ?>
                                    </div>
                                </button>
                            </div>
                            <div class="hidden group-hover:block absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                                <div class="block px-4 py-2 text-sm text-gray-700 border-b">
                                    <div class="font-bold"><?php echo $_SESSION['full_name']; ?></div>
                                    <div class="text-gray-500"><?php echo $_SESSION['username']; ?></div>
                                </div>
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">Profile</a>
                                <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">Sign out</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="text-white hover:bg-blue-500 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                        <a href="register.php" class="bg-white text-blue-600 hover:bg-blue-50 ml-2 px-3 py-2 rounded-md text-sm font-medium">Register</a>
                    <?php endif; ?>
                </div>
                <div class="flex items-center md:hidden">
                    <button type="button" class="bg-blue-600 inline-flex items-center justify-center p-2 rounded-md text-white hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-600 focus:ring-white" id="mobile-menu-button">
                        <span class="sr-only">Open main menu</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu, show/hide based on menu state -->
        <div class="hidden md:hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="index.php" class="<?php echo $active_page == 'home' ? 'bg-blue-700 text-white' : 'text-white hover:bg-blue-500'; ?> block px-3 py-2 rounded-md text-base font-medium">Home</a>
                
                <?php if (isLoggedIn()): ?>
                    <a href="resources.php" class="<?php echo $active_page == 'resources' ? 'bg-blue-700 text-white' : 'text-white hover:bg-blue-500'; ?> block px-3 py-2 rounded-md text-base font-medium">Resources</a>
                    <a href="my_bookings.php" class="<?php echo $active_page == 'my_bookings' ? 'bg-blue-700 text-white' : 'text-white hover:bg-blue-500'; ?> block px-3 py-2 rounded-md text-base font-medium">My Bookings</a>
                    
                    <?php if (isAdmin()): ?>
                        <a href="admin/dashboard.php" class="<?php echo strpos($active_page, 'admin_') === 0 ? 'bg-blue-700 text-white' : 'text-white hover:bg-blue-500'; ?> block px-3 py-2 rounded-md text-base font-medium">Admin</a>
                    <?php endif; ?>
                    
                    <div class="border-t border-blue-500 pt-2 mt-2">
                        <a href="profile.php" class="text-white hover:bg-blue-500 block px-3 py-2 rounded-md text-base font-medium">Profile</a>
                        <a href="logout.php" class="text-white hover:bg-blue-500 block px-3 py-2 rounded-md text-base font-medium">Sign out</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="text-white hover:bg-blue-500 block px-3 py-2 rounded-md text-base font-medium">Login</a>
                    <a href="register.php" class="text-white hover:bg-blue-500 block px-3 py-2 rounded-md text-base font-medium">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <?php displayFlashMessage(); ?>
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $page_title; ?></h1> 