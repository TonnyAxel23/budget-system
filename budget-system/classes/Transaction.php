<?php
require_once '../includes/db.php';

class Transaction {
    private $db;
    
    public function __construct() {
        $this->db = new DB();
    }

    public function addTransaction($user_id, $category_id, $amount, $description, $date) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, category_id, amount, description, date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iidss", $user_id, $category_id, $amount, $description, $date);
        
        return $stmt->execute();
    }

    public function getTransactions($user_id, $type = null, $month = null, $year = null) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT t.*, c.name as category_name, c.type as category_type 
                FROM transactions t
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE t.user_id = ?";
        
        $params = array($user_id);
        $types = "i";
        
        if ($type) {
            $sql .= " AND c.type = ?";
            $params[] = $type;
            $types .= "s";
        }
        
        if ($month && $year) {
            $sql .= " AND MONTH(t.date) = ? AND YEAR(t.date) = ?";
            $params[] = $month;
            $params[] = $year;
            $types .= "ii";
        }
        
        $sql .= " ORDER BY t.date DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $transactions = array();
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        
        return $transactions;
    }

    public function getTransactionById($id, $user_id) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    public function updateTransaction($id, $user_id, $category_id, $amount, $description, $date) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("UPDATE transactions SET category_id = ?, amount = ?, description = ?, date = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("idssii", $category_id, $amount, $description, $date, $id, $user_id);
        
        return $stmt->execute();
    }

    public function deleteTransaction($id, $user_id) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        
        return $stmt->execute();
    }

    public function getSummary($user_id, $month = null, $year = null) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT 
                SUM(CASE WHEN c.type = 'income' THEN t.amount ELSE 0 END) as total_income,
                SUM(CASE WHEN c.type = 'expense' THEN t.amount ELSE 0 END) as total_expense
                FROM transactions t
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE t.user_id = ?";
        
        $params = array($user_id);
        $types = "i";
        
        if ($month && $year) {
            $sql .= " AND MONTH(t.date) = ? AND YEAR(t.date) = ?";
            $params[] = $month;
            $params[] = $year;
            $types .= "ii";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
}
?>