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
$classes = mysqli_query($conn, "SELECT tgs.grade_id, g.name as grade_name, gsa.section, tgs.subject_id, s.name as subject_name, gsa.id as gsa_id FROM teacher_grade_subjects tgs JOIN grades g ON tgs.grade_id = g.id JOIN subjects s ON tgs.subject_id = s.id JOIN grade_subject_assignments gsa ON gsa.grade_id = tgs.grade_id AND gsa.subject_id = tgs.subject_id WHERE tgs.teacher_id = $teacher_id ORDER BY g.name, gsa.section, s.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Performance - Teacher Portal</title>
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
            <a href="teacherclasses.php" class="sidebar-link"><i class="fas fa-users-class"></i>My Classes</a>
            <a href="teachersubjects.php" class="sidebar-link"><i class="fas fa-book"></i>My Subjects</a>
            <a href="teacherassignments.php" class="sidebar-link"><i class="fas fa-tasks"></i>Assignments & Materials</a>
            <a href="teacherperformance.php" class="sidebar-link active"><i class="fas fa-chart-bar"></i>Student Performance</a>
            <a href="teacherschedule.php" class="sidebar-link"><i class="fas fa-calendar-alt"></i>Schedule</a>
            <a href="teacherprofile.php" class="sidebar-link"><i class="fas fa-user"></i>Profile</a>
            <a href="teachernotifications.php" class="sidebar-link"><i class="fas fa-bell"></i>Notifications</a>
        </div>
    </aside>
    <div class="main-content">
        <h2 class="mb-4"><i class="fas fa-chart-bar me-2"></i>Student Performance</h2>
        <?php
        // Add success/error message display
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>
        <?php
// Gather all classes for the teacher for the class filter
$class_options = [];
if ($classes && mysqli_num_rows($classes) > 0) {
    mysqli_data_seek($classes, 0);
    while ($c = mysqli_fetch_assoc($classes)) {
        $class_key = $c['grade_id'] . '|' . $c['section'] . '|' . $c['subject_id'];
        $class_label = $c['grade_name'] . $c['section'] . ' - ' . $c['subject_name'];
        $class_options[$class_key] = $class_label;
    }
    mysqli_data_seek($classes, 0);
}
$selected_class = $_GET['class'] ?? '';
$selected_assignment = $_GET['assignment'] ?? '';
$student_search = $_GET['student'] ?? '';
$status_filter = $_GET['status'] ?? '';
// If a class is selected, get assignments for that class
$assignment_options = [];
if ($selected_class) {
    list($grade_id, $section, $subject_id) = explode('|', $selected_class);
    $course = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM courses WHERE program LIKE (SELECT name FROM grades WHERE id = $grade_id) AND course_name LIKE (SELECT name FROM subjects WHERE id = $subject_id) LIMIT 1"));
    $course_id = $course['id'] ?? 0;
    if ($course_id) {
        $as = mysqli_query($conn, "SELECT id, title FROM course_assignments WHERE course_id = $course_id AND created_by = $teacher_id");
        while ($a = mysqli_fetch_assoc($as)) {
            $assignment_options[$a['id']] = $a['title'];
        }
    }
}
?>
<form class="row g-2 mb-4" method="get">
    <div class="col-md-3">
        <select name="class" class="form-select" onchange="this.form.submit()">
            <option value="">All Classes</option>
            <?php foreach ($class_options as $key => $label): ?>
                <option value="<?php echo htmlspecialchars($key); ?>" <?php if ($selected_class === $key) echo 'selected'; ?>><?php echo htmlspecialchars($label); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select name="assignment" class="form-select" onchange="this.form.submit()" <?php if (!$selected_class) echo 'disabled'; ?>>
            <option value="">All Assignments</option>
            <?php foreach ($assignment_options as $aid => $title): ?>
                <option value="<?php echo $aid; ?>" <?php if ($selected_assignment == $aid) echo 'selected'; ?>><?php echo htmlspecialchars($title); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <input type="text" name="student" class="form-control" placeholder="Search student name/email" value="<?php echo htmlspecialchars($student_search); ?>">
    </div>
    <div class="col-md-2">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="submitted" <?php if ($status_filter === 'submitted') echo 'selected'; ?>>Submitted</option>
            <option value="graded" <?php if ($status_filter === 'graded') echo 'selected'; ?>>Graded</option>
            <option value="overdue" <?php if ($status_filter === 'overdue') echo 'selected'; ?>>Overdue</option>
        </select>
    </div>
    <div class="col-md-1">
        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i></button>
    </div>
</form>
        <div class="row">
        <?php if ($classes && mysqli_num_rows($classes) > 0):
            mysqli_data_seek($classes, 0);
            while ($class = mysqli_fetch_assoc($classes)):
                $grade = htmlspecialchars($class['grade_name']);
                $section = htmlspecialchars($class['section']);
                $subject = htmlspecialchars($class['subject_name']);
                $gsa_id = $class['gsa_id'];
                $class_key = $class['grade_id'] . '|' . $class['section'] . '|' . $class['subject_id'];
                // Filter by class if selected
                if ($selected_class && $selected_class !== $class_key) continue;
                // Find course_id for this grade/subject
                $course = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM courses WHERE course_name LIKE '%$subject%' AND program LIKE '%$grade%' LIMIT 1"));
                $course_id = $course['id'] ?? 0;
        ?>
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <strong><?php echo "$grade$section - $subject"; ?></strong>
                    </div>
                    <div class="card-body">
                        <?php
                        if ($course_id) {
                            // Get students in this grade/section, filter by search if needed
                            $student_query = "SELECT id, full_name, email FROM students WHERE grade_id = {$class['grade_id']} AND section = '".mysqli_real_escape_string($conn, $section)."'";
                            if ($student_search) {
                                $search = mysqli_real_escape_string($conn, $student_search);
                                $student_query .= " AND (full_name LIKE '%$search%' OR email LIKE '%$search%')";
                            }
                            $students = mysqli_query($conn, $student_query);
                            if ($students && mysqli_num_rows($students) > 0) {
                                echo '<div class="table-responsive"><table class="table table-bordered align-middle">';
                                echo '<thead class="table-light"><tr><th>Student</th><th>Email</th><th>Assignment</th><th>Status</th><th>Score</th><th>Grade</th></tr></thead><tbody>';
                                while ($s = mysqli_fetch_assoc($students)) {
                                    // For each student, get their assignments for this course, filter by assignment if needed
                                    $assignment_query = "SELECT ca.id as ca_id, ca.title, sa.status, sa.score, sa.grade FROM student_assignments sa JOIN course_assignments ca ON sa.course_id = ca.course_id AND sa.title = ca.title WHERE sa.student_id = {$s['id']} AND sa.course_id = $course_id";
                                    if ($selected_assignment) {
                                        $assignment_id = (int)$selected_assignment;
                                        $assignment_title = mysqli_fetch_array(mysqli_query($conn, "SELECT title FROM course_assignments WHERE id = $assignment_id LIMIT 1"))[0] ?? '';
                                        $assignment_query .= " AND ca.title = '" . mysqli_real_escape_string($conn, $assignment_title) . "'";
                                    }
                                    if ($status_filter) {
                                        $assignment_query .= " AND sa.status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
                                    }
                                    $assignments = mysqli_query($conn, $assignment_query);
                                    if ($assignments && mysqli_num_rows($assignments) > 0) {
                                        while ($a = mysqli_fetch_assoc($assignments)) {
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($s['full_name']) . '</td>';
                                            echo '<td>' . htmlspecialchars($s['email']) . '</td>';
                                            echo '<td>' . htmlspecialchars($a['title']) . '</td>';
                                            echo '<td>' . htmlspecialchars($a['status']);
                                            // If there is a submission, show view button
                                            $submission = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM assignment_submissions WHERE assignment_id = (SELECT id FROM course_assignments WHERE course_id = $course_id AND title = '" . mysqli_real_escape_string($conn, $a['title']) . "' LIMIT 1) AND student_id = {$s['id']}"));
                                            if ($submission) {
                                                echo ' <button class="btn btn-sm btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#viewSubmissionModal_' . $submission['id'] . '"><i class="fas fa-eye"></i> View Submission</button>';
                                            }
                                            echo '</td>';
                                            echo '<td>' . htmlspecialchars($a['score']) . '</td>';
                                            echo '<td>' . htmlspecialchars($a['grade']) . '</td>';
                                            echo '</tr>';
                                            // Modal for viewing/grading submission
                                            if ($submission) {
?>
<div class="modal fade" id="viewSubmissionModal_<?php echo $submission['id']; ?>" tabindex="-1" aria-labelledby="viewSubmissionLabel_<?php echo $submission['id']; ?>" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="viewSubmissionLabel_<?php echo $submission['id']; ?>">Submission: <?php echo htmlspecialchars($a['title']); ?> (<?php echo htmlspecialchars($s['full_name']); ?>)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?php if (!empty($submission['submission_file'])): ?>
            <div class="mb-2"><a href="<?php echo htmlspecialchars($submission['submission_file']); ?>" download>Download Submission File</a></div>
          <?php endif; ?>
          <?php if (!empty($submission['submission_text'])): ?>
            <div class="mb-2"><strong>Submission Text:</strong><br><?php echo nl2br(htmlspecialchars($submission['submission_text'])); ?></div>
          <?php endif; ?>
          <div class="mb-2"><strong>Current Grade:</strong> <?php echo htmlspecialchars($submission['grade']); ?></div>
          <div class="mb-2"><strong>Feedback:</strong> <?php echo nl2br(htmlspecialchars($submission['feedback'])); ?></div>
          <?php if (empty($submission['grade'])): ?>
          <hr>
          <h6>Grade Submission</h6>
          <input type="hidden" name="grade_submission" value="1">
          <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
          <div class="mb-3">
            <label class="form-label">Grade</label>
            <input type="text" name="grade" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Score</label>
            <input type="number" name="score" class="form-control" step="0.01" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Feedback</label>
            <textarea name="feedback" class="form-control" rows="3"></textarea>
          </div>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <?php if (empty($submission['grade'])): ?>
          <button type="submit" class="btn btn-primary">Submit Grade</button>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
</div>
<?php
                                            }
                                        }
                                    } else {
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($s['full_name']) . '</td>';
                                        echo '<td>' . htmlspecialchars($s['email']) . '</td>';
                                        echo '<td colspan="4" class="text-muted">No assignments found.</td>';
                                        echo '</tr>';
                                    }
                                }
                                echo '</tbody></table></div>';
                            } else {
                                echo '<div class="text-muted">No students found for this class.</div>';
                            }
                        } else {
                            echo '<div class="text-muted">No course found for this subject.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php endwhile; else: ?>
            <div class="col-12"><div class="alert alert-info">No classes assigned.</div></div>
        <?php endif; ?>
        </div>
    </div>
<?php
// Handle grading form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade_submission'])) {
    $submission_id = (int)$_POST['submission_id'];
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);
    $score = (float)$_POST['score'];
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    $update = mysqli_query($conn, "UPDATE assignment_submissions SET grade='$grade', score=$score, feedback='$feedback', graded_by=$teacher_id, graded_at=NOW() WHERE id=$submission_id");
    if ($update) {
        $_SESSION['success_message'] = 'Submission graded successfully!';
    } else {
        $_SESSION['error_message'] = 'Failed to grade submission.';
    }
    header('Location: teacherperformance.php'); exit();
}
?>
</body>
</html> 