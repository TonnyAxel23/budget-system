<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../classes/Transaction.php';
require_once '../classes/Report.php';

redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$transaction = new Transaction();
$report = new Report();

// Get current month/year or selected month/year
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

// Generate report data
$report_data = $report->generateMonthlyReport($user_id, $month, $year);

// Handle export
if (isset($_GET['export'])) {
    $report->exportToCSV($user_id, $month, $year);
}

// Get available months for filter
$months = [
    '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
    '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
    '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
];

$years = range(date('Y') - 5, date('Y'));
rsort($years);

$page_title = "Financial Reports - " . $months[$month] . " " . $year;
?>
<?php include '../includes/head.php'; ?>

<?php include '../includes/navbar.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <h1 class="text-3xl font-bold">Financial Reports</h1>
        
        <div class="mt-4 md:mt-0 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
            <!-- Month/Year Filter -->
            <form method="get" action="reports.php" class="flex space-x-2">
                <select name="month" class="form-input">
                    <?php foreach ($months as $key => $name): ?>
                        <option value="<?php echo $key; ?>" <?php echo $key == $month ? 'selected' : ''; ?>>
                            <?php echo $name; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="year" class="form-input">
                    <?php foreach ($years as $y): ?>
                        <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="form-button px-4 py-2">Filter</button>
            </form>
            
            <!-- Export Button -->
            <a href="reports.php?month=<?php echo $month; ?>&year=<?php echo $year; ?>&export=1" 
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-center">
                Export CSV
            </a>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="dashboard-card">
            <h3 class="text-lg font-semibold text-gray-600 mb-2">Total Income</h3>
            <p class="text-2xl font-bold text-green-600">
                $<?php echo number_format($report_data['income']['total'], 2); ?>
            </p>
            <p class="text-sm text-gray-500 mt-1">
                <?php echo $report_data['income']['count']; ?> transactions
            </p>
        </div>
        
        <div class="dashboard-card">
            <h3 class="text-lg font-semibold text-gray-600 mb-2">Total Expenses</h3>
            <p class="text-2xl font-bold text-red-600">
                $<?php echo number_format($report_data['expense']['total'], 2); ?>
            </p>
            <p class="text-sm text-gray-500 mt-1">
                <?php echo $report_data['expense']['count']; ?> transactions
            </p>
        </div>
        
        <div class="dashboard-card">
            <h3 class="text-lg font-semibold text-gray-600 mb-2">Net Savings</h3>
            <p class="text-2xl font-bold <?php echo ($report_data['income']['total'] - $report_data['expense']['total']) >= 0 ? 'text-blue-600' : 'text-red-600'; ?>">
                $<?php echo number_format($report_data['income']['total'] - $report_data['expense']['total'], 2); ?>
            </p>
            <p class="text-sm text-gray-500 mt-1">
                <?php echo ($report_data['income']['count'] + $report_data['expense']['count']); ?> total transactions
            </p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Expense Breakdown Chart -->
        <div class="dashboard-card">
            <h2 class="text-xl font-semibold mb-4">Expense Breakdown</h2>
            <div class="h-80">
                <canvas id="expenseChart"></canvas>
            </div>
        </div>
        
        <!-- Income vs Expense Chart -->
        <div class="dashboard-card">
            <h2 class="text-xl font-semibold mb-4">Income vs Expense</h2>
            <div class="h-80">
                <canvas id="incomeExpenseChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Recent Transactions -->
    <div class="dashboard-card mb-8">
        <h2 class="text-xl font-semibold mb-4">Recent Transactions</h2>
        
        <?php if (empty($report_data['transactions'])): ?>
            <p class="text-gray-500">No transactions found for this period.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="responsive-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($report_data['transactions'] as $t): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M j, Y', strtotime($t['date'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                          style="background-color: <?php echo $t['category_color']; ?>; color: white;">
                                        <?php echo $t['category_name']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $t['description']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium <?php echo $t['category_type'] === 'income' ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo ($t['category_type'] === 'income' ? '+' : '-') . number_format($t['amount'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Expense Breakdown Chart
    const expenseCtx = document.getElementById('expenseChart').getContext('2d');
    const expenseChart = new Chart(expenseCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($report_data['categories'], 'name')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($report_data['categories'], 'total')); ?>,
                backgroundColor: <?php echo json_encode(array_column($report_data['categories'], 'color')); ?>,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: $${value.toFixed(2)} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    
    // Income vs Expense Chart
    const incomeExpenseCtx = document.getElementById('incomeExpenseChart').getContext('2d');
    const incomeExpenseChart = new Chart(incomeExpenseCtx, {
        type: 'bar',
        data: {
            labels: ['Income', 'Expense', 'Net'],
            datasets: [{
                label: 'Amount',
                data: [
                    <?php echo $report_data['income']['total']; ?>,
                    <?php echo $report_data['expense']['total']; ?>,
                    <?php echo $report_data['income']['total'] - $report_data['expense']['total']; ?>
                ],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>