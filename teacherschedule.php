<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'teacher') {
    header("Location: login.php");
    exit();
}
$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) { die("Database connection failed: " . mysqli_connect_error()); }
$teacher_id = $_SESSION['teacher_id'];
// Define days and periods (can be customized)
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
$periods = [
    ['08:00:00','09:00:00'],
    ['09:00:00','10:00:00'],
    ['10:15:00','11:15:00'],
    ['11:15:00','12:15:00'],
    ['13:00:00','14:00:00']
];
// Fetch all schedule entries for this teacher
$schedules = [];
$res = mysqli_query($conn, "SELECT * FROM student_schedule WHERE teacher_id = $teacher_id AND is_active = 1");
while ($row = mysqli_fetch_assoc($res)) {
    $schedules[] = $row;
}
function getClassInfo($conn, $course_id) {
    $course = mysqli_fetch_assoc(mysqli_query($conn, "SELECT course_name, program FROM courses WHERE id = $course_id"));
    return $course ? $course['course_name'] . ' (' . $course['program'] . ')' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Schedule - Teacher Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #2563eb; --secondary-color: #1e40af; --light-color: #f8fafc; --border-color: #e5e7eb; }
        * { font-family: 'Poppins', sans-serif; }
        body { background: var(--light-color); }
        .main-content { margin-left: 280px; padding: 2rem; }
        .schedule-table th, .schedule-table td { text-align: center; vertical-align: middle; }
        .schedule-table .fw-semibold { font-weight: 600; }
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
            <a href="teacherassignments.php" class="sidebar-link"><i class="fas fa-tasks"></i>Assignments & Materials</a>
            <a href="teacherperformance.php" class="sidebar-link"><i class="fas fa-chart-bar"></i>Student Performance</a>
            <a href="teacherschedule.php" class="sidebar-link active"><i class="fas fa-calendar-alt"></i>Schedule</a>
            <a href="teacherprofile.php" class="sidebar-link"><i class="fas fa-user"></i>Profile</a>
            <a href="teachernotifications.php" class="sidebar-link"><i class="fas fa-bell"></i>Notifications</a>
        </div>
    </aside>
    <div class="main-content">
        <h2 class="mb-4"><i class="fas fa-calendar-alt me-2"></i>My Schedule</h2>
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-bordered schedule-table">
                    <thead class="table-primary">
                        <tr>
                            <th>Period</th>
                            <?php foreach ($days as $day) echo "<th>$day</th>"; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($periods as $i => $period): ?>
                        <tr>
                            <td class="fw-bold"><?php echo date('H:i', strtotime($period[0])) . '-' . date('H:i', strtotime($period[1])); ?></td>
                            <?php foreach ($days as $day): ?>
                                <td>
                                    <?php
                                    $found = false;
                                    foreach ($schedules as $sch) {
                                        if ($sch['day_of_week'] === $day && $sch['start_time'] === $period[0] && $sch['end_time'] === $period[1]) {
                                            $found = true;
                                            echo '<div class="fw-semibold">' . htmlspecialchars(getClassInfo($conn, $sch['course_id'])) . '</div>';
                                            if (!empty($sch['room'])) {
                                                echo '<div class="text-muted small"><i class="fas fa-door-open me-1"></i>' . htmlspecialchars($sch['room']) . '</div>';
                                            }
                                            break;
                                        }
                                    }
                                    if (!$found) {
                                        echo '<span class="text-muted">Free</span>';
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html> 