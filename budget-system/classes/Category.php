<?php
require_once '../includes/db.php';

class Category {
    private $db;
    
    public function __construct() {
        $this->db = new DB();
    }

    public function addCategory($user_id, $name, $type, $color = '#666666', $icon = null) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("INSERT INTO categories (user_id, name, type, color, icon) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $name, $type, $color, $icon);
        
        return $stmt->execute();
    }

    public function getCategories($user_id) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM categories WHERE user_id = ? ORDER BY type, name");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $categories = array();
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        
        return $categories;
    }

    public function getCategoriesByType($user_id, $type) {
        $conn = $this->db->getConnection();
        
        // Get categories with their total amounts
        $sql = "SELECT c.*, COALESCE(SUM(t.amount), 0) as total_amount
                FROM categories c
                LEFT JOIN transactions t ON c.id = t.category_id AND MONTH(t.date) = ? AND YEAR(t.date) = ?
                WHERE c.user_id = ? AND c.type = ?
                GROUP BY c.id
                ORDER BY total_amount DESC";
        
        $current_month = date('m');
        $current_year = date('Y');
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiis", $current_month, $current_year, $user_id, $type);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $categories = array();
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        
        return $categories;
    }

    public function getCategoryById($id, $user_id) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    public function updateCategory($id, $user_id, $name, $type, $color, $icon) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("UPDATE categories SET name = ?, type = ?, color = ?, icon = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssssii", $name, $type, $color, $icon, $id, $user_id);
        
        return $stmt->execute();
    }

    public function deleteCategory($id, $user_id) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        
        return $stmt->execute();
    }
}
?>