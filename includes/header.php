<?php
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes Sharing Platform</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                    },
                    transitionProperty: {
                        'height': 'height',
                        'spacing': 'margin, padding',
                    },
                    transitionDuration: {
                        '400': '400ms',
                    },
                },
            },
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom Styles -->
    <style>
        /* Custom animations and transitions */
        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Fade in animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        /* Staggered fade in for children */
        .stagger-children > * {
            opacity: 0;
            animation: fadeIn 0.5s ease-out forwards;
        }
        .stagger-children > *:nth-child(1) { animation-delay: 0.1s; }
        .stagger-children > *:nth-child(2) { animation-delay: 0.2s; }
        .stagger-children > *:nth-child(3) { animation-delay: 0.3s; }
        .stagger-children > *:nth-child(4) { animation-delay: 0.4s; }
        .stagger-children > *:nth-child(5) { animation-delay: 0.5s; }
        
        /* Dropdown menu styles */
        .dropdown-menu {
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: opacity 0.3s, transform 0.3s, visibility 0s 0.3s;
        }
        
        .dropdown-trigger:hover + .dropdown-menu,
        .dropdown-menu:hover {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            transition: opacity 0.3s, transform 0.3s, visibility 0s;
        }
        
        /* Nav link hover effect */
        .nav-link {
            position: relative;
            overflow: hidden;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: white;
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s ease;
        }
        
        .nav-link:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }
        
        /* Auth pages styles */
        .auth-page {
            height: 100vh;
            overflow: hidden;
        }
        
        .auth-left-panel {
            background: linear-gradient(135deg, #0284c7 0%, #0c4a6e 100%);
            color: white;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .auth-left-panel h2 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .auth-left-panel p {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }
        
        .auth-feature {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .auth-feature i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-primary-600 text-white shadow-lg sticky top-0 z-50 transition-all duration-300 ease-in-out">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <a href="<?php echo $base_url; ?>" class="text-xl font-bold hover:text-primary-200 transition-colors duration-300 flex items-center">
                    <i class="fas fa-book-open text-2xl mr-2"></i>
                    <span>Notes Sharing</span>
                </a>
                
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-white hover:text-primary-200 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                    <a href="<?php echo $base_url; ?>" class="nav-link px-3 py-2 rounded hover:bg-primary-700 transition-all duration-300 hover:shadow-md flex items-center gap-1">
                        <i class="fas fa-home text-sm"></i>
                        <span>Home</span>
                    </a>
                    <a href="<?php echo $base_url; ?>/upload.php" class="nav-link px-3 py-2 rounded hover:bg-primary-700 transition-all duration-300 hover:shadow-md flex items-center gap-1">
                        <i class="fas fa-upload text-sm"></i>
                        <span>Upload Notes</span>
                    </a>
                    <a href="<?php echo $base_url; ?>/my_notes.php" class="nav-link px-3 py-2 rounded hover:bg-primary-700 transition-all duration-300 hover:shadow-md flex items-center gap-1">
                        <i class="fas fa-book text-sm"></i>
                        <span>My Notes</span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (isAdmin()): ?>
                    <a href="<?php echo $base_url; ?>/admin/index.php" class="nav-link px-3 py-2 rounded hover:bg-primary-700 transition-all duration-300 hover:shadow-md flex items-center gap-1">
                        <i class="fas fa-shield-alt text-sm"></i>
                        <span>Admin Panel</span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (isLoggedIn()): ?>
                    <!-- User Dropdown -->
                    <div class="relative">
                        <button class="dropdown-trigger flex items-center px-3 py-2 rounded hover:bg-primary-700 transition-all duration-300 hover:shadow-md">
                            <i class="fas fa-user-circle mr-2"></i>
                            <span>Welcome, <?php echo $_SESSION['name']; ?></span>
                            <i class="fas fa-chevron-down ml-2 text-xs"></i>
                        </button>
                        <div class="dropdown-menu absolute right-0 mt-0 pt-2 w-48 bg-transparent z-10">
                            <div class="bg-white rounded-md shadow-lg py-1 overflow-hidden">
                                <a href="<?php echo $base_url; ?>/profile.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100 flex items-center">
                                    <i class="fas fa-id-card mr-2 text-primary-600"></i>
                                    <span>Profile</span>
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <a href="<?php echo $base_url; ?>/logout.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100 flex items-center">
                                    <i class="fas fa-sign-out-alt mr-2 text-primary-600"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <a href="<?php echo $base_url; ?>/login.php" class="px-3 py-2 bg-white text-primary-600 rounded hover:bg-gray-100 transition-all duration-300 hover:shadow-md flex items-center gap-1">
                        <i class="fas fa-sign-in-alt text-sm"></i>
                        <span>Login</span>
                    </a>
                    <a href="<?php echo $base_url; ?>/register.php" class="px-3 py-2 border border-white rounded hover:bg-primary-700 transition-all duration-300 hover:shadow-md flex items-center gap-1">
                        <i class="fas fa-user-plus text-sm"></i>
                        <span>Register</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Mobile Navigation -->
            <div id="mobile-menu" class="md:hidden hidden mt-3 pb-2">
                <?php if (isLoggedIn()): ?>
                <a href="<?php echo $base_url; ?>" class="block py-2 hover:bg-primary-700 rounded px-3 transition-colors duration-300 flex items-center gap-1">
                    <i class="fas fa-home text-sm"></i>
                    <span>Home</span>
                </a>
                <a href="<?php echo $base_url; ?>/upload.php" class="block py-2 hover:bg-primary-700 rounded px-3 transition-colors duration-300 flex items-center gap-1">
                    <i class="fas fa-upload text-sm"></i>
                    <span>Upload Notes</span>
                </a>
                <a href="<?php echo $base_url; ?>/my_notes.php" class="block py-2 hover:bg-primary-700 rounded px-3 transition-colors duration-300 flex items-center gap-1">
                    <i class="fas fa-book text-sm"></i>
                    <span>My Notes</span>
                </a>
                <?php endif; ?>
                
                <?php if (isAdmin()): ?>
                <a href="<?php echo $base_url; ?>/admin/index.php" class="block py-2 hover:bg-primary-700 rounded px-3 transition-colors duration-300 flex items-center gap-1">
                    <i class="fas fa-shield-alt text-sm"></i>
                    <span>Admin Panel</span>
                </a>
                <?php endif; ?>
                
                <?php if (isLoggedIn()): ?>
                <div class="border-t border-primary-500 my-2"></div>
                <a href="<?php echo $base_url; ?>/profile.php" class="block py-2 hover:bg-primary-700 rounded px-3 transition-colors duration-300 flex items-center gap-1">
                    <i class="fas fa-id-card text-sm"></i>
                    <span>Profile</span>
                </a>
                <a href="<?php echo $base_url; ?>/logout.php" class="block py-2 hover:bg-primary-700 rounded px-3 transition-colors duration-300 flex items-center gap-1">
                    <i class="fas fa-sign-out-alt text-sm"></i>
                    <span>Logout</span>
                </a>
                <?php else: ?>
                <div class="border-t border-primary-500 my-2"></div>
                <a href="<?php echo $base_url; ?>/login.php" class="block py-2 hover:bg-primary-700 rounded px-3 transition-colors duration-300 flex items-center gap-1">
                    <i class="fas fa-sign-in-alt text-sm"></i>
                    <span>Login</span>
                </a>
                <a href="<?php echo $base_url; ?>/register.php" class="block py-2 hover:bg-primary-700 rounded px-3 transition-colors duration-300 flex items-center gap-1">
                    <i class="fas fa-user-plus text-sm"></i>
                    <span>Register</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-6 flex-grow">
        <?php
        if (isset($_SESSION['success'])) {
            echo displaySuccess($_SESSION['success']);
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo displayError($_SESSION['error']);
            unset($_SESSION['error']);
        }
        ?>