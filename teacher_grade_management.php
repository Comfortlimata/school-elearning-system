<?php
session_start();

// Check if teacher is logged in
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$data = mysqli_connect($host, $user, $password, $db);

if (!$data) {
    die("Database connection failed: " . mysqli_connect_error());
}

$teacher_id = $_SESSION['teacher_id'];

// Handle grade updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_grade'])) {
    $grade_id = (int)$_POST['grade_id'];
    $new_grade = mysqli_real_escape_string($data, $_POST['new_grade']);
    $new_score = (float)$_POST['new_score'];
    $comments = mysqli_real_escape_string($data, $_POST['comments']);
    
    $update_sql = "UPDATE student_grades SET grade = ?, score = ?, comments = ?, graded_date = CURDATE() WHERE id = ?";
    $stmt = mysqli_prepare($data, $update_sql);
    mysqli_stmt_bind_param($stmt, "sdsi", $new_grade, $new_score, $comments, $grade_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Grade updated successfully!";
    } else {
        $error_message = "Failed to update grade: " . mysqli_error($data);
    }
}

// Get teacher's courses and students
$teacher_sql = "SELECT specialization FROM teacher WHERE id = ?";
$stmt = mysqli_prepare($data, $teacher_sql);
mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);
$teacher_result = mysqli_stmt_get_result($stmt);
$teacher = mysqli_fetch_assoc($teacher_result);

// Get student grades for teacher's specialization
$grades_sql = "SELECT sg.*, s.full_name as student_name, c.course_name 
               FROM student_grades sg 
               JOIN students s ON sg.student_id = s.id 
               JOIN courses c ON sg.course_id = c.id 
               WHERE c.program = ? 
               ORDER BY sg.submitted_date DESC";
$stmt = mysqli_prepare($data, $grades_sql);
mysqli_stmt_bind_param($stmt, "s", $teacher['specialization']);
mysqli_stmt_execute($stmt);
$grades_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Grade Management - Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; font-family: 'Arial', sans-serif; }
        .card { border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .btn-primary { background: linear-gradient(135deg, #2563eb, #1e40af); border: none; }
        .table th { background: #f8f9fa; border: none; }
        .alert { border-radius: 10px; }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse" style="min-height: 100vh;">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="teacherhome.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white active" href="teacher_grade_management.php">
                                <i class="fas fa-chart-bar me-2"></i>Grade Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="teacher_schedule_management.php">
                                <i class="fas fa-calendar me-2"></i>Schedule Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Grade Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <span class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </div>
                    </div>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <!-- Grade Management Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Student Grades
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Course</th>
                                        <th>Assignment</th>
                                        <th>Current Grade</th>
                                        <th>Score</th>
                                        <th>Submitted Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($grades_result) > 0): ?>
                                        <?php while ($grade = mysqli_fetch_assoc($grades_result)): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($grade['student_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($grade['course_name']); ?></td>
                                            <td><?php echo htmlspecialchars($grade['assignment_name']); ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($grade['grade']); ?></span>
                                            </td>
                                            <td><?php echo $grade['score']; ?>/<?php echo $grade['max_score']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($grade['submitted_date'])); ?></td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editGradeModal<?php echo $grade['id']; ?>">
                                                    <i class="fas fa-edit"></i> Update
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Edit Grade Modal -->
                                        <div class="modal fade" id="editGradeModal<?php echo $grade['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Update Grade</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="grade_id" value="<?php echo $grade['id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Student</label>
                                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($grade['student_name']); ?>" readonly>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Assignment</label>
                                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($grade['assignment_name']); ?>" readonly>
                                                            </div>
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Grade</label>
                                                                        <select name="new_grade" class="form-select" required>
                                                                            <option value="">Select Grade</option>
                                                                            <option value="A" <?php echo ($grade['grade'] == 'A') ? 'selected' : ''; ?>>A</option>
                                                                            <option value="A-" <?php echo ($grade['grade'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                                                                            <option value="B+" <?php echo ($grade['grade'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                                                                            <option value="B" <?php echo ($grade['grade'] == 'B') ? 'selected' : ''; ?>>B</option>
                                                                            <option value="B-" <?php echo ($grade['grade'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                                                                            <option value="C+" <?php echo ($grade['grade'] == 'C+') ? 'selected' : ''; ?>>C+</option>
                                                                            <option value="C" <?php echo ($grade['grade'] == 'C') ? 'selected' : ''; ?>>C</option>
                                                                            <option value="C-" <?php echo ($grade['grade'] == 'C-') ? 'selected' : ''; ?>>C-</option>
                                                                            <option value="D" <?php echo ($grade['grade'] == 'D') ? 'selected' : ''; ?>>D</option>
                                                                            <option value="F" <?php echo ($grade['grade'] == 'F') ? 'selected' : ''; ?>>F</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Score</label>
                                                                        <input type="number" name="new_score" class="form-control" value="<?php echo $grade['score']; ?>" min="0" max="<?php echo $grade['max_score']; ?>" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Comments</label>
                                                                <textarea name="comments" class="form-control" rows="3"><?php echo htmlspecialchars($grade['comments'] ?? ''); ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="update_grade" class="btn btn-primary">Update Grade</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">
                                                <i class="fas fa-info-circle me-2"></i>No grades found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 