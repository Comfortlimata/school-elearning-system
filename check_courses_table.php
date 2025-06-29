<?php
// Database connection
$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$data = mysqli_connect($host, $user, $password, $db);

if (!$data) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "<h2>Courses Table Structure</h2>";

// Check if courses table exists
$check_table = "SHOW TABLES LIKE 'courses'";
$table_exists = mysqli_query($data, $check_table);

if (mysqli_num_rows($table_exists) == 0) {
    echo "<p style='color: red;'>✗ Courses table does not exist.</p>";
} else {
    echo "<p style='color: green;'>✓ Courses table exists.</p>";
    
    // Show table structure
    $structure = mysqli_query($data, "DESCRIBE courses");
    echo "<h3>Current Columns:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($structure)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for program column specifically
    $check_program = "SHOW COLUMNS FROM courses LIKE 'program'";
    $program_exists = mysqli_query($data, $check_program);
    
    if (mysqli_num_rows($program_exists) > 0) {
        echo "<p style='color: orange;'>⚠ Program column still exists in courses table.</p>";
    } else {
        echo "<p style='color: green;'>✓ Program column does not exist in courses table (already removed).</p>";
    }
}

echo "<br><p><a href='add_courses.php'>Go to Add Courses</a></p>";
echo "<p><a href='adminhome.php'>Go to Admin Dashboard</a></p>";
?> 