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

// Handle message sending
if (isset($_POST['send_message'])) {
    $student_id = $_POST['student_id'];
    $message = mysqli_real_escape_string($data, $_POST['message']);
    $teacher_id = $_SESSION['teacher_id'];
    
    $insert_message = "INSERT INTO teacher_student_messages (teacher_id, student_id, message, sender_type, created_at) 
                       VALUES (?, ?, ?, 'teacher', NOW())";
    $stmt = mysqli_prepare($data, $insert_message);
    mysqli_stmt_bind_param($stmt, "iis", $teacher_id, $student_id, $message);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Message sent successfully!";
    } else {
        $error_message = "Failed to send message.";
    }
}

// Get teacher information
$teacher_id = $_SESSION['teacher_id'];
$teacher_sql = "SELECT * FROM teacher WHERE id = ?";
$stmt = mysqli_prepare($data, $teacher_sql);
mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);
$teacher_result = mysqli_stmt_get_result($stmt);
$teacher = mysqli_fetch_assoc($teacher_result);

// Get students enrolled in teacher's assigned courses
$students_sql = "SELECT DISTINCT s.* FROM students s 
                 INNER JOIN courses c ON s.program = c.program 
                 WHERE c.teacher_id = ?";
$stmt = mysqli_prepare($data, $students_sql);
mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);
$students_result = mysqli_stmt_get_result($stmt);

// Get teacher's assigned courses
$teacher_courses_sql = "SELECT * FROM courses WHERE teacher_id = ?";
$stmt = mysqli_prepare($data, $teacher_courses_sql);
mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);
$teacher_courses_result = mysqli_stmt_get_result($stmt);

// Get recent messages
$messages_sql = "SELECT tsm.*, s.full_name as student_name, t.name as teacher_name 
                 FROM teacher_student_messages tsm 
                 LEFT JOIN students s ON tsm.student_id = s.id 
                 LEFT JOIN teacher t ON tsm.teacher_id = t.id 
                 WHERE tsm.teacher_id = ? 
                 ORDER BY tsm.created_at DESC 
                 LIMIT 10";
$stmt = mysqli_prepare($data, $messages_sql);
mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);
$messages_result = mysqli_stmt_get_result($stmt);
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
        }
        
        /* Content Styles */
        .content-section {
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .stat-icon.courses { background: var(--primary-color); }
        .stat-icon.students { background: var(--success-color); }
        .stat-icon.messages { background: var(--warning-color); }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
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
            flex-wrap: wrap;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            border-radius: 8px;
        }
        
        .communication-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }
        
        .section-header {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
            padding: 1.5rem;
            border-radius: 15px 15px 0 0;
        }
        
        .section-body {
            padding: 1.5rem;
        }
        
        .student-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .student-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .student-item:hover {
            background: var(--light-color);
        }
        
        .student-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .student-avatar {
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
        
        .message-modal .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .message-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .message-item {
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            margin-bottom: 0.5rem;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .message-sender {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .message-time {
            font-size: 0.8rem;
            color: #6b7280;
        }
        
        .message-content {
            color: var(--dark-color);
        }
        
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
            
            .content-section {
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="teacherhome.php" class="sidebar-brand">
                <i class="fas fa-graduation-cap me-2"></i>
                Teacher Panel
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
                <a href="teacher_courses.php" class="sidebar-link active">
                    <i class="fas fa-book sidebar-icon"></i>
                    My Courses
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="teacher_student_management.php" class="sidebar-link">
                    <i class="fas fa-users sidebar-icon"></i>
                    Students
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="teacher_grade_management.php" class="sidebar-link">
                    <i class="fas fa-chart-line sidebar-icon"></i>
                    Grades
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="teacher_content_management.php" class="sidebar-link">
                    <i class="fas fa-edit sidebar-icon"></i>
                    Content
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="teacher_schedule_management.php" class="sidebar-link">
                    <i class="fas fa-calendar-alt sidebar-icon"></i>
                    Schedule
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="top-header">
            <div class="header-title">
                <i class="fas fa-book me-2"></i>
                My Courses
            </div>
            <div class="header-actions">
                <div class="teacher-info">
                    <div class="teacher-avatar">
                        <?php echo strtoupper(substr($teacher['name'], 0, 1)); ?>
                    </div>
                    <span><?php echo htmlspecialchars($teacher['name']); ?></span>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Logout
                </a>
            </div>
        </div>

        <!-- Content Section -->
        <div class="content-section">
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

            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-book me-3"></i>
                    My Courses
                </h1>
                <p class="page-subtitle">Courses for <?php echo htmlspecialchars($teacher['specialization']); ?> specialization</p>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon courses">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo mysqli_num_rows($teacher_courses_result); ?></div>
                    <div class="stat-label">Total Courses</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon students">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo mysqli_num_rows($students_result); ?></div>
                    <div class="stat-label">Enrolled Students</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon messages">
                            <i class="fas fa-comments"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo mysqli_num_rows($messages_result); ?></div>
                    <div class="stat-label">Recent Messages</div>
                </div>
            </div>

            <!-- Communication Section -->
            <div class="communication-section">
                <div class="section-header">
                    <h3><i class="fas fa-comments me-2"></i>Student Communication</h3>
                    <p class="mb-0">Connect with your students</p>
                </div>
                <div class="section-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>My Students</h5>
                            <div class="student-list">
                                <?php if (mysqli_num_rows($students_result) > 0): ?>
                                    <?php while ($student = mysqli_fetch_assoc($students_result)): ?>
                                        <div class="student-item">
                                            <div class="student-info">
                                                <div class="student-avatar">
                                                    <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($student['program']); ?></small>
                                                </div>
                                            </div>
                                            <button class="btn btn-primary btn-sm" onclick="openMessageModal(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['full_name']); ?>')">
                                                <i class="fas fa-comment me-1"></i>Message
                                            </button>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted">No students found in your courses.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Recent Messages</h5>
                            <div class="message-list">
                                <?php if (mysqli_num_rows($messages_result) > 0): ?>
                                    <?php while ($message = mysqli_fetch_assoc($messages_result)): ?>
                                        <div class="message-item">
                                            <div class="message-header">
                                                <span class="message-sender">
                                                    <?php echo $message['sender_type'] === 'teacher' ? 'You' : htmlspecialchars($message['student_name']); ?>
                                                </span>
                                                <span class="message-time">
                                                    <?php echo date('M d, H:i', strtotime($message['created_at'])); ?>
                                                </span>
                                            </div>
                                            <div class="message-content">
                                                <?php echo htmlspecialchars($message['message']); ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted">No recent messages.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Courses Grid -->
            <div class="row">
                <?php if (mysqli_num_rows($teacher_courses_result) > 0): ?>
                    <?php while ($course = mysqli_fetch_assoc($teacher_courses_result)): ?>
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
                                        <?php if (!empty($course['document_path'])): ?>
                                            <a href="<?php echo htmlspecialchars($course['document_path']); ?>" 
                                               target="_blank" class="btn btn-primary btn-sm">
                                                <i class="fas fa-download me-1"></i>Download
                                            </a>
                                        <?php endif; ?>
                                        <button class="btn btn-outline-secondary btn-sm" onclick="viewCourseDetails(<?php echo $course['id']; ?>)">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </button>
                                        <button class="btn btn-success btn-sm" onclick="manageCourse(<?php echo $course['id']; ?>)">
                                            <i class="fas fa-cog me-1"></i>Manage
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
    </div>

    <!-- Message Modal -->
    <div class="modal fade message-modal" id="messageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send Message to <span id="studentName"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="student_id" id="studentId">
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required 
                                      placeholder="Type your message here..."></textarea>
                        </div>
                        <button type="submit" name="send_message" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function openMessageModal(studentId, studentName) {
            document.getElementById('studentId').value = studentId;
            document.getElementById('studentName').textContent = studentName;
            new bootstrap.Modal(document.getElementById('messageModal')).show();
        }
        
        function viewCourseDetails(courseId) {
            alert('Course details functionality can be implemented here');
        }
        
        function manageCourse(courseId) {
            alert('Course management functionality can be implemented here');
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html> 