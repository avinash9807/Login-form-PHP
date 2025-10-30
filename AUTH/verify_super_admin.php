<?php
// AUTH/verify_super_admin.php - MODERN DESIGN VERSION
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session start karo
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../config.php';

// Agar already logged in hai to dashboard redirect karo
if (isset($_SESSION['user_id'])) {
    header("Location: ../DASHBOARD/dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Strict validation - no spaces allowed
    if (empty($username) || empty($password)) {
        $error = "❌ Username and password are required!";
    } elseif (preg_match('/\s/', $username)) {
        $error = "❌ Username cannot contain spaces!";
    } elseif (!isValidUsername($username)) {
        $error = "❌ Username can only contain letters, numbers, and underscores!";
    } else {
        $conn = getDBConnection();
        
        // CASE-SENSITIVE Super Admin verification
        $stmt = $conn->prepare("SELECT id, name, username, password FROM users WHERE BINARY username = ? AND account_type = 'super_admin'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $super_admin = $result->fetch_assoc();
            
            if (password_verify($password, $super_admin['password'])) {
                // Credentials correct, redirect to create admin
                $_SESSION['super_admin_verified'] = true;
                $_SESSION['verified_admin'] = $super_admin['username'];
                $_SESSION['verified_admin_name'] = $super_admin['name'];
                $_SESSION['verified_user_id'] = $super_admin['id'];
                header("Location: ../CREATE/create_admin.php");
                exit();
            } else {
                $error = "❌ Invalid Super Admin password!";
            }
        } else {
            $error = "❌ Super Admin username not found!";
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
    <title>Verify Super Admin - <?php echo SITE_NAME; ?></title>
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
            --dark: #343a40;
            --light: #f8f9fa;
            --text: #333;
            --text-light: #6c757d;
            --shadow: 0 10px 30px rgba(0,0,0,0.1);
            --radius: 12px;
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
        }
        
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo {
            font-size: 48px;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        h2 {
            color: var(--text);
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: 700;
        }
        
        .subtitle {
            color: var(--text-light);
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .info-box {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            padding: 20px;
            border-radius: var(--radius);
            margin-bottom: 25px;
            text-align: center;
            border-left: 4px solid var(--primary);
            font-size: 14px;
            line-height: 1.5;
        }
        
        .info-box strong {
            color: var(--primary-dark);
            display: block;
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text);
            font-weight: 600;
            font-size: 14px;
        }
        
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            color: var(--text-light);
            font-size: 18px;
            z-index: 2;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 15px 15px 15px 50px;
            border: 2px solid #e9ecef;
            border-radius: var(--radius);
            font-size: 16px;
            transition: var(--transition);
            background: var(--light);
            font-family: 'Segoe UI', sans-serif;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
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
            font-size: 18px;
            color: var(--text-light);
            padding: 5px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            border-radius: 50%;
        }
        
        .toggle-password:hover {
            background: #e9ecef;
            color: var(--primary);
        }
        
        .note {
            background: var(--light);
            padding: 10px;
            border-radius: 8px;
            margin-top: 8px;
            font-size: 12px;
            color: var(--text-light);
            border-left: 3px solid var(--warning);
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--accent), #20c997);
            color: white;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: var(--transition);
            margin-top: 10px;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid var(--danger);
            font-size: 14px;
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .back-links {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 25px;
            text-align: center;
        }
        
        .back-btn {
            color: var(--text-light);
            text-decoration: none;
            padding: 12px;
            border-radius: var(--radius);
            transition: var(--transition);
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid #e9ecef;
        }
        
        .back-btn:hover {
            background: var(--light);
            color: var(--primary);
            border-color: var(--primary);
        }
        
        /* Contact Us Button */
        .contact-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--accent);
            color: white;
            padding: 15px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 1000;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .contact-btn:hover {
            background: #218838;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        
        .social-popup {
            position: fixed;
            bottom: 80px;
            right: 20px;
            background: white;
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            display: none;
            flex-direction: column;
            gap: 12px;
            z-index: 1001;
            animation: slideUp 0.3s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .social-popup.show {
            display: flex;
        }
        
        .social-icon-popup {
            font-size: 20px;
            text-decoration: none;
            padding: 12px;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            color: white;
        }
        
        .social-icon-popup:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .telegram { background: #0088cc; }
        .whatsapp { background: #25D366; }
        .instagram { background: #E4405F; }
        .twitter { background: #1DA1F2; }
        .youtube { background: #FF0000; }
        .facebook { background: #1877F2; }
        
        /* Mobile Responsive */
        @media (max-width: 480px) {
            body {
                padding: 15px;
                align-items: flex-start;
                padding-top: 40px;
            }
            
            .container {
                padding: 30px 25px;
                border-radius: 16px;
            }
            
            h2 {
                font-size: 24px;
            }
            
            .logo {
                font-size: 40px;
            }
            
            input[type="text"],
            input[type="password"] {
                padding: 14px 14px 14px 45px;
                font-size: 16px;
            }
            
            .contact-btn {
                bottom: 15px;
                right: 15px;
                padding: 12px 18px;
                font-size: 13px;
            }
            
            .social-popup {
                bottom: 70px;
                right: 15px;
            }
            
            .back-links {
                flex-direction: column;
            }
        }
        
        @media (max-width: 360px) {
            .container {
                padding: 25px 20px;
            }
            
            h2 {
                font-size: 22px;
            }
            
            .logo {
                font-size: 36px;
            }
        }
    </style>
</head>
<body>
    <!-- Contact Us Button & Social Popup -->
    <a href="#" class="contact-btn" onclick="toggleSocialPopup(event)">
        <i class="fas fa-headset"></i>
        Contact Us
    </a>
    <div class="social-popup" id="socialPopup">
        <?php if (SOCIAL_TELEGRAM): ?>
            <a href="<?php echo SOCIAL_TELEGRAM_URL; ?>" target="_blank" class="social-icon-popup telegram" title="<?php echo SOCIAL_TELEGRAM_TEXT; ?>">
                <i class="fab fa-telegram"></i>
            </a>
        <?php endif; ?>
        
        <?php if (SOCIAL_WHATSAPP): ?>
            <a href="<?php echo SOCIAL_WHATSAPP_URL; ?>" target="_blank" class="social-icon-popup whatsapp" title="<?php echo SOCIAL_WHATSAPP_TEXT; ?>">
                <i class="fab fa-whatsapp"></i>
            </a>
        <?php endif; ?>
        
        <?php if (SOCIAL_INSTAGRAM): ?>
            <a href="<?php echo SOCIAL_INSTAGRAM_URL; ?>" target="_blank" class="social-icon-popup instagram" title="<?php echo SOCIAL_INSTAGRAM_TEXT; ?>">
                <i class="fab fa-instagram"></i>
            </a>
        <?php endif; ?>
        
        <?php if (SOCIAL_TWITTER): ?>
            <a href="<?php echo SOCIAL_TWITTER_URL; ?>" target="_blank" class="social-icon-popup twitter" title="<?php echo SOCIAL_TWITTER_TEXT; ?>">
                <i class="fab fa-twitter"></i>
            </a>
        <?php endif; ?>
        
        <?php if (SOCIAL_YOUTUBE): ?>
            <a href="<?php echo SOCIAL_YOUTUBE_URL; ?>" target="_blank" class="social-icon-popup youtube" title="<?php echo SOCIAL_YOUTUBE_TEXT; ?>">
                <i class="fab fa-youtube"></i>
            </a>
        <?php endif; ?>
        
        <?php if (SOCIAL_FACEBOOK): ?>
            <a href="<?php echo SOCIAL_FACEBOOK_URL; ?>" target="_blank" class="social-icon-popup facebook" title="<?php echo SOCIAL_FACEBOOK_TEXT; ?>">
                <i class="fab fa-facebook"></i>
            </a>
        <?php endif; ?>
    </div>

    <div class="container">
        <div class="header">
            <div class="logo">
                <i class="fas fa-crown"></i>
            </div>
            <h2>Super Admin Verification</h2>
            <p class="subtitle">Enter Super Admin credentials to create new Admin</p>
        </div>
        
        <div class="info-box">
            <strong>Super Admin Verification Required</strong>
            Please enter your Super Admin username and password to create Admin accounts.
        </div>
        
        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user-shield"></i>
                    Super Admin Username
                </label>
                <div class="input-group">
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <input type="text" id="username" name="username" placeholder="Enter your Super Admin username" required>
                </div>
                <div class="note">
                    No spaces allowed. Case-sensitive verification.
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                    Super Admin Password
                </label>
                <div class="input-group">
                    <div class="input-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <input type="password" id="password" name="password" placeholder="Enter your Super Admin password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-check-circle"></i>
                Verify & Continue
            </button>
        </form>

        <div class="back-links">
            <a href="login.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Login
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="../DASHBOARD/dashboard.php" class="back-btn">
                    <i class="fas fa-tachometer-alt"></i>
                    Back to Dashboard
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleButton = passwordField.parentElement.querySelector('.toggle-password');
            const icon = toggleButton.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passwordField.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        function toggleSocialPopup(event) {
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

        // Auto focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>