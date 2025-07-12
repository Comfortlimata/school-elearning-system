<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'teacher') {
    header("Location: login.php");
    exit();
}
$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) { die("Database connection failed: " . mysqli_connect_error()); }
$teacher_id = $_SESSION['teacher_id'];
$classes = mysqli_query($conn, "SELECT tgs.grade_id, g.name as grade_name, gsa.section, tgs.subject_id, s.name as subject_name FROM teacher_grade_subjects tgs JOIN grades g ON tgs.grade_id = g.id JOIN subjects s ON tgs.subject_id = s.id JOIN grade_subject_assignments gsa ON gsa.grade_id = tgs.grade_id AND gsa.subject_id = tgs.subject_id WHERE tgs.teacher_id = $teacher_id ORDER BY g.name, gsa.section, s.name");
// Group by grade and section
$class_groups = [];
if ($classes && mysqli_num_rows($classes) > 0) {
    while ($row = mysqli_fetch_assoc($classes)) {
        $grade = $row['grade_name'];
        $section = $row['section'];
        $key = $grade . '|' . $section;
        if (!isset($class_groups[$key])) {
            $class_groups[$key] = [
                'grade' => $grade,
                'section' => $section,
                'subjects' => []
            ];
        }
        $class_groups[$key]['subjects'][] = $row['subject_name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Classes - Teacher Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f4f6fb; font-family: 'Poppins', sans-serif; }
        .main-content { min-height: 100vh; padding: 5.5rem 1.5rem 2.5rem 1.5rem; }
        .classes-title { font-size: 2rem; font-weight: 700; margin-bottom: 2rem; color: #1e293b; display: flex; align-items: center; gap: 0.75rem; }
        .classes-title i { color: #2563eb; font-size: 2.2rem; }
        .classes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
        }
        .class-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(37,99,235,0.08);
            border: 1px solid #e5e7eb;
            transition: box-shadow 0.2s, transform 0.2s;
            display: flex;
            flex-direction: column;
            min-height: 260px;
            padding: 2rem 1.5rem 1.5rem 1.5rem;
            position: relative;
        }
        .class-card:hover {
            box-shadow: 0 8px 32px rgba(37,99,235,0.15);
            transform: translateY(-4px) scale(1.01);
        }
        .class-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.2rem;
        }
        .class-icon {
            width: 48px; height: 48px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.7rem; box-shadow: 0 2px 8px rgba(37,99,235,0.10); }
        .class-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
            color: #1e293b;
        }
        .subjects-list {
            list-style: none;
            padding: 0;
            margin: 0 0 1.2rem 0;
        }
        .subjects-list li {
            font-size: 1rem;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #2563eb;
        }
        .subjects-list li:last-child { margin-bottom: 0; }
        .subject-icon { color: #10b981; font-size: 1.1rem; }
        .students-list {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 120px;
            overflow-y: auto;
        }
        .students-list li {
            font-size: 0.98rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .students-list li:last-child { margin-bottom: 0; }
        .student-email {
            color: #6b7280;
            font-size: 0.92rem;
        }
        .no-students {
            color: #888;
            font-size: 0.98rem;
            margin-top: 0.5rem;
        }
        @media (max-width: 900px) {
            .main-content { padding: 1rem 0.5rem 2.5rem 0.5rem; }
            .classes-grid { gap: 1rem; }
        }
        @media (max-width: 600px) {
            .main-content { padding: 0.5rem 0.2rem 2.5rem 0.2rem; }
            .classes-grid { grid-template-columns: 1fr; gap: 1rem; }
        }
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
        <div class="classes-title"><i class="fas fa-users-class"></i>My Classes</div>
        <div class="classes-grid">
        <?php if (!empty($class_groups)):
            foreach ($class_groups as $group):
                $grade = htmlspecialchars($group['grade']);
                $section = htmlspecialchars($group['section']);
                $subjects = $group['subjects'];
                $subject_count = count($subjects);
                $students = mysqli_query($conn, "SELECT full_name, email FROM students WHERE grade_id = (SELECT id FROM grades WHERE name = '" . mysqli_real_escape_string($conn, $grade) . "') AND section = '" . mysqli_real_escape_string($conn, $section) . "'");
        ?>
            <div class="class-card">
                <div class="class-header">
                    <div class="class-icon"><i class="fas fa-chalkboard"></i></div>
                    <div class="class-title"><?php echo "$grade$section"; ?> - <?php echo $subject_count; ?> Subject<?php if($subject_count>1) echo 's'; ?></div>
                </div>
                <h6 class="fw-bold mb-2" style="color:#1e40af;"><i class="fas fa-book me-1"></i>Subjects:</h6>
                <ul class="subjects-list">
                    <?php foreach ($subjects as $subject): ?>
                        <li><span class="subject-icon"><i class="fas fa-book"></i></span><?php echo htmlspecialchars($subject); ?></li>
                    <?php endforeach; ?>
                </ul>
                <h6 class="fw-bold mb-2 mt-3" style="color:#1e40af;"><i class="fas fa-user-graduate me-1"></i>Students:</h6>
                <?php if ($students && mysqli_num_rows($students) > 0): ?>
                    <ul class="students-list">
                        <?php while ($s = mysqli_fetch_assoc($students)): ?>
                            <li><i class="fas fa-user text-primary"></i><?php echo htmlspecialchars($s['full_name']); ?> <span class="student-email">(<?php echo htmlspecialchars($s['email']); ?>)</span></li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <div class="no-students">No students found.</div>
                <?php endif; ?>
            </div>
        <?php endforeach; else: ?>
            <div class="col-12"><div class="alert alert-info">No classes assigned.</div></div>
        <?php endif; ?>
        </div>
    </div>
</body>
</html> 