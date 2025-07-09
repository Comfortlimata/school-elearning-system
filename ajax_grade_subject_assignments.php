<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'admin') {
    http_response_code(403); exit('Forbidden');
}
$conn = mysqli_connect('localhost', 'root', '', 'schoolproject');
if (!$conn) { http_response_code(500); exit('DB error'); }

header('Content-Type: application/json');
$action = $_REQUEST['action'] ?? '';

function getAssignments($conn) {
    $where = [];
    if (!empty($_GET['filter_grade'])) {
        $grade_id = (int)$_GET['filter_grade'];
        $where[] = "gsa.grade_id = $grade_id";
    }
    if (!empty($_GET['filter_section'])) {
        $section = mysqli_real_escape_string($conn, $_GET['filter_section']);
        $where[] = "gsa.section = '$section'";
    }
    if (!empty($_GET['filter_subject'])) {
        $subject = mysqli_real_escape_string($conn, $_GET['filter_subject']);
        $where[] = "s.name LIKE '%$subject%'";
    }
    $sql = "SELECT gsa.*, g.name as grade, s.name as subject FROM grade_subject_assignments gsa JOIN grades g ON gsa.grade_id = g.id JOIN subjects s ON gsa.subject_id = s.id";
    if ($where) $sql .= " WHERE " . implode(' AND ', $where);
    $sql .= " ORDER BY g.name, gsa.section, s.name";
    $res = mysqli_query($conn, $sql);
    ob_start();
    echo '<table class="table table-bordered table-hover"><thead><tr><th>Grade</th><th>Section</th><th>Subject</th><th>Required</th><th>Credits</th><th>Description</th><th>Actions</th></tr></thead><tbody>';
    if ($res && mysqli_num_rows($res)) {
        while ($row = mysqli_fetch_assoc($res)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['grade'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($row['section'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($row['subject'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . ($row['is_required'] ? 'Yes' : 'No') . '</td>';
            echo '<td>' . (int)$row['credits'] . '</td>';
            echo '<td>' . htmlspecialchars($row['description'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>';
            echo '<button class="btn btn-sm btn-primary edit-btn me-1" data-id="' . $row['id'] . '"><i class="fas fa-edit"></i></button>';
            echo '<button class="btn btn-sm btn-danger delete-btn" data-id="' . $row['id'] . '"><i class="fas fa-trash"></i></button>';
            echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="7" class="text-center">No assignments found.</td></tr>';
    }
    echo '</tbody></table>';
    $html = ob_get_clean();
    header('Content-Type: text/html');
    echo $html;
    exit;
}

if ($action === 'add') {
    // Add assignment
    $grade_id = (int)$_POST['grade_id'];
    $section = mysqli_real_escape_string($conn, $_POST['section']);
    $subject_id = (int)$_POST['subject_id'];
    $is_required = isset($_POST['is_required']) ? (int)$_POST['is_required'] : 0;
    $is_elective = $is_required ? 0 : 1;
    $credits = isset($_POST['credits']) ? (int)$_POST['credits'] : 1;
    $description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : '';
    $exists = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM grade_subject_assignments WHERE grade_id = $grade_id AND section = '$section' AND subject_id = $subject_id"))[0];
    if ($exists) {
        echo json_encode(['success' => false, 'error' => 'Assignment already exists for this grade/section/subject.']);
        exit;
    }
    $ins = mysqli_query($conn, "INSERT INTO grade_subject_assignments (grade_id, section, subject_id, is_required, is_elective, credits, description) VALUES ($grade_id, '$section', $subject_id, $is_required, $is_elective, $credits, '$description')");
    if ($ins) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($conn)]);
    }
    exit;
}

if ($action === 'edit_form') {
    $id = (int)$_GET['id'];
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM grade_subject_assignments WHERE id = $id"));
    $grades = mysqli_query($conn, "SELECT id, name FROM grades ORDER BY name");
    $subjects = mysqli_query($conn, "SELECT id, name FROM subjects ORDER BY name");
    $sections = ['A','B','C','D','E'];
    ob_start();
    ?>
    <form id="editAssignmentForm">
      <input type="hidden" name="id" value="<?= $row['id'] ?>">
      <div class="modal-header">
        <h5 class="modal-title">Edit Assignment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="editError" class="alert alert-danger d-none"></div>
        <div class="mb-3">
          <label class="form-label">Grade</label>
          <select class="form-select" name="grade_id" required>
            <?php foreach ($grades as $g): ?>
              <option value="<?= $g['id'] ?>" <?= $g['id']==$row['grade_id']?'selected':'' ?>><?= htmlspecialchars($g['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Section</label>
          <select class="form-select" name="section" required>
            <?php foreach ($sections as $sec): ?>
              <option value="<?= $sec ?>" <?= $sec==$row['section']?'selected':'' ?>><?= htmlspecialchars($sec) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Subject</label>
          <select class="form-select" name="subject_id" required>
            <?php foreach ($subjects as $s): ?>
              <option value="<?= $s['id'] ?>" <?= $s['id']==$row['subject_id']?'selected':'' ?>><?= htmlspecialchars($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Required?</label>
          <select class="form-select" name="is_required">
            <option value="1" <?= $row['is_required']?'selected':'' ?>>Yes</option>
            <option value="0" <?= !$row['is_required']?'selected':'' ?>>No</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Credits</label>
          <input type="number" class="form-control" name="credits" value="<?= (int)$row['credits'] ?>" min="1" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea class="form-control" name="description"><?= htmlspecialchars($row['description']) ?></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
    <?php
    $html = ob_get_clean();
    header('Content-Type: text/html');
    echo $html;
    exit;
}

if ($action === 'edit') {
    $id = (int)$_POST['id'];
    $grade_id = (int)$_POST['grade_id'];
    $section = mysqli_real_escape_string($conn, $_POST['section']);
    $subject_id = (int)$_POST['subject_id'];
    $is_required = isset($_POST['is_required']) ? (int)$_POST['is_required'] : 0;
    $is_elective = $is_required ? 0 : 1;
    $credits = isset($_POST['credits']) ? (int)$_POST['credits'] : 1;
    $description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : '';
    // Check for duplicate (excluding self)
    $exists = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM grade_subject_assignments WHERE grade_id = $grade_id AND section = '$section' AND subject_id = $subject_id AND id != $id"))[0];
    if ($exists) {
        echo json_encode(['success' => false, 'error' => 'Assignment already exists for this grade/section/subject.']);
        exit;
    }
    $upd = mysqli_query($conn, "UPDATE grade_subject_assignments SET grade_id=$grade_id, section='$section', subject_id=$subject_id, is_required=$is_required, is_elective=$is_elective, credits=$credits, description='$description' WHERE id=$id");
    if ($upd) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($conn)]);
    }
    exit;
}

if ($action === 'delete') {
    $id = (int)$_POST['id'];
    $del = mysqli_query($conn, "DELETE FROM grade_subject_assignments WHERE id = $id");
    if ($del) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($conn)]);
    }
    exit;
}

// Default: return table
getAssignments($conn); 