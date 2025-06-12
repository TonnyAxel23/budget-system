<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../classes/User.php';
require_once '../classes/Transaction.php';
require_once '../classes/Category.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$transaction = new Transaction();
$category = new Category();

// Get current month/year
$current_month = date('m');
$current_year = date('Y');

// Get summary data
$summary = $transaction->getSummary($user_id, $current_month, $current_year);
$total_income = $summary['total_income'] ?? 0;
$total_expense = $summary['total_expense'] ?? 0;
$balance = $total_income - $total_expense;

// Get recent transactions
$recent_transactions = $transaction->getTransactions($user_id, null, $current_month, $current_year);
$recent_transactions = array_slice($recent_transactions, 0, 5);

// Get categories for expense breakdown
$expense_categories = $category->getCategoriesByType($user_id, 'expense');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Dashboard</h1>
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-600 mb-2">Total Income</h3>
                <p class="text-2xl font-bold text-green-600">$<?php echo number_format($total_income, 2); ?></p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-600 mb-2">Total Expenses</h3>
                <p class="text-2xl font-bold text-red-600">$<?php echo number_format($total_expense, 2); ?></p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-600 mb-2">Current Balance</h3>
                <p class="text-2xl font-bold <?php echo $balance >= 0 ? 'text-blue-600' : 'text-red-600'; ?>">
                    $<?php echo number_format($balance, 2); ?>
                </p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Transactions -->
            <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Recent Transactions</h2>
                    <a href="transactions.php" class="text-blue-600 hover:underline">View All</a>
                </div>
                
                <?php if (empty($recent_transactions)): ?>
                    <p class="text-gray-500">No transactions found.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($recent_transactions as $transaction): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M j, Y', strtotime($transaction['date'])); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" style="background-color: <?php echo $transaction['color'] ?? '#666666'; ?>; color: white;">
                                                <?php echo $transaction['category_name']; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $transaction['description']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium <?php echo $transaction['category_type'] === 'income' ? 'text-green-600' : 'text-red-600'; ?>">
                                            <?php echo ($transaction['category_type'] === 'income' ? '+' : '-') . number_format($transaction['amount'], 2); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Expense Breakdown -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Expense Breakdown</h2>
                <canvas id="expenseChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <script>
        // Expense Chart
        const expenseCtx = document.getElementById('expenseChart').getContext('2d');
        const expenseChart = new Chart(expenseCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($expense_categories, 'name')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($expense_categories, 'total_amount')); ?>,
                    backgroundColor: <?php echo json_encode(array_column($expense_categories, 'color')); ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
    </script>
</body>
</html>