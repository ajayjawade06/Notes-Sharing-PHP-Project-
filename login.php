<?php
// Include necessary files
require_once 'config.php';
require_once 'includes/functions.php';

// Check if user is already logged in
if (isLoggedIn()) {
    redirect($base_url);
}

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $error = '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        // Check if user exists
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect to home page
                $_SESSION['success'] = 'You have been logged in successfully';
                redirect($base_url);
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'User with this email does not exist';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Notes Sharing Platform</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Home Navigation Button -->
    <a href="<?php echo $base_url; ?>" class="home-nav-btn fixed top-4 left-4 z-[10000] bg-white rounded-full p-3 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <i class="fas fa-home text-primary-600 text-xl"></i>
    </a>
    <div class="auth-container">
        <!-- Left Panel with Info -->
        <div class="auth-left-panel">
            <i class="fas fa-book-reader text-5xl mb-4"></i>
            <h2>Welcome to Notes Sharing</h2>
            <p>Access your academic resources anytime, anywhere.</p>
            
            <div class="auth-features">
                <div class="auth-feature">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Upload and share your notes</span>
                </div>
                <div class="auth-feature">
                    <i class="fas fa-download"></i>
                    <span>Download study materials</span>
                </div>

            </div>
        </div>
        
        <!-- Right Panel with Login Form -->
        <div class="auth-right-panel">
            <div class="max-w-md w-full mx-auto fade-in">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                        <i class="fas fa-sign-in-alt text-blue-600 text-2xl"></i>
                    </div>
                    <h2 class="text-3xl font-extrabold text-gray-900">Sign in to your account</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Or
                        <a href="register.php" class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-300">
                            create a new account
                        </a>
                    </p>
                </div>
                
                <?php if (isset($error) && !empty($error)): ?>
                    <?php echo displayError($error); ?>
                <?php endif; ?>
                
                <div class="bg-white py-8 px-6 shadow rounded-lg mt-6">
                <form class="space-y-6" action="login.php" method="POST">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" autocomplete="email" required 
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 
                                focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                                placeholder="Enter your email">
                        </div>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="mt-1 relative">
                            <input id="password" name="password" type="password" autocomplete="current-password" required 
                                class="password-field appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                                placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                                placeholder="Enter your password">
                            <button type="button" class="toggle-password absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors duration-300">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox" 
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-colors duration-300">
                            <label for="remember" class="ml-2 block text-sm text-gray-700">
                                Remember me
                            </label>
                        </div>
                        
                        <div class="text-sm">
                            <a href="forgot_password.php" class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-300">
                                Forgot password?
                            </a>
                        </div>
                    </div>
                    
                    <div>
                        <button type="submit" 
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent rounded-md 
                            text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 
                            focus:ring-blue-500 transform hover:-translate-y-1 transition-all duration-300">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="fas fa-lock text-blue-500 group-hover:text-blue-400 transition-colors duration-300"></i>
                            </span>
                            Sign in
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-sm text-gray-600">
                    By signing in, you agree to our 
                    <a href="#" class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-300">Terms of Service</a> 
                    and 
                    <a href="#" class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-300">Privacy Policy</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Toast Container for Notifications -->
    <div id="toastContainer" class="fixed bottom-4 right-4 z-50 flex flex-col space-y-4"></div>

    <!-- Custom JS -->
    <script src="js/script.js"></script>
</body>
</html>