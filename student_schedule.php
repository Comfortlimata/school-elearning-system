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

// Sample schedule data (in a real system, this would come from a schedule table)
$sample_schedule = [
    [
        'day' => 'Monday',
        'courses' => [
            ['time' => '09:00 - 10:30', 'course' => 'Mathematics', 'teacher' => 'Dr. Sarah Johnson', 'room' => 'Room 201'],
            ['time' => '11:00 - 12:30', 'course' => 'Physics', 'teacher' => 'Prof. Michael Chen', 'room' => 'Room 305'],
            ['time' => '14:00 - 15:30', 'course' => 'Computer Science', 'teacher' => 'Dr. James Wilson', 'room' => 'Room 401']
        ]
    ],
    [
        'day' => 'Tuesday',
        'courses' => [
            ['time' => '09:00 - 10:30', 'course' => 'English', 'teacher' => 'Ms. Emily Rodriguez', 'room' => 'Room 102'],
            ['time' => '11:00 - 12:30', 'course' => 'Chemistry', 'teacher' => 'Prof. Lisa Thompson', 'room' => 'Room 203'],
            ['time' => '14:00 - 15:30', 'course' => 'Mathematics', 'teacher' => 'Dr. Sarah Johnson', 'room' => 'Room 201']
        ]
    ],
    [
        'day' => 'Wednesday',
        'courses' => [
            ['time' => '09:00 - 10:30', 'course' => 'Physics', 'teacher' => 'Prof. Michael Chen', 'room' => 'Room 305'],
            ['time' => '11:00 - 12:30', 'course' => 'Computer Science', 'teacher' => 'Dr. James Wilson', 'room' => 'Room 401'],
            ['time' => '14:00 - 15:30', 'course' => 'English', 'teacher' => 'Ms. Emily Rodriguez', 'room' => 'Room 102']
        ]
    ],
    [
        'day' => 'Thursday',
        'courses' => [
            ['time' => '09:00 - 10:30', 'course' => 'Chemistry', 'teacher' => 'Prof. Lisa Thompson', 'room' => 'Room 203'],
            ['time' => '11:00 - 12:30', 'course' => 'Mathematics', 'teacher' => 'Dr. Sarah Johnson', 'room' => 'Room 201'],
            ['time' => '14:00 - 15:30', 'course' => 'Physics', 'teacher' => 'Prof. Michael Chen', 'room' => 'Room 305']
        ]
    ],
    [
        'day' => 'Friday',
        'courses' => [
            ['time' => '09:00 - 10:30', 'course' => 'Computer Science', 'teacher' => 'Dr. James Wilson', 'room' => 'Room 401'],
            ['time' => '11:00 - 12:30', 'course' => 'English', 'teacher' => 'Ms. Emily Rodriguez', 'room' => 'Room 102'],
            ['time' => '14:00 - 15:30', 'course' => 'Chemistry', 'teacher' => 'Prof. Lisa Thompson', 'room' => 'Room 203']
        ]
    ]
];

// Get current week info
$current_week = date('W');
$current_year = date('Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Schedule - Student Dashboard</title>

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
        
        /* Schedule Content */
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
        
        /* Week Navigation */
        .week-nav {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }
        
        .week-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .current-week {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .week-actions {
            display: flex;
            gap: 1rem;
        }
        
        /* Schedule Grid */
        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .day-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .day-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .day-header {
            background: var(--primary-color);
            color: white;
            padding: 1rem 1.5rem;
            text-align: center;
        }
        
        .day-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }
        
        .day-body {
            padding: 1.5rem;
        }
        
        .class-item {
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
        }
        
        .class-item:last-child {
            border-bottom: none;
        }
        
        .class-time {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .class-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
        }
        
        .class-teacher {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        
        .class-room {
            color: var(--info-color);
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        /* Today Highlight */
        .day-card.today {
            border: 2px solid var(--success-color);
        }
        
        .day-card.today .day-header {
            background: var(--success-color);
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
            
            .schedule-grid {
                grid-template-columns: 1fr;
            }
            
            .week-info {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
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
                <a href="student_assignments.php" class="sidebar-link">
                    <i class="fas fa-tasks sidebar-icon"></i>
                    Assignments
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="student_schedule.php" class="sidebar-link active">
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
                <h1 class="header-title mb-0">My Schedule</h1>
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

        <!-- Schedule Content -->
        <div class="schedule-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Class Schedule</h1>
                <p class="page-subtitle">Your weekly class timetable and schedule</p>
            </div>

            <!-- Week Navigation -->
            <div class="week-nav">
                <div class="week-info">
                    <div class="current-week">
                        <i class="fas fa-calendar-week me-2"></i>
                        Week <?php echo $current_week; ?>, <?php echo $current_year; ?>
                    </div>
                    <div class="week-actions">
                        <button class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-chevron-left me-1"></i>Previous Week
                        </button>
                        <button class="btn btn-outline-primary btn-sm">
                            Next Week<i class="fas fa-chevron-right ms-1"></i>
                        </button>
                        <button class="btn btn-primary btn-sm">
                            <i class="fas fa-download me-1"></i>Download PDF
                        </button>
                    </div>
                </div>
            </div>

            <!-- Schedule Grid -->
            <div class="schedule-grid">
                <?php 
                $today = date('l'); // Get current day name
                foreach ($sample_schedule as $day): 
                    $isToday = ($day['day'] === $today);
                ?>
                <div class="day-card <?php echo $isToday ? 'today' : ''; ?>">
                    <div class="day-header">
                        <h3 class="day-title">
                            <?php if ($isToday): ?>
                                <i class="fas fa-star me-2"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($day['day']); ?>
                        </h3>
                    </div>
                    
                    <div class="day-body">
                        <?php if (empty($day['courses'])): ?>
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                <p>No classes scheduled</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($day['courses'] as $course): ?>
                            <div class="class-item">
                                <div class="class-time">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo htmlspecialchars($course['time']); ?>
                                </div>
                                <div class="class-name">
                                    <?php echo htmlspecialchars($course['course']); ?>
                                </div>
                                <div class="class-teacher">
                                    <i class="fas fa-chalkboard-teacher me-1"></i>
                                    <?php echo htmlspecialchars($course['teacher']); ?>
                                </div>
                                <div class="class-room">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo htmlspecialchars($course['room']); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
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