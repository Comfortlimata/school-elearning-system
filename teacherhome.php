<?php
session_start();

// Check if teacher is logged in
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'teacher') {
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

// Get teacher information
$teacher_id = $_SESSION['teacher_id'];
$teacher_sql = "SELECT * FROM teacher WHERE id = ?";
$stmt = mysqli_prepare($data, $teacher_sql);
mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);
$teacher_result = mysqli_stmt_get_result($stmt);
$teacher = mysqli_fetch_assoc($teacher_result);

// Get all grades and subjects assigned to this teacher
$teacher_grades = mysqli_query($data, "SELECT DISTINCT g.name as name FROM teacher_grade_subjects tgs JOIN grades g ON tgs.grade_id = g.id WHERE tgs.teacher_id = $teacher_id ORDER BY g.name");
$teacher_subjects = mysqli_query($data, "SELECT DISTINCT s.name as name, g.name as grade_name, (SELECT GROUP_CONCAT(DISTINCT gsa.section) FROM grade_subject_assignments gsa WHERE gsa.grade_id = tgs.grade_id AND gsa.subject_id = tgs.subject_id) as sections FROM teacher_grade_subjects tgs JOIN grades g ON tgs.grade_id = g.id JOIN subjects s ON tgs.subject_id = s.id WHERE tgs.teacher_id = $teacher_id ORDER BY g.name, s.name");

// Get selected grade/subject from GET
$selected_grade = isset($_GET['grade']) ? $_GET['grade'] : '';
$selected_subject = isset($_GET['subject']) ? $_GET['subject'] : '';

// Build filter for queries
$filter = "WHERE tgs.teacher_id = $teacher_id";
if ($selected_grade) $filter .= " AND tgs.grade = '$selected_grade'";
if ($selected_subject) $filter .= " AND tgs.subject = '$selected_subject'";

// Filtered assignment count
$assignment_count = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM teacher_grade_subjects tgs $filter"))[0];

// Filtered student count - simplified for now
$student_count = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM students"))[0];

// New: Use teacher_grade_subjects mapping
$total_students = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM students"))[0];

// Handle Add Course form submission (at the top, after DB connection and teacher info)
$add_course_message = '';
$grades_result = mysqli_query($data, "SELECT id, name FROM grades ORDER BY id");
if (isset($_POST['add_course_teacher'])) {
    $name = mysqli_real_escape_string($data, $_POST['course_name']);
    $code = mysqli_real_escape_string($data, $_POST['course_code']);
    $desc = mysqli_real_escape_string($data, $_POST['course_description']);
    $credits = mysqli_real_escape_string($data, $_POST['credits']);
    $duration = mysqli_real_escape_string($data, $_POST['duration']);
    $grade_id = isset($_POST['grade_id']) ? (int)$_POST['grade_id'] : 0;
    $section = isset($_POST['section']) ? mysqli_real_escape_string($data, $_POST['section']) : '';
    $teacher_id = $_SESSION['teacher_id'];
    // File Upload
    $filePath = '';
    if (isset($_FILES['course_file']) && $_FILES['course_file']['error'] == 0) {
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $fileType = $_FILES['course_file']['type'];
        if (!in_array($fileType, $allowedTypes)) {
            $add_course_message = "Invalid file type. Only PDF, DOC, and DOCX allowed.";
        } else {
            $uploadDir = "uploads/";
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
            $fileName = time() . '_' . basename($_FILES["course_file"]["name"]);
            $filePath = $uploadDir . $fileName;
            if (!move_uploaded_file($_FILES["course_file"]["tmp_name"], $filePath)) {
                $add_course_message = "Failed to upload file. Please try again.";
                $filePath = '';
            }
        }
    }
    if (empty($add_course_message)) {
        $sql = "INSERT INTO courses (course_name, course_code, course_description, document_path, credits, duration, grade_id, section, teacher_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($data, $sql);
        mysqli_stmt_bind_param($stmt, "ssssiiisi", $name, $code, $desc, $filePath, $credits, $duration, $grade_id, $section, $teacher_id);
        if (mysqli_stmt_execute($stmt)) {
            $add_course_message = "<span style='color:green;'>Course added successfully!</span>";
            $_POST = array(); // Clear form
        } else {
            $add_course_message = "<span style='color:red;'>Error: " . mysqli_error($data) . "</span>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher Dashboard - Miles e-School Academy</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --border-color: #e5e7eb;
        }
        
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: var(--light-color);
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 280px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        
        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }
        
        .sidebar-brand:hover {
            color: white;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .sidebar-item {
            margin: 0.5rem 1rem;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .sidebar-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .sidebar-icon {
            width: 20px;
            margin-right: 0.75rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }
        
        /* Header */
        .top-header {
            background: white;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .teacher-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .teacher-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .logout-btn {
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #dc2626;
            color: white;
            transform: translateY(-2px);
        }
        
        /* Dashboard Content */
        .dashboard-content {
            padding: 2rem;
        }
        
        .welcome-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        
        .welcome-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .welcome-subtitle {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .stat-icon.courses { background: var(--success-color); }
        .stat-icon.students { background: var(--info-color); }
        .stat-icon.total-students { background: var(--warning-color); }
        .stat-icon.specialization { background: var(--danger-color); }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            color: #6b7280;
            font-weight: 500;
        }
        
        /* Quick Actions */
        .quick-actions {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 1.5rem;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .action-card {
            background: var(--light-color);
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            text-decoration: none;
            color: var(--dark-color);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .action-card:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
        }
        
        .action-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .action-card:hover .action-icon {
            color: white;
        }
        
        .action-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .action-desc {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .actions-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
        }
        
        /* Toggle Button */
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark-color);
        }
        
        @media (max-width: 768px) {
            .sidebar-toggle {
                display: block;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-brand">
                <i class="fas fa-chalkboard-teacher me-2"></i>
                Teacher Portal
            </a>
        </div>
        <div class="sidebar-menu">
            <div class="sidebar-item">
                <a href="teacherhome.php" class="sidebar-link active">
                    <i class="fas fa-tachometer-alt sidebar-icon"></i>
                    Dashboard
                </a>
            </div>
            <div class="sidebar-item">
                <a href="add_courses.php" class="sidebar-link">
                    <i class="fas fa-book sidebar-icon"></i>
                    Manage Courses
                </a>
            </div>
            <div class="sidebar-item">
                <a href="teacher_view_courses.php" class="sidebar-link">
                    <i class="fas fa-list sidebar-icon"></i>
                    View Courses & Students
                </a>
            </div>
        </div>
    </div>
    <div class="main-content">
        <div class="top-header">
            <div class="d-flex align-items-center">
                <button class="sidebar-toggle me-3" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="header-title mb-0">Dashboard</h1>
            </div>
            <div class="header-actions">
                <div class="teacher-info">
                    <div class="teacher-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                        <small class="text-muted">Teacher</small>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
        <div class="dashboard-content">
            <div class="welcome-section">
                <h1 class="welcome-title">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                <p class="welcome-subtitle">This is your e-learning dashboard. More features coming soon.</p>
            </div>
            <!-- Removed Upload Material Section and Uploaded Materials Table -->


            <div class="card mt-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-book me-2"></i>Your Registered Subjects</h5>
                </div>
                <div class="card-body">
                    <?php 
                    // Get teacher's assignments with proper grade-subject-section display
                    $teacher_classes_sql = "SELECT 
                        s.name as subject,
                        g.name as grade,
                        tgs.grade_id,
                        tgs.subject_id,
                        gsa.section
                    FROM teacher_grade_subjects tgs
                    JOIN grades g ON tgs.grade_id = g.id
                    JOIN subjects s ON tgs.subject_id = s.id
                    JOIN grade_subject_assignments gsa ON gsa.grade_id = tgs.grade_id AND gsa.subject_id = tgs.subject_id
                    WHERE tgs.teacher_id = $teacher_id
                    ORDER BY g.name, s.name, gsa.section";
                    
                    $teacher_classes = mysqli_query($data, $teacher_classes_sql);
                    
                    if ($teacher_classes && mysqli_num_rows($teacher_classes) > 0): ?>
                        <ul class="list-group">
                            <?php while ($class = mysqli_fetch_assoc($teacher_classes)): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <a href="teacher_view_courses.php?grade_id=<?php echo $class['grade_id']; ?>&section=<?php echo urlencode($class['section']); ?>&subject_id=<?php echo $class['subject_id']; ?>" style="text-decoration:none;font-weight:bold;">
                                            <?php echo htmlspecialchars($class['subject']); ?>
                                        </a>
                                        <br>
                                        <small class="text-muted">
                                            Grade <?php echo htmlspecialchars($class['grade']); ?>
                                            - Section: <?php echo htmlspecialchars($class['section']); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-primary">
                                        Grade <?php echo htmlspecialchars($class['grade']); ?> (<?php echo htmlspecialchars($class['section']); ?>)
                                    </span>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>No subjects found for your grade and section.</strong>
                            <br>
                            <small class="text-muted">
                                Please contact the administrator to assign subjects to your account, or use the new teacher registration form to properly set up your grade-subject assignments.
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
        
        // Active link highlighting
        const currentPage = window.location.pathname.split('/').pop();
        const sidebarLinks = document.querySelectorAll('.sidebar-link');
        
        sidebarLinks.forEach(link => {
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
    </script>

    <!-- Add Course Modal -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form method="POST" enctype="multipart/form-data">
            <div class="modal-header">
              <h5 class="modal-title" id="addCourseModalLabel"><i class="fas fa-book me-2"></i>Add New Course</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <?php if (!empty($add_course_message)): ?>
                <div class="alert alert-info"><?php echo $add_course_message; ?></div>
              <?php endif; ?>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label for="course_name" class="form-label"><i class="fas fa-book me-2"></i>Course Name *</label>
                    <input type="text" class="form-control" name="course_name" id="course_name" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label for="course_code" class="form-label"><i class="fas fa-code me-2"></i>Course Code *</label>
                    <input type="text" class="form-control" name="course_code" id="course_code" required>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label for="grade_id" class="form-label"><i class="fas fa-graduation-cap me-2"></i>Grade *</label>
                    <select name="grade_id" id="grade_id" class="form-select" required>
                      <option value="">-- Select Grade --</option>
                      <?php if ($grades_result) mysqli_data_seek($grades_result, 0); while ($grade = mysqli_fetch_assoc($grades_result)): ?>
                        <option value="<?php echo $grade['id']; ?>"><?php echo htmlspecialchars($grade['name']); ?></option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label for="section" class="form-label"><i class="fas fa-layer-group me-2"></i>Section *</label>
                    <select name="section" id="section" class="form-select" required>
                      <option value="">-- Select Section --</option>
                      <option value="A">A</option>
                      <option value="B">B</option>
                      <option value="C">C</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label for="credits" class="form-label"><i class="fas fa-star me-2"></i>Credits</label>
                    <input type="number" class="form-control" name="credits" id="credits" min="1" max="6" value="3">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label for="duration" class="form-label"><i class="fas fa-clock me-2"></i>Duration (weeks)</label>
                    <input type="number" class="form-control" name="duration" id="duration" min="1" max="52" value="16">
                  </div>
                </div>
              </div>
              <div class="form-group mb-3">
                <label for="course_file" class="form-label"><i class="fas fa-file-upload me-2"></i>Course Document</label>
                <input class="form-control" type="file" name="course_file" id="course_file" accept=".pdf,.doc,.docx">
                <small class="text-muted">PDF, DOC, DOCX (Max 10MB)</small>
              </div>
              <div class="form-group mb-3">
                <label for="course_description" class="form-label"><i class="fas fa-align-left me-2"></i>Course Description *</label>
                <textarea class="form-control" name="course_description" id="course_description" rows="4" required></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" name="add_course_teacher" class="btn btn-primary"><i class="fas fa-save me-2"></i>Add Course</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </form>
        </div>
      </div>
    </div>
</body>
</html> 