<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Redirect unauthorized users
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "schoolproject");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$grades_result = mysqli_query($conn, "SELECT id, name FROM grades ORDER BY id");
$subjects_result = mysqli_query($conn, "SELECT id, name FROM subjects ORDER BY name");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user input
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $grade_id = isset($_POST['grade_id']) ? (int)$_POST['grade_id'] : 0;
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $date_of_birth = !empty($_POST['date_of_birth']) ? mysqli_real_escape_string($conn, $_POST['date_of_birth']) : NULL;
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $section = isset($_POST['section']) ? mysqli_real_escape_string($conn, $_POST['section']) : '';

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if username or email already exists
    $check_sql = "SELECT id FROM students WHERE username = '$username' OR email = '$email'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $message = "Username or email already exists!";
    } elseif (!$grade_id) {
        $message = "Please select a grade. (Debug: grade_id is $grade_id)";
    } else {
    // Insert data into the database
        $sql = "INSERT INTO students (username, email, password, grade_id, full_name, phone, address, date_of_birth, gender, section) 
                VALUES ('$username', '$email', '$hashed_password', $grade_id, '$full_name', '$phone', '$address', ".($date_of_birth ? "'$date_of_birth'" : "NULL").", '$gender', '$section')";
    $result = mysqli_query($conn, $sql);

    if ($result) {
            $message = "Student added successfully!";
            $_POST = array(); // Clear form
    } else {
            $message = "Error adding student: " . mysqli_error($conn);
        }
    }
}

// Always fetch grades for the dropdown after form processing
$grades_result = mysqli_query($conn, "SELECT id, name FROM grades ORDER BY id");

// Get current students count for display
$students_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM students"))[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Student - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
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
                <a href="admission.php">
                    <i class="fas fa-user-plus me-2"></i>
                    Admissions
                </a>
            </li>
            <li>
                <a href="add_student.php" class="active">
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
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-user-graduate me-2"></i>Add New Student</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo strpos($message, 'successfully') !== false ? 'success' : 'danger'; ?>">
                                <i class="fas fa-<?php echo strpos($message, 'successfully') !== false ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="add_student.php">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="username" class="form-label">
                                            <i class="fas fa-user me-2"></i>Username *
                                        </label>
                                        <input type="text" name="username" id="username" class="form-control" 
                                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope me-2"></i>Email *
                                        </label>
                                        <input type="email" name="email" id="email" class="form-control" 
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="full_name" class="form-label">
                                            <i class="fas fa-id-card me-2"></i>Full Name *
                                        </label>
                                        <input type="text" name="full_name" id="full_name" class="form-control" 
                                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone" class="form-label">
                                            <i class="fas fa-phone me-2"></i>Phone Number
                                        </label>
                                        <input type="tel" name="phone" id="phone" class="form-control" 
                                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    </div>
                                </div>
        </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password" class="form-label">
                                            <i class="fas fa-lock me-2"></i>Password *
                                        </label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="grade_id" class="form-label">
                                            <i class="fas fa-graduation-cap me-2"></i>Select Grade *
                                        </label>
                                        <select name="grade_id" id="grade_id" class="form-select" required>
                                            <option value="">-- Select Grade --</option>
                                            <?php while ($grade = mysqli_fetch_assoc($grades_result)): ?>
                                                <option value="<?php echo $grade['id']; ?>" <?php echo (isset($_POST['grade_id']) && $_POST['grade_id'] == $grade['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($grade['name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="section" class="form-label">
                                            <i class="fas fa-layer-group me-2"></i>Section *
                                        </label>
                                        <select name="section" id="section" class="form-select" required>
                                            <option value="">-- Select Section --</option>
                                            <option value="A" <?php echo (isset($_POST['section']) && $_POST['section'] == 'A') ? 'selected' : ''; ?>>A</option>
                                            <option value="B" <?php echo (isset($_POST['section']) && $_POST['section'] == 'B') ? 'selected' : ''; ?>>B</option>
                                            <option value="C" <?php echo (isset($_POST['section']) && $_POST['section'] == 'C') ? 'selected' : ''; ?>>C</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date_of_birth" class="form-label">
                                            <i class="fas fa-calendar me-2"></i>Date of Birth
                                        </label>
                                        <input type="date" name="date_of_birth" id="date_of_birth" class="form-control">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gender" class="form-label">
                                            <i class="fas fa-venus-mars me-2"></i>Gender
                                        </label>
                                        <select name="gender" id="gender" class="form-select">
                                            <option value="">-- Select Gender --</option>
                                            <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
            </select>
        </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="address" class="form-label">
                                    <i class="fas fa-map-marker-alt me-2"></i>Address
                                </label>
                                <textarea name="address" id="address" class="form-control" rows="3" 
                                          placeholder="Enter student's address..."><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Add Student
                                </button>
                                <a href="adminhome.php" class="btn btn-secondary ms-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                </a>
                            </div>
    </form>
</div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle me-2"></i>Student Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <i class="fas fa-user-graduate" style="font-size: 3rem; color: var(--primary-color);"></i>
                        </div>
                        <div class="text-center">
                            <h6>Total Students</h6>
                            <h3 class="text-primary"><?php echo $students_count; ?></h3>
                        </div>
                        <hr>
                        <div class="small text-muted">
                            <p><i class="fas fa-info-circle me-2"></i>All fields marked with * are required.</p>
                            <p><i class="fas fa-shield-alt me-2"></i>Passwords are securely hashed.</p>
                            <p><i class="fas fa-check-circle me-2"></i>Duplicate usernames/emails are not allowed.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add some interactive features
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation enhancement
            const form = document.querySelector('form');
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                field.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        this.classList.add('invalid');
                    } else {
                        this.classList.remove('invalid');
                    }
                });
            });
            
            // Password strength indicator
            const passwordField = document.getElementById('password');
            passwordField.addEventListener('input', function() {
                const password = this.value;
                const strength = calculatePasswordStrength(password);
                updatePasswordStrengthIndicator(strength);
            });
        });
        
        function calculatePasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            return strength;
        }
        
        function updatePasswordStrengthIndicator(strength) {
            const passwordField = document.getElementById('password');
            const colors = ['#ef4444', '#f59e0b', '#f59e0b', '#10b981', '#10b981'];
            const messages = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
            
            if (strength > 0) {
                passwordField.style.borderColor = colors[strength - 1];
                passwordField.title = `Password Strength: ${messages[strength - 1]}`;
            } else {
                passwordField.style.borderColor = '';
                passwordField.title = '';
            }
        }
    </script>
</body>
</html>