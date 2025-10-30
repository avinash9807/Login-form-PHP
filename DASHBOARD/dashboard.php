<?php
// DASHBOARD/dashboard.php - UPDATED WITH PASSWORD CHANGE CHECK
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session start karo
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include config
include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../AUTH/login.php");
    exit();
}

// Check if user needs to change password
if (needsPasswordChange($_SESSION['user_id'])) {
    header("Location: ../AUTH/change_forced_password.php");
    exit();
}

// Safely get session values
$username = $_SESSION['username'] ?? 'User';
$name = $_SESSION['name'] ?? $username;
$account_type = $_SESSION['account_type'] ?? 'client';
$user_id = $_SESSION['user_id'];

// Determine user type safely
$is_super_admin = ($account_type == 'super_admin');
$is_admin = ($account_type == 'admin' || $is_super_admin);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Dashboard</title>
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
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-info h1 {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }
        
        .user-welcome {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .user-badge {
            background: rgba(255,255,255,0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        
        .main-content {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .user-info {
            border-left: 4px solid #667eea;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        }
        
        .info-value {
            font-size: 1rem;
            font-weight: 600;
        }
        
        .quick-actions h2 {
            color: #333;
            margin-bottom: 1.5rem;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .action-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            text-decoration: none;
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 1rem;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
        }
        
        .action-icon {
            font-size: 3rem;
        }
        
        .action-card.admin {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
        
        .action-card.client {
            background: linear-gradient(135deg, #6c757d, #868e96);
        }
        
        .logout-section {
            text-align: center;
            margin-top: 3rem;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .main-content {
                padding: 0 1rem;
            }
            
            .dashboard-card {
                padding: 1.5rem;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>🎮 <?php echo SITE_NAME; ?> Dashboard</h1>
                <div class="user-welcome">
                    Welcome, <strong><?php echo htmlspecialchars($name); ?></strong>
                    <span class="user-badge"><?php echo getUserDisplayName($account_type); ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <!-- User Info Card -->
        <div class="dashboard-card user-info">
            <h3>👤 User Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($name); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Username</span>
                    <span class="info-value"><?php echo htmlspecialchars($username); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Account Type</span>
                    <span class="info-value"><?php echo getUserDisplayName($account_type); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">User ID</span>
                    <span class="info-value">#<?php echo $user_id; ?></span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-card quick-actions">
            <h2>🚀 Quick Actions</h2>
            <div class="actions-grid">
                <?php if ($is_super_admin): ?>
                <a href="../CREATE/create_admin.php" class="action-card admin">
                    <div class="action-icon">👑</div>
                    <div class="action-title">Create Admin</div>
                    <div class="action-description">Create new administrator account</div>
                </a>
                <?php endif; ?>
                
                <?php if ($is_admin): ?>
                <a href="../CREATE/create_client.php" class="action-card client">
                    <div class="action-icon">👥</div>
                    <div class="action-title">Create Client</div>
                    <div class="action-description">Create new client account</div>
                </a>
                <?php endif; ?>
                
                <?php if ($is_admin): ?>
                <a href="user_list.php" class="action-card">
                    <div class="action-icon">📋</div>
                    <div class="action-title">User List</div>
                    <div class="action-description">View and manage all users</div>
                </a>
                <?php endif; ?>
                
                <!-- Change Own Password -->
                <a href="reset_password.php?user_id=<?php echo $user_id; ?>" class="action-card">
                    <div class="action-icon">🔒</div>
                    <div class="action-title">Change Password</div>
                    <div class="action-description">Change your account password</div>
                </a>
            </div>
        </div>

        <!-- Logout Section -->
        <div class="logout-section">
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </div>
</body>
</html>