<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$data = mysqli_connect($host, $user, $password, $db);

if (!$data) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Handle status updates
if (isset($_POST['update_status'])) {
    $admission_id = $_POST['admission_id'];
    $new_status = $_POST['status'];
    
    $update_sql = "UPDATE admission SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($data, $update_sql);
    mysqli_stmt_bind_param($stmt, "si", $new_status, $admission_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Status updated successfully!";
    } else {
        $_SESSION['message'] = "Error updating status!";
    }
    
    header("Location: admission.php");
    exit();
}

// Get admission data with search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($data, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($data, $_GET['status']) : '';

$sql = "SELECT * FROM admission WHERE 1=1";
if (!empty($search)) {
    $sql .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR program LIKE '%$search%')";
}
if (!empty($status_filter)) {
    $sql .= " AND status = '$status_filter'";
}
$sql .= " ORDER BY created_at DESC";

$result = mysqli_query($data, $sql);

// Get statistics
$total_applications = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM admission"))[0];
$pending_applications = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM admission WHERE status = 'Pending'"))[0];
$approved_applications = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM admission WHERE status = 'Approved'"))[0];
$rejected_applications = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM admission WHERE status = 'Rejected'"))[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admission Management - Admin Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="admin.css">

    <style>
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }
        
        .stats-icon.total { background: var(--primary-color); }
        .stats-icon.pending { background: var(--warning-color); }
        .stats-icon.approved { background: var(--success-color); }
        .stats-icon.rejected { background: var(--danger-color); }
        
        .search-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }
        
        .admission-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
        }
        
        .table-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1rem 1.5rem;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }
        
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .message-preview {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <a href="adminhome.php">
            <i class="fas fa-graduation-cap me-2"></i>
            Admin Dashboard
        </a>
        <div class="logout">
            <a href="logout.php">
                <i class="fas fa-sign-out-alt me-2"></i>
                Logout
            </a>
        </div>
    </header>

    <!-- Sidebar -->
    <aside>
        <ul>
            <li>
                <a href="adminhome.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="content_management.php">
                    <i class="fas fa-edit me-2"></i>
                    Content Management
                </a>
            </li>
            <li>
                <a href="admission.php" class="active">
                    <i class="fas fa-user-plus me-2"></i>
                    Admissions
                </a>
            </li>
            <li>
                <a href="add_student.php">
                    <i class="fas fa-user-graduate me-2"></i>
                    Add Student
                </a>
            </li>
            <li>
                <a href="view_student.php">
                    <i class="fas fa-users me-2"></i>
                    View Students
                </a>
            </li>
            <li>
                <a href="add_teacher_auth.php">
                    <i class="fas fa-chalkboard-teacher me-2"></i>
                    Add Teacher
                </a>
            </li>
            <li>
                <a href="add_courses.php">
                    <i class="fas fa-book me-2"></i>
                    Add Courses
                </a>
            </li>
            <li>
                <a href="view_courses.php">
                    <i class="fas fa-list me-2"></i>
                    View Courses
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="content fade-in">
        <!-- Page Header -->
        <div class="card-header mb-4">
            <h2><i class="fas fa-user-plus me-2"></i>Admission Management</h2>
            <p class="mb-0">Manage student admission applications and track their status</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid mb-4">
            <div class="stats-card">
                <div class="stats-icon total">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="mb-1"><?php echo $total_applications; ?></h3>
                <p class="text-muted mb-0">Total Applications</p>
            </div>
            
            <div class="stats-card">
                <div class="stats-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <h3 class="mb-1"><?php echo $pending_applications; ?></h3>
                <p class="text-muted mb-0">Pending Review</p>
            </div>
            
            <div class="stats-card">
                <div class="stats-icon approved">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="mb-1"><?php echo $approved_applications; ?></h3>
                <p class="text-muted mb-0">Approved</p>
            </div>
            
            <div class="stats-card">
                <div class="stats-icon rejected">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h3 class="mb-1"><?php echo $rejected_applications; ?></h3>
                <p class="text-muted mb-0">Rejected</p>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-section">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search Applications</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search by name, email, or program...">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Filter by Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Approved" <?php echo $status_filter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="Rejected" <?php echo $status_filter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                        <a href="admission.php" class="btn btn-secondary">
                            <i class="fas fa-refresh me-1"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Applications Table -->
        <div class="admission-table">
            <div class="table-header">
                <h4 class="mb-0"><i class="fas fa-list me-2"></i>Admission Applications</h4>
    </div>
            
    <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Program</th>
                    <th>Message</th>
                            <th>Status</th>
                            <th>Date Applied</th>
                            <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                        <?php 
                        if (mysqli_num_rows($result) > 0) {
                            while ($info = mysqli_fetch_assoc($result)) { 
                                $status_class = '';
                                switch($info['status'] ?? 'Pending') {
                                    case 'Approved': $status_class = 'status-approved'; break;
                                    case 'Rejected': $status_class = 'status-rejected'; break;
                                    default: $status_class = 'status-pending'; break;
                                }
                                $status = $info['status'] ?? 'Pending';
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <i class="fas fa-user-circle fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($info['name']); ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($info['email']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($info['email']); ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="tel:<?php echo htmlspecialchars($info['phone']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($info['phone']); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($info['program']); ?></span>
                                </td>
                                <td>
                                    <div class="message-preview" title="<?php echo htmlspecialchars($info['message']); ?>">
                                        <?php echo htmlspecialchars(substr($info['message'], 0, 50)) . (strlen($info['message']) > 50 ? '...' : ''); ?>
                                    </div>
                                    <button class="btn btn-sm btn-link p-0" onclick="viewMessage('<?php echo htmlspecialchars($info['message']); ?>', '<?php echo htmlspecialchars($info['name']); ?>')">
                                        View Full
                                    </button>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $status; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($info['created_at'] ?? 'now')); ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-outline-primary" onclick="updateStatus(<?php echo $info['id']; ?>, 'Approved')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="updateStatus(<?php echo $info['id']; ?>, 'Rejected')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info" onclick="viewDetails(<?php echo $info['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                    </tr>
                        <?php 
                            }
                        } else {
                            echo '<tr><td colspan="8" class="text-center py-4 text-muted">No applications found</td></tr>';
                        }
                        ?>
            </tbody>
        </table>
    </div>
</div>
    </div>

    <!-- Message Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Application Message</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="messageContent"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Form -->
    <form id="statusForm" method="POST" style="display: none;">
        <input type="hidden" name="admission_id" id="admissionId">
        <input type="hidden" name="status" id="statusValue">
        <input type="hidden" name="update_status" value="1">
    </form>

<!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // View full message
        function viewMessage(message, name) {
            document.getElementById('messageContent').innerHTML = message.replace(/\n/g, '<br>');
            document.getElementById('messageModal').querySelector('.modal-title').textContent = `Message from ${name}`;
            new bootstrap.Modal(document.getElementById('messageModal')).show();
        }

        // Update status
        function updateStatus(admissionId, status) {
            if (confirm(`Are you sure you want to ${status.toLowerCase()} this application?`)) {
                document.getElementById('admissionId').value = admissionId;
                document.getElementById('statusValue').value = status;
                document.getElementById('statusForm').submit();
            }
        }

        // View details (placeholder for future enhancement)
        function viewDetails(admissionId) {
            alert('Detailed view functionality can be added here');
        }

        // Show success message if exists
        <?php if (isset($_SESSION['message'])): ?>
            alert('<?php echo $_SESSION['message']; ?>');
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
    </script>
</body>
</html>