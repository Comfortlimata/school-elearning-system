<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'teacher') {
    header("Location: login.php");
    exit();
}
$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) { die("Database connection failed: " . mysqli_connect_error()); }
$teacher_id = $_SESSION['teacher_id'];

// Handle unassigned assignment form
if (isset($_POST['add_assignment_unassigned'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    $total_points = intval($_POST['total_points']);
    $file_path = '';
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/assignments/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = time() . '_' . basename($_FILES['assignment_file']['name']);
        $target = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $target)) {
            $file_path = $target;
        }
    }
    $sql = "INSERT INTO teacher_assignments (course_id, title, description, due_date, total_points, file_path, created_by) VALUES (NULL, '$title', '$description', '$due_date', $total_points, '$file_path', $teacher_id)";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success_message'] = 'Unassigned assignment added successfully!';
    } else {
        $_SESSION['error_message'] = 'Failed to add unassigned assignment.';
    }
    header('Location: teacherassignments.php');
    exit();
}
// Handle unassigned material form
if (isset($_POST['add_material_unassigned'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $file_path = '';
    if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/materials/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = time() . '_' . basename($_FILES['material_file']['name']);
        $target = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['material_file']['tmp_name'], $target)) {
            $file_path = $target;
        }
    }
    $sql = "INSERT INTO materials (course_id, title, description, file_path, uploaded_by) VALUES (NULL, '$title', '$description', '$file_path', $teacher_id)";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success_message'] = 'Unassigned material added successfully!';
    } else {
        $_SESSION['error_message'] = 'Failed to add unassigned material.';
    }
    header('Location: teacherassignments.php');
    exit();
}
// Get all classes (grade, section, subject) assigned to this teacher
$classes = mysqli_query($conn, "SELECT tgs.grade_id, g.name as grade_name, gsa.section, tgs.subject_id, s.name as subject_name, gsa.id as gsa_id FROM teacher_grade_subjects tgs JOIN grades g ON tgs.grade_id = g.id JOIN subjects s ON tgs.subject_id = s.id JOIN grade_subject_assignments gsa ON gsa.grade_id = tgs.grade_id AND gsa.subject_id = tgs.subject_id WHERE tgs.teacher_id = $teacher_id ORDER BY g.name, gsa.section, s.name");
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
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }
        .assignment-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 1.5rem 1.2rem;
            margin-bottom: 2rem;
        }
        .assignment-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .assignment-icon {
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
        .material-icon {
            background: #f0e6ff;
            color: #7c3aed;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }
        .card-header.bg-primary {
            border-radius: 1rem 1rem 0 0;
        }
        @media (max-width: 600px) {
            .main-content {
                padding: 1rem 0.2rem 0.5rem 0.2rem;
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
        <h2 class="mb-4"><i class="fas fa-tasks me-2"></i>Assignments & Materials</h2>
        <div class="row">
        <?php if ($classes && mysqli_num_rows($classes) > 0):
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                unset($_SESSION['error_message']);
            }
            while ($class = mysqli_fetch_assoc($classes)):
                $grade = htmlspecialchars($class['grade_name']);
                $section = htmlspecialchars($class['section']);
                $subject = htmlspecialchars($class['subject_name']);
                $gsa_id = $class['gsa_id'];
                // Fix: Remove 'program' from WHERE clause, match only by course_name
                $course = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM courses WHERE course_name LIKE '%$subject%' LIMIT 1"));
                $course_id = $course['id'] ?? 0;
                $class_key = $grade . $section . $subject . $gsa_id;
        ?>
            <div class="col-12 mb-4">
                <div class="assignment-card">
                    <div class="assignment-header">
                        <div class="assignment-icon"><i class="fas fa-users-class"></i></div>
                        <strong><?php echo "$grade$section - $subject"; ?></strong>
                        <div class="ms-auto">
                            <button class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addAssignmentUnassignedModal"><i class="fas fa-plus me-1"></i> Add Assignment</button>
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addMaterialUnassignedModal"><i class="fas fa-upload me-1"></i> Add Material</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2"><i class="fas fa-tasks me-1"></i>Assignments</h6>
                            <?php
                            if ($course_id) {
                                $assignments = mysqli_query($conn, "SELECT * FROM teacher_assignments WHERE course_id = $course_id AND created_by = $teacher_id ORDER BY due_date DESC");
                                if ($assignments && mysqli_num_rows($assignments) > 0) {
                                    echo '<ul class="list-group mb-3">';
                                    while ($a = mysqli_fetch_assoc($assignments)) {
                                        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                                        echo '<span><i class="fas fa-file-alt me-1"></i>' . htmlspecialchars($a['title']) . ' <span class="text-muted small">(Due: ' . htmlspecialchars($a['due_date']) . ')</span></span>';
                                        if (!empty($a['file_path'])) {
                                            echo '<a href="' . htmlspecialchars($a['file_path']) . '" download class="btn btn-sm btn-outline-primary ms-2"><i class="fas fa-download"></i> Download</a>';
                                        }
                                        echo '<button class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#editAssignmentModal_' . $a['id'] . '"><i class="fas fa-edit"></i> Edit</button>';
                                        echo '<form method="post" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to delete this assignment?\');"><input type="hidden" name="delete_assignment" value="1"><input type="hidden" name="assignment_id" value="' . $a['id'] . '"><button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i> Delete</button></form>';
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                } else {
                                    echo '<div class="text-muted mb-2">No assignments found.</div>';
                                }
                            } else {
                                echo '<div class="text-muted mb-2">No course found for this subject.</div>';
                            }
                            ?>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2"><i class="fas fa-file me-1"></i>Materials</h6>
                            <?php
                            if ($course_id) {
                                $materials = mysqli_query($conn, "SELECT * FROM materials WHERE course_id = $course_id AND uploaded_by = $teacher_id ORDER BY created_at DESC");
                                if ($materials && mysqli_num_rows($materials) > 0) {
                                    echo '<ul class="list-group">';
                                    while ($m = mysqli_fetch_assoc($materials)) {
                                        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                                        echo '<span><i class="fas fa-file me-1"></i>' . htmlspecialchars($m['title']) . '</span>';
                                        echo '<a href="' . htmlspecialchars($m['file_path']) . '" download class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i> Download</a>';
                                        echo '<button class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#editMaterialModal_' . $m['id'] . '"><i class="fas fa-edit"></i> Edit</button>';
                                        echo '<form method="post" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to delete this material?\');"><input type="hidden" name="delete_material" value="1"><input type="hidden" name="material_id" value="' . $m['id'] . '"><button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i> Delete</button></form>';
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                } else {
                                    echo '<div class="text-muted">No materials found.</div>';
                                }
                            } else {
                                echo '<div class="text-muted">No course found for this subject.</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="assignment-card text-center">
                    <div class="assignment-icon mb-3"><i class="fas fa-exclamation-triangle"></i></div>
                    <h4>No Classes or Subjects Assigned</h4>
                    <p class="text-muted">You haven't been assigned to any classes or subjects yet. Please contact your school administrator.</p>
                </div>
            </div>
        <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Add Assignment Unassigned Modal -->
    <div class="modal fade" id="addAssignmentUnassignedModal" tabindex="-1" aria-labelledby="addAssignmentUnassignedModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="addAssignmentUnassignedModalLabel"><i class="fas fa-plus me-2"></i>Add Assignment (Woodwork Only)</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="add_assignment_unassigned" value="1">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Due Date</label>
                <input type="date" name="due_date" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Total Points</label>
                <input type="number" name="total_points" class="form-control" value="100" min="1" max="1000">
              </div>
              <div class="mb-3">
                <label class="form-label">File (optional)</label>
                <input type="file" name="assignment_file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Assignment</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Add Material Unassigned Modal -->
    <div class="modal fade" id="addMaterialUnassignedModal" tabindex="-1" aria-labelledby="addMaterialUnassignedModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="addMaterialUnassignedModalLabel"><i class="fas fa-upload me-2"></i>Add Material (Woodwork Only)</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="add_material_unassigned" value="1">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">File</label>
                <input type="file" name="material_file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.jpg,.jpeg,.png" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success"><i class="fas fa-upload me-2"></i>Add Material</button>
            </div>
          </form>
        </div>
      </div>
    </div>
</body>
</html> 