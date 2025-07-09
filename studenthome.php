<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Dashboard - Miles e-School Academy</title>
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
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center; }
        .sidebar-brand { font-size: 1.5rem; font-weight: 700; color: white; text-decoration: none; }
        .sidebar-brand:hover { color: white; }
        .sidebar-menu { padding: 1rem 0; }
        .sidebar-item { margin: 0.5rem 1rem; }
        .sidebar-link { display: flex; align-items: center; padding: 0.75rem 1rem; color: rgba(255,255,255,0.8); text-decoration: none; border-radius: 10px; transition: all 0.3s ease; }
        .sidebar-link:hover { background: rgba(255,255,255,0.1); color: white; transform: translateX(5px); }
        .sidebar-link.active { background: rgba(255,255,255,0.2); color: white; }
        .sidebar-icon { width: 20px; margin-right: 0.75rem; }
        .main-content { margin-left: 280px; min-height: 100vh; transition: all 0.3s ease; }
        .top-header { background: white; height: 70px; display: flex; align-items: center; justify-content: space-between; padding: 0 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 999; }
        .header-title { font-size: 1.5rem; font-weight: 600; color: var(--dark-color); }
        .header-actions { display: flex; align-items: center; gap: 1rem; }
        .student-info { display: flex; align-items: center; gap: 0.5rem; }
        .student-avatar { width: 40px; height: 40px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; }
        .logout-btn { background: var(--danger-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; transition: all 0.3s ease; }
        .logout-btn:hover { background: #dc2626; color: white; transform: translateY(-2px); }
        .dashboard-content { padding: 2rem; }
        .welcome-section { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 2rem; border-radius: 15px; margin-bottom: 2rem; }
        .welcome-title { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
        .welcome-subtitle { opacity: 0.9; font-size: 1.1rem; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
        .sidebar-toggle { display: none; background: none; border: none; font-size: 1.5rem; color: var(--dark-color); }
        @media (max-width: 768px) { .sidebar-toggle { display: block; } }
    </style>
</head>
<body>
    <?php include 'studentsidebar.php'; ?>
    <div class="main-content" style="margin-left:280px;">
        <div class="top-header">
            <div class="d-flex align-items-center">
                <button class="sidebar-toggle me-3" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="header-title mb-0">Dashboard</h1>
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
            <div class="welcome-section text-center" style="background: linear-gradient(135deg, #2563eb, #1e40af); color: white; padding: 3rem 2rem; border-radius: 20px; margin-bottom: 2rem; box-shadow: 0 4px 24px rgba(37,99,235,0.10);">
                <h1 class="welcome-title mb-3" style="font-size:2.5rem; font-weight:700;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                <p class="welcome-subtitle mb-4" style="font-size:1.2rem; opacity:0.95;">This is your e-learning dashboard. Use the sidebar to access your subjects, teachers, assignments, and materials.</p>
            </div>
            <!-- Dashboard is now empty except for the welcome section -->
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