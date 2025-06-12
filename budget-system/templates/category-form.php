<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../classes/Category.php';

redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$category = new Category();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    $color = $_POST['color'];
    $icon = $_POST['icon'] ?? null;
    
    if (empty($name)) {
        $error = 'Category name is required';
    } else {
        if (isset($_POST['id'])) {
            // Update existing category
            if ($category->updateCategory($_POST['id'], $user_id, $name, $type, $color, $icon)) {
                $_SESSION['message'] = 'Category updated successfully';
                $_SESSION['message_type'] = 'success';
                header('Location: categories.php');
                exit;
            } else {
                $error = 'Error updating category';
            }
        } else {
            // Add new category
            if ($category->addCategory($user_id, $name, $type, $color, $icon)) {
                $_SESSION['message'] = 'Category added successfully';
                $_SESSION['message_type'] = 'success';
                header('Location: categories.php');
                exit;
            } else {
                $error = 'Error adding category';
            }
        }
    }
}

// Get existing category if editing
$existing_category = null;
if (isset($_GET['id'])) {
    $existing_category = $category->getCategoryById($_GET['id'], $user_id);
    if (!$existing_category) {
        header('Location: categories.php');
        exit;
    }
}

$page_title = $existing_category ? "Edit Category" : "Add Category";
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
        
        <form method="post" action="category-form.php" class="bg-white shadow rounded-lg p-6">
            <?php if ($existing_category): ?>
                <input type="hidden" name="id" value="<?php echo $existing_category['id']; ?>">
            <?php endif; ?>
            
            <div class="mb-4">
                <label for="name" class="form-label">Category Name</label>
                <input type="text" id="name" name="name" class="form-input" required
                       value="<?php echo $existing_category['name'] ?? ''; ?>">
            </div>
            
            <div class="mb-4">
                <label class="form-label">Type</label>
                <div class="mt-1">
                    <label class="inline-flex items-center mr-4">
                        <input type="radio" name="type" value="income" class="form-radio" 
                               <?php echo (isset($existing_category['type']) && $existing_category['type'] === 'income') ? 'checked' : ''; ?> required>
                        <span class="ml-2">Income</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="type" value="expense" class="form-radio" 
                               <?php echo (isset($existing_category['type']) && $existing_category['type'] === 'expense') ? 'checked' : ''; ?>>
                        <span class="ml-2">Expense</span>
                    </label>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="color" class="form-label">Color</label>
                <input type="color" id="color" name="color" class="h-10 w-16 cursor-pointer" 
                       value="<?php echo $existing_category['color'] ?? '#666666'; ?>" required>
            </div>
            
            <div class="mb-6">
                <label for="icon" class="form-label">Icon (optional)</label>
                <input type="text" id="icon" name="icon" class="form-input" 
                       placeholder="e.g. fas fa-shopping-cart"
                       value="<?php echo $existing_category['icon'] ?? ''; ?>">
                <p class="text-xs text-gray-500 mt-1">Use Font Awesome icon classes (e.g. fas fa-home)</p>
            </div>
            
            <div class="flex justify-between">
                <a href="categories.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                    Cancel
                </a>
                <button type="submit" class="form-button">
                    <?php echo $existing_category ? 'Update' : 'Save'; ?> Category
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>