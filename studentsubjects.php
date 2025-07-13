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
    <title>My Subjects & Teachers - Student Portal</title>
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
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .subject-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            overflow: hidden;
            cursor: pointer;
            position: relative;
        }
        
        .subject-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .subject-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem;
            position: relative;
        }
        
        .subject-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .subject-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .subject-subtitle {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .subject-body {
            padding: 1.5rem;
        }
        
        .teachers-section {
            margin-bottom: 1.5rem;
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
        
        .subject-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        
        .btn-subject {
            flex: 1;
            border-radius: 8px;
            font-weight: 500;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-subject:hover {
            transform: translateY(-1px);
        }
        
        .btn-assignments {
            background: var(--success-color);
            color: white;
            border: none;
        }
        
        .btn-assignments:hover {
            background: #059669;
            color: white;
        }
        
        .btn-materials {
            background: var(--info-color);
            color: white;
            border: none;
        }
        
        .btn-materials:hover {
            background: #2563eb;
            color: white;
        }
        
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            text-align: center;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            margin: 0 auto 1rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #6b7280;
            font-weight: 500;
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
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .subjects-grid {
                grid-template-columns: 1fr;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .subject-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include 'studentsidebar.php'; ?>
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-book-open me-3"></i>
                My Subjects & Teachers
            </h1>
            <p class="page-subtitle">Click on any subject to view assignments and materials from your teachers</p>
        </div>

        <?php
        $conn = mysqli_connect("localhost", "root", "", "schoolproject");
        $student_username = $_SESSION['username'];
        $student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT grade_id, section FROM students WHERE username = '".mysqli_real_escape_string($conn, $student_username)."' LIMIT 1"));
        $grade_id = $student['grade_id'] ?? null;
        
        if ($grade_id) {
            // Get statistics
            $total_subjects = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(DISTINCT gsa.subject_id) FROM grade_subject_assignments gsa WHERE gsa.grade_id = $grade_id"))[0];
            $total_teachers = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(DISTINCT tgs.teacher_id) FROM teacher_grade_subjects tgs WHERE tgs.grade_id = $grade_id"))[0];
            
            // Get assignments count - join with grade_subject_assignments to filter by grade
            $total_assignments = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM teacher_assignments ta 
                JOIN courses c ON ta.course_id = c.id 
                JOIN subjects s ON c.course_name LIKE CONCAT('%', s.name, '%')
                JOIN grade_subject_assignments gsa ON s.id = gsa.subject_id 
                WHERE gsa.grade_id = $grade_id"))[0];
            
            // Get materials count - join with grade_subject_assignments to filter by grade
            $total_materials = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM materials m 
                JOIN courses c ON m.course_id = c.id 
                JOIN subjects s ON c.course_name LIKE CONCAT('%', s.name, '%')
                JOIN grade_subject_assignments gsa ON s.id = gsa.subject_id 
                WHERE gsa.grade_id = $grade_id"))[0];
            ?>
            
            <!-- Statistics Section -->
            <div class="stats-section">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_subjects; ?></div>
                    <div class="stat-label">Subjects</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_teachers; ?></div>
                    <div class="stat-label">Teachers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_assignments; ?></div>
                    <div class="stat-label">Assignments</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_materials; ?></div>
                    <div class="stat-label">Materials</div>
                </div>
            </div>

            <!-- Subjects Grid -->
            <div class="subjects-grid">
                <?php
                $subjects = mysqli_query($conn, "SELECT gsa.subject_id, s.name as subject_name FROM grade_subject_assignments gsa JOIN subjects s ON gsa.subject_id = s.id WHERE gsa.grade_id = $grade_id GROUP BY gsa.subject_id ORDER BY s.name");
                
                if ($subjects && mysqli_num_rows($subjects) > 0) {
                    while ($subj = mysqli_fetch_assoc($subjects)) {
                        $subject_id = $subj['subject_id'];
                        $subject_name = $subj['subject_name'];
                        
                        // Get teachers for this subject
                        $teachers = mysqli_query($conn, "SELECT t.name, t.email FROM teacher_grade_subjects tgs JOIN teacher t ON tgs.teacher_id = t.id WHERE tgs.grade_id = $grade_id AND tgs.subject_id = $subject_id");
                        
                        // Get assignment and material counts
                        $assignment_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM teacher_assignments ta JOIN courses c ON ta.course_id = c.id WHERE c.course_name LIKE '%".mysqli_real_escape_string($conn, $subject_name)."%'"))[0];
                        $material_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM materials m JOIN courses c ON m.course_id = c.id WHERE c.course_name LIKE '%".mysqli_real_escape_string($conn, $subject_name)."%'"))[0];
                        ?>
                        
                        <div class="subject-card" onclick="window.location.href='student_subject_detail.php?subject_id=<?php echo $subject_id; ?>&subject_name=<?php echo urlencode($subject_name); ?>'">
                            <div class="subject-header">
                                <div class="subject-icon">
                                    <i class="fas fa-book"></i>
                                </div>
                                <h3 class="subject-title"><?php echo htmlspecialchars($subject_name); ?></h3>
                                <p class="subject-subtitle">
                                    <?php echo $assignment_count; ?> assignments • <?php echo $material_count; ?> materials
                                </p>
                            </div>
                            
                            <div class="subject-body">
                                <div class="teachers-section">
                                    <h6 class="teachers-title">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                        Your Teachers
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
                                
                                <div class="subject-actions">
                                    <a href="student_subject_detail.php?subject_id=<?php echo $subject_id; ?>&subject_name=<?php echo urlencode($subject_name); ?>&tab=assignments" 
                                       class="btn btn-subject btn-assignments">
                                        <i class="fas fa-tasks"></i>
                                        Assignments (<?php echo $assignment_count; ?>)
                                    </a>
                                    <a href="student_subject_detail.php?subject_id=<?php echo $subject_id; ?>&subject_name=<?php echo urlencode($subject_name); ?>&tab=materials" 
                                       class="btn btn-subject btn-materials">
                                        <i class="fas fa-file-alt"></i>
                                        Materials (<?php echo $material_count; ?>)
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="empty-state">
                        <i class="fas fa-book-open"></i>
                        <h4>No Subjects Found</h4>
                        <p class="text-muted">No subjects have been assigned to your grade yet. Please contact your school administrator.</p>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        } else {
            ?>
            <div class="empty-state">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>Grade Not Assigned</h4>
                <p class="text-muted">Your grade information is not properly configured. Please contact your school administrator.</p>
            </div>
            <?php
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add click animation
        document.querySelectorAll('.subject-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Don't trigger if clicking on action buttons
                if (e.target.closest('.btn-subject')) {
                    e.stopPropagation();
                    return;
                }
                
                // Add click effect
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
    </script>
</body>
</html> 