<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'teacher') {
    header("Location: login.php");
    exit();
}
$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) { die("Database connection failed: " . mysqli_connect_error()); }
$teacher_id = $_SESSION['teacher_id'];
// Handle mark as read/unread and delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_read'])) {
        $nid = (int)$_POST['notification_id'];
        mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE id = $nid AND user_id = $teacher_id AND user_type = 'teacher'");
    }
    if (isset($_POST['mark_unread'])) {
        $nid = (int)$_POST['notification_id'];
        mysqli_query($conn, "UPDATE notifications SET is_read = 0 WHERE id = $nid AND user_id = $teacher_id AND user_type = 'teacher'");
    }
    if (isset($_POST['delete_notification'])) {
        $nid = (int)$_POST['notification_id'];
        mysqli_query($conn, "DELETE FROM notifications WHERE id = $nid AND user_id = $teacher_id AND user_type = 'teacher'");
    }
    header('Location: teachernotifications.php'); exit();
}
// Fetch notifications for this teacher
$notifications = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = $teacher_id AND user_type = 'teacher' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notifications - Comfort e-School Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
        }
        .top-nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            z-index: 1000;
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 60px;
        }
        .top-nav-link {
            color: #444;
            text-decoration: none;
            font-size: 1.1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 10px;
            transition: color 0.2s;
        }
        .top-nav-link i {
            font-size: 1.3rem;
        }
        .top-nav-link.active, .top-nav-link:hover {
            color: #007bff;
        }
        .main-content {
            margin-top: 80px;
            padding: 2rem 1rem 1rem 1rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        .notification-card {
            border-radius: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border: none;
        }
        .notification-unread {
            background: #e3f0ff;
        }
        .notification-icon {
            background: #f0e6ff;
            color: #7c3aed;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        @media (max-width: 600px) {
            .main-content {
                padding: 1rem 0.2rem 0.5rem 0.2rem;
            }
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <a href="teacherhome.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherhome.php') echo ' active';?>"><i class="fas fa-home"></i><span>Home</span></a>
        <a href="teacherclasses.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherclasses.php') echo ' active';?>"><i class="fas fa-users-class"></i><span>Classes</span></a>
        <a href="teachersubjects.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teachersubjects.php') echo ' active';?>"><i class="fas fa-book"></i><span>Subjects</span></a>
        <a href="teacherassignments.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherassignments.php') echo ' active';?>"><i class="fas fa-tasks"></i><span>Assignments</span></a>
        <a href="teacherperformance.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherperformance.php') echo ' active';?>"><i class="fas fa-chart-line"></i><span>Performance</span></a>
        <a href="teacherschedule.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherschedule.php') echo ' active';?>"><i class="fas fa-calendar-alt"></i><span>Schedule</span></a>
        <a href="teachernotifications.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teachernotifications.php') echo ' active';?>"><i class="fas fa-bell"></i><span>Notifications</span></a>
        <a href="teacherprofile.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherprofile.php') echo ' active';?>"><i class="fas fa-user"></i><span>Profile</span></a>
    </nav>
    <div class="main-content">
        <div class="d-flex align-items-center mb-4">
            <div class="notification-icon"><i class="fas fa-bell"></i></div>
            <h2 class="mb-0">Notifications</h2>
        </div>
        <?php if ($notifications && mysqli_num_rows($notifications) > 0): ?>
            <div class="row">
                <?php while ($n = mysqli_fetch_assoc($notifications)): ?>
                <div class="col-12 mb-3">
                    <div class="card notification-card <?php if (!$n['is_read']) echo 'notification-unread'; ?>">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="notification-icon"><i class="fas fa-bell"></i></div>
                                <div>
                                    <h5 class="card-title mb-1">
                                        <i class="fas fa-circle me-2 <?php echo $n['is_read'] ? 'text-secondary' : 'text-primary'; ?>"></i>
                                        <?php echo htmlspecialchars($n['title']); ?>
                                    </h5>
                                    <div class="mb-1 text-muted small"><?php echo date('M d, Y H:i', strtotime($n['created_at'])); ?></div>
                                    <div><?php echo nl2br(htmlspecialchars($n['message'])); ?></div>
                                </div>
                            </div>
                            <div>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="notification_id" value="<?php echo $n['id']; ?>">
                                    <?php if (!$n['is_read']): ?>
                                        <button type="submit" name="mark_read" class="btn btn-sm btn-outline-success me-1"><i class="fas fa-check"></i> Mark Read</button>
                                    <?php else: ?>
                                        <button type="submit" name="mark_unread" class="btn btn-sm btn-outline-warning me-1"><i class="fas fa-undo"></i> Mark Unread</button>
                                    <?php endif; ?>
                                    <button type="submit" name="delete_notification" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i> Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No notifications found.</div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 