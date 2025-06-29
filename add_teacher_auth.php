<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "Start of file<br>";

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

// Define teachers SQL and result early so it's always available
$teachers_sql = "SELECT username, email, name, grade_section, department, status 
                 FROM teacher 
                 ORDER BY name";
$teachers_result = mysqli_query($data, $teachers_sql);

$message = '';

// Get available courses for selection
$courses_sql = "SELECT * FROM courses ORDER BY course_name";
$courses_result = mysqli_query($data, $courses_sql);

// Fetch grades for selection
$grades_result = mysqli_query($data, "SELECT id, name FROM grades ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($data, $_POST['username'] ?? '');
    $email = mysqli_real_escape_string($data, $_POST['email'] ?? '');
    $name = mysqli_real_escape_string($data, $_POST['name'] ?? '');
    $phone = mysqli_real_escape_string($data, $_POST['phone'] ?? '');
    $grade_section = mysqli_real_escape_string($data, $_POST['grade_section'] ?? '');
    $qualification = mysqli_real_escape_string($data, $_POST['qualification'] ?? '');
    $experience_years = (int)($_POST['experience_years'] ?? 0);
    $bio = mysqli_real_escape_string($data, $_POST['bio'] ?? '');
    $department = mysqli_real_escape_string($data, $_POST['department'] ?? '');
    $office_location = mysqli_real_escape_string($data, $_POST['office_location'] ?? '');
    $office_hours = mysqli_real_escape_string($data, $_POST['office_hours'] ?? '');
    $linkedin_url = mysqli_real_escape_string($data, $_POST['linkedin_url'] ?? '');
    $password = $_POST['password'] ?? '';
    $status = $_POST['status'] ?? '';
    $selected_grades = isset($_POST['grades']) ? $_POST['grades'] : [];

    if (empty($username) || empty($email) || empty($name) || empty($password) || empty($grade_section) || empty($qualification)) {
        $message = "Please fill in all required fields!";
    } elseif (empty($selected_grades)) {
        $message = "Please select at least one grade for the teacher!";
    } else {
        // Check if username already exists
        $check_username = mysqli_query($data, "SELECT id FROM teacher WHERE username = '$username'");
        if (mysqli_num_rows($check_username) > 0) {
            $message = "Username already exists!";
        } else {
            // Check if email already exists
            $check_email = mysqli_query($data, "SELECT id FROM teacher WHERE email = '$email'");
            if (mysqli_num_rows($check_email) > 0) {
                $message = "Email already exists!";
            } else {
                // Start transaction
                mysqli_begin_transaction($data);
                
                try {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert teacher with comprehensive data
                    $insert_sql = "INSERT INTO teacher (
                        username, password, name, email, phone, grade_section, qualification, 
                        experience_years, bio, department, office_location, office_hours, 
                        linkedin_url, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = mysqli_prepare($data, $insert_sql);
                    mysqli_stmt_bind_param($stmt, "sssssssissssss", 
                        $username, $hashed_password, $name, $email, $phone, $grade_section, 
                        $qualification, $experience_years, $bio, $department, $office_location, 
                        $office_hours, $linkedin_url, $status
                    );
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $teacher_id = mysqli_insert_id($data);
                        
                        // Assign grades to teacher
                        foreach ($selected_grades as $grade_id) {
                            $grade_id = (int)$grade_id;
                            mysqli_query($data, "INSERT INTO teacher_grades (teacher_id, grade_id) VALUES ($teacher_id, $grade_id)");
                        }
                        
                        mysqli_commit($data);
                        $message = "Teacher added successfully with " . count($selected_grades) . " grade(s) assigned!";
                        $_POST = array();
                        // Refresh teachers list after adding
                        $teachers_result = mysqli_query($data, $teachers_sql);
                    } else {
                        throw new Exception("Error adding teacher: " . mysqli_error($data));
                    }
                } catch (Exception $e) {
                    mysqli_rollback($data);
                    $message = $e->getMessage();
                }
            }
        }
    }
}

// Handle delete teacher
if (isset($_POST['delete_teacher_id'])) {
    $username = mysqli_real_escape_string($data, $_POST['delete_teacher_id']);
    mysqli_query($data, "DELETE FROM teacher WHERE username = '$username'");
    header("Location: add_teacher_auth.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Teacher - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    
    <style>
        .course-selection {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .course-item {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .course-item:hover {
            border-color: #007bff;
            transform: translateY(-2px);
        }
        
        .course-item.selected {
            border-color: #007bff;
            background: #f0f8ff;
        }
        
        .course-checkbox {
            display: none;
        }
        
        .course-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .course-code {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.5rem;
        }
        
        .course-description {
            font-size: 0.85rem;
            color: #777;
            line-height: 1.4;
        }
        
        .course-program {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            margin-top: 0.5rem;
        }
        
        .selected-count {
            background: #007bff;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
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
                <a href="admission.php">
                    <i class="fas fa-user-plus me-2"></i>
                    Admission
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
                <a href="add_courses.php">
                    <i class="fas fa-book me-2"></i>
                    Add Course
                </a>
            </li>
            <li>
                <a href="content_management.php">
                    <i class="fas fa-file-alt me-2"></i>
                    Content Management
                </a>
            </li>
            <li>
                <a href="add_teacher_auth.php" class="active">
                    <i class="fas fa-chalkboard-teacher me-2"></i>
                    Add Teacher
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="content fade-in">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-chalkboard-teacher me-2"></i>Add New Teacher</h5>
                        <a href="current_teachers.php" class="btn btn-outline-primary btn-sm" id="viewTeachersBtn">
                            <i class="fas fa-users me-1"></i> View Teachers
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo strpos($message, 'successfully') !== false ? 'success' : 'danger'; ?>">
                                <i class="fas fa-<?php echo strpos($message, 'successfully') !== false ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="username" class="form-label">
                                            <i class="fas fa-user me-2"></i>Username *
                                        </label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope me-2"></i>Email *
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-label">
                                            <i class="fas fa-id-card me-2"></i>Full Name *
                                        </label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone" class="form-label">
                                            <i class="fas fa-phone me-2"></i>Phone
                                        </label>
                                        <input type="text" class="form-control" id="phone" name="phone" 
                                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="grade_section" class="form-label">
                                            <i class="fas fa-layer-group me-2"></i>Grade Section *
                                        </label>
                                        <input type="text" class="form-control" id="grade_section" name="grade_section" 
                                               value="<?php echo isset($_POST['grade_section']) ? htmlspecialchars($_POST['grade_section']) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="qualification" class="form-label">
                                            <i class="fas fa-certificate me-2"></i>Qualification *
                                        </label>
                                        <input type="text" class="form-control" id="qualification" name="qualification" 
                                               value="<?php echo isset($_POST['qualification']) ? htmlspecialchars($_POST['qualification']) : ''; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="experience_years" class="form-label">
                                            <i class="fas fa-clock me-2"></i>Years of Experience
                                        </label>
                                        <input type="number" class="form-control" id="experience_years" name="experience_years" 
                                               value="<?php echo isset($_POST['experience_years']) ? htmlspecialchars($_POST['experience_years']) : '0'; ?>" min="0">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="department" class="form-label">
                                            <i class="fas fa-building me-2"></i>Department
                                        </label>
                                        <input type="text" class="form-control" id="department" name="department" 
                                               value="<?php echo isset($_POST['department']) ? htmlspecialchars($_POST['department']) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="bio" class="form-label">
                                    <i class="fas fa-user-edit me-2"></i>Biography
                                </label>
                                <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Tell us about the teacher's background and expertise..."><?php echo isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : ''; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="office_location" class="form-label">
                                            <i class="fas fa-map-marker-alt me-2"></i>Office Location
                                        </label>
                                        <input type="text" class="form-control" id="office_location" name="office_location" 
                                               value="<?php echo isset($_POST['office_location']) ? htmlspecialchars($_POST['office_location']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="office_hours" class="form-label">
                                            <i class="fas fa-calendar-alt me-2"></i>Office Hours
                                        </label>
                                        <input type="text" class="form-control" id="office_hours" name="office_hours" 
                                               value="<?php echo isset($_POST['office_hours']) ? htmlspecialchars($_POST['office_hours']) : ''; ?>" placeholder="e.g., Mon-Fri 9AM-5PM">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="linkedin_url" class="form-label">
                                            <i class="fab fa-linkedin me-2"></i>LinkedIn URL
                                        </label>
                                        <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" 
                                               value="<?php echo isset($_POST['linkedin_url']) ? htmlspecialchars($_POST['linkedin_url']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password" class="form-label">
                                            <i class="fas fa-lock me-2"></i>Password *
                                        </label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="status" class="form-label">
                                    <i class="fas fa-toggle-on me-2"></i>Status
                                </label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="grades" class="form-label">
                                    <i class="fas fa-graduation-cap me-2"></i>Grades Taught *</label>
                                <select class="form-control" id="grades" name="grades[]" multiple required>
                                    <?php while ($grade = mysqli_fetch_assoc($grades_result)): ?>
                                        <option value="<?php echo $grade['id']; ?>" <?php echo (isset($_POST['grades']) && in_array($grade['id'], $_POST['grades'])) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($grade['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <small class="text-muted">Hold Ctrl (Windows) or Command (Mac) to select multiple grades.</small>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Add Teacher
                                </button>
                                <a href="adminhome.php" class="btn btn-secondary ms-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleCourse(courseId) {
            const checkbox = document.getElementById('course_' + courseId);
            const courseItem = checkbox.parentElement;
            
            checkbox.checked = !checkbox.checked;
            
            if (checkbox.checked) {
                courseItem.classList.add('selected');
            } else {
                courseItem.classList.remove('selected');
            }
            
            updateSelectedCount();
        }
        
        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.course-checkbox:checked');
            const countElement = document.getElementById('selectedCount');
            const countText = document.getElementById('countText');
            
            if (checkboxes.length > 0) {
                countElement.style.display = 'block';
                countText.textContent = checkboxes.length + ' course(s) selected';
            } else {
                countElement.style.display = 'none';
            }
        }
        
        // Initialize count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateSelectedCount();
            
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php mysqli_close($data); ?> 