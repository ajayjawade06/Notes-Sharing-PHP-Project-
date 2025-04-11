<?php
// Include config file
require_once 'config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = 'You must be logged in to delete notes';
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

// Check if user is the owner of the note or an admin
if ($note['user_id'] != $user_id && !isAdmin()) {
    $_SESSION['error'] = 'You do not have permission to delete this note';
    redirect($base_url);
}

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

// Redirect to my notes page
redirect("$base_url/my_notes.php");
?>