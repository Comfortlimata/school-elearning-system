<?php
session_start();
$message = $_SESSION['loginMessage'] ?? '';
$success_message = $_SESSION['successMessage'] ?? '';
unset($_SESSION['loginMessage']);
unset($_SESSION['successMessage']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Miles e-School Academy</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="style.css">
    
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg,rgb(126, 98, 89) 0%,rgb(245, 245, 239) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: rgba(19, 37, 50, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .login-header::before {
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
        
        .login-header-content {
            position: relative;
            z-index: 2;
        }
        
        .login-icon {
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
        
        .login-body {
            padding: 2rem;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-floating .form-control {
            border-radius: 10px;
            border: 2px solidrgb(50, 220, 144);
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
            color:rgb(242, 243, 247);
        }
        
        .form-floating .form-control:focus ~ label,
        .form-floating .form-control:not(:placeholder-shown) ~ label {
            color: var(--primary-color);
            transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 1rem;
        }
        
        .forgot-password a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .forgot-password a:hover {
            color: var(--secondary-color);
        }
        
        .back-to-home {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .back-to-home a {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .back-to-home a:hover {
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
        
        .user-type-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 1.5rem;
        }
        
        .user-type-option {
            flex: 1;
            text-align: center;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .user-type-option.active {
            border-color: var(--primary-color);
            background: var(--primary-color);
            color: white;
        }
        
        .user-type-option:hover {
            border-color: var(--primary-color);
        }
        
        .user-type-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .hidden {
            display: none !important;
        }
        
        @media (max-width: 576px) {
            .login-card {
                margin: 10px;
            }
            
            .login-header,
            .login-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-header-content">
                    <div class="login-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h2>Welcome Back</h2>
                    <p class="mb-0">Sign in to your account</p>
                </div>
            </div>
            
            <div class="login-body">
                <?php if ($message): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <form action="login_check.php" method="POST" id="loginForm">
                    <!-- User Type Selector -->
                    <div class="user-type-selector">
                        <div class="user-type-option active" data-type="admin">
                            <div class="user-type-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div>Admin</div>
                        </div>
                        <div class="user-type-option" data-type="teacher">
                            <div class="user-type-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div>Teacher</div>
                        </div>
                        <div class="user-type-option" data-type="student">
                            <div class="user-type-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div>Student</div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="usertype" id="usertype" value="admin">
                    
                    <!-- Username Field -->
                    <div class="form-floating">
                        <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
                        <label for="username">
                            <i class="fas fa-user me-2"></i>Username
                        </label>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="form-floating">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                        <label for="password">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                    </div>
                    
                    <!-- Login Button -->
                    <button type="submit" name="submit" class="btn btn-primary btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                </form>
                
                <!-- Forgot Password Link -->
                <div class="forgot-password">
                    <a href="forgot_password.php">
                        <i class="fas fa-key me-1"></i>Forgot your password?
                    </a>
                </div>
                
                <!-- Back to Home -->
                <div class="back-to-home">
                    <a href="index.php">
                        <i class="fas fa-arrow-left me-1"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // User type selector functionality
        const userTypeOptions = document.querySelectorAll('.user-type-option');
        const usertypeInput = document.getElementById('usertype');
        
        userTypeOptions.forEach(option => {
            option.addEventListener('click', () => {
                // Remove active class from all options
                userTypeOptions.forEach(opt => opt.classList.remove('active'));
                
                // Add active class to clicked option
                option.classList.add('active');
                
                // Update hidden input
                const selectedType = option.dataset.type;
                usertypeInput.value = selectedType;
            });
        });
        
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const usertype = usertypeInput.value;
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return;
            }
        });
        
        // Add loading state to form submission
        document.getElementById('loginForm').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>
