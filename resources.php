<?php
require_once 'includes/auth.php';
require_once 'includes/utilities.php';
require_once 'classes/ResourceManager.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage("You must be logged in to access resources.", "error");
    redirect('login.php');
}

// Get category filter from URL parameter
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

// Initialize resource manager
$resourceManager = new ResourceManager();

// Get all resource categories for filter dropdown
$categories = $resourceManager->getCategories();

// Get resources based on filter
$resources = $resourceManager->getResources($category_id);

// Get current category name if filtering
$categoryName = '';
if ($category_id && isset($categories)) {
    foreach ($categories as $category) {
        if ($category['id'] == $category_id) {
            $categoryName = $category['name'];
            break;
        }
    }
}

$page_title = $categoryName ? 'Resources: ' . $categoryName : 'All Resources';
$active_page = 'resources';
include 'includes/header.php';
?>

<div class="flex flex-col md:flex-row gap-6">
    <!-- Sidebar filters -->
    <div class="w-full md:w-1/4">
        <div class="bg-white shadow-md rounded-lg p-4 mb-4">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Categories</h3>
            <ul class="space-y-2">
                <li>
                    <a href="resources.php" class="<?php echo !$category_id ? 'text-blue-600 font-bold' : 'text-gray-700 hover:text-blue-600'; ?> block">
                        All Resources
                    </a>
                </li>
                <?php foreach ($categories as $category): ?>
                    <li>
                        <a href="resources.php?category_id=<?php echo $category['id']; ?>" class="<?php echo $category_id == $category['id'] ? 'text-blue-600 font-bold' : 'text-gray-700 hover:text-blue-600'; ?> block">
                            <?php echo $category['name']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    
    <!-- Main content area -->
    <div class="w-full md:w-3/4">
        <?php if (empty($resources)): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                <p class="font-bold">No resources found</p>
                <p>There are no resources available in this category.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <?php foreach ($resources as $resource): ?>
                    <div class="bg-white overflow-hidden shadow-md rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-start">
                                <h3 class="text-lg font-medium text-gray-900"><?php echo $resource['name']; ?></h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    <?php
                                    switch ($resource['status']) {
                                        case 'available':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'maintenance':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'inactive':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>
                                ">
                                    <?php echo ucfirst($resource['status']); ?>
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1"><?php echo $resource['category_name']; ?></p>
                            <p class="mt-3 text-gray-600"><?php echo $resource['description']; ?></p>
                            
                            <div class="mt-4 text-sm text-gray-500">
                                <?php if ($resource['location']): ?>
                                    <p>
                                        <span class="font-medium">Location:</span> 
                                        <?php echo $resource['location']; ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($resource['capacity']): ?>
                                    <p>
                                        <span class="font-medium">Capacity:</span> 
                                        <?php echo $resource['capacity']; ?> people
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mt-6">
                                <?php if ($resource['status'] == 'available'): ?>
                                    <a href="book_resource.php?resource_id=<?php echo $resource['id']; ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Book Now
                                    </a>
                                <?php else: ?>
                                    <button disabled class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-400 cursor-not-allowed">
                                        Not Available
                                    </button>
                                <?php endif; ?>
                                
                                <a href="view_resource.php?id=<?php echo $resource['id']; ?>" class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 