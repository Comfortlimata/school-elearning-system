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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user input
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $program = mysqli_real_escape_string($conn, $_POST['program']);

    // Insert data into the database
    $sql = "INSERT INTO students (username, email, password, program) VALUES ('$username', '$email', '$password', '$program')";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        echo "<script>alert('Students added successfully.');</script>";
    } else {
        echo "<script>alert('Error adding students.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Students</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3 class="text-center">Add Students</h3>
    <form method="POST" action="add_student.php">
        <div class="mb-3">
            <label for="username" class="form-label">Username:</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="program" class="form-label">Select Program:</label>
            <select name="program" id="program" class="form-control" required>
                <option value="">-- Select Program --</option>
                <option value="Computer Science">Computer Science</option>
                <option value="Engineering">Engineering</option>
                <option value="Mathematics">Mathematics</option>
                <option value="Business Administration">Business Administration</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Students</button>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>