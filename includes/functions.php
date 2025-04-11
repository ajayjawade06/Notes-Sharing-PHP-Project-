<?php
// Include config file if not already included
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/../config.php';
}

/**
 * Get user details by ID
 * 
 * @param int $user_id User ID
 * @return array|false User details or false if not found
 */
function getUserById($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    
    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Get note details by ID
 * 
 * @param int $note_id Note ID
 * @return array|false Note details or false if not found
 */
function getNoteById($note_id) {
    global $conn;
    $note_id = (int)$note_id;
    
    $sql = "SELECT n.*, u.name as uploader_name 
            FROM notes n 
            JOIN users u ON n.user_id = u.id 
            WHERE n.id = $note_id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Increment view count for a note
 * 
 * @param int $note_id Note ID
 * @return bool True if successful, false otherwise
 */
function incrementViews($note_id) {
    global $conn;
    $note_id = (int)$note_id;
    
    $sql = "UPDATE notes SET views = views + 1 WHERE id = $note_id";
    return $conn->query($sql);
}

/**
 * Check if user has downloaded a note
 * 
 * @param int $user_id User ID
 * @param int $note_id Note ID
 * @return bool True if downloaded, false otherwise
 */
function hasDownloaded($user_id, $note_id) {
    global $conn;
    $user_id = (int)$user_id;
    $note_id = (int)$note_id;
    
    $sql = "SELECT * FROM downloads WHERE user_id = $user_id AND note_id = $note_id";
    $result = $conn->query($sql);
    
    return ($result && $result->num_rows > 0);
}

/**
 * Record a download
 * 
 * @param int $user_id User ID
 * @param int $note_id Note ID
 * @return bool True if successful, false otherwise
 */
function recordDownload($user_id, $note_id) {
    global $conn;
    $user_id = (int)$user_id;
    $note_id = (int)$note_id;
    
    // Only record if not already downloaded
    if (!hasDownloaded($user_id, $note_id)) {
        $sql = "INSERT INTO downloads (user_id, note_id) VALUES ($user_id, $note_id)";
        return $conn->query($sql);
    }
    
    return true;
}

/**
 * Get file extension
 * 
 * @param string $filename Filename
 * @return string File extension
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file type is allowed
 * 
 * @param string $extension File extension
 * @return bool True if allowed, false otherwise
 */
function isAllowedFileType($extension) {
    $allowed_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'zip', 'rar'];
    return in_array(strtolower($extension), $allowed_types);
}

/**
 * Generate a unique filename
 * 
 * @param string $filename Original filename
 * @return string Unique filename
 */
function generateUniqueFilename($filename) {
    $extension = getFileExtension($filename);
    return uniqid() . '_' . time() . '.' . $extension;
}

/**
 * Get total notes count
 * 
 * @return int Total notes count
 */
function getTotalNotesCount() {
    global $conn;
    
    $sql = "SELECT COUNT(*) as count FROM notes";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    return 0;
}

/**
 * Get total users count
 * 
 * @return int Total users count
 */
function getTotalUsersCount() {
    global $conn;
    
    $sql = "SELECT COUNT(*) as count FROM users";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    return 0;
}

/**
 * Get total downloads count
 * 
 * @return int Total downloads count
 */
function getTotalDownloadsCount() {
    global $conn;
    
    $sql = "SELECT COUNT(*) as count FROM downloads";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    return 0;
}

/**
 * Format date
 * 
 * @param string $date Date string
 * @return string Formatted date
 */
function formatDate($date) {
    return date('F j, Y, g:i a', strtotime($date));
}

/**
 * Format date for CSV export (Excel-friendly format)
 * 
 * @param string $date Date string
 * @return string Formatted date for CSV in Excel-friendly format
 */
function formatDateForCSV($date) {
    return date('m/d/Y', strtotime($date));
}

/**
 * Get file icon based on extension
 * 
 * @param string $extension File extension
 * @return string Font Awesome icon class
 */
function getFileIcon($extension) {
    $extension = strtolower($extension);
    
    switch ($extension) {
        case 'pdf':
            return 'fa-file-pdf';
        case 'doc':
        case 'docx':
            return 'fa-file-word';
        case 'ppt':
        case 'pptx':
            return 'fa-file-powerpoint';
        case 'txt':
            return 'fa-file-alt';
        case 'zip':
        case 'rar':
            return 'fa-file-archive';
        default:
            return 'fa-file';
    }
}

/**
 * Send email
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email message (HTML)
 * @param string $from Sender email (optional)
 * @return bool True if successful, false otherwise
 */
function sendEmail($to, $subject, $message, $from = 'salunkhesakshii2263@gmail.com') {
    // SMTP configuration
    $smtp_host = 'smtp.gmail.com';
    $smtp_port = 587;
    $smtp_username = 'salunkhesakshii2263@gmail.com';
    $smtp_password = 'dipak2805'; // Actual password for email authentication
    
    // In a real application, you would use PHPMailer or similar library for SMTP authentication
    // Example with PHPMailer (commented out as we don't have the library installed):
    /*
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = 0;                      // Disable debug output
        $mail->isSMTP();                           // Send using SMTP
        $mail->Host       = $smtp_host;            // SMTP server
        $mail->SMTPAuth   = true;                  // Enable SMTP authentication
        $mail->Username   = $smtp_username;        // SMTP username
        $mail->Password   = $smtp_password;        // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port       = $smtp_port;            // TCP port to connect to
        
        // Recipients
        $mail->setFrom($from, 'Notes Sharing');
        $mail->addAddress($to);                    // Add a recipient
        
        // Content
        $mail->isHTML(true);                       // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $message;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
    */
    
    // For this demo, we'll just simulate sending an email
    // Headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Notes Sharing <$from>" . "\r\n";
    
    // Log the email (for demonstration purposes)
    $log = "Email would be sent using SMTP authentication:\n";
    $log .= "SMTP Host: $smtp_host\n";
    $log .= "SMTP Port: $smtp_port\n";
    $log .= "SMTP Username: $smtp_username\n";
    $log .= "SMTP Password: [HIDDEN]\n";
    $log .= "To: $to\n";
    $log .= "Subject: $subject\n";
    $log .= "Message: $message\n";
    $log .= "Headers: $headers\n";
    
    // In a real application with basic mail() function (not recommended for production):
    // return mail($to, $subject, $message, $headers);
    
    // For this demo, always return true
    return true;
}
?>