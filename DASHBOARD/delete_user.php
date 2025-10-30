<?php
// DASHBOARD/delete_user.php - FIXED FOR ADMIN PERMISSIONS
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

// FIXED PERMISSION CHECK: Admin can delete client users
$can_delete = false;
if ($is_super_admin) {
    // Super Admin can delete anyone (except themselves)
    if ($target_user_id != $user_id) {
        $can_delete = true;
    }
} elseif ($is_admin && $target_user['account_type'] == 'client' && $target_user_id != $user_id) {
    // Admin can only delete client users (except themselves)
    $can_delete = true;
}

if (!$can_delete) {
    $conn->close();
    if ($target_user_id == $user_id) {
        header("Location: user_list.php?error=You cannot delete your own account");
    } else {
        header("Location: user_list.php?error=You don't have permission to delete this user");
    }
    exit();
}

// Process user deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if any other users are created by this user
    $stmt = $conn->prepare("SELECT COUNT(*) as user_count FROM users WHERE created_by = ?");
    $stmt->bind_param("i", $target_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_count = $result->fetch_assoc()['user_count'];
    $stmt->close();
    
    if ($user_count > 0) {
        // If user has created other users, update their created_by to NULL
        $stmt = $conn->prepare("UPDATE users SET created_by = NULL WHERE created_by = ?");
        $stmt->bind_param("i", $target_user_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Now delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $target_user_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        
        $success_message = "User " . htmlspecialchars($target_user['name']) . " has been deleted successfully";
        header("Location: user_list.php?success=" . urlencode($success_message));
        exit();
    } else {
        $error = "Failed to delete user: " . $conn->error;
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User - <?php echo SITE_NAME; ?></title>
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
        
        .danger-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid #dc3545;
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
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c82333;
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
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-title">
            <i class="fas fa-trash"></i>
            Delete User Account
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
            <strong>Your Permissions:</strong> 
            <?php echo $is_super_admin ? 'Super Admin (Full Access)' : 'Admin (Client Access Only)'; ?>
        </div>

        <div class="danger-box">
            <h4><i class="fas fa-exclamation-triangle"></i> DANGER: Permanent Deletion</h4>
            <p>You are about to <strong>permanently delete</strong> the account of <strong><?php echo htmlspecialchars($target_user['name']); ?></strong>.</p>
            <p>This action will:</p>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>Permanently remove the user from the system</li>
                <li>Delete all user data</li>
                <li>Cannot be undone</li>
                <li>The user will no longer be able to login</li>
            </ul>
            <p><strong>Are you absolutely sure you want to proceed?</strong></p>
        </div>

        <form method="POST" action="">
            <div class="form-actions">
                <button type="submit" class="btn btn-delete" onclick="return confirm('Are you SURE you want to permanently delete this user? This cannot be undone!')">
                    <i class="fas fa-trash"></i>
                    Delete User Permanently
                </button>
                <a href="user_list.php" class="btn btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</body>
</html>