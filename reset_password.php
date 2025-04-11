<?php
// Include header
require_once 'includes/header.php';

// Check if user is already logged in
if (isLoggedIn()) {
    redirect($base_url);
}

// Check if all required parameters are provided
if (!isset($_GET['uid']) || !isset($_GET['expires']) || !isset($_GET['token']) || 
    empty($_GET['uid']) || empty($_GET['expires']) || empty($_GET['token'])) {
    $_SESSION['error'] = 'Invalid password reset link';
    redirect("$base_url/forgot_password.php");
}

$userId = (int)sanitize($_GET['uid']);
$expires = (int)sanitize($_GET['expires']);
$providedToken = sanitize($_GET['token']);
$error = '';
$success = '';

// Check if token has expired
if (time() > $expires) {
    $_SESSION['error'] = 'Password reset link has expired';
    redirect("$base_url/forgot_password.php");
}

// Recreate the token to verify it
$tokenData = $userId . '|' . $expires;
$expectedToken = hash_hmac('sha256', $tokenData, 'secure_secret_key');

// Verify the token
if (!hash_equals($expectedToken, $providedToken)) {
    $_SESSION['error'] = 'Invalid password reset link';
    redirect("$base_url/forgot_password.php");
}

// Get user information
$sql = "SELECT * FROM users WHERE id = $userId";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    $_SESSION['error'] = 'User not found';
    redirect("$base_url/forgot_password.php");
}

$user = $result->fetch_assoc();

// Process reset password form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if (empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password
        $sql = "UPDATE users SET password = '$hashed_password' WHERE id = " . $user['id'];
        
        if ($conn->query($sql) === TRUE) {
            $_SESSION['success'] = 'Your password has been reset successfully. You can now login with your new password.';
            redirect("$base_url/login.php");
        } else {
            $error = 'Error resetting password: ' . $conn->error;
        }
    }
}
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-md w-full space-y-8 fade-in">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
                <i class="fas fa-key text-green-600 text-2xl"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900">Reset Password</h2>
            <p class="mt-2 text-sm text-gray-600">
                Create a new password for your account
            </p>
        </div>
        
        <?php if (isset($error) && !empty($error)): ?>
            <?php echo displayError($error); ?>
        <?php endif; ?>
        
        <?php if (isset($success) && !empty($success)): ?>
            <?php echo displaySuccess($success); ?>
        <?php endif; ?>
        
        <div class="bg-white py-8 px-6 shadow rounded-lg">
            <form class="space-y-6" action="reset_password.php?uid=<?php echo $userId; ?>&expires=<?php echo $expires; ?>&token=<?php echo $providedToken; ?>" method="POST">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" required 
                            class="password-field appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 
                            focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300"
                            placeholder="Create a new password">
                        <button type="button" class="toggle-password absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors duration-300">
                            <i class="fas fa-eye"></i>
                        </button>
                        <p class="mt-1 text-xs text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            Password must be at least 6 characters long.
                        </p>
                    </div>
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <div class="mt-1">
                        <input id="confirm_password" name="confirm_password" type="password" required 
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 
                            focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300"
                            placeholder="Confirm your new password">
                    </div>
                </div>
                
                <div>
                    <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent rounded-md 
                        text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 
                        focus:ring-green-500 transform hover:-translate-y-1 transition-all duration-300">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-lock text-green-500 group-hover:text-green-400 transition-colors duration-300"></i>
                        </span>
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
        
        <div class="text-center mt-4">
            <p class="text-sm text-gray-600">
                Remember your password? 
                <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-300">
                    Sign in
                </a>
            </p>
        </div>
    </div>
</div>

<!-- Toast Container for Notifications -->
<div id="toastContainer" class="fixed bottom-4 right-4 z-50 flex flex-col space-y-4"></div>

<?php
// Include footer
require_once 'includes/footer.php';
?>