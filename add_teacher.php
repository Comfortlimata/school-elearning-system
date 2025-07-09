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
$subjects_result = mysqli_query($conn, "SELECT id, name FROM subjects ORDER BY name");
$sections = ['A', 'B', 'C', 'D', 'E'];

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $qualification = mysqli_real_escape_string($conn, $_POST['qualification'] ?? '');
    $grades = $_POST['grades'] ?? [];
    $sections_selected = $_POST['sections'] ?? [];
    $subjects = $_POST['subjects'] ?? [];
    
    if (!$username || !$email || !$password || !$name || !$qualification || empty($grades) || empty($sections_selected) || empty($subjects)) {
        $message = "Please fill in all required fields and select at least one grade, section, and subject.";
    } else {
        $check_user = mysqli_query($conn, "SELECT id FROM teacher WHERE username = '$username' OR email = '$email'");
        if (mysqli_num_rows($check_user) > 0) {
            $message = "Username or email already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            mysqli_begin_transaction($conn);
            try {
                $insert_sql = "INSERT INTO teacher (username, password, name, email, qualification, status) VALUES (?, ?, ?, ?, ?, 'active')";
                $stmt = mysqli_prepare($conn, $insert_sql);
                mysqli_stmt_bind_param($stmt, "sssss", $username, $hashed_password, $name, $email, $qualification);
                if (mysqli_stmt_execute($stmt)) {
                    $teacher_id = mysqli_insert_id($conn);
                    $count = 0;
                    foreach ($grades as $grade_id) {
                        foreach ($sections_selected as $section) {
                            foreach ($subjects as $subject_id) {
                                // Only insert if this grade-section-subject exists in grade_subject_assignments
                                $check = mysqli_query($conn, "SELECT id FROM grade_subject_assignments WHERE grade_id = $grade_id AND section = '".mysqli_real_escape_string($conn, $section)."' AND subject_id = $subject_id");
                                if (mysqli_num_rows($check) > 0) {
                                    $ins = mysqli_query($conn, "INSERT IGNORE INTO teacher_grade_subjects (teacher_id, grade_id, subject_id) VALUES ($teacher_id, $grade_id, $subject_id)");
                                    if ($ins) $count++;
                                }
                            }
                        }
                    }
                    mysqli_commit($conn);
                    $message = "Teacher added and assigned to $count grade-section-subject combinations!";
                    $_POST = array();
                } else {
                    throw new Exception("Error adding teacher: " . mysqli_error($conn));
                }
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $message = $e->getMessage();
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
    <title>Add Teacher - Admin Panel</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-section { margin-bottom: 1.5rem; }
        .multi-select { min-height: 120px; }
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
                    <h5><i class="fas fa-chalkboard-teacher me-2"></i>Add New Teacher</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo strpos($message, 'successfully') !== false || strpos($message, 'added') !== false ? 'success' : 'danger'; ?>">
                            <i class="fas fa-<?php echo strpos($message, 'successfully') !== false || strpos($message, 'added') !== false ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="add_teacher.php">
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
                                <input type="text" class="form-control" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Password *</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Qualification *</label>
                                <input type="text" class="form-control" name="qualification" value="<?php echo isset($_POST['qualification']) ? htmlspecialchars($_POST['qualification']) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="form-section">
                            <div class="form-group">
                                <label class="form-label">Assign Grades *</label>
                                <select name="grades[]" class="form-control multi-select" multiple required>
                                    <?php if ($grades_result) mysqli_data_seek($grades_result, 0); while ($grade = mysqli_fetch_assoc($grades_result)): ?>
                                        <option value="<?php echo $grade['id']; ?>" <?php echo (isset($_POST['grades']) && in_array($grade['id'], $_POST['grades'])) ? 'selected' : ''; ?>><?php echo htmlspecialchars($grade['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Assign Sections *</label>
                                <select name="sections[]" class="form-control multi-select" multiple required>
                                    <?php foreach ($sections as $section): ?>
                                        <option value="<?php echo $section; ?>" <?php echo (isset($_POST['sections']) && in_array($section, $_POST['sections'])) ? 'selected' : ''; ?>><?php echo $section; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Assign Subjects *</label>
                                <select name="subjects[]" class="form-control multi-select" multiple required>
                                    <?php if ($subjects_result) mysqli_data_seek($subjects_result, 0); while ($subject = mysqli_fetch_assoc($subjects_result)): ?>
                                        <option value="<?php echo $subject['id']; ?>" <?php echo (isset($_POST['subjects']) && in_array($subject['id'], $_POST['subjects'])) ? 'selected' : ''; ?>><?php echo htmlspecialchars($subject['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Teacher</button>
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