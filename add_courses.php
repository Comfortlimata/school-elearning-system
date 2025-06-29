<?php
session_start();

// Database connection
$conn = mysqli_connect("localhost", "root", "", "schoolproject");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Only admins can add courses
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'admin') {
    echo "<script>alert('Access denied. Only admins can add courses.');</script>";
    header("Location: login.php");
    exit();
}

$message = '';

// Fetch grades for dropdown
$grades_result = mysqli_query($conn, "SELECT id, name FROM grades ORDER BY id");
// Fetch subjects for dropdown

if (isset($_POST['add_course'])) {
    $name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $code = mysqli_real_escape_string($conn, $_POST['course_code']);
    $desc = mysqli_real_escape_string($conn, $_POST['course_description']);
    $credits = mysqli_real_escape_string($conn, $_POST['credits']);
    $duration = mysqli_real_escape_string($conn, $_POST['duration']);
    $grade_id = isset($_POST['grade_id']) ? (int)$_POST['grade_id'] : 0;
    $section = isset($_POST['section']) ? mysqli_real_escape_string($conn, $_POST['section']) : '';
    
    // File Upload
    $filePath = '';
    if (isset($_FILES['course_file']) && $_FILES['course_file']['error'] == 0) {
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $fileType = $_FILES['course_file']['type'];

        if (!in_array($fileType, $allowedTypes)) {
            $message = "Invalid file type. Only PDF, DOC, and DOCX allowed.";
        } else {
            $uploadDir = "uploads/";
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileName = time() . '_' . basename($_FILES["course_file"]["name"]);
            $filePath = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES["course_file"]["tmp_name"], $filePath)) {
                $message = "Failed to upload file. Please try again.";
                $filePath = '';
            }
        }
    }

    if (empty($message)) {
        $sql = "INSERT INTO courses (course_name, course_code, course_description, document_path, credits, duration, grade_id, section)
                VALUES ('$name', '$code', '$desc', '$filePath', '$credits', '$duration', '$grade_id', '$section')";
        if (mysqli_query($conn, $sql)) {
            $message = "Course added successfully!";
            $_POST = array(); // Clear form
        } else {
            $message = "Error: " . mysqli_error($conn);
        }
    }
}

// Get current courses count for display
$courses_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM courses"))[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Course - Admin Dashboard</title>
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
                <a href="add_courses.php" class="active">
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
                        <h5><i class="fas fa-book me-2"></i>Add New Course</h5>
        </div>
        <div class="card-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo strpos($message, 'successfully') !== false ? 'success' : 'danger'; ?>">
                                <i class="fas fa-<?php echo strpos($message, 'successfully') !== false ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="course_name" class="form-label">
                                            <i class="fas fa-book me-2"></i>Course Name *
                                        </label>
                                        <input type="text" class="form-control" name="course_name" id="course_name" 
                                               value="<?php echo isset($_POST['course_name']) ? htmlspecialchars($_POST['course_name']) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="course_code" class="form-label">
                                            <i class="fas fa-code me-2"></i>Course Code *
                                        </label>
                                        <input type="text" class="form-control" name="course_code" id="course_code" 
                                               value="<?php echo isset($_POST['course_code']) ? htmlspecialchars($_POST['course_code']) : ''; ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="grade_id" class="form-label">
                                            <i class="fas fa-graduation-cap me-2"></i>Grade *
                                        </label>
                                        <select name="grade_id" id="grade_id" class="form-select" required>
                                            <option value="">-- Select Grade --</option>
                                            <?php if ($grades_result) mysqli_data_seek($grades_result, 0); while ($grade = mysqli_fetch_assoc($grades_result)): ?>
                                                <option value="<?php echo $grade['id']; ?>" <?php echo (isset($_POST['grade_id']) && $_POST['grade_id'] == $grade['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($grade['name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
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
                                        <label for="credits" class="form-label">
                                            <i class="fas fa-star me-2"></i>Credits
                                        </label>
                                        <input type="number" class="form-control" name="credits" id="credits" min="1" max="6" 
                                               value="<?php echo isset($_POST['credits']) ? htmlspecialchars($_POST['credits']) : '3'; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="duration" class="form-label">
                                            <i class="fas fa-clock me-2"></i>Duration (weeks)
                                        </label>
                                        <input type="number" class="form-control" name="duration" id="duration" min="1" max="52" 
                                               value="<?php echo isset($_POST['duration']) ? htmlspecialchars($_POST['duration']) : '16'; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="course_file" class="form-label">
                                            <i class="fas fa-file-upload me-2"></i>Course Document
                                        </label>
                                        <input class="form-control" type="file" name="course_file" id="course_file" accept=".pdf,.doc,.docx">
                                        <small class="text-muted">PDF, DOC, DOCX (Max 10MB)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="course_description" class="form-label">
                                    <i class="fas fa-align-left me-2"></i>Course Description *
                                </label>
                                <textarea class="form-control" name="course_description" id="course_description" rows="4" 
                                          placeholder="Enter detailed course description, objectives, and learning outcomes..." required><?php echo isset($_POST['course_description']) ? htmlspecialchars($_POST['course_description']) : ''; ?></textarea>
                            </div>

                            <div class="form-group">
                                <button type="submit" name="add_course" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Add Course
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
                        <h5><i class="fas fa-info-circle me-2"></i>Course Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <i class="fas fa-book" style="font-size: 3rem; color: var(--primary-color);"></i>
                        </div>
                        <div class="text-center">
                            <h6>Total Courses</h6>
                            <h3 class="text-primary"><?php echo $courses_count; ?></h3>
                        </div>
                        <hr>
                        <div class="small text-muted">
                            <p><i class="fas fa-info-circle me-2"></i>All fields marked with * are required.</p>
                            <p><i class="fas fa-file-pdf me-2"></i>Supported file types: PDF, DOC, DOCX</p>
                            <p><i class="fas fa-shield-alt me-2"></i>Files are securely stored and accessible to students.</p>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5><i class="fas fa-lightbulb me-2"></i>Tips</h5>
                    </div>
                    <div class="card-body">
                        <div class="small">
                            <p><i class="fas fa-check me-2"></i>Use clear, descriptive course names</p>
                            <p><i class="fas fa-check me-2"></i>Include detailed learning objectives</p>
                            <p><i class="fas fa-check me-2"></i>Upload relevant course materials</p>
                            <p><i class="fas fa-check me-2"></i>Set appropriate credit hours</p>
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
            
            // File upload preview
            const fileInput = document.getElementById('course_file');
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const fileSize = file.size / 1024 / 1024; // Convert to MB
                    if (fileSize > 10) {
                        alert('File size must be less than 10MB');
                        this.value = '';
                    }
                }
            });
            
            // Auto-generate course code based on name
            const courseNameInput = document.getElementById('course_name');
            const courseCodeInput = document.getElementById('course_code');
            
            courseNameInput.addEventListener('input', function() {
                if (courseCodeInput.value === '') {
                    const name = this.value;
                    const code = name.replace(/[^A-Z]/gi, '').substring(0, 3).toUpperCase() + 
                               Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                    courseCodeInput.value = code;
                }
            });
        });
    </script>
</body>
</html>