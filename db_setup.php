<?php
// db_setup.php - Database Setup using conn.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the connection file
require_once 'conn.php';

function setupDatabase() {
    // Use the connection function from conn.php
    $conn = getDBConnection();
    
    if ($conn->connect_error) {
        die("❌ Database connection failed: " . $conn->connect_error);
    }
    
    echo "<div style='border: 2px solid green; padding: 15px; margin: 10px 0; border-radius: 5px; background: #f0fff0;'>";
    echo "<div style='color: green; font-weight: bold;'>✅ Database connected successfully</div>";
    echo "</div><br>";
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<div style='border: 2px solid green; padding: 15px; margin: 10px 0; border-radius: 5px; background: #f0fff0;'>";
        echo "<div style='color: green; font-weight: bold;'>✅ Users table created successfully</div>";
        echo "</div><br>";
    } else {
        echo "<div style='border: 2px solid red; padding: 15px; margin: 10px 0; border-radius: 5px; background: #fff0f0;'>";
        echo "<div style='color: red; font-weight: bold;'>❌ Error creating table: " . $conn->error . "</div>";
        echo "</div><br>";
    }
    
    // Insert default admin user
    $username = "admin";
    $email = "admin@example.com";
    $hashed_password = password_hash("admin123", PASSWORD_DEFAULT);
    
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT IGNORE INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "<div style='border: 2px solid green; padding: 15px; margin: 10px 0; border-radius: 5px; background: #f0fff0;'>";
            echo "<div style='color: green; font-weight: bold;'>✅ Default admin user created</div>";
            echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>🔑 Default Login Credentials:</strong><br>";
            echo "👤 Username: <strong>admin</strong><br>";
            echo "📧 Email: <strong>admin@example.com</strong><br>";
            echo "🔐 Password: <strong>admin123</strong>";
            echo "</div>";
            echo "</div><br>";
        } else {
            echo "<div style='border: 2px solid blue; padding: 15px; margin: 10px 0; border-radius: 5px; background: #f0f8ff;'>";
            echo "<div style='color: blue; font-weight: bold;'>ℹ️ Admin user already exists</div>";
            echo "</div><br>";
        }
    } else {
        echo "<div style='border: 2px solid red; padding: 15px; margin: 10px 0; border-radius: 5px; background: #fff0f0;'>";
        echo "<div style='color: red; font-weight: bold;'>❌ Error creating user: " . $stmt->error . "</div>";
        echo "</div><br>";
    }
    
    $stmt->close();
    $conn->close();
    
    return true;
}

// Run setup only if not already initialized
if (!isset($_SESSION['db_setup_done'])) {
    echo "<div style='border: 2px solid #0066cc; padding: 15px; margin: 10px 0; border-radius: 5px; background: #e6f7ff;'>";
    echo "<h3 style='color: #0066cc; margin-top: 0;'>🚀 Database Setup Initialized</h3>";
    echo "</div>";
    
    setupDatabase();
    $_SESSION['db_setup_done'] = true;
    
    echo "<div style='border: 2px solid #28a745; padding: 15px; margin: 10px 0; border-radius: 5px; background: #d4edda;'>";
    echo "<h3 style='color: #155724; margin-top: 0;'>🎉 Database Setup Completed Successfully!</h3>";
    echo "</div>";
} else {
    echo "<div style='border: 2px solid #6c757d; padding: 15px; margin: 10px 0; border-radius: 5px; background: #f8f9fa;'>";
    echo "<div style='color: #6c757d; font-weight: bold;'>ℹ️ Database setup was already completed</div>";
    echo "</div>";
}
?>
