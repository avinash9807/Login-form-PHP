<?php
// AUTH/setup.php - FIXED VERSION
include '../config.php';

// Agar already koi user hai to login page redirect karo
if (hasUsers()) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Username cleaning and validation
    $username = cleanUsername($username);
    $email = strtolower(trim($email));
    
    // Validation
    if (empty($name) || empty($username) || empty($email) || empty($password)) {
        $error = "❌ All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "❌ Please enter a valid email address!";
    } elseif ($password !== $confirm_password) {
        $error = "❌ Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "❌ Password must be at least 6 characters!";
    } elseif (!isValidUsername($username)) {
        $error = "❌ Username can only contain letters, numbers, and underscores!";
    } elseif (strlen($username) < 3) {
        $error = "❌ Username must be at least 3 characters!";
    } elseif (strlen($name) < 2) {
        $error = "❌ Name must be at least 2 characters!";
    } else {
        $conn = getDBConnection();
        
        // Check if username already exists (case-insensitive)
        $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "❌ Username or Email already exists!";
        } else {
            // Insert the first user as SUPER ADMIN
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, username, email, password, account_type) VALUES (?, ?, ?, ?, ?)");
            $account_type = USER_SUPER_ADMIN;
            $stmt->bind_param("sssss", $name, $username, $email, $hashed_password, $account_type);
            
            if ($stmt->execute()) {
                $success = "✅ ".DISPLAY_SUPER_ADMIN." account created successfully! You can now login.";
                // Auto redirect to login after 3 seconds
                header("refresh:3;url=login.php");
            } else {
                $error = "❌ Setup failed! " . $conn->error;
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}

// ❌ FUNCTIONS COMPLETELY REMOVED FROM HERE - They are now in config.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Initial Setup</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 15px 45px 15px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .password-wrapper {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            color: #666;
            padding: 5px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        button[type="submit"] {
            width: 100%;
            padding: 15px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s;
            margin-top: 10px;
        }
        button[type="submit"]:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        .error {
            color: #d63031;
            text-align: center;
            margin-bottom: 20px;
            padding: 12px;
            background: #ffe6e6;
            border-radius: 8px;
            border-left: 4px solid #d63031;
            font-size: 14px;
        }
        .success {
            color: #00b894;
            text-align: center;
            margin-bottom: 20px;
            padding: 12px;
            background: #e6ffe6;
            border-radius: 8px;
            border-left: 4px solid #00b894;
            font-size: 14px;
        }
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #007bff;
            font-size: 14px;
            line-height: 1.4;
        }
        .note {
            background: #fff3cd;
            padding: 8px;
            border-radius: 5px;
            margin-top: 5px;
            font-size: 12px;
            color: #856404;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
            font-size: 14px;
        }

        /* Mobile Responsive */
        @media (max-width: 480px) {
            body {
                padding: 15px;
                align-items: flex-start;
                padding-top: 50px;
            }
            .container {
                padding: 25px 20px;
                border-radius: 12px;
            }
            h2 {
                font-size: 22px;
            }
            input[type="text"],
            input[type="email"],
            input[type="password"] {
                padding: 12px 40px 12px 12px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>⚙️ <?php echo SITE_NAME; ?> - Initial Setup</h2>
        
        <div class="info">
            <strong>Welcome to <?php echo SITE_NAME; ?>!</strong><br>
            This is your first time setup. Please create your <?php echo DISPLAY_SUPER_ADMIN; ?> account.
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
            <p style="text-align:center; margin-top:10px;">Redirecting to login page...</p>
        <?php else: ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">👤 Your Full Name:</label>
                <input type="text" id="name" name="name" placeholder="Enter your full name" required>
            </div>
            
            <div class="form-group">
                <label for="username">🔑 <?php echo DISPLAY_SUPER_ADMIN; ?> Username:</label>
                <input type="text" id="username" name="username" placeholder="Enter username (letters, numbers, _ only)" required>
                <div class="note">Note: Spaces will be converted to underscores</div>
            </div>
            
            <div class="form-group">
                <label for="email">📧 Email Address:</label>
                <input type="email" id="email" name="email" placeholder="Enter email" required>
            </div>
            
            <div class="form-group">
                <label for="password">🔒 Password:</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter password (min 6 characters)" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">👁️</button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">✅ Confirm Password:</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">👁️</button>
                </div>
            </div>
            
            <button type="submit">🚀 Create <?php echo DISPLAY_SUPER_ADMIN; ?></button>
        </form>
        
        <p style="text-align:center; margin-top:15px; font-size:12px; color:#666;">
            Note: This will be your main admin account with full privileges
        </p>
        
        <?php endif; ?>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleButton = passwordField.nextElementSibling;
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleButton.textContent = '🔒';
            } else {
                passwordField.type = 'password';
                toggleButton.textContent = '👁️';
            }
        }
    </script>
</body>
</html>