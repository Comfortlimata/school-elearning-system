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
$success_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new grade-section-subject assignment
    if (isset($_POST['add_assignment'])) {
        $grade_id = (int)$_POST['grade_id'];
        $section = mysqli_real_escape_string($data, $_POST['section']);
        $subject_id = (int)$_POST['subject_id'];
        $is_required = isset($_POST['is_required']) ? 1 : 0;
        $is_elective = isset($_POST['is_elective']) ? 1 : 0;
        $credits = (int)$_POST['credits'];
        $description = mysqli_real_escape_string($data, $_POST['description']);
        
        // Check if assignment already exists
        $check_sql = "SELECT id FROM grade_subject_assignments WHERE grade_id = ? AND section = ? AND subject_id = ?";
        $stmt = mysqli_prepare($data, $check_sql);
        mysqli_stmt_bind_param($stmt, "isi", $grade_id, $section, $subject_id);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $message = "This grade-section-subject assignment already exists!";
        } else {
            $insert_sql = "INSERT INTO grade_subject_assignments (grade_id, section, subject_id, is_required, is_elective, credits, description) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($data, $insert_sql);
            mysqli_stmt_bind_param($stmt, "isiissi", $grade_id, $section, $subject_id, $is_required, $is_elective, $credits, $description);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Grade-section-subject assignment added successfully!";
            } else {
                $message = "Error adding assignment: " . mysqli_error($data);
            }
        }
    }
    
    // Update existing assignment
    if (isset($_POST['update_assignment'])) {
        $assignment_id = (int)$_POST['assignment_id'];
        $is_required = isset($_POST['is_required']) ? 1 : 0;
        $is_elective = isset($_POST['is_elective']) ? 1 : 0;
        $credits = (int)$_POST['credits'];
        $description = mysqli_real_escape_string($data, $_POST['description']);
        
        $update_sql = "UPDATE grade_subject_assignments SET is_required = ?, is_elective = ?, credits = ?, description = ? WHERE id = ?";
        $stmt = mysqli_prepare($data, $update_sql);
        mysqli_stmt_bind_param($stmt, "iiisi", $is_required, $is_elective, $credits, $description, $assignment_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Assignment updated successfully!";
        } else {
            $message = "Error updating assignment: " . mysqli_error($data);
        }
    }
    
    // Delete assignment
    if (isset($_POST['delete_assignment'])) {
        $assignment_id = (int)$_POST['assignment_id'];
        
        // Check if this assignment is being used by teachers
        $check_usage = "SELECT COUNT(*) as count FROM teacher_grade_subjects tgs 
                       JOIN grade_subject_assignments gsa ON tgs.grade_id = gsa.grade_id AND tgs.subject_id = gsa.subject_id 
                       WHERE gsa.id = ?";
        $stmt = mysqli_prepare($data, $check_usage);
        mysqli_stmt_bind_param($stmt, "i", $assignment_id);
        mysqli_stmt_execute($stmt);
        $usage_result = mysqli_stmt_get_result($stmt);
        $usage_count = mysqli_fetch_assoc($usage_result)['count'];
        
        if ($usage_count > 0) {
            $message = "Cannot delete: This assignment is currently being used by $usage_count teacher(s).";
        } else {
            $delete_sql = "DELETE FROM grade_subject_assignments WHERE id = ?";
            $stmt = mysqli_prepare($data, $delete_sql);
            mysqli_stmt_bind_param($stmt, "i", $assignment_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Assignment deleted successfully!";
            } else {
                $message = "Error deleting assignment: " . mysqli_error($data);
            }
        }
    }
}

// Get all grades and subjects for dropdowns
$grades_result = mysqli_query($data, "SELECT id, name FROM grades ORDER BY name");
$subjects_result = mysqli_query($data, "SELECT id, name FROM subjects ORDER BY name");

// Get all grade-section-subject assignments with grade and subject names
$assignments_sql = "SELECT gsa.*, g.name as grade_name, s.name as subject_name 
                   FROM grade_subject_assignments gsa 
                   JOIN grades g ON gsa.grade_id = g.id 
                   JOIN subjects s ON gsa.subject_id = s.id 
                   ORDER BY g.name, gsa.section, s.name";
$assignments_result = mysqli_query($data, $assignments_sql);

// Get statistics
$total_assignments = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM grade_subject_assignments"))[0];
$required_assignments = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM grade_subject_assignments WHERE is_required = 1"))[0];
$elective_assignments = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM grade_subject_assignments WHERE is_elective = 1"))[0];
$total_sections = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(DISTINCT CONCAT(grade_id, section)) FROM grade_subject_assignments"))[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Grade-Section-Subject Management - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .assignments-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-required {
            background: #dc3545;
            color: white;
        }
        
        .badge-elective {
            background: #28a745;
            color: white;
        }
        
        .badge-section {
            background: #6c757d;
            color: white;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <a href="adminhome.php">
            <i class="fas fa-graduation-cap me-2"></i>
            Grade-Section-Subject Management
        </a>
        <div class="logout">
            <a href="adminhome.php">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Dashboard
            </a>
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
                <a href="add_teacher_auth.php">
                    <i class="fas fa-chalkboard-teacher me-2"></i>
                    Add Teacher
                </a>
            </li>
            <li>
                <a href="add_courses.php">
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
            <li>
                <a href="admin_grade_subject_management.php" class="active">
                    <i class="fas fa-link me-2"></i>
                    Grade-Section-Subject Management
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="content fade-in">
        <h1><i class="fas fa-link me-2"></i>Grade-Section-Subject Management</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_assignments; ?></div>
                <div class="stat-label">Total Assignments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_sections; ?></div>
                <div class="stat-label">Grade Sections</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $required_assignments; ?></div>
                <div class="stat-label">Required Subjects</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $elective_assignments; ?></div>
                <div class="stat-label">Elective Subjects</div>
            </div>
        </div>

        <!-- Add New Assignment Form -->
        <div class="form-section">
            <h2><i class="fas fa-plus me-2"></i>Add New Grade-Section-Subject Assignment</h2>
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="grade_id" class="form-label">
                                <i class="fas fa-graduation-cap me-2"></i>Grade *
                            </label>
                            <select name="grade_id" id="grade_id" class="form-select" required>
                                <option value="">-- Select Grade --</option>
                                <?php if ($grades_result) mysqli_data_seek($grades_result, 0); while ($grade = mysqli_fetch_assoc($grades_result)): ?>
                                    <option value="<?php echo $grade['id']; ?>">
                                        <?php echo htmlspecialchars($grade['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="section" class="form-label">
                                <i class="fas fa-layer-group me-2"></i>Section *
                            </label>
                            <select name="section" id="section" class="form-select" required>
                                <option value="">-- Select Section --</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                                <option value="E">E</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="subject_id" class="form-label">
                                <i class="fas fa-book me-2"></i>Subject *
                            </label>
                            <select name="subject_id" id="subject_id" class="form-select" required>
                                <option value="">-- Select Subject --</option>
                                <?php if ($subjects_result) mysqli_data_seek($subjects_result, 0); while ($subject = mysqli_fetch_assoc($subjects_result)): ?>
                                    <option value="<?php echo $subject['id']; ?>">
                                        <?php echo htmlspecialchars($subject['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="credits" class="form-label">
                                <i class="fas fa-star me-2"></i>Credits
                            </label>
                            <input type="number" name="credits" id="credits" class="form-control" value="1" min="1" max="10">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-check-circle me-2"></i>Subject Type
                            </label>
                            <div class="form-check">
                                <input type="checkbox" name="is_required" id="is_required" class="form-check-input" value="1">
                                <label for="is_required" class="form-check-label">Required Subject</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="is_elective" id="is_elective" class="form-check-input" value="1" checked>
                                <label for="is_elective" class="form-check-label">Elective Subject</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="description" class="form-label">
                                <i class="fas fa-info-circle me-2"></i>Description
                            </label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Optional description..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="add_assignment" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Assignment
                    </button>
                </div>
            </form>
        </div>

        <!-- Current Assignments Table -->
        <div class="assignments-table">
            <div class="table-header">
                <h2><i class="fas fa-list me-2"></i>Current Grade-Section-Subject Assignments</h2>
            </div>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Grade</th>
                            <th>Section</th>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Credits</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($assignments_result && mysqli_num_rows($assignments_result) > 0): ?>
                            <?php while ($assignment = mysqli_fetch_assoc($assignments_result)): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($assignment['grade_name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-section"><?php echo htmlspecialchars($assignment['section']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($assignment['subject_name']); ?></td>
                                    <td>
                                        <?php if ($assignment['is_required']): ?>
                                            <span class="badge badge-required">Required</span>
                                        <?php else: ?>
                                            <span class="badge badge-elective">Elective</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $assignment['credits']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($assignment['description'] ?: '-'); ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editAssignment(<?php echo $assignment['id']; ?>, '<?php echo htmlspecialchars($assignment['grade_name']); ?>', '<?php echo htmlspecialchars($assignment['section']); ?>', '<?php echo htmlspecialchars($assignment['subject_name']); ?>', <?php echo $assignment['is_required']; ?>, <?php echo $assignment['is_elective']; ?>, <?php echo $assignment['credits']; ?>, '<?php echo htmlspecialchars($assignment['description']); ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteAssignment(<?php echo $assignment['id']; ?>, '<?php echo htmlspecialchars($assignment['grade_name']); ?>', '<?php echo htmlspecialchars($assignment['section']); ?>', '<?php echo htmlspecialchars($assignment['subject_name']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No grade-section-subject assignments found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Assignment Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3><i class="fas fa-edit me-2"></i>Edit Assignment</h3>
            <form method="POST" action="">
                <input type="hidden" name="assignment_id" id="edit_assignment_id">
                
                <div class="form-group">
                    <label class="form-label">Grade</label>
                    <input type="text" id="edit_grade_name" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Section</label>
                    <input type="text" id="edit_section_name" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Subject</label>
                    <input type="text" id="edit_subject_name" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label for="edit_credits" class="form-label">Credits</label>
                    <input type="number" name="credits" id="edit_credits" class="form-control" min="1" max="10">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Subject Type</label>
                    <div class="form-check">
                        <input type="checkbox" name="is_required" id="edit_is_required" class="form-check-input" value="1">
                        <label for="edit_is_required" class="form-check-label">Required Subject</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_elective" id="edit_is_elective" class="form-check-input" value="1">
                        <label for="edit_is_elective" class="form-check-label">Elective Subject</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_description" class="form-label">Description</label>
                    <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="update_assignment" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Assignment
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h3>
            <p>Are you sure you want to delete the assignment for <strong id="delete_grade_name"></strong> <strong id="delete_section_name"></strong> - <strong id="delete_subject_name"></strong>?</p>
            <p class="text-danger">This action cannot be undone.</p>
            
            <form method="POST" action="">
                <input type="hidden" name="assignment_id" id="delete_assignment_id">
                <div class="form-group">
                    <button type="submit" name="delete_assignment" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Delete Assignment
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functionality
        function editAssignment(id, gradeName, sectionName, subjectName, isRequired, isElective, credits, description) {
            document.getElementById('edit_assignment_id').value = id;
            document.getElementById('edit_grade_name').value = gradeName;
            document.getElementById('edit_section_name').value = sectionName;
            document.getElementById('edit_subject_name').value = subjectName;
            document.getElementById('edit_credits').value = credits;
            document.getElementById('edit_is_required').checked = isRequired == 1;
            document.getElementById('edit_is_elective').checked = isElective == 1;
            document.getElementById('edit_description').value = description;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function deleteAssignment(id, gradeName, sectionName, subjectName) {
            document.getElementById('delete_assignment_id').value = id;
            document.getElementById('delete_grade_name').textContent = gradeName;
            document.getElementById('delete_section_name').textContent = sectionName;
            document.getElementById('delete_subject_name').textContent = subjectName;
            document.getElementById('deleteModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Close modal when clicking on X or outside
        window.onclick = function(event) {
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
        }
        
        // Close modal when clicking X button
        document.querySelectorAll('.close').forEach(function(closeBtn) {
            closeBtn.onclick = function() {
                closeModal();
            }
        });
        
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const gradeSelect = document.getElementById('grade_id');
                const sectionSelect = document.getElementById('section');
                const subjectSelect = document.getElementById('subject_id');
                
                if (!gradeSelect.value || !sectionSelect.value || !subjectSelect.value) {
                    e.preventDefault();
                    alert('Please select grade, section, and subject.');
                    return;
                }
            });
        });
    </script>
</body>
</html> 