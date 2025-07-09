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
    <title>Assignments & Materials - Student Portal</title>
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
        <h2 class="mb-4"><i class="fas fa-tasks me-2"></i>Assignments & Materials</h2>
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
                    $course = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM courses WHERE course_name LIKE '%".mysqli_real_escape_string($conn, $subject_name)."%' LIMIT 1"));
                    $course_id = $course['id'] ?? null;
                    echo '<div class="col-md-6 col-lg-4 mb-4">';
                    echo '<div class="card h-100 shadow-sm">';
                    echo '<div class="card-header bg-light"><strong><i class="fas fa-book me-2"></i>' . htmlspecialchars($subject_name) . '</strong></div>';
                    echo '<div class="card-body">';
                    if ($course_id) {
                        $assignments = mysqli_query($conn, "SELECT * FROM course_assignments WHERE course_id = $course_id ORDER BY due_date DESC");
                        if ($assignments && mysqli_num_rows($assignments) > 0) {
                            echo '<h6 class="fw-bold mb-2"><i class="fas fa-tasks me-1"></i>Assignments</h6>';
                            echo '<ul class="list-group mb-3">';
                            while ($a = mysqli_fetch_assoc($assignments)) {
                                $submission = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM assignment_submissions WHERE assignment_id = {$a['id']} AND student_id = (SELECT id FROM students WHERE username = '".mysqli_real_escape_string($conn, $student_username)."')"));
                                $status = $submission ? 'Submitted' : (strtotime($a['due_date']) < time() ? 'Overdue' : 'Pending');
                                $grade = $submission['grade'] ?? null;
                                echo '<li class="list-group-item">';
                                echo '<div class="d-flex justify-content-between align-items-center">';
                                echo '<span><strong>' . htmlspecialchars($a['title']) . '</strong> <span class="badge bg-secondary ms-2">Due: ' . date('M j, Y', strtotime($a['due_date'])) . '</span></span>';
                                echo '<span class="badge '.($status=='Submitted'?'bg-success':($status=='Overdue'?'bg-danger':'bg-warning text-dark')).'">' . $status . '</span>';
                                echo '</div>';
                                if ($grade) echo '<div class="mt-1"><span class="badge bg-primary">Grade: ' . htmlspecialchars($grade) . '</span></div>';
                                echo '</li>';
                            }
                            echo '</ul>';
                        } else {
                            echo '<div class="text-muted mb-2">No assignments found.</div>';
                        }
                        $materials = mysqli_query($conn, "SELECT * FROM course_materials WHERE course_id = $course_id ORDER BY created_at DESC");
                        if ($materials && mysqli_num_rows($materials) > 0) {
                            echo '<h6 class="fw-bold mb-2"><i class="fas fa-file-alt me-1"></i>Materials</h6>';
                            echo '<ul class="list-group">';
                            while ($m = mysqli_fetch_assoc($materials)) {
                                echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                                echo '<span><i class="fas fa-file me-1"></i>' . htmlspecialchars($m['title']) . '</span>';
                                echo '<a href="' . htmlspecialchars($m['file_path']) . '" download class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i> Download</a>';
                                echo '</li>';
                            }
                            echo '</ul>';
                        } else {
                            echo '<div class="text-muted">No materials found.</div>';
                        }
                    } else {
                        echo '<div class="text-muted">No course found for this subject.</div>';
                    }
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