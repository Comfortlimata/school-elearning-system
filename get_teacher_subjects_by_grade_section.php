<?php
session_start();
header('Content-Type: application/json');
$teacher_id = isset($_SESSION['teacher_id']) ? (int)$_SESSION['teacher_id'] : 0;
$grade_id = isset($_GET['grade_id']) ? (int)$_GET['grade_id'] : 0;
$section = isset($_GET['section']) ? $_GET['section'] : '';
$subjects = [];
if ($teacher_id && $grade_id && $section) {
    $conn = mysqli_connect('localhost', 'root', '', 'schoolproject');
    if ($conn) {
        $section_esc = mysqli_real_escape_string($conn, $section);
        $sql = "SELECT DISTINCT s.id, s.name FROM teacher_grade_subjects tgs JOIN grade_subject_assignments gsa ON tgs.grade_id = gsa.grade_id AND tgs.subject_id = gsa.subject_id JOIN subjects s ON tgs.subject_id = s.id WHERE tgs.teacher_id = $teacher_id AND tgs.grade_id = $grade_id AND gsa.section = '$section_esc' ORDER BY s.name";
        $result = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $subjects[] = $row;
        }
        mysqli_close($conn);
    }
}
echo json_encode($subjects); 