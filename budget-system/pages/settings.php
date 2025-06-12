<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../classes/User.php';

redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$user = new User();
$user_data = $user->getUserById($user_id);

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Username is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    // Check if password is being changed
    $password_changed = false;
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = 'Current password is required to change password';
        } elseif (strlen($new_password) < 8) {
            $errors[] = 'New password must be at least 8 characters';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'New passwords do not match';
        } else {
            $password_changed = true;
        }
    }
    
    if (empty($errors)) {
        // Verify current password if changing password
        if ($password_changed) {
            $conn = (new DB())->getConnection();
            $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if (!password_verify($current_password, $user['password_hash'])) {
                $errors[] = 'Current password is incorrect';
            }
        }
        
        if (empty($errors)) {
            // Update user data
            $conn = (new DB())->getConnection();
            
            if ($password_changed) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password_hash = ? WHERE id = ?");
                $stmt->bind_param("sssi", $username, $email, $hashed_password, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $username, $email, $user_id);
            }
            
            if ($stmt->execute()) {
                $message = 'Profile updated successfully';
                $message_type = 'success';
                $_SESSION['username'] = $username;
                $user_data = $user->getUserById($user_id); // Refresh data
            } else {
                $message = 'Error updating profile: ' . $conn->error;
                $message_type = 'error';
            }
        }
    }
    
    if (!empty($errors)) {
        $message = implode('<br>', $errors);
        $message_type = 'error';
    }
}

$page_title = "Account Settings";
?>
<?php include '../includes/head.php'; ?>

<?php include '../includes/navbar.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Account Settings</h1>
        
        <?php if ($message): ?>
            <div class="mb-6 <?php echo $message_type === 'success' ? 'alert-success' : 'alert-error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="bg-white shadow rounded-lg p-6">
            <form method="post" action="settings.php">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-4">Profile Information</h2>
                    
                    <div class="mb-4">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-input" 
                               value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-input" 
                               value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-4">Change Password</h2>
                    <p class="text-gray-500 mb-4">Leave blank to keep current password</p>
                    
                    <div class="mb-4">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-input">
                    </div>
                    
                    <div class="mb-4">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-input">
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input">
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="form-button">Save Changes</button>
                </div>
            </form>
        </div>
        
        <div class="bg-white shadow rounded-lg p-6 mt-6 border border-red-200">
            <h2 class="text-xl font-semibold mb-4 text-red-600">Danger Zone</h2>
            
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-medium">Delete Account</h3>
                    <p class="text-gray-500 text-sm">Once you delete your account, there is no going back.</p>
                </div>
                
                <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm" 
                        onclick="confirmDelete()">
                    Delete Account
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete your account? All your data will be permanently lost.')) {
        // In a real application, this would be a form submission to a delete endpoint
        alert('Account deletion would be processed here. This is just a demo.');
    }
}
</script>

<?php include '../includes/footer.php'; ?>