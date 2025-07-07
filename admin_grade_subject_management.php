<?php
session_start();

// Only admins can access
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "schoolproject");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch grades, sections, and subjects
$grades_result = mysqli_query($conn, "SELECT id, name FROM grades ORDER BY id");
$subjects_result = mysqli_query($conn, "SELECT id, name FROM subjects ORDER BY name");
$sections = ['A', 'B', 'C', 'D', 'E']; // You can adjust or fetch dynamically if needed

// Handle bulk assignment form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_subjects'])) {
    $selected_grades = isset($_POST['grades']) ? $_POST['grades'] : [];
    $selected_sections = isset($_POST['sections']) ? $_POST['sections'] : [];
    $selected_subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];
    $is_required = isset($_POST['is_required']) ? 1 : 0;
    $is_elective = $is_required ? 0 : 1;
    $credits = isset($_POST['credits']) ? (int)$_POST['credits'] : 1;
    $description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : '';

    $count = 0;
    foreach ($selected_grades as $grade_id) {
        foreach ($selected_sections as $section) {
            foreach ($selected_subjects as $subject_id) {
                // Insert or update assignment
                $sql = "INSERT INTO grade_subject_assignments (grade_id, section, subject_id, is_required, is_elective, credits, description) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE is_required=VALUES(is_required), is_elective=VALUES(is_elective), credits=VALUES(credits), description=VALUES(description)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "isiiiss", $grade_id, $section, $subject_id, $is_required, $is_elective, $credits, $description);
                if (mysqli_stmt_execute($stmt)) {
                    $count++;
                }
            }
        }
    }
    $message = "<div class='alert alert-success'>Successfully assigned $count subject(s) to selected grades and sections.</div>";
}

// Handle delete assignment
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM grade_subject_assignments WHERE id = $id");
    $message = "<div class='alert alert-success'>Assignment deleted.</div>";
}

// Fetch all assignments
$assignments = mysqli_query($conn, "SELECT gsa.*, g.name as grade_name, s.name as subject_name FROM grade_subject_assignments gsa JOIN grades g ON gsa.grade_id = g.id JOIN subjects s ON gsa.subject_id = s.id ORDER BY g.name, gsa.section, s.name");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Grade/Section Subject Management</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .bulk-form { background: #fff; border-radius: 12px; box-shadow: var(--shadow-md); padding: 2rem; margin-bottom: 2rem; }
        .table { width: 100%; border-collapse: collapse; margin-top: 2rem; }
        .table th, .table td { padding: 0.75rem 1rem; border: 1px solid var(--border-color); }
        .table th { background: var(--primary-color); color: #fff; }
        .table-striped tbody tr:nth-child(odd) { background: var(--light-color); }
        .actions { display: flex; gap: 0.5rem; }
        .actions a { color: var(--danger-color); text-decoration: none; }
        .actions a.edit { color: var(--primary-color); }
    </style>
</head>
<body>
<?php include 'adminsidebar.php'; ?>
<div class="content">
    <h1 class="mb-4">Grade/Section Subject Management</h1>
    <?php if ($message) echo $message; ?>
    <div class="bulk-form card">
        <h2 class="mb-3">Bulk Assign Subjects to Grades & Sections</h2>
        <form method="post">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Select Grades</label>
                    <select name="grades[]" class="form-select" multiple required>
                        <?php if ($grades_result) mysqli_data_seek($grades_result, 0); while ($grade = mysqli_fetch_assoc($grades_result)): ?>
                            <option value="<?php echo $grade['id']; ?>"><?php echo htmlspecialchars($grade['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Select Sections</label>
                    <select name="sections[]" class="form-select" multiple required>
                        <?php foreach ($sections as $section): ?>
                            <option value="<?php echo $section; ?>"><?php echo $section; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Select Subjects</label>
                    <select name="subjects[]" class="form-select" multiple required>
                        <?php if ($subjects_result) mysqli_data_seek($subjects_result, 0); while ($subject = mysqli_fetch_assoc($subjects_result)): ?>
                            <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-4">
                    <label class="form-label">Required?</label>
                    <input type="checkbox" name="is_required" value="1"> <span>Mark as Required (otherwise Elective)</span>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Credits</label>
                    <input type="number" name="credits" class="form-control" value="1" min="1" max="10">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" maxlength="255">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" name="assign_subjects" class="btn btn-primary">Assign Subjects</button>
            </div>
        </form>
    </div>
    <div class="card">
        <h2 class="mb-3">Current Grade/Section/Subject Assignments</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Grade</th>
                    <th>Section</th>
                    <th>Subject</th>
                    <th>Required</th>
                    <th>Elective</th>
                    <th>Credits</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($assignments)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['grade_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['section']); ?></td>
                        <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                        <td><?php echo $row['is_required'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo $row['is_elective'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo htmlspecialchars($row['credits']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td class="actions">
                            <!-- Edit functionality can be added here -->
                            <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this assignment?');" title="Delete"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html> 