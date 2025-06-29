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

// Get teacher's assigned subjects from teacher_grade_subjects table
$teacher_subjects_sql = "SELECT DISTINCT tgs.*, g.name as grade_name, s.name as subject_name, s.id as subject_id 
                        FROM teacher_grade_subjects tgs 
                        JOIN grades g ON tgs.grade_id = g.id 
                        JOIN subjects s ON tgs.subject_id = s.id 
                        WHERE tgs.teacher_id = ? 
                        ORDER BY g.name, s.name";
$stmt = mysqli_prepare($data, $teacher_subjects_sql);
mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);
$teacher_subjects_result = mysqli_stmt_get_result($stmt);

// Get grade-section-subject assignments for the teacher's subjects
$teacher_assignments_sql = "SELECT gsa.*, g.name as grade_name, s.name as subject_name 
                           FROM grade_subject_assignments gsa 
                           JOIN grades g ON gsa.grade_id = g.id 
                           JOIN subjects s ON gsa.subject_id = s.id 
                           JOIN teacher_grade_subjects tgs ON gsa.grade_id = tgs.grade_id AND gsa.subject_id = tgs.subject_id 
                           WHERE tgs.teacher_id = ? 
                           ORDER BY g.name, gsa.section, s.name";
$stmt = mysqli_prepare($data, $teacher_assignments_sql);
mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);
$teacher_assignments_result = mysqli_stmt_get_result($stmt);

// Get students enrolled in teacher's assigned grades
$students_sql = "SELECT DISTINCT s.*, g.name as grade_name 
                 FROM students s 
                 JOIN grades g ON s.grade_id = g.id 
                 JOIN teacher_grade_subjects tgs ON s.grade_id = tgs.grade_id 
                 WHERE tgs.teacher_id = ? 
                 ORDER BY g.name, s.section, s.full_name";
$stmt = mysqli_prepare($data, $students_sql);
mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);
$students_result = mysqli_stmt_get_result($stmt);

// Get teacher's assigned courses from courses table (for backward compatibility)
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
    <title>My Subjects - Teacher Dashboard</title>

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
        
        .stat-icon.subjects { background: var(--primary-color); }
        .stat-icon.students { background: var(--success-color); }
        .stat-icon.messages { background: var(--warning-color); }
        .stat-icon.assignments { background: var(--info-color); }
        
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
        
        .subject-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .subject-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .subject-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem;
        }
        
        .subject-body {
            padding: 1.5rem;
        }
        
        .subject-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .subject-grade {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 1rem;
        }
        
        .subject-description {
            color: #6b7280;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        
        .subject-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            border-radius: 8px;
        }
        
        .assignment-card {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .assignment-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .assignment-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .assignment-title {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .assignment-badges {
            display: flex;
            gap: 0.5rem;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
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
                    My Subjects
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
                My Subjects
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
                    My Subjects
                </h1>
                <p class="page-subtitle">Subjects assigned to <?php echo htmlspecialchars($teacher['specialization'] ?? $teacher['name']); ?></p>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon subjects">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo mysqli_num_rows($teacher_subjects_result); ?></div>
                    <div class="stat-label">Total Subjects</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon assignments">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo mysqli_num_rows($teacher_assignments_result); ?></div>
                    <div class="stat-label">Grade-Section Assignments</div>
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
                                                    <small class="text-muted">Grade <?php echo htmlspecialchars($student['grade_name']); ?> Section <?php echo htmlspecialchars($student['section']); ?></small>
                                                </div>
                                            </div>
                                            <button class="btn btn-primary btn-sm" onclick="openMessageModal(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['full_name']); ?>')">
                                                <i class="fas fa-comment me-1"></i>Message
                                            </button>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted">No students found in your assigned grades.</p>
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

            <!-- Subjects Grid -->
            <div class="row">
                <?php if (mysqli_num_rows($teacher_subjects_result) > 0): ?>
                    <?php while ($subject = mysqli_fetch_assoc($teacher_subjects_result)): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="subject-card">
                                <div class="subject-header">
                                    <h5 class="subject-title"><?php echo htmlspecialchars($subject['subject_name']); ?></h5>
                                    <div class="subject-grade">Grade <?php echo htmlspecialchars($subject['grade_name']); ?></div>
                                </div>
                                <div class="subject-body">
                                    <div class="subject-actions">
                                        <button class="btn btn-outline-primary btn-sm" onclick="viewSubjectDetails(<?php echo $subject['subject_id']; ?>, '<?php echo htmlspecialchars($subject['subject_name']); ?>')">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </button>
                                        <button class="btn btn-success btn-sm" onclick="manageSubject(<?php echo $subject['subject_id']; ?>, '<?php echo htmlspecialchars($subject['subject_name']); ?>')">
                                            <i class="fas fa-cog me-1"></i>Manage
                                        </button>
                                        <button class="btn btn-info btn-sm" onclick="viewAssignments(<?php echo $subject['subject_id']; ?>, '<?php echo htmlspecialchars($subject['subject_name']); ?>')">
                                            <i class="fas fa-tasks me-1"></i>Assignments
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
                            <h3 class="text-muted">No Subjects Found</h3>
                            <p class="text-muted">No subjects are currently assigned to you. Please contact the administrator.</p>
                            <a href="teacherhome.php" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Grade-Section Assignments -->
            <?php if (mysqli_num_rows($teacher_assignments_result) > 0): ?>
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Grade-Section Assignments</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php while ($assignment = mysqli_fetch_assoc($teacher_assignments_result)): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="assignment-card">
                                <div class="assignment-header">
                                    <div class="assignment-title">
                                        <?php echo htmlspecialchars($assignment['subject_name']); ?>
                                    </div>
                                    <div class="assignment-badges">
                                        <span class="badge bg-<?php echo $assignment['is_required'] ? 'success' : 'info'; ?>">
                                            <?php echo $assignment['is_required'] ? 'Required' : 'Elective'; ?>
                                        </span>
                                        <span class="badge bg-secondary">
                                            <?php echo $assignment['credits']; ?> Credit<?php echo $assignment['credits'] > 1 ? 's' : ''; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-muted small">
                                    <strong>Grade:</strong> <?php echo htmlspecialchars($assignment['grade_name']); ?> Section <?php echo htmlspecialchars($assignment['section']); ?>
                                </div>
                                <?php if (!empty($assignment['description'])): ?>
                                <div class="text-muted small mt-2">
                                    <?php echo htmlspecialchars($assignment['description']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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
        
        function viewSubjectDetails(subjectId, subjectName) {
            alert('Subject details for ' + subjectName + ' can be implemented here');
        }
        
        function manageSubject(subjectId, subjectName) {
            alert('Subject management for ' + subjectName + ' can be implemented here');
        }
        
        function viewAssignments(subjectId, subjectName) {
            alert('View assignments for ' + subjectName + ' can be implemented here');
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