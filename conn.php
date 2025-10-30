<?php
// conn.php - Database Connection ONLY
error_reporting(E_ALL);
ini_set('display_errors', 1);

// put Your Database Credentials here
$db_host = 'localhost';
$db_user = 'avinash';  
$db_pass = 'Avinash123';
$db_name = 'stream';

// Database Connection Function
function getDBConnection() {
    global $db_host, $db_user, $db_pass, $db_name;
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        die("❌ Database connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}
?>
