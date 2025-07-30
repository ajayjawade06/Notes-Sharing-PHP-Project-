<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "notes_sharing";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define base URL
$base_url = "http://localhost/NotesSharing";

// Define upload directory
$upload_dir = "uploads/";

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

// Function to redirect
function redirect($url) {
    // Clean any output buffers before sending headers
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header("Location: $url");
    exit();
}

// Function to sanitize input
function sanitize($input) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($input));
}

// Function to display error message
function displayError($message) {
    $id = 'toast-' . time() . '-' . rand(1000, 9999);
    return "
    <div id='$id' class='mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded shadow-md transition-opacity duration-300 fade-in'>
        <div class='flex items-center'>
            <div class='flex-shrink-0'>
                <i class='fas fa-exclamation-circle text-red-500 text-xl'></i>
            </div>
            <div class='ml-3'>
                <p class='text-sm text-red-700'>$message</p>
            </div>
            <div class='ml-auto pl-3'>
                <div class='-mx-1.5 -my-1.5'>
                    <button type='button' onclick='this.parentNode.parentNode.parentNode.remove()' class='inline-flex rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none'>
                        <span class='sr-only'>Dismiss</span>
                        <i class='fas fa-times'></i>
                    </button>
                </div>
            </div>
        </div>
    </div>";
}

// Function to display success message
function displaySuccess($message) {
    $id = 'toast-' . time() . '-' . rand(1000, 9999);
    return "
    <div id='$id' class='mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded shadow-md transition-opacity duration-300 fade-in'>
        <div class='flex items-center'>
            <div class='flex-shrink-0'>
                <i class='fas fa-check-circle text-green-500 text-xl'></i>
            </div>
            <div class='ml-3'>
                <p class='text-sm text-green-700'>$message</p>
            </div>
            <div class='ml-auto pl-3'>
                <div class='-mx-1.5 -my-1.5'>
                    <button type='button' onclick='this.parentNode.parentNode.parentNode.remove()' class='inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none'>
                        <span class='sr-only'>Dismiss</span>
                        <i class='fas fa-times'></i>
                    </button>
                </div>
            </div>
        </div>
    </div>";
}
?>