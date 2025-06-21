<?php
session_start();

// Check if teacher is logged in
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'teacher') {
    header("Location: teacher_login.php");
    exit();
}

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$data = mysqli_connect($host, $user, $password, $db);

if (!$data) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get teacher information
$teacher_id = $_SESSION['teacher_id'];
$teacher_sql = "SELECT * FROM teachers WHERE id = ?";
$stmt = mysqli_prepare($data, $teacher_sql);
mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);
$teacher_result = mysqli_stmt_get_result($stmt);
$teacher = mysqli_fetch_assoc($teacher_result);

// Get courses for teacher's specialization
$specialization = $teacher['specialization'];
$courses_sql = "SELECT * FROM courses WHERE program = ?";
$stmt = mysqli_prepare($data, $courses_sql);
mysqli_stmt_bind_param($stmt, "s", $specialization);
mysqli_stmt_execute($stmt);
$courses_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Courses - Teacher Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --border-color: #e5e7eb;
        }
        
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: var(--light-color);
        }
        
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            padding: 2rem;
        }
        
        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
        }
        
        .course-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .course-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem;
        }
        
        .course-body {
            padding: 1.5rem;
        }
        
        .course-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .course-code {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 1rem;
        }
        
        .course-description {
            color: #6b7280;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        
        .course-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            border-radius: 8px;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Include Sidebar -->
    <?php include 'adminsidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-book me-3"></i>
                My Courses
            </h1>
            <p class="page-subtitle">Courses for <?php echo htmlspecialchars($teacher['specialization']); ?> specialization</p>
        </div>

        <!-- Courses Grid -->
        <div class="row">
            <?php if (mysqli_num_rows($courses_result) > 0): ?>
                <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="course-card">
                            <div class="course-header">
                                <h5 class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></h5>
                                <div class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></div>
                            </div>
                            <div class="course-body">
                                <p class="course-description">
                                    <?php echo htmlspecialchars($course['course_description']); ?>
                                </p>
                                <div class="course-actions">
                                    <a href="<?php echo htmlspecialchars($course['document_path']); ?>" 
                                       target="_blank" class="btn btn-primary btn-sm">
                                        <i class="fas fa-download me-1"></i>Download
                                    </a>
                                    <button class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-book fa-3x text-muted mb-3"></i>
                        <h3 class="text-muted">No Courses Found</h3>
                        <p class="text-muted">No courses are currently assigned to your specialization.</p>
                        <a href="teacherhome.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 