<?php
session_start();

// Check if user is logged in and is admin
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

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($data, $_POST['username']);
    $email = mysqli_real_escape_string($data, $_POST['email']);
    $name = mysqli_real_escape_string($data, $_POST['name']);
    $specialization = mysqli_real_escape_string($data, $_POST['specialization']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($name) || empty($password) || empty($specialization)) {
        $message = "Please fill in all required fields!";
    } else {
        // Check if username already exists
        $check_username = mysqli_query($data, "SELECT id FROM teacher WHERE username = '$username'");
        if (mysqli_num_rows($check_username) > 0) {
            $message = "Username already exists!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert teacher
            $insert_sql = "INSERT INTO teacher (username, password, name, email, specialization) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($data, $insert_sql);
            mysqli_stmt_bind_param($stmt, "sssss", $username, $hashed_password, $name, $email, $specialization);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Teacher added successfully!";
                $_POST = array();
            } else {
                $message = "Error adding teacher: " . mysqli_error($data);
            }
        }
    }
}

$teachers_sql = "SELECT username, name, specialization FROM teacher ORDER BY name";
$teachers_result = mysqli_query($data, $teachers_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test Add Teacher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Test Add Teacher</h2>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo strpos($message, 'successfully') !== false ? 'success' : 'danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Add New Teacher</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="specialization" class="form-label">Specialization *</label>
                                <input type="text" class="form-control" id="specialization" name="specialization" 
                                       value="<?php echo isset($_POST['specialization']) ? htmlspecialchars($_POST['specialization']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Add Teacher</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Current Teachers</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Name</th>
                                    <th>Specialization</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($teachers_result && mysqli_num_rows($teachers_result) > 0): ?>
                                    <?php while ($teacher = mysqli_fetch_assoc($teachers_result)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($teacher['username']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['specialization']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No teachers found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="adminhome.php" class="btn btn-secondary">Back to Admin Dashboard</a>
            <a href="add_teacher_auth.php" class="btn btn-info">Go to Full Add Teacher Page</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php mysqli_close($data); ?> 