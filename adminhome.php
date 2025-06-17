<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
} elseif ($_SESSION['usertype'] == 'student') {
    header("location:login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header a {
            color: white;
            text-decoration: none;
            font-size: 20px;
            font-weight: bold;
        }

        .logout a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            padding: 8px 16px;
            background-color: #dc3545;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .logout a:hover {
            background-color: #b52a37;
        }

        nav {
            background-color: #007bff;
            padding: 10px 0;
        }

        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        nav ul li {
            display: inline;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        nav ul li a:hover {
            background-color: #0056b3;
        }

        .container {
            padding: 40px 20px;
            text-align: center;
        }

        .title_deg {
            font-size: 24px;
            color: #007bff;
            margin-bottom: 20px;
        }

        .form_deg {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: inline-block;
            text-align: left;
        }

        .form_deg p {
            font-size: 16px;
            color: #333;
        }
    </style>
</head>

<body>

<header>
    <a href="#">Admin Dashboard</a>
    <div class="logout">
        <a href="logout.php">Logout</a>
    </div>
</header>

<nav>
    <ul>
        <li><a href="admission.php">Admission</a></li>
        <li><a href="add_student.php">Add Student</a></li>
        <li><a href="view_student.php">View Student</a></li>
        <li><a href="add_teacher.php">Student Profile</a></li>
        <li><a href="add_courses.php">Add Courses</a></li>
        <li><a href="view_courses.php">View Courses</a></li>
    </ul>
</nav>

<div class="container">
    <div class="form_deg">
        <h2 class="title_deg">Welcome to the Admin Dashboard</h2>
        <p>Use the navigation bar above to access different sections.</p>
    </div>
</div>

</body>

</html>