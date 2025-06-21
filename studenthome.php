<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect unauthorized access
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$program = $_SESSION['program'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 60px;
            position: fixed;
            width: 220px;
        }

        .sidebar h4 {
            margin-bottom: 30px;
            text-align: center;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            transition: 0.2s;
        }

        .sidebar a:hover {
            background-color: #495057;
        }

        .content {
            margin-left: 230px;
            padding: 30px;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 220px;
            right: 0;
            z-index: 1000;
        }

        .card {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 30px;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4><?= htmlspecialchars($username) ?> 👋</h4>
    <a href="student_courses.php">📚 View My Courses</a>
    <a href="student_results.php">📈 Results</a>
    <a href="student_account.php">👤 Account</a>
    <a href="logout.php" style="color: #ffc107;">🚪 Logout</a>
</div>

<!-- Top Navbar -->
<nav class="navbar navbar-dark bg-primary">
    <div class="container-fluid">
        <span class="navbar-brand">Student Dashboard</span>
        <span class="text-white">Program: <?= htmlspecialchars($program) ?></span>
    </div>
</nav>

<!-- Main Content -->
<div class="content">
    <div class="container">
        <div class="card">
            <h2>Welcome, <?= htmlspecialchars($username) ?>!</h2>
            <p>This is your student dashboard. Use the sidebar to access your courses, view results, and manage your account.</p>
        </div>
    </div>
</div>

</body>
</html>
