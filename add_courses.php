<?php
session_start();

// Only admins can add courses
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "schoolproject");

if (!$conn) die("Connection failed: " . mysqli_connect_error());

if (isset($_POST['add_course'])) {
    $name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $code = mysqli_real_escape_string($conn, $_POST['course_code']);
    $desc = mysqli_real_escape_string($conn, $_POST['course_description']);
    
    // File Upload
    $filePath = '';
    if (isset($_FILES['course_file']) && $_FILES['course_file']['error'] == 0) {
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $fileType = $_FILES['course_file']['type'];

        if (!in_array($fileType, $allowedTypes)) {
            echo "<script>alert('Invalid file type. Only PDF, DOC, and DOCX allowed.');</script>";
        } else {
            $uploadDir = "uploads/";
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
            
            // Generate unique filename to avoid overwrite
            $fileName = time() . '_' . basename($_FILES["course_file"]["name"]);
            $filePath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($_FILES["course_file"]["tmp_name"], $filePath)) {
                echo "<script>alert('Failed to upload file. Please try again.');</script>";
                $filePath = '';
            }
        }
    }

    $sql = "INSERT INTO courses (course_name, course_code, course_description, document_path)
            VALUES ('$name', '$code', '$desc', '$filePath')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Course added successfully');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Course</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin-top: 80px;
        }
        .card {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .form-label {
            font-weight: 600;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Admin Dashboard</a>
        <div class="d-flex">
            <span class="text-white me-3">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<!-- Main Form Card -->
<div class="container">
    <div class="card">
        <div class="card-header bg-primary text-white text-center">
            <h4>Add New Course</h4>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="course_name" class="form-label">Course Name</label>
                    <input type="text" class="form-control" name="course_name" id="course_name" required>
                </div>
                <div class="mb-3">
                    <label for="course_code" class="form-label">Course Code</label>
                    <input type="text" class="form-control" name="course_code" id="course_code" required>
                </div>
                <div class="mb-3">
                    <label for="course_description" class="form-label">Course Description</label>
                    <textarea class="form-control" name="course_description" id="course_description" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="course_file" class="form-label">Attach Document (PDF, DOC, DOCX)</label>
                    <input class="form-control" type="file" name="course_file" id="course_file" accept=".pdf,.doc,.docx">
                </div>
                <button type="submit" name="add_course" class="btn btn-primary w-100">Add Course</button>
            </form>

            <!-- Back button -->
            <div class="mt-3 text-center">
                <a href="admission.php" class="btn btn-secondary">Back to Admission Page</a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>