<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Subjects & Teachers - Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --light-color: #f8fafc;
            --border-color: #e5e7eb;
        }
        * { font-family: 'Poppins', sans-serif; }
        body { background: var(--light-color); }
        .main-content { margin-left: 280px; padding: 2rem; }
    </style>
</head>
<body>
    <?php include 'studentsidebar.php'; ?>
    <div class="main-content">
        <h2 class="mb-4"><i class="fas fa-book-open me-2"></i>My Subjects & Teachers</h2>
        <?php
        $conn = mysqli_connect("localhost", "root", "", "schoolproject");
        $student_username = $_SESSION['username'];
        $student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT grade_id, section FROM students WHERE username = '".mysqli_real_escape_string($conn, $student_username)."' LIMIT 1"));
        $grade_id = $student['grade_id'] ?? null;
        if ($grade_id) {
            $subjects = mysqli_query($conn, "SELECT gsa.subject_id, s.name as subject_name FROM grade_subject_assignments gsa JOIN subjects s ON gsa.subject_id = s.id WHERE gsa.grade_id = $grade_id GROUP BY gsa.subject_id ORDER BY s.name");
            echo '<div class="row">';
            if ($subjects && mysqli_num_rows($subjects) > 0) {
                while ($subj = mysqli_fetch_assoc($subjects)) {
                    $subject_id = $subj['subject_id'];
                    $subject_name = $subj['subject_name'];
                    $teachers = mysqli_query($conn, "SELECT t.name, t.email FROM teacher_grade_subjects tgs JOIN teacher t ON tgs.teacher_id = t.id WHERE tgs.grade_id = $grade_id AND tgs.subject_id = $subject_id");
                    echo '<div class="col-md-6 col-lg-4 mb-4">';
                    echo '<div class="card h-100 shadow-sm">';
                    echo '<div class="card-body">';
                    echo '<h6 class="fw-bold mb-2"><i class="fas fa-book me-2"></i>' . htmlspecialchars($subject_name) . '</h6>';
                    echo '<div class="mb-2"><span class="text-muted">Teacher(s):</span><ul class="mb-0">';
                    if ($teachers && mysqli_num_rows($teachers) > 0) {
                        while ($t = mysqli_fetch_assoc($teachers)) {
                            echo '<li><i class="fas fa-chalkboard-teacher me-1"></i>' . htmlspecialchars($t['name']) . ' <span class="text-muted">(' . htmlspecialchars($t['email']) . ')</span></li>';
                        }
                    } else {
                        echo '<li class="text-danger">No teacher assigned</li>';
                    }
                    echo '</ul></div>';
                    echo '</div></div></div></div>';
                }
            } else {
                echo '<div class="alert alert-info text-center mb-0">No subjects found for your grade.</div>';
            }
            echo '</div>';
        }
        ?>
    </div>
</body>
</html> 