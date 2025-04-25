<?php
require_once '../includes/auth.php';
require_once '../includes/utilities.php';
require_once '../classes/ResourceManager.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage("You must be an administrator to access this page.", "error");
    redirect('../index.php');
}

// Initialize resource manager
$resourceManager = new ResourceManager();

// Get category filter from URL parameter
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

// Get all resource categories for filter dropdown
$categories = $resourceManager->getCategories();

// Get resources based on filter
$resources = $resourceManager->getResources($category_id, false);

// Handle add resource form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_resource'])) {
    $category_id = (int)$_POST['category_id'];
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $location = sanitizeInput($_POST['location'] ?? '');
    $capacity = !empty($_POST['capacity']) ? (int)$_POST['capacity'] : null;
    
    // Validate input
    $errors = [];
    if (empty($name)) {
        $errors[] = "Resource name is required.";
    }
    
    if (empty($category_id)) {
        $errors[] = "Category is required.";
    }
    
    // If no validation errors, attempt to add resource
    if (empty($errors)) {
        $result = $resourceManager->addResource($category_id, $name, $description, $location, $capacity);
        
        if ($result === true) {
            setFlashMessage("Resource added successfully.", "success");
            redirect('resources.php');
        } else {
            setFlashMessage($result, "error");
        }
    } else {
        setFlashMessage($errors[0], "error");
    }
}

// Handle update resource form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_resource'])) {
    $id = (int)$_POST['id'];
    $category_id = (int)$_POST['category_id'];
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $location = sanitizeInput($_POST['location'] ?? '');
    $capacity = !empty($_POST['capacity']) ? (int)$_POST['capacity'] : null;
    $status = sanitizeInput($_POST['status'] ?? 'available');
    
    // Validate input
    $errors = [];
    if (empty($name)) {
        $errors[] = "Resource name is required.";
    }
    
    if (empty($category_id)) {
        $errors[] = "Category is required.";
    }
    
    // If no validation errors, attempt to update resource
    if (empty($errors)) {
        $result = $resourceManager->updateResource($id, $category_id, $name, $description, $location, $capacity, $status);
        
        if ($result === true) {
            setFlashMessage("Resource updated successfully.", "success");
            redirect('resources.php');
        } else {
            setFlashMessage($result, "error");
        }
    } else {
        setFlashMessage($errors[0], "error");
    }
}

// Handle delete resource form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_resource'])) {
    $id = (int)$_POST['id'];
    
    $result = $resourceManager->deleteResource($id);
    
    if ($result === true) {
        setFlashMessage("Resource deleted successfully.", "success");
    } else {
        setFlashMessage($result, "error");
    }
    redirect('resources.php');
}

$page_title = 'Manage Resources';
$active_page = 'admin_resources';
include '../includes/header.php';
?>

<div class="mb-6">
    <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
    </a>
</div>

<div class="flex flex-col md:flex-row gap-6">
    <!-- Add Resource Form -->
    <div class="w-full md:w-1/3">
        <div class="bg-white overflow-hidden shadow-md rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Resource</h3>
            
            <form method="POST">
                <div class="mb-4">
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category_id" id="category_id" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Resource Name</label>
                    <input type="text" name="name" id="name" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                    <input type="text" name="location" id="location" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity (if applicable)</label>
                    <input type="number" name="capacity" id="capacity" min="1" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div>
                    <button type="submit" name="add_resource" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Add Resource
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Resources List -->
    <div class="w-full md:w-2/3">
        <div class="bg-white overflow-hidden shadow-md rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Resources</h3>
                
                <div>
                    <select id="category-filter" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" onchange="window.location.href = this.value">
                        <option value="resources.php">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="resources.php?category_id=<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo $category['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <?php if (empty($resources)): ?>
                <div class="p-6 text-center">
                    <p class="text-gray-500">No resources found. Please add a new resource.</p>
                </div>
            <?php else: ?>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Location
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($resources as $resource): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $resource['name']; ?></div>
                                    <?php if ($resource['capacity']): ?>
                                        <div class="text-xs text-gray-500">Capacity: <?php echo $resource['capacity']; ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $resource['category_name']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $resource['location']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                        }
                                        ?>
                                    ">
                                        <?php echo ucfirst($resource['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button type="button" class="text-blue-600 hover:text-blue-900 mr-3" 
                                            onclick="openEditModal(<?php echo $resource['id']; ?>, <?php echo $resource['category_id']; ?>, '<?php echo addslashes($resource['name']); ?>', '<?php echo addslashes($resource['description']); ?>', '<?php echo addslashes($resource['location']); ?>', <?php echo $resource['capacity'] ? $resource['capacity'] : 'null'; ?>, '<?php echo $resource['status']; ?>')">
                                        Edit
                                    </button>
                                    
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this resource? This will also delete all bookings for this resource.');">
                                        <input type="hidden" name="id" value="<?php echo $resource['id']; ?>">
                                        <button type="submit" name="delete_resource" class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Resource Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
        <form method="POST">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Resource</h3>
                
                <div class="mb-4">
                    <label for="edit_category_id" class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category_id" id="edit_category_id" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="edit_name" class="block text-sm font-medium text-gray-700">Resource Name</label>
                    <input type="text" name="name" id="edit_name" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="edit_description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="edit_description" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="edit_location" class="block text-sm font-medium text-gray-700">Location</label>
                    <input type="text" name="location" id="edit_location" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="edit_capacity" class="block text-sm font-medium text-gray-700">Capacity (if applicable)</label>
                    <input type="number" name="capacity" id="edit_capacity" min="1" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="edit_status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="edit_status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="available">Available</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="submit" name="update_resource" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Save Changes
                </button>
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeEditModal()">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, categoryId, name, description, location, capacity, status) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_category_id').value = categoryId;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_location').value = location;
        document.getElementById('edit_capacity').value = capacity !== null ? capacity : '';
        document.getElementById('edit_status').value = status;
        document.getElementById('editModal').classList.remove('hidden');
    }
    
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
    
    // Close modal when clicking outside
    document.getElementById('editModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeEditModal();
        }
    });
</script>

<?php include '../includes/footer.php'; ?> 