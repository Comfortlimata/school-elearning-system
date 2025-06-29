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

echo "<h2>Adding Section Column to Grade-Subject Assignments</h2>";

// Check if section column exists
$check_section = "SHOW COLUMNS FROM grade_subject_assignments LIKE 'section'";
$section_exists = mysqli_query($data, $check_section);

if (mysqli_num_rows($section_exists) == 0) {
    echo "<p style='color: orange;'>⚠️ Section column does not exist. Adding it...</p>";
    
    // Add section column
    $add_section = "ALTER TABLE grade_subject_assignments ADD COLUMN section VARCHAR(10) AFTER grade_id";
    if (mysqli_query($data, $add_section)) {
        echo "<p style='color: green;'>✅ Section column added successfully!</p>";
        
        // Update unique constraint
        echo "<p>Updating unique constraint...</p>";
        
        // Drop old unique constraint if it exists
        $drop_constraint = "ALTER TABLE grade_subject_assignments DROP INDEX unique_grade_subject";
        mysqli_query($data, $drop_constraint);
        
        // Add new unique constraint with section
        $add_constraint = "ALTER TABLE grade_subject_assignments ADD UNIQUE KEY unique_grade_section_subject (grade_id, section, subject_id)";
        if (mysqli_query($data, $add_constraint)) {
            echo "<p style='color: green;'>✅ Unique constraint updated successfully!</p>";
        } else {
            echo "<p style='color: red;'>❌ Error updating constraint: " . mysqli_error($data) . "</p>";
        }
        
        // Set default section 'A' for existing records
        $update_existing = "UPDATE grade_subject_assignments SET section = 'A' WHERE section IS NULL OR section = ''";
        if (mysqli_query($data, $update_existing)) {
            $affected = mysqli_affected_rows($data);
            echo "<p style='color: green;'>✅ Updated $affected existing records with default section 'A'</p>";
        } else {
            echo "<p style='color: red;'>❌ Error updating existing records: " . mysqli_error($data) . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Error adding section column: " . mysqli_error($data) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Section column already exists.</p>";
}

// Show current table structure
echo "<h3>Current Table Structure:</h3>";
$structure = mysqli_query($data, "DESCRIBE grade_subject_assignments");
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

// Show sample data
echo "<h3>Sample Data:</h3>";
$sample_data = mysqli_query($data, "SELECT gsa.*, g.name as grade_name, s.name as subject_name 
                                   FROM grade_subject_assignments gsa 
                                   JOIN grades g ON gsa.grade_id = g.id 
                                   JOIN subjects s ON gsa.subject_id = s.id 
                                   ORDER BY g.name, gsa.section, s.name 
                                   LIMIT 10");

if (mysqli_num_rows($sample_data) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Grade</th><th>Section</th><th>Subject</th><th>Required</th><th>Elective</th><th>Credits</th></tr>";
    
    while ($row = mysqli_fetch_assoc($sample_data)) {
        echo "<tr>";
        echo "<td>" . $row['grade_name'] . "</td>";
        echo "<td>" . ($row['section'] ?: 'A') . "</td>";
        echo "<td>" . $row['subject_name'] . "</td>";
        echo "<td>" . ($row['is_required'] ? 'Yes' : 'No') . "</td>";
        echo "<td>" . ($row['is_elective'] ? 'Yes' : 'No') . "</td>";
        echo "<td>" . $row['credits'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>No data found in grade_subject_assignments table.</p>";
}

echo "<p><a href='admin_grade_subject_management.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Grade-Subject Management</a></p>";
?> 