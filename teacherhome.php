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
// Stats
$total_classes = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(DISTINCT CONCAT(g.name, gsa.section)) FROM teacher_grade_subjects tgs JOIN grades g ON tgs.grade_id = g.id JOIN grade_subject_assignments gsa ON gsa.grade_id = tgs.grade_id AND gsa.subject_id = tgs.subject_id WHERE tgs.teacher_id = $teacher_id"))[0];
$total_subjects = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(DISTINCT tgs.subject_id) FROM teacher_grade_subjects tgs WHERE tgs.teacher_id = $teacher_id"))[0];
$total_assignments = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM grade_subject_assignments gsa JOIN teacher_grade_subjects tgs ON gsa.grade_id = tgs.grade_id AND gsa.subject_id = tgs.subject_id WHERE tgs.teacher_id = $teacher_id"))[0];
$total_students = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(DISTINCT s.id) FROM students s JOIN grade_subject_assignments gsa ON s.grade_id = gsa.grade_id JOIN teacher_grade_subjects tgs ON tgs.grade_id = gsa.grade_id AND tgs.subject_id = gsa.subject_id WHERE tgs.teacher_id = $teacher_id"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f4f6fb; font-family: 'Poppins', sans-serif; }
        .main-content { min-height: 100vh; padding: 5.5rem 1.5rem 2.5rem 1.5rem; }
        .welcome-card {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(37,99,235,0.10);
            padding: 2.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2.5rem;
        }
        .welcome-avatar {
            width: 80px; height: 80px; border-radius: 50%; background: #fff2; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; }
        .welcome-info h2 { font-size: 2.1rem; font-weight: 700; margin-bottom: 0.5rem; }
        .welcome-info p { font-size: 1.1rem; opacity: 0.95; margin-bottom: 0; }
        .stats-row { display: flex; flex-wrap: wrap; gap: 2rem; margin-bottom: 2.5rem; }
        .stat-tile {
            flex: 1 1 220px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(37,99,235,0.07);
            padding: 2rem 1.5rem;
            display: flex; flex-direction: column; align-items: center;
            min-width: 200px;
            min-height: 160px;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .stat-tile:hover { box-shadow: 0 8px 32px rgba(37,99,235,0.13); transform: translateY(-3px) scale(1.01); }
        .stat-icon {
            width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem; color: #fff; }
        .stat-icon.bg-blue { background: #2563eb; }
        .stat-icon.bg-green { background: #10b981; }
        .stat-icon.bg-orange { background: #f59e0b; }
        .stat-icon.bg-purple { background: #7c3aed; }
        .stat-number { font-size: 2.1rem; font-weight: 700; color: #1e293b; margin-bottom: 0.25rem; }
        .stat-label { font-size: 1.1rem; color: #64748b; font-weight: 500; }
        .top-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            background: #fff;
            box-shadow: 0 2px 16px rgba(37,99,235,0.08);
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 68px;
            z-index: 1001;
            border-bottom-left-radius: 18px;
            border-bottom-right-radius: 18px;
        }
        .top-nav-link {
            flex: 1 1 0;
            text-align: center;
            color: #2563eb;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 500;
            padding: 0.5rem 0 0.2rem 0;
            transition: color 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .top-nav-link.active, .top-nav-link:focus, .top-nav-link:hover {
            color: #1e40af;
        }
        .top-nav-link i {
            font-size: 1.35rem;
            margin-bottom: 0.15rem;
        }
        @media (max-width: 900px) {
            .main-content { padding: 1rem 0.5rem 5.5rem 0.5rem; }
            .welcome-card { flex-direction: column; align-items: flex-start; gap: 1.2rem; }
            .stats-row { gap: 1rem; }
        }
        @media (max-width: 600px) {
            .main-content { padding: 0.5rem 0.2rem 5.5rem 0.2rem; }
            .welcome-card { padding: 1.2rem 0.5rem; }
            .stats-row { flex-direction: column; gap: 1rem; }
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <a href="teacherhome.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherhome.php') echo ' active';?>"><i class="fas fa-home"></i><span>Home</span></a>
        <a href="teacherclasses.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherclasses.php') echo ' active';?>"><i class="fas fa-users-class"></i><span>Classes</span></a>
        <a href="teachersubjects.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teachersubjects.php') echo ' active';?>"><i class="fas fa-book"></i><span>Subjects</span></a>
        <a href="teacherassignments.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherassignments.php') echo ' active';?>"><i class="fas fa-tasks"></i><span>Assignments</span></a>
        <a href="teacherperformance.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherperformance.php') echo ' active';?>"><i class="fas fa-chart-bar"></i><span>Performance</span></a>
        <a href="teacherschedule.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherschedule.php') echo ' active';?>"><i class="fas fa-calendar-alt"></i><span>Schedule</span></a>
        <a href="teacherprofile.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherprofile.php') echo ' active';?>"><i class="fas fa-user"></i><span>Profile</span></a>
        <a href="teachernotifications.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teachernotifications.php') echo ' active';?>"><i class="fas fa-bell"></i><span>Notifications</span></a>
    </nav>
    <div class="main-content">
        <div class="welcome-card">
            <div class="welcome-avatar bg-white text-primary d-flex align-items-center justify-content-center"><i class="fas fa-user"></i></div>
            <div class="welcome-info">
                <h2>Welcome, <?php echo htmlspecialchars($teacher['name']); ?>!</h2>
                <p>Your teacher dashboard gives you quick access to your classes, subjects, assignments, and students. Use the quick links below to get started.</p>
            </div>
        </div>
        <div class="stats-row">
            <div class="stat-tile">
                <div class="stat-icon bg-blue"><i class="fas fa-users-class"></i></div>
                <div class="stat-number"><?php echo $total_classes; ?></div>
                <div class="stat-label">Classes</div>
            </div>
            <div class="stat-tile">
                <div class="stat-icon bg-purple"><i class="fas fa-book"></i></div>
                <div class="stat-number"><?php echo $total_subjects; ?></div>
                <div class="stat-label">Subjects</div>
            </div>
            <div class="stat-tile">
                <div class="stat-icon bg-green"><i class="fas fa-tasks"></i></div>
                <div class="stat-number"><?php echo $total_assignments; ?></div>
                <div class="stat-label">Assignments</div>
            </div>
            <div class="stat-tile">
                <div class="stat-icon bg-orange"><i class="fas fa-user-graduate"></i></div>
                <div class="stat-number"><?php echo $total_students; ?></div>
                <div class="stat-label">Students</div>
            </div>
        </div>
    </div>
</body>
</html> 