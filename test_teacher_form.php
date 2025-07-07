<?php
$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$data = mysqli_connect($host, $user, $password, $db);

if (!$data) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "<h2>Teacher Registration Form Test</h2>";

// Check if teacher_grade_subjects table exists and its structure
echo "<h3>1. Checking teacher_grade_subjects table structure:</h3>";
$result = mysqli_query($data, "SHOW TABLES LIKE 'teacher_grade_subjects'");
if (mysqli_num_rows($result) > 0) {
    echo "✅ teacher_grade_subjects table exists.<br>";
    
    $structure = mysqli_query($data, "DESCRIBE teacher_grade_subjects");
    echo "<strong>Current structure:</strong><br>";
    while ($row = mysqli_fetch_assoc($structure)) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }
} else {
    echo "❌ teacher_grade_subjects table does not exist.<br>";
}

// Check if grade_subject_assignments table has data
echo "<h3>2. Checking grade_subject_assignments data:</h3>";
$assignments_count = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM grade_subject_assignments"))[0];
echo "Total grade-subject assignments: " . $assignments_count . "<br>";

if ($assignments_count > 0) {
    echo "<strong>Sample assignments:</strong><br>";
    $sample = mysqli_query($data, "SELECT g.name as grade, s.name as subject, gsa.section 
                                   FROM grade_subject_assignments gsa 
                                   JOIN grades g ON gsa.grade_id = g.id 
                                   JOIN subjects s ON gsa.subject_id = s.id 
                                   LIMIT 5");
    while ($row = mysqli_fetch_assoc($sample)) {
        echo "- Grade: " . $row['grade'] . ", Subject: " . $row['subject'] . ", Section: " . $row['section'] . "<br>";
    }
} else {
    echo "❌ No grade-subject assignments found. Need to run setup SQL.<br>";
}

// Check if there are any existing teacher assignments
echo "<h3>3. Checking existing teacher assignments:</h3>";
$teacher_assignments = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM teacher_grade_subjects"))[0];
echo "Total teacher assignments: " . $teacher_assignments . "<br>";

// Test the query that the form uses
echo "<h3>4. Testing the form's assignment query:</h3>";
$assignments_sql = "SELECT DISTINCT g.name as grade, s.name as subject, gsa.section 
                   FROM grade_subject_assignments gsa 
                   JOIN grades g ON gsa.grade_id = g.id 
                   JOIN subjects s ON gsa.subject_id = s.id 
                   ORDER BY g.name, s.name, gsa.section";
$assignments_result = mysqli_query($data, $assignments_sql);

if ($assignments_result && mysqli_num_rows($assignments_result) > 0) {
    echo "✅ Form query works. Found " . mysqli_num_rows($assignments_result) . " assignments.<br>";
    echo "<strong>First 3 assignments:</strong><br>";
    $count = 0;
    while ($row = mysqli_fetch_assoc($assignments_result) && $count < 3) {
        echo "- " . $row['subject'] . " (Grade " . $row['grade'] . ", Section " . $row['section'] . ")<br>";
        $count++;
    }
} else {
    echo "❌ Form query failed or returned no results.<br>";
    if ($assignments_result) {
        echo "Error: " . mysqli_error($data) . "<br>";
    }
}

echo "<h3>5. Recommendations:</h3>";
if ($assignments_count == 0) {
    echo "🔧 Run the setup SQL commands to populate grade_subject_assignments table.<br>";
}

$structure_check = mysqli_query($data, "DESCRIBE teacher_grade_subjects");
$has_grade_column = false;
while ($row = mysqli_fetch_assoc($structure_check)) {
    if ($row['Field'] == 'grade') {
        $has_grade_column = true;
        break;
    }
}

if (!$has_grade_column) {
    echo "🔧 teacher_grade_subjects table needs to be recreated with correct structure.<br>";
    echo "Run: DROP TABLE teacher_grade_subjects; CREATE TABLE teacher_grade_subjects (...)<br>";
} else {
    echo "✅ teacher_grade_subjects table structure looks correct.<br>";
}
?> 