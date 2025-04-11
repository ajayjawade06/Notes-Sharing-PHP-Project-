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

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $error = '';
    
    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Check if email already exists
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $error = 'Email already exists. Please use a different email or login';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";
            
            if ($conn->query($sql) === TRUE) {
                // Registration successful, set success message
                $_SESSION['success'] = 'Registration successful! Please login with your credentials.';
                
                // Redirect to login page
                redirect($base_url . '/login.php');
            } else {
                $error = 'Error: ' . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Notes Sharing Platform</title>
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
            <i class="fas fa-graduation-cap text-5xl mb-4"></i>
            <h2>Join Notes Sharing Today</h2>
            <p>Create an account to start sharing and accessing academic resources.</p>
            
            <div class="auth-features">
                <div class="auth-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Free account creation</span>
                </div>
                <div class="auth-feature">
                    <i class="fas fa-shield-alt"></i>
                    <span>Secure and private</span>
                </div>
            </div>
        </div>
        
        <!-- Right Panel with Registration Form -->
        <div class="auth-right-panel">
            <div class="max-w-md w-full mx-auto fade-in">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
                        <i class="fas fa-user-plus text-green-600 text-2xl"></i>
                    </div>
                    <h2 class="text-3xl font-extrabold text-gray-900">Create your account</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Or
                        <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-300">
                            sign in to your existing account
                        </a>
                    </p>
                </div>
                
                <?php if (isset($error) && !empty($error)): ?>
                    <?php echo displayError($error); ?>
                <?php endif; ?>
                
                <div class="bg-white py-8 px-6 shadow rounded-lg mt-6">
                <form class="space-y-6" action="register.php" method="POST">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <div class="mt-1">
                            <input id="name" name="name" type="text" autocomplete="name" required 
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 
                                focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                                placeholder="Enter your full name">
                        </div>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" autocomplete="email" required 
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 
                                focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                                placeholder="Enter your email">
                            <p class="mt-1 text-xs text-gray-500 flex items-center">
                                <i class="fas fa-shield-alt mr-1"></i>
                                We'll never share your email with anyone else.
                            </p>
                        </div>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="mt-1 relative">
                            <input id="password" name="password" type="password" autocomplete="new-password" required 
                                class="password-field appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                                placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                                placeholder="Create a password">
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
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <div class="mt-1">
                            <input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" required 
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 
                                focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                                placeholder="Confirm your password">
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <input id="terms" name="terms" type="checkbox" required
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-colors duration-300">
                        <label for="terms" class="ml-2 block text-sm text-gray-700">
                            I agree to the 
                            <a href="#" class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-300">Terms and Conditions</a>
                        </label>
                    </div>
                    
                    <div>
                        <button type="submit" 
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent rounded-md 
                            text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 
                            focus:ring-green-500 transform hover:-translate-y-1 transition-all duration-300">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="fas fa-user-plus text-green-500 group-hover:text-green-400 transition-colors duration-300"></i>
                            </span>
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-sm text-gray-600">
                    By creating an account, you agree to our 
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