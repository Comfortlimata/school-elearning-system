<?php
session_start();

// Redirect unauthorized users
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Load environment variables
require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection
$conn = mysqli_connect($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch courses from the database
$courses = mysqli_query($conn, "SELECT * FROM courses");

if (!$courses) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Courses</title>
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
        .btn-success {
            font-size: 14px;
            padding: 5px 10px;
        }
        .text-muted {
            font-size: 14px;
        }
    </style>
</head>
<body>

<!-- Navigation Header -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Courses Portal</a>
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
            <h3>Available Courses</h3>
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
                            <?php if (!empty($row['document_path']) && file_exists($row['document_path'])) { ?>
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