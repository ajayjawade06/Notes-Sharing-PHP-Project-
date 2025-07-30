<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "notes_sharing";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db($dbname);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Users table created successfully or already exists<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

// Create notes table
$sql = "CREATE TABLE IF NOT EXISTS notes (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    views INT(11) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Notes table created successfully or already exists<br>";
} else {
    echo "Error creating notes table: " . $conn->error . "<br>";
}

// Create downloads table
$sql = "CREATE TABLE IF NOT EXISTS downloads (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    note_id INT(11) NOT NULL,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Downloads table created successfully or already exists<br>";
} else {
    echo "Error creating downloads table: " . $conn->error . "<br>";
}

// Create an admin user if it doesn't exist
$adminName = "Admin";
$adminEmail = "admin@notessharing.com";
$adminPassword = password_hash("admin123", PASSWORD_DEFAULT); // Hashed password

$checkAdmin = "SELECT * FROM users WHERE email = '$adminEmail'";
$result = $conn->query($checkAdmin);

if ($result->num_rows == 0) {
    $sql = "INSERT INTO users (name, email, password, role) VALUES ('$adminName', '$adminEmail', '$adminPassword', 'admin')";
    if ($conn->query($sql) === TRUE) {
        echo "Admin user created successfully<br>";
    } else {
        echo "Error creating admin user: " . $conn->error . "<br>";
    }
} else {
    echo "Admin user already exists<br>";
}

// Create necessary directories
$directories = ['uploads', 'css', 'js', 'images'];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
        echo "Directory '$dir' created successfully<br>";
    } else {
        echo "Directory '$dir' already exists<br>";
    }
}

echo "Database setup completed successfully!";

$conn->close();
?>