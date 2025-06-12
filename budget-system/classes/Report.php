<?php
require_once '../includes/db.php';

class Report {
    private $db;
    
    public function __construct() {
        $this->db = new DB();
    }

    public function generateMonthlyReport($user_id, $month, $year) {
        $conn = $this->db->getConnection();
        
        // Get income/expense summary
        $sql = "SELECT 
                c.type,
                SUM(t.amount) as total,
                COUNT(t.id) as count
                FROM transactions t
                JOIN categories c ON t.category_id = c.id
                WHERE t.user_id = ? AND MONTH(t.date) = ? AND YEAR(t.date) = ?
                GROUP BY c.type";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $user_id, $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $report = [
            'income' => ['total' => 0, 'count' => 0],
            'expense' => ['total' => 0, 'count' => 0],
            'categories' => [],
            'transactions' => []
        ];
        
        while ($row = $result->fetch_assoc()) {
            $report[strtolower($row['type'])] = [
                'total' => $row['total'],
                'count' => $row['count']
            ];
        }
        
        // Get category breakdown
        $sql = "SELECT 
                c.name,
                c.color,
                SUM(t.amount) as total,
                COUNT(t.id) as count
                FROM transactions t
                JOIN categories c ON t.category_id = c.id
                WHERE t.user_id = ? AND MONTH(t.date) = ? AND YEAR(t.date) = ?
                GROUP BY c.id
                ORDER BY total DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $user_id, $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $report['categories'][] = $row;
        }
        
        // Get recent transactions
        $sql = "SELECT 
                t.*,
                c.name as category_name,
                c.type as category_type,
                c.color as category_color
                FROM transactions t
                JOIN categories c ON t.category_id = c.id
                WHERE t.user_id = ? AND MONTH(t.date) = ? AND YEAR(t.date) = ?
                ORDER BY t.date DESC
                LIMIT 10";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $user_id, $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $report['transactions'][] = $row;
        }
        
        return $report;
    }

    public function exportToCSV($user_id, $month, $year) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT 
                t.date,
                c.name as category,
                c.type,
                t.amount,
                t.description
                FROM transactions t
                JOIN categories c ON t.category_id = c.id
                WHERE t.user_id = ? AND MONTH(t.date) = ? AND YEAR(t.date) = ?
                ORDER BY t.date DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $user_id, $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $filename = "transactions_" . date('F_Y', mktime(0, 0, 0, $month, 1, $year)) . ".csv";
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // CSV header
        fputcsv($output, ['Date', 'Category', 'Type', 'Amount', 'Description']);
        
        // CSV data
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['date'],
                $row['category'],
                ucfirst($row['type']),
                ($row['type'] === 'income' ? '+' : '-') . $row['amount'],
                $row['description']
            ]);
        }
        
        fclose($output);
        exit;
    }
}
?>