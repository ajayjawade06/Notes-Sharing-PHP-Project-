<?php
// Include header
require_once '../includes/header.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = 'You do not have permission to access the admin panel';
    redirect($base_url);
}

// Get statistics
$total_users = getTotalUsersCount();
$total_notes = getTotalNotesCount();
$total_downloads = getTotalDownloadsCount();

// Get recent notes
$sql = "SELECT n.*, u.name as uploader_name 
        FROM notes n 
        JOIN users u ON n.user_id = u.id 
        ORDER BY n.created_at DESC 
        LIMIT 5";
$recent_notes = $conn->query($sql);

// Get top viewed notes
$sql = "SELECT n.*, u.name as uploader_name 
        FROM notes n 
        JOIN users u ON n.user_id = u.id 
        ORDER BY n.views DESC 
        LIMIT 5";
$top_viewed = $conn->query($sql);

// Get top downloaded notes
$sql = "SELECT n.*, u.name as uploader_name, 
        (SELECT COUNT(*) FROM downloads WHERE note_id = n.id) as download_count 
        FROM notes n 
        JOIN users u ON n.user_id = u.id 
        ORDER BY download_count DESC 
        LIMIT 5";
$top_downloaded = $conn->query($sql);

// Get active users
$sql = "SELECT u.*, COUNT(n.id) as note_count 
        FROM users u 
        LEFT JOIN notes n ON u.id = n.user_id 
        GROUP BY u.id 
        ORDER BY note_count DESC 
        LIMIT 5";
$active_users = $conn->query($sql);
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
                    <span class="text-sm font-medium text-gray-500">Admin Dashboard</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="mb-8 fade-in">
        <h1 class="text-3xl font-bold text-gray-800 mb-2 flex items-center">
            <i class="fas fa-tachometer-alt text-blue-600 mr-3"></i>Admin Dashboard
        </h1>
        <p class="text-lg text-gray-600">Welcome to the admin panel. Here you can manage users, notes, and view statistics.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 fade-in stagger-children">
        <!-- Total Users -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden transform hover:-translate-y-1 transition-all duration-300 hover:shadow-lg">
            <div class="p-6 bg-gradient-to-r from-blue-500 to-blue-600">
                <div class="flex justify-between items-center">
                    <div class="bg-white bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-users text-white text-2xl"></i>
                    </div>
                    <div class="text-right">
                        <h3 class="text-3xl font-bold text-white"><?php echo $total_users; ?></h3>
                        <p class="text-blue-100">Total Users</p>
                    </div>
                </div>
            </div>
            <div class="bg-blue-50 px-6 py-2">
                <a href="users.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center justify-end">
                    <span>View Details</span>
                    <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- Total Notes -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden transform hover:-translate-y-1 transition-all duration-300 hover:shadow-lg">
            <div class="p-6 bg-gradient-to-r from-green-500 to-green-600">
                <div class="flex justify-between items-center">
                    <div class="bg-white bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-file-alt text-white text-2xl"></i>
                    </div>
                    <div class="text-right">
                        <h3 class="text-3xl font-bold text-white"><?php echo $total_notes; ?></h3>
                        <p class="text-green-100">Total Notes</p>
                    </div>
                </div>
            </div>
            <div class="bg-green-50 px-6 py-2">
                <a href="notes.php" class="text-green-600 hover:text-green-800 text-sm font-medium flex items-center justify-end">
                    <span>View Details</span>
                    <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- Total Views -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden transform hover:-translate-y-1 transition-all duration-300 hover:shadow-lg">
            <div class="p-6 bg-gradient-to-r from-yellow-500 to-yellow-600">
                <div class="flex justify-between items-center">
                    <div class="bg-white bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-eye text-white text-2xl"></i>
                    </div>
                    <div class="text-right">
                        <h3 class="text-3xl font-bold text-white"><?php echo $total_notes > 0 ? round($conn->query("SELECT SUM(views) as total FROM notes")->fetch_assoc()['total']) : 0; ?></h3>
                        <p class="text-yellow-100">Total Views</p>
                    </div>
                </div>
            </div>
            <div class="bg-yellow-50 px-6 py-2">
                <a href="reports.php" class="text-yellow-600 hover:text-yellow-800 text-sm font-medium flex items-center justify-end">
                    <span>View Details</span>
                    <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- Total Downloads -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden transform hover:-translate-y-1 transition-all duration-300 hover:shadow-lg">
            <div class="p-6 bg-gradient-to-r from-purple-500 to-purple-600">
                <div class="flex justify-between items-center">
                    <div class="bg-white bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-download text-white text-2xl"></i>
                    </div>
                    <div class="text-right">
                        <h3 class="text-3xl font-bold text-white"><?php echo $total_downloads; ?></h3>
                        <p class="text-purple-100">Total Downloads</p>
                    </div>
                </div>
            </div>
            <div class="bg-purple-50 px-6 py-2">
                <a href="reports.php" class="text-purple-600 hover:text-purple-800 text-sm font-medium flex items-center justify-end">
                    <span>View Details</span>
                    <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Admin Links -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-8 fade-in">
        <div class="flex flex-col md:flex-row justify-around gap-4">
            <a href="users.php" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                <i class="fas fa-users mr-2"></i>
                Manage Users
            </a>
            <a href="notes.php" class="inline-flex items-center justify-center px-6 py-3 bg-green-600 text-white rounded-lg shadow-md hover:bg-green-700 hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                <i class="fas fa-file-alt mr-2"></i>
                Manage Notes
            </a>
            <a href="reports.php" class="inline-flex items-center justify-center px-6 py-3 bg-yellow-600 text-white rounded-lg shadow-md hover:bg-yellow-700 hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                <i class="fas fa-chart-bar mr-2"></i>
                Generate Reports
            </a>
        </div>
    </div>

    <!-- Data Panels -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 fade-in">
        <!-- Recent Notes -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-blue-600 text-white px-6 py-4">
                <h5 class="font-semibold flex items-center">
                    <i class="fas fa-clock mr-2"></i>Recent Notes
                </h5>
            </div>
            <div class="divide-y divide-gray-200">
                <?php
                if ($recent_notes && $recent_notes->num_rows > 0) {
                    while ($note = $recent_notes->fetch_assoc()) {
                ?>
                <a href="../view.php?id=<?php echo $note['id']; ?>" class="block hover:bg-gray-50 transition-colors duration-200">
                    <div class="px-6 py-4">
                        <div class="flex justify-between items-center">
                            <h6 class="font-medium text-gray-800"><?php echo htmlspecialchars($note['title']); ?></h6>
                            <span class="text-xs text-gray-500"><?php echo formatDate($note['created_at']); ?></span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">
                            Uploaded by <?php echo htmlspecialchars($note['uploader_name']); ?>
                        </p>
                    </div>
                </a>
                <?php
                    }
                } else {
                    echo '<div class="px-6 py-4 text-center text-gray-500">No notes available</div>';
                }
                ?>
            </div>
        </div>
        
        <!-- Top Viewed Notes -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-green-600 text-white px-6 py-4">
                <h5 class="font-semibold flex items-center">
                    <i class="fas fa-eye mr-2"></i>Top Viewed Notes
                </h5>
            </div>
            <div class="divide-y divide-gray-200">
                <?php
                if ($top_viewed && $top_viewed->num_rows > 0) {
                    while ($note = $top_viewed->fetch_assoc()) {
                ?>
                <a href="../view.php?id=<?php echo $note['id']; ?>" class="block hover:bg-gray-50 transition-colors duration-200">
                    <div class="px-6 py-4">
                        <div class="flex justify-between items-center">
                            <h6 class="font-medium text-gray-800"><?php echo htmlspecialchars($note['title']); ?></h6>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <?php echo $note['views']; ?> views
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">
                            Uploaded by <?php echo htmlspecialchars($note['uploader_name']); ?>
                        </p>
                    </div>
                </a>
                <?php
                    }
                } else {
                    echo '<div class="px-6 py-4 text-center text-gray-500">No notes available</div>';
                }
                ?>
            </div>
        </div>
        
        <!-- Top Downloaded Notes -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-yellow-600 text-white px-6 py-4">
                <h5 class="font-semibold flex items-center">
                    <i class="fas fa-download mr-2"></i>Top Downloaded Notes
                </h5>
            </div>
            <div class="divide-y divide-gray-200">
                <?php
                if ($top_downloaded && $top_downloaded->num_rows > 0) {
                    while ($note = $top_downloaded->fetch_assoc()) {
                ?>
                <a href="../view.php?id=<?php echo $note['id']; ?>" class="block hover:bg-gray-50 transition-colors duration-200">
                    <div class="px-6 py-4">
                        <div class="flex justify-between items-center">
                            <h6 class="font-medium text-gray-800"><?php echo htmlspecialchars($note['title']); ?></h6>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <?php echo $note['download_count']; ?> downloads
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">
                            Uploaded by <?php echo htmlspecialchars($note['uploader_name']); ?>
                        </p>
                    </div>
                </a>
                <?php
                    }
                } else {
                    echo '<div class="px-6 py-4 text-center text-gray-500">No notes available</div>';
                }
                ?>
            </div>
        </div>
        
        <!-- Active Users -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-purple-600 text-white px-6 py-4">
                <h5 class="font-semibold flex items-center">
                    <i class="fas fa-user-check mr-2"></i>Active Users
                </h5>
            </div>
            <div class="divide-y divide-gray-200">
                <?php
                if ($active_users && $active_users->num_rows > 0) {
                    while ($user = $active_users->fetch_assoc()) {
                ?>
                <div class="px-6 py-4">
                    <div class="flex justify-between items-center">
                        <h6 class="font-medium text-gray-800"><?php echo htmlspecialchars($user['name']); ?></h6>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            <?php echo $user['note_count']; ?> notes
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">
                        <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                </div>
                <?php
                    }
                } else {
                    echo '<div class="px-6 py-4 text-center text-gray-500">No users available</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Back to Top Button -->
<button id="backToTop" class="fixed bottom-6 right-6 p-3 rounded-full bg-blue-600 text-white shadow-lg opacity-0 invisible transform translate-y-10 transition-all duration-300 hover:bg-blue-700 focus:outline-none">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Toast Container for Notifications -->
<div id="toastContainer" class="fixed bottom-4 right-4 z-50 flex flex-col space-y-4"></div>

<?php
// Include footer
require_once '../includes/footer.php';
?>