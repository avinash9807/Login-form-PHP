<?php
// user_const.php - User Types & Permissions Constants

// User Type Constants
define('USER_SUPER_ADMIN', 'super_admin');
define('USER_ADMIN', 'admin');
define('USER_CLIENT', 'client');

// User Display Names
define('DISPLAY_SUPER_ADMIN', '👑 Super Admin');
define('DISPLAY_ADMIN', '⚡ Admin'); 
define('DISPLAY_CLIENT', '👤 Client');

// Permission Constants
define('PERM_CREATE_ADMIN', 'create_admin');
define('PERM_CREATE_CLIENT', 'create_client');
define('PERM_DELETE_USERS', 'delete_users');
define('PERM_EDIT_USERS', 'edit_users');

// Text Constants for Forms
define('TEXT_CREATE_SUPER_ADMIN', 'Create Super Admin');
define('TEXT_CREATE_ADMIN', 'Create Admin');
define('TEXT_CREATE_CLIENT', 'Create Client');
define('TEXT_FIRST_TIME_SETUP', 'First time here? Create Super Admin Account');
?>