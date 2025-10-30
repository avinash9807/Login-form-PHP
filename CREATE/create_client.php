<?php
// CREATE/create_client.php - FIXED VERSION WITH DIRECT ACCESS
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session start karo
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../config.php';

// Check if admin verified YA direct logged in as admin/super_admin - FIXED
if (!isset($_SESSION['admin_verified']) && !(isAdmin() && isset($_SESSION['user_id']))) {
    header("Location: ../AUTH/verify_admin.php");
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
        
        // Check if username already exists (case-sensitive)
        $stmt = $conn->prepare("SELECT id FROM users WHERE BINARY username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "❌ Username or Email already exists!";
        } else {
            // Insert new client with name field and created_by tracking - FIXED
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, username, email, password, account_type, created_by) VALUES (?, ?, ?, ?, 'client', ?)");
            
            // Get the Admin ID who is creating this client - FIXED
            if (isset($_SESSION['verified_user_id'])) {
                $created_by = $_SESSION['verified_user_id']; // Verification se aaya hai
            } else {
                $created_by = $_SESSION['user_id']; // Direct logged in Admin/Super Admin
            }
            $stmt->bind_param("ssssi", $name, $username, $email, $hashed_password, $created_by);
            
            if ($stmt->execute()) {
                $success = "✅ ".DISPLAY_CLIENT." account created successfully!";
                // Clear verification only if came from verification
                if (isset($_SESSION['admin_verified'])) {
                    unset($_SESSION['admin_verified']);
                    unset($_SESSION['verified_admin']);
                    unset($_SESSION['verified_admin_name']);
                    unset($_SESSION['verified_admin_type']);
                    unset($_SESSION['verified_user_id']);
                }
            } else {
                $error = "❌ Failed to create client account! " . $conn->error;
            }
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
    <title>Create Client - <?php echo SITE_NAME; ?></title>
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
            margin-bottom: 10px;
            font-size: 24px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
            font-size: 14px;
            line-height: 1.4;
        }
        .verified-info {
            background: #d4edda;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #28a745;
            font-size: 14px;
        }
        .direct-access-info {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #ffc107;
            font-size: 14px;
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
        input:focus {
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
            background: #6c757d;
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
            background: #545b62;
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
        .back-btn {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            padding: 10px;
            transition: color 0.3s;
        }
        .back-btn:hover {
            color: #333;
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

        /* Contact Us Button */
        .contact-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 1000;
            transition: all 0.3s;
        }
        .contact-btn:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        .social-popup {
            position: fixed;
            bottom: 70px;
            right: 20px;
            background: white;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            display: none;
            flex-direction: column;
            gap: 10px;
            z-index: 1001;
        }
        .social-popup.show {
            display: flex;
        }
        .social-icon-popup {
            font-size: 24px;
            text-decoration: none;
            padding: 10px;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        .social-icon-popup:hover {
            transform: scale(1.1);
        }
        .telegram { background: #0088cc; color: white; }
        .whatsapp { background: #25D366; color: white; }
        .instagram { background: #E4405F; color: white; }
        .twitter { background: #1DA1F2; color: white; }
        .youtube { background: #FF0000; color: white; }
        .facebook { background: #1877F2; color: white; }

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
            .contact-btn {
                bottom: 15px;
                right: 15px;
                padding: 10px 15px;
                font-size: 14px;
            }
            .social-popup {
                bottom: 60px;
                right: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Contact Us Button & Social Popup -->
    <a href="#" class="contact-btn" onclick="toggleSocialPopup()">📞 Contact Us</a>
    <div class="social-popup" id="socialPopup">
        <?php if (SOCIAL_TELEGRAM): ?>
            <a href="<?php echo SOCIAL_TELEGRAM_URL; ?>" target="_blank" class="social-icon-popup telegram" title="<?php echo SOCIAL_TELEGRAM_TEXT; ?>">📱</a>
        <?php endif; ?>
        
        <?php if (SOCIAL_WHATSAPP): ?>
            <a href="<?php echo SOCIAL_WHATSAPP_URL; ?>" target="_blank" class="social-icon-popup whatsapp" title="<?php echo SOCIAL_WHATSAPP_TEXT; ?>">💬</a>
        <?php endif; ?>
        
        <?php if (SOCIAL_INSTAGRAM): ?>
            <a href="<?php echo SOCIAL_INSTAGRAM_URL; ?>" target="_blank" class="social-icon-popup instagram" title="<?php echo SOCIAL_INSTAGRAM_TEXT; ?>">📸</a>
        <?php endif; ?>
        
        <?php if (SOCIAL_TWITTER): ?>
            <a href="<?php echo SOCIAL_TWITTER_URL; ?>" target="_blank" class="social-icon-popup twitter" title="<?php echo SOCIAL_TWITTER_TEXT; ?>">🐦</a>
        <?php endif; ?>
        
        <?php if (SOCIAL_YOUTUBE): ?>
            <a href="<?php echo SOCIAL_YOUTUBE_URL; ?>" target="_blank" class="social-icon-popup youtube" title="<?php echo SOCIAL_YOUTUBE_TEXT; ?>">🎥</a>
        <?php endif; ?>
        
        <?php if (SOCIAL_FACEBOOK): ?>
            <a href="<?php echo SOCIAL_FACEBOOK_URL; ?>" target="_blank" class="social-icon-popup facebook" title="<?php echo SOCIAL_FACEBOOK_TEXT; ?>">📘</a>
        <?php endif; ?>
    </div>

    <div class="container">
        <h2>👥 Create Client Account</h2>
        <p class="subtitle">Create a new client account for <?php echo SITE_NAME; ?></p>
        
        <?php if (isset($_SESSION['verified_admin'])): ?>
            <div class="verified-info">
                ✅ Verified as: <strong><?php echo $_SESSION['verified_admin']; ?></strong> 
                (<?php echo $_SESSION['verified_admin_type'] == 'super_admin' ? DISPLAY_SUPER_ADMIN : DISPLAY_ADMIN; ?>)
            </div>
        <?php elseif (isAdmin() && isset($_SESSION['user_id'])): ?>
            <div class="direct-access-info">
                ✅ Direct Access as: <strong><?php echo $_SESSION['username']; ?></strong> 
                (<?php echo isSuperAdmin() ? DISPLAY_SUPER_ADMIN : DISPLAY_ADMIN; ?>)<br>
                <small>You are directly creating client accounts without re-verification.</small>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
            <p style="text-align:center; margin-top:10px;">
                <a href="../DASHBOARD/dashboard.php" style="color: #28a745; text-decoration: none; margin: 0 10px;">Go to Dashboard →</a>
                <a href="create_client.php" style="color: #007bff; text-decoration: none; margin: 0 10px;">Create Another Client →</a>
            </p>
        <?php else: ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">👤 Client Full Name:</label>
                <input type="text" id="name" name="name" placeholder="Enter client full name" required>
            </div>
            
            <div class="form-group">
                <label for="username">🔑 Client Username:</label>
                <input type="text" id="username" name="username" placeholder="Enter client username" required>
                <div class="note">Only letters, numbers, and underscores allowed. Spaces will be converted to underscores.</div>
            </div>
            
            <div class="form-group">
                <label for="email">📧 Client Email:</label>
                <input type="email" id="email" name="email" placeholder="Enter client email" required>
            </div>
            
            <div class="form-group">
                <label for="password">🔒 Client Password:</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter password (min 6 characters)" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">👁️</button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">✅ Confirm Password:</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">👁️</button>
                </div>
            </div>
            
            <button type="submit">🚀 Create <?php echo DISPLAY_CLIENT; ?> Account</button>
        </form>

        <a href="../DASHBOARD/dashboard.php" class="back-btn">← Back to Dashboard</a>
        
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

        function toggleSocialPopup() {
            event.preventDefault();
            const popup = document.getElementById('socialPopup');
            popup.classList.toggle('show');
        }

        // Close popup when clicking outside
        document.addEventListener('click', function(event) {
            const popup = document.getElementById('socialPopup');
            const contactBtn = document.querySelector('.contact-btn');
            
            if (!popup.contains(event.target) && !contactBtn.contains(event.target)) {
                popup.classList.remove('show');
            }
        });
    </script>
</body>
</html>