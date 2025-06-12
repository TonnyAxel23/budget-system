<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../classes/Category.php';
require_once '../classes/Budget.php';

redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$category = new Category();
$budget = new Budget();

// Get all expense categories
$categories = $category->getCategoriesByType($user_id, 'expense');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'];
    $monthly_limit = $_POST['monthly_limit'];
    
    if ($budget->setBudgetGoal($user_id, $category_id, $monthly_limit)) {
        $_SESSION['message'] = 'Budget goal saved successfully';
        $_SESSION['message_type'] = 'success';
        header('Location: budgets.php');
        exit;
    } else {
        $error = 'Error saving budget goal';
    }
}

// Get existing budget if editing
$existing_budget = null;
if (isset($_GET['id'])) {
    $existing_budget = $budget->getBudgetGoalById($_GET['id'], $user_id);
    if (!$existing_budget) {
        header('Location: budgets.php');
        exit;
    }
}

$page_title = $existing_budget ? "Edit Budget Goal" : "Add Budget Goal";
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
        
        <form method="post" action="budget-form.php" class="bg-white shadow rounded-lg p-6">
            <?php if ($existing_budget): ?>
                <input type="hidden" name="id" value="<?php echo $existing_budget['id']; ?>">
            <?php endif; ?>
            
            <div class="mb-4">
                <label for="category_id" class="form-label">Category</label>
                <select id="category_id" name="category_id" class="form-input" required 
                    <?php echo $existing_budget ? 'disabled' : ''; ?>>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                            <?php echo ($existing_budget && $existing_budget['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo $cat['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($existing_budget): ?>
                    <input type="hidden" name="category_id" value="<?php echo $existing_budget['category_id']; ?>">
                <?php endif; ?>
            </div>
            
            <div class="mb-6">
                <label for="monthly_limit" class="form-label">Monthly Limit ($)</label>
                <input type="number" id="monthly_limit" name="monthly_limit" class="form-input" 
                       min="0" step="0.01" required
                       value="<?php echo $existing_budget['monthly_limit'] ?? ''; ?>">
            </div>
            
            <div class="flex justify-between">
                <a href="budgets.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                    Cancel
                </a>
                <button type="submit" class="form-button">
                    <?php echo $existing_budget ? 'Update' : 'Save'; ?> Budget
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>