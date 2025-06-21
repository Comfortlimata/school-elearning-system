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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset'])) {
    $email = mysqli_real_escape_string($data, $_POST['email']);
    $usertype = mysqli_real_escape_string($data, $_POST['usertype']);
    $program = isset($_POST['program']) ? mysqli_real_escape_string($data, $_POST['program']) : '';
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['forgotMessage'] = "Please enter a valid email address.";
        header("Location: forgot_password.php");
        exit();
    }
    
    // Check if user exists based on usertype
    if ($usertype == 'admin') {
        $sql = "SELECT * FROM admin WHERE email = ?";
        $stmt = mysqli_prepare($data, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
    } else {
        $sql = "SELECT * FROM students WHERE email = ? AND program = ?";
        $stmt = mysqli_prepare($data, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $email, $program);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store reset token in database
        if ($usertype == 'admin') {
            $update_sql = "UPDATE admin SET reset_token = ?, reset_expires = ? WHERE email = ?";
        } else {
            $update_sql = "UPDATE students SET reset_token = ?, reset_expires = ? WHERE email = ?";
        }
        
        $update_stmt = mysqli_prepare($data, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "sss", $token, $expires, $email);
        
        if (mysqli_stmt_execute($update_stmt)) {
            // Send reset email
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/new_password.php?token=" . $token . "&type=" . $usertype;
            
            $to = $email;
            $subject = "Password Reset Request - Miles e-School Academy";
            $message = "
            <html>
            <head>
                <title>Password Reset</title>
            </head>
            <body>
                <h2>Password Reset Request</h2>
                <p>Hello " . htmlspecialchars($user['name'] ?? $user['username']) . ",</p>
                <p>You have requested to reset your password for your Miles e-School Academy account.</p>
                <p>Click the link below to reset your password:</p>
                <p><a href='$reset_link' style='background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn't request this password reset, please ignore this email.</p>
                <p>Best regards,<br>Miles e-School Academy Team</p>
            </body>
            </html>
            ";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: Miles e-School Academy <noreply@milesacademy.edu>" . "\r\n";
            
            if (mail($to, $subject, $message, $headers)) {
                $_SESSION['forgotSuccess'] = "Password reset link has been sent to your email address. Please check your inbox and spam folder.";
            } else {
                $_SESSION['forgotMessage'] = "Failed to send reset email. Please try again or contact support.";
            }
        } else {
            $_SESSION['forgotMessage'] = "Failed to process reset request. Please try again.";
        }
    } else {
        // Don't reveal if email exists or not for security
        $_SESSION['forgotSuccess'] = "If an account with this email exists, a password reset link has been sent.";
    }
    
    header("Location: forgot_password.php");
    exit();
} else {
    header("Location: forgot_password.php");
    exit();
}
?> 