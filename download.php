<?php
// Start output buffering
ob_start();

// Include config file
require_once 'config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = 'You must be logged in to download notes';
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

// Record download
recordDownload($user_id, $note_id);

// Get file path
$file_path = $note['file_path'];

// Check if file exists
if (!file_exists($file_path)) {
    $_SESSION['error'] = 'File not found';
    redirect($base_url);
}

// Get file extension
$file_extension = strtolower(pathinfo($note['file_name'], PATHINFO_EXTENSION));

// Set appropriate content type based on file extension
$content_type = 'application/octet-stream'; // Default
$disposition = 'attachment'; // Default to download

switch ($file_extension) {
    case 'pdf':
        $content_type = 'application/pdf';
        $disposition = 'inline'; // View in browser
        break;
    case 'doc':
        $content_type = 'application/msword';
        // Word files typically download rather than display inline
        break;
    case 'docx':
        $content_type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        // Word files typically download rather than display inline
        break;
    case 'ppt':
        $content_type = 'application/vnd.ms-powerpoint';
        // PowerPoint files typically download rather than display inline
        break;
    case 'pptx':
        $content_type = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
        // PowerPoint files typically download rather than display inline
        break;
    case 'txt':
        $content_type = 'text/plain';
        $disposition = 'inline'; // View in browser
        break;
    // Add more file types as needed
}

// Set headers for download or inline viewing
header('Content-Description: File Transfer');
header('Content-Type: ' . $content_type);
header('Content-Disposition: ' . $disposition . '; filename="' . basename($note['file_name']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

// Clear output buffer
ob_clean();
flush();

// Read file and output to browser
readfile($file_path);
exit;
?>