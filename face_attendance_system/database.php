<?php
// Database configuration
$host = "localhost";
$database = "face_attendance";
$user = "root";
$password = "";

// Create database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create tables if they don't exist
function initializeDatabase() {
    global $pdo;
    
    // Create students table
    $createStudentsTable = "
    CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        registration_number VARCHAR(50) UNIQUE NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        images JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    // Create attendance table
    $createAttendanceTable = "
    CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        registration_number VARCHAR(50) NOT NULL,
        attendance_status ENUM('Present', 'Absent') NOT NULL,
        date_marked DATE NOT NULL,
        time_marked TIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (registration_number) REFERENCES students(registration_number)
    )";
    
    try {
        $pdo->exec($createStudentsTable);
        $pdo->exec($createAttendanceTable);
    } catch (PDOException $e) {
        echo "Error creating tables: " . $e->getMessage();
    }
}

// Initialize database on include
initializeDatabase();
?>