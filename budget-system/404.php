<?php
require_once 'includes/config.php';

$page_title = "Page Not Found";
?>
<?php include 'includes/head.php'; ?>

<?php include 'includes/navbar.php'; ?>

<div class="container mx-auto px-4 py-16 text-center">
    <div class="max-w-md mx-auto">
        <h1 class="text-9xl font-bold text-gray-400 mb-4">404</h1>
        <h2 class="text-2xl font-bold text-gray-700 mb-4">Page Not Found</h2>
        <p class="text-gray-500 mb-8">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
        <a href="dashboard.php" class="form-button inline-block px-6 py-3">Go to Dashboard</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>