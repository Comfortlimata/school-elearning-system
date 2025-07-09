<?php
session_start();

// Only admins can access
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$grades_result = mysqli_query($conn, "SELECT id, name FROM grades ORDER BY id");
$sections = ['A', 'B', 'C', 'D', 'E'];

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name'] ?? '');
    $grade_id = $_POST['grade_id'] ?? '';
    $section = $_POST['section'] ?? '';
    
    if (!$username || !$email || !$password || !$full_name || !$grade_id || !$section) {
        $message = "Please fill in all required fields.";
    } else {
        $check_user = mysqli_query($conn, "SELECT id FROM students WHERE username = '$username' OR email = '$email'");
        if (mysqli_num_rows($check_user) > 0) {
            $message = "Username or email already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO students (username, email, password, full_name, grade_id, section) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($stmt, "ssssis", $username, $email, $hashed_password, $full_name, $grade_id, $section);
            if (mysqli_stmt_execute($stmt)) {
                $student_id = mysqli_insert_id($conn);
                // Link student to all teachers for their grade and section
                $subjects = mysqli_query($conn, "SELECT subject_id FROM grade_subject_assignments WHERE grade_id = $grade_id AND section = '".mysqli_real_escape_string($conn, $section)."'");
                $link_count = 0;
                while ($row = mysqli_fetch_assoc($subjects)) {
                    $subject_id = $row['subject_id'];
                    $teachers = mysqli_query($conn, "SELECT teacher_id FROM teacher_grade_subjects WHERE grade_id = $grade_id AND subject_id = $subject_id");
                    while ($t = mysqli_fetch_assoc($teachers)) {
                        $teacher_id = $t['teacher_id'];
                        $ins = mysqli_query($conn, "INSERT IGNORE INTO student_teacher_subject (student_id, teacher_id, subject_id) VALUES ($student_id, $teacher_id, $subject_id)");
                        if ($ins) $link_count++;
                    }
                }
                $message = "Student added and linked to $link_count teacher-subject(s)!";
                $_POST = array();
    } else {
            $message = "Error adding student: " . mysqli_error($conn);
        }
    }
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Student - Admin Panel</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-section { margin-bottom: 1.5rem; }
        .card { background: #fff; border-radius: 12px; box-shadow: var(--shadow-md); padding: 2rem; }
        .form-label { font-weight: 500; }
    </style>
</head>
<body>
<?php include 'adminsidebar.php'; ?>
    <div class="content fade-in">
    <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-user-graduate me-2"></i>Add New Student</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo strpos($message, 'added') !== false ? 'success' : 'danger'; ?>">
                            <i class="fas fa-<?php echo strpos($message, 'added') !== false ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="add_student.php">
                        <div class="form-section">
                                    <div class="form-group">
                                <label class="form-label">Username *</label>
                                <input type="text" class="form-control" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                            </div>
                                    <div class="form-group">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                                    <div class="form-group">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="full_name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                            </div>
                                    <div class="form-group">
                                <label class="form-label">Password *</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                        </div>
                        <div class="form-section">
                            <div class="form-group">
                                <label class="form-label">Grade *</label>
                                <select name="grade_id" class="form-control" required>
                                    <option value="">-- Select Grade --</option>
                                    <?php if ($grades_result) mysqli_data_seek($grades_result, 0); while ($grade = mysqli_fetch_assoc($grades_result)): ?>
                                        <option value="<?php echo $grade['id']; ?>" <?php echo (isset($_POST['grade_id']) && $_POST['grade_id'] == $grade['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($grade['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Section *</label>
                                <select name="section" class="form-control" required>
                                    <option value="">-- Select Section --</option>
                                    <?php foreach ($sections as $sec): ?>
                                        <option value="<?php echo $sec; ?>" <?php echo (isset($_POST['section']) && $_POST['section'] == $sec) ? 'selected' : ''; ?>><?php echo $sec; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Student</button>
                            <a href="adminhome.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>