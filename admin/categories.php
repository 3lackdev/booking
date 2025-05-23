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

// Handle add category form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    
    // Validate input
    $errors = [];
    if (empty($name)) {
        $errors[] = "Category name is required.";
    }
    
    // If no validation errors, attempt to add category
    if (empty($errors)) {
        $result = $resourceManager->addCategory($name, $description);
        
        if ($result === true) {
            setFlashMessage("Category added successfully.", "success");
            redirect('categories.php');
        } else {
            setFlashMessage($result, "error");
        }
    } else {
        setFlashMessage($errors[0], "error");
    }
}

// Handle update category form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_category'])) {
    $id = (int)$_POST['id'];
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $status = sanitizeInput($_POST['status'] ?? 'active');
    
    // Validate input
    $errors = [];
    if (empty($name)) {
        $errors[] = "Category name is required.";
    }
    
    // If no validation errors, attempt to update category
    if (empty($errors)) {
        $result = $resourceManager->updateCategory($id, $name, $description, $status);
        
        if ($result === true) {
            setFlashMessage("Category updated successfully.", "success");
            redirect('categories.php');
        } else {
            setFlashMessage($result, "error");
        }
    } else {
        setFlashMessage($errors[0], "error");
    }
}

// Handle delete category form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_category'])) {
    $id = (int)$_POST['id'];
    
    $result = $resourceManager->deleteCategory($id);
    
    if ($result === true) {
        setFlashMessage("Category deleted successfully.", "success");
    } else {
        setFlashMessage($result, "error");
    }
    redirect('categories.php');
}

// Get resource categories
$categories = $resourceManager->getCategories(false);

$page_title = 'Manage Categories';
$active_page = 'admin_categories';
include '../includes/header.php';
?>

<div class="mb-6">
    <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
    </a>
</div>

<div class="flex flex-col md:flex-row gap-6">
    <!-- Add Category Form -->
    <div class="w-full md:w-1/3">
        <div class="bg-white overflow-hidden shadow-md rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Category</h3>
            
            <form method="POST">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Category Name</label>
                    <input type="text" name="name" id="name" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                </div>
                
                <div>
                    <button type="submit" name="add_category" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Categories List -->
    <div class="w-full md:w-2/3">
        <div class="bg-white overflow-hidden shadow-md rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Resource Categories</h3>
            </div>
            
            <?php if (empty($categories)): ?>
                <div class="p-6 text-center">
                    <p class="text-gray-500">No categories found.</p>
                </div>
            <?php else: ?>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Description
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
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $category['name']; ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500"><?php echo $category['description']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        <?php echo $category['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>
                                    ">
                                        <?php echo ucfirst($category['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button type="button" class="text-blue-600 hover:text-blue-900 mr-3" 
                                            onclick="openEditModal(<?php echo $category['id']; ?>, '<?php echo addslashes($category['name']); ?>', '<?php echo addslashes($category['description']); ?>', '<?php echo $category['status']; ?>')">
                                        Edit
                                    </button>
                                    
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this category? This will also delete all resources in this category.');">
                                        <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                        <button type="submit" name="delete_category" class="text-red-600 hover:text-red-900">
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

<!-- Edit Category Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
        <form method="POST">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Category</h3>
                
                <div class="mb-4">
                    <label for="edit_name" class="block text-sm font-medium text-gray-700">Category Name</label>
                    <input type="text" name="name" id="edit_name" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="edit_description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="edit_description" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="edit_status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="edit_status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="submit" name="update_category" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
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
    function openEditModal(id, name, description, status) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = description;
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