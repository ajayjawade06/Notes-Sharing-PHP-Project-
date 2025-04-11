<?php
// Include header
require_once '../includes/header.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = 'You do not have permission to access the admin panel';
    redirect($base_url);
}

// Process note actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['note_id'])) {
        $action = $_POST['action'];
        $note_id = (int)$_POST['note_id'];
        
        switch ($action) {
            case 'delete':
                // Get note details
                $note = getNoteById($note_id);
                
                if ($note) {
                    // Delete note
                    $sql = "DELETE FROM notes WHERE id = $note_id";
                    
                    if ($conn->query($sql) === TRUE) {
                        // Delete file
                        if (file_exists($note['file_path'])) {
                            unlink($note['file_path']);
                        }
                        
                        $_SESSION['success'] = 'Note deleted successfully';
                    } else {
                        $_SESSION['error'] = 'Error deleting note: ' . $conn->error;
                    }
                } else {
                    $_SESSION['error'] = 'Note not found';
                }
                break;
        }
        
        // Redirect to refresh the page
        redirect("$base_url/admin/notes.php");
    }
}

// Get all notes
$sql = "SELECT n.*, u.name as uploader_name, 
        (SELECT COUNT(*) FROM downloads WHERE note_id = n.id) as download_count 
        FROM notes n 
        JOIN users u ON n.user_id = u.id 
        ORDER BY n.created_at DESC";
$result = $conn->query($sql);

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
                    <span class="text-sm font-medium text-gray-500">Manage Notes</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 fade-in">
        <div class="mb-4 md:mb-0">
            <h1 class="text-3xl font-bold text-gray-800 mb-2 flex items-center">
                <i class="fas fa-file-alt text-green-600 mr-3"></i>Manage Notes
            </h1>
            <p class="text-lg text-gray-600">View and manage all uploaded notes</p>
        </div>
        <div>
            <a href="index.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Notes Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8 fade-in">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploader</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Downloads</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($note = $result->fetch_assoc()) {
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="../view.php?id=<?php echo $note['id']; ?>" class="inline-flex items-center p-1.5 text-blue-600 bg-blue-100 rounded-md hover:bg-blue-200 transition-colors duration-200" title="View Note">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="../download.php?id=<?php echo $note['id']; ?>" class="inline-flex items-center p-1.5 text-green-600 bg-green-100 rounded-md hover:bg-green-200 transition-colors duration-200" title="Download Note">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form action="notes.php" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this note? This action cannot be undone.');">
                                    <input type="hidden" name="note_id" value="<?php echo $note['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="inline-flex items-center p-1.5 text-red-600 bg-red-100 rounded-md hover:bg-red-200 transition-colors duration-200" title="Delete Note">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No notes found</td>
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

<?php
// Include footer
require_once '../includes/footer.php';
?>