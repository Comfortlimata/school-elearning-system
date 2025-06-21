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
    <title>Assign Teacher Passwords</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-key me-2"></i>Assign Teacher Passwords</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Set Default Password -->
                        <div class="mb-4">
                            <h5>Set Default Password for All Teachers</h5>
                            <p class="text-muted">This will set "teacher123" as password for all teachers without passwords.</p>
                            <form method="POST">
                                <button type="submit" name="set_default" class="btn btn-success">
                                    <i class="fas fa-users me-2"></i>Set Default Password (teacher123)
                                </button>
                            </form>
                        </div>

                        <hr>

                        <!-- Individual Password Assignment -->
                        <div class="mb-4">
                            <h5>Assign Individual Password</h5>
                            <form method="POST" class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Select Teacher</label>
                                    <select class="form-select" name="teacher_id" required>
                                        <option value="">Choose a teacher...</option>
                                        <?php 
                                        while ($teacher = mysqli_fetch_assoc($teachers_result)): 
                                        ?>
                                            <option value="<?php echo $teacher['id']; ?>">
                                                <?php echo htmlspecialchars($teacher['name']); ?> (<?php echo htmlspecialchars($teacher['email']); ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="new_password" 
                                           minlength="6" required placeholder="Enter password">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" name="assign_individual" class="btn btn-primary w-100">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <hr>

                        <!-- Teachers List -->
                        <div>
                            <h5>Current Teachers</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Password Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        mysqli_data_seek($teachers_result, 0);
                                        while ($teacher = mysqli_fetch_assoc($teachers_result)): 
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                                                <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                                <td>
                                                    <?php if (!empty($teacher['password'])): ?>
                                                        <span class="badge bg-success">Password Set</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">No Password</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-3">
                            <a href="adminhome.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 