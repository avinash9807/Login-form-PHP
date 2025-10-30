<?php
// AUTH/login.php - UPDATED WITH FORCE PASSWORD CHECK
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session start karo
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../config.php';

if (isset($_SESSION['user_id'])) {
    // Check if user needs to change password
    if (needsPasswordChange($_SESSION['user_id'])) {
        header("Location: change_forced_password.php");
        exit();
    } else {
        header("Location: ../DASHBOARD/dashboard.php");
        exit();
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = "❌ Username and password are required!";
    } elseif (preg_match('/\s/', $username)) {
        $error = "❌ Username cannot contain spaces!";
    } elseif (!isValidUsername($username)) {
        $error = "❌ Username can only contain letters, numbers, and underscores!";
    } else {
        $conn = getDBConnection();
        
        // ✅ CASE-SENSITIVE LOGIN
        $stmt = $conn->prepare("SELECT id, name, username, password, account_type, force_password_change FROM users WHERE BINARY username = ?");
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
                
                // Check if user needs to change password
                if ($user['force_password_change'] == 1) {
                    header("Location: change_forced_password.php");
                } else {
                    header("Location: ../DASHBOARD/dashboard.php");
                }
                exit();
            } else {
                $error = "❌ Invalid password!";
            }
        } else {
            $error = "❌ Username not found!";
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Login</title>
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
        .login-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
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
        input[type="password"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
            margin: 5px 0;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        button[type="submit"] {
            width: 100%;
            padding: 15px;
            background: #007bff;
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
            background: #0056b3;
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
        .creation-buttons {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            flex-direction: column;
        }
        .creation-btn {
            padding: 12px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: background 0.3s;
        }
        .creation-btn:hover {
            background: #218838;
        }
        .creation-btn.admin {
            background: #ffc107;
            color: black;
        }
        .creation-btn.admin:hover {
            background: #e0a800;
        }
        .default-creds {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            font-size: 12px;
            text-align: center;
        }

        /* Mobile Responsive */
        @media (max-width: 480px) {
            body {
                padding: 15px;
                align-items: flex-start;
                padding-top: 50px;
            }
            .login-container {
                padding: 25px 20px;
                border-radius: 12px;
            }
            h2 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>🎮 <?php echo SITE_NAME; ?> Login</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <input type="text" name="username" placeholder="👤 Username" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="🔒 Password" required>
            </div>
            <button type="submit">🚀 Login</button>
        </form>

        <div class="creation-buttons">
            <a href="verify_super_admin.php" class="creation-btn admin">👑 Create Admin Account</a>
            <a href="verify_admin.php" class="creation-btn">👥 Create Client Account</a>
        </div>

        <?php if (!hasUsers()): ?>
            <div class="default-creds">
                <p>First time? <a href="setup.php">Create Super Admin Account</a></p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('input[name="username"]').focus();
        });
    </script>
</body>
</html>