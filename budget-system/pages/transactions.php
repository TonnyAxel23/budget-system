<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../classes/Transaction.php';
require_once '../classes/Category.php';

redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$transaction = new Transaction();
$category = new Category();

// Get filter parameters
$type = $_GET['type'] ?? null;
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$category_id = $_GET['category'] ?? null;

// Get transactions based on filters
$transactions = $transaction->getTransactions($user_id, $type, $month, $year, $category_id);

// Get all categories for filter dropdown
$categories = $category->getCategories($user_id);

// Get months/years for filter
$months = [
    '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
    '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
    '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
];

$years = range(date('Y') - 5, date('Y'));
rsort($years);

$page_title = "Transactions";
?>
<?php include '../includes/head.php'; ?>

<?php include '../includes/navbar.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <h1 class="text-3xl font-bold">Transactions</h1>
        
        <div class="mt-4 md:mt-0">
            <a href="transaction-form.php" class="form-button">
                <i class="fas fa-plus mr-2"></i> Add Transaction
            </a>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <form method="get" action="transactions.php" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="form-label">Type</label>
                <select name="type" class="form-input">
                    <option value="">All</option>
                    <option value="income" <?php echo $type === 'income' ? 'selected' : ''; ?>>Income</option>
                    <option value="expense" <?php echo $type === 'expense' ? 'selected' : ''; ?>>Expense</option>
                </select>
            </div>
            
            <div>
                <label class="form-label">Month</label>
                <select name="month" class="form-input">
                    <?php foreach ($months as $key => $name): ?>
                        <option value="<?php echo $key; ?>" <?php echo $key == $month ? 'selected' : ''; ?>>
                            <?php echo $name; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="form-label">Year</label>
                <select name="year" class="form-input">
                    <?php foreach ($years as $y): ?>
                        <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="form-label">Category</label>
                <select name="category" class="form-input">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $category_id ? 'selected' : ''; ?>>
                            <?php echo $cat['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="form-button h-10">Apply Filters</button>
            </div>
        </form>
    </div>
    
    <!-- Transactions Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <?php if (empty($transactions)): ?>
            <div class="p-6 text-center text-gray-500">
                No transactions found. <a href="transaction-form.php" class="text-blue-600 hover:underline">Add one now</a>.
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full responsive-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M j, Y', strtotime($t['date'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $t['category_type'] === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo ucfirst($t['category_type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                          style="background-color: <?php echo $t['color'] ?? '#666666'; ?>; color: white;">
                                        <?php echo $t['category_name']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $t['description']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium <?php echo $t['category_type'] === 'income' ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo ($t['category_type'] === 'income' ? '+' : '-') . number_format($t['amount'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="transaction-form.php?id=<?php echo $t['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                    <form method="post" action="process-transaction.php" class="inline delete-form">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>