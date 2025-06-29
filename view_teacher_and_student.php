<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect unauthorized users
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$show = $_GET['show'] ?? 'students';

if ($show === 'teachers') {
    $result = mysqli_query($conn, "SELECT id, username, email, name AS full_name, grade_section AS section, department, status, created_at FROM teacher ORDER BY created_at DESC");
} else {
    $result = mysqli_query($conn, "SELECT id, username, email, full_name, section, grade_id, created_at FROM students ORDER BY created_at DESC");
}

// Get statistics
$total_students = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM students"))[0];
$active_students = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM students WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"))[0];

// Handle delete student
if (isset($_POST['delete_student'])) {
    $student_id = (int)$_POST['student_id'];
    $del_sql = "DELETE FROM students WHERE id = ?";
    $stmt = mysqli_prepare($conn, $del_sql);
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    if (mysqli_stmt_execute($stmt)) {
        $msg = "Student deleted successfully!";
    } else {
        $msg = "Error deleting student!";
    }
    header("Location: view_student.php");
    exit();
}

// Handle edit student
if (isset($_POST['edit_student'])) {
    $student_id = (int)$_POST['student_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $grade_id = (int)$_POST['grade_id'];
    $section = trim($_POST['section']);
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $gender = trim($_POST['gender']);
    $update_sql = "UPDATE students SET username=?, email=?, grade_id=?, section=?, full_name=?, phone=?, address=?, date_of_birth=?, gender=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "sssisssssi", $username, $email, $grade_id, $section, $full_name, $phone, $address, $date_of_birth, $gender, $student_id);
    if (mysqli_stmt_execute($stmt)) {
        $msg = "Student updated successfully!";
    } else {
        $msg = "Error updating student!";
    }
    header("Location: view_student.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Teacher and Student</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8fafc; }
        .container { max-width: 1100px; margin-top: 40px; }
        .card { border-radius: 16px; box-shadow: 0 4px 24px #6366f11a; }
        .table thead th { background: #6366f1; color: #fff; }
        .btn-group .btn { min-width: 140px; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4><i class="fas fa-users me-2"></i>View Teacher and Student</h4>
            <div class="btn-group" role="group">
                <a href="view_teacher_and_student.php?show=students" class="btn btn-outline-primary<?= $show !== 'teachers' ? ' active' : '' ?>">Students</a>
                <a href="view_teacher_and_student.php?show=teachers" class="btn btn-outline-primary<?= $show === 'teachers' ? ' active' : '' ?>">Teachers</a>
                <a href="adminhome.php" class="btn btn-secondary ms-2"><i class="fas fa-arrow-left me-1"></i>Back to Dashboard</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <?php if ($show === 'teachers'): ?>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Section</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                        <?php else: ?>
                        <tr>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Section</th>
                            <th>Grade ID</th>
                            <th>Created At</th>
                        </tr>
                        <?php endif; ?>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <?php if ($show === 'teachers'): ?>
                                    <td><?= htmlspecialchars((string)$row['full_name']) ?></td>
                                    <td><?= htmlspecialchars((string)$row['username']) ?></td>
                                    <td><?= htmlspecialchars((string)$row['email']) ?></td>
                                    <td><?= htmlspecialchars((string)$row['section']) ?></td>
                                    <td><?= htmlspecialchars((string)$row['department']) ?></td>
                                    <td><?= htmlspecialchars((string)$row['status']) ?></td>
                                    <td><?= htmlspecialchars((string)$row['created_at']) ?></td>
                                <?php else: ?>
                                    <td><?= htmlspecialchars((string)$row['full_name']) ?></td>
                                    <td><?= htmlspecialchars((string)$row['username']) ?></td>
                                    <td><?= htmlspecialchars((string)$row['email']) ?></td>
                                    <td><?= htmlspecialchars((string)$row['section']) ?></td>
                                    <td><?= htmlspecialchars((string)$row['grade_id']) ?></td>
                                    <td><?= htmlspecialchars((string)$row['created_at']) ?></td>
                                <?php endif; ?>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">No records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
