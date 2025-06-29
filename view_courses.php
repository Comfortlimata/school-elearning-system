<?php
session_start();

// Check if admin is logged in
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

// Handle course deletion
if (isset($_POST['delete_course'])) {
    $course_id = mysqli_real_escape_string($data, $_POST['course_id']);
    
    // Check if course has assigned teacher
    $check_teacher = mysqli_query($data, "SELECT teacher_id FROM courses WHERE id = '$course_id'");
    $course = mysqli_fetch_assoc($check_teacher);
    
    if ($course['teacher_id']) {
        $message = "Cannot delete course: It is assigned to a teacher. Please reassign or remove the teacher first.";
    } else {
        $delete_sql = "DELETE FROM courses WHERE id = '$course_id'";
        if (mysqli_query($data, $delete_sql)) {
            $message = "Course deleted successfully!";
        } else {
            $message = "Error deleting course: " . mysqli_error($data);
        }
    }
}

// Get all courses with teacher information
$courses_sql = "SELECT c.*, t.name as teacher_name, t.email as teacher_email, g.name as grade_name
                FROM courses c
                LEFT JOIN teacher t ON c.teacher_id = t.id
                LEFT JOIN grades g ON c.grade_id = g.id
                ORDER BY g.name, c.course_name";
$courses_result = mysqli_query($data, $courses_sql);

// Get statistics
$total_courses = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM courses"))[0];
$assigned_courses = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM courses WHERE teacher_id IS NOT NULL"))[0];
$unassigned_courses = $total_courses - $assigned_courses;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Courses - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    
    <style>
        .courses-container {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }
        
        .courses-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .courses-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        
        .add-course-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .add-course-btn:hover {
            background: rgba(255,255,255,0.3);
            color: white;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 0 1.5rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            text-align: center;
            border-left: 4px solid var(--primary-color);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .courses-table {
            min-width: 900px;
            width: 100%;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px #e0e7ef33;
        }
        
        .courses-table th {
            background: var(--light-color);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark-color);
            border-bottom: 2px solid var(--border-color);
        }
        
        .courses-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }
        
        .courses-table tr:hover {
            background: var(--light-color);
        }
        
        .course-name {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .course-code {
            font-family: 'Courier New', monospace;
            background: var(--light-color);
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        
        .course-program {
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .course-credits {
            background: var(--success-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .course-duration {
            background: var(--warning-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .teacher-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .teacher-name {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .teacher-email {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        
        .no-teacher {
            color: var(--danger-color);
            font-style: italic;
            font-size: 0.9rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-edit {
            background: var(--warning-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        
        .btn-edit:hover {
            background: #d97706;
            color: white;
        }
        
        .btn-delete {
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-delete:hover {
            background: #dc2626;
        }
        
        .btn-assign {
            background: var(--info-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        
        .btn-assign:hover {
            background: #0891b2;
            color: white;
        }
        
        .no-courses {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }
        
        .no-courses i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 1.5rem;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 1000px) {
            .courses-table {
                min-width: 700px;
            }
        }
        
        @media (max-width: 700px) {
            .courses-table {
                min-width: 500px;
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
                <a href="add_courses.php">
                    <i class="fas fa-book me-2"></i>
                    Add Course
                </a>
            </li>
            <li>
                <a href="view_courses.php" class="active">
                    <i class="fas fa-list me-2"></i>
                    View Courses
                </a>
            </li>
            <li>
                <a href="add_teacher_auth.php">
                    <i class="fas fa-chalkboard-teacher me-2"></i>
                    Add Teacher
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="content fade-in">
        <div class="courses-container">
            <div class="courses-header">
                <h1 class="courses-title">
                    <i class="fas fa-book me-2"></i>
                    Course Management
                </h1>
                <a href="add_courses.php" class="add-course-btn">
                    <i class="fas fa-plus me-2"></i>
                    Add New Course
                </a>
            </div>
            
            <!-- Statistics -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_courses; ?></div>
                    <div class="stat-label">Total Courses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $assigned_courses; ?></div>
                    <div class="stat-label">Assigned to Teachers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $unassigned_courses; ?></div>
                    <div class="stat-label">Unassigned</div>
                </div>
            </div>
            
            <!-- Alert Messages -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo strpos($message, 'successfully') !== false ? 'success' : 'danger'; ?>">
                    <i class="fas fa-<?php echo strpos($message, 'successfully') !== false ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Courses Table -->
            <div class="table-responsive" style="width:100%;overflow-x:auto;margin-bottom:1.5rem;">
                <table class="courses-table table table-striped align-middle" style="min-width:900px;width:100%;background:#fff;border-radius:12px;box-shadow:0 2px 8px #e0e7ef33;">
                    <thead>
                        <tr>
                            <th>Course Name</th>
                            <th>Code</th>
                            <th>Program</th>
                            <th>Credits</th>
                            <th>Duration</th>
                            <th>Assigned Teacher</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($courses_result && mysqli_num_rows($courses_result) > 0): ?>
                            <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
                                <tr>
                                    <td>
                                        <div class="course-name"><?php echo htmlspecialchars($course['course_name'] ?? ''); ?></div>
                                        <div class="course-code"><?php echo htmlspecialchars($course['course_code'] ?? ''); ?></div>
                                        <?php if (!empty($course['course_description'])): ?>
                                            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                                <?php echo htmlspecialchars(substr($course['course_description'] ?? '', 0, 100)) . (strlen($course['course_description'] ?? '') > 100 ? '...' : ''); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="course-code"><?php echo htmlspecialchars($course['course_code'] ?? ''); ?></span>
                                    </td>
                                    <td>
                                        <span class="course-program"><?php echo htmlspecialchars($course['program'] ?? ''); ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($course['credits'])): ?>
                                            <span class="course-credits"><?php echo htmlspecialchars($course['credits'] ?? ''); ?> credits</span>
                                        <?php else: ?>
                                            <span style="color: var(--text-secondary); font-style: italic;">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($course['duration'])): ?>
                                            <span class="course-duration"><?php echo htmlspecialchars($course['duration'] ?? ''); ?> weeks</span>
                                        <?php else: ?>
                                            <span style="color: var(--text-secondary); font-style: italic;">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($course['teacher_name']): ?>
                                            <div class="teacher-info">
                                                <div class="teacher-name"><?php echo htmlspecialchars($course['teacher_name'] ?? ''); ?></div>
                                                <div class="teacher-email"><?php echo htmlspecialchars($course['teacher_email'] ?? ''); ?></div>
                                            </div>
                                        <?php else: ?>
                                            <div class="no-teacher">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                No teacher assigned
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="add_courses.php?edit=<?php echo $course['id']; ?>" class="btn-edit">
                                                <i class="fas fa-edit me-1"></i>
                                                Edit
                                            </a>
                                            <?php if (!$course['teacher_id']): ?>
                                                <a href="add_teacher_auth.php?assign_course=<?php echo $course['id']; ?>" class="btn-assign">
                                                    <i class="fas fa-user-plus me-1"></i>
                                                    Assign Teacher
                                                </a>
                                            <?php endif; ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this course? This action cannot be undone.');">
                                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                <button type="submit" name="delete_course" class="btn-delete">
                                                    <i class="fas fa-trash me-1"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-courses">
                                    <i class="fas fa-book-open"></i>
                                    <h3>No Courses Found</h3>
                                    <p>Start by adding your first course to the system.</p>
                                    <a href="add_courses.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>
                                        Add First Course
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
        
        // Confirm delete action
        function confirmDelete(courseName) {
            return confirm(`Are you sure you want to delete "${courseName}"? This action cannot be undone.`);
        }

        function validateCourseSelection() {
            // Only validate if there are courses to select from
            if (document.querySelectorAll('.course-checkbox').length === 0) return true;
            const checked = document.querySelectorAll('.course-checkbox:checked');
            if (checked.length === 0) {
                alert('Please select at least one course for the teacher!');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>

<?php mysqli_close($data); ?> 