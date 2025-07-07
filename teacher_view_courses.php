<?php
session_start();

// Check if teacher is logged in
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$conn = mysqli_connect($host, $user, $password, $db);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$teacher_id = $_SESSION['teacher_id'];

// Get all grade-section-subject assignments for this teacher
$assignments_sql = "SELECT tgs.grade_id, g.name as grade, tgs.subject_id, s.name as subject, gsa.section
    FROM teacher_grade_subjects tgs
    JOIN grades g ON tgs.grade_id = g.id
    JOIN subjects s ON tgs.subject_id = s.id
    JOIN grade_subject_assignments gsa ON gsa.grade_id = tgs.grade_id AND gsa.subject_id = tgs.subject_id
    WHERE tgs.teacher_id = $teacher_id
    ORDER BY g.name, s.name, gsa.section";
$assignments_result = mysqli_query($conn, $assignments_sql);

$assignments = [];
$grades = [];
$sections = [];
$subjects = [];
while ($row = mysqli_fetch_assoc($assignments_result)) {
    $assignments[] = $row;
    $grades[$row['grade_id']] = $row['grade'];
    $sections[$row['section']] = $row['section'];
    $subjects[$row['subject_id']] = $row['subject'];
}

// Get filter values
$filter_grade = isset($_GET['grade_id']) ? (int)$_GET['grade_id'] : 0;
$filter_section = isset($_GET['section']) ? $_GET['section'] : '';
$filter_subject = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

// Filter assignments
$filtered_assignments = array_filter($assignments, function($a) use ($filter_grade, $filter_section, $filter_subject) {
    $ok = true;
    if ($filter_grade && $a['grade_id'] != $filter_grade) $ok = false;
    if ($filter_section && $a['section'] != $filter_section) $ok = false;
    if ($filter_subject && $a['subject_id'] != $filter_subject) $ok = false;
    return $ok;
});

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Courses & Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8fafc; }
        .container { max-width: 1100px; margin-top: 40px; }
        .card { border-radius: 16px; box-shadow: 0 4px 24px #6366f11a; margin-bottom: 2rem; }
        .table thead th { background: #2563eb; color: #fff; }
        .filter-form { margin-bottom: 1.5rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4><i class="fas fa-book me-2"></i>My Courses & Students</h4>
            <a href="teacherhome.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Dashboard</a>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 filter-form">
                <div class="col-md-3">
                    <label for="grade_id" class="form-label">Grade</label>
                    <select name="grade_id" id="grade_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Grades</option>
                        <?php foreach ($grades as $gid => $gname): ?>
                            <option value="<?php echo $gid; ?>" <?php if ($filter_grade == $gid) echo 'selected'; ?>><?php echo htmlspecialchars($gname); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="section" class="form-label">Section</label>
                    <select name="section" id="section" class="form-select" onchange="this.form.submit()">
                        <option value="">All Sections</option>
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?php echo htmlspecialchars($sec); ?>" <?php if ($filter_section == $sec) echo 'selected'; ?>><?php echo htmlspecialchars($sec); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="subject_id" class="form-label">Subject</label>
                    <select name="subject_id" id="subject_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Subjects</option>
                        <?php foreach ($subjects as $sid => $sname): ?>
                            <option value="<?php echo $sid; ?>" <?php if ($filter_subject == $sid) echo 'selected'; ?>><?php echo htmlspecialchars($sname); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
            <?php if (empty($filtered_assignments)): ?>
                <div class="alert alert-info text-center">No courses found for your selected filters.</div>
            <?php endif; ?>
            <?php foreach ($filtered_assignments as $assignment): ?>
                <?php
                // Get students for this grade and section
                $students_sql = "SELECT s.id, s.username, s.email, s.full_name, s.grade_id, s.section, g.name as grade_name
                    FROM students s
                    JOIN grades g ON s.grade_id = g.id
                    WHERE s.grade_id = ? AND s.section = ?
                    ORDER BY s.full_name";
                $stmt = mysqli_prepare($conn, $students_sql);
                mysqli_stmt_bind_param($stmt, "is", $assignment['grade_id'], $assignment['section']);
                mysqli_stmt_execute($stmt);
                $students_result = mysqli_stmt_get_result($stmt);
                ?>
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-book-open me-2"></i>
                            <?php echo htmlspecialchars($assignment['subject']); ?> - Grade <?php echo htmlspecialchars($assignment['grade']); ?> (Section <?php echo htmlspecialchars($assignment['section']); ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($students_result && mysqli_num_rows($students_result) > 0): ?>
                            <table class="table table-bordered table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Grade</th>
                                        <th>Section</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($students_result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['grade_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['section']); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="alert alert-warning text-center mb-0">No students found for this class.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?> 