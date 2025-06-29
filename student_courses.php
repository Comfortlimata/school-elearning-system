<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check user session and usertype = 'student'
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Connect to database
$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get student's grade_id, section, and grade_name
$grade_id = $_SESSION['grade_id'] ?? null;
$section = $_SESSION['section'] ?? '';
$grade_name = $_SESSION['grade_name'] ?? '';

// Fetch courses for the student's grade and section
$courses = false;
$subjects = false;

if ($grade_id) {
    // First, get courses from the courses table that match the student's grade
    $courses_sql = "SELECT * FROM courses WHERE grade_id = ?";
    $stmt = mysqli_prepare($conn, $courses_sql);
    mysqli_stmt_bind_param($stmt, "i", $grade_id);
    mysqli_stmt_execute($stmt);
    $courses = mysqli_stmt_get_result($stmt);
    
    // Then, get subjects from grade_subject_assignments that match the student's grade and section
    $subjects_sql = "SELECT gsa.*, s.name as subject_name, s.id as subject_id, g.name as grade_name 
                     FROM grade_subject_assignments gsa 
                     JOIN subjects s ON gsa.subject_id = s.id 
                     JOIN grades g ON gsa.grade_id = g.id 
                     WHERE gsa.grade_id = ? AND gsa.section = ? 
                     ORDER BY gsa.is_required DESC, s.name";
    $stmt = mysqli_prepare($conn, $subjects_sql);
    mysqli_stmt_bind_param($stmt, "is", $grade_id, $section);
    mysqli_stmt_execute($stmt);
    $subjects = mysqli_stmt_get_result($stmt);
}

if (!$courses && !$subjects) {
    die("No courses or subjects found for your grade and section.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Courses</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container mt-5">
    <h3 class="text-center mb-4">
        <i class="fas fa-graduation-cap me-2"></i>
        Courses for Grade <?= htmlspecialchars($grade_name) ?> Section <?= htmlspecialchars($section) ?>
    </h3>
    
    <!-- Subjects from Grade-Subject Assignments -->
    <?php if ($subjects && mysqli_num_rows($subjects) > 0): ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-book me-2"></i>Your Subjects</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php while ($subject = mysqli_fetch_assoc($subjects)) { ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100 border-<?= $subject['is_required'] ? 'success' : 'info' ?>">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-<?= $subject['is_required'] ? 'star' : 'bookmark' ?> me-2 text-<?= $subject['is_required'] ? 'success' : 'info' ?>"></i>
                                <?= htmlspecialchars($subject['subject_name']) ?>
                            </h6>
                            <p class="card-text small text-muted">
                                <?= htmlspecialchars($subject['description'] ?? 'No description available') ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-<?= $subject['is_required'] ? 'success' : 'info' ?>">
                                    <?= $subject['is_required'] ? 'Required' : 'Elective' ?>
                                </span>
                                <span class="badge bg-secondary">
                                    <?= $subject['credits'] ?> Credit<?= $subject['credits'] > 1 ? 's' : '' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Courses from Courses Table -->
    <?php if ($courses && mysqli_num_rows($courses) > 0): ?>
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>Course Materials</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover table-bordered align-middle">
                <thead class="table-success">
                    <tr>
                        <th><i class="fas fa-book me-2"></i>Course Name</th>
                        <th><i class="fas fa-code me-2"></i>Code</th>
                        <th><i class="fas fa-info-circle me-2"></i>Description</th>
                        <th><i class="fas fa-file me-2"></i>Document</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($courses)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['course_name']) ?></td>
                        <td><?= htmlspecialchars($row['course_code']) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['course_description'])) ?></td>
                        <td class="text-center">
                            <?php if (!empty($row['document_path'])) { ?>
                                <a href="<?= htmlspecialchars($row['document_path']) ?>" target="_blank" class="btn btn-sm btn-success">
                                    <i class="fas fa-download me-1"></i>Download
                                </a>
                            <?php } else { ?>
                                <span class="text-muted"><i class="fas fa-times me-1"></i>No document</span>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ((!$subjects || mysqli_num_rows($subjects) == 0) && (!$courses || mysqli_num_rows($courses) == 0)): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle me-2"></i>
        No courses or subjects have been assigned to your grade and section yet. Please contact your administrator.
    </div>
    <?php endif; ?>
    
    <div class="text-center mt-4">
        <a href="studenthome.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
