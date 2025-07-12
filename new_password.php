<?php
session_start();

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$data = mysqli_connect($host, $user, $password, $db);

if (!$data) {
    $_SESSION['forgotMessage'] = "Database connection failed. Please try again.";
    header("Location: forgot_password.php");
    exit();
}

$token = $_GET['token'] ?? '';
$usertype = $_GET['type'] ?? '';
$message = '';
$error = '';

// Validate token and usertype
if (empty($token) || empty($usertype) || !in_array($usertype, ['admin', 'student'])) {
    $error = "Invalid reset link. Please request a new password reset.";
} else {
    // Check if token exists and is not expired
    if ($usertype == 'admin') {
        $sql = "SELECT * FROM admin WHERE reset_token = ? AND reset_expires > NOW()";
    } else {
        $sql = "SELECT * FROM students WHERE reset_token = ? AND reset_expires > NOW()";
    }
    
    $stmt = mysqli_prepare($data, $sql);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        $error = "Reset link has expired or is invalid. Please request a new password reset.";
    }
}

// Handle password reset
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password and clear reset token
        if ($usertype == 'admin') {
            $update_sql = "UPDATE admin SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?";
        } else {
            $update_sql = "UPDATE students SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?";
        }
        
        $update_stmt = mysqli_prepare($data, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "ss", $hashed_password, $token);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $message = "Password updated successfully! You can now login with your new password.";
        } else {
            $error = "Failed to update password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set New Password - Comfort e-School Academy</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="style.css">
    
    <style>
        .new-password-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .new-password-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .new-password-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .new-password-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('row.jpg') center/cover;
            opacity: 0.1;
            z-index: 1;
        }
        
        .new-password-header-content {
            position: relative;
            z-index: 2;
        }
        
        .new-password-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
        }
        
        .new-password-body {
            padding: 2rem;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-floating .form-control {
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            padding: 1rem 0.75rem;
            height: auto;
            font-size: 1rem;
        }
        
        .form-floating .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }
        
        .form-floating label {
            padding: 1rem 0.75rem;
            color: #6b7280;
        }
        
        .form-floating .form-control:focus ~ label,
        .form-floating .form-control:not(:placeholder-shown) ~ label {
            color: var(--primary-color);
            transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
        }
        
        .btn-update {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }
        
        .back-to-login {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .back-to-login a {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .back-to-login a:hover {
            color: var(--primary-color);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 1.5rem;
        }
        
        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.875rem;
        }
        
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #198754; }
        
        @media (max-width: 576px) {
            .new-password-card {
                margin: 10px;
            }
            
            .new-password-header,
            .new-password-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="new-password-container">
        <div class="new-password-card">
            <div class="new-password-header">
                <div class="new-password-header-content">
                    <div class="new-password-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h2>Set New Password</h2>
                    <p class="mb-0">Create a secure password for your account</p>
                </div>
            </div>
            
            <div class="new-password-body">
                <?php if ($error): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <div class="text-center">
                        <a href="forgot_password.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Forgot Password
                        </a>
                    </div>
                <?php elseif ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                    <div class="text-center">
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Go to Login
                        </a>
                    </div>
                <?php else: ?>
                    <form method="POST" id="newPasswordForm">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <input type="hidden" name="usertype" value="<?php echo htmlspecialchars($usertype); ?>">
                        
                        <!-- New Password Field -->
                        <div class="form-floating">
                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="New Password" required>
                            <label for="new_password">
                                <i class="fas fa-lock me-2"></i>New Password
                            </label>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                        
                        <!-- Confirm Password Field -->
                        <div class="form-floating">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm Password" required>
                            <label for="confirm_password">
                                <i class="fas fa-lock me-2"></i>Confirm Password
                            </label>
                        </div>
                        <div class="password-strength" id="passwordMatch"></div>
                        
                        <!-- Update Button -->
                        <button type="submit" name="update_password" class="btn btn-primary btn-update">
                            <i class="fas fa-save me-2"></i>Update Password
                        </button>
                    </form>
                    
                    <!-- Back to Login -->
                    <div class="back-to-login">
                        <a href="login.php">
                            <i class="fas fa-arrow-left me-1"></i>Back to Login
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Password strength checker
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordStrength = document.getElementById('passwordStrength');
        const passwordMatch = document.getElementById('passwordMatch');
        
        function checkPasswordStrength(password) {
            let strength = 0;
            let feedback = [];
            
            if (password.length >= 6) strength++;
            else feedback.push('At least 6 characters');
            
            if (password.match(/[a-z]/)) strength++;
            else feedback.push('Lowercase letter');
            
            if (password.match(/[A-Z]/)) strength++;
            else feedback.push('Uppercase letter');
            
            if (password.match(/[0-9]/)) strength++;
            else feedback.push('Number');
            
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            else feedback.push('Special character');
            
            let strengthText = '';
            let strengthClass = '';
            
            if (strength < 3) {
                strengthText = 'Weak password. Consider adding: ' + feedback.slice(0, 2).join(', ');
                strengthClass = 'strength-weak';
            } else if (strength < 5) {
                strengthText = 'Medium strength password';
                strengthClass = 'strength-medium';
            } else {
                strengthText = 'Strong password!';
                strengthClass = 'strength-strong';
            }
            
            passwordStrength.innerHTML = '<i class="fas fa-shield-alt me-1"></i>' + strengthText;
            passwordStrength.className = 'password-strength ' + strengthClass;
        }
        
        function checkPasswordMatch() {
            const newPass = newPassword.value;
            const confirmPass = confirmPassword.value;
            
            if (confirmPass === '') {
                passwordMatch.innerHTML = '';
                return;
            }
            
            if (newPass === confirmPass) {
                passwordMatch.innerHTML = '<i class="fas fa-check me-1"></i>Passwords match';
                passwordMatch.className = 'password-strength strength-strong';
            } else {
                passwordMatch.innerHTML = '<i class="fas fa-times me-1"></i>Passwords do not match';
                passwordMatch.className = 'password-strength strength-weak';
            }
        }
        
        if (newPassword) {
            newPassword.addEventListener('input', function() {
                checkPasswordStrength(this.value);
                checkPasswordMatch();
            });
        }
        
        if (confirmPassword) {
            confirmPassword.addEventListener('input', checkPasswordMatch);
        }
        
        // Form validation
        const form = document.getElementById('newPasswordForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const newPass = newPassword.value;
                const confirmPass = confirmPassword.value;
                
                if (newPass.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters long.');
                    return;
                }
                
                if (newPass !== confirmPass) {
                    e.preventDefault();
                    alert('Passwords do not match.');
                    return;
                }
            });
        }
        
        // Add loading state to form submission
        if (form) {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
                submitBtn.disabled = true;
            });
        }
    </script>
</body>
</html>
