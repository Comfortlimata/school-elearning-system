<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'admin') {
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

$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['set_default'])) {
        // Set default password for all teachers
        $default_password = 'teacher123';
        $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
        
        $update_sql = "UPDATE teachers SET password = ? WHERE password = '' OR password IS NULL";
        $stmt = mysqli_prepare($data, $update_sql);
        mysqli_stmt_bind_param($stmt, "s", $hashed_password);
        
        if (mysqli_stmt_execute($stmt)) {
            $affected_rows = mysqli_stmt_affected_rows($stmt);
            $message = "Successfully set default password '$default_password' for $affected_rows teachers!";
        } else {
            $message = "Error updating passwords: " . mysqli_error($data);
        }
    }
    
    if (isset($_POST['assign_individual'])) {
        // Assign individual password
        $teacher_id = (int)$_POST['teacher_id'];
        $new_password = $_POST['new_password'];
        
        if (strlen($new_password) >= 6) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_sql = "UPDATE teachers SET password = ? WHERE id = ?";
            $stmt = mysqli_prepare($data, $update_sql);
            mysqli_stmt_bind_param($stmt, "si", $hashed_password, $teacher_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Password updated successfully for teacher ID: $teacher_id";
            } else {
                $message = "Error updating password: " . mysqli_error($data);
            }
        } else {
            $message = "Password must be at least 6 characters long!";
        }
    }
}

// Get all teachers
$teachers_sql = "SELECT id, name, email, specialization, password FROM teachers ORDER BY name";
$teachers_result = mysqli_query($data, $teachers_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assign Teacher Passwords - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .main-content {
            margin-left: 280px;
            padding: 2rem;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }
        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        .table th {
            background: #f8f9fa;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <?php include 'adminsidebar.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-key me-3"></i>Assign Teacher Passwords</h1>
            <a href="adminhome.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Set Default Password for All -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Set Default Password for All Teachers</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">This will set the password "teacher123" for all teachers who don't have a password yet.</p>
                <form method="POST">
                    <button type="submit" name="set_default" class="btn btn-primary">
                        <i class="fas fa-key me-2"></i>Set Default Password (teacher123)
                    </button>
                </form>
            </div>
        </div>

        <!-- Individual Password Assignment -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Assign Individual Password</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-4">
                        <label for="teacher_id" class="form-label">Select Teacher</label>
                        <select class="form-select" id="teacher_id" name="teacher_id" required>
                            <option value="">Choose a teacher...</option>
                            <?php 
                            mysqli_data_seek($teachers_result, 0);
                            while ($teacher = mysqli_fetch_assoc($teachers_result)): 
                            ?>
                                <option value="<?php echo $teacher['id']; ?>">
                                    <?php echo htmlspecialchars($teacher['name']); ?> (<?php echo htmlspecialchars($teacher['email']); ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" 
                               minlength="6" required placeholder="Enter new password">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" name="assign_individual" class="btn btn-success d-block">
                            <i class="fas fa-save me-2"></i>Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Teachers Table -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Current Teachers and Password Status</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Specialization</th>
                                <th>Password Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            mysqli_data_seek($teachers_result, 0);
                            while ($teacher = mysqli_fetch_assoc($teachers_result)): 
                            ?>
                                <tr>
                                    <td><?php echo $teacher['id']; ?></td>
                                    <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                                    <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                    <td><?php echo htmlspecialchars($teacher['specialization']); ?></td>
                                    <td>
                                        <?php if (!empty($teacher['password'])): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Password Set
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>No Password
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="setTeacherPassword(<?php echo $teacher['id']; ?>, '<?php echo htmlspecialchars($teacher['name']); ?>')">
                                            <i class="fas fa-key me-1"></i>Set Password
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function setTeacherPassword(teacherId, teacherName) {
            const password = prompt(`Enter new password for ${teacherName}:`);
            if (password && password.length >= 6) {
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="teacher_id" value="${teacherId}">
                    <input type="hidden" name="new_password" value="${password}">
                    <input type="hidden" name="assign_individual" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            } else if (password !== null) {
                alert('Password must be at least 6 characters long!');
            }
        }
    </script>
</body>
</html> 