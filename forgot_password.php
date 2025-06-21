<?php
session_start();
$message = $_SESSION['forgotMessage'] ?? '';
$success_message = $_SESSION['forgotSuccess'] ?? '';
unset($_SESSION['forgotMessage']);
unset($_SESSION['forgotSuccess']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - Miles e-School Academy</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="style.css">
    
    <style>
        .forgot-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .forgot-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .forgot-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .forgot-header::before {
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
        
        .forgot-header-content {
            position: relative;
            z-index: 2;
        }
        
        .forgot-icon {
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
        
        .forgot-body {
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
        
        .btn-reset {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-reset:hover {
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
        
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border-left: 4px solid #3b82f6;
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
            .forgot-card {
                margin: 10px;
            }
            
            .forgot-header,
            .forgot-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-card">
            <div class="forgot-header">
                <div class="forgot-header-content">
                    <div class="forgot-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <h2>Reset Password</h2>
                    <p class="mb-0">Enter your email to receive reset instructions</p>
                </div>
            </div>
            
            <div class="forgot-body">
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
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>How it works:</strong> Enter your email address and user type. We'll send you a password reset link if your account exists.
                </div>
                
                <form action="reset_password.php" method="POST" id="forgotForm">
                    <!-- User Type Selector -->
                    <div class="user-type-selector">
                        <div class="user-type-option active" data-type="admin">
                            <div class="user-type-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div>Admin</div>
                        </div>
                        <div class="user-type-option" data-type="student">
                            <div class="user-type-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div>Student</div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="usertype" id="usertype" value="admin">
                    
                    <!-- Email Field -->
                    <div class="form-floating">
                        <input type="email" name="email" id="email" class="form-control" placeholder="Email" required>
                        <label for="email">
                            <i class="fas fa-envelope me-2"></i>Email Address
                        </label>
                    </div>
                    
                    <!-- Program Field (Hidden by default) -->
                    <div class="form-floating hidden" id="programField">
                        <select name="program" id="program" class="form-control">
                            <option value="">-- Select Program --</option>
                            <option value="Computer Science">Computer Science</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Mathematics">Mathematics</option>
                            <option value="Business Administration">Business Administration</option>
                        </select>
                        <label for="program">
                            <i class="fas fa-book me-2"></i>Program
                        </label>
                    </div>
                    
                    <!-- Reset Button -->
                    <button type="submit" name="reset" class="btn btn-primary btn-reset">
                        <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                    </button>
                </form>
                
                <!-- Back to Login -->
                <div class="back-to-login">
                    <a href="login.php">
                        <i class="fas fa-arrow-left me-1"></i>Back to Login
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
        const programField = document.getElementById('programField');
        const programSelect = document.getElementById('program');
        
        userTypeOptions.forEach(option => {
            option.addEventListener('click', () => {
                // Remove active class from all options
                userTypeOptions.forEach(opt => opt.classList.remove('active'));
                
                // Add active class to clicked option
                option.classList.add('active');
                
                // Update hidden input
                const selectedType = option.dataset.type;
                usertypeInput.value = selectedType;
                
                // Show/hide program field
                if (selectedType === 'student') {
                    programField.classList.remove('hidden');
                    programSelect.required = true;
                } else {
                    programField.classList.add('hidden');
                    programSelect.required = false;
                    programSelect.value = '';
                }
            });
        });
        
        // Form validation
        document.getElementById('forgotForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const usertype = usertypeInput.value;
            
            if (!email) {
                e.preventDefault();
                alert('Please enter your email address.');
                return;
            }
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return;
            }
            
            if (usertype === 'student' && !programSelect.value) {
                e.preventDefault();
                alert('Please select a program for student password reset.');
                return;
            }
        });
        
        // Add loading state to form submission
        document.getElementById('forgotForm').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html> 