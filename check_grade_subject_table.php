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

// Check if grade_subject_assignments table exists
$check_table = "SHOW TABLES LIKE 'grade_subject_assignments'";
$table_exists = mysqli_query($data, $check_table);

if (mysqli_num_rows($table_exists) == 0) {
    echo "<p style='color: orange;'>⚠️ Grade-subject assignments table does not exist. Creating it...</p>";
    
    // Create the table
    $create_table = "CREATE TABLE IF NOT EXISTS grade_subject_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        grade_id INT NOT NULL,
        subject_id INT NOT NULL,
        is_required BOOLEAN DEFAULT FALSE,
        is_elective BOOLEAN DEFAULT TRUE,
        credits INT DEFAULT 1,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (grade_id) REFERENCES grades(id) ON DELETE CASCADE,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
        UNIQUE KEY unique_grade_subject (grade_id, subject_id),
        INDEX idx_grade_id (grade_id),
        INDEX idx_subject_id (subject_id)
    )";
    
    if (mysqli_query($data, $create_table)) {
        echo "<p style='color: green;'>✅ Grade-subject assignments table created successfully!</p>";
        
        // Insert some sample data
        echo "<p>Inserting sample grade-subject assignments...</p>";
        
        // Get grades and subjects
        $grades = mysqli_query($data, "SELECT id, name FROM grades ORDER BY name");
        $subjects = mysqli_query($data, "SELECT id, name FROM subjects ORDER BY name");
        
        if ($grades && $subjects) {
            $inserted = 0;
            while ($grade = mysqli_fetch_assoc($grades)) {
                // Add core subjects to all grades
                $core_subjects = ['English Language', 'Mathematics', 'Religious Education'];
                
                foreach ($core_subjects as $subject_name) {
                    $subject_query = "SELECT id FROM subjects WHERE name = ?";
                    $stmt = mysqli_prepare($data, $subject_query);
                    mysqli_stmt_bind_param($stmt, "s", $subject_name);
                    mysqli_stmt_execute($stmt);
                    $subject_result = mysqli_stmt_get_result($stmt);
                    $subject = mysqli_fetch_assoc($subject_result);
                    
                    if ($subject) {
                        $insert_sql = "INSERT IGNORE INTO grade_subject_assignments (grade_id, subject_id, is_required, is_elective, credits) VALUES (?, ?, 1, 0, 1)";
                        $stmt = mysqli_prepare($data, $insert_sql);
                        mysqli_stmt_bind_param($stmt, "ii", $grade['id'], $subject['id']);
                        
                        if (mysqli_stmt_execute($stmt) && mysqli_affected_rows($data) > 0) {
                            $inserted++;
                        }
                    }
                }
            }
            
            echo "<p style='color: green;'>✅ Inserted $inserted sample assignments!</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Error creating table: " . mysqli_error($data) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Grade-subject assignments table already exists.</p>";
}

// Check if grades and subjects tables exist
$check_grades = "SHOW TABLES LIKE 'grades'";
$grades_exists = mysqli_query($data, $check_grades);

if (mysqli_num_rows($grades_exists) == 0) {
    echo "<p style='color: red;'>❌ Grades table does not exist. Please run create_school_grading_tables.sql first.</p>";
} else {
    echo "<p style='color: green;'>✅ Grades table exists.</p>";
}

$check_subjects = "SHOW TABLES LIKE 'subjects'";
$subjects_exists = mysqli_query($data, $check_subjects);

if (mysqli_num_rows($subjects_exists) == 0) {
    echo "<p style='color: red;'>❌ Subjects table does not exist. Please run create_school_grading_tables.sql first.</p>";
} else {
    echo "<p style='color: green;'>✅ Subjects table exists.</p>";
}

echo "<p><a href='admin_grade_subject_management.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Grade-Subject Management</a></p>";
?> 