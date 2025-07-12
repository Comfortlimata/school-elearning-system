<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$teacher_id = $_SESSION['teacher_id'];
$teacher = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM teacher WHERE id = $teacher_id"));

// Get subjects taught by this teacher
$subjects_query = "SELECT DISTINCT s.id, s.name as subject_name, 
                          g.name as grade_name, gsa.section
                   FROM subjects s 
                   JOIN teacher_grade_subjects tgs ON s.id = tgs.subject_id 
                   JOIN grades g ON tgs.grade_id = g.id 
                   JOIN grade_subject_assignments gsa ON gsa.grade_id = tgs.grade_id AND gsa.subject_id = tgs.subject_id 
                   WHERE tgs.teacher_id = $teacher_id 
                   ORDER BY g.name, gsa.section, s.name";

$subjects_result = mysqli_query($conn, $subjects_query);
$subjects = [];
while ($row = mysqli_fetch_assoc($subjects_result)) {
    $subjects[] = $row;
}

// Count total subjects
$total_subjects = count($subjects);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Subjects - Comfort e-School Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
        }
        .top-nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            z-index: 1000;
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 60px;
        }
        .top-nav-link {
            color: #444;
            text-decoration: none;
            font-size: 1.1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 10px;
            transition: color 0.2s;
        }
        .top-nav-link i {
            font-size: 1.3rem;
        }
        .top-nav-link.active, .top-nav-link:hover {
            color: #007bff;
        }
        .main-content {
            margin-top: 80px;
            padding: 2rem 1rem 1rem 1rem;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }
        .welcome-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        .stats-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
            justify-content: center;
        }
        .stat-icon.subjects {
            background: #e3f0ff;
            color: #007bff;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }
        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
            gap: 1.5rem;
        }
        .subject-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 1.5rem 1.2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 260px;
        }
        .subject-header {
            text-align: center;
            margin-bottom: 1rem;
        }
        .subject-icon {
            background: #f0e6ff;
            color: #7c3aed;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 0.5rem auto;
        }
        .subject-body {
            width: 100%;
        }
        .subject-info {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
            justify-content: center;
        }
        .info-item {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            min-width: 110px;
            text-align: center;
        }
        .info-label {
            font-size: 0.9rem;
            color: #888;
        }
        .info-value {
            font-weight: 600;
            font-size: 1.1rem;
        }
        @media (max-width: 600px) {
            .main-content {
                padding: 1rem 0.2rem 0.5rem 0.2rem;
            }
            .stats-card {
                flex-direction: column;
                gap: 0.5rem;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <a href="teacherhome.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherhome.php') echo ' active';?>"><i class="fas fa-home"></i><span>Home</span></a>
        <a href="teacherclasses.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherclasses.php') echo ' active';?>"><i class="fas fa-users-class"></i><span>Classes</span></a>
        <a href="teachersubjects.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teachersubjects.php') echo ' active';?>"><i class="fas fa-book"></i><span>Subjects</span></a>
        <a href="teacherassignments.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherassignments.php') echo ' active';?>"><i class="fas fa-tasks"></i><span>Assignments</span></a>
        <a href="teacherperformance.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherperformance.php') echo ' active';?>"><i class="fas fa-chart-line"></i><span>Performance</span></a>
        <a href="teacherschedule.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherschedule.php') echo ' active';?>"><i class="fas fa-calendar-alt"></i><span>Schedule</span></a>
        <a href="teachernotifications.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teachernotifications.php') echo ' active';?>"><i class="fas fa-bell"></i><span>Notifications</span></a>
        <a href="teacherprofile.php" class="top-nav-link<?php if(basename($_SERVER['PHP_SELF'])=='teacherprofile.php') echo ' active';?>"><i class="fas fa-user"></i><span>Profile</span></a>
    </nav>
    <div class="main-content">
        <div class="welcome-section">
            <h1 class="welcome-title mb-3" style="font-size:2.2rem; font-weight:700;">My Subjects</h1>
            <p class="welcome-subtitle mb-4" style="font-size:1.1rem; opacity:0.95;">Here are all the subjects you teach across different grades and sections.</p>
        </div>
        <div class="stats-card">
            <div class="stat-icon subjects"><i class="fas fa-book"></i></div>
            <div class="stat-number" style="font-size:2rem;font-weight:700;"><?php echo $total_subjects; ?></div>
            <div class="stat-label">Total Subjects</div>
        </div>
        <?php if ($subjects): ?>
            <div class="subjects-grid">
                <?php foreach ($subjects as $subject): ?>
                    <div class="subject-card">
                        <div class="subject-header">
                            <div class="subject-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <h4 class="mb-2"><?php echo htmlspecialchars($subject['subject_name']); ?></h4>
                            <p class="mb-0">Subject taught by <?php echo htmlspecialchars($teacher['name']); ?></p>
                        </div>
                        <div class="subject-body">
                            <div class="subject-info">
                                <div class="info-item">
                                    <div class="info-label">Grade Level</div>
                                    <div class="info-value"><?php echo htmlspecialchars($subject['grade_name']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Section</div>
                                    <div class="info-value"><?php echo htmlspecialchars($subject['section']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Subject Code</div>
                                    <div class="info-value"><?php echo htmlspecialchars($subject['id']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Subject ID</div>
                                    <div class="info-value"><?php echo htmlspecialchars($subject['id']); ?></div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="teacherclasses.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-users me-1"></i>View Class
                                </a>
                                <a href="teacherassignments.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-tasks me-1"></i>Assignments
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="subject-card">
                <div class="subject-header">
                    <div class="subject-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h4 class="mb-2">No Subjects Assigned</h4>
                    <p class="mb-0">You haven't been assigned to any subjects yet</p>
                </div>
                <div class="subject-body">
                    <div class="no-subjects">
                        <div class="no-subjects-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h4>No Subjects Found</h4>
                        <p class="text-muted">You haven't been assigned to teach any subjects yet. Please contact your school administrator to get subjects assigned to you.</p>
                        <a href="teacherprofile.php" class="btn btn-primary">
                            <i class="fas fa-user me-2"></i>Update Profile
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 