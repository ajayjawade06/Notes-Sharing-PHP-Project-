<?php
// Include header and functions
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Check if user is already logged in
if (isLoggedIn()) {
    redirect($base_url);
}

// Process forgot password form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $error = '';
    $success = '';
    
    // Validate input
    if (empty($email)) {
        $error = 'Please enter your email address';
    } else {
        // Check if email exists
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate a secure token that includes user ID and expiration time
            $userId = $user['id'];
            $expires = time() + 3600; // 1 hour from now
            $tokenData = $userId . '|' . $expires;
            $token = hash_hmac('sha256', $tokenData, 'secure_secret_key'); // Use a secure secret key
            
            // Generate reset link with user ID, expiration, and token
            $reset_link = "$base_url/reset_password.php?uid=$userId&expires=$expires&token=$token";
            
            // Display the reset link
            $success = 'Password reset link has been generated.<br>';
            $success .= "Here is your reset link: <a href='$reset_link' class='text-blue-600 hover:text-blue-800 underline'>$reset_link</a>";
        } else {
            // Don't reveal that the email doesn't exist for security reasons
            $success = 'If your email is registered, a reset link would be generated.';
        }
    }
}
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-md w-full space-y-8 fade-in">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                <i class="fas fa-key text-blue-600 text-2xl"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900">Forgot Password</h2>
            <p class="mt-2 text-sm text-gray-600">
                Enter your email to receive a password reset link
            </p>
        </div>
        
        <?php if (isset($error) && !empty($error)): ?>
            <?php echo displayError($error); ?>
        <?php endif; ?>
        
        <?php if (isset($success) && !empty($success)): ?>
            <?php echo displaySuccess($success); ?>
        <?php endif; ?>
        
        <div class="bg-white py-8 px-6 shadow rounded-lg">
            <form class="space-y-6" action="forgot_password.php" method="POST">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required 
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 
                            focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                            placeholder="Enter your email">
                        <p class="mt-1 text-xs text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            Enter the email address you used to register.
                        </p>
                    </div>
                </div>
                
                <div>
                    <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent rounded-md 
                        text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 
                        focus:ring-blue-500 transform hover:-translate-y-1 transition-all duration-300">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-unlock-alt text-blue-500 group-hover:text-blue-400 transition-colors duration-300"></i>
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