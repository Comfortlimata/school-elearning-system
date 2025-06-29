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

echo "<h2>Fixing Courses Table Structure</h2>";

// Check if courses table exists
$check_table = "SHOW TABLES LIKE 'courses'";
$table_exists = mysqli_query($data, $check_table);

if (mysqli_num_rows($table_exists) == 0) {
    echo "<p style='color: red;'>✗ Courses table does not exist. Creating it...</p>";
    
    $create_table = "CREATE TABLE courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_name VARCHAR(255) NOT NULL,
        course_code VARCHAR(50) NOT NULL,
        course_description TEXT,
        document_path VARCHAR(500),
        credits INT DEFAULT 3,
        duration INT DEFAULT 16,
        grade_id INT,
        section VARCHAR(10),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (mysqli_query($data, $create_table)) {
        echo "<p style='color: green;'>✓ Courses table created successfully with all required columns.</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating courses table: " . mysqli_error($data) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Courses table exists.</p>";
    
    // Check if section column exists
    $check_section = "SHOW COLUMNS FROM courses LIKE 'section'";
    $section_exists = mysqli_query($data, $check_section);
    
    if (mysqli_num_rows($section_exists) == 0) {
        echo "<p style='color: orange;'>⚠ Section column missing. Adding it...</p>";
        
        $add_section = "ALTER TABLE courses ADD COLUMN section VARCHAR(10) AFTER grade_id";
        if (mysqli_query($data, $add_section)) {
            echo "<p style='color: green;'>✓ Section column added successfully.</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding section column: " . mysqli_error($data) . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✓ Section column already exists.</p>";
    }
    
    // Check if grade_id column exists
    $check_grade_id = "SHOW COLUMNS FROM courses LIKE 'grade_id'";
    $grade_id_exists = mysqli_query($data, $check_grade_id);
    
    if (mysqli_num_rows($grade_id_exists) == 0) {
        echo "<p style='color: orange;'>⚠ Grade ID column missing. Adding it...</p>";
        
        $add_grade_id = "ALTER TABLE courses ADD COLUMN grade_id INT AFTER duration";
        if (mysqli_query($data, $add_grade_id)) {
            echo "<p style='color: green;'>✓ Grade ID column added successfully.</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding grade_id column: " . mysqli_error($data) . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✓ Grade ID column already exists.</p>";
    }
}

// Show final table structure
echo "<h3>Final Courses Table Structure:</h3>";
$structure = mysqli_query($data, "DESCRIBE courses");
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

echo "<br><p style='color: green;'><strong>Courses table is now ready!</strong></p>";
echo "<p><a href='add_courses.php'>Go to Add Courses</a></p>";
echo "<p><a href='adminhome.php'>Go to Admin Dashboard</a></p>";
?> 