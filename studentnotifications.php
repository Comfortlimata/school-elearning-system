<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
    header("Location: login.php");
    exit();
}
// Sample notifications/messages
$notifications = [
    [
        'type' => 'info',
        'title' => 'New Assignment',
        'message' => 'Mathematics Assignment 2 has been posted.',
        'date' => '2024-07-01 10:00',
    ],
    [
        'type' => 'success',
        'title' => 'Grade Posted',
        'message' => 'Your grade for Physics Lab Report is now available.',
        'date' => '2024-06-30 15:30',
    ],
    [
        'type' => 'message',
        'title' => 'Message from Mr. Smith',
        'message' => 'Please see me after class regarding your project.',
        'date' => '2024-06-29 09:15',
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notifications & Messages - Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #2563eb; --secondary-color: #1e40af; --light-color: #f8fafc; --border-color: #e5e7eb; }
        * { font-family: 'Poppins', sans-serif; }
        body { background: var(--light-color); }
        .main-content { margin-left: 280px; padding: 2rem; }
    </style>
</head>
<body>
    <?php include 'studentsidebar.php'; ?>
    <div class="main-content">
        <h2 class="mb-4"><i class="fas fa-bell me-2"></i>Notifications & Messages</h2>
        <div class="row">
            <?php foreach ($notifications as $n): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card shadow-sm border-<?php echo $n['type'] === 'success' ? 'success' : ($n['type'] === 'info' ? 'info' : 'primary'); ?>">
                    <div class="card-header bg-<?php echo $n['type'] === 'success' ? 'success' : ($n['type'] === 'info' ? 'info' : 'primary'); ?> text-white">
                        <strong><?php echo htmlspecialchars($n['title']); ?></strong>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><?php echo htmlspecialchars($n['message']); ?></p>
                        <div class="text-muted small"><i class="fas fa-clock me-1"></i><?php echo htmlspecialchars($n['date']); ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html> 