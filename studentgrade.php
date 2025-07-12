<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$student_username = $_SESSION['username'];
$student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE username = '".mysqli_real_escape_string($conn, $student_username)."' LIMIT 1"));

if (!$student) {
    header("Location: login.php");
    exit();
}

// Get grade information
$grade_info = null;
if ($student['grade_id']) {
    $grade_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM grades WHERE id = {$student['grade_id']}"));
}

// Get section information
$section = $student['section'] ?? 'Not Assigned';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Grade - Comfort e-School Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        * { font-family: 'Poppins', sans-serif; }
        body { background: var(--light-color); overflow-x: hidden; }
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
        .main-content { margin-left: 280px; min-height: 100vh; transition: all 0.3s ease; }
        .top-header { background: white; height: 70px; display: flex; align-items: center; justify-content: space-between; padding: 0 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 999; }
        .header-title { font-size: 1.5rem; font-weight: 600; color: var(--dark-color); }
        .header-actions { display: flex; align-items: center; gap: 1rem; }
        .student-info { display: flex; align-items: center; gap: 0.5rem; }
        .student-avatar { width: 40px; height: 40px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; }
        .logout-btn { background: var(--danger-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; transition: all 0.3s ease; }
        .logout-btn:hover { background: #dc2626; color: white; transform: translateY(-2px); }
        .dashboard-content { padding: 2rem; }
        .grade-card { background: white; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 2rem; }
        .grade-header { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 2rem; text-align: center; }
        .grade-icon { width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2.5rem; }
        .grade-body { padding: 2rem; }
        .grade-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
        .info-item { background: var(--light-color); padding: 1.5rem; border-radius: 15px; border-left: 4px solid var(--primary-color); }
        .info-label { font-weight: 600; color: var(--dark-color); margin-bottom: 0.5rem; }
        .info-value { font-size: 1.2rem; color: var(--primary-color); font-weight: 700; }
        .no-grade { text-align: center; padding: 3rem; color: var(--text-secondary); }
        .no-grade-icon { font-size: 4rem; color: var(--border-color); margin-bottom: 1rem; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <?php include 'studentsidebar.php'; ?>
    <div class="main-content">
        <div class="top-header">
            <div class="d-flex align-items-center">
                <button class="sidebar-toggle me-3" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="header-title mb-0">My Grade</h1>
            </div>
            <div class="header-actions">
                <div class="student-info">
                    <div class="student-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                        <small class="text-muted">Student</small>
                    </div>
                </div>
                <a href="studentnotifications.php" class="btn btn-light position-relative mx-2" style="border-radius:50%;width:44px;height:44px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
                </a>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
        
        <div class="dashboard-content">
            <?php if ($grade_info): ?>
                <div class="grade-card">
                    <div class="grade-header">
                        <div class="grade-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h2 class="mb-2">Current Grade Information</h2>
                        <p class="mb-0">Academic Year: <?php echo date('Y'); ?></p>
                    </div>
                    <div class="grade-body">
                        <div class="grade-info">
                            <div class="info-item">
                                <div class="info-label">Grade Level</div>
                                <div class="info-value"><?php echo htmlspecialchars($grade_info['name']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Section</div>
                                <div class="info-value"><?php echo htmlspecialchars($section); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Grade Description</div>
                                <div class="info-value"><?php echo htmlspecialchars($grade_info['description'] ?? 'Standard Grade Level'); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Academic Year</div>
                                <div class="info-value"><?php echo date('Y'); ?> - <?php echo date('Y') + 1; ?></div>
                            </div>
                        </div>
                        
                        <div class="mt-4 p-4 bg-light rounded-3">
                            <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Grade Information</h5>
                            <p class="mb-2"><strong>Grade Level:</strong> <?php echo htmlspecialchars($grade_info['name']); ?></p>
                            <p class="mb-2"><strong>Section:</strong> <?php echo htmlspecialchars($section); ?></p>
                            <p class="mb-2"><strong>Student ID:</strong> <?php echo htmlspecialchars($student['id']); ?></p>
                            <p class="mb-0"><strong>Full Name:</strong> <?php echo htmlspecialchars($student['full_name']); ?></p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="grade-card">
                    <div class="grade-header">
                        <div class="grade-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h2 class="mb-2">Grade Not Assigned</h2>
                        <p class="mb-0">Please contact your administrator</p>
                    </div>
                    <div class="grade-body">
                        <div class="no-grade">
                            <div class="no-grade-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <h4>No Grade Assigned</h4>
                            <p class="text-muted">Your grade information has not been assigned yet. Please contact your school administrator to get your grade level assigned.</p>
                            <a href="studentprofile.php" class="btn btn-primary">
                                <i class="fas fa-user me-2"></i>Update Profile
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('aside');
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
        
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
        
        // Active link highlighting
        const currentPage = window.location.pathname.split('/').pop();
        const sidebarLinks = document.querySelectorAll('aside a');
        sidebarLinks.forEach(link => {
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
    </script>
</body>
</html> 