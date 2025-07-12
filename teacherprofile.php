<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'teacher') {
    header("Location: login.php");
    exit();
}
$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) { die("Database connection failed: " . mysqli_connect_error()); }
$teacher_id = $_SESSION['teacher_id'];
// Fetch teacher info
$teacher = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM teacher WHERE id = $teacher_id"));
// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $qualification = mysqli_real_escape_string($conn, $_POST['qualification']);
        $update = mysqli_query($conn, "UPDATE teacher SET name='$name', email='$email', qualification='$qualification' WHERE id=$teacher_id");
        if ($update) {
            $_SESSION['success_message'] = 'Profile updated successfully!';
            header('Location: teacherprofile.php'); exit();
        } else {
            $_SESSION['error_message'] = 'Failed to update profile.';
        }
    }
    if (isset($_POST['change_password'])) {
        $current = mysqli_real_escape_string($conn, $_POST['current_password']);
        $new = mysqli_real_escape_string($conn, $_POST['new_password']);
        $confirm = mysqli_real_escape_string($conn, $_POST['confirm_password']);
        $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT password FROM teacher WHERE id = $teacher_id"));
        if (!password_verify($current, $row['password'])) {
            $_SESSION['error_message'] = 'Current password is incorrect.';
        } elseif ($new !== $confirm) {
            $_SESSION['error_message'] = 'New passwords do not match.';
        } elseif (strlen($new) < 6) {
            $_SESSION['error_message'] = 'New password must be at least 6 characters.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $update = mysqli_query($conn, "UPDATE teacher SET password='$hash' WHERE id=$teacher_id");
            if ($update) {
                $_SESSION['success_message'] = 'Password changed successfully!';
                header('Location: teacherprofile.php'); exit();
            } else {
                $_SESSION['error_message'] = 'Failed to change password.';
            }
        }
    }
}
// Handle add grade form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_grade'])) {
    $grade_name = mysqli_real_escape_string($conn, $_POST['grade_name']);
    if (strlen($grade_name) < 1 || strlen($grade_name) > 10) {
        $_SESSION['error_message'] = 'Grade name must be between 1 and 10 characters.';
    } else {
        $exists = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM grades WHERE name = '$grade_name'"))[0];
        if ($exists) {
            $_SESSION['error_message'] = 'Grade already exists.';
        } else {
            $insert = mysqli_query($conn, "INSERT INTO grades (name) VALUES ('$grade_name')");
            if ($insert) {
                $_SESSION['success_message'] = 'Grade added successfully!';
                header('Location: teacherprofile.php'); exit();
            } else {
                $_SESSION['error_message'] = 'Failed to add grade.';
            }
        }
    }
}
// Fetch grades and subjects for dropdowns
$grades = mysqli_query($conn, "SELECT id, name FROM grades ORDER BY name");
$subjects = mysqli_query($conn, "SELECT id, name FROM subjects ORDER BY name");
// Handle add assignment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_assignment'])) {
    $grade_id = (int)$_POST['grade_id'];
    $section = mysqli_real_escape_string($conn, $_POST['section']);
    $subject_id = (int)$_POST['subject_id'];
    // Check if already assigned
    $exists = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM teacher_grade_subjects WHERE teacher_id = $teacher_id AND grade_id = $grade_id AND subject_id = $subject_id"))[0];
    if ($exists) {
        $_SESSION['error_message'] = 'You are already assigned to this grade/subject.';
    } else {
        // Ensure grade_subject_assignments exists for this grade/section/subject
        $gsa_exists = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM grade_subject_assignments WHERE grade_id = $grade_id AND section = '$section' AND subject_id = $subject_id"))[0];
        if (!$gsa_exists) {
            mysqli_query($conn, "INSERT INTO grade_subject_assignments (grade_id, section, subject_id, is_required, is_elective, credits, description) VALUES ($grade_id, '$section', $subject_id, 1, 0, 1, 'Added by teacher')");
        }
        // Add to teacher_grade_subjects
        $insert = mysqli_query($conn, "INSERT INTO teacher_grade_subjects (teacher_id, grade_id, subject_id) VALUES ($teacher_id, $grade_id, $subject_id)");
        if ($insert) {
            $_SESSION['success_message'] = 'You have been assigned to the new grade/section/subject!';
            header('Location: teacherprofile.php'); exit();
        } else {
            $_SESSION['error_message'] = 'Failed to assign new grade/subject.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Profile - Teacher Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #2563eb; --secondary-color: #1e40af; --light-color: #f8fafc; --border-color: #e5e7eb; }
        * { font-family: 'Poppins', sans-serif; }
        body { background: var(--light-color); }
        .main-content { margin-left: 280px; padding: 2rem; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-brand">Teacher Portal</a>
        </div>
        <div class="sidebar-menu">
            <a href="teacherhome.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
            <a href="teacherclasses.php" class="sidebar-link"><i class="fas fa-users-class"></i>My Classes</a>
            <a href="teachersubjects.php" class="sidebar-link"><i class="fas fa-book"></i>My Subjects</a>
            <a href="teacherassignments.php" class="sidebar-link"><i class="fas fa-tasks"></i>Assignments & Materials</a>
            <a href="teacherperformance.php" class="sidebar-link"><i class="fas fa-chart-bar"></i>Student Performance</a>
            <a href="teacherschedule.php" class="sidebar-link"><i class="fas fa-calendar-alt"></i>Schedule</a>
            <a href="teacherprofile.php" class="sidebar-link active"><i class="fas fa-user"></i>Profile</a>
            <a href="teachernotifications.php" class="sidebar-link"><i class="fas fa-bell"></i>Notifications</a>
        </div>
    </aside>
    <div class="main-content">
        <h2 class="mb-4"><i class="fas fa-user me-2"></i>My Profile</h2>
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Profile Information</div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="update_profile" value="1">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($teacher['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($teacher['username']); ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Qualification</label>
                                <input type="text" name="qualification" class="form-control" value="<?php echo htmlspecialchars($teacher['qualification']); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
                <!-- Assign Teacher to New Grade/Section/Subject Form -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">Assign Yourself to a New Grade/Section/Subject</div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="add_assignment" value="1">
                            <div class="mb-3">
                                <label class="form-label">Grade</label>
                                <select name="grade_id" class="form-select" required>
                                    <option value="">Select Grade</option>
                                    <?php if ($grades) while ($g = mysqli_fetch_assoc($grades)): ?>
                                        <option value="<?php echo $g['id']; ?>"><?php echo htmlspecialchars($g['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Section</label>
                                <input type="text" name="section" class="form-control" maxlength="10" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subject</label>
                                <select name="subject_id" class="form-select" required>
                                    <option value="">Select Subject</option>
                                    <?php if ($subjects) while ($s = mysqli_fetch_assoc($subjects)): ?>
                                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success">Assign</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">Change Password</div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="change_password" value="1">
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
                            <button type="submit" class="btn btn-secondary">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 