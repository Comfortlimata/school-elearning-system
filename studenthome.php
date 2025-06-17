<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect unauthorized users
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "schoolproject");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get the student's program from the session
$program = $_SESSION['program'];

// Fetch courses based on the student's program
$courses = mysqli_query($conn, "SELECT * FROM courses WHERE program = '$program'");

if (!$courses) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 60px;
        }
        .card {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        th, td {
            vertical-align: middle;
        }
        .btn-back {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<!-- Navigation Header -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Student Dashboard</a>
        <div class="d-flex">
            <span class="text-white me-3">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container">
    <div class="card">
        <div class="card-header bg-primary text-white text-center">
            <h3>Courses for <?= htmlspecialchars($program) ?></h3>
        </div>
        <div class="card-body">
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
                            <?php if ($row['document_path']) { ?>
                                <a href="<?= htmlspecialchars($row['document_path']) ?>" target="_blank" class="btn btn-sm btn-success">Download</a>
                            <?php } else { ?>
                                <span class="text-muted">No document</span>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bootstrap JS (Optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>