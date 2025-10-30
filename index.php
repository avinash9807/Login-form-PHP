<?php
// index.php - SIMPLE VERSION
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple session start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include config
include 'config.php';

// Agar user hai to login, nahi to setup
if (hasUsers()) {
    header("Location: AUTH/login.php");
} else {
    header("Location: AUTH/setup.php");
}
exit();
?>