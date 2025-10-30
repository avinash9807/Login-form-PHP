<?php
// conn.php - Database Connection ONLY
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Your Database Credentials
$db_host = 'sql100.ezyro.com';
$db_user = 'ezyro_40131500';  
$db_pass = 'Avinash9807@';
$db_name = 'ezyro_40131500_stream';

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