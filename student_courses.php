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

// Get student's program
$program = $_SESSION['program'];

// Fetch courses for the student's program
$sql = "SELECT * FROM courses WHERE program = '".mysqli_real_escape_string($conn, $program)."'";
$courses = mysqli_query($conn, $sql);

if (!$courses) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Courses</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
    <h3 class="text-center mb-4">Courses for <?= htmlspecialchars($program) ?></h3>
    <table class="table table-striped table-hover table-bordered align-middle">
        <thead class="table-primary">
            <tr>
                <th>Course Name</th>
                <th>Code</th>
                <th>Description</th>
                <th>Document</th>
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
                        <a href="<?= htmlspecialchars($row['document_path']) ?>" target="_blank" class="btn btn-sm btn-success">Download</a>
                    <?php } else { ?>
                        <span class="text-muted">No document</span>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <div class="text-center mt-3">
        <a href="studenthome.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
