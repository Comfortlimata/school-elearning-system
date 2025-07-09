<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
    header("Location: login.php");
    exit();
}
$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) { die("Database connection failed: " . mysqli_connect_error()); }
$username = $_SESSION['username'];
$student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT grade_id, section FROM students WHERE username = '".mysqli_real_escape_string($conn, $username)."' LIMIT 1"));
$grade_id = $student['grade_id'] ?? null;
$section = $student['section'] ?? null;
// Sample periods and days
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
$periods = ['08:00-09:00','09:00-10:00','10:15-11:15','11:15-12:15','13:00-14:00'];
// Fetch subjects for this grade/section
$subjects = [];
if ($grade_id && $section) {
    $result = mysqli_query($conn, "SELECT gsa.subject_id, s.name as subject_name FROM grade_subject_assignments gsa JOIN subjects s ON gsa.subject_id = s.id WHERE gsa.grade_id = $grade_id AND gsa.section = '".mysqli_real_escape_string($conn, $section)."' ORDER BY s.name");
    while ($row = mysqli_fetch_assoc($result)) {
        $subjects[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Schedule - Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #2563eb; --secondary-color: #1e40af; --light-color: #f8fafc; --border-color: #e5e7eb; }
        * { font-family: 'Poppins', sans-serif; }
        body { background: var(--light-color); }
        .main-content { margin-left: 280px; padding: 2rem; }
        .schedule-table th, .schedule-table td { text-align: center; vertical-align: middle; }
    </style>
</head>
<body>
    <?php include 'studentsidebar.php'; ?>
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
                            <td class="fw-bold"><?php echo $period; ?></td>
                            <?php foreach ($days as $j => $day): ?>
                                <td>
                                    <?php
                                    // For demo: assign subjects in round-robin
                                    if (count($subjects) > 0) {
                                        $subj = $subjects[($i+$j)%count($subjects)];
                                        echo '<div class="fw-semibold">' . htmlspecialchars($subj['subject_name']) . '</div>';
                                        // Find teacher
                                        $tq = mysqli_query($conn, "SELECT t.name FROM teacher_grade_subjects tgs JOIN teacher t ON tgs.teacher_id = t.id WHERE tgs.grade_id = $grade_id AND tgs.subject_id = {$subj['subject_id']}");
                                        if ($tq && $t = mysqli_fetch_assoc($tq)) {
                                            echo '<div class="text-muted small"><i class=\'fas fa-chalkboard-teacher me-1\'></i>' . htmlspecialchars($t['name']) . '</div>';
                                        }
                                    } else {
                                        echo '<span class="text-muted">-</span>';
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