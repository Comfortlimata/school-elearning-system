<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) { 
    die("Database connection failed: " . mysqli_connect_error()); 
}

$teacher_id = $_SESSION['teacher_id'];

// Handle actions with prepared statements for security
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_read'])) {
        $nid = (int)$_POST['notification_id'];
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ? AND user_type = 'teacher'");
        $stmt->bind_param("ii", $nid, $teacher_id);
        $stmt->execute();
        $stmt->close();
    }
    if (isset($_POST['mark_unread'])) {
        $nid = (int)$_POST['notification_id'];
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 0 WHERE id = ? AND user_id = ? AND user_type = 'teacher'");
        $stmt->bind_param("ii", $nid, $teacher_id);
        $stmt->execute();
        $stmt->close();
    }
    if (isset($_POST['delete_notification'])) {
        $nid = (int)$_POST['notification_id'];
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ? AND user_type = 'teacher'");
        $stmt->bind_param("ii", $nid, $teacher_id);
        $stmt->execute();
        $stmt->close();
    }
    if (isset($_POST['bulk_action'])) {
        $action = $_POST['bulk_action'];
        $selected_ids = isset($_POST['selected_notifications']) ? $_POST['selected_notifications'] : [];
        
        if (!empty($selected_ids)) {
            $placeholders = str_repeat('?,', count($selected_ids) - 1) . '?';
            if ($action === 'mark_read') {
                $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id IN ($placeholders) AND user_id = ? AND user_type = 'teacher'");
                $params = array_merge($selected_ids, [$teacher_id]);
                $stmt->bind_param(str_repeat('i', count($params)), ...$params);
                $stmt->execute();
                $stmt->close();
            } elseif ($action === 'mark_unread') {
                $stmt = $conn->prepare("UPDATE notifications SET is_read = 0 WHERE id IN ($placeholders) AND user_id = ? AND user_type = 'teacher'");
                $params = array_merge($selected_ids, [$teacher_id]);
                $stmt->bind_param(str_repeat('i', count($params)), ...$params);
                $stmt->execute();
                $stmt->close();
            } elseif ($action === 'delete') {
                $stmt = $conn->prepare("DELETE FROM notifications WHERE id IN ($placeholders) AND user_id = ? AND user_type = 'teacher'");
                $params = array_merge($selected_ids, [$teacher_id]);
                $stmt->bind_param(str_repeat('i', count($params)), ...$params);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    header('Location: teachernotifications.php'); 
    exit();
}

// Get filter parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query with filters
$where_conditions = ["user_id = ? AND user_type = 'teacher'"];
$params = [$teacher_id];
$param_types = "i";

if ($filter === 'unread') {
    $where_conditions[] = "is_read = 0";
} elseif ($filter === 'read') {
    $where_conditions[] = "is_read = 1";
}

if (!empty($search)) {
    $where_conditions[] = "(title LIKE ? OR message LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "ss";
}

$where_clause = implode(" AND ", $where_conditions);
$query = "SELECT * FROM notifications WHERE $where_clause ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$notifications = $stmt->get_result();

// Get counts for statistics
$total_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
    SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_count
    FROM notifications WHERE user_id = ? AND user_type = 'teacher'";
$count_stmt = $conn->prepare($total_query);
$count_stmt->bind_param("i", $teacher_id);
$count_stmt->execute();
$stats = $count_stmt->get_result()->fetch_assoc();
$count_stmt->close();
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
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #7c3aed;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --light-bg: #f8fafc;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light-bg);
            color: #374151;
        }

        .top-nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: #fff;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            z-index: 1000;
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 70px;
            backdrop-filter: blur(10px);
        }

        .top-nav-link {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.875rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 12px;
            transition: all 0.3s ease;
            border-radius: 8px;
        }

        .top-nav-link i {
            font-size: 1.25rem;
            margin-bottom: 4px;
        }

        .top-nav-link.active, .top-nav-link:hover {
            color: var(--primary-color);
            background: rgba(79, 70, 229, 0.1);
            transform: translateY(-2px);
        }

        .main-content {
            margin-top: 90px;
            padding: 2rem 1rem 1rem 1rem;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid #e5e7eb;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .filters-section {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            border: 1px solid #e5e7eb;
        }

        .notification-card {
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }

        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .notification-unread {
            background: linear-gradient(135deg, #e0e7ff 0%, #f3f4f6 100%);
            border-left: 4px solid var(--primary-color);
        }

        .notification-icon {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: #fff;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-right: 1rem;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);
        }

        .notification-time {
            color: #9ca3af;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .notification-title {
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .notification-message {
            color: #6b7280;
            line-height: 1.6;
        }

        .btn-action {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }

        .btn-action:hover {
            transform: translateY(-1px);
        }

        .bulk-actions {
            background: #fff;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: var(--card-shadow);
            border: 1px solid #e5e7eb;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }

        .search-box {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 0.75rem 1rem;
            transition: border-color 0.2s ease;
        }

        .search-box:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .filter-btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }

        .filter-btn.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem 0.5rem 0.5rem 0.5rem;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .top-nav-link span {
                display: none;
            }
        }

        .notification-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .read-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 0.75rem;
        }

        .read-indicator.unread {
            background: var(--primary-color);
        }

        .read-indicator.read {
            background: #9ca3af;
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <a href="teacherhome.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherhome.php') echo ' active';?>"><i class="fas fa-home"></i><span>Home</span></a>
        <a href="teacherclasses.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherclasses.php') echo ' active';?>"><i class="fas fa-users"></i><span>Classes</span></a>
        <a href="teachersubjects.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teachersubjects.php') echo ' active';?>"><i class="fas fa-book"></i><span>Subjects</span></a>
        <a href="teacherassignments.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherassignments.php') echo ' active';?>"><i class="fas fa-tasks"></i><span>Assignments</span></a>
        <a href="teacherperformance.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherperformance.php') echo ' active';?>"><i class="fas fa-chart-line"></i><span>Performance</span></a>
        <a href="teacherschedule.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherschedule.php') echo ' active';?>"><i class="fas fa-calendar-alt"></i><span>Schedule</span></a>
        <a href="teachernotifications.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teachernotifications.php') echo ' active';?>"><i class="fas fa-bell"></i><span>Notifications</span></a>
        <a href="teacherprofile.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherprofile.php') echo ' active';?>"><i class="fas fa-user"></i><span>Profile</span></a>
    </nav>

    <div class="main-content">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center">
                <div class="notification-icon"><i class="fas fa-bell"></i></div>
                <div>
                    <h2 class="mb-0">Notifications</h2>
                    <p class="text-muted mb-0">Manage your notifications and stay updated</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="teacherhome.php" class="btn btn-outline-primary btn-action">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Notifications</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-warning"><?php echo $stats['unread']; ?></div>
                <div class="stat-label">Unread</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-success"><?php echo $stats['read_count']; ?></div>
                <div class="stat-label">Read</div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control search-box" placeholder="Search notifications..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select name="filter" class="form-select">
                        <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Notifications</option>
                        <option value="unread" <?php echo $filter === 'unread' ? 'selected' : ''; ?>>Unread Only</option>
                        <option value="read" <?php echo $filter === 'read' ? 'selected' : ''; ?>>Read Only</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-action w-100">
                        <i class="fas fa-search me-2"></i>Filter
                    </button>
                </div>
                <div class="col-md-3">
                    <a href="teachernotifications.php" class="btn btn-outline-secondary btn-action w-100">
                        <i class="fas fa-refresh me-2"></i>Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Bulk Actions -->
        <?php if ($notifications && mysqli_num_rows($notifications) > 0): ?>
        <div class="bulk-actions">
            <form method="POST" id="bulkForm">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                            <label class="form-check-label" for="selectAll">
                                <strong>Select All</strong>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <select name="bulk_action" class="form-select" required>
                            <option value="">Choose Action</option>
                            <option value="mark_read">Mark as Read</option>
                            <option value="mark_unread">Mark as Unread</option>
                            <option value="delete">Delete Selected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-action w-100" id="bulkSubmit" disabled>
                            <i class="fas fa-play me-2"></i>Apply
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Notifications List -->
        <?php if ($notifications && mysqli_num_rows($notifications) > 0): ?>
            <div class="row">
                <?php while ($n = mysqli_fetch_assoc($notifications)): ?>
                <div class="col-12">
                    <div class="card notification-card <?php if (!$n['is_read']) echo 'notification-unread'; ?>">
                        <div class="card-body">
                            <div class="row align-items-start">
                                <div class="col-md-1">
                                    <div class="form-check">
                                        <input class="form-check-input notification-checkbox" type="checkbox" name="selected_notifications[]" value="<?php echo $n['id']; ?>">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="notification-content">
                                        <div class="notification-header">
                                            <div class="read-indicator <?php echo $n['is_read'] ? 'read' : 'unread'; ?>"></div>
                                            <h5 class="notification-title mb-1"><?php echo htmlspecialchars($n['title']); ?></h5>
                                        </div>
                                        <div class="notification-time mb-2">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo date('M d, Y \a\t H:i', strtotime($n['created_at'])); ?>
                                        </div>
                                        <div class="notification-message">
                                            <?php echo nl2br(htmlspecialchars($n['message'])); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="notification-actions">
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="notification_id" value="<?php echo $n['id']; ?>">
                                            <?php if (!$n['is_read']): ?>
                                                <button type="submit" name="mark_read" class="btn btn-sm btn-success btn-action" title="Mark as Read">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" name="mark_unread" class="btn btn-sm btn-warning btn-action" title="Mark as Unread">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="submit" name="delete_notification" class="btn btn-sm btn-danger btn-action" title="Delete" onclick="return confirm('Are you sure you want to delete this notification?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-bell-slash"></i>
                <h4>No notifications found</h4>
                <p class="text-muted">You're all caught up! No new notifications at the moment.</p>
                <?php if (!empty($search) || $filter !== 'all'): ?>
                    <a href="teachernotifications.php" class="btn btn-primary btn-action">
                        <i class="fas fa-refresh me-2"></i>Clear Filters
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Bulk actions functionality
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const notificationCheckboxes = document.querySelectorAll('.notification-checkbox');
            const bulkSubmit = document.getElementById('bulkSubmit');
            const bulkForm = document.getElementById('bulkForm');

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    notificationCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateBulkSubmitButton();
                });
            }

            notificationCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateBulkSubmitButton);
            });

            function updateBulkSubmitButton() {
                const checkedBoxes = document.querySelectorAll('.notification-checkbox:checked');
                const actionSelect = document.querySelector('select[name="bulk_action"]');
                
                if (checkedBoxes.length > 0 && actionSelect.value) {
                    bulkSubmit.disabled = false;
                } else {
                    bulkSubmit.disabled = true;
                }
            }

            // Update button state when action changes
            const actionSelect = document.querySelector('select[name="bulk_action"]');
            if (actionSelect) {
                actionSelect.addEventListener('change', updateBulkSubmitButton);
            }

            // Confirm bulk delete
            if (bulkForm) {
                bulkForm.addEventListener('submit', function(e) {
                    const actionSelect = document.querySelector('select[name="bulk_action"]');
                    if (actionSelect.value === 'delete') {
                        const checkedBoxes = document.querySelectorAll('.notification-checkbox:checked');
                        if (!confirm(`Are you sure you want to delete ${checkedBoxes.length} notification(s)?`)) {
                            e.preventDefault();
                        }
                    }
                });
            }
        });

        // Auto-refresh notifications every 30 seconds
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html> 