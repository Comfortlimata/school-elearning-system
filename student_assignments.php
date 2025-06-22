<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check user session and usertype = 'student'
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
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

$username = $_SESSION['username'];
$program = $_SESSION['program'];

// Get student information
$student_sql = "SELECT * FROM students WHERE username = ?";
$stmt = mysqli_prepare($data, $student_sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$student_result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($student_result);

// Sample assignments data (in a real system, this would come from an assignments table)
$sample_assignments = [
    [
        'title' => 'Mathematics Assignment 1',
        'course' => 'Mathematics',
        'due_date' => '2024-01-15',
        'status' => 'Submitted',
        'grade' => 'A',
        'description' => 'Complete exercises 1-10 from Chapter 3'
    ],
    [
        'title' => 'Physics Lab Report',
        'course' => 'Physics',
        'due_date' => '2024-01-20',
        'status' => 'Pending',
        'grade' => null,
        'description' => 'Write a lab report on the pendulum experiment'
    ],
    [
        'title' => 'Computer Science Project',
        'course' => 'Computer Science',
        'due_date' => '2024-01-25',
        'status' => 'In Progress',
        'grade' => null,
        'description' => 'Develop a simple web application using HTML, CSS, and JavaScript'
    ],
    [
        'title' => 'English Essay',
        'course' => 'English',
        'due_date' => '2024-01-18',
        'status' => 'Submitted',
        'grade' => 'B+',
        'description' => 'Write a 1000-word essay on Shakespeare\'s influence'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Assignments - Student Dashboard</title>

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
        
        .student-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
        
        /* Assignments Content */
        .assignments-content {
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
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin: 0 auto 1rem;
        }
        
        .stat-icon.total { background: var(--info-color); }
        .stat-icon.submitted { background: var(--success-color); }
        .stat-icon.pending { background: var(--warning-color); }
        .stat-icon.overdue { background: var(--danger-color); }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            color: #6b7280;
            font-weight: 500;
        }
        
        /* Assignment Cards */
        .assignments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        
        .assignment-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .assignment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .assignment-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .assignment-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .assignment-course {
            color: var(--primary-color);
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .assignment-body {
            padding: 1.5rem;
        }
        
        .assignment-description {
            color: #6b7280;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        
        .assignment-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .due-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-submitted { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-progress { background: #dbeafe; color: #1e40af; }
        .status-overdue { background: #fee2e2; color: #991b1b; }
        
        .grade-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
            background: var(--success-color);
            color: white;
        }
        
        .assignment-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            border-radius: 8px;
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
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            
            .assignments-grid {
                grid-template-columns: 1fr;
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
                <i class="fas fa-user-graduate me-2"></i>
                Student Portal
            </a>
        </div>
        
        <div class="sidebar-menu">
            <div class="sidebar-item">
                <a href="studenthome.php" class="sidebar-link">
                    <i class="fas fa-tachometer-alt sidebar-icon"></i>
                    Dashboard
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="student_courses.php" class="sidebar-link">
                    <i class="fas fa-book sidebar-icon"></i>
                    My Courses
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="student_results.php" class="sidebar-link">
                    <i class="fas fa-chart-bar sidebar-icon"></i>
                    My Results
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="student_assignments.php" class="sidebar-link active">
                    <i class="fas fa-tasks sidebar-icon"></i>
                    Assignments
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="student_schedule.php" class="sidebar-link">
                    <i class="fas fa-calendar sidebar-icon"></i>
                    Schedule
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="student_profile.php" class="sidebar-link">
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
                <h1 class="header-title mb-0">My Assignments</h1>
            </div>
            
            <div class="header-actions">
                <div class="student-info">
                    <div class="student-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div class="fw-bold"><?php echo htmlspecialchars($username); ?></div>
                        <small class="text-muted"><?php echo htmlspecialchars($program); ?> Student</small>
                    </div>
                </div>
                
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>

        <!-- Assignments Content -->
        <div class="assignments-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">My Assignments</h1>
                <p class="page-subtitle">Track your assignments, due dates, and grades</p>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-number">4</div>
                    <div class="stat-label">Total</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon submitted">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number">2</div>
                    <div class="stat-label">Submitted</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number">1</div>
                    <div class="stat-label">Pending</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon overdue">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-number">0</div>
                    <div class="stat-label">Overdue</div>
                </div>
            </div>

            <!-- Assignments Grid -->
            <div class="assignments-grid">
                <?php foreach ($sample_assignments as $assignment): ?>
                <div class="assignment-card">
                    <div class="assignment-header">
                        <h3 class="assignment-title"><?php echo htmlspecialchars($assignment['title']); ?></h3>
                        <div class="assignment-course">
                            <i class="fas fa-book me-1"></i>
                            <?php echo htmlspecialchars($assignment['course']); ?>
                        </div>
                    </div>
                    
                    <div class="assignment-body">
                        <p class="assignment-description">
                            <?php echo htmlspecialchars($assignment['description']); ?>
                        </p>
                        
                        <div class="assignment-meta">
                            <div class="due-date">
                                <i class="fas fa-calendar-alt"></i>
                                Due: <?php echo date('M j, Y', strtotime($assignment['due_date'])); ?>
                            </div>
                            <span class="status-badge status-<?php echo strtolower($assignment['status']); ?>">
                                <?php echo htmlspecialchars($assignment['status']); ?>
                            </span>
                        </div>
                        
                        <?php if ($assignment['grade']): ?>
                        <div class="mb-3">
                            <span class="grade-badge">
                                Grade: <?php echo htmlspecialchars($assignment['grade']); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="assignment-actions">
                            <?php if ($assignment['status'] === 'Submitted'): ?>
                                <button class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View Submission
                                </button>
                            <?php elseif ($assignment['status'] === 'Pending'): ?>
                                <button class="btn btn-primary btn-sm">
                                    <i class="fas fa-upload me-1"></i>Submit Assignment
                                </button>
                            <?php else: ?>
                                <button class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit me-1"></i>Continue Working
                                </button>
                            <?php endif; ?>
                            
                            <button class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-download me-1"></i>Download
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
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
    </script>
</body>
</html> 