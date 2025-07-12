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
    <title>Notifications - Teacher Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #2563eb; --secondary-color: #1e40af; --light-color: #f8fafc; --border-color: #e5e7eb; }
        * { font-family: 'Poppins', sans-serif; }
        body { background: var(--light-color); }
        .main-content { margin-left: 280px; padding: 2rem; }
        .notification-card { border-left: 6px solid var(--primary-color); }
        .notification-unread { background: #e0e7ff; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-brand">Teacher Portal</a>
        </div>
        <div class="sidebar-menu">
            <a href="teacherhome.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
            <a href="teacherclasses.php" class="sidebar-link"><i class="fas fa-users-class"></i>My Classes</a>
            <a href="teachersubjects.php" class="sidebar-link"><i class="fas fa-book"></i>My Subjects</a>
            <a href="teacherassignments.php" class="sidebar-link"><i class="fas fa-tasks"></i>Assignments & Materials</a>
            <a href="teacherperformance.php" class="sidebar-link"><i class="fas fa-chart-bar"></i>Student Performance</a>
            <a href="teacherschedule.php" class="sidebar-link"><i class="fas fa-calendar-alt"></i>Schedule</a>
            <a href="teacherprofile.php" class="sidebar-link"><i class="fas fa-user"></i>Profile</a>
            <a href="teachernotifications.php" class="sidebar-link active"><i class="fas fa-bell"></i>Notifications</a>
        </div>
    </aside>
    <div class="main-content">
        <h2 class="mb-4"><i class="fas fa-bell me-2"></i>Notifications</h2>
        <?php if ($notifications && mysqli_num_rows($notifications) > 0): ?>
            <div class="row">
                <?php while ($n = mysqli_fetch_assoc($notifications)): ?>
                <div class="col-12 mb-3">
                    <div class="card notification-card <?php if (!$n['is_read']) echo 'notification-unread'; ?>">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-1">
                                    <i class="fas fa-circle me-2 <?php echo $n['is_read'] ? 'text-secondary' : 'text-primary'; ?>"></i>
                                    <?php echo htmlspecialchars($n['title']); ?>
                                </h5>
                                <div class="mb-1 text-muted small"><?php echo date('M d, Y H:i', strtotime($n['created_at'])); ?></div>
                                <div><?php echo nl2br(htmlspecialchars($n['message'])); ?></div>
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
</body>
</html> 