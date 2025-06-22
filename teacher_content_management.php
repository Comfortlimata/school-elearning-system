<?php
session_start();

// Check if teacher is logged in
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'teacher') {
    header("Location: login.php");
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

$teacher_id = $_SESSION['teacher_id'];
$teacher_username = $_SESSION['username'];

// Get teacher information
$teacher_sql = "SELECT * FROM teacher WHERE id = ?";
$stmt = mysqli_prepare($data, $teacher_sql);
mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);
$teacher_result = mysqli_stmt_get_result($stmt);
$teacher = mysqli_fetch_assoc($teacher_result);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Handle schedule updates
    if (isset($_POST['update_schedule'])) {
        $student_id = (int)$_POST['student_id'];
        $course_id = (int)$_POST['course_id'];
        $day_of_week = mysqli_real_escape_string($data, $_POST['day_of_week']);
        $start_time = mysqli_real_escape_string($data, $_POST['start_time']);
        $end_time = mysqli_real_escape_string($data, $_POST['end_time']);
        $room = mysqli_real_escape_string($data, $_POST['room']);
        
        // Check if schedule entry exists
        $check_sql = "SELECT id FROM student_schedule WHERE student_id = ? AND course_id = ? AND day_of_week = ?";
        $check_stmt = mysqli_prepare($data, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "iis", $student_id, $course_id, $day_of_week);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update existing schedule
            $update_sql = "UPDATE student_schedule SET start_time = ?, end_time = ?, room = ?, updated_at = NOW() 
                          WHERE student_id = ? AND course_id = ? AND day_of_week = ?";
            $update_stmt = mysqli_prepare($data, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "sssiis", $start_time, $end_time, $room, $student_id, $course_id, $day_of_week);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $success_message = "Schedule updated successfully!";
            } else {
                $error_message = "Failed to update schedule: " . mysqli_error($data);
            }
        } else {
            // Insert new schedule
            $insert_sql = "INSERT INTO student_schedule (student_id, course_id, day_of_week, start_time, end_time, room, teacher_id) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($data, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, "iissssi", $student_id, $course_id, $day_of_week, $start_time, $end_time, $room, $teacher_id);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                $success_message = "Schedule entry added successfully!";
            } else {
                $error_message = "Failed to add schedule: " . mysqli_error($data);
            }
        }
    }
    
    // Handle assignment creation
    if (isset($_POST['create_assignment'])) {
        $course_id = (int)$_POST['course_id'];
        $title = mysqli_real_escape_string($data, $_POST['title']);
        $description = mysqli_real_escape_string($data, $_POST['description']);
        $due_date = mysqli_real_escape_string($data, $_POST['due_date']);
        $max_score = (float)$_POST['max_score'];
        
        $create_assignment_sql = "INSERT INTO teacher_assignments (teacher_id, course_id, title, description, due_date, max_score) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($data, $create_assignment_sql);
        mysqli_stmt_bind_param($stmt, "iissds", $teacher_id, $course_id, $title, $description, $due_date, $max_score);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Assignment created successfully!";
        } else {
            $error_message = "Failed to create assignment: " . mysqli_error($data);
        }
    }
    
    // Handle grade updates
    if (isset($_POST['update_grade'])) {
        $grade_id = (int)$_POST['grade_id'];
        $new_grade = mysqli_real_escape_string($data, $_POST['new_grade']);
        $new_score = (float)$_POST['new_score'];
        $comments = mysqli_real_escape_string($data, $_POST['comments']);
        
        $update_grade_sql = "UPDATE student_grades SET grade = ?, score = ?, comments = ?, graded_date = CURDATE() WHERE id = ?";
        $stmt = mysqli_prepare($data, $update_grade_sql);
        mysqli_stmt_bind_param($stmt, "sdsi", $new_grade, $new_score, $comments, $grade_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Grade updated successfully!";
        } else {
            $error_message = "Failed to update grade: " . mysqli_error($data);
        }
    }
    
    // Handle material upload
    if (isset($_POST['upload_material'])) {
        $course_id = (int)$_POST['course_id'];
        $title = mysqli_real_escape_string($data, $_POST['material_title']);
        $description = mysqli_real_escape_string($data, $_POST['material_description']);
        
        // Handle file upload
        if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] == 0) {
            $file_name = $_FILES['material_file']['name'];
            $file_tmp = $_FILES['material_file']['tmp_name'];
            $file_size = $_FILES['material_file']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Allowed file types
            $allowed = array('pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png');
            
            if (in_array($file_ext, $allowed)) {
                $upload_dir = 'uploads/';
                $new_file_name = time() . '_' . $file_name;
                $upload_path = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Insert into database (you'll need to create a materials table)
                    $insert_material_sql = "INSERT INTO course_materials (teacher_id, course_id, title, description, file_path, file_name, file_size, file_type, download_count, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($data, $insert_material_sql);
                    mysqli_stmt_bind_param($stmt, "iisssssisss", $teacher_id, $course_id, $title, $description, $upload_path, $file_name, $file_size, $file_ext, 0, 1, NOW(), NOW());
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $success_message = "Material uploaded successfully!";
                    } else {
                        $error_message = "Failed to save material: " . mysqli_error($data);
                    }
                } else {
                    $error_message = "Failed to upload file.";
                }
            } else {
                $error_message = "Invalid file type. Allowed: PDF, DOC, DOCX, PPT, PPTX, TXT, JPG, PNG";
            }
        } else {
            $error_message = "Please select a file to upload.";
        }
    }
}

// Get students in teacher's courses
$students_sql = "SELECT DISTINCT s.id, s.username, s.full_name, s.program, c.course_name 
                FROM students s 
                JOIN courses c ON s.program = c.program 
                WHERE c.program = ? 
                ORDER BY s.full_name";
$stmt = mysqli_prepare($data, $students_sql);
mysqli_stmt_bind_param($stmt, "s", $teacher['specialization']);
mysqli_stmt_execute($stmt);
$students_result = mysqli_stmt_get_result($stmt);

// Get courses for teacher's specialization
$courses_sql = "SELECT * FROM courses WHERE program = ?";
$stmt = mysqli_prepare($data, $courses_sql);
mysqli_stmt_bind_param($stmt, "s", $teacher['specialization']);
mysqli_stmt_execute($stmt);
$courses_result = mysqli_stmt_get_result($stmt);

// Get current schedules
$schedules_sql = "SELECT ss.*, s.full_name as student_name, c.course_name 
                 FROM student_schedule ss 
                 JOIN students s ON ss.student_id = s.id 
                 JOIN courses c ON ss.course_id = c.id 
                 WHERE ss.teacher_id = ? 
                 ORDER BY ss.day_of_week, ss.start_time";
$stmt = mysqli_prepare($data, $schedules_sql);
mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);
$schedules_result = mysqli_stmt_get_result($stmt);

// Get student grades
$grades_sql = "SELECT sg.*, s.full_name as student_name, c.course_name 
               FROM student_grades sg 
               JOIN students s ON sg.student_id = s.id 
               JOIN courses c ON sg.course_id = c.id 
               WHERE c.program = ? 
               ORDER BY sg.submitted_date DESC";
$stmt = mysqli_prepare($data, $grades_sql);
mysqli_stmt_bind_param($stmt, "s", $teacher['specialization']);
mysqli_stmt_execute($stmt);
$grades_result = mysqli_stmt_get_result($stmt);

// Get teacher's assignments
$teacher_assignments_sql = "SELECT ta.*, c.course_name 
                           FROM teacher_assignments ta 
                           JOIN courses c ON ta.course_id = c.id 
                           WHERE ta.teacher_id = ? 
                           ORDER BY ta.due_date DESC";
$stmt = mysqli_prepare($data, $teacher_assignments_sql);
mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);
$teacher_assignments_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Content Management - Teacher Dashboard</title>

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
            --info-color: #3b82f6;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --border-color: #e5e7eb;
        }
        
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: var(--light-color);
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 280px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        
        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }
        
        .sidebar-brand:hover {
            color: white;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .sidebar-item {
            margin: 0.5rem 1rem;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .sidebar-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .sidebar-icon {
            width: 20px;
            margin-right: 0.75rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }
        
        /* Header */
        .top-header {
            background: white;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .teacher-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .teacher-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .logout-btn {
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #dc2626;
            color: white;
            transform: translateY(-2px);
        }
        
        /* Content Management */
        .content-management {
            padding: 2rem;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        /* Card Styles */
        .management-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }
        
        .card-header {
            background: var(--light-color);
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            border-radius: 15px 15px 0 0;
        }
        
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-color);
            margin: 0;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid var(--border-color);
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }
        
        .btn-success {
            background: var(--success-color);
            border: none;
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-warning {
            background: var(--warning-color);
            border: none;
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        /* Table Styles */
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table th {
            background: var(--light-color);
            border: none;
            padding: 1rem;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .table td {
            border: none;
            padding: 1rem;
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background: var(--light-color);
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-submitted { background: #dbeafe; color: #1e40af; }
        .status-graded { background: #d1fae5; color: #065f46; }
        .status-overdue { background: #fee2e2; color: #991b1b; }
        
        /* Navigation Tabs */
        .nav-tabs .nav-link {
            border-radius: 10px 10px 0 0;
            border: none;
            color: var(--dark-color);
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
        
        /* Toggle Button */
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark-color);
        }
        
        @media (max-width: 768px) {
            .sidebar-toggle {
                display: block;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-brand">
                <i class="fas fa-chalkboard-teacher me-2"></i>
                Teacher Portal
            </a>
        </div>
        
        <div class="sidebar-menu">
            <div class="sidebar-item">
                <a href="teacherhome.php" class="sidebar-link">
                    <i class="fas fa-tachometer-alt sidebar-icon"></i>
                    Dashboard
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="teacher_content_management.php" class="sidebar-link active">
                    <i class="fas fa-edit sidebar-icon"></i>
                    Content Management
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="teacher_grade_management.php" class="sidebar-link">
                    <i class="fas fa-chart-bar sidebar-icon"></i>
                    Grade Management
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="teacher_schedule_management.php" class="sidebar-link">
                    <i class="fas fa-calendar sidebar-icon"></i>
                    Schedule Management
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="logout.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt sidebar-icon"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="top-header">
            <div class="d-flex align-items-center">
                <button class="sidebar-toggle me-3" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="header-title mb-0">Content Management</h1>
            </div>
            
            <div class="header-actions">
                <div class="teacher-info">
                    <div class="teacher-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div class="fw-bold"><?php echo htmlspecialchars($teacher_username); ?></div>
                        <small class="text-muted"><?php echo htmlspecialchars($teacher['specialization']); ?> Teacher</small>
                    </div>
                </div>
                
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>

        <!-- Content Management -->
        <div class="content-management">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Student Content Management</h1>
                <p class="page-subtitle">Update schedules, assignments, grades, and materials for your students</p>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs" id="contentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab">
                        <i class="fas fa-calendar me-2"></i>Schedule
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="assignments-tab" data-bs-toggle="tab" data-bs-target="#assignments" type="button" role="tab">
                        <i class="fas fa-tasks me-2"></i>Assignments
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="grades-tab" data-bs-toggle="tab" data-bs-target="#grades" type="button" role="tab">
                        <i class="fas fa-chart-bar me-2"></i>Grades
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="materials-tab" data-bs-toggle="tab" data-bs-target="#materials" type="button" role="tab">
                        <i class="fas fa-file-upload me-2"></i>Materials
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="contentTabsContent">
                <!-- Schedule Tab -->
                <div class="tab-pane fade show active" id="schedule" role="tabpanel">
                    <div class="management-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar me-2"></i>
                                Manage Student Schedules
                            </h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Student</label>
                                            <select name="student_id" class="form-select" required>
                                                <option value="">-- Select Student --</option>
                                                <?php 
                                                mysqli_data_seek($students_result, 0);
                                                while ($student = mysqli_fetch_assoc($students_result)): 
                                                ?>
                                                    <option value="<?php echo $student['id']; ?>">
                                                        <?php echo htmlspecialchars($student['full_name']); ?> (<?php echo htmlspecialchars($student['username']); ?>)
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Course</label>
                                            <select name="course_id" class="form-select" required>
                                                <option value="">-- Select Course --</option>
                                                <?php 
                                                mysqli_data_seek($courses_result, 0);
                                                while ($course = mysqli_fetch_assoc($courses_result)): 
                                                ?>
                                                    <option value="<?php echo $course['id']; ?>">
                                                        <?php echo htmlspecialchars($course['course_name']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label">Day of Week</label>
                                            <select name="day_of_week" class="form-select" required>
                                                <option value="">-- Select Day --</option>
                                                <option value="Monday">Monday</option>
                                                <option value="Tuesday">Tuesday</option>
                                                <option value="Wednesday">Wednesday</option>
                                                <option value="Thursday">Thursday</option>
                                                <option value="Friday">Friday</option>
                                                <option value="Saturday">Saturday</option>
                                                <option value="Sunday">Sunday</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label">Start Time</label>
                                            <input type="time" name="start_time" class="form-control" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label">End Time</label>
                                            <input type="time" name="end_time" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Room</label>
                                    <input type="text" name="room" class="form-control" placeholder="e.g., Room 201" required>
                                </div>
                                
                                <button type="submit" name="update_schedule" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Schedule
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Assignments Tab -->
                <div class="tab-pane fade" id="assignments" role="tabpanel">
                    <div class="management-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-tasks me-2"></i>
                                Create New Assignment
                            </h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Course</label>
                                            <select name="course_id" class="form-select" required>
                                                <option value="">-- Select Course --</option>
                                                <?php 
                                                mysqli_data_seek($courses_result, 0);
                                                while ($course = mysqli_fetch_assoc($courses_result)): 
                                                ?>
                                                    <option value="<?php echo $course['id']; ?>">
                                                        <?php echo htmlspecialchars($course['course_name']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Due Date</label>
                                            <input type="date" name="due_date" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Assignment Title</label>
                                    <input type="text" name="title" class="form-control" placeholder="Enter assignment title" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="Enter assignment description"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Maximum Score</label>
                                    <input type="number" name="max_score" class="form-control" value="100" min="1" max="100" required>
                                </div>
                                
                                <button type="submit" name="create_assignment" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create Assignment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Grades Tab -->
                <div class="tab-pane fade" id="grades" role="tabpanel">
                    <div class="management-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar me-2"></i>
                                Update Student Grades
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Course</th>
                                            <th>Assignment</th>
                                            <th>Current Grade</th>
                                            <th>Score</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($grades_result) > 0): ?>
                                            <?php while ($grade = mysqli_fetch_assoc($grades_result)): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($grade['student_name']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($grade['course_name']); ?></td>
                                                <td><?php echo htmlspecialchars($grade['assignment_name']); ?></td>
                                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($grade['grade']); ?></span></td>
                                                <td><?php echo $grade['score']; ?>/<?php echo $grade['max_score']; ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editGradeModal<?php echo $grade['id']; ?>">
                                                        <i class="fas fa-edit"></i> Update
                                                    </button>
                                                </td>
                                            </tr>
                                            
                                            <!-- Edit Grade Modal -->
                                            <div class="modal fade" id="editGradeModal<?php echo $grade['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Update Grade</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="grade_id" value="<?php echo $grade['id']; ?>">
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Student</label>
                                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($grade['student_name']); ?>" readonly>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Assignment</label>
                                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($grade['assignment_name']); ?>" readonly>
                                                                </div>
                                                                
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Grade</label>
                                                                            <select name="new_grade" class="form-select" required>
                                                                                <option value="">Select Grade</option>
                                                                                <option value="A" <?php echo ($grade['grade'] == 'A') ? 'selected' : ''; ?>>A</option>
                                                                                <option value="A-" <?php echo ($grade['grade'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                                                                                <option value="B+" <?php echo ($grade['grade'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                                                                                <option value="B" <?php echo ($grade['grade'] == 'B') ? 'selected' : ''; ?>>B</option>
                                                                                <option value="B-" <?php echo ($grade['grade'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                                                                                <option value="C+" <?php echo ($grade['grade'] == 'C+') ? 'selected' : ''; ?>>C+</option>
                                                                                <option value="C" <?php echo ($grade['grade'] == 'C') ? 'selected' : ''; ?>>C</option>
                                                                                <option value="C-" <?php echo ($grade['grade'] == 'C-') ? 'selected' : ''; ?>>C-</option>
                                                                                <option value="D" <?php echo ($grade['grade'] == 'D') ? 'selected' : ''; ?>>D</option>
                                                                                <option value="F" <?php echo ($grade['grade'] == 'F') ? 'selected' : ''; ?>>F</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Score</label>
                                                                            <input type="number" name="new_score" class="form-control" value="<?php echo $grade['score']; ?>" min="0" max="<?php echo $grade['max_score']; ?>" required>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Comments</label>
                                                                    <textarea name="comments" class="form-control" rows="3"><?php echo htmlspecialchars($grade['comments'] ?? ''); ?></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" name="update_grade" class="btn btn-primary">Update Grade</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">
                                                    <i class="fas fa-info-circle me-2"></i>No grades found
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Materials Tab -->
                <div class="tab-pane fade" id="materials" role="tabpanel">
                    <div class="management-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-upload me-2"></i>
                                Upload Course Materials
                            </h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Course</label>
                                            <select name="course_id" class="form-select" required>
                                                <option value="">-- Select Course --</option>
                                                <?php 
                                                mysqli_data_seek($courses_result, 0);
                                                while ($course = mysqli_fetch_assoc($courses_result)): 
                                                ?>
                                                    <option value="<?php echo $course['id']; ?>">
                                                        <?php echo htmlspecialchars($course['course_name']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Material Title</label>
                                            <input type="text" name="material_title" class="form-control" placeholder="Enter material title" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <textarea name="material_description" class="form-control" rows="3" placeholder="Enter material description"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Upload File</label>
                                    <input type="file" name="material_file" class="form-control" required>
                                    <small class="text-muted">Allowed formats: PDF, DOC, DOCX, PPT, PPTX, TXT, JPG, PNG (Max: 10MB)</small>
                                </div>
                                
                                <button type="submit" name="upload_material" class="btn btn-primary">
                                    <i class="fas fa-upload me-2"></i>Upload Material
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Content Overview -->
            <div class="management-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list me-2"></i>
                        Current Content Overview
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-calendar me-2"></i>Recent Schedules</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Course</th>
                                            <th>Day</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $recent_schedules = mysqli_query($data, "SELECT ss.*, s.full_name as student_name, c.course_name 
                                                                                FROM student_schedule ss 
                                                                                JOIN students s ON ss.student_id = s.id 
                                                                                JOIN courses c ON ss.course_id = c.id 
                                                                                WHERE ss.teacher_id = $teacher_id 
                                                                                ORDER BY ss.updated_at DESC LIMIT 5");
                                        while ($schedule = mysqli_fetch_assoc($recent_schedules)): 
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($schedule['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($schedule['course_name']); ?></td>
                                            <td><?php echo htmlspecialchars($schedule['day_of_week']); ?></td>
                                            <td><?php echo date('H:i', strtotime($schedule['start_time'])); ?> - <?php echo date('H:i', strtotime($schedule['end_time'])); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5><i class="fas fa-tasks me-2"></i>Recent Assignments</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Course</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $recent_assignments = mysqli_query($data, "SELECT ta.*, c.course_name 
                                                                                FROM teacher_assignments ta 
                                                                                JOIN courses c ON ta.course_id = c.id 
                                                                                WHERE ta.teacher_id = $teacher_id 
                                                                                ORDER BY ta.created_at DESC LIMIT 5");
                                        while ($assignment = mysqli_fetch_assoc($recent_assignments)): 
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                            <td><?php echo htmlspecialchars($assignment['course_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($assignment['due_date'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo (strtotime($assignment['due_date']) < time()) ? 'danger' : 'success'; ?>">
                                                    <?php echo (strtotime($assignment['due_date']) < time()) ? 'Overdue' : 'Active'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
        
        // Active link highlighting
        const currentPage = window.location.pathname.split('/').pop();
        const sidebarLinks = document.querySelectorAll('.sidebar-link');
        
        sidebarLinks.forEach(link => {
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
        
        // Tab functionality
        const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabLinks.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const target = this.getAttribute('data-bs-target');
                
                // Remove active class from all tabs and content
                document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));
                
                // Add active class to clicked tab and content
                this.classList.add('active');
                document.querySelector(target).classList.add('show', 'active');
            });
        });
        
        // File upload validation
        const fileInput = document.querySelector('input[type="file"]');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                const maxSize = 10 * 1024 * 1024; // 10MB
                
                if (file && file.size > maxSize) {
                    alert('File size must be less than 10MB');
                    this.value = '';
                }
            });
        }
    </script>
</body>
</html>
