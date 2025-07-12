<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'teacher') {
    header("Location: login.php");
    exit();
}
$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) { die("Database connection failed: " . mysqli_connect_error()); }
$teacher_id = $_SESSION['teacher_id'];
// Get all classes (grade, section, subject) assigned to this teacher
$classes = mysqli_query($conn, "SELECT tgs.grade_id, g.name as grade_name, gsa.section, tgs.subject_id, s.name as subject_name, gsa.id as gsa_id FROM teacher_grade_subjects tgs JOIN grades g ON tgs.grade_id = g.id JOIN subjects s ON tgs.subject_id = s.id JOIN grade_subject_assignments gsa ON gsa.grade_id = tgs.grade_id AND gsa.subject_id = tgs.subject_id WHERE tgs.teacher_id = $teacher_id ORDER BY g.name, gsa.section, s.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assignments & Materials - Teacher Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #2563eb; --secondary-color: #1e40af; --light-color: #f8fafc; --border-color: #e5e7eb; }
        * { font-family: 'Poppins', sans-serif; }
        body { background: var(--light-color); }
        .main-content { margin-left: 280px; padding: 2rem; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-brand">Teacher Portal</a>
        </div>
        <div class="sidebar-menu">
            <a href="teacherhome.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
            <a href="teacherclasses.php" class="sidebar-link"><i class="fas fa-users-class"></i>My Classes</a>
            <a href="teachersubjects.php" class="sidebar-link"><i class="fas fa-book"></i>My Subjects</a>
            <a href="teacherassignments.php" class="sidebar-link active"><i class="fas fa-tasks"></i>Assignments & Materials</a>
            <a href="teacherperformance.php" class="sidebar-link"><i class="fas fa-chart-bar"></i>Student Performance</a>
            <a href="teacherschedule.php" class="sidebar-link"><i class="fas fa-calendar-alt"></i>Schedule</a>
            <a href="teacherprofile.php" class="sidebar-link"><i class="fas fa-user"></i>Profile</a>
            <a href="teachernotifications.php" class="sidebar-link"><i class="fas fa-bell"></i>Notifications</a>
        </div>
    </aside>
    <div class="main-content">
        <h2 class="mb-4"><i class="fas fa-tasks me-2"></i>Assignments & Materials</h2>
        <div class="row">
        <?php if ($classes && mysqli_num_rows($classes) > 0):
            // Show global success/error messages
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
                // Find course_id for this grade/subject
                $course = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM courses WHERE course_name LIKE '%$subject%' AND program LIKE '%$grade%' LIMIT 1"));
                $course_id = $course['id'] ?? 0;
                $class_key = $grade . $section . $subject . $gsa_id;
?>
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <strong><?php echo "$grade$section - $subject"; ?></strong>
                        <div>
                            <button class="btn btn-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addAssignmentModal_<?php echo $class_key; ?>"><i class="fas fa-plus me-1"></i>Add Assignment</button>
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addMaterialModal_<?php echo $class_key; ?>"><i class="fas fa-upload me-1"></i>Add Material</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-2"><i class="fas fa-tasks me-1"></i>Assignments</h6>
                                <?php
                                if ($course_id) {
                                    $assignments = mysqli_query($conn, "SELECT * FROM course_assignments WHERE course_id = $course_id AND created_by = $teacher_id ORDER BY due_date DESC");
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
                                    $materials = mysqli_query($conn, "SELECT * FROM course_materials WHERE course_id = $course_id AND uploaded_by = $teacher_id ORDER BY created_at DESC");
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
            </div>
            <!-- Add Assignment Modal -->
            <div class="modal fade" id="addAssignmentModal_<?php echo $class_key; ?>" tabindex="-1" aria-labelledby="addAssignmentLabel_<?php echo $class_key; ?>" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                      <h5 class="modal-title" id="addAssignmentLabel_<?php echo $class_key; ?>">Add Assignment for <?php echo "$grade$section - $subject"; ?></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <input type="hidden" name="add_assignment" value="1">
                      <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                      <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="assignment_title" class="form-control" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="assignment_description" class="form-control" rows="3"></textarea>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="assignment_due_date" class="form-control" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">File (optional, PDF/DOCX, max 10MB)</label>
                        <input type="file" name="assignment_file" class="form-control" accept=".pdf,.doc,.docx">
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary">Add Assignment</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!-- Add Material Modal -->
            <div class="modal fade" id="addMaterialModal_<?php echo $class_key; ?>" tabindex="-1" aria-labelledby="addMaterialLabel_<?php echo $class_key; ?>" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                      <h5 class="modal-title" id="addMaterialLabel_<?php echo $class_key; ?>">Add Material for <?php echo "$grade$section - $subject"; ?></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <input type="hidden" name="add_material" value="1">
                      <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                      <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="material_title" class="form-control" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="material_description" class="form-control" rows="3"></textarea>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">File (PDF/DOCX/PPT/JPG/PNG, max 10MB)</label>
                        <input type="file" name="material_file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.jpg,.jpeg,.png">
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary">Add Material</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
<?php
        // After each assignment and material list item, add the edit modal
        // For assignments:
        if ($course_id) {
            $assignments = mysqli_query($conn, "SELECT * FROM course_assignments WHERE course_id = $course_id AND created_by = $teacher_id ORDER BY due_date DESC");
            if ($assignments && mysqli_num_rows($assignments) > 0) {
                while ($a = mysqli_fetch_assoc($assignments)) {
                    $aid = $a['id'];
?>
<div class="modal fade" id="editAssignmentModal_<?php echo $aid; ?>" tabindex="-1" aria-labelledby="editAssignmentLabel_<?php echo $aid; ?>" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="editAssignmentLabel_<?php echo $aid; ?>">Edit Assignment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="edit_assignment" value="1">
          <input type="hidden" name="assignment_id" value="<?php echo $aid; ?>">
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="assignment_title" class="form-control" value="<?php echo htmlspecialchars($a['title']); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="assignment_description" class="form-control" rows="3"><?php echo htmlspecialchars($a['description']); ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Due Date</label>
            <input type="date" name="assignment_due_date" class="form-control" value="<?php echo htmlspecialchars($a['due_date']); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Replace File (optional, PDF/DOCX, max 10MB)</label>
            <input type="file" name="assignment_file" class="form-control" accept=".pdf,.doc,.docx">
            <?php if (!empty($a['file_path'])): ?>
                <div class="mt-2"><a href="<?php echo htmlspecialchars($a['file_path']); ?>" download>Current File</a></div>
            <?php endif; ?>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php
                }
            }
        }
        // For materials:
        if ($course_id) {
            $materials = mysqli_query($conn, "SELECT * FROM course_materials WHERE course_id = $course_id AND uploaded_by = $teacher_id ORDER BY created_at DESC");
            if ($materials && mysqli_num_rows($materials) > 0) {
                while ($m = mysqli_fetch_assoc($materials)) {
                    $mid = $m['id'];
?>
<div class="modal fade" id="editMaterialModal_<?php echo $mid; ?>" tabindex="-1" aria-labelledby="editMaterialLabel_<?php echo $mid; ?>" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="editMaterialLabel_<?php echo $mid; ?>">Edit Material</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="edit_material" value="1">
          <input type="hidden" name="material_id" value="<?php echo $mid; ?>">
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="material_title" class="form-control" value="<?php echo htmlspecialchars($m['title']); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="material_description" class="form-control" rows="3"><?php echo htmlspecialchars($m['description']); ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Replace File (PDF/DOCX/PPT/JPG/PNG, max 10MB)</label>
            <input type="file" name="material_file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.jpg,.jpeg,.png">
            <?php if (!empty($m['file_path'])): ?>
                <div class="mt-2"><a href="<?php echo htmlspecialchars($m['file_path']); ?>" download>Current File</a></div>
            <?php endif; ?>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php
                }
            }
        }
?>
<?php endwhile; else: ?>
            <div class="col-12"><div class="alert alert-info">No classes assigned.</div></div>
        <?php endif; ?>
        </div>
    </div>
<?php
// Handle POST requests for edit and delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_assignment'])) {
        $course_id = (int)$_POST['course_id'];
        $title = mysqli_real_escape_string($conn, $_POST['assignment_title']);
        $desc = mysqli_real_escape_string($conn, $_POST['assignment_description']);
        $due = mysqli_real_escape_string($conn, $_POST['assignment_due_date']);
        $file_path = '';
        if (!empty($_FILES['assignment_file']['name'])) {
            $file = $_FILES['assignment_file'];
            $allowed = ['pdf','doc','docx'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $file['size'] <= 10*1024*1024) {
                $newname = 'uploads/assignments/' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $newname)) {
                    $file_path = $newname;
                } else {
                    $_SESSION['error_message'] = 'File upload failed.';
                    header('Location: teacherassignments.php'); exit();
                }
            } else {
                $_SESSION['error_message'] = 'Invalid file type or size.';
                header('Location: teacherassignments.php'); exit();
            }
        }
        $insert = mysqli_query($conn, "INSERT INTO course_assignments (course_id, title, description, due_date, created_by, file_path) VALUES ($course_id, '$title', '$desc', '$due', $teacher_id, '$file_path')");
        if ($insert) {
            $_SESSION['success_message'] = 'Assignment added successfully!';
        } else {
            $_SESSION['error_message'] = 'Failed to add assignment.';
        }
        header('Location: teacherassignments.php'); exit();
    }
    if (isset($_POST['add_material'])) {
        $course_id = (int)$_POST['course_id'];
        $title = mysqli_real_escape_string($conn, $_POST['material_title']);
        $desc = mysqli_real_escape_string($conn, $_POST['material_description']);
        $file_path = '';
        $file_name = '';
        $file_size = 0;
        $file_type = '';
        if (!empty($_FILES['material_file']['name'])) {
            $file = $_FILES['material_file'];
            $allowed = ['pdf','doc','docx','ppt','pptx','txt','jpg','jpeg','png'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $file['size'] <= 10*1024*1024) {
                $newname = 'uploads/assignments/' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $newname)) {
                    $file_path = $newname;
                    $file_name = $file['name'];
                    $file_size = $file['size'];
                    $file_type = $ext;
                } else {
                    $_SESSION['error_message'] = 'File upload failed.';
                    header('Location: teacherassignments.php'); exit();
                }
            } else {
                $_SESSION['error_message'] = 'Invalid file type or size.';
                header('Location: teacherassignments.php'); exit();
            }
        }
        $insert = mysqli_query($conn, "INSERT INTO course_materials (course_id, title, description, file_path, file_name, file_size, file_type, uploaded_by) VALUES ($course_id, '$title', '$desc', '$file_path', '$file_name', $file_size, '$file_type', $teacher_id)");
        if ($insert) {
            $_SESSION['success_message'] = 'Material added successfully!';
        } else {
            $_SESSION['error_message'] = 'Failed to add material.';
        }
        header('Location: teacherassignments.php'); exit();
    }
    if (isset($_POST['edit_assignment'])) {
        $aid = (int)$_POST['assignment_id'];
        $title = mysqli_real_escape_string($conn, $_POST['assignment_title']);
        $desc = mysqli_real_escape_string($conn, $_POST['assignment_description']);
        $due = mysqli_real_escape_string($conn, $_POST['assignment_due_date']);
        $file_path = '';
        $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT file_path FROM course_assignments WHERE id = $aid AND created_by = $teacher_id"));
        if (!empty($_FILES['assignment_file']['name'])) {
            $file = $_FILES['assignment_file'];
            $allowed = ['pdf','doc','docx'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $file['size'] <= 10*1024*1024) {
                $newname = 'uploads/assignments/' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $newname)) {
                    $file_path = $newname;
                    if (!empty($old['file_path']) && file_exists($old['file_path'])) {
                        unlink($old['file_path']);
                    }
                } else {
                    $_SESSION['error_message'] = 'File upload failed.';
                    header('Location: teacherassignments.php'); exit();
                }
            } else {
                $_SESSION['error_message'] = 'Invalid file type or size.';
                header('Location: teacherassignments.php'); exit();
            }
        } else {
            $file_path = $old['file_path'];
        }
        $update = mysqli_query($conn, "UPDATE course_assignments SET title='$title', description='$desc', due_date='$due', file_path='$file_path' WHERE id=$aid AND created_by=$teacher_id");
        if ($update) {
            $_SESSION['success_message'] = 'Assignment updated successfully!';
        } else {
            $_SESSION['error_message'] = 'Failed to update assignment.';
        }
        header('Location: teacherassignments.php'); exit();
    }
    if (isset($_POST['edit_material'])) {
        $mid = (int)$_POST['material_id'];
        $title = mysqli_real_escape_string($conn, $_POST['material_title']);
        $desc = mysqli_real_escape_string($conn, $_POST['material_description']);
        $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT file_path FROM course_materials WHERE id = $mid AND uploaded_by = $teacher_id"));
        $file_path = $old['file_path'];
        $file_name = '';
        $file_size = 0;
        $file_type = '';
        if (!empty($_FILES['material_file']['name'])) {
            $file = $_FILES['material_file'];
            $allowed = ['pdf','doc','docx','ppt','pptx','txt','jpg','jpeg','png'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $file['size'] <= 10*1024*1024) {
                $newname = 'uploads/assignments/' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $newname)) {
                    if (!empty($old['file_path']) && file_exists($old['file_path'])) {
                        unlink($old['file_path']);
                    }
                    $file_path = $newname;
                    $file_name = $file['name'];
                    $file_size = $file['size'];
                    $file_type = $ext;
                } else {
                    $_SESSION['error_message'] = 'File upload failed.';
                    header('Location: teacherassignments.php'); exit();
                }
            } else {
                $_SESSION['error_message'] = 'Invalid file type or size.';
                header('Location: teacherassignments.php'); exit();
            }
        }
        $update = mysqli_query($conn, "UPDATE course_materials SET title='$title', description='$desc', file_path='$file_path', file_name='$file_name', file_size=$file_size, file_type='$file_type' WHERE id=$mid AND uploaded_by=$teacher_id");
        if ($update) {
            $_SESSION['success_message'] = 'Material updated successfully!';
        } else {
            $_SESSION['error_message'] = 'Failed to update material.';
        }
        header('Location: teacherassignments.php'); exit();
    }
    if (isset($_POST['delete_assignment'])) {
        $aid = (int)$_POST['assignment_id'];
        $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT file_path FROM course_assignments WHERE id = $aid AND created_by = $teacher_id"));
        if (!empty($old['file_path']) && file_exists($old['file_path'])) {
            unlink($old['file_path']);
        }
        $delete = mysqli_query($conn, "DELETE FROM course_assignments WHERE id = $aid AND created_by = $teacher_id");
        if ($delete) {
            $_SESSION['success_message'] = 'Assignment deleted successfully!';
        } else {
            $_SESSION['error_message'] = 'Failed to delete assignment.';
        }
        header('Location: teacherassignments.php'); exit();
    }
    if (isset($_POST['delete_material'])) {
        $mid = (int)$_POST['material_id'];
        $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT file_path FROM course_materials WHERE id = $mid AND uploaded_by = $teacher_id"));
        if (!empty($old['file_path']) && file_exists($old['file_path'])) {
            unlink($old['file_path']);
        }
        $delete = mysqli_query($conn, "DELETE FROM course_materials WHERE id = $mid AND uploaded_by = $teacher_id");
        if ($delete) {
            $_SESSION['success_message'] = 'Material deleted successfully!';
        } else {
            $_SESSION['error_message'] = 'Failed to delete material.';
        }
        header('Location: teacherassignments.php'); exit();
    }
}
?>
</body>
</html> 