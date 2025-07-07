<?php
header('Content-Type: application/json');
$grade_id = isset($_GET['grade_id']) ? (int)$_GET['grade_id'] : 0;
$section = isset($_GET['section']) ? $_GET['section'] : '';
$subjects = [];
if ($grade_id && $section) {
    $conn = mysqli_connect('localhost', 'root', '', 'schoolproject');
    if ($conn) {
        $section_esc = mysqli_real_escape_string($conn, $section);
        $sql = "SELECT DISTINCT s.id, s.name FROM grade_subject_assignments gsa JOIN subjects s ON gsa.subject_id = s.id WHERE gsa.grade_id = $grade_id AND gsa.section = '$section_esc' ORDER BY s.name";
        $result = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $subjects[] = $row;
        }
        mysqli_close($conn);
    }
}
echo json_encode($subjects); 