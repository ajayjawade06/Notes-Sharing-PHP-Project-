<?php
// Include header
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = 'You must be logged in to view your profile';
    redirect("$base_url/login.php");
}

$user_id = $_SESSION['user_id'];

// Get user details
$user = getUserById($user_id);

// Check if user exists
if (!$user) {
    $_SESSION['error'] = 'User not found';
    redirect($base_url);
}

// Get user stats
$sql = "SELECT COUNT(*) as note_count FROM notes WHERE user_id = $user_id";
$result = $conn->query($sql);
$note_count = 0;

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $note_count = $row['note_count'];
}

$sql = "SELECT SUM(views) as total_views FROM notes WHERE user_id = $user_id";
$result = $conn->query($sql);
$total_views = 0;

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_views = $row['total_views'] ?: 0;
}

$sql = "SELECT COUNT(*) as download_count FROM downloads d 
        JOIN notes n ON d.note_id = n.id 
        WHERE n.user_id = $user_id";
$result = $conn->query($sql);
$download_count = 0;

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $download_count = $row['download_count'];
}

// Process profile update form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $error = '';
    $success = '';
    
    // Validate input
    if (empty($name)) {
        $error = 'Please enter your name';
    } elseif (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        // Password change requested
        if (empty($current_password)) {
            $error = 'Please enter your current password';
        } elseif (empty($new_password) || empty($confirm_password)) {
            $error = 'Please enter both new password and confirm password';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters long';
        } elseif (!password_verify($current_password, $user['password'])) {
            $error = 'Current password is incorrect';
        } else {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update user
            $sql = "UPDATE users SET name = '$name', password = '$hashed_password' WHERE id = $user_id";
            
            if ($conn->query($sql) === TRUE) {
                $_SESSION['name'] = $name;
                $success = 'Profile updated successfully';
            } else {
                $error = 'Error updating profile: ' . $conn->error;
            }
        }
    } else {
        // Only name update
        $sql = "UPDATE users SET name = '$name' WHERE id = $user_id";
        
        if ($conn->query($sql) === TRUE) {
            $_SESSION['name'] = $name;
            $success = 'Profile updated successfully';
        } else {
            $error = 'Error updating profile: ' . $conn->error;
        }
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
                    <span class="text-sm font-medium text-gray-500">Profile</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Profile Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-xl shadow-md p-6 mb-8 fade-in">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="mb-4 md:mb-0">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-user-circle text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($user['name']); ?></h1>
                        <p class="text-blue-100"><?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="text-xs text-blue-200 mt-1">Member since <?php echo formatDate($user['created_at']); ?></p>
                    </div>
                </div>
            </div>
            <div>
                <a href="my_notes.php" class="inline-flex items-center px-4 py-2 bg-white text-blue-600 rounded-lg shadow-sm hover:bg-blue-50 transform hover:-translate-y-1 transition-all duration-300">
                    <i class="fas fa-file-alt mr-2"></i>
                    My Notes
                </a>
            </div>
        </div>
        
        <!-- Profile Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="bg-white bg-opacity-10 rounded-lg p-4 text-center hover:bg-opacity-20 transition-all duration-300 transform hover:-translate-y-1">
                <div class="text-3xl font-bold"><?php echo $note_count; ?></div>
                <div class="text-sm text-blue-100">Notes Uploaded</div>
            </div>
            <div class="bg-white bg-opacity-10 rounded-lg p-4 text-center hover:bg-opacity-20 transition-all duration-300 transform hover:-translate-y-1">
                <div class="text-3xl font-bold"><?php echo $total_views; ?></div>
                <div class="text-sm text-blue-100">Total Views</div>
            </div>
            <div class="bg-white bg-opacity-10 rounded-lg p-4 text-center hover:bg-opacity-20 transition-all duration-300 transform hover:-translate-y-1">
                <div class="text-3xl font-bold"><?php echo $download_count; ?></div>
                <div class="text-sm text-blue-100">Total Downloads</div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Form -->
    <div class="max-w-2xl mx-auto fade-in">
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-user-edit text-blue-500 mr-2"></i>
                Edit Profile
            </h2>
            
            <?php if (isset($error) && !empty($error)): ?>
                <?php echo displayError($error); ?>
            <?php endif; ?>
            
            <?php if (isset($success) && !empty($success)): ?>
                <?php echo displaySuccess($success); ?>
            <?php endif; ?>
            
            <form action="profile.php" method="POST" class="space-y-6">
                <!-- Name Field -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                </div>
                
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                    <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly
                        class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-500">
                    <p class="mt-1 text-xs text-gray-500 flex items-center">
                        <i class="fas fa-info-circle mr-1"></i>
                        Email address cannot be changed.
                    </p>
                </div>
                
                <!-- Change Password Section -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-key text-blue-500 mr-2"></i>
                        Change Password
                    </h3>
                    
                    <!-- Current Password -->
                    <div class="mb-4">
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <input type="password" id="current_password" name="current_password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                    </div>
                    
                    <!-- New Password -->
                    <div class="mb-4">
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <div class="relative">
                            <input type="password" id="new_password" name="new_password" class="password-field w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                            <button type="button" class="toggle-password absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors duration-300">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            Leave blank if you don't want to change your password.
                        </p>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                        <i class="fas fa-save mr-2"></i>
                        <span>Update Profile</span>
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