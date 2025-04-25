<?php
/**
 * Utility Functions
 */

/**
 * Redirect to a URL
 * 
 * @param string $url
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Display flash message
 * 
 * @param string $message
 * @param string $type (success, error, warning, info)
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get flash message and clear it
 * 
 * @return array|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $type = $flash['type'];
        $message = $flash['message'];
        
        $bg_color = 'bg-blue-100 border-blue-500 text-blue-700';
        $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>';
        
        if ($type == 'success') {
            $bg_color = 'bg-green-100 border-green-500 text-green-700';
            $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>';
        } elseif ($type == 'error') {
            $bg_color = 'bg-red-100 border-red-500 text-red-700';
            $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>';
        } elseif ($type == 'warning') {
            $bg_color = 'bg-yellow-100 border-yellow-500 text-yellow-700';
            $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>';
        }
        
        echo <<<HTML
        <div class="flex p-4 mb-4 text-sm border-t-4 rounded-lg $bg_color" role="alert">
            <div class="inline-flex items-center mr-3">
                $icon
            </div>
            <div>
                <span class="font-medium">$message</span>
            </div>
        </div>
        HTML;
    }
}

/**
 * Format date and time
 * 
 * @param string $datetime
 * @param string $format
 * @return string
 */
function formatDateTime($datetime, $format = 'M d, Y h:i A') {
    return date($format, strtotime($datetime));
}

/**
 * Format date
 * 
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Secure input data
 * 
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Generate pagination HTML
 * 
 * @param int $current_page
 * @param int $total_pages
 * @param string $url_pattern
 * @return string
 */
function generatePagination($current_page, $total_pages, $url_pattern) {
    if ($total_pages <= 1) {
        return '';
    }
    
    $html = '<div class="flex justify-center mt-6">
              <nav aria-label="Page navigation">
                <ul class="inline-flex items-center -space-x-px">';
    
    // Previous button
    if ($current_page > 1) {
        $prev_url = str_replace('{PAGE}', $current_page - 1, $url_pattern);
        $html .= '<li>
                    <a href="' . $prev_url . '" class="block px-3 py-2 ml-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700">
                      <span class="sr-only">Previous</span>
                      <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    </a>
                  </li>';
    } else {
        $html .= '<li>
                    <span class="block px-3 py-2 ml-0 leading-tight text-gray-300 bg-white border border-gray-300 rounded-l-lg cursor-not-allowed">
                      <span class="sr-only">Previous</span>
                      <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    </span>
                  </li>';
    }
    
    // Page numbers
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    if ($start > 1) {
        $url = str_replace('{PAGE}', 1, $url_pattern);
        $html .= '<li>
                    <a href="' . $url . '" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700">1</a>
                  </li>';
        if ($start > 2) {
            $html .= '<li>
                        <span class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300">...</span>
                      </li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $current_page) {
            $html .= '<li>
                        <span aria-current="page" class="px-3 py-2 text-blue-600 border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white">' . $i . '</span>
                      </li>';
        } else {
            $url = str_replace('{PAGE}', $i, $url_pattern);
            $html .= '<li>
                        <a href="' . $url . '" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700">' . $i . '</a>
                      </li>';
        }
    }
    
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            $html .= '<li>
                        <span class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300">...</span>
                      </li>';
        }
        $url = str_replace('{PAGE}', $total_pages, $url_pattern);
        $html .= '<li>
                    <a href="' . $url . '" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700">' . $total_pages . '</a>
                  </li>';
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $next_url = str_replace('{PAGE}', $current_page + 1, $url_pattern);
        $html .= '<li>
                    <a href="' . $next_url . '" class="block px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700">
                      <span class="sr-only">Next</span>
                      <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    </a>
                  </li>';
    } else {
        $html .= '<li>
                    <span class="block px-3 py-2 leading-tight text-gray-300 bg-white border border-gray-300 rounded-r-lg cursor-not-allowed">
                      <span class="sr-only">Next</span>
                      <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    </span>
                  </li>';
    }
    
    $html .= '  </ul>
              </nav>
            </div>';
    
    return $html;
}

/**
 * Handle file upload for resource images
 * 
 * @param array $file The uploaded file ($_FILES['image'])
 * @return array|bool Returns array with 'path' and 'error' on success or error, or false if no file
 */
function handleResourceImageUpload($file) {
    // If no file uploaded or error occurred
    if (!isset($file) || $file['error'] == UPLOAD_ERR_NO_FILE) {
        return false;
    }
    
    // Check for upload errors
    if ($file['error'] != UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        
        $error_message = isset($error_messages[$file['error']]) 
            ? $error_messages[$file['error']] 
            : 'Unknown upload error';
            
        return ['path' => null, 'error' => $error_message];
    }
    
    // Check file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        return ['path' => null, 'error' => 'Only JPG, PNG, GIF, and WEBP image files are allowed'];
    }
    
    // Check file size (5MB max)
    $max_size = 5 * 1024 * 1024; // 5MB in bytes
    if ($file['size'] > $max_size) {
        return ['path' => null, 'error' => 'Image file size must be less than 5MB'];
    }
    
    // Create upload directory if it doesn't exist
    $upload_dir = __DIR__ . '/../uploads/resources/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $unique_name = uniqid('resource_') . '.' . $extension;
    $upload_path = $upload_dir . $unique_name;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['path' => 'uploads/resources/' . $unique_name, 'error' => null];
    } else {
        return ['path' => null, 'error' => 'Failed to move uploaded file'];
    }
} 