<?php
// working_test.php - NO SHELL_EXEC USAGE
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>VIP CHAUHAN - WORKING TEST</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .success { color: green; background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #ffe6e6; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .box { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🎮 VIP CHAUHAN - WORKING SYSTEM TEST</h1>
        <hr>";

// STEP 1: BASIC CHECK
echo "<div class='box'>
    <h2>🔧 STEP 1: Server Check</h2>
    <div class='success'>✅ PHP Version: " . phpversion() . "</div>
    <div class='success'>✅ Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Apache') . "</div>";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
    echo "<div class='success'>✅ Sessions working</div>";
}

echo "</div>";

// STEP 2: DATABASE CHECK
echo "<div class='box'>
    <h2>🗄️ STEP 2: Database Check</h2>";

$db_host = 'sql100.ezyro.com';
$db_user = 'ezyro_40131500';  
$db_pass = 'Avinash9807@';
$db_name = 'ezyro_40131500_stream';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    echo "<div class='error'>❌ Database connection failed: " . $conn->connect_error . "</div>";
} else {
    echo "<div class='success'>✅ Database connected successfully!</div>";
    
    $result = $conn->query("SELECT id, name, username, account_type FROM users");
    if ($result && $result->num_rows > 0) {
        echo "<div class='success'>✅ Found " . $result->num_rows . " users in database</div>";
        while($user = $result->fetch_assoc()) {
            echo "👤 <strong>" . $user['name'] . "</strong> (" . $user['username'] . ") - " . $user['account_type'] . "<br>";
        }
    }
    $conn->close();
}

echo "</div>";

// STEP 3: SIMPLE FILE CHECK
echo "<div class='box'>
    <h2>📁 STEP 3: File Check</h2>";

$files = [
    'config.php',
    'conn.php', 
    'AUTH/login.php',
    'AUTH/setup.php',
    'DASHBOARD/dashboard.php',
    'CONSTANTS/name_const.php',
    'CONSTANTS/user_const.php'
];

$all_files_ok = true;
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<div class='success'>✅ $file exists</div>";
        
        // Simple include test without shell_exec
        ob_start();
        try {
            $included = @include $file;
            $output = ob_get_clean();
            
            if ($included) {
                echo "<div class='success'>✅ $file can be included</div>";
            } else {
                echo "<div class='error'>❌ $file cannot be included</div>";
                $all_files_ok = false;
            }
        } catch (Exception $e) {
            ob_end_clean();
            echo "<div class='error'>❌ $file has error: " . $e->getMessage() . "</div>";
            $all_files_ok = false;
        }
    } else {
        echo "<div class='error'>❌ $file missing!</div>";
        $all_files_ok = false;
    }
    echo "<br>";
}

echo "</div>";

// STEP 4: CONFIG.PHP SPECIFIC TEST
echo "<div class='box'>
    <h2>⚙️ STEP 4: Config.php Function Test</h2>";

if (file_exists('config.php')) {
    // Include config.php and check functions
    include 'config.php';
    
    $functions = ['getDBConnection', 'hasUsers', 'isLoggedIn', 'isAdmin'];
    $all_functions_ok = true;
    
    foreach ($functions as $func) {
        if (function_exists($func)) {
            echo "<div class='success'>✅ $func() function exists</div>";
        } else {
            echo "<div class='error'>❌ $func() function missing</div>";
            $all_functions_ok = false;
        }
    }
    
    // Test hasUsers function
    if (function_exists('hasUsers')) {
        $has_users = hasUsers();
        echo "<div class='success'>✅ hasUsers() returned: " . ($has_users ? 'TRUE (Users exist)' : 'FALSE (No users)') . "</div>";
    }
} else {
    echo "<div class='error'>❌ config.php not found for function test</div>";
}

echo "</div>";

// STEP 5: FINAL RESULT
echo "<div class='box'>
    <h2>🎯 STEP 5: Final Result</h2>";

if ($all_files_ok) {
    echo "<div class='success' style='font-size: 18px;'>
        <h3>✅ SYSTEM READY!</h3>
        <p>All files are present and working. Your login system should work now.</p>
    </div>";
    
    echo "<h3>🚀 Try these links:</h3>";
    echo "<a href='AUTH/login.php'><button>🔑 Main Login Page</button></a>";
    echo "<a href='DASHBOARD/dashboard.php'><button style='background: #28a745;'>📊 Dashboard (if logged in)</button></a>";
    
} else {
    echo "<div class='error' style='font-size: 18px;'>
        <h3>❌ SYSTEM HAS ISSUES</h3>
        <p>Some files are missing or have errors. Check Step 3 for details.</p>
    </div>";
}

echo "</div>";

// STEP 6: DIRECT LOGIN TEST
echo "<div class='box'>
    <h2>🔑 STEP 6: Direct Login Test</h2>";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if (!$conn->connect_error) {
        $stmt = $conn->prepare("SELECT id, name, username, password, account_type FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['account_type'] = $user['account_type'];
                
                echo "<div class='success'>✅ LOGIN SUCCESSFUL!</div>";
                echo "<p>Welcome, <strong>" . $user['name'] . "</strong>!</p>";
                echo "<p>Redirecting to dashboard...</p>";
                echo "<script>setTimeout(() => window.location.href = 'DASHBOARD/dashboard.php', 3000)</script>";
            } else {
                echo "<div class='error'>❌ Invalid password!</div>";
            }
        } else {
            echo "<div class='error'>❌ Username not found!</div>";
        }
        
        $stmt->close();
        $conn->close();
    }
}

echo "<form method='POST'>
    <p><strong>Test with existing user:</strong></p>
    <input type='text' name='username' placeholder='Username' value='admin' required style='width: 100%; padding: 10px; margin: 5px 0;'><br>
    <input type='password' name='password' placeholder='Password' required style='width: 100%; padding: 10px; margin: 5px 0;'><br>
    <button type='submit'>🔑 Test Login</button>
</form>";

echo "</div>";

echo "</div>
</body>
</html>";