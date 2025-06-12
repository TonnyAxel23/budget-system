<nav class="bg-blue-600 text-white shadow-lg">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <a href="dashboard.php" class="text-xl font-bold flex items-center">
            <img src="assets/images/logo.png" alt="Logo" class="h-8 mr-2">
            Budget Planner
        </a>
        
        <div class="flex items-center space-x-4">
            <?php if (isLoggedIn()): ?>
                <a href="dashboard.php" class="hover:bg-blue-500 px-3 py-2 rounded">Dashboard</a>
                <a href="transactions.php" class="hover:bg-blue-500 px-3 py-2 rounded">Transactions</a>
                <a href="reports.php" class="hover:bg-blue-500 px-3 py-2 rounded">Reports</a>
                <div class="relative group">
                    <button class="hover:bg-blue-500 px-3 py-2 rounded flex items-center">
                        Settings <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden group-hover:block">
                        <a href="settings.php" class="block px-4 py-2 text-gray-800 hover:bg-blue-100">Account</a>
                        <a href="categories.php" class="block px-4 py-2 text-gray-800 hover:bg-blue-100">Categories</a>
                        <a href="budgets.php" class="block px-4 py-2 text-gray-800 hover:bg-blue-100">Budgets</a>
                    </div>
                </div>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-2 rounded">Logout</a>
            <?php else: ?>
                <a href="login.php" class="hover:bg-blue-500 px-3 py-2 rounded">Login</a>
                <a href="register.php" class="bg-blue-700 hover:bg-blue-800 px-3 py-2 rounded">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>