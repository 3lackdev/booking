<?php
/**
 * Booking Manager Class
 */

require_once __DIR__ . '/ResourceManager.php';

class BookingManager {
    private $conn;
    private $resourceManager;
    
    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $this->conn = getDbConnection();
        $this->resourceManager = new ResourceManager();
    }
    
    public function __destruct() {
        closeDbConnection($this->conn);
    }
    
    /**
     * Get all bookings
     * 
     * @param int|null $user_id
     * @param int|null $resource_id
     * @param string|null $status
     * @param string|null $start_date
     * @param string|null $end_date
     * @return array
     */
    public function getBookings($user_id = null, $resource_id = null, $status = null, $start_date = null, $end_date = null) {
        $where_conditions = [];
        
        if ($user_id) {
            $user_id = (int) $user_id;
            $where_conditions[] = "b.user_id = $user_id";
        }
        
        if ($resource_id) {
            $resource_id = (int) $resource_id;
            $where_conditions[] = "b.resource_id = $resource_id";
        }
        
        if ($status) {
            $status = $this->conn->real_escape_string($status);
            $where_conditions[] = "b.status = '$status'";
        }
        
        if ($start_date) {
            $start_date = $this->conn->real_escape_string($start_date);
            $where_conditions[] = "DATE(b.start_time) >= '$start_date'";
        }
        
        if ($end_date) {
            $end_date = $this->conn->real_escape_string($end_date);
            $where_conditions[] = "DATE(b.end_time) <= '$end_date'";
        }
        
        $where_clause = "";
        if (!empty($where_conditions)) {
            $where_clause = " WHERE " . implode(" AND ", $where_conditions);
        }
        
        $sql = "SELECT b.*, 
                r.name AS resource_name, 
                c.name AS category_name, 
                u.full_name AS user_name 
                FROM bookings b
                JOIN resources r ON b.resource_id = r.id
                JOIN resource_categories c ON r.category_id = c.id
                JOIN users u ON b.user_id = u.id" . 
                $where_clause . 
                " ORDER BY b.start_time DESC";
        
        $result = $this->conn->query($sql);
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        
        return $bookings;
    }
    
    /**
     * Get booking by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function getBookingById($id) {
        $id = (int) $id;
        
        $sql = "SELECT b.*, 
                r.name AS resource_name, 
                c.name AS category_name, 
                u.full_name AS user_name 
                FROM bookings b
                JOIN resources r ON b.resource_id = r.id
                JOIN resource_categories c ON r.category_id = c.id
                JOIN users u ON b.user_id = u.id
                WHERE b.id = $id";
        
        $result = $this->conn->query($sql);
        
        if ($result->num_rows == 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Create new booking
     * 
     * @param int $user_id
     * @param int $resource_id
     * @param string $title
     * @param string $description
     * @param string $start_time
     * @param string $end_time
     * @return bool|string
     */
    public function createBooking($user_id, $resource_id, $title, $description, $start_time, $end_time) {
        $user_id = (int) $user_id;
        $resource_id = (int) $resource_id;
        $title = $this->conn->real_escape_string($title);
        $description = $this->conn->real_escape_string($description);
        $start_time = $this->conn->real_escape_string($start_time);
        $end_time = $this->conn->real_escape_string($end_time);
        
        // Validate start and end times
        if (strtotime($start_time) >= strtotime($end_time)) {
            return "Error: End time must be after start time.";
        }
        
        // Check if resource is available
        if (!$this->resourceManager->isResourceAvailable($resource_id, $start_time, $end_time)) {
            return "Error: Resource is not available for the selected time period.";
        }
        
        $sql = "INSERT INTO bookings (user_id, resource_id, title, description, start_time, end_time) 
                VALUES ($user_id, $resource_id, '$title', '$description', '$start_time', '$end_time')";
        
        if ($this->conn->query($sql) === TRUE) {
            return true;
        } else {
            return "Error: " . $this->conn->error;
        }
    }
    
    /**
     * Update booking
     * 
     * @param int $id
     * @param int $resource_id
     * @param string $title
     * @param string $description
     * @param string $start_time
     * @param string $end_time
     * @param string $status
     * @return bool|string
     */
    public function updateBooking($id, $resource_id, $title, $description, $start_time, $end_time, $status) {
        $id = (int) $id;
        $resource_id = (int) $resource_id;
        $title = $this->conn->real_escape_string($title);
        $description = $this->conn->real_escape_string($description);
        $start_time = $this->conn->real_escape_string($start_time);
        $end_time = $this->conn->real_escape_string($end_time);
        $status = $this->conn->real_escape_string($status);
        
        // Validate start and end times
        if (strtotime($start_time) >= strtotime($end_time)) {
            return "Error: End time must be after start time.";
        }
        
        // Check if resource is available (excluding current booking)
        if (!$this->resourceManager->isResourceAvailable($resource_id, $start_time, $end_time, $id)) {
            return "Error: Resource is not available for the selected time period.";
        }
        
        $sql = "UPDATE bookings SET 
                resource_id = $resource_id, 
                title = '$title', 
                description = '$description', 
                start_time = '$start_time', 
                end_time = '$end_time', 
                status = '$status' 
                WHERE id = $id";
                
        if ($this->conn->query($sql) === TRUE) {
            return true;
        } else {
            return "Error: " . $this->conn->error;
        }
    }
    
    /**
     * Cancel booking
     * 
     * @param int $id
     * @return bool|string
     */
    public function cancelBooking($id) {
        $id = (int) $id;
        
        $sql = "UPDATE bookings SET status = 'cancelled' WHERE id = $id";
        
        if ($this->conn->query($sql) === TRUE) {
            return true;
        } else {
            return "Error: " . $this->conn->error;
        }
    }
    
    /**
     * Delete booking
     * 
     * @param int $id
     * @return bool|string
     */
    public function deleteBooking($id) {
        $id = (int) $id;
        
        $sql = "DELETE FROM bookings WHERE id = $id";
        
        if ($this->conn->query($sql) === TRUE) {
            return true;
        } else {
            return "Error: " . $this->conn->error;
        }
    }
    
    /**
     * Approve booking
     * 
     * @param int $id
     * @return bool|string
     */
    public function approveBooking($id) {
        $id = (int) $id;
        
        // Get booking info
        $booking = $this->getBookingById($id);
        if (!$booking) {
            return "Error: Booking not found.";
        }
        
        // Check if resource is available
        if (!$this->resourceManager->isResourceAvailable($booking['resource_id'], $booking['start_time'], $booking['end_time'], $id)) {
            return "Error: Resource is no longer available for this booking.";
        }
        
        $sql = "UPDATE bookings SET status = 'confirmed' WHERE id = $id";
        
        if ($this->conn->query($sql) === TRUE) {
            return true;
        } else {
            return "Error: " . $this->conn->error;
        }
    }
    
    /**
     * Get upcoming bookings for user
     * 
     * @param int $user_id
     * @param int $limit
     * @return array
     */
    public function getUpcomingBookingsForUser($user_id, $limit = 5) {
        $user_id = (int) $user_id;
        $limit = (int) $limit;
        
        $sql = "SELECT b.*, 
                r.name AS resource_name, 
                c.name AS category_name 
                FROM bookings b
                JOIN resources r ON b.resource_id = r.id
                JOIN resource_categories c ON r.category_id = c.id
                WHERE b.user_id = $user_id 
                AND b.status IN ('pending', 'confirmed') 
                AND b.start_time > NOW() 
                ORDER BY b.start_time ASC
                LIMIT $limit";
        
        $result = $this->conn->query($sql);
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        
        return $bookings;
    }
    
    /**
     * Get pending bookings for admin
     * 
     * @param int $limit
     * @return array
     */
    public function getPendingBookings($limit = 10) {
        $limit = (int) $limit;
        
        $sql = "SELECT b.*, 
                r.name AS resource_name, 
                c.name AS category_name, 
                u.full_name AS user_name 
                FROM bookings b
                JOIN resources r ON b.resource_id = r.id
                JOIN resource_categories c ON r.category_id = c.id
                JOIN users u ON b.user_id = u.id
                WHERE b.status = 'pending' 
                ORDER BY b.created_at ASC
                LIMIT $limit";
        
        $result = $this->conn->query($sql);
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        
        return $bookings;
    }
    
    /**
     * Get bookings by date range for resource
     * 
     * @param int $resource_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function getBookingsByDateRange($resource_id, $start_date, $end_date) {
        $resource_id = (int) $resource_id;
        $start_date = $this->conn->real_escape_string($start_date);
        $end_date = $this->conn->real_escape_string($end_date);
        
        $sql = "SELECT b.*, 
                u.full_name AS user_name 
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                WHERE b.resource_id = $resource_id 
                AND b.status IN ('pending', 'confirmed') 
                AND (
                    (DATE(b.start_time) >= '$start_date' AND DATE(b.start_time) <= '$end_date') OR
                    (DATE(b.end_time) >= '$start_date' AND DATE(b.end_time) <= '$end_date') OR
                    (DATE(b.start_time) <= '$start_date' AND DATE(b.end_time) >= '$end_date')
                )
                ORDER BY b.start_time ASC";
        
        $result = $this->conn->query($sql);
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        
        return $bookings;
    }
} 