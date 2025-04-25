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
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
        $upload_result = handleResourceImageUpload($_FILES['image']);
        if ($upload_result && !isset($upload_result['error'])) {
            $image_path = $upload_result['path'];
        } elseif (isset($upload_result['error'])) {
            setFlashMessage($upload_result['error'], "error");
            redirect('resources.php');
        }
    }
    
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
        $result = $resourceManager->addResource($category_id, $name, $description, $location, $capacity, $image_path);
        
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
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
        $upload_result = handleResourceImageUpload($_FILES['image']);
        if ($upload_result && !isset($upload_result['error'])) {
            $image_path = $upload_result['path'];
            
            // If there's an existing image, get the current resource to find and delete old image
            $current_resource = $resourceManager->getResourceById($id);
            if ($current_resource && !empty($current_resource['image_path'])) {
                $old_image_path = __DIR__ . '/../' . $current_resource['image_path'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
        } elseif (isset($upload_result['error'])) {
            setFlashMessage($upload_result['error'], "error");
            redirect('resources.php');
        }
    }
    
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
        $result = $resourceManager->updateResource($id, $category_id, $name, $description, $location, $capacity, $status, $image_path);
        
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
            
            <form method="POST" enctype="multipart/form-data">
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
                
                <div class="mb-4">
                    <label for="image" class="block text-sm font-medium text-gray-700">Resource Image</label>
                    <input type="file" name="image" id="image" accept="image/*" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    <p class="mt-1 text-xs text-gray-500">Supported formats: JPG, PNG, GIF, WEBP. Max size: 5MB.</p>
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
                                Resource
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
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <?php if (!empty($resource['image_path'])): ?>
                                                <img class="h-10 w-10 rounded-full object-cover" src="<?php echo '../' . $resource['image_path']; ?>" alt="<?php echo $resource['name']; ?>">
                                            <?php else: ?>
                                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                    <i class="fas fa-image text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo $resource['name']; ?></div>
                                            <?php if ($resource['capacity']): ?>
                                                <div class="text-xs text-gray-500">Capacity: <?php echo $resource['capacity']; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
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
                                            onclick="openEditModal(<?php echo $resource['id']; ?>, <?php echo $resource['category_id']; ?>, '<?php echo addslashes($resource['name']); ?>', '<?php echo addslashes($resource['description']); ?>', '<?php echo addslashes($resource['location']); ?>', <?php echo $resource['capacity'] ? $resource['capacity'] : 'null'; ?>, '<?php echo $resource['status']; ?>', '<?php echo addslashes($resource['image_path'] ?? ''); ?>')">
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
<div id="editResourceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Edit Resource</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit-id">
                
                <div class="mb-4">
                    <label for="edit-category_id" class="block text-sm font-medium text-gray-700 text-left">Category</label>
                    <select name="category_id" id="edit-category_id" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="edit-name" class="block text-sm font-medium text-gray-700 text-left">Resource Name</label>
                    <input type="text" name="name" id="edit-name" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="edit-description" class="block text-sm font-medium text-gray-700 text-left">Description</label>
                    <textarea name="description" id="edit-description" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="edit-location" class="block text-sm font-medium text-gray-700 text-left">Location</label>
                    <input type="text" name="location" id="edit-location" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="edit-capacity" class="block text-sm font-medium text-gray-700 text-left">Capacity (if applicable)</label>
                    <input type="number" name="capacity" id="edit-capacity" min="1" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="edit-status" class="block text-sm font-medium text-gray-700 text-left">Status</label>
                    <select name="status" id="edit-status" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="available">Available</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="edit-image" class="block text-sm font-medium text-gray-700 text-left">Resource Image</label>
                    <div id="current-image-container" class="my-2 flex justify-center">
                        <!-- Current image will be shown here by JavaScript -->
                    </div>
                    <input type="file" name="image" id="edit-image" accept="image/*" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    <p class="mt-1 text-xs text-gray-500 text-left">Upload a new image to replace the current one. Leave empty to keep current image.</p>
                </div>
                
                <div class="flex items-center justify-between mt-6">
                    <button type="button" onclick="closeEditModal()" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </button>
                    <button type="submit" name="update_resource" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(id, category_id, name, description, location, capacity, status, image_path) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-category_id').value = category_id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-description').value = description;
    document.getElementById('edit-location').value = location;
    document.getElementById('edit-capacity').value = capacity ? capacity : '';
    document.getElementById('edit-status').value = status;
    
    // Display current image if it exists
    const currentImageContainer = document.getElementById('current-image-container');
    currentImageContainer.innerHTML = '';
    
    if (image_path && image_path !== 'null' && image_path !== '') {
        const img = document.createElement('img');
        img.src = '../' + image_path;
        img.alt = name;
        img.className = 'h-32 w-auto rounded object-cover';
        currentImageContainer.appendChild(img);
    } else {
        const noImg = document.createElement('div');
        noImg.className = 'h-32 w-32 bg-gray-200 rounded flex items-center justify-center';
        noImg.innerHTML = '<i class="fas fa-image text-gray-400 text-3xl"></i>';
        currentImageContainer.appendChild(noImg);
    }
    
    document.getElementById('editResourceModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editResourceModal').classList.add('hidden');
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('editResourceModal');
    if (event.target === modal) {
        closeEditModal();
    }
});
</script>

<?php include '../includes/footer.php'; ?> 