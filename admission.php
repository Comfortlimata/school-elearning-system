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

// Handle admit student
if (isset($_POST['admit_student_modal'])) {
    $admission_id = (int)$_POST['admission_id'];
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = trim($_POST['email']);
    $grade = trim($_POST['grade']);
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $gender = trim($_POST['gender']);
    $section = trim($_POST['section']);
    if (!$username || !$password) {
        $_SESSION['message'] = "Username and password are required!";
        header("Location: admission.php");
        exit();
    }
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    // Check for existing student
    $check_sql = "SELECT id FROM students WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($data, $check_sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $email);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['message'] = "Student with this email or username already exists!";
    } else {
        $insert_sql = "INSERT INTO students (username, email, password, grade, full_name, phone, address, date_of_birth, gender, section) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($data, $insert_sql);
        mysqli_stmt_bind_param($stmt, "ssssssssss", $username, $email, $hashed_password, $grade, $full_name, $phone, $address, $date_of_birth, $gender, $section);
        if (mysqli_stmt_execute($stmt)) {
            // Delete admission after admitting
            $del_sql = "DELETE FROM admission WHERE id = ?";
            $stmt = mysqli_prepare($data, $del_sql);
            mysqli_stmt_bind_param($stmt, "i", $admission_id);
            mysqli_stmt_execute($stmt);
            $_SESSION['message'] = "Student admitted and added successfully!";
        } else {
            $_SESSION['message'] = "Error admitting student: " . mysqli_error($data);
        }
    }
    header("Location: admission.php");
    exit();
}

// Handle delete admission
if (isset($_POST['delete_admission'])) {
    $admission_id = (int)$_POST['admission_id'];
    $del_sql = "DELETE FROM admission WHERE id = ?";
    $stmt = mysqli_prepare($data, $del_sql);
    mysqli_stmt_bind_param($stmt, "i", $admission_id);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Admission deleted successfully!";
    } else {
        $_SESSION['message'] = "Error deleting admission!";
    }
    header("Location: admission.php");
    exit();
}

// Get admission data with search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($data, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($data, $_GET['status']) : '';

$sql = "SELECT * FROM admission WHERE 1=1";
if (!empty($search)) {
    $sql .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR grade LIKE '%$search%' OR section LIKE '%$search%')";
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

$course_count = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM courses"))[0];
$student_count = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM students"))[0];
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
                    <th>Grade</th>
                    <th>Section</th>
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
                                $json_info = htmlspecialchars(json_encode($info), ENT_QUOTES, 'UTF-8');
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
                                    <span class="badge bg-info"><?php echo htmlspecialchars($info['grade']); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($info['section']); ?></span>
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
                                        <button type="button" class="btn btn-sm btn-success" title="Admit" onclick='showAdmitModal(<?php echo $json_info; ?>)'><i class="fas fa-user-check"></i></button>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this application?');">
                                            <input type="hidden" name="admission_id" value="<?php echo $info['id']; ?>">
                                            <input type="hidden" name="delete_admission" value="1">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                        <button class="btn btn-sm btn-outline-info" onclick="viewDetailsModal(<?php echo $json_info; ?>)"><i class="fas fa-eye"></i></button>
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

    <!-- Add a new modal for View Details -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Admission Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group" id="detailsList"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Admit Student Modal -->
    <div class="modal fade" id="admitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="admitForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Admit Student</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="admission_id" id="admit_admission_id">
                        <div class="mb-2">
                            <label class="form-label">Username *</label>
                            <input type="text" class="form-control" name="username" id="admit_username" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Password *</label>
                            <input type="password" class="form-control" name="password" id="admit_password" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="admit_email">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" id="admit_full_name">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" id="admit_phone">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Grade</label>
                            <input type="text" class="form-control" name="grade" id="admit_grade">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" id="admit_address">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth" id="admit_date_of_birth">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="gender" id="admit_gender">
                                <option value="">-- Select Gender --</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Section *</label>
                            <select class="form-select" name="section" id="admit_section" required>
                                <option value="">-- Select Section --</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Admit Student</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                    <input type="hidden" name="admit_student_modal" value="1">
                </form>
            </div>
        </div>
    </div>

<!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // View full message
        function viewMessage(message, name) {
            document.getElementById('messageContent').innerHTML = message.replace(/\n/g, '<br>');
            document.getElementById('messageModal').querySelector('.modal-title').textContent = `Message from ${name}`;
            new bootstrap.Modal(document.getElementById('messageModal')).show();
        }

        // View Details Modal
        function viewDetailsModal(info) {
            let html = '';
            for (const key in info) {
                if (info.hasOwnProperty(key)) {
                    html += `<li class='list-group-item'><strong>${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}:</strong> ${info[key]}</li>`;
                }
            }
            document.getElementById('detailsList').innerHTML = html;
            new bootstrap.Modal(document.getElementById('detailsModal')).show();
        }

        // Show Admit Modal and prefill fields
        function showAdmitModal(info) {
            document.getElementById('admit_admission_id').value = info.id;
            document.getElementById('admit_username').value = info.email ? info.email.split('@')[0] : info.name.replace(/\s+/g, '').toLowerCase();
            document.getElementById('admit_email').value = info.email || '';
            document.getElementById('admit_full_name').value = info.name || '';
            document.getElementById('admit_phone').value = info.phone || '';
            document.getElementById('admit_grade').value = info.grade || '';
            document.getElementById('admit_address').value = '';
            document.getElementById('admit_date_of_birth').value = '';
            document.getElementById('admit_gender').value = '';
            document.getElementById('admit_password').value = '';
            document.getElementById('admit_section').value = '';
            new bootstrap.Modal(document.getElementById('admitModal')).show();
        }

        // Show success message if exists
        <?php if (isset($_SESSION['message'])): ?>
            alert('<?php echo $_SESSION['message']; ?>');
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
    </script>

<!-- Place these at the very end, just before </body> -->

<div class="offcanvas offcanvas-end" tabindex="-1" id="viewStudentModal" aria-labelledby="viewStudentModalLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="viewStudentModalLabel">Student Details</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <ul class="list-group" id="viewStudentList"></ul>
  </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="editStudentModal" aria-labelledby="editStudentModalLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="editStudentModalLabel">Edit Student</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <!-- ... your edit form ... -->
  </div>
</div>
</body>
</html>