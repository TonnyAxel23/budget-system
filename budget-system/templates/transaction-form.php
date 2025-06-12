<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../classes/Transaction.php';
require_once '../classes/Category.php';

redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$transaction = new Transaction();
$category = new Category();

// Get all categories
$categories = $category->getCategories($user_id);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'];
    $amount = $_POST['amount'];
    $description = trim($_POST['description']);
    $date = $_POST['date'];
    
    if (empty($category_id) || empty($amount) || empty($date)) {
        $error = 'Please fill all required fields';
    } else {
        if (isset($_POST['id'])) {
            // Update existing transaction
            if ($transaction->updateTransaction($_POST['id'], $user_id, $category_id, $amount, $description, $date)) {
                $_SESSION['message'] = 'Transaction updated successfully';
                $_SESSION['message_type'] = 'success';
                header('Location: transactions.php');
                exit;
            } else {
                $error = 'Error updating transaction';
            }
        } else {
            // Add new transaction
            if ($transaction->addTransaction($user_id, $category_id, $amount, $description, $date)) {
                $_SESSION['message'] = 'Transaction added successfully';
                $_SESSION['message_type'] = 'success';
                header('Location: transactions.php');
                exit;
            } else {
                $error = 'Error adding transaction';
            }
        }
    }
}

// Get existing transaction if editing
$existing_transaction = null;
if (isset($_GET['id'])) {
    $existing_transaction = $transaction->getTransactionById($_GET['id'], $user_id);
    if (!$existing_transaction) {
        header('Location: transactions.php');
        exit;
    }
}

$page_title = $existing_transaction ? "Edit Transaction" : "Add Transaction";
?>
<?php include '../includes/head.php'; ?>

<?php include '../includes/navbar.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto">
        <h1 class="text-2xl font-bold mb-6"><?php echo $page_title; ?></h1>
        
        <?php if (isset($error)): ?>
            <div class="alert-error mb-6">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="transaction-form.php" class="bg-white shadow rounded-lg p-6">
            <?php if ($existing_transaction): ?>
                <input type="hidden" name="id" value="<?php echo $existing_transaction['id']; ?>">
            <?php endif; ?>
            
            <div class="mb-4">
                <label for="category_id" class="form-label">Category</label>
                <select id="category_id" name="category_id" class="form-input" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $cat): ?>
                        <optgroup label="<?php echo ucfirst($cat['type']); ?>">
                            <option value="<?php echo $cat['id']; ?>" 
                                data-type="<?php echo $cat['type']; ?>"
                                <?php echo (isset($existing_transaction['category_id']) && $existing_transaction['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo $cat['name']; ?>
                            </option>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label for="amount" class="form-label">Amount</label>
                <input type="number" id="amount" name="amount" class="form-input" 
                       min="0.01" step="0.01" required
                       value="<?php echo $existing_transaction['amount'] ?? ''; ?>">
            </div>
            
            <div class="mb-4">
                <label for="description" class="form-label">Description (optional)</label>
                <input type="text" id="description" name="description" class="form-input" 
                       value="<?php echo $existing_transaction['description'] ?? ''; ?>">
            </div>
            
            <div class="mb-6">
                <label for="date" class="form-label">Date</label>
                <input type="date" id="date" name="date" class="form-input datepicker" required
                       value="<?php echo $existing_transaction['date'] ?? date('Y-m-d'); ?>">
            </div>
            
            <div class="flex justify-between">
                <a href="transactions.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                    Cancel
                </a>
                <button type="submit" class="form-button">
                    <?php echo $existing_transaction ? 'Update' : 'Save'; ?> Transaction
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set amount field placeholder based on category type
    const categorySelect = document.getElementById('category_id');
    const amountInput = document.getElementById('amount');
    
    function updateAmountPlaceholder() {
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        const type = selectedOption.getAttribute('data-type');
        
        if (type === 'income') {
            amountInput.placeholder = 'Positive amount';
        } else if (type === 'expense') {
            amountInput.placeholder = 'Negative amount';
        } else {
            amountInput.placeholder = '';
        }
    }
    
    categorySelect.addEventListener('change', updateAmountPlaceholder);
    updateAmountPlaceholder(); // Initialize
});
</script>

<?php include '../includes/footer.php'; ?>