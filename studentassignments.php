<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assignments & Materials - Comfort e-School Academy</title>
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
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --border-color: #e5e7eb;
        }
        * { font-family: 'Poppins', sans-serif; }
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .page-container {
            min-height: 100vh;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        
        .header-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .back-button {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
        }
        
        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            text-align: center;
        }
        
        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            text-align: center;
            margin-top: 0.5rem;
        }
        
        .content-section {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .subject-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            height: fit-content;
        }
        
        .subject-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .subject-header {
            background: linear-gradient(135deg, var(--info-color), var(--primary-color));
            color: white;
            padding: 1rem 1.25rem;
            position: relative;
        }
        
        .subject-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .subject-title {
            position: relative;
            z-index: 2;
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .subject-body {
            padding: 1.25rem;
        }
        
        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding-bottom: 0.25rem;
            border-bottom: 1px solid var(--light-color);
        }
        
        .assignment-item {
            background: var(--light-color);
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            border-left: 3px solid var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .assignment-item:hover {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transform: translateX(3px);
        }
        
        .assignment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.25rem;
        }
        
        .assignment-title {
            font-weight: 600;
            color: var(--dark-color);
            margin: 0;
            font-size: 0.95rem;
        }
        
        .assignment-due {
            font-size: 0.8rem;
            color: var(--text-secondary);
            background: var(--light-color);
            padding: 0.2rem 0.5rem;
            border-radius: 15px;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-submitted { background: var(--success-color); color: white; }
        .status-overdue { background: var(--danger-color); color: white; }
        .status-pending { background: var(--warning-color); color: white; }
        
        .grade-badge {
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 0.5rem;
            display: inline-block;
        }
        
        .material-item {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .material-item:hover {
            border-color: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .material-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .material-icon {
            width: 32px;
            height: 32px;
            background: var(--light-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 0.9rem;
        }
        
        .download-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .download-btn:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }
        
        .empty-icon {
            font-size: 4rem;
            color: var(--border-color);
            margin-bottom: 1rem;
        }
        
        .no-subjects {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        
        .no-subjects-icon {
            font-size: 4rem;
            color: var(--border-color);
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .page-title { font-size: 1.8rem; }
            .page-subtitle { font-size: 1rem; }
            .content-section { padding: 1rem; }
            .subject-body { padding: 1rem; }
            .subjects-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .assignment-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            .material-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            .download-btn {
                align-self: stretch;
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .page-title { font-size: 1.5rem; }
            .header-section { padding: 1.5rem 0; }
            .subject-title { font-size: 1rem; }
            .assignment-title { font-size: 0.9rem; }
            .material-info h5 { font-size: 0.9rem; }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Header Section -->
        <div class="header-section">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="studenthome.php" class="back-button">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                    <div class="student-info text-end">
                        <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                        <small class="opacity-75">Student</small>
                    </div>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-tasks me-3"></i>Assignments & Materials
                </h1>
                <p class="page-subtitle">View and manage your academic assignments and learning materials</p>
            </div>
        </div>
        
        <!-- Content Section -->
        <div class="content-section">
            <?php
            $conn = mysqli_connect("localhost", "root", "", "schoolproject");
            $student_username = $_SESSION['username'];
            $student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT grade_id, section FROM students WHERE username = '".mysqli_real_escape_string($conn, $student_username)."' LIMIT 1"));
            $grade_id = $student['grade_id'] ?? null;
            
            if ($grade_id) {
                $subjects = mysqli_query($conn, "SELECT gsa.subject_id, s.name as subject_name FROM grade_subject_assignments gsa JOIN subjects s ON gsa.subject_id = s.id WHERE gsa.grade_id = $grade_id GROUP BY gsa.subject_id ORDER BY s.name");
                
                if ($subjects && mysqli_num_rows($subjects) > 0) {
                    echo '<div class="subjects-grid">';
                    while ($subj = mysqli_fetch_assoc($subjects)) {
                        $subject_id = $subj['subject_id'];
                        $subject_name = $subj['subject_name'];
                        $course = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM courses WHERE course_name LIKE '%".mysqli_real_escape_string($conn, $subject_name)."%' LIMIT 1"));
                        $course_id = $course['id'] ?? null;
                        ?>
                        
                        <div class="subject-card">
                            <div class="subject-header">
                                <h2 class="subject-title">
                                    <i class="fas fa-book"></i>
                                    <?php echo htmlspecialchars($subject_name); ?>
                                </h2>
                            </div>
                            <div class="subject-body">
                                <?php if ($course_id): ?>
                                    <!-- Assignments Section -->
                                    <?php
                                    $assignments = mysqli_query($conn, "SELECT * FROM course_assignments WHERE course_id = $course_id ORDER BY due_date DESC");
                                    if ($assignments && mysqli_num_rows($assignments) > 0):
                                    ?>
                                        <h3 class="section-title">
                                            <i class="fas fa-tasks"></i>
                                            Assignments
                                        </h3>
                                        <?php while ($a = mysqli_fetch_assoc($assignments)): 
                                            $submission = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM assignment_submissions WHERE assignment_id = {$a['id']} AND student_id = (SELECT id FROM students WHERE username = '".mysqli_real_escape_string($conn, $student_username)."')"));
                                            $status = $submission ? 'submitted' : (strtotime($a['due_date']) < time() ? 'overdue' : 'pending');
                                            $grade = $submission['grade'] ?? null;
                                        ?>
                                            <div class="assignment-item">
                                                <div class="assignment-header">
                                                    <h4 class="assignment-title"><?php echo htmlspecialchars($a['title']); ?></h4>
                                                    <span class="status-badge status-<?php echo $status; ?>">
                                                        <?php echo ucfirst($status); ?>
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="assignment-due">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        Due: <?php echo date('M j, Y', strtotime($a['due_date'])); ?>
                                                    </span>
                                                    <?php if ($grade): ?>
                                                        <span class="grade-badge">
                                                            <i class="fas fa-star me-1"></i>
                                                            Grade: <?php echo htmlspecialchars($grade); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-tasks"></i>
                                            </div>
                                            <h5>No Assignments</h5>
                                            <p class="text-muted">No assignments have been posted for this subject yet.</p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Materials Section -->
                                    <?php
                                    $materials = mysqli_query($conn, "SELECT * FROM course_materials WHERE course_id = $course_id ORDER BY created_at DESC");
                                    if ($materials && mysqli_num_rows($materials) > 0):
                                    ?>
                                        <h3 class="section-title mt-4">
                                            <i class="fas fa-file-alt"></i>
                                            Learning Materials
                                        </h3>
                                        <?php while ($m = mysqli_fetch_assoc($materials)): ?>
                                            <div class="material-item">
                                                <div class="material-info">
                                                    <div class="material-icon">
                                                        <i class="fas fa-file"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="mb-1"><?php echo htmlspecialchars($m['title']); ?></h5>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock me-1"></i>
                                                            Added: <?php echo date('M j, Y', strtotime($m['created_at'])); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                                <a href="<?php echo htmlspecialchars($m['file_path']); ?>" download class="download-btn">
                                                    <i class="fas fa-download me-1"></i>
                                                    Download
                                                </a>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="empty-state mt-4">
                                            <div class="empty-icon">
                                                <i class="fas fa-file-alt"></i>
                                            </div>
                                            <h5>No Materials</h5>
                                            <p class="text-muted">No learning materials have been uploaded for this subject yet.</p>
                                        </div>
                                    <?php endif; ?>
                                    
                                <?php else: ?>
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <h5>Course Not Found</h5>
                                        <p class="text-muted">No course has been set up for this subject yet.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php
                    }
                    echo '</div>'; // Close subjects-grid
                } else {
                    ?>
                    <div class="no-subjects">
                        <div class="no-subjects-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h4>No Subjects Found</h4>
                        <p class="text-muted">No subjects have been assigned to your grade level yet. Please contact your administrator.</p>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div class="no-subjects">
                    <div class="no-subjects-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h4>Grade Not Assigned</h4>
                    <p class="text-muted">Your grade level has not been assigned yet. Please contact your administrator to get your grade level set up.</p>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 