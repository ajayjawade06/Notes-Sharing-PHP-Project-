<?php
// Include header
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Check if note ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = 'Note ID is required';
    redirect($base_url);
}

$note_id = (int)$_GET['id'];

// Get note details
$note = getNoteById($note_id);

// Check if note exists
if (!$note) {
    $_SESSION['error'] = 'Note not found';
    redirect($base_url);
}

// Increment view count
incrementViews($note_id);

// Get download count
$sql = "SELECT COUNT(*) as count FROM downloads WHERE note_id = $note_id";
$result = $conn->query($sql);
$download_count = 0;

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $download_count = $row['count'];
}

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

$iconColor = isset($colorClasses[$note['file_type']]) ? $colorClasses[$note['file_type']] : $colorClasses['default'];
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
                    <span class="text-sm font-medium text-gray-500">Note Details</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="max-w-4xl mx-auto fade-in">
        <!-- Note Header -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8 text-center">
            <div class="transform transition-transform duration-500 hover:scale-105">
                <i class="fas <?php echo getFileIcon($note['file_type']); ?> text-6xl <?php echo $iconColor; ?> mb-4"></i>
                <h1 class="text-3xl font-bold text-gray-800 mb-3"><?php echo htmlspecialchars($note['title']); ?></h1>
            </div>
            
            <div class="flex justify-center items-center space-x-6 text-gray-600">
                <div class="flex items-center">
                    <i class="fas fa-eye mr-2 text-blue-500"></i>
                    <span><?php echo $note['views']; ?> views</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-download mr-2 text-green-500"></i>
                    <span><?php echo $download_count; ?> downloads</span>
                </div>
            </div>
        </div>
        
        <!-- Note Content -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <!-- Note Information -->
            <div class="bg-white rounded-xl shadow-md p-6 h-full">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                    Note Information
                </h2>
                
                <div class="space-y-4">
                    <div class="flex items-center transition-transform duration-300 hover:translate-x-1">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-user <?php echo $iconColor; ?>"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Uploaded by</p>
                            <p class="text-base text-gray-900"><?php echo htmlspecialchars($note['uploader_name']); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center transition-transform duration-300 hover:translate-x-1">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-calendar-alt <?php echo $iconColor; ?>"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Upload Date</p>
                            <p class="text-base text-gray-900"><?php echo formatDate($note['created_at']); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center transition-transform duration-300 hover:translate-x-1">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas <?php echo getFileIcon($note['file_type']); ?> <?php echo $iconColor; ?>"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">File Type</p>
                            <p class="text-base text-gray-900"><?php echo strtoupper($note['file_type']); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center transition-transform duration-300 hover:translate-x-1">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-file <?php echo $iconColor; ?>"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">File Name</p>
                            <p class="text-base text-gray-900 break-all"><?php echo htmlspecialchars($note['file_name']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Description -->
            <div class="bg-white rounded-xl shadow-md p-6 h-full">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-align-left mr-2 text-blue-500"></i>
                    Description
                </h2>
                
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 overflow-hidden">
                    <p class="text-gray-700 whitespace-pre-line"><?php echo nl2br(htmlspecialchars($note['description'])); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Download Section -->
        <div class="text-center mb-8">
            <?php if (isLoggedIn()): ?>
                <a href="download.php?id=<?php echo $note['id']; ?>" class="inline-flex items-center px-8 py-4 bg-green-600 text-white rounded-lg shadow-md hover:bg-green-700 hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                    <i class="fas fa-download mr-3 text-xl"></i>
                    <span class="text-lg font-medium">Download Note</span>
                </a>
            <?php else: ?>
                <div class="bg-blue-50 border border-blue-100 rounded-xl shadow-md p-6 mb-6 max-w-xl mx-auto">
                    <div class="flex flex-col items-center">
                        <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mb-4">
                            <i class="fas fa-lock text-blue-500 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Authentication Required</h3>
                        <p class="text-gray-600 mb-6">You need to login or register to download this note.</p>
                        <div class="flex space-x-4">
                            <a href="login.php" class="inline-flex items-center px-5 py-2 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700 hover:shadow-md transition-all duration-300">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Login
                            </a>
                            <a href="register.php" class="inline-flex items-center px-5 py-2 bg-white text-blue-600 border border-blue-200 rounded-lg shadow-sm hover:bg-blue-50 hover:shadow-md transition-all duration-300">
                                <i class="fas fa-user-plus mr-2"></i>
                                Register
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Note Management -->
        <?php if (isLoggedIn() && $_SESSION['user_id'] == $note['user_id']): ?>
            <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 text-center">
                    <i class="fas fa-cog mr-2 text-gray-500"></i>
                    Note Management
                </h2>
                
                <div class="flex justify-center space-x-4">
                    <a href="edit.php?id=<?php echo $note['id']; ?>" class="inline-flex items-center px-5 py-2 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700 hover:shadow-md transition-all duration-300">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Note
                    </a>
                    <a href="delete.php?id=<?php echo $note['id']; ?>" class="inline-flex items-center px-5 py-2 bg-red-600 text-white rounded-lg shadow-sm hover:bg-red-700 hover:shadow-md transition-all duration-300 delete-btn">
                        <i class="fas fa-trash mr-2"></i>
                        Delete Note
                    </a>
                </div>
            </div>
        <?php endif; ?>
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