<?php
// Include header
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Get all notes
$sql = "SELECT n.*, u.name as uploader_name, 
        (SELECT COUNT(*) FROM downloads WHERE note_id = n.id) as download_count 
        FROM notes n 
        JOIN users u ON n.user_id = u.id 
        ORDER BY n.created_at DESC";
$result = $conn->query($sql);
?>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 md:py-24 relative overflow-hidden">
    <!-- Background pattern -->
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
            <path d="M0,0 L100,0 L100,100 L0,100 Z" fill="url(#grid-pattern)"></path>
        </svg>
        <defs>
            <pattern id="grid-pattern" width="10" height="10" patternUnits="userSpaceOnUse">
                <path d="M0,0 L10,0 L10,10 L0,10 Z" fill="none" stroke="currentColor" stroke-width="0.5"></path>
            </pattern>
        </defs>
    </div>
    
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center max-w-3xl mx-auto fade-in stagger-children">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-shadow">Welcome to Notes Sharing Platform</h1>
            <p class="text-xl md:text-2xl mb-8 opacity-90">A place to discover, share, and download high-quality educational notes and resources</p>
            <div class="flex flex-wrap justify-center gap-4">
                <?php if (!isLoggedIn()): ?>
                    <a href="login.php" class="inline-flex items-center px-6 py-3 bg-white text-blue-700 rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition duration-300">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login
                    </a>
                    <a href="register.php" class="inline-flex items-center px-6 py-3 bg-transparent border-2 border-white text-white rounded-lg hover:bg-white hover:bg-opacity-10 transform hover:-translate-y-1 transition duration-300">
                        <i class="fas fa-user-plus mr-2"></i> Register
                    </a>
                <?php else: ?>
                    <a href="upload.php" class="inline-flex items-center px-6 py-3 bg-white text-blue-700 rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition duration-300">
                        <i class="fas fa-upload mr-2"></i> Upload Notes
                    </a>
                    <a href="my_notes.php" class="inline-flex items-center px-6 py-3 bg-transparent border-2 border-white text-white rounded-lg hover:bg-white hover:bg-opacity-10 transform hover:-translate-y-1 transition duration-300">
                        <i class="fas fa-book mr-2"></i> My Notes
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Decorative elements -->
    <div class="absolute bottom-0 left-0 w-full h-16 bg-gradient-to-t from-white opacity-10"></div>
</div>

<!-- Search and Filter Section -->
<div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div>
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <span class="text-gray-700 hover:text-blue-600 text-sm font-medium">Home</span>
                    </li>
                </ol>
            </nav>
        </div>
        <div>
            <div class="relative">
                <input type="text" class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300" placeholder="Search notes..." id="searchInput">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Filter -->
    <div class="mb-8">
        <div class="flex flex-wrap gap-2">
            <button class="px-4 py-2 rounded-lg bg-blue-600 text-white shadow-sm hover:shadow-md transition-all duration-300 category-filter" data-category="all">All</button>
            <button class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 shadow-sm hover:shadow-md hover:bg-gray-200 transition-all duration-300 category-filter" data-category="pdf">PDF</button>
            <button class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 shadow-sm hover:shadow-md hover:bg-gray-200 transition-all duration-300 category-filter" data-category="doc">Word</button>
            <button class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 shadow-sm hover:shadow-md hover:bg-gray-200 transition-all duration-300 category-filter" data-category="ppt">PowerPoint</button>
            <button class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 shadow-sm hover:shadow-md hover:bg-gray-200 transition-all duration-300 category-filter" data-category="txt">Text</button>
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
        <div class="note-card" data-category="<?php echo $extension; ?>">
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
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>By <?php echo htmlspecialchars($row['uploader_name']); ?></span>
                        <span><?php echo formatDate($row['created_at']); ?></span>
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
                            No notes available yet. 
                            <?php if (!isLoggedIn()): ?>
                                <a href="login.php" class="font-medium underline">Login</a> or 
                                <a href="register.php" class="font-medium underline">Register</a> to upload notes.
                            <?php else: ?>
                                <a href="upload.php" class="font-medium underline">Upload</a> your first note.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        }
        ?>
    </div>

    <?php if (isLoggedIn()): ?>
    <div class="mt-12 mb-8 text-center">
        <a href="upload.php" class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg shadow-md hover:bg-green-700 hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
            <i class="fas fa-upload mr-2"></i> Upload New Note
        </a>
    </div>
    <?php endif; ?>
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