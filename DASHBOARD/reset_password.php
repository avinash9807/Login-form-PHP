<?php
// DASHBOARD/reset_password.php - SMART PERMISSIONS VERSION
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session start karo
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../config.php';

// Check if user is logged in and is Admin/Super Admin
if (!isset($_SESSION['user_id']) || !(isAdmin())) {
    header("Location: ../AUTH/login.php");
    exit();
}

// Safely get session values
$username = $_SESSION['username'] ?? 'User';
$name = $_SESSION['name'] ?? $username;
$account_type = $_SESSION['account_type'] ?? 'client';
$user_id = $_SESSION['user_id'];
$is_super_admin = isSuperAdmin();
$is_admin = isAdmin();

// Get user_id from URL
$target_user_id = $_GET['user_id'] ?? 0;

if (!$target_user_id) {
    header("Location: user_list.php?error=Invalid user ID");
    exit();
}

// Get target user details
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, name, username, account_type FROM users WHERE id = ?");
$stmt->bind_param("i", $target_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    $conn->close();
    header("Location: user_list.php?error=User not found");
    exit();
}

$target_user = $result->fetch_assoc();
$stmt->close();

// SMART PERMISSION CHECK:
$can_reset = false;
$is_own_account = ($target_user_id == $user_id);
$action_type = 'reset'; // Default action

if ($is_own_account) {
    // User can always change their own password
    $can_reset = true;
    $action_type = 'change';
} elseif ($is_super_admin) {
    // Super Admin can reset anyone's password
    $can_reset = true;
    $action_type = 'reset';
} elseif ($is_admin && $target_user['account_type'] == 'client') {
    // Admin can reset client passwords only
    $can_reset = true;
    $action_type = 'reset';
}

if (!$can_reset) {
    $conn->close();
    header("Location: user_list.php?error=You don't have permission to manage this user's password");
    exit();
}

// Generate random password
function generateRandomPassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

// Process password reset/change
$new_password = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($is_own_account) {
        // Own account - change password with confirmation
        $current_password = $_POST['current_password'] ?? '';
        $new_password_input = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $stmt->close();
        
        if (password_verify($current_password, $user_data['password'])) {
            if ($new_password_input === $confirm_password) {
                if (strlen($new_password_input) >= 6) {
                    $hashed_password = password_hash($new_password_input, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->bind_param("si", $hashed_password, $target_user_id);
                    
                    if ($stmt->execute()) {
                        $success = true;
                        $new_password = $new_password_input; // For display
                    }
                    $stmt->close();
                } else {
                    $error = "New password must be at least 6 characters long";
                }
            } else {
                $error = "New password and confirm password do not match";
            }
        } else {
            $error = "Current password is incorrect";
        }
    } else {
        // Other user - reset password with random password
        $new_password = generateRandomPassword(8);
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $target_user_id);
        
        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = "Failed to reset password: " . $conn->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $action_type === 'change' ? 'Change Password' : 'Reset Password'; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f4f4;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 600px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .page-title {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-info {
            background: #e3f2fd;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid #2196F3;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .info-label {
            font-size: 0.875rem;
            color: #666;
            font-weight: 500;
        }
        
        .info-value {
            font-size: 1rem;
            color: #333;
            font-weight: 600;
        }
        
        .account-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
            display: inline-block;
        }
        
        .badge-super-admin {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #000;
        }
        
        .badge-admin {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .badge-client {
            background: linear-gradient(135deg, #6c757d, #868e96);
            color: white;
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
        
        .success-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid #28a745;
        }
        
        .password-display {
            background: #f8f9fa;
            border: 2px dashed #6c757d;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
            text-align: center;
        }
        
        .password-text {
            font-size: 1.5rem;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 1rem;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }
        
        .copy-btn {
            background: #17a2b8;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 auto;
            transition: all 0.3s;
        }
        
        .copy-btn:hover {
            background: #138496;
            transform: translateY(-2px);
        }
        
        .copy-btn.copied {
            background: #28a745;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s;
        }
        
        .btn-reset {
            background: #17a2b8;
            color: white;
        }
        
        .btn-reset:hover {
            background: #138496;
            transform: translateY(-2px);
        }
        
        .btn-change {
            background: #28a745;
            color: white;
        }
        
        .btn-change:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
        }
        
        .btn-cancel:hover {
            background: #545b62;
            transform: translateY(-2px);
        }
        
        .permission-info {
            background: #e3f2fd;
            color: #0c5460;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
            border-left: 4px solid #17a2b8;
        }
        
        .note {
            background: #fff3cd;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
            font-size: 0.9rem;
            text-align: center;
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
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-input:focus {
            border-color: #17a2b8;
            outline: none;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-title">
            <i class="fas fa-key"></i>
            <?php echo $action_type === 'change' ? 'Change My Password' : 'Reset User Password'; ?>
        </div>

        <div class="user-info">
            <h3><i class="fas fa-user"></i> User Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($target_user['name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Username</span>
                    <span class="info-value"><?php echo htmlspecialchars($target_user['username']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Account Type</span>
                    <span class="info-value">
                        <span class="account-badge <?php echo 'badge-' . str_replace('_', '-', $target_user['account_type']); ?>">
                            <?php echo getUserDisplayName($target_user['account_type']); ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <div class="permission-info">
            <i class="fas fa-info-circle"></i>
            <strong>Action Type:</strong> 
            <?php 
            if ($is_own_account) {
                echo 'Changing your own password';
            } elseif ($is_super_admin) {
                echo 'Resetting password as Super Admin';
            } else {
                echo 'Resetting Client password as Admin';
            }
            ?>
        </div>

        <?php if ($success): ?>
            <!-- SUCCESS MESSAGE -->
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> 
                    <?php echo $action_type === 'change' ? 'Password Changed Successfully!' : 'Password Reset Successful!'; ?>
                </h4>
                <p>
                    <?php if ($action_type === 'change'): ?>
                        Your password has been changed successfully. You can now use your new password to login.
                    <?php else: ?>
                        The password for <strong><?php echo htmlspecialchars($target_user['name']); ?></strong> has been reset successfully.
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($action_type === 'reset'): ?>
                <!-- PASSWORD DISPLAY FOR RESET ACTION -->
                <div class="password-display">
                    <div class="password-text" id="passwordText"><?php echo $new_password; ?></div>
                    <button class="copy-btn" onclick="copyPassword()" id="copyButton">
                        <i class="fas fa-copy"></i>
                        Copy Password
                    </button>
                </div>

                <div class="note">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Important:</strong> Copy this password and share it securely with the user. 
                    The user will need to use this new password to login.
                </div>
            <?php endif; ?>

            <div class="form-actions">
                <a href="user_list.php" class="btn btn-cancel">
                    <i class="fas fa-arrow-left"></i>
                    Back to User List
                </a>
                <?php if ($action_type === 'reset'): ?>
                    <a href="reset_password.php?user_id=<?php echo $target_user_id; ?>" class="btn btn-reset">
                        <i class="fas fa-sync-alt"></i>
                        Reset Again
                    </a>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- PASSWORD FORM -->
            <?php if ($action_type === 'change'): ?>
                <!-- CHANGE OWN PASSWORD FORM -->
                <div class="warning-box">
                    <h4><i class="fas fa-exclamation-triangle"></i> Change Your Password</h4>
                    <p>You are changing your own password. Please enter your current password and choose a new one.</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label" for="current_password">
                            <i class="fas fa-lock"></i> Current Password
                        </label>
                        <input type="password" id="current_password" name="current_password" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_password">
                            <i class="fas fa-key"></i> New Password (min 6 characters)
                        </label>
                        <input type="password" id="new_password" name="new_password" class="form-input" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">
                            <i class="fas fa-key"></i> Confirm New Password
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" required minlength="6">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-change">
                            <i class="fas fa-save"></i>
                            Change Password
                        </button>
                        <a href="user_list.php" class="btn btn-cancel">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                    </div>
                </form>

            <?php else: ?>
                <!-- RESET OTHER USER'S PASSWORD FORM -->
                <div class="warning-box">
                    <h4><i class="fas fa-exclamation-triangle"></i> Warning</h4>
                    <p>You are about to reset the password for <strong><?php echo htmlspecialchars($target_user['name']); ?></strong>.</p>
                    <p>This action will:</p>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li>Generate a new random password</li>
                        <li>Immediately change the user's password</li>
                        <li>Show you the new password (share it securely with the user)</li>
                        <li>The user will need to use the new password to login</li>
                    </ul>
                </div>

                <form method="POST" action="">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-reset">
                            <i class="fas fa-sync-alt"></i>
                            Reset Password
                        </button>
                        <a href="user_list.php" class="btn btn-cancel">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        function copyPassword() {
            const passwordText = document.getElementById('passwordText');
            const copyButton = document.getElementById('copyButton');
            
            // Create a temporary textarea to copy from
            const textarea = document.createElement('textarea');
            textarea.value = passwordText.textContent;
            document.body.appendChild(textarea);
            
            // Select and copy the text
            textarea.select();
            textarea.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    // Change button to show success
                    copyButton.innerHTML = '<i class="fas fa-check"></i> Copied!';
                    copyButton.classList.add('copied');
                    
  