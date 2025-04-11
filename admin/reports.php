<?php
// Include necessary files
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = 'You do not have permission to access the admin panel';
    redirect($base_url);
}

// Get report type
$report_type = isset($_GET['type']) ? $_GET['type'] : 'notes';

// Get date range
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Prepare report data
$report_data = [];
$report_title = '';

switch ($report_type) {
    case 'notes':
        $report_title = 'Notes Report';
        $sql = "SELECT n.*, u.name as uploader_name, 
                (SELECT COUNT(*) FROM downloads WHERE note_id = n.id) as download_count 
                FROM notes n 
                JOIN users u ON n.user_id = u.id 
                WHERE DATE(n.created_at) BETWEEN '$start_date' AND '$end_date' 
                ORDER BY n.created_at DESC";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $report_data[] = $row;
            }
        }
        break;
        
    case 'users':
        $report_title = 'Users Report';
        $sql = "SELECT u.*, 
                (SELECT COUNT(*) FROM notes WHERE user_id = u.id) as note_count,
                (SELECT SUM(views) FROM notes WHERE user_id = u.id) as total_views,
                (SELECT COUNT(*) FROM downloads d JOIN notes n ON d.note_id = n.id WHERE n.user_id = u.id) as total_downloads
                FROM users u 
                WHERE DATE(u.created_at) BETWEEN '$start_date' AND '$end_date' 
                ORDER BY u.created_at DESC";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $report_data[] = $row;
            }
        }
        break;
        
    case 'downloads':
        $report_title = 'Downloads Report';
        $sql = "SELECT d.*, n.title as note_title, u.name as user_name 
                FROM downloads d 
                JOIN notes n ON d.note_id = n.id 
                JOIN users u ON d.user_id = u.id 
                WHERE DATE(d.downloaded_at) BETWEEN '$start_date' AND '$end_date' 
                ORDER BY d.downloaded_at DESC";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $report_data[] = $row;
            }
        }
        break;
        
    case 'views':
        $report_title = 'Views Report';
        $sql = "SELECT n.id, n.title, n.views, n.created_at, u.name as uploader_name 
                FROM notes n 
                JOIN users u ON n.user_id = u.id 
                WHERE DATE(n.created_at) BETWEEN '$start_date' AND '$end_date' 
                ORDER BY n.views DESC";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $report_data[] = $row;
            }
        }
        break;
}

// Generate CSV if requested
if (isset($_GET['format']) && $_GET['format'] === 'csv') {
    // Set headers before any output
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $report_type . '_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add CSV headers based on report type
    switch ($report_type) {
        case 'notes':
            fputcsv($output, ['ID', 'Title', 'Description', 'File Type', 'Uploader', 'Views', 'Downloads', 'Created At']);
            foreach ($report_data as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['title'],
                    $row['description'],
                    $row['file_type'],
                    $row['uploader_name'],
                    $row['views'],
                    $row['download_count'],
                    formatDateForCSV($row['created_at'])
                ]);
            }
            break;
            
        case 'users':
            fputcsv($output, ['ID', 'Name', 'Email', 'Role', 'Notes', 'Views', 'Downloads', 'Created At']);
            foreach ($report_data as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['name'],
                    $row['email'],
                    $row['role'],
                    $row['note_count'],
                    $row['total_views'] ?: 0,
                    $row['total_downloads'],
                    formatDateForCSV($row['created_at'])
                ]);
            }
            break;
            
        case 'downloads':
            fputcsv($output, ['ID', 'Note', 'User', 'Downloaded At']);
            foreach ($report_data as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['note_title'],
                    $row['user_name'],
                    formatDateForCSV($row['downloaded_at'])
                ]);
            }
            break;
            
        case 'views':
            fputcsv($output, ['ID', 'Title', 'Uploader', 'Views', 'Created At']);
            foreach ($report_data as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['title'],
                    $row['uploader_name'],
                    $row['views'],
                    formatDateForCSV($row['created_at'])
                ]);
            }
            break;
    }
    
    fclose($output);
    exit; // Make sure to exit to prevent any HTML output
}

// Include header (only if not generating CSV)
require_once '../includes/header.php';

// Function to get badge color based on file type
function getBadgeColor($fileType) {
    switch ($fileType) {
        case 'pdf':
            return 'bg-red-100 text-red-800';
        case 'doc':
        case 'docx':
            return 'bg-blue-100 text-blue-800';
        case 'ppt':
        case 'pptx':
            return 'bg-orange-100 text-orange-800';
        case 'xls':
        case 'xlsx':
            return 'bg-green-100 text-green-800';
        case 'txt':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-purple-100 text-purple-800';
    }
}
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
                    <span class="text-sm font-medium text-gray-500">Generate Reports</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 fade-in">
        <div class="mb-4 md:mb-0">
            <h1 class="text-3xl font-bold text-gray-800 mb-2 flex items-center">
                <i class="fas fa-chart-bar text-purple-600 mr-3"></i>Generate Reports
            </h1>
            <p class="text-lg text-gray-600">Generate and download reports on notes, users, downloads, and views</p>
        </div>
        <div>
            <a href="index.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Report Form -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8 fade-in">
        <div class="p-6">
            <form action="reports.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                    <select id="type" name="type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500 focus:ring-opacity-50 transition-colors duration-200">
                        <option value="notes" <?php echo $report_type === 'notes' ? 'selected' : ''; ?>>Notes Report</option>
                        <option value="users" <?php echo $report_type === 'users' ? 'selected' : ''; ?>>Users Report</option>
                        <option value="downloads" <?php echo $report_type === 'downloads' ? 'selected' : ''; ?>>Downloads Report</option>
                        <option value="views" <?php echo $report_type === 'views' ? 'selected' : ''; ?>>Views Report</option>
                    </select>
                </div>
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500 focus:ring-opacity-50 transition-colors duration-200">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500 focus:ring-opacity-50 transition-colors duration-200">
                </div>
                <div class="flex items-end space-x-3">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg shadow-md hover:bg-purple-700 hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Generate Report
                    </button>
                    <a href="reports.php?type=<?php echo $report_type; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&format=csv" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg shadow-md hover:bg-green-700 hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                        <i class="fas fa-download mr-2"></i>
                        Download CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8 fade-in">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                <?php echo $report_title; ?> 
                <span class="ml-2 text-sm font-normal text-gray-500">(<?php echo count($report_data); ?> records)</span>
            </h2>
        </div>
        <div class="overflow-x-auto">
            <?php if ($report_type === 'notes'): ?>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploader</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Downloads</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        if (!empty($report_data)) {
                            foreach ($report_data as $note) {
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $note['id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="../view.php?id=<?php echo $note['id']; ?>" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors duration-200">
                                    <?php echo htmlspecialchars($note['title']); ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo getBadgeColor($note['file_type']); ?>">
                                    <i class="fas <?php echo getFileIcon($note['file_type']); ?> mr-1"></i>
                                    <?php echo strtoupper($note['file_type']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($note['uploader_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 flex items-center">
                                    <i class="fas fa-eye text-blue-400 mr-1"></i>
                                    <?php echo $note['views']; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 flex items-center">
                                    <i class="fas fa-download text-green-400 mr-1"></i>
                                    <?php echo $note['download_count']; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatDate($note['created_at']); ?></td>
                        </tr>
                        <?php
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No data available</td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            <?php elseif ($report_type === 'users'): ?>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Downloads</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        if (!empty($report_data)) {
                            foreach ($report_data as $user) {
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 flex items-center">
                                    <i class="fas fa-file-alt text-green-400 mr-1"></i>
                                    <?php echo $user['note_count']; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 flex items-center">
                                    <i class="fas fa-eye text-blue-400 mr-1"></i>
                                    <?php echo $user['total_views'] ?: 0; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 flex items-center">
                                    <i class="fas fa-download text-green-400 mr-1"></i>
                                    <?php echo $user['total_downloads']; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatDate($user['created_at']); ?></td>
                        </tr>
                        <?php
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No data available</td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            <?php elseif ($report_type === 'downloads'): ?>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Downloaded At</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        if (!empty($report_data)) {
                            foreach ($report_data as $download) {
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $download['id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="../view.php?id=<?php echo $download['note_id']; ?>" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors duration-200">
                                    <?php echo htmlspecialchars($download['note_title']); ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($download['user_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatDate($download['downloaded_at']); ?></td>
                        </tr>
                        <?php
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No data available</td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            <?php elseif ($report_type === 'views'): ?>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploader</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        if (!empty($report_data)) {
                            foreach ($report_data as $note) {
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $note['id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="../view.php?id=<?php echo $note['id']; ?>" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors duration-200">
                                    <?php echo htmlspecialchars($note['title']); ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($note['uploader_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 flex items-center">
                                    <i class="fas fa-eye text-blue-400 mr-1"></i>
                                    <?php echo $note['views']; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatDate($note['created_at']); ?></td>
                        </tr>
                        <?php
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No data available</td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            <?php endif; ?>
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