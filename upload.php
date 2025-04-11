<?php
// Include header
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = 'You must be logged in to upload notes';
    redirect("$base_url/login.php");
}

// Process upload form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $error = '';
    
    // Validate input
    if (empty($title)) {
        $error = 'Please enter a title for your note';
    } elseif (!isset($_FILES['note_file']) || $_FILES['note_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a file to upload';
    } else {
        $file = $_FILES['note_file'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_error = $file['error'];
        
        // Get file extension
        $file_ext = getFileExtension($file_name);
        
        // Check if file type is allowed
        if (!isAllowedFileType($file_ext)) {
            $error = 'File type not allowed. Allowed types: PDF, DOC, DOCX, PPT, PPTX, TXT, ZIP, RAR';
        } elseif ($file_size > 10485760) { // 10MB
            $error = 'File size too large. Maximum size: 10MB';
        } else {
            // Generate unique filename
            $new_file_name = generateUniqueFilename($file_name);
            $upload_path = $upload_dir . $new_file_name;
            
            // Upload file
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Insert note into database
                $user_id = $_SESSION['user_id'];
                $sql = "INSERT INTO notes (user_id, title, description, file_name, file_type, file_path) 
                        VALUES ($user_id, '$title', '$description', '$file_name', '$file_ext', '$upload_path')";
                
                if ($conn->query($sql) === TRUE) {
                    $_SESSION['success'] = 'Note uploaded successfully';
                    redirect($base_url);
                } else {
                    $error = 'Error: ' . $conn->error;
                    // Delete uploaded file if database insertion fails
                    unlink($upload_path);
                }
            } else {
                $error = 'Error uploading file';
            }
        }
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto fade-in">
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
                        <span class="text-sm font-medium text-gray-500">Upload Note</span>
                    </div>
                </li>
            </ol>
        </nav>
        
        <!-- Upload Form -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                    <i class="fas fa-upload text-blue-600 text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Upload Note</h1>
                <p class="text-gray-600 mt-2">Share your knowledge with others</p>
            </div>
            
            <?php if (isset($error) && !empty($error)): ?>
                <?php echo displayError($error); ?>
            <?php endif; ?>
            
            <form action="upload.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <!-- Title Field -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" id="title" name="title" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                        placeholder="Enter a descriptive title for your note">
                </div>
                
                <!-- Description Field -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                        placeholder="Provide a detailed description of your note"></textarea>
                </div>
                
                <!-- File Upload Area -->
                <div>
                    <label for="note_file" class="block text-sm font-medium text-gray-700 mb-1">File</label>
                    <div class="file-drop-area border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-500 transition-colors duration-300">
                        <div class="file-drop-icon mb-4">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400"></i>
                        </div>
                        <div class="file-msg mb-2 text-gray-600">Drag & drop file or click to browse</div>
                        <div class="selected-file text-sm text-blue-600 hidden"></div>
                        <input type="file" class="file-input absolute inset-0 w-full h-full opacity-0 cursor-pointer" id="note_file" name="note_file" required>
                    </div>
                    
                    <!-- File Information -->
                    <div class="mt-2 text-xs text-gray-500 space-y-1">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle mr-1 text-blue-500"></i>
                            <span>Allowed file types: PDF, DOC, DOCX, PPT, PPTX, TXT, ZIP, RAR</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-weight-hanging mr-1 text-blue-500"></i>
                            <span>Maximum file size: 10MB</span>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                        <i class="fas fa-upload mr-2"></i>
                        <span>Upload Note</span>
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

<script>
    // File upload handling
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.querySelector('.file-input');
        const fileDropArea = document.querySelector('.file-drop-area');
        const fileMsg = document.querySelector('.file-msg');
        const selectedFile = document.querySelector('.selected-file');
        const fileIcon = document.querySelector('.file-drop-icon i');
        
        // Handle file selection
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const fileName = this.files[0].name;
                selectedFile.textContent = fileName;
                selectedFile.classList.remove('hidden');
                fileMsg.textContent = 'File selected:';
                fileIcon.classList.remove('text-gray-400');
                fileIcon.classList.add('text-blue-500');
            }
        });
        
        // Handle drag events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileDropArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        // Handle drop area highlighting
        ['dragenter', 'dragover'].forEach(eventName => {
            fileDropArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            fileDropArea.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            fileDropArea.classList.add('border-blue-500', 'bg-blue-50');
        }
        
        function unhighlight() {
            fileDropArea.classList.remove('border-blue-500', 'bg-blue-50');
        }
        
        // Handle file drop
        fileDropArea.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            
            if (files && files[0]) {
                const fileName = files[0].name;
                selectedFile.textContent = fileName;
                selectedFile.classList.remove('hidden');
                fileMsg.textContent = 'File selected:';
                fileIcon.classList.remove('text-gray-400');
                fileIcon.classList.add('text-blue-500');
            }
        }
    });
</script>

<?php
// Include footer
require_once 'includes/footer.php';
?>