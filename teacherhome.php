<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'teacher') {
    header("Location: login.php");
    exit();
}
$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) { die("Database connection failed: " . mysqli_connect_error()); }
$teacher_id = $_SESSION['teacher_id'];
$teacher = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM teacher WHERE id = $teacher_id"));
// Quick stats
$total_classes = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM teacher_grade_subjects WHERE teacher_id = $teacher_id"))[0];
// Count assignments as the number of grade_subject_assignments for this teacher's grades/subjects
$total_assignments = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM grade_subject_assignments gsa JOIN teacher_grade_subjects tgs ON gsa.grade_id = tgs.grade_id AND gsa.subject_id = tgs.subject_id WHERE tgs.teacher_id = $teacher_id"))[0];
$total_students = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(DISTINCT s.id) FROM students s JOIN grade_subject_assignments gsa ON s.grade_id = gsa.grade_id JOIN teacher_grade_subjects tgs ON tgs.grade_id = gsa.grade_id AND tgs.subject_id = gsa.subject_id WHERE tgs.teacher_id = $teacher_id"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher Dashboard - Comfort e-School Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --border-color: #e5e7eb;
        }
        * { font-family: 'Poppins', sans-serif; }
        body { background: var(--light-color); overflow-x: hidden; }
        .sidebar {
            position: fixed; left: 0; top: 0; height: 100vh; width: 280px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white; z-index: 1000; transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1); display: flex; flex-direction: column;
        }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center; }
        .sidebar-brand { font-size: 1.5rem; font-weight: 700; color: white; text-decoration: none; }
        .sidebar-menu { flex: 1; overflow-y: auto; padding: 1rem 0; }
        .sidebar-link { display: flex; align-items: center; padding: 0.75rem 1rem; color: rgba(255,255,255,0.8); text-decoration: none; border-radius: 10px; transition: all 0.3s ease; font-weight: 600; font-size: 1.1rem; gap: 0.75rem; }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(255,255,255,0.15); color: white; }
        .main-content { margin-left: 280px; min-height: 100vh; transition: all 0.3s ease; padding: 2rem; }
        .top-header { background: white; height: 70px; display: flex; align-items: center; justify-content: space-between; padding: 0 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 999; }
        .header-title { font-size: 1.5rem; font-weight: 600; color: var(--dark-color); }
        .header-actions { display: flex; align-items: center; gap: 1rem; }
        .teacher-info { display: flex; align-items: center; gap: 0.5rem; }
        .teacher-avatar { width: 40px; height: 40px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; }
        .logout-btn { background: var(--danger-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; transition: all 0.3s ease; }
        .logout-btn:hover { background: #dc2626; color: white; transform: translateY(-2px); }
        .dashboard-content { padding: 2rem 0; }
        .welcome-section { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 2.5rem 2rem; border-radius: 20px; margin-bottom: 2rem; box-shadow: 0 4px 24px rgba(37,99,235,0.10); text-align: center; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 2rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 2rem 1.5rem; border-radius: 18px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border: 1px solid var(--border-color); text-align: center; transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.13); }
        .stat-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; margin: 0 auto 1rem; }
        .stat-icon.classes { background: var(--info-color); }
        .stat-icon.assignments { background: var(--success-color); }
        .stat-icon.students { background: var(--primary-color); }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-brand">Teacher Portal</a>
        </div>
        <div class="sidebar-menu">
            <a href="teacherhome.php" class="sidebar-link active"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
            <a href="teacherclasses.php" class="sidebar-link"><i class="fas fa-users-class"></i>My Classes</a>
            <a href="teachersubjects.php" class="sidebar-link"><i class="fas fa-book"></i>My Subjects</a>
            <a href="teacherassignments.php" class="sidebar-link"><i class="fas fa-tasks"></i>Assignments & Materials</a>
            <a href="teacherperformance.php" class="sidebar-link"><i class="fas fa-chart-bar"></i>Student Performance</a>
            <a href="teacherschedule.php" class="sidebar-link"><i class="fas fa-calendar-alt"></i>Schedule</a>
            <a href="teacherprofile.php" class="sidebar-link"><i class="fas fa-user"></i>Profile</a>
            <a href="teachernotifications.php" class="sidebar-link"><i class="fas fa-bell"></i>Notifications</a>
        </div>
    </aside>
    <div class="main-content">
        <div class="top-header">
            <div class="d-flex align-items-center">
                <h1 class="header-title mb-0">Dashboard</h1>
            </div>
            <div class="header-actions">
                <div class="teacher-info">
                    <div class="teacher-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div class="fw-bold"><?php echo htmlspecialchars($teacher['name']); ?></div>
                        <small class="text-muted">Teacher</small>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
        <div class="dashboard-content">
            <div class="welcome-section">
                <h1 class="welcome-title mb-3" style="font-size:2.2rem; font-weight:700;">Welcome, <?php echo htmlspecialchars($teacher['name']); ?>!</h1>
                <p class="welcome-subtitle mb-4" style="font-size:1.1rem; opacity:0.95;">This is your teacher dashboard. Use the sidebar to access your classes, assignments, students, and more.</p>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon classes"><i class="fas fa-users-class"></i></div>
                    <div class="stat-number" style="font-size:2rem;font-weight:700;"><?php echo $total_classes; ?></div>
                    <div class="stat-label">My Classes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon assignments"><i class="fas fa-tasks"></i></div>
                    <div class="stat-number" style="font-size:2rem;font-weight:700;"><?php echo $total_assignments; ?></div>
                    <div class="stat-label">Assignments</div>
                                    </div>
                <div class="stat-card">
                    <div class="stat-icon students"><i class="fas fa-user-graduate"></i></div>
                    <div class="stat-number" style="font-size:2rem;font-weight:700;"><?php echo $total_students; ?></div>
                    <div class="stat-label">Students</div>
                </div>
        </div>
      </div>
    </div>
</body>
</html> 