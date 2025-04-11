<?php
// Include header
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = 'You must be logged in to view your notes';
    redirect("$base_url/login.php");
}

$user_id = $_SESSION['user_id'];

// Get user's notes
$sql = "SELECT n.*, 
        (SELECT COUNT(*) FROM downloads WHERE note_id = n.id) as download_count 
        FROM notes n 
        WHERE n.user_id = $user_id 
        ORDER BY n.created_at DESC";
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
                    <span class="text-sm font-medium text-gray-500">My Notes</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 fade-in">
        <div class="mb-4 md:mb-0">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">My Notes</h1>
            <p class="text-lg text-gray-600">Manage your uploaded notes and resources</p>
        </div>
        <div>
            <a href="upload.php" class="inline-flex items-center px-5 py-3 bg-green-600 text-white rounded-lg shadow-md hover:bg-green-700 hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                <i class="fas fa-upload mr-2"></i>
                Upload New Note
            </a>
        </div>
    </div>

    <!-- Notes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 fade-in stagger-children">
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $extension = getFileExtension($row['file_name']);
                $icon_class = getFileIcon($extension);
                
                // Define color classes based on file type
                $colorClasses = [
                    'pdf' => 'text-red-500',
                    'doc' => 'text-blue-500',
                    'docx' => 'text-blue-500',
                    'ppt' => 'text-orange-500',
                    'pptx' => 'text-orange-500',
                    'txt' => 'text-gray-500',
                    'default' => 'text-indigo-500'
                ];
                
                $iconColor = isset($colorClasses[$extension]) ? $colorClasses[$extension] : $colorClasses['default'];
        ?>
        <div class="note-card">
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 h-full transform hover:-translate-y-1 overflow-hidden">
                <div class="p-6 flex flex-col h-full">
                    <div class="text-center mb-4">
                        <i class="fas <?php echo $icon_class; ?> text-5xl <?php echo $iconColor; ?> mb-3 transform transition-transform duration-300 hover:scale-110"></i>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($row['title']); ?></h3>
                    </div>
                    
                    <p class="text-gray-600 mb-4 flex-grow"><?php echo htmlspecialchars(substr($row['description'], 0, 100)) . (strlen($row['description']) > 100 ? '...' : ''); ?></p>
                    
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center text-gray-500 text-sm">
                            <i class="fas fa-eye mr-1"></i>
                            <span><?php echo $row['views']; ?> views</span>
                        </div>
                        <div class="flex items-center text-gray-500 text-sm">
                            <i class="fas fa-download mr-1"></i>
                            <span><?php echo $row['download_count']; ?> downloads</span>
                        </div>
                    </div>
                    
                    <a href="view.php?id=<?php echo $row['id']; ?>" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-300">
                        <i class="fas fa-info-circle mr-2"></i> View Details
                    </a>
                </div>
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500"><?php echo formatDate($row['created_at']); ?></span>
                        <div class="flex space-x-2">
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="p-1.5 bg-blue-100 text-blue-600 rounded-md hover:bg-blue-200 transition-colors duration-300" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="p-1.5 bg-red-100 text-red-600 rounded-md hover:bg-red-200 transition-colors duration-300 delete-btn" title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
            }
        } else {
        ?>
        <div class="col-span-full">
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            You haven't uploaded any notes yet. 
                            <a href="upload.php" class="font-medium underline">Upload</a> your first note.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        }
        ?>
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
require_once 'includes/footer.php';
?>