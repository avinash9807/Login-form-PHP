<?php
// config.php - UPDATED WITH FORCE PASSWORD CHANGE
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session start only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include constants
include 'CONSTANTS/name_const.php';
include 'CONSTANTS/user_const.php';

// Include database connection
include 'conn.php';

// NEW: Safe table setup function that preserves your user detection feature
function safeSetupTables() {
    $conn = getDBConnection();
    
    try {
        // First check if users table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'users'");
        
        if ($table_check->num_rows == 0) {
            // Table doesn't exist, create it with all columns
            $sql = "CREATE TABLE users (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                account_type ENUM('super_admin', 'admin', 'client') DEFAULT 'client',
                force_password_change TINYINT(1) DEFAULT 0,
                created_by INT(6) UNSIGNED,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            if ($conn->query($sql) === TRUE) {
                error_log("✅ Users table created successfully");
                // Don't create default user here - let your setup page handle it
            } else {
                error_log("❌ Error creating users table: " . $conn->error);
            }
        } else {
            // Table exists, check if force_password_change column exists
            $column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'force_password_change'");
            if ($column_check->num_rows == 0) {
                // Add the new column
                $conn->query("ALTER TABLE users ADD COLUMN force_password_change TINYINT(1) DEFAULT 0");
                error_log("✅ Added force_password_change column to users table");
            }
        }
        
    } catch (Exception $e) {
        error_log("❌ Table setup error: " . $e->getMessage());
    } finally {
        $conn->close();
    }
}

// Check if any user exists in database - THIS IS YOUR IMPORTANT FEATURE
function hasUsers() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT id FROM users LIMIT 1");
    $has_users = ($result->num_rows > 0);
    $conn->close();
    return $has_users;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user type
function getUserType() {
    return $_SESSION['account_type'] ?? 'client';
}

// Get user display name based on type
function getUserDisplayName($account_type) {
    switch($account_type) {
        case 'super_admin':
            return '👑 Super Admin';
        case 'admin':
            return '⚡ Admin';
        case 'client':
            return '👤 Client';
        default:
            return 'User';
    }
}

// Check if user is Super Admin
function isSuperAdmin() {
    return ($_SESSION['account_type'] ?? '') == 'super_admin';
}

// Check if user is Admin or Super Admin
function isAdmin() {
    $type = $_SESSION['account_type'] ?? '';
    return ($type == 'admin' || $type == 'super_admin');
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: AUTH/login.php");
        exit();
    }
}

// Redirect if not admin
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: DASHBOARD/dashboard.php");
        exit();
    }
}

// Redirect if not super admin
function requireSuperAdmin() {
    if (!isSuperAdmin()) {
        header("Location: DASHBOARD/dashboard.php");
        exit();
    }
}

// Get creator name for a user
function getCreatorName($created_by) {
    if (!$created_by) return 'System';
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param("i", $created_by);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $creator = $result->fetch_assoc();
        return $creator['name'];
    }
    
    $stmt->close();
    $conn->close();
    return 'Unknown';
}

// Username validation function
function isValidUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]+$/', $username);
}

// Username cleaning function
function cleanUsername($username) {
    $username = trim($username);
    $username = preg_replace('/\s+/', ' ', $username);
    $username = str_replace(' ', '_', $username);
    return $username;
}

// NEW: Check if user needs to change password
function needsPasswordChange($user_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT force_password_change FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $user['force_password_change'] == 1;
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

// NEW: Set force password change flag
function setForcePasswordChange($user_id, $force = true) {
    $conn = getDBConnection();
    $force_value = $force ? 1 : 0;
    $stmt = $conn->prepare("UPDATE users SET force_password_change = ? WHERE id = ?");
    $stmt->bind_param("ii", $force_value, $user_id);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}

// NEW: Clear force password change flag
function clearForcePasswordChange($user_id) {
    return setForcePasswordChange($user_id, false);
}

// Initialize tables safely
safeSetupTables();
?>
