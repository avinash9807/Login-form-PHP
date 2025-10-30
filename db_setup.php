<?php
function setupDatabase() {
    $db_host = 'sql100.ezyro.com';
$db_user = 'ezyro_40131500';  
$db_pass = 'Avinash9807@';
$db_name = 'ezyro_40131500_stream';
    
    // Create connection without database
    $conn = new mysqli($host, $username, $password);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "<div style='color: green;'>✅ Database created successfully</div><br>";
    } else {
        echo "<div style='color: red;'>❌ Error creating database: " . $conn->error . "</div><br>";
    }
    
    // Select database
    $conn->select_db($dbname);
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<div style='color: green;'>✅ Users table created successfully</div><br>";
    } else {
        echo "<div style='color: red;'>❌ Error creating table: " . $conn->error . "</div><br>";
    }
    
    // Insert default admin user
    $username = "admin";
    $email = "admin@example.com";
    $hashed_password = password_hash("admin123", PASSWORD_DEFAULT);
    
    $sql = "INSERT IGNORE INTO users (username, email, password) 
            VALUES ('$username', '$email', '$hashed_password')";
    
    if ($conn->query($sql) === TRUE) {
        if ($conn->affected_rows > 0) {
            echo "<div style='color: green;'>✅ Default admin user created</div><br>";
            echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>🔑 Default Login Credentials:</strong><br>";
            echo "👤 Username: <strong>admin</strong><br>";
            echo "🔐 Password: <strong>admin123</strong>";
            echo "</div>";
        } else {
            echo "<div style='color: blue;'>ℹ️ Admin user already exists</div><br>";
        }
    } else {
        echo "<div style='color: red;'>❌ Error creating user: " . $conn->error . "</div><br>";
    }
    
    $conn->close();
    return true;
}

// Run setup only if not already initialized
if (!isset($_SESSION['db_setup_done'])) {
    setupDatabase();
    $_SESSION['db_setup_done'] = true;
}
?>