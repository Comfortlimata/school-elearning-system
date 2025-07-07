<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
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
$grade_id = $_SESSION['grade_id'] ?? null;
$section = $_SESSION['section'] ?? '';
$subjects = false;
if ($grade_id && $section) {
    $sql = "SELECT s.id as subject_id, s.name FROM grade_subject_assignments gsa JOIN subjects s ON gsa.subject_id = s.id WHERE gsa.grade_id = ? AND gsa.section = ? ORDER BY s.name";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $grade_id, $section);
    mysqli_stmt_execute($stmt);
    $subjects = mysqli_stmt_get_result($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Subjects</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <h3 class="text-center mb-4">
            <i class="fas fa-book-open me-2"></i>
            My Subjects
        </h3>
        <?php if ($subjects && mysqli_num_rows($subjects) > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Subject Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($subject = mysqli_fetch_assoc($subjects)): ?>
                        <tr>
                            <td><a href="student_subject_materials.php?subject_id=<?php echo $subject['subject_id']; ?>" class="btn btn-link"><?php echo htmlspecialchars($subject['name']); ?></a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info text-center">
                No subjects found for your grade and section.
            </div>
        <?php endif; ?>
        <div class="text-center mt-4">
            <a href="studenthome.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html> 