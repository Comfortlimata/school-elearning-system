<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) { 
    die("Database connection failed: " . mysqli_connect_error()); 
}

$student_username = $_SESSION['username'];
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$subject_name = isset($_GET['subject_name']) ? $_GET['subject_name'] : '';
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'assignments';

// Get student info
$student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT grade_id, section FROM students WHERE username = '".mysqli_real_escape_string($conn, $student_username)."' LIMIT 1"));
$grade_id = $student['grade_id'] ?? null;

// Get subject details
$subject_query = "SELECT s.*, g.name as grade_name FROM subjects s 
                  JOIN grade_subject_assignments gsa ON s.id = gsa.subject_id 
                  JOIN grades g ON gsa.grade_id = g.id 
                  WHERE s.id = $subject_id AND gsa.grade_id = $grade_id";
$subject_result = mysqli_query($conn, $subject_query);
$subject = mysqli_fetch_assoc($subject_result);

if (!$subject) {
    header("Location: studentsubjects.php");
    exit();
}

// Get teachers for this subject
$teachers_query = "SELECT t.name, t.email FROM teacher_grade_subjects tgs 
                   JOIN teacher t ON tgs.teacher_id = t.id 
                   WHERE tgs.grade_id = $grade_id AND tgs.subject_id = $subject_id";
$teachers = mysqli_query($conn, $teachers_query);

// Get course for this subject
$course = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM courses WHERE course_name LIKE '%".mysqli_real_escape_string($conn, $subject['name'])."%' LIMIT 1"));
$course_id = $course['id'] ?? null;

// Get assignments
if ($course_id) {
    $assignments_query = "SELECT ta.*, t.name as teacher_name 
                          FROM teacher_assignments ta 
                          JOIN teacher t ON ta.created_by = t.id 
                          JOIN student_teacher_subject sts ON t.id = sts.teacher_id 
                          WHERE sts.student_id = (SELECT id FROM students WHERE username = '".mysqli_real_escape_string($conn, $student_username)."')
                          AND (ta.course_id IS NULL OR ta.course_id = $course_id)
                          ORDER BY ta.due_date DESC";
} else {
    $assignments_query = "SELECT ta.*, t.name as teacher_name 
                          FROM teacher_assignments ta 
                          JOIN teacher t ON ta.created_by = t.id 
                          JOIN student_teacher_subject sts ON t.id = sts.teacher_id 
                          WHERE sts.student_id = (SELECT id FROM students WHERE username = '".mysqli_real_escape_string($conn, $student_username)."')
                          AND ta.course_id IS NULL
                          ORDER BY ta.due_date DESC";
}
$assignments = mysqli_query($conn, $assignments_query);

// Get materials
if ($course_id) {
    $materials_query = "SELECT m.*, t.name as teacher_name 
                        FROM materials m 
                        JOIN teacher t ON m.uploaded_by = t.id 
                        JOIN student_teacher_subject sts ON t.id = sts.teacher_id 
                        WHERE sts.student_id = (SELECT id FROM students WHERE username = '".mysqli_real_escape_string($conn, $student_username)."')
                        AND (m.course_id IS NULL OR m.course_id = $course_id)
                        ORDER BY m.created_at DESC";
} else {
    $materials_query = "SELECT m.*, t.name as teacher_name 
                        FROM materials m 
                        JOIN teacher t ON m.uploaded_by = t.id 
                        JOIN student_teacher_subject sts ON t.id = sts.teacher_id 
                        WHERE sts.student_id = (SELECT id FROM students WHERE username = '".mysqli_real_escape_string($conn, $student_username)."')
                        AND m.course_id IS NULL
                        ORDER BY m.created_at DESC";
}
$materials = mysqli_query($conn, $materials_query);

// Count totals
$assignment_count = mysqli_num_rows($assignments);
$material_count = mysqli_num_rows($materials);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($subject['name']); ?> - Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --light-color: #f8fafc;
            --border-color: #e5e7eb;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        * { font-family: 'Poppins', sans-serif; }
        body { 
            background: var(--light-color); 
            color: #374151;
        }
        .main-content { 
            margin-left: 280px; 
            padding: 2rem; 
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
        }
        
        .breadcrumb-nav {
            margin-bottom: 1rem;
        }
        
        .breadcrumb-nav a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .breadcrumb-nav a:hover {
            color: white;
        }
        
        .subject-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .subject-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }
        
        .subject-stats {
            display: flex;
            gap: 2rem;
            margin-top: 1rem;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .tabs-container {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }
        
        .tabs-header {
            display: flex;
            border-bottom: 1px solid var(--border-color);
        }
        
        .tab-button {
            flex: 1;
            padding: 1rem 1.5rem;
            background: none;
            border: none;
            font-weight: 500;
            color: #6b7280;
            transition: all 0.2s;
            position: relative;
        }
        
        .tab-button.active {
            color: var(--primary-color);
            background: #f8fafc;
        }
        
        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-color);
        }
        
        .tab-content {
            padding: 2rem;
        }
        
        .content-grid {
            display: grid;
            gap: 1rem;
        }
        
        .content-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            transition: all 0.2s;
        }
        
        .content-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .content-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .content-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.5rem;
        }
        
        .content-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 1rem;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .content-description {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .content-actions {
            display: flex;
            gap: 0.75rem;
        }
        
        .btn-download {
            background: var(--success-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        
        .btn-download:hover {
            background: #059669;
            color: white;
            transform: translateY(-1px);
        }
        
        .btn-view {
            background: var(--info-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        
        .btn-view:hover {
            background: #2563eb;
            color: white;
            transform: translateY(-1px);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }
        
        .teachers-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }
        
        .teachers-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .teacher-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            border-left: 3px solid var(--primary-color);
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
            font-size: 1rem;
        }
        
        .teacher-info h6 {
            margin: 0;
            font-weight: 600;
            color: #374151;
        }
        
        .teacher-info small {
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .due-date {
            color: var(--danger-color);
            font-weight: 500;
        }
        
        .due-date.past {
            color: var(--warning-color);
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .subject-title {
                font-size: 2rem;
            }
            
            .subject-stats {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .tabs-header {
                flex-direction: column;
            }
            
            .tab-button {
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <?php include 'studentsidebar.php'; ?>
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="breadcrumb-nav">
                <a href="studentsubjects.php"><i class="fas fa-arrow-left me-2"></i>Back to Subjects</a>
            </div>
            <h1 class="subject-title">
                <i class="fas fa-book me-3"></i>
                <?php echo htmlspecialchars($subject['name']); ?>
            </h1>
            <p class="subject-subtitle">
                Grade <?php echo htmlspecialchars($subject['grade_name']); ?> • 
                <?php echo $assignment_count; ?> assignments • 
                <?php echo $material_count; ?> materials
            </p>
            <div class="subject-stats">
                <div class="stat-item">
                    <i class="fas fa-calendar"></i>
                    <span>Due dates and schedules</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-download"></i>
                    <span>Download materials</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-bell"></i>
                    <span>Get notifications</span>
                </div>
            </div>
        </div>

        <!-- Teachers Section -->
        <div class="teachers-section">
            <h6 class="teachers-title">
                <i class="fas fa-chalkboard-teacher"></i>
                Your Teachers for <?php echo htmlspecialchars($subject['name']); ?>
            </h6>
            
            <?php if ($teachers && mysqli_num_rows($teachers) > 0): ?>
                <?php while ($t = mysqli_fetch_assoc($teachers)): ?>
                    <div class="teacher-item">
                        <div class="teacher-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="teacher-info">
                            <h6><?php echo htmlspecialchars($t['name']); ?></h6>
                            <small><?php echo htmlspecialchars($t['email']); ?></small>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="teacher-item">
                    <div class="teacher-avatar" style="background: #ef4444;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="teacher-info">
                        <h6>No Teacher Assigned</h6>
                        <small>Please contact administration</small>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tabs Container -->
        <div class="tabs-container">
            <div class="tabs-header">
                <button class="tab-button <?php echo $active_tab === 'assignments' ? 'active' : ''; ?>" 
                        onclick="switchTab('assignments')">
                    <i class="fas fa-tasks me-2"></i>
                    Assignments (<?php echo $assignment_count; ?>)
                </button>
                <button class="tab-button <?php echo $active_tab === 'materials' ? 'active' : ''; ?>" 
                        onclick="switchTab('materials')">
                    <i class="fas fa-file-alt me-2"></i>
                    Materials (<?php echo $material_count; ?>)
                </button>
            </div>
            
            <div class="tab-content">
                <!-- Assignments Tab -->
                <div id="assignments-tab" class="tab-pane <?php echo $active_tab === 'assignments' ? 'active' : ''; ?>">
                    <?php if ($assignments && mysqli_num_rows($assignments) > 0): ?>
                        <div class="content-grid">
                            <?php while ($assignment = mysqli_fetch_assoc($assignments)): ?>
                                <div class="content-card">
                                    <div class="content-header">
                                        <div class="flex-grow-1">
                                            <h5 class="content-title"><?php echo htmlspecialchars($assignment['title']); ?></h5>
                                            <div class="content-meta">
                                                <div class="meta-item">
                                                    <i class="fas fa-user"></i>
                                                    <span><?php echo htmlspecialchars($assignment['teacher_name']); ?></span>
                                                </div>
                                                <div class="meta-item">
                                                    <i class="fas fa-calendar"></i>
                                                    <span>Posted: <?php echo date('M d, Y', strtotime($assignment['created_at'])); ?></span>
                                                </div>
                                                <?php if ($assignment['due_date']): ?>
                                                    <div class="meta-item">
                                                        <i class="fas fa-clock"></i>
                                                        <span class="due-date <?php echo strtotime($assignment['due_date']) < time() ? 'past' : ''; ?>">
                                                            Due: <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="content-description">
                                        <?php echo nl2br(htmlspecialchars($assignment['description'])); ?>
                                    </div>
                                    
                                    <div class="content-actions">
                                        <?php if ($assignment['file_path']): 
                                            $file_path = $assignment['file_path'];
                                            $file_exists = file_exists($file_path);
                                        ?>
                                            <a href="download_file.php?file=<?php echo urlencode($file_path); ?>" 
                                               class="btn-download"
                                               onclick="return checkFileExists('<?php echo $file_path; ?>', <?php echo $file_exists ? 'true' : 'false'; ?>);">
                                                <i class="fas fa-download"></i>
                                                Download Assignment
                                                <?php if (!$file_exists): ?>
                                                    <small class="text-warning">(File not found)</small>
                                                <?php endif; ?>
                                            </a>
                                            <a href="<?php echo $file_path; ?>" 
                                               class="btn-view" target="_blank"
                                               onclick="return checkFileExists('<?php echo $file_path; ?>', <?php echo $file_exists ? 'true' : 'false'; ?>);">
                                                <i class="fas fa-eye"></i>
                                                View Assignment
                                            </a>
                                            <?php if (!$file_exists): ?>
                                                <small class="text-danger d-block mt-1">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    File path: <?php echo htmlspecialchars($file_path); ?>
                                                </small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-tasks"></i>
                            <h4>No Assignments Yet</h4>
                            <p class="text-muted">Your teachers haven't posted any assignments for this subject yet. Check back later!</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Materials Tab -->
                <div id="materials-tab" class="tab-pane <?php echo $active_tab === 'materials' ? 'active' : ''; ?>">
                    <?php if ($materials && mysqli_num_rows($materials) > 0): ?>
                        <div class="content-grid">
                            <?php while ($material = mysqli_fetch_assoc($materials)): ?>
                                <div class="content-card">
                                    <div class="content-header">
                                        <div class="flex-grow-1">
                                            <h5 class="content-title"><?php echo htmlspecialchars($material['title']); ?></h5>
                                            <div class="content-meta">
                                                <div class="meta-item">
                                                    <i class="fas fa-user"></i>
                                                    <span><?php echo htmlspecialchars($material['teacher_name']); ?></span>
                                                </div>
                                                <div class="meta-item">
                                                    <i class="fas fa-calendar"></i>
                                                    <span>Posted: <?php echo date('M d, Y', strtotime($material['created_at'])); ?></span>
                                                </div>
                                                <div class="meta-item">
                                                    <i class="fas fa-file"></i>
                                                    <span><?php echo pathinfo($material['file_path'], PATHINFO_EXTENSION); ?> file</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="content-description">
                                        <?php echo nl2br(htmlspecialchars($material['description'])); ?>
                                    </div>
                                    
                                    <div class="content-actions">
                                        <?php 
                                        // Debug: Check if file exists
                                        $file_path = $material['file_path'];
                                        $file_exists = file_exists($file_path);
                                        ?>
                                        <a href="download_file.php?file=<?php echo urlencode($file_path); ?>" 
                                           class="btn-download"
                                           onclick="return checkFileExists('<?php echo $file_path; ?>', <?php echo $file_exists ? 'true' : 'false'; ?>);">
                                            <i class="fas fa-download"></i>
                                            Download Material
                                            <?php if (!$file_exists): ?>
                                                <small class="text-warning">(File not found)</small>
                                            <?php endif; ?>
                                        </a>
                                        <a href="<?php echo $file_path; ?>" 
                                           class="btn-view" target="_blank"
                                           onclick="return checkFileExists('<?php echo $file_path; ?>', <?php echo $file_exists ? 'true' : 'false'; ?>);">
                                            <i class="fas fa-eye"></i>
                                            View Material
                                        </a>
                                        <?php if (!$file_exists): ?>
                                            <small class="text-danger d-block mt-1">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                File path: <?php echo htmlspecialchars($file_path); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-file-alt"></i>
                            <h4>No Materials Yet</h4>
                            <p class="text-muted">Your teachers haven't uploaded any materials for this subject yet. Check back later!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function switchTab(tabName) {
            // Update URL without page reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.pushState({}, '', url);
            
            // Update active tab button
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Show/hide tab content
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            document.getElementById(tabName + '-tab').classList.add('active');
        }
        
        // Initialize active tab
        document.addEventListener('DOMContentLoaded', function() {
            const activeTab = '<?php echo $active_tab; ?>';
            if (activeTab) {
                document.getElementById(activeTab + '-tab').classList.add('active');
            }
        });

        function checkFileExists(url, exists) {
            if (!exists) {
                alert('File not found at: ' + url);
                return false; // Prevent download/view
            }
            return true; // Allow download/view
        }
    </script>
</body>
</html> 