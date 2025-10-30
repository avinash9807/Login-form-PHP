<?php
// AUTH/change_forced_password.php - FORCE PASSWORD CHANGE PAGE
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session start karo
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user actually needs to change password
if (!needsPasswordChange($_SESSION['user_id'])) {
    header("Location: ../DASHBOARD/dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = "❌ All fields are required!";
    } elseif ($new_password !== $confirm_password) {
        $error = "❌ New password and confirm password do not match!";
    } elseif (strlen($new_password) < 6) {
        $error = "❌ Password must be at least 6 characters long!";
    } else {
        $conn = getDBConnection();
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password and clear force password change flag
        $stmt = $conn->prepare("UPDATE users SET password = ?, force_password_change = 0 WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $success = "✅ Password changed successfully! Redirecting to dashboard...";
            // Clear the force password change flag in session
            clearForcePasswordChange($_SESSION['user_id']);
            
            // Redirect after 2 seconds
            echo "<script>setTimeout(() => window.location.href = '../DASHBOARD/dashboard.php', 2000)</script>";
        } else {
            $error = "❌ Failed to change password: " . $conn->error;
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
    <title>Change Password Required - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        h1 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        
        .subtitle {
            color: #666;
            font-size: 1rem;
        }
        
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid #ffc107;
        }
        
        .warning-box h3 {
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-input:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-change {
            background: #28a745;
            color: white;
        }
        
        .btn-change:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .error {
            color: #d63031;
            text-align: center;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #ffe6e6;
            border-radius: 8px;
            border-left: 4px solid #d63031;
        }
        
        .success {
            color: #155724;
            text-align: center;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #d4edda;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        
        .user-info {
            background: #e3f2fd;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            border-left: 4px solid #2196F3;
        }
        
        @media (max-width: 480px) {
            body {
                padding: 15px;
                align-items: flex-start;
                padding-top: 50px;
            }
            .container {
                padding: 1.5rem;
            }
            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1>Password Change Required</h1>
            <p class="subtitle">You must change your password to continue</p>
        </div>

        <div class="user-info">
            <strong>Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? $_SESSION['username']); ?></strong>
            <br>
            <small>Account: <?php echo getUserDisplayName($_SESSION['account_type'] ?? 'client'); ?></small>
        </div>

        <div class="warning-box">
            <h3><i class="fas fa-exclamation-triangle"></i> Security Notice</h3>
            <p>Your password has been reset by an administrator. For security reasons, you must set a new password before accessing the system.</p>
        </div>

        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="new_password">
                        <i class="fas fa-key"></i> New Password (minimum 6 characters)
                    </label>
                    <input type="password" id="new_password" name="new_password" class="form-input" required minlength="6" placeholder="Enter your new password">
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">
                        <i class="fas fa-key"></i> Confirm New Password
                    </label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required minlength="6" placeholder="Confirm your new password">
                </div>

                <button type="submit" class="btn btn-change">
                    <i class="fas fa-save"></i>
                    Change Password & Continue
                </button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        // Password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            function validatePasswords() {
                if (newPassword.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
            
            newPassword.addEventListener('input', validatePasswords);
            confirmPassword.addEventListener('input', validatePasswords);
            
            // Auto focus on first field
            newPassword.focus();
        });
    </script>
</body>
</html>