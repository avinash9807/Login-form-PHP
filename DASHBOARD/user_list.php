<?php
// DASHBOARD/user_list.php - FIXED BUTTON TEXT VERSION
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

// FIX: Define admin variables properly
$is_super_admin = isSuperAdmin();
$is_admin = isAdmin();

// Success/Error messages
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Get all users from database
$conn = getDBConnection();
$sql = "SELECT u.id, u.name, u.username, u.email, u.account_type, u.created_at, 
               creator.name as creator_name 
        FROM users u 
        LEFT JOIN users creator ON u.created_by = creator.id 
        ORDER BY u.created_at DESC";
$result = $conn->query($sql);

$users = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #667eea;
            --primary-dark: #764ba2;
            --secondary: #f8f9fa;
            --accent: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --text: #2c3e50;
            --text-light: #7f8c8d;
            --shadow: 0 10px 30px rgba(0,0,0,0.1);
            --radius: 12px;
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        /* Header Styles */
        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logo {
            font-size: 2rem;
            background: white;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .header-info h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .user-welcome {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .user-badge {
            background: rgba(255,255,255,0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        /* Main Content */
        .main-content {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light);
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .back-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: var(--radius);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
        }
        
        /* Messages */
        .message {
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .success-message {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left: 4px solid var(--accent);
        }
        
        .error-message {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left: 4px solid var(--danger);
        }
        
        /* Table Styles */
        .table-container {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .table-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 1.5rem 2rem;
        }
        
        .table-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .users-table th {
            background: var(--light);
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: var(--text);
            border-bottom: 2px solid var(--light);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .users-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--light);
            transition: var(--transition);
        }
        
        .users-table tr:hover td {
            background: #f8f9fa;
        }
        
        .users-table tr:last-child td {
            border-bottom: none;
        }
        
        /* Badges */
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
            background: linear-gradient(135deg, var(--accent), #20c997);
            color: white;
        }
        
        .badge-client {
            background: linear-gradient(135deg, #6c757d, #868e96);
            color: white;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            text-align: center;
        }
        
        .btn-reset {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
        }
        
        .btn-reset:hover {
            transform: translateY(-2px);
        }
        
        .btn-change {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .btn-change:hover {
            transform: translateY(-2px);
        }
        
        .btn-delete {
            background: linear-gradient(135deg, var(--danger), #e74c3c);
            color: white;
        }
        
        .btn-delete:hover {
            transform: translateY(-2px);
        }
        
        .btn:disabled {
            background: var(--text-light);
            cursor: not-allowed;
            transform: none;
        }
        
        /* No Users Message */
        .no-users {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--text-light);
        }
        
        .no-users-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .no-users h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--text);
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header {
                padding: 1rem;
            }
            
            .header-content {
                flex-wrap: wrap;
                gap: 1rem;
            }
            
            .header-info h1 {
                font-size: 1.25rem;
            }
            
            .main-content {
                padding: 0 1rem;
                margin: 1rem auto;
            }
            
            .page-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .users-table {
                min-width: 800px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <div class="logo">
                    <i class="fas fa-gamepad"></i>
                </div>
                <div class="header-info">
                    <h1><?php echo SITE_NAME; ?> - User Management</h1>
                    <div class="user-welcome">
                        Welcome, <strong><?php echo htmlspecialchars($name); ?></strong>
                        <span class="user-badge"><?php echo getUserDisplayName($account_type); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <div class="page-title">
                <i class="fas fa-users"></i>
                User List
            </div>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>

        <?php if ($success): ?>
            <div class="message success-message">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <div class="table-header">
                <h3><i class="fas fa-list"></i> All System Users</h3>
            </div>
            
            <?php if (count($users) > 0): ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Account Type</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php 
                                    $badge_class = '';
                                    switch($user['account_type']) {
                                        case 'super_admin': $badge_class = 'badge-super-admin'; break;
                                        case 'admin': $badge_class = 'badge-admin'; break;
                                        case 'client': $badge_class = 'badge-client'; break;
                                    }
                                    ?>
                                    <span class="account-badge <?php echo $badge_class; ?>">
                                        <?php echo getUserDisplayName($user['account_type']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['creator_name'] ?? 'System'); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- Password Management Button -->
                                        <?php 
                                        $is_own_account = ($user['id'] == $user_id);
                                        $button_text = $is_own_account ? 'Change' : 'Reset';
                                        $button_class = $is_own_account ? 'btn-change' : 'btn-reset';
                                        $button_icon = $is_own_account ? 'fa-edit' : 'fa-key';
                                        ?>
                                        
                                        <?php if ($is_super_admin || ($is_admin && $user['account_type'] == 'client') || $is_own_account): ?>
                                            <a href="reset_password.php?user_id=<?php echo $user['id']; ?>" class="btn <?php echo $button_class; ?>">
                                                <i class="fas <?php echo $button_icon; ?>"></i> 
                                                <?php echo $button_text; ?>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <!-- Delete Button -->
                                        <?php if ($is_super_admin && $user['id'] != $user_id): ?>
                                            <a href="delete_user.php?user_id=<?php echo $user['id']; ?>" class="btn btn-delete">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        <?php elseif ($is_admin && $user['account_type'] == 'client' && $user['id'] != $user_id): ?>
                                            <a href="delete_user.php?user_id=<?php echo $user['id']; ?>" class="btn btn-delete">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        <?php else: ?>
                                            <button class="btn" disabled>
                                                <i class="fas fa-ban"></i> Delete
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-users">
                    <div class="no-users-icon">
                        <i class="fas fa-users-slash"></i>
                    </div>
                    <h3>No Users Found</h3>
                    <p>There are no users in the system yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>