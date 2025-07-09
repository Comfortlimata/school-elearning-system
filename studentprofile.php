<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$username = $_SESSION['username'];
$student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE username = '".mysqli_real_escape_string($conn, $username)."' LIMIT 1"));

// Handle profile update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $update = mysqli_query($conn, "UPDATE students SET full_name='$full_name', email='$email' WHERE username='$username'");
    if ($update) {
        $message = '<div class="alert alert-success">Profile updated successfully.</div>';
        $student['full_name'] = $full_name;
        $student['email'] = $email;
    } else {
        $message = '<div class="alert alert-danger">Failed to update profile.</div>';
    }
}
// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    if (password_verify($current, $student['password'])) {
        if ($new === $confirm && strlen($new) >= 6) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $update = mysqli_query($conn, "UPDATE students SET password='$hashed' WHERE username='$username'");
            if ($update) {
                $message = '<div class="alert alert-success">Password changed successfully.</div>';
            } else {
                $message = '<div class="alert alert-danger">Failed to change password.</div>';
            }
        } else {
            $message = '<div class="alert alert-warning">Passwords do not match or are too short (min 6 chars).</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Current password is incorrect.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Profile - Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --light-color: #f8fafc;
            --border-color: #e5e7eb;
        }
        * { font-family: 'Poppins', sans-serif; }
        body { background: var(--light-color); }
        .main-content { margin-left: 280px; padding: 2rem; }
    </style>
</head>
<body>
    <?php include 'studentsidebar.php'; ?>
    <div class="main-content">
        <h2 class="mb-4"><i class="fas fa-user me-2"></i>My Profile</h2>
        <?php echo $message; ?>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white"><strong>Personal Information</strong></div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Grade</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['grade_id']); ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Section</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['section']); ?>" disabled>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white"><strong>Change Password</strong></div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-secondary">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 