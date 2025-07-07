<?php
// CONFIGURE THESE:
$teacher_id = 23; // <-- Set this to the new teacher's ID
$grade_ids = [1, 2, 3]; // <-- Array of grade_ids (e.g., 1 for grade 8, 2 for grade 9, etc.)
$sections = ['A', 'B']; // <-- Array of sections

$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$conn = mysqli_connect($host, $user, $password, $db);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$total_assigned = 0;
foreach ($grade_ids as $grade_id) {
    foreach ($sections as $section) {
        // Get all subject_ids for this grade and section
        $subjects_sql = "SELECT subject_id FROM grade_subject_assignments WHERE grade_id = ? AND section = ?";
        $stmt = mysqli_prepare($conn, $subjects_sql);
        mysqli_stmt_bind_param($stmt, "is", $grade_id, $section);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $assigned = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $subject_id = $row['subject_id'];
            // Insert assignment, ignore if already exists
            $insert_sql = "INSERT IGNORE INTO teacher_grade_subjects (teacher_id, grade_id, subject_id) VALUES (?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, "iii", $teacher_id, $grade_id, $subject_id);
            if (mysqli_stmt_execute($insert_stmt)) {
                $assigned++;
                $total_assigned++;
            }
        }
        echo "Assigned teacher $teacher_id to $assigned subjects for grade $grade_id, section $section.<br>";
    }
}
echo "<br>Total assignments made: $total_assigned<br>";

 