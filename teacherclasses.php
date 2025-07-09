<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'teacher') {
    header("Location: login.php");
    exit();
}
$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) { die("Database connection failed: " . mysqli_connect_error()); }
$teacher_id = $_SESSION['teacher_id'];
// Get all classes (grade, section, subject) assigned to this teacher
$classes = mysqli_query($conn, "SELECT tgs.grade_id, g.name as grade_name, gsa.section, tgs.subject_id, s.name as subject_name FROM teacher_grade_subjects tgs JOIN grades g ON tgs.grade_id = g.id JOIN subjects s ON tgs.subject_id = s.id JOIN grade_subject_assignments gsa ON gsa.grade_id = tgs.grade_id AND gsa.subject_id = tgs.subject_id WHERE tgs.teacher_id = $teacher_id ORDER BY g.name, gsa.section, s.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Classes - Teacher Portal</title>
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
            <a href="teacherclasses.php" class="sidebar-link active"><i class="fas fa-users-class"></i>My Classes</a>
            <a href="teacherassignments.php" class="sidebar-link"><i class="fas fa-tasks"></i>Assignments & Materials</a>
            <a href="teacherperformance.php" class="sidebar-link"><i class="fas fa-chart-bar"></i>Student Performance</a>
            <a href="teacherschedule.php" class="sidebar-link"><i class="fas fa-calendar-alt"></i>Schedule</a>
            <a href="teacherprofile.php" class="sidebar-link"><i class="fas fa-user"></i>Profile</a>
            <a href="teachernotifications.php" class="sidebar-link"><i class="fas fa-bell"></i>Notifications</a>
        </div>
    </aside>
    <div class="main-content">
        <h2 class="mb-4"><i class="fas fa-users-class me-2"></i>My Classes</h2>
        <div class="row">
        <?php if ($classes && mysqli_num_rows($classes) > 0):
            while ($class = mysqli_fetch_assoc($classes)):
                $grade = htmlspecialchars($class['grade_name']);
                $section = htmlspecialchars($class['section']);
                $subject = htmlspecialchars($class['subject_name']);
                // Get students in this grade/section
                $students = mysqli_query($conn, "SELECT full_name, email FROM students WHERE grade_id = {$class['grade_id']} AND section = '".mysqli_real_escape_string($conn, $section)."'");
        ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <strong><?php echo "$grade$section - $subject"; ?></strong>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">Students:</h6>
                        <?php if ($students && mysqli_num_rows($students) > 0): ?>
                            <ul class="mb-0">
                                <?php while ($s = mysqli_fetch_assoc($students)): ?>
                                    <li><i class="fas fa-user me-1"></i><?php echo htmlspecialchars($s['full_name']); ?> <span class="text-muted small">(<?php echo htmlspecialchars($s['email']); ?>)</span></li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-muted">No students found.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; else: ?>
            <div class="col-12"><div class="alert alert-info">No classes assigned.</div></div>
        <?php endif; ?>
        </div>
    </div>
</body>
</html> 