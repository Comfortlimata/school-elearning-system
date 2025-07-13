<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) { die("Database connection failed: " . mysqli_connect_error()); }

$student_username = $_SESSION['username'];
$student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT grade_id, section FROM students WHERE username = '".mysqli_real_escape_string($conn, $student_username)."' LIMIT 1"));
$grade_id = $student['grade_id'] ?? null;

// Get filter parameters
$filter_subject = $_GET['subject'] ?? '';
$filter_type = $_GET['type'] ?? '';
$filter_status = $_GET['status'] ?? '';
$search_query = $_GET['search'] ?? '';

// Get all subjects for this student
$subjects = [];
if ($grade_id) {
    $subjects_result = mysqli_query($conn, "SELECT DISTINCT s.id, s.name as subject_name FROM subjects s 
                                           JOIN grade_subject_assignments gsa ON s.id = gsa.subject_id 
                                           WHERE gsa.grade_id = $grade_id ORDER BY s.name");
    while ($row = mysqli_fetch_assoc($subjects_result)) {
        $subjects[] = $row;
    }
}

// Build query for assignments and materials
$where_conditions = [];
$params = [];

if ($grade_id) {
    $where_conditions[] = "s.grade_id = $grade_id";
}

if ($filter_subject) {
    $where_conditions[] = "s.id = " . intval($filter_subject);
}

if ($search_query) {
    $search_escaped = mysqli_real_escape_string($conn, $search_query);
    $where_conditions[] = "(ta.title LIKE '%$search_escaped%' OR m.title LIKE '%$search_escaped%' OR ta.description LIKE '%$search_escaped%' OR m.description LIKE '%$search_escaped%')";
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get assignments - include both course-specific and unassigned (NULL course_id)
$assignments_query = "SELECT ta.*, s.name as subject_name, t.name as teacher_name 
                      FROM teacher_assignments ta 
                      LEFT JOIN courses c ON ta.course_id = c.id 
                      LEFT JOIN subjects s ON c.course_name LIKE CONCAT('%', s.name, '%')
                      JOIN teacher t ON ta.created_by = t.id 
                      JOIN student_teacher_subject sts ON t.id = sts.teacher_id 
                      WHERE sts.student_id = (SELECT id FROM students WHERE username = '$student_username')
                      AND (ta.course_id IS NULL OR s.grade_id = $grade_id)
                      ORDER BY ta.due_date DESC";
$assignments = mysqli_query($conn, $assignments_query);

// Get materials - include both course-specific and unassigned (NULL course_id)
$materials_query = "SELECT m.*, s.name as subject_name, t.name as teacher_name 
                    FROM materials m 
                    LEFT JOIN courses c ON m.course_id = c.id 
                    LEFT JOIN subjects s ON c.course_name LIKE CONCAT('%', s.name, '%')
                    JOIN teacher t ON m.uploaded_by = t.id 
                    JOIN student_teacher_subject sts ON t.id = sts.teacher_id 
                    WHERE sts.student_id = (SELECT id FROM students WHERE username = '$student_username')
                    AND (m.course_id IS NULL OR s.grade_id = $grade_id)
                    ORDER BY m.created_at DESC";
$materials = mysqli_query($conn, $materials_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Assignments & Materials - Comfort e-School Academy</title>
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
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid var(--border-color);
        }
        
        .filter-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .filter-label {
            font-weight: 500;
            color: var(--dark-color);
            font-size: 0.9rem;
        }
        
        .filter-input {
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .filter-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }
        
        .filter-button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .filter-button:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .clear-button {
            background: var(--light-color);
            color: var(--dark-color);
            border: 2px solid var(--border-color);
        }
        
        .clear-button:hover {
            background: var(--danger-color);
            color: white;
            border-color: var(--danger-color);
        }
        
        .content-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            background: white;
            padding: 0.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .tab-button {
            flex: 1;
            padding: 1rem;
            border: none;
            background: transparent;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .tab-button.active {
            background: var(--primary-color);
            color: white;
        }
        
        .tab-button:hover:not(.active) {
            background: var(--light-color);
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        
        .item-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .item-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .item-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--light-color);
        }
        
        .item-type {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }
        
        .type-assignment {
            background: var(--warning-color);
            color: white;
        }
        
        .type-material {
            background: var(--success-color);
            color: white;
        }
        
        .item-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-color);
            margin: 0 0 0.5rem 0;
        }
        
        .item-subject {
            color: var(--primary-color);
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .item-body {
            padding: 1.25rem;
        }
        
        .item-description {
            color: var(--dark-color);
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        
        .item-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: var(--light-color);
            border-radius: 8px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--dark-color);
        }
        
        .item-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-button {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-download {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-download:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-1px);
        }
        
        .btn-view {
            background: var(--light-color);
            color: var(--dark-color);
            border: 2px solid var(--border-color);
        }
        
        .btn-view:hover {
            background: var(--info-color);
            color: white;
            border-color: var(--info-color);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--dark-color);
        }
        
        .empty-icon {
            font-size: 4rem;
            color: var(--border-color);
            margin-bottom: 1rem;
        }
        
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: var(--dark-color);
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .content-section {
                padding: 1rem;
            }
            
            .filter-row {
                grid-template-columns: 1fr;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .page-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="header-section">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="studenthome.php" class="back-button">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-book-open me-3"></i>
                    My Assignments & Materials
                </h1>
                <p class="page-subtitle">Access all your course materials and assignments in one place</p>
            </div>
        </div>

        <div class="content-section">
            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-title">
                    <i class="fas fa-filter"></i>
                    Filter & Search
                </div>
                <form method="GET" class="filter-row">
                    <div class="filter-group">
                        <label class="filter-label">Subject</label>
                        <select name="subject" class="filter-input">
                            <option value="">All Subjects</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>" <?php echo $filter_subject == $subject['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Type</label>
                        <select name="type" class="filter-input">
                            <option value="">All Types</option>
                            <option value="assignment" <?php echo $filter_type == 'assignment' ? 'selected' : ''; ?>>Assignments</option>
                            <option value="material" <?php echo $filter_type == 'material' ? 'selected' : ''; ?>>Materials</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Search</label>
                        <input type="text" name="search" class="filter-input" placeholder="Search by title or description..." value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="filter-button">
                                <i class="fas fa-search me-2"></i>
                                Apply Filters
                            </button>
                            <a href="student_subject_materials.php" class="filter-button clear-button">
                                <i class="fas fa-times me-2"></i>
                                Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Stats Section -->
            <div class="stats-section">
                <div class="stat-card">
                    <div class="stat-number"><?php echo mysqli_num_rows($assignments); ?></div>
                    <div class="stat-label">Total Assignments</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo mysqli_num_rows($materials); ?></div>
                    <div class="stat-label">Total Materials</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($subjects); ?></div>
                    <div class="stat-label">Subjects</div>
                </div>
            </div>

            <!-- Content Tabs -->
            <div class="content-tabs">
                <button class="tab-button active" onclick="showTab('all')">
                    <i class="fas fa-th-large me-2"></i>
                    All Items
                </button>
                <button class="tab-button" onclick="showTab('assignments')">
                    <i class="fas fa-tasks me-2"></i>
                    Assignments
                </button>
                <button class="tab-button" onclick="showTab('materials')">
                    <i class="fas fa-file me-2"></i>
                    Materials
                </button>
            </div>

            <!-- All Items Tab -->
            <div id="all-tab" class="tab-content">
                <div class="content-grid">
                    <?php 
                    $has_items = false;
                    
                    // Display Assignments
                    if ($assignments && mysqli_num_rows($assignments) > 0):
                        while ($assignment = mysqli_fetch_assoc($assignments)):
                            $has_items = true;
                    ?>
                        <div class="item-card assignment-item">
                            <div class="item-header">
                                <span class="item-type type-assignment">Assignment</span>
                                <h3 class="item-title"><?php echo htmlspecialchars($assignment['title']); ?></h3>
                                <div class="item-subject">
                                    <i class="fas fa-book me-1"></i>
                                    <?php echo htmlspecialchars($assignment['subject_name']); ?>
                                </div>
                            </div>
                            <div class="item-body">
                                <p class="item-description"><?php echo htmlspecialchars($assignment['description']); ?></p>
                                <div class="item-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>Due: <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                        <span><?php echo htmlspecialchars($assignment['teacher_name']); ?></span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <?php if (!empty($assignment['file_path'])): ?>
                                        <a href="<?php echo htmlspecialchars($assignment['file_path']); ?>" download class="action-button btn-download">
                                            <i class="fas fa-download"></i>
                                            Download
                                        </a>
                                    <?php endif; ?>
                                    <button class="action-button btn-view" onclick="viewDetails('assignment', <?php echo $assignment['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    endif;
                    
                    // Display Materials
                    if ($materials && mysqli_num_rows($materials) > 0):
                        while ($material = mysqli_fetch_assoc($materials)):
                            $has_items = true;
                    ?>
                        <div class="item-card material-item">
                            <div class="item-header">
                                <span class="item-type type-material">Material</span>
                                <h3 class="item-title"><?php echo htmlspecialchars($material['title']); ?></h3>
                                <div class="item-subject">
                                    <i class="fas fa-book me-1"></i>
                                    <?php echo htmlspecialchars($material['subject_name']); ?>
                                </div>
                            </div>
                            <div class="item-body">
                                <p class="item-description"><?php echo htmlspecialchars($material['description']); ?></p>
                                <div class="item-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>Uploaded: <?php echo date('M d, Y', strtotime($material['created_at'])); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                        <span><?php echo htmlspecialchars($material['teacher_name']); ?></span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="<?php echo htmlspecialchars($material['file_path']); ?>" download class="action-button btn-download">
                                        <i class="fas fa-download"></i>
                                        Download
                                    </a>
                                    <button class="action-button btn-view" onclick="viewDetails('material', <?php echo $material['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    endif;
                    
                    if (!$has_items):
                    ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <h3>No Items Found</h3>
                            <p class="text-muted">No assignments or materials match your current filters.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Assignments Tab -->
            <div id="assignments-tab" class="tab-content" style="display: none;">
                <div class="content-grid">
                    <?php 
                    $has_assignments = false;
                    if ($assignments && mysqli_num_rows($assignments) > 0):
                        mysqli_data_seek($assignments, 0);
                        while ($assignment = mysqli_fetch_assoc($assignments)):
                            $has_assignments = true;
                    ?>
                        <div class="item-card assignment-item">
                            <div class="item-header">
                                <span class="item-type type-assignment">Assignment</span>
                                <h3 class="item-title"><?php echo htmlspecialchars($assignment['title']); ?></h3>
                                <div class="item-subject">
                                    <i class="fas fa-book me-1"></i>
                                    <?php echo htmlspecialchars($assignment['subject_name']); ?>
                                </div>
                            </div>
                            <div class="item-body">
                                <p class="item-description"><?php echo htmlspecialchars($assignment['description']); ?></p>
                                <div class="item-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>Due: <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                        <span><?php echo htmlspecialchars($assignment['teacher_name']); ?></span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <?php if (!empty($assignment['file_path'])): ?>
                                        <a href="<?php echo htmlspecialchars($assignment['file_path']); ?>" download class="action-button btn-download">
                                            <i class="fas fa-download"></i>
                                            Download
                                        </a>
                                    <?php endif; ?>
                                    <button class="action-button btn-view" onclick="viewDetails('assignment', <?php echo $assignment['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    endif;
                    
                    if (!$has_assignments):
                    ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <h3>No Assignments</h3>
                            <p class="text-muted">No assignments have been posted yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Materials Tab -->
            <div id="materials-tab" class="tab-content" style="display: none;">
                <div class="content-grid">
                    <?php 
                    $has_materials = false;
                    if ($materials && mysqli_num_rows($materials) > 0):
                        mysqli_data_seek($materials, 0);
                        while ($material = mysqli_fetch_assoc($materials)):
                            $has_materials = true;
                    ?>
                        <div class="item-card material-item">
                            <div class="item-header">
                                <span class="item-type type-material">Material</span>
                                <h3 class="item-title"><?php echo htmlspecialchars($material['title']); ?></h3>
                                <div class="item-subject">
                                    <i class="fas fa-book me-1"></i>
                                    <?php echo htmlspecialchars($material['subject_name']); ?>
                                </div>
                            </div>
                            <div class="item-body">
                                <p class="item-description"><?php echo htmlspecialchars($material['description']); ?></p>
                                <div class="item-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>Uploaded: <?php echo date('M d, Y', strtotime($material['created_at'])); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                        <span><?php echo htmlspecialchars($material['teacher_name']); ?></span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="<?php echo htmlspecialchars($material['file_path']); ?>" download class="action-button btn-download">
                                        <i class="fas fa-download"></i>
                                        Download
                                    </a>
                                    <button class="action-button btn-view" onclick="viewDetails('material', <?php echo $material['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    endif;
                    
                    if (!$has_materials):
                    ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-file"></i>
                            </div>
                            <h3>No Materials</h3>
                            <p class="text-muted">No materials have been uploaded yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.style.display = 'none');
            
            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => button.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').style.display = 'block';
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
        
        function viewDetails(type, id) {
            // You can implement a modal or redirect to a details page
            alert('View details for ' + type + ' with ID: ' + id);
        }
        
        // Auto-submit form when filters change
        document.querySelectorAll('.filter-input').forEach(input => {
            input.addEventListener('change', function() {
                if (this.name !== 'search') {
                    this.form.submit();
                }
            });
        });
    </script>
</body>
</html> 