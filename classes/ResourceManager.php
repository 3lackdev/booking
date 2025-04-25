<?php
/**
 * Resource Manager Class
 */

class ResourceManager {
    private $conn;
    
    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $this->conn = getDbConnection();
    }
    
    public function __destruct() {
        closeDbConnection($this->conn);
    }
    
    /**
     * Get all resource categories
     * 
     * @param bool $active_only
     * @return array
     */
    public function getCategories($active_only = true) {
        $status_condition = $active_only ? " WHERE status = 'active'" : "";
        
        $sql = "SELECT * FROM resource_categories" . $status_condition . " ORDER BY name";
        $result = $this->conn->query($sql);
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        
        return $categories;
    }
    
    /**
     * Get category by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function getCategoryById($id) {
        $id = (int) $id;
        
        $sql = "SELECT * FROM resource_categories WHERE id = $id";
        $result = $this->conn->query($sql);
        
        if ($result->num_rows == 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Add new category
     * 
     * @param string $name
     * @param string $description
     * @return bool|string
     */
    public function addCategory($name, $description) {
        $name = $this->conn->real_escape_string($name);
        $description = $this->conn->real_escape_string($description);
        
        $sql = "INSERT INTO resource_categories (name, description) VALUES ('$name', '$description')";
        
        if ($this->conn->query($sql) === TRUE) {
            return true;
        } else {
            return "Error: " . $this->conn->error;
        }
    }
    
    /**
     * Update category
     * 
     * @param int $id
     * @param string $name
     * @param string $description
     * @param string $status
     * @return bool|string
     */
    public function updateCategory($id, $name, $description, $status) {
        $id = (int) $id;
        $name = $this->conn->real_escape_string($name);
        $description = $this->conn->real_escape_string($description);
        $status = $this->conn->real_escape_string($status);
        
        $sql = "UPDATE resource_categories SET 
                name = '$name', 
                description = '$description', 
                status = '$status' 
                WHERE id = $id";
                
        if ($this->conn->query($sql) === TRUE) {
            return true;
        } else {
            return "Error: " . $this->conn->error;
        }
    }
    
    /**
     * Delete category
     * 
     * @param int $id
     * @return bool|string
     */
    public function deleteCategory($id) {
        $id = (int) $id;
        
        $sql = "DELETE FROM resource_categories WHERE id = $id";
        
        if ($this->conn->query($sql) === TRUE) {
            return true;
        } else {
            return "Error: " . $this->conn->error;
        }
    }
    
    /**
     * Get all resources
     * 
     * @param int|null $category_id
     * @param bool $available_only
     * @return array
     */
    public function getResources($category_id = null, $available_only = false) {
        $where_conditions = [];
        
        if ($category_id) {
            $category_id = (int) $category_id;
            $where_conditions[] = "r.category_id = $category_id";
        }
        
        if ($available_only) {
            $where_conditions[] = "r.status = 'available'";
        }
        
        $where_clause = "";
        if (!empty($where_conditions)) {
            $where_clause = " WHERE " . implode(" AND ", $where_conditions);
        }
        
        $sql = "SELECT r.*, c.name AS category_name 
                FROM resources r
                JOIN resource_categories c ON r.category_id = c.id" . 
                $where_clause . 
                " ORDER BY r.name";
        
        $result = $this->conn->query($sql);
        
        $resources = [];
        while ($row = $result->fetch_assoc()) {
            $resources[] = $row;
        }
        
        return $resources;
    }
    
    /**
     * Get resource by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function getResourceById($id) {
        $id = (int) $id;
        
        $sql = "SELECT r.*, c.name AS category_name 
                FROM resources r
                JOIN resource_categories c ON r.category_id = c.id
                WHERE r.id = $id";
        
        $result = $this->conn->query($sql);
        
        if ($result->num_rows == 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Add new resource
     * 
     * @param int $category_id
     * @param string $name
     * @param string $description
     * @param string $location
     * @param int|null $capacity
     * @param string|null $image_path
     * @return bool|string
     */
    public function addResource($category_id, $name, $description, $location, $capacity = null, $image_path = null) {
        $category_id = (int) $category_id;
        $name = $this->conn->real_escape_string($name);
        $description = $this->conn->real_escape_string($description);
        $location = $this->conn->real_escape_string($location);
        
        if ($capacity !== null) {
            $capacity = (int) $capacity;
        } else {
            $capacity = "NULL";
        }
        
        if ($image_path !== null) {
            $image_path = "'" . $this->conn->real_escape_string($image_path) . "'";
        } else {
            $image_path = "NULL";
        }
        
        $sql = "INSERT INTO resources (category_id, name, description, location, capacity, image_path) 
                VALUES ($category_id, '$name', '$description', '$location', $capacity, $image_path)";
        
        if ($this->conn->query($sql) === TRUE) {
            return true;
        } else {
            return "Error: " . $this->conn->error;
        }
    }
    
    /**
     * Update resource
     * 
     * @param int $id
     * @param int $category_id
     * @param string $name
     * @param string $description
     * @param string $location
     * @param int|null $capacity
     * @param string $status
     * @param string|null $image_path
     * @return bool|string
     */
    public function updateResource($id, $category_id, $name, $description, $location, $capacity = null, $status = 'available', $image_path = null) {
        $id = (int) $id;
        $category_id = (int) $category_id;
        $name = $this->conn->real_escape_string($name);
        $description = $this->conn->real_escape_string($description);
        $location = $this->conn->real_escape_string($location);
        $status = $this->conn->real_escape_string($status);
        
        if ($capacity !== null) {
            $capacity = (int) $capacity;
        } else {
            $capacity = "NULL";
        }
        
        // Build the SQL query parts
        $sql_parts = [
            "category_id = $category_id",
            "name = '$name'",
            "description = '$description'",
            "location = '$location'",
            "capacity = $capacity",
            "status = '$status'"
        ];
        
        // Add image_path if provided
        if ($image_path !== null) {
            $image_path = $this->conn->real_escape_string($image_path);
            $sql_parts[] = "image_path = '$image_path'";
        }
        
        // Construct the full SQL query
        $sql = "UPDATE resources SET " . implode(", ", $sql_parts) . " WHERE id = $id";
                
        if ($this->conn->query($sql) === TRUE) {
            return true;
        } else {
            return "Error: " . $this->conn->error;
        }
    }
    
    /**
     * Delete resource
     * 
     * @param int $id
     * @return bool|string
     */
    public function deleteResource($id) {
        $id = (int) $id;
        
        $sql = "DELETE FROM resources WHERE id = $id";
        
        if ($this->conn->query($sql) === TRUE) {
            return true;
        } else {
            return "Error: " . $this->conn->error;
        }
    }
    
    /**
     * Check if resource is available for booking
     * 
     * @param int $resource_id
     * @param string $start_time
     * @param string $end_time
     * @param int|null $exclude_booking_id
     * @return bool
     */
    public function isResourceAvailable($resource_id, $start_time, $end_time, $exclude_booking_id = null) {
        $resource_id = (int) $resource_id;
        $start_time = $this->conn->real_escape_string($start_time);
        $end_time = $this->conn->real_escape_string($end_time);
        
        // Check resource status
        $sql = "SELECT status FROM resources WHERE id = $resource_id";
        $result = $this->conn->query($sql);
        
        if ($result->num_rows == 0 || $result->fetch_assoc()['status'] != 'available') {
            return false;
        }
        
        // Check for conflicting bookings
        $exclude_condition = "";
        if ($exclude_booking_id) {
            $exclude_booking_id = (int) $exclude_booking_id;
            $exclude_condition = " AND id != $exclude_booking_id";
        }
        
        $sql = "SELECT COUNT(*) AS conflict_count FROM bookings 
                WHERE resource_id = $resource_id 
                AND status IN ('pending', 'confirmed') 
                AND (
                    (start_time <= '$start_time' AND end_time > '$start_time') OR
                    (start_time < '$end_time' AND end_time >= '$end_time') OR
                    (start_time >= '$start_time' AND end_time <= '$end_time')
                )" . $exclude_condition;
        
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        
        return $row['conflict_count'] == 0;
    }
} 