<?php
// Database connection
$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Remove All Subjects - Fresh Start</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 2rem; background-color: #f8f9fa; }
        .container { max-width: 800px; }
        .alert { margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class='container'>
        <h1 class='mb-4 text-danger'><i class='fas fa-exclamation-triangle'></i> Remove All Subjects</h1>
        <div class='alert alert-warning'>
            <strong>Warning:</strong> This will permanently delete all subjects and related data from your database.
            This action cannot be undone!
        </div>";

// Check if user confirmed the action
if (isset($_POST['confirm_remove']) && $_POST['confirm_remove'] === 'yes') {
    echo "<div class='alert alert-info'>Starting removal process...</div>";
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        $deleted_counts = [];
        
        // 1. Delete from teacher_grade_subjects (teacher assignments)
        $delete_teacher_assignments = "DELETE FROM teacher_grade_subjects";
        if (mysqli_query($conn, $delete_teacher_assignments)) {
            $deleted_counts['teacher_assignments'] = mysqli_affected_rows($conn);
            echo "<div class='alert alert-success'>✅ Deleted " . $deleted_counts['teacher_assignments'] . " teacher grade-subject assignments</div>";
        } else {
            throw new Exception("Error deleting teacher assignments: " . mysqli_error($conn));
        }
        
        // 2. Delete from student_teacher_subject (student-teacher-subject mappings)
        $delete_student_assignments = "DELETE FROM student_teacher_subject";
        if (mysqli_query($conn, $delete_student_assignments)) {
            $deleted_counts['student_assignments'] = mysqli_affected_rows($conn);
            echo "<div class='alert alert-success'>✅ Deleted " . $deleted_counts['student_assignments'] . " student-teacher-subject assignments</div>";
        } else {
            throw new Exception("Error deleting student assignments: " . mysqli_error($conn));
        }
        
        // 3. Delete from grade_subject_assignments (grade-subject mappings)
        $delete_grade_assignments = "DELETE FROM grade_subject_assignments";
        if (mysqli_query($conn, $delete_grade_assignments)) {
            $deleted_counts['grade_assignments'] = mysqli_affected_rows($conn);
            echo "<div class='alert alert-success'>✅ Deleted " . $deleted_counts['grade_assignments'] . " grade-subject assignments</div>";
        } else {
            throw new Exception("Error deleting grade assignments: " . mysqli_error($conn));
        }
        
        // 4. Delete from courses table where course_name matches subject names
        $delete_courses = "DELETE c FROM courses c 
                          INNER JOIN subjects s ON c.course_name LIKE CONCAT('%', s.name, '%')";
        if (mysqli_query($conn, $delete_courses)) {
            $deleted_counts['courses'] = mysqli_affected_rows($conn);
            echo "<div class='alert alert-success'>✅ Deleted " . $deleted_counts['courses'] . " courses related to subjects</div>";
        } else {
            throw new Exception("Error deleting courses: " . mysqli_error($conn));
        }
        
        // 5. Delete from course_assignments where course_id no longer exists
        $delete_course_assignments = "DELETE ca FROM course_assignments ca 
                                     LEFT JOIN courses c ON ca.course_id = c.id 
                                     WHERE c.id IS NULL";
        if (mysqli_query($conn, $delete_course_assignments)) {
            $deleted_counts['course_assignments'] = mysqli_affected_rows($conn);
            echo "<div class='alert alert-success'>✅ Deleted " . $deleted_counts['course_assignments'] . " orphaned course assignments</div>";
        } else {
            throw new Exception("Error deleting course assignments: " . mysqli_error($conn));
        }
        
        // 6. Delete from course_materials where course_id no longer exists
        $delete_course_materials = "DELETE cm FROM course_materials cm 
                                   LEFT JOIN courses c ON cm.course_id = c.id 
                                   WHERE c.id IS NULL";
        if (mysqli_query($conn, $delete_course_materials)) {
            $deleted_counts['course_materials'] = mysqli_affected_rows($conn);
            echo "<div class='alert alert-success'>✅ Deleted " . $deleted_counts['course_materials'] . " orphaned course materials</div>";
        } else {
            throw new Exception("Error deleting course materials: " . mysqli_error($conn));
        }
        
        // 7. Delete from student_schedule where subject_id no longer exists
        $delete_student_schedule = "DELETE ss FROM student_schedule ss 
                                   LEFT JOIN subjects s ON ss.subject_id = s.id 
                                   WHERE s.id IS NULL";
        if (mysqli_query($conn, $delete_student_schedule)) {
            $deleted_counts['student_schedule'] = mysqli_affected_rows($conn);
            echo "<div class='alert alert-success'>✅ Deleted " . $deleted_counts['student_schedule'] . " orphaned student schedule entries</div>";
        } else {
            throw new Exception("Error deleting student schedule: " . mysqli_error($conn));
        }
        
        // 8. Finally, delete all subjects
        $delete_subjects = "DELETE FROM subjects";
        if (mysqli_query($conn, $delete_subjects)) {
            $deleted_counts['subjects'] = mysqli_affected_rows($conn);
            echo "<div class='alert alert-success'>✅ Deleted " . $deleted_counts['subjects'] . " subjects</div>";
        } else {
            throw new Exception("Error deleting subjects: " . mysqli_error($conn));
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Show summary
        $total_deleted = array_sum($deleted_counts);
        echo "<div class='alert alert-success'>
                <h4>🎉 Cleanup Complete!</h4>
                <p>Successfully removed <strong>$total_deleted</strong> records:</p>
                <ul>";
        foreach ($deleted_counts as $type => $count) {
            echo "<li>" . ucwords(str_replace('_', ' ', $type)) . ": $count</li>";
        }
        echo "</ul>
                <p><strong>Your database is now clean and ready for a fresh start!</strong></p>
              </div>";
        
        // Show remaining data
        echo "<div class='alert alert-info'>
                <h5>Remaining Data:</h5>";
        
        $grades_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM grades"))[0];
        $teachers_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM teacher"))[0];
        $students_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM students"))[0];
        
        echo "<ul>
                <li>Grades: $grades_count</li>
                <li>Teachers: $teachers_count</li>
                <li>Students: $students_count</li>
              </ul>
              </div>";
        
    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($conn);
        echo "<div class='alert alert-danger'>
                <h4>❌ Error occurred!</h4>
                <p>" . $e->getMessage() . "</p>
                <p>All changes have been rolled back. No data was deleted.</p>
              </div>";
    }
    
} else {
    // Show confirmation form
    echo "<div class='alert alert-danger'>
            <h4>⚠️ Confirmation Required</h4>
            <p>This action will permanently delete:</p>
            <ul>
                <li>All subjects from the subjects table</li>
                <li>All grade-subject assignments</li>
                <li>All teacher-subject assignments</li>
                <li>All student-teacher-subject mappings</li>
                <li>All courses related to subjects</li>
                <li>All course assignments and materials for deleted courses</li>
                <li>All schedule entries for deleted subjects</li>
            </ul>
            <p><strong>This action cannot be undone!</strong></p>
          </div>";
    
    echo "<form method='POST' class='mb-4'>
            <div class='form-check mb-3'>
                <input class='form-check-input' type='checkbox' id='confirm' required>
                <label class='form-check-label' for='confirm'>
                    I understand that this will permanently delete all subject-related data and cannot be undone
                </label>
            </div>
            <input type='hidden' name='confirm_remove' value='yes'>
            <button type='submit' class='btn btn-danger btn-lg' onclick='return confirm(\"Are you absolutely sure? This will delete ALL subjects and related data!\")'>
                <i class='fas fa-trash'></i> Remove All Subjects
            </button>
            <a href='adminhome.php' class='btn btn-secondary btn-lg ms-2'>
                <i class='fas fa-arrow-left'></i> Cancel
            </a>
          </form>";
    
    // Show current data summary
    echo "<div class='alert alert-info'>
            <h5>Current Data Summary:</h5>";
    
    $subjects_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM subjects"))[0];
    $grade_assignments_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM grade_subject_assignments"))[0];
    $teacher_assignments_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM teacher_grade_subjects"))[0];
    $student_assignments_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM student_teacher_subject"))[0];
    $courses_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM courses"))[0];
    
    echo "<ul>
            <li>Subjects: $subjects_count</li>
            <li>Grade-Subject Assignments: $grade_assignments_count</li>
            <li>Teacher Assignments: $teacher_assignments_count</li>
            <li>Student Assignments: $student_assignments_count</li>
            <li>Courses: $courses_count</li>
          </ul>
          </div>";
}

echo "</div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js'></script>
    <script src='https://kit.fontawesome.com/your-fontawesome-kit.js'></script>
</body>
</html>";

mysqli_close($conn);
?> 