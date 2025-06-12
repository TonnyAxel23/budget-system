<?php
require_once '../includes/db.php';

class Budget {
    private $db;
    
    public function __construct() {
        $this->db = new DB();
    }

    public function setBudgetGoal($user_id, $category_id, $monthly_limit) {
        $conn = $this->db->getConnection();
        
        // Check if goal already exists
        $stmt = $conn->prepare("SELECT id FROM budget_goals WHERE user_id = ? AND category_id = ?");
        $stmt->bind_param("ii", $user_id, $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing goal
            $stmt = $conn->prepare("UPDATE budget_goals SET monthly_limit = ? WHERE user_id = ? AND category_id = ?");
            $stmt->bind_param("dii", $monthly_limit, $user_id, $category_id);
        } else {
            // Insert new goal
            $stmt = $conn->prepare("INSERT INTO budget_goals (user_id, category_id, monthly_limit) VALUES (?, ?, ?)");
            $stmt->bind_param("iid", $user_id, $category_id, $monthly_limit);
        }
        
        return $stmt->execute();
    }

    public function getBudgetGoals($user_id) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT bg.*, c.name as category_name, c.type as category_type, c.color as category_color,
                COALESCE(SUM(t.amount), 0) as current_spending
                FROM budget_goals bg
                JOIN categories c ON bg.category_id = c.id
                LEFT JOIN transactions t ON t.category_id = c.id 
                    AND MONTH(t.date) = MONTH(CURRENT_DATE()) 
                    AND YEAR(t.date) = YEAR(CURRENT_DATE())
                WHERE bg.user_id = ?
                GROUP BY bg.id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $goals = array();
        while ($row = $result->fetch_assoc()) {
            $row['remaining'] = $row['monthly_limit'] - $row['current_spending'];
            $row['percentage'] = ($row['current_spending'] / $row['monthly_limit']) * 100;
            $goals[] = $row;
        }
        
        return $goals;
    }

    public function deleteBudgetGoal($id, $user_id) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("DELETE FROM budget_goals WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        
        return $stmt->execute();
    }
}
?>