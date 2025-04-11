<?php
// Include header
require_once '../includes/header.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = 'You do not have permission to access the admin panel';
    redirect($base_url);
}

// Process user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $action = $_POST['action'];
        $user_id = (int)$_POST['user_id'];
        
        // Make sure admin can't delete themselves
        if ($action === 'delete' && $user_id === $_SESSION['user_id']) {
            $_SESSION['error'] = 'You cannot delete your own account';
        } else {
            switch ($action) {
                case 'delete':
                    $sql = "DELETE FROM users WHERE id = $user_id";
                    if ($conn->query($sql) === TRUE) {
                        $_SESSION['success'] = 'User deleted successfully';
                    } else {
                        $_SESSION['error'] = 'Error deleting user: ' . $conn->error;
                    }
                    break;
                    
                case 'make_admin':
                    $sql = "UPDATE users SET role = 'admin' WHERE id = $user_id";
                    if ($conn->query($sql) === TRUE) {
                        $_SESSION['success'] = 'User promoted to admin successfully';
                    } else {
                        $_SESSION['error'] = 'Error promoting user: ' . $conn->error;
                    }
                    break;
                    
                case 'remove_admin':
                    // Make sure admin can't remove their own admin status
                    if ($user_id === $_SESSION['user_id']) {
                        $_SESSION['error'] = 'You cannot remove your own admin status';
                    } else {
                        $sql = "UPDATE users SET role = 'user' WHERE id = $user_id";
                        if ($conn->query($sql) === TRUE) {
                            $_SESSION['success'] = 'Admin status removed successfully';
                        } else {
                            $_SESSION['error'] = 'Error removing admin status: ' . $conn->error;
                        }
                    }
                    break;
            }
        }
        
        // Redirect to refresh the page
        redirect("$base_url/admin/users.php");
    }
}

// Get all users
$sql = "SELECT u.*, COUNT(n.id) as note_count 
        FROM users u 
        LEFT JOIN notes n ON u.id = n.user_id 
        GROUP BY u.id 
        ORDER BY u.created_at DESC";
$result = $conn->query($sql);
?>

<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb Navigation -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="<?php echo $base_url; ?>" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors duration-200">
                    <i class="fas fa-home mr-2"></i>
                    Home
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                    <a href="index.php" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors duration-200">
                        Admin Dashboard
                    </a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                    <span class="text-sm font-medium text-gray-500">Manage Users</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 fade-in">
        <div class="mb-4 md:mb-0">
            <h1 class="text-3xl font-bold text-gray-800 mb-2 flex items-center">
                <i class="fas fa-users text-blue-600 mr-3"></i>Manage Users
            </h1>
            <p class="text-lg text-gray-600">View, edit, and manage user accounts</p>
        </div>
        <div>
            <a href="index.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8 fade-in">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($user = $result->fetch_assoc()) {
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $user['id']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($user['role'] === 'admin'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Admin
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    User
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $user['note_count']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatDate($user['created_at']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                <button @click="open = !open" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors duration-200">
                                    Actions
                                    <i class="fas fa-chevron-down ml-2 mt-1"></i>
                                </button>
                                
                                <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                                    <div class="py-1">
                                        <form action="users.php" method="POST">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <?php if ($user['role'] === 'admin'): ?>
                                                <input type="hidden" name="action" value="remove_admin">
                                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-50 transition-colors duration-200">
                                                    <i class="fas fa-user-minus mr-2"></i> Remove Admin
                                                </button>
                                            <?php else: ?>
                                                <input type="hidden" name="action" value="make_admin">
                                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-blue-700 hover:bg-blue-50 transition-colors duration-200">
                                                    <i class="fas fa-user-shield mr-2"></i> Make Admin
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                        
                                        <form action="users.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This will also delete all their notes and cannot be undone.');">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 transition-colors duration-200">
                                                <i class="fas fa-trash mr-2"></i> Delete User
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No users found</td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Back to Top Button -->
<button id="backToTop" class="fixed bottom-6 right-6 p-3 rounded-full bg-blue-600 text-white shadow-lg opacity-0 invisible transform translate-y-10 transition-all duration-300 hover:bg-blue-700 focus:outline-none">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Toast Container for Notifications -->
<div id="toastContainer" class="fixed bottom-4 right-4 z-50 flex flex-col space-y-4"></div>

<!-- Alpine.js for Dropdowns -->
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<?php
// Include footer
require_once '../includes/footer.php';
?>