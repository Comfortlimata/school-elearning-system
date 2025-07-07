<?php
session_start();

// Database connection
$conn = mysqli_connect("localhost", "root", "", "schoolproject");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Only allow access to admins or teachers
if (!isset($_SESSION['username']) || !in_array($_SESSION['usertype'], ['admin', 'teacher'])) {
    header("Location: login.php");
    exit();
}

$message = '';

// Fetch grades for dropdown
$grades_result = mysqli_query($conn, "SELECT id, name FROM grades ORDER BY id");
// Fetch subjects for dropdown

// --- ADMIN ADD COURSE LOGIC ---
if ($_SESSION['usertype'] === 'admin') {
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
}

// --- TEACHER UPLOAD MATERIAL LOGIC ---
if ($_SESSION['usertype'] === 'teacher') {
    $teacher_id = $_SESSION['teacher_id'];
    $data = $conn; // for compatibility with teacherhome.php code
    $upload_message = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_material'])) {
        $subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
        $grade_id = isset($_POST['grade_id']) ? (int)$_POST['grade_id'] : 0;
        $section = isset($_POST['section']) ? $_POST['section'] : '';
        $title = mysqli_real_escape_string($data, $_POST['title'] ?? '');
        $description = mysqli_real_escape_string($data, $_POST['description'] ?? '');
        $filePath = '';
        $fileName = '';
        $fileType = '';
        $fileSize = 0;
        if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] == 0) {
            $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            $fileType = $_FILES['material_file']['type'];
            if (!in_array($fileType, $allowedTypes)) {
                $upload_message = "Invalid file type. Only PDF, DOC, and DOCX allowed.";
            } else {
                $uploadDir = "uploads/";
                if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
                $fileName = time() . '_' . basename($_FILES["material_file"]["name"]);
                $filePath = $uploadDir . $fileName;
                $fileSize = $_FILES['material_file']['size'];
                if (!move_uploaded_file($_FILES["material_file"]["tmp_name"], $filePath)) {
                    $upload_message = "Failed to upload file. Please try again.";
                    $filePath = '';
                }
            }
        }
        if (empty($upload_message) && $subject_id && $grade_id && $section && $filePath) {
            $sql = "INSERT INTO course_materials (teacher_id, subject_id, grade_id, section, title, description, file_path, file_name, file_size, file_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($data, $sql);
            mysqli_stmt_bind_param($stmt, "iiisssssis", $teacher_id, $subject_id, $grade_id, $section, $title, $description, $filePath, $fileName, $fileSize, $fileType);
            if (mysqli_stmt_execute($stmt)) {
                $upload_message = '<span class="text-success">Material uploaded successfully!</span>';
            } else {
                $upload_message = '<span class="text-danger">Error: ' . mysqli_error($data) . '</span>';
            }
        } elseif (empty($upload_message) && isset($_POST['upload_material'])) {
            $upload_message = '<span class="text-danger">Please fill all fields and upload a valid file.</span>';
        }
    }
}
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
    <?php if ($_SESSION['usertype'] === 'admin'): ?>
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
    <?php elseif ($_SESSION['usertype'] === 'teacher'): ?>
    <aside>
        <ul>
            <li>
                <a href="teacherhome.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="add_courses.php" class="active">
                    <i class="fas fa-book me-2"></i>
                    Manage Courses
                </a>
            </li>
            <li>
                <a href="teacher_students.php">
                    <i class="fas fa-users me-2"></i>
                    View Students
                </a>
            </li>
        </ul>
    </aside>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="content fade-in">
        <div class="row">
            <?php if ($_SESSION['usertype'] === 'admin'): ?>
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
                        <!-- Admin Add Course Form (existing code) -->
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
                                        <select name="grade_id" id="grade_id" class="form-select" required size="6">
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
                                        <label for="subject_id" class="form-label">
                                            <i class="fas fa-book me-2"></i>Subject *
                                        </label>
                                        <select name="subject_id" id="subject_id" class="form-select" required size="6">
                                            <option value="">-- Select Subject --</option>
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
            <?php endif; ?>

            <?php if ($_SESSION['usertype'] === 'teacher'): ?>
            <div class="col-md-12">
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Upload Subject Material</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($upload_message)): ?>
                            <div class="alert alert-info"><?php echo $upload_message; ?></div>
                        <?php endif; ?>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="grade_id" class="form-label">Grade *</label>
                                        <select name="grade_id" id="teacher_grade_id" class="form-select" required size="6">
                                            <option value="">-- Select Grade --</option>
                                            <?php
                                            $teacher_grades = mysqli_query($conn, "SELECT DISTINCT g.id, g.name FROM teacher_grade_subjects tgs JOIN grades g ON tgs.grade_id = g.id WHERE tgs.teacher_id = $teacher_id ORDER BY g.name");
                                            while ($grade = mysqli_fetch_assoc($teacher_grades)) {
                                                echo '<option value="' . $grade['id'] . '">' . htmlspecialchars($grade['name']) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="section" class="form-label">Section *</label>
                                        <select name="section" id="teacher_section" class="form-select" required>
                                            <option value="">-- Select Section --</option>
                                            <option value="A">A</option>
                                            <option value="B">B</option>
                                            <option value="C">C</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="subject_id" class="form-label">Subject *</label>
                                        <select name="subject_id" id="teacher_subject_id" class="form-select" required size="6">
                                            <option value="">-- Select Subject --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="title" class="form-label">Title *</label>
                                        <input type="text" class="form-control" name="title" id="title" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="material_file" class="form-label">File (PDF, DOC, DOCX) *</label>
                                        <input class="form-control" type="file" name="material_file" id="material_file" accept=".pdf,.doc,.docx" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" name="description" id="description" rows="2"></textarea>
                            </div>
                            <button type="submit" name="upload_material" class="btn btn-primary"><i class="fas fa-upload me-2"></i>Upload Material</button>
                        </form>
                    </div>
                </div>
                <!-- List of Uploaded Materials -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-folder-open me-2"></i>Your Uploaded Materials</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $materials = mysqli_query($conn, "SELECT * FROM course_materials WHERE teacher_id = $teacher_id ORDER BY created_at DESC");
                        if ($materials && mysqli_num_rows($materials) > 0): ?>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Subject</th>
                                        <th>Grade</th>
                                        <th>Section</th>
                                        <th>File</th>
                                        <th>Uploaded</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($mat = mysqli_fetch_assoc($materials)):
                                        // Get subject and grade names
                                        $subject_name = '';
                                        $grade_name = '';
                                        $sres = mysqli_query($conn, "SELECT name FROM subjects WHERE id = " . (int)$mat['subject_id']);
                                        if ($sres && $srow = mysqli_fetch_assoc($sres)) $subject_name = $srow['name'];
                                        $gres = mysqli_query($conn, "SELECT name FROM grades WHERE id = " . (int)$mat['grade_id']);
                                        if ($gres && $grow = mysqli_fetch_assoc($gres)) $grade_name = $grow['name'];
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($mat['title']); ?></td>
                                        <td><?php echo htmlspecialchars($subject_name); ?></td>
                                        <td><?php echo htmlspecialchars($grade_name); ?></td>
                                        <td><?php echo htmlspecialchars($mat['section']); ?></td>
                                        <td><a href="<?php echo htmlspecialchars($mat['file_path']); ?>" target="_blank" class="btn btn-sm btn-primary"><i class="fas fa-download me-1"></i>Download</a></td>
                                        <td><?php echo date('Y-m-d', strtotime($mat['created_at'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">No materials uploaded yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const gradeSelect = document.getElementById('grade_id');
    const subjectSelect = document.getElementById('subject_id');
    gradeSelect.addEventListener('change', function() {
        const gradeId = this.value;
        subjectSelect.innerHTML = '<option value="">Loading...</option>';
        if (gradeId) {
            fetch('get_subjects_by_grade.php?grade_id=' + gradeId)
                .then(response => response.json())
                .then(data => {
                    let options = '<option value="">-- Select Subject --</option>';
                    data.forEach(subject => {
                        options += `<option value="${subject.id}">${subject.name}</option>`;
                    });
                    subjectSelect.innerHTML = options;
                })
                .catch(() => {
                    subjectSelect.innerHTML = '<option value="">No subjects found</option>';
                });
        } else {
            subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
        }
    });
});
</script>
    <script>
    // Dynamic subject dropdown for teacher upload form
    document.addEventListener('DOMContentLoaded', function() {
        const gradeSelect = document.getElementById('teacher_grade_id');
        const sectionSelect = document.getElementById('teacher_section');
        const subjectSelect = document.getElementById('teacher_subject_id');
        if (gradeSelect && sectionSelect && subjectSelect) {
            function updateSubjects() {
                const gradeId = gradeSelect.value;
                const section = sectionSelect.value;
                subjectSelect.innerHTML = '<option value="">Loading...</option>';
                if (gradeId && section) {
                    fetch('get_teacher_subjects_by_grade_section.php?grade_id=' + gradeId + '&section=' + encodeURIComponent(section))
                        .then(response => response.json())
                        .then(data => {
                            let options = '<option value="">-- Select Subject --</option>';
                            data.forEach(subject => {
                                options += `<option value="${subject.id}">${subject.name}</option>`;
                            });
                            subjectSelect.innerHTML = options;
                        })
                        .catch(() => {
                            subjectSelect.innerHTML = '<option value="">No subjects found</option>';
                        });
                } else {
                    subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
                }
            }
            gradeSelect.addEventListener('change', updateSubjects);
            sectionSelect.addEventListener('change', updateSubjects);
        }
    });
    </script>
    <style>
.form-select {
    max-height: 220px;
    overflow-y: auto;
}
</style>
</body>
</html>