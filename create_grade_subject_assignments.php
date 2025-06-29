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

echo "<h2>Creating Grade-Subject Assignment System</h2>";

// 1. Create grade_subject_assignments table
$create_grade_subject_table = "CREATE TABLE IF NOT EXISTS grade_subject_assignments (
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

if (mysqli_query($data, $create_grade_subject_table)) {
    echo "<p style='color: green;'>✅ Grade-subject assignments table created successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating grade-subject assignments table: " . mysqli_error($data) . "</p>";
}

// 2. Ensure grades table exists and has data
$check_grades = "SELECT COUNT(*) as count FROM grades";
$grades_result = mysqli_query($data, $check_grades);
$grades_count = mysqli_fetch_assoc($grades_result)['count'];

if ($grades_count == 0) {
    echo "<p style='color: orange;'>⚠️ No grades found. Inserting default grades...</p>";
    
    $insert_grades = "INSERT IGNORE INTO grades (name) VALUES 
        ('8'), ('9'), ('10'), ('11'), ('12'), ('GCE')";
    
    if (mysqli_query($data, $insert_grades)) {
        echo "<p style='color: green;'>✅ Default grades inserted successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error inserting grades: " . mysqli_error($data) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Grades table already has $grades_count grades.</p>";
}

// 3. Ensure subjects table exists and has data
$check_subjects = "SELECT COUNT(*) as count FROM subjects";
$subjects_result = mysqli_query($data, $check_subjects);
$subjects_count = mysqli_fetch_assoc($subjects_result)['count'];

if ($subjects_count == 0) {
    echo "<p style='color: orange;'>⚠️ No subjects found. Inserting default subjects...</p>";
    
    $insert_subjects = "INSERT IGNORE INTO subjects (name) VALUES 
        ('English Language'),
        ('Mathematics'),
        ('Science'),
        ('Religious Education'),
        ('Moral Education'),
        ('Civic Education'),
        ('Physical Education'),
        ('Information and Communication Technology'),
        ('Physics'),
        ('Chemistry'),
        ('Biology'),
        ('Geography'),
        ('History'),
        ('Economics'),
        ('Business Studies'),
        ('Computer Science'),
        ('Art'),
        ('Music'),
        ('French'),
        ('Spanish')";
    
    if (mysqli_query($data, $insert_subjects)) {
        echo "<p style='color: green;'>✅ Default subjects inserted successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error inserting subjects: " . mysqli_error($data) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Subjects table already has $subjects_count subjects.</p>";
}

// 4. Insert some sample grade-subject assignments
echo "<h3>Inserting Sample Grade-Subject Assignments</h3>";

$sample_assignments = [
    // Grade 8
    ['grade' => '8', 'subject' => 'English Language', 'required' => true, 'credits' => 1],
    ['grade' => '8', 'subject' => 'Mathematics', 'required' => true, 'credits' => 1],
    ['grade' => '8', 'subject' => 'Science', 'required' => true, 'credits' => 1],
    ['grade' => '8', 'subject' => 'Religious Education', 'required' => true, 'credits' => 1],
    ['grade' => '8', 'subject' => 'Physical Education', 'required' => true, 'credits' => 1],
    ['grade' => '8', 'subject' => 'Information and Communication Technology', 'required' => false, 'credits' => 1],
    
    // Grade 9
    ['grade' => '9', 'subject' => 'English Language', 'required' => true, 'credits' => 1],
    ['grade' => '9', 'subject' => 'Mathematics', 'required' => true, 'credits' => 1],
    ['grade' => '9', 'subject' => 'Science', 'required' => true, 'credits' => 1],
    ['grade' => '9', 'subject' => 'Religious Education', 'required' => true, 'credits' => 1],
    ['grade' => '9', 'subject' => 'Physical Education', 'required' => true, 'credits' => 1],
    ['grade' => '9', 'subject' => 'Information and Communication Technology', 'required' => false, 'credits' => 1],
    ['grade' => '9', 'subject' => 'Geography', 'required' => false, 'credits' => 1],
    
    // Grade 10
    ['grade' => '10', 'subject' => 'English Language', 'required' => true, 'credits' => 1],
    ['grade' => '10', 'subject' => 'Mathematics', 'required' => true, 'credits' => 1],
    ['grade' => '10', 'subject' => 'Physics', 'required' => false, 'credits' => 1],
    ['grade' => '10', 'subject' => 'Chemistry', 'required' => false, 'credits' => 1],
    ['grade' => '10', 'subject' => 'Biology', 'required' => false, 'credits' => 1],
    ['grade' => '10', 'subject' => 'Religious Education', 'required' => true, 'credits' => 1],
    ['grade' => '10', 'subject' => 'Physical Education', 'required' => true, 'credits' => 1],
    ['grade' => '10', 'subject' => 'Information and Communication Technology', 'required' => false, 'credits' => 1],
    ['grade' => '10', 'subject' => 'Geography', 'required' => false, 'credits' => 1],
    ['grade' => '10', 'subject' => 'History', 'required' => false, 'credits' => 1],
    
    // Grade 11
    ['grade' => '11', 'subject' => 'English Language', 'required' => true, 'credits' => 1],
    ['grade' => '11', 'subject' => 'Mathematics', 'required' => true, 'credits' => 1],
    ['grade' => '11', 'subject' => 'Physics', 'required' => false, 'credits' => 1],
    ['grade' => '11', 'subject' => 'Chemistry', 'required' => false, 'credits' => 1],
    ['grade' => '11', 'subject' => 'Biology', 'required' => false, 'credits' => 1],
    ['grade' => '11', 'subject' => 'Economics', 'required' => false, 'credits' => 1],
    ['grade' => '11', 'subject' => 'Business Studies', 'required' => false, 'credits' => 1],
    ['grade' => '11', 'subject' => 'Computer Science', 'required' => false, 'credits' => 1],
    ['grade' => '11', 'subject' => 'Geography', 'required' => false, 'credits' => 1],
    ['grade' => '11', 'subject' => 'History', 'required' => false, 'credits' => 1],
    
    // Grade 12
    ['grade' => '12', 'subject' => 'English Language', 'required' => true, 'credits' => 1],
    ['grade' => '12', 'subject' => 'Mathematics', 'required' => true, 'credits' => 1],
    ['grade' => '12', 'subject' => 'Physics', 'required' => false, 'credits' => 1],
    ['grade' => '12', 'subject' => 'Chemistry', 'required' => false, 'credits' => 1],
    ['grade' => '12', 'subject' => 'Biology', 'required' => false, 'credits' => 1],
    ['grade' => '12', 'subject' => 'Economics', 'required' => false, 'credits' => 1],
    ['grade' => '12', 'subject' => 'Business Studies', 'required' => false, 'credits' => 1],
    ['grade' => '12', 'subject' => 'Computer Science', 'required' => false, 'credits' => 1],
    ['grade' => '12', 'subject' => 'Geography', 'required' => false, 'credits' => 1],
    ['grade' => '12', 'subject' => 'History', 'required' => false, 'credits' => 1],
    
    // GCE
    ['grade' => 'GCE', 'subject' => 'English Language', 'required' => true, 'credits' => 1],
    ['grade' => 'GCE', 'subject' => 'Mathematics', 'required' => true, 'credits' => 1],
    ['grade' => 'GCE', 'subject' => 'Physics', 'required' => false, 'credits' => 1],
    ['grade' => 'GCE', 'subject' => 'Chemistry', 'required' => false, 'credits' => 1],
    ['grade' => 'GCE', 'subject' => 'Biology', 'required' => false, 'credits' => 1],
    ['grade' => 'GCE', 'subject' => 'Economics', 'required' => false, 'credits' => 1],
    ['grade' => 'GCE', 'subject' => 'Business Studies', 'required' => false, 'credits' => 1],
    ['grade' => 'GCE', 'subject' => 'Computer Science', 'required' => false, 'credits' => 1],
    ['grade' => 'GCE', 'subject' => 'Geography', 'required' => false, 'credits' => 1],
    ['grade' => 'GCE', 'subject' => 'History', 'required' => false, 'credits' => 1],
    ['grade' => 'GCE', 'subject' => 'French', 'required' => false, 'credits' => 1],
    ['grade' => 'GCE', 'subject' => 'Spanish', 'required' => false, 'credits' => 1]
];

$inserted_count = 0;
foreach ($sample_assignments as $assignment) {
    $grade_name = $assignment['grade'];
    $subject_name = $assignment['subject'];
    $is_required = $assignment['required'] ? 1 : 0;
    $credits = $assignment['credits'];
    
    // Get grade_id and subject_id
    $grade_query = "SELECT id FROM grades WHERE name = ?";
    $stmt = mysqli_prepare($data, $grade_query);
    mysqli_stmt_bind_param($stmt, "s", $grade_name);
    mysqli_stmt_execute($stmt);
    $grade_result = mysqli_stmt_get_result($stmt);
    $grade_row = mysqli_fetch_assoc($grade_result);
    
    $subject_query = "SELECT id FROM subjects WHERE name = ?";
    $stmt = mysqli_prepare($data, $subject_query);
    mysqli_stmt_bind_param($stmt, "s", $subject_name);
    mysqli_stmt_execute($stmt);
    $subject_result = mysqli_stmt_get_result($stmt);
    $subject_row = mysqli_fetch_assoc($subject_result);
    
    if ($grade_row && $subject_row) {
        $grade_id = $grade_row['id'];
        $subject_id = $subject_row['id'];
        
        $insert_assignment = "INSERT IGNORE INTO grade_subject_assignments (grade_id, subject_id, is_required, is_elective, credits) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($data, $insert_assignment);
        mysqli_stmt_bind_param($stmt, "iiiii", $grade_id, $subject_id, $is_required, $is_elective, $credits);
        $is_elective = $is_required ? 0 : 1;
        
        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_affected_rows($data) > 0) {
                $inserted_count++;
                echo "<p>✅ Added: Grade $grade_name - $subject_name</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Error adding assignment: " . mysqli_error($data) . "</p>";
        }
    }
}

echo "<p style='color: green;'>✅ Successfully inserted $inserted_count grade-subject assignments!</p>";

echo "<h3>Setup Complete!</h3>";
echo "<p>The grade-subject assignment system is now ready. You can:</p>";
echo "<ul>";
echo "<li>Manage grade-subject assignments through the admin interface</li>";
echo "<li>Add new assignments</li>";
echo "<li>Edit existing assignments</li>";
echo "<li>Remove assignments</li>";
echo "<li>Set subjects as required or elective</li>";
echo "</ul>";

echo "<p><a href='admin_grade_subject_management.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Grade-Subject Management</a></p>";
?> 