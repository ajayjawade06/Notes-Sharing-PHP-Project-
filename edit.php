<?php
// Include header
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = 'You must be logged in to edit notes';
    redirect("$base_url/login.php");
}

// Check if note ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = 'Note ID is required';
    redirect($base_url);
}

$note_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get note details
$note = getNoteById($note_id);

// Check if note exists
if (!$note) {
    $_SESSION['error'] = 'Note not found';
    redirect($base_url);
}

// Check if user is the owner of the note
if ($note['user_id'] != $user_id && !isAdmin()) {
    $_SESSION['error'] = 'You do not have permission to edit this note';
    redirect($base_url);
}

// Process edit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $error = '';
    
    // Validate input
    if (empty($title)) {
        $error = 'Please enter a title for your note';
    } else {
        // Update note
        $sql = "UPDATE notes SET title = '$title', description = '$description', updated_at = CURRENT_TIMESTAMP WHERE id = $note_id";
        
        if ($conn->query($sql) === TRUE) {
            $_SESSION['success'] = 'Note updated successfully';
            redirect("$base_url/view.php?id=$note_id");
        } else {
            $error = 'Error: ' . $conn->error;
        }
    }
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
                    <a href="view.php?id=<?php echo $note_id; ?>" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors duration-200">
                        Note Details
                    </a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                    <span class="text-sm font-medium text-gray-500">Edit Note</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="max-w-2xl mx-auto fade-in">
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                    <i class="fas fa-edit text-blue-600 text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Note</h1>
                <p class="text-gray-600 mt-2">Update your note information</p>
            </div>
            
            <?php if (isset($error) && !empty($error)): ?>
                <?php echo displayError($error); ?>
            <?php endif; ?>
            
            <form action="edit.php?id=<?php echo $note_id; ?>" method="POST" class="space-y-6">
                <!-- Title Field -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($note['title']); ?>" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                </div>
                
                <!-- Description Field -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"><?php echo htmlspecialchars($note['description']); ?></textarea>
                </div>
                
                <!-- File Information -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File</label>
                    <div class="flex">
                        <div class="flex items-center justify-center w-10 h-10 rounded-l-lg bg-gray-100 border border-r-0 border-gray-300">
                            <i class="fas <?php echo getFileIcon($note['file_type']); ?> <?php echo $iconColor; ?>"></i>
                        </div>
                        <input type="text" value="<?php echo htmlspecialchars($note['file_name']); ?>" readonly
                            class="flex-grow px-4 py-2 bg-gray-100 border border-gray-300 rounded-r-lg text-gray-500">
                    </div>
                    <p class="mt-1 text-xs text-gray-500 flex items-center">
                        <i class="fas fa-info-circle mr-1"></i>
                        To change the file, you need to delete this note and upload a new one.
                    </p>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex justify-between pt-4">
                    <a href="view.php?id=<?php echo $note_id; ?>" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 hover:shadow transition-all duration-300">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </a>
                    <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                        <i class="fas fa-save mr-2"></i>
                        Update Note
                    </button>
                </div>
            </form>
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
require_once 'includes/footer.php';
?>