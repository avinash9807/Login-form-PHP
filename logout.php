<?php
// logout.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sab session variables clear karo
session_unset();
session_destroy();

// Login page redirect karo - PATH UPDATE
header("Location: AUTH/login.php");
exit();
?>