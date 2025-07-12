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
    <title>My Schedule - Comfort e-School Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
        }
        .top-nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            z-index: 1000;
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 60px;
        }
        .top-nav-link {
            color: #444;
            text-decoration: none;
            font-size: 1.1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 10px;
            transition: color 0.2s;
        }
        .top-nav-link i {
            font-size: 1.3rem;
        }
        .top-nav-link.active, .top-nav-link:hover {
            color: #007bff;
        }
        .main-content {
            margin-top: 80px;
            padding: 2rem 1rem 1rem 1rem;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }
        .schedule-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .schedule-icon {
            background: #e3f0ff;
            color: #007bff;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }
        .schedule-table {
            background: #fff;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .schedule-table th, .schedule-table td {
            text-align: center;
            vertical-align: middle;
            font-size: 1rem;
        }
        .schedule-table th {
            background: #e3f0ff;
            color: #007bff;
            font-weight: 600;
        }
        .schedule-table td {
            background: #f8f9fa;
        }
        .schedule-table .fw-semibold {
            color: #333;
            font-weight: 600;
        }
        .schedule-table .text-muted {
            font-size: 0.95rem;
        }
        @media (max-width: 900px) {
            .main-content {
                padding: 1rem 0.2rem 0.5rem 0.2rem;
            }
            .schedule-table th, .schedule-table td {
                font-size: 0.95rem;
                padding: 0.5rem;
            }
        }
        @media (max-width: 600px) {
            .main-content {
                padding: 0.5rem 0.1rem 0.2rem 0.1rem;
            }
            .schedule-header h2 {
                font-size: 1.2rem;
            }
            .schedule-icon {
                width: 36px;
                height: 36px;
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <a href="teacherhome.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherhome.php') echo ' active';?>"><i class="fas fa-home"></i><span>Home</span></a>
        <a href="teacherclasses.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherclasses.php') echo ' active';?>"><i class="fas fa-users-class"></i><span>Classes</span></a>
        <a href="teachersubjects.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teachersubjects.php') echo ' active';?>"><i class="fas fa-book"></i><span>Subjects</span></a>
        <a href="teacherassignments.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherassignments.php') echo ' active';?>"><i class="fas fa-tasks"></i><span>Assignments</span></a>
        <a href="teacherperformance.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherperformance.php') echo ' active';?>"><i class="fas fa-chart-line"></i><span>Performance</span></a>
        <a href="teacherschedule.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherschedule.php') echo ' active';?>"><i class="fas fa-calendar-alt"></i><span>Schedule</span></a>
        <a href="teachernotifications.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teachernotifications.php') echo ' active';?>"><i class="fas fa-bell"></i><span>Notifications</span></a>
        <a href="teacherprofile.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherprofile.php') echo ' active';?>"><i class="fas fa-user"></i><span>Profile</span></a>
    </nav>
    <div class="main-content">
        <div class="schedule-header">
            <div class="schedule-icon"><i class="fas fa-calendar-alt"></i></div>
            <h2 class="mb-0">My Schedule</h2>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-bordered schedule-table">
                    <thead>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 