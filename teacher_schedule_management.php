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

// Get teacher information
$teacher_id = $_SESSION['teacher_id'];
$teacher_sql = "SELECT * FROM teacher WHERE id = ?";
$stmt = mysqli_prepare($data, $teacher_sql);
mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);
$teacher_result = mysqli_stmt_get_result($stmt);
$teacher = mysqli_fetch_assoc($teacher_result);

// Handle form submission for updating schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Schedule Management - Teacher Dashboard</title>

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
        
        /* Schedule Management Content */
        .schedule-content {
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
        
        /* Form and Table Styles */
        .schedule-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }
        
        .schedule-header {
            background: var(--light-color);
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            border-radius: 15px 15px 0 0;
        }
        
        .schedule-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-color);
            margin: 0;
        }
        
        .schedule-body {
            padding: 1.5rem;
        }
        
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
                <a href="teacher_students.php" class="sidebar-link">
                    <i class="fas fa-users sidebar-icon"></i>
                    My Students
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="teacher_assignments.php" class="sidebar-link">
                    <i class="fas fa-tasks sidebar-icon"></i>
                    Assignments
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="teacher_grades.php" class="sidebar-link">
                    <i class="fas fa-chart-bar sidebar-icon"></i>
                    Grades
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="teacher_schedule_management.php" class="sidebar-link active">
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
                <h1 class="header-title mb-0">Schedule Management</h1>
            </div>
            
            <div class="header-actions">
                <div class="teacher-info">
                    <div class="teacher-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                        <small class="text-muted"><?php echo htmlspecialchars($teacher['specialization']); ?> Teacher</small>
                    </div>
                </div>
                
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>

        <!-- Schedule Management Content -->
        <div class="schedule-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Student Schedule Management</h1>
                <p class="page-subtitle">Update and manage student class schedules</p>
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

            <!-- Add/Update Schedule Form -->
            <div class="schedule-card">
                <div class="schedule-header">
                    <h3 class="schedule-title">
                        <i class="fas fa-plus me-2"></i>
                        Add/Update Schedule Entry
                    </h3>
                </div>
                <div class="schedule-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Student</label>
                                    <select name="student_id" class="form-select" required>
                                        <option value="">-- Select Student --</option>
                                        <?php while ($student = mysqli_fetch_assoc($students_result)): ?>
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
                                        <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
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
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Room</label>
                                    <input type="text" name="room" class="form-control" placeholder="e.g., Room 201" required>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_schedule" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Schedule
                        </button>
                    </form>
                </div>
            </div>

            <!-- Current Schedules Table -->
            <div class="schedule-card">
                <div class="schedule-header">
                    <h3 class="schedule-title">
                        <i class="fas fa-list me-2"></i>
                        Current Schedules
                    </h3>
                </div>
                <div class="schedule-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-user me-2"></i>Student</th>
                                    <th><i class="fas fa-book me-2"></i>Course</th>
                                    <th><i class="fas fa-calendar me-2"></i>Day</th>
                                    <th><i class="fas fa-clock me-2"></i>Time</th>
                                    <th><i class="fas fa-map-marker-alt me-2"></i>Room</th>
                                    <th><i class="fas fa-cogs me-2"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($schedules_result) > 0): ?>
                                    <?php while ($schedule = mysqli_fetch_assoc($schedules_result)): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($schedule['student_name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($schedule['course_name']); ?></td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($schedule['day_of_week']); ?></span>
                                        </td>
                                        <td>
                                            <?php echo date('H:i', strtotime($schedule['start_time'])); ?> - 
                                            <?php echo date('H:i', strtotime($schedule['end_time'])); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($schedule['room']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-warning" onclick="editSchedule(<?php echo $schedule['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteSchedule(<?php echo $schedule['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <i class="fas fa-info-circle me-2"></i>No schedules found
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
        
        // Edit and delete functions (placeholder)
        function editSchedule(id) {
            alert('Edit schedule with ID: ' + id);
            // In a real implementation, this would populate the form with existing data
        }
        
        function deleteSchedule(id) {
            if (confirm('Are you sure you want to delete this schedule entry?')) {
                // In a real implementation, this would send a DELETE request
                alert('Delete schedule with ID: ' + id);
            }
        }
    </script>
</body>
</html> 