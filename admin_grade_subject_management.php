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

// Fetch grades and subjects
$grades_result = mysqli_query($conn, "SELECT id, name FROM grades ORDER BY id");
$subjects_result = mysqli_query($conn, "SELECT id, name FROM subjects ORDER BY name");
$sections = ['A', 'B', 'C', 'D', 'E']; // You can adjust or fetch dynamically if needed

// Handle single assignment form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_assignment'])) {
    $grade_id = (int)$_POST['grade_id'];
    $section = mysqli_real_escape_string($conn, $_POST['section']);
    $subject_id = (int)$_POST['subject_id'];
    $is_required = isset($_POST['is_required']) ? 1 : 0;
    $is_elective = $is_required ? 0 : 1;
    $credits = isset($_POST['credits']) ? (int)$_POST['credits'] : 1;
    $description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : '';
    // Check for duplicate
    $exists = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM grade_subject_assignments WHERE grade_id = $grade_id AND section = '$section' AND subject_id = $subject_id"))[0];
    if ($exists) {
        $message = "<div class='alert alert-warning'>Assignment already exists for this grade/section/subject.</div>";
    } else {
        $ins = mysqli_query($conn, "INSERT INTO grade_subject_assignments (grade_id, section, subject_id, is_required, is_elective, credits, description) VALUES ($grade_id, '$section', $subject_id, $is_required, $is_elective, $credits, '$description')");
        if ($ins) {
            $message = "<div class='alert alert-success'>Assignment added successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade-Section-Subject Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { background: #f8fafc; }
        .back-btn {
            margin: 2rem 0 1.5rem 0;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <a href="adminhome.php" class="btn btn-secondary back-btn"><i class="fas fa-arrow-left"></i> Back to Home</a>
        <h2 class="mb-4">Grade-Section-Subject Assignments</h2>
        <!-- Search/Filter Bar -->
        <form id="filterForm" class="row g-3 mb-3">
            <div class="col-md-3">
                <select class="form-select" name="filter_grade" id="filter_grade">
                    <option value="">All Grades</option>
                    <?php if ($grades_result) mysqli_data_seek($grades_result, 0); while ($grade = mysqli_fetch_assoc($grades_result)): ?>
                        <option value="<?= $grade['id'] ?>"><?= htmlspecialchars($grade['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="filter_section" id="filter_section">
                    <option value="">All Sections</option>
                    <?php foreach ($sections as $sec): ?>
                        <option value="<?= $sec ?>"><?= htmlspecialchars($sec) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" name="filter_subject" id="filter_subject" placeholder="Search Subject Name">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
        <!-- Add Assignment Button -->
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus"></i> Add Assignment</button>
        <!-- Assignment Table -->
        <div id="assignmentTable"></div>
    </div>
    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="addAssignmentForm">
            <div class="modal-header">
              <h5 class="modal-title" id="addModalLabel">Add Assignment</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div id="addError" class="alert alert-danger d-none"></div>
              <div class="mb-3">
                <label for="add_grade" class="form-label">Grade</label>
                <select class="form-select" id="add_grade" name="grade_id" required>
                  <option value="">Select Grade</option>
                  <?php if ($grades_result) mysqli_data_seek($grades_result, 0); while ($grade = mysqli_fetch_assoc($grades_result)): ?>
                    <option value="<?= $grade['id'] ?>"><?= htmlspecialchars($grade['name']) ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="add_section" class="form-label">Section</label>
                <select class="form-select" id="add_section" name="section" required>
                  <option value="">Select Section</option>
                  <?php foreach ($sections as $sec): ?>
                    <option value="<?= $sec ?>"><?= htmlspecialchars($sec) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="add_subject" class="form-label">Subject</label>
                <select class="form-select" id="add_subject" name="subject_id" required>
                  <option value="">Select Subject</option>
                  <?php if ($subjects_result) mysqli_data_seek($subjects_result, 0); while ($subject = mysqli_fetch_assoc($subjects_result)): ?>
                    <option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="add_required" class="form-label">Required?</label>
                <select class="form-select" id="add_required" name="is_required">
                  <option value="1">Yes</option>
                  <option value="0">No</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="add_credits" class="form-label">Credits</label>
                <input type="number" class="form-control" id="add_credits" name="credits" value="1" min="1" required>
              </div>
              <div class="mb-3">
                <label for="add_description" class="form-label">Description</label>
                <textarea class="form-control" id="add_description" name="description"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success">Add Assignment</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Edit Modal (content loaded dynamically) -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content" id="editModalContent">
          <!-- Content loaded by JS -->
        </div>
      </div>
    </div>
    <script>
    function loadAssignments() {
      const data = $('#filterForm').serialize();
      $.get('ajax_grade_subject_assignments.php', data, function(html) {
        $('#assignmentTable').html(html);
      });
    }
    $(function() {
      loadAssignments();
      $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        loadAssignments();
      });
      $('#addAssignmentForm').on('submit', function(e) {
        e.preventDefault();
        $.post('ajax_grade_subject_assignments.php', $(this).serialize() + '&action=add', function(resp) {
          if (resp.success) {
            $('#addModal').modal('hide');
            loadAssignments();
            $('#addAssignmentForm')[0].reset();
            $('#addError').addClass('d-none');
          } else {
            $('#addError').removeClass('d-none').text(resp.error);
          }
        }, 'json');
      });
      // Edit and delete handled in loaded table
      $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        $.get('ajax_grade_subject_assignments.php', {action: 'edit_form', id}, function(html) {
          $('#editModalContent').html(html);
          $('#editModal').modal('show');
        });
      });
      $(document).on('submit', '#editAssignmentForm', function(e) {
        e.preventDefault();
        $.post('ajax_grade_subject_assignments.php', $(this).serialize() + '&action=edit', function(resp) {
          if (resp.success) {
            $('#editModal').modal('hide');
            loadAssignments();
          } else {
            $('#editError').removeClass('d-none').text(resp.error);
          }
        }, 'json');
      });
      $(document).on('click', '.delete-btn', function() {
        if (confirm('Are you sure you want to delete this assignment?')) {
          const id = $(this).data('id');
          $.post('ajax_grade_subject_assignments.php', {action: 'delete', id}, function(resp) {
            if (resp.success) loadAssignments();
            else alert(resp.error);
          }, 'json');
        }
      });
    });
    </script>
</body>
</html> 