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
    
    // Handle assignment status updates
    if (isset($_POST['update_assignment'])) {
        $assignment_id = (int)$_POST['assignment_id'];
        $new_status = mysqli_real_escape_string($data, $_POST['new_status']);
        $grade = mysqli_real_escape_string($data, $_POST['grade']);
        $score = (float)$_POST['score'];
        $teacher_comments = mysqli_real_escape_string($data, $_POST['teacher_comments']);
        
        $update_assignment_sql = "UPDATE student_assignments SET status = ?, grade = ?, score = ?, teacher_comments = ? WHERE id = ?";
        $stmt = mysqli_prepare($data, $update_assignment_sql);
        mysqli_stmt_bind_param($stmt, "ssdsi", $new_status, $grade, $score, $teacher_comments, $assignment_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Assignment updated successfully!";
        } else {
            $error_message = "Failed to update assignment: " . mysqli_error($data);
        }
    }
    
    // Handle new assignment creation
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
}

// Get students in teacher's courses
$students_sql = "SELECT DISTINCT s.id, s.username, s.full_name, s.program, s.email 
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

// Get student assignments
$assignments_sql = "SELECT sa.*, s.full_name as student_name, c.course_name 
                   FROM student_assignments sa 
                   JOIN students s ON sa.student_id = s.id 
                   JOIN courses c ON sa.course_id = c.id 
                   WHERE c.program = ? 
                   ORDER BY sa.due_date DESC";
$stmt = mysqli_prepare($data, $assignments_sql);
mysqli_stmt_bind_param($stmt, "s", $teacher['specialization']);
mysqli_stmt_execute($stmt);
$assignments_result = mysqli_stmt_get_result($stmt);

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
    <title>Student Management - Teacher Dashboard</title>

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
        
        /* Management Content */
        .management-content {
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
                <a href="teacher_courses.php" class="sidebar-link">
                    <i class="fas fa-book sidebar-icon"></i>
                    My Courses
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="teacher_student_management.php" class="sidebar-link active">
                    <i class="fas fa-users sidebar-icon"></i>
                    Student Management
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="teacher_schedule_management.php" class="sidebar-link">
                    <i class="fas fa-calendar sidebar-icon"></i>
                    Schedule Management
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="teacher_profile.php" class="sidebar-link">
                    <i class="fas fa-user sidebar-icon"></i>
                    Profile
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
                <h1 class="header-title mb-0">Student Management</h1>
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

        <!-- Management Content -->
        <div class="management-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Student Management Dashboard</h1>
                <p class="page-subtitle">Manage student grades, assignments, and academic progress</p>
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

            <!-- Create New Assignment -->
            <div class="management-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus me-2"></i>
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
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Maximum Score</label>
                                    <input type="number" name="max_score" class="form-control" value="100" min="1" max="100" required>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="create_assignment" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Assignment
                        </button>
                    </form>
                </div>
            </div>

            <!-- Student Assignments -->
            <div class="management-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tasks me-2"></i>
                        Student Assignments
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
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Grade</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($assignments_result) > 0): ?>
                                    <?php while ($assignment = mysqli_fetch_assoc($assignments_result)): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($assignment['student_name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($assignment['course_name']); ?></td>
                                        <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo date('M d, Y', strtotime($assignment['due_date'])); ?></span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $assignment['status']; ?>">
                                                <?php echo ucfirst($assignment['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($assignment['grade']): ?>
                                                <span class="badge bg-success"><?php echo htmlspecialchars($assignment['grade']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">Not graded</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" onclick="editAssignment(<?php echo $assignment['id']; ?>)">
                                                <i class="fas fa-edit"></i> Grade
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            <i class="fas fa-info-circle me-2"></i>No assignments found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Student Grades -->
            <div class="management-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar me-2"></i>
                        Student Grades
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
                                    <th>Grade</th>
                                    <th>Score</th>
                                    <th>Submitted Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($grades_result) > 0): ?>
                                    <?php while ($grade = mysqli_fetch_assoc($grades_result)): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($grade['student_name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($grade['course_name']); ?></td>
                                        <td><?php echo htmlspecialchars($grade['assignment_name']); ?></td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($grade['grade']); ?></span>
                                        </td>
                                        <td><?php echo $grade['score']; ?>/<?php echo $grade['max_score']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($grade['submitted_date'])); ?></td>
                                        <td>
                                            <button class="btn btn-success btn-sm" onclick="editGrade(<?php echo $grade['id']; ?>)">
                                                <i class="fas fa-edit"></i> Update
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
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
        
        // Edit functions (placeholder - in real implementation, these would open modals)
        function editAssignment(id) {
            alert('Edit assignment with ID: ' + id + '\nThis would open a modal to update the assignment grade and comments.');
        }
        
        function editGrade(id) {
            alert('Edit grade with ID: ' + id + '\nThis would open a modal to update the grade and comments.');
        }
    </script>
</body>
</html> 