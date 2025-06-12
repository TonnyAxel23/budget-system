<?php
require_once 'includes/config.php';

$page_title = "Personal Budget Planner";
?>
<?php include 'includes/head.php'; ?>

<div class="min-h-screen flex flex-col">
    <?php include 'includes/navbar.php'; ?>
    
    <main class="flex-grow">
        <!-- Hero Section -->
        <section class="bg-blue-600 text-white py-20">
            <div class="container mx-auto px-4 text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">Take Control of Your Finances</h1>
                <p class="text-xl mb-8 max-w-2xl mx-auto">Track your income and expenses, set budget goals, and achieve financial freedom with our easy-to-use budget planner.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="dashboard.php" class="bg-white text-blue-600 hover:bg-gray-100 font-bold py-3 px-6 rounded-lg text-lg transition duration-300">Go to Dashboard</a>
                    <?php else: ?>
                        <a href="register.php" class="bg-white text-blue-600 hover:bg-gray-100 font-bold py-3 px-6 rounded-lg text-lg transition duration-300">Get Started</a>
                        <a href="login.php" class="bg-transparent border-2 border-white hover:bg-blue-700 font-bold py-3 px-6 rounded-lg text-lg transition duration-300">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        
        <!-- Features Section -->
        <section class="py-16 bg-gray-50">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-12">Key Features</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="text-blue-600 text-4xl mb-4">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Expense Tracking</h3>
                        <p class="text-gray-600">Categorize and track all your expenses to understand where your money goes.</p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="text-blue-600 text-4xl mb-4">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Income Management</h3>
                        <p class="text-gray-600">Record all income sources to get a complete picture of your cash flow.</p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="text-blue-600 text-4xl mb-4">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Budget Goals</h3>
                        <p class="text-gray-600">Set monthly spending limits and track your progress toward financial goals.</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Call to Action -->
        <section class="py-16 bg-blue-50">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-3xl font-bold mb-6">Ready to Transform Your Finances?</h2>
                <p class="text-xl mb-8 max-w-2xl mx-auto">Join thousands of users who are taking control of their money with our budget planner.</p>
                <a href="register.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg text-lg inline-block transition duration-300">Sign Up for Free</a>
            </div>
        </section>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</div>