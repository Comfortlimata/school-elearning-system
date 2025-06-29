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

echo "<h2>Setting up Student Grade-Section System</h2>";

// 1. Check if section column exists in students table
$check_section = "SHOW COLUMNS FROM students LIKE 'section'";
$section_exists = mysqli_query($data, $check_section);

if (mysqli_num_rows($section_exists) == 0) {
    echo "<p style='color: orange;'>⚠️ Section column does not exist in students table. Adding it...</p>";
    
    $add_section = "ALTER TABLE students ADD COLUMN section VARCHAR(10) AFTER grade_id";
    if (mysqli_query($data, $add_section)) {
        echo "<p style='color: green;'>✅ Section column added to students table successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error adding section column: " . mysqli_error($data) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Section column already exists in students table.</p>";
}

// 2. Check if grade_id column exists in students table
$check_grade_id = "SHOW COLUMNS FROM students LIKE 'grade_id'";
$grade_id_exists = mysqli_query($data, $check_grade_id);

if (mysqli_num_rows($grade_id_exists) == 0) {
    echo "<p style='color: orange;'>⚠️ Grade ID column does not exist in students table. Adding it...</p>";
    
    $add_grade_id = "ALTER TABLE students ADD COLUMN grade_id INT AFTER password";
    if (mysqli_query($data, $add_grade_id)) {
        echo "<p style='color: green;'>✅ Grade ID column added to students table successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error adding grade_id column: " . mysqli_error($data) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Grade ID column already exists in students table.</p>";
}

// 3. Ensure grades table exists and has data
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

// 4. Ensure subjects table exists and has data
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
        ('Physical Education'),
        ('Information and Communication Technology'),
        ('Geography'),
        ('History'),
        ('Physics'),
        ('Chemistry'),
        ('Biology'),
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

// 5. Ensure grade_subject_assignments table exists and has section column
$check_gsa_section = "SHOW COLUMNS FROM grade_subject_assignments LIKE 'section'";
$gsa_section_exists = mysqli_query($data, $check_gsa_section);

if (mysqli_num_rows($gsa_section_exists) == 0) {
    echo "<p style='color: orange;'>⚠️ Section column does not exist in grade_subject_assignments table. Adding it...</p>";
    
    $add_gsa_section = "ALTER TABLE grade_subject_assignments ADD COLUMN section VARCHAR(10) AFTER grade_id";
    if (mysqli_query($data, $add_gsa_section)) {
        echo "<p style='color: green;'>✅ Section column added to grade_subject_assignments table successfully!</p>";
        
        // Update unique constraint
        $drop_constraint = "ALTER TABLE grade_subject_assignments DROP INDEX unique_grade_subject";
        mysqli_query($data, $drop_constraint);
        
        $add_constraint = "ALTER TABLE grade_subject_assignments ADD UNIQUE KEY unique_grade_section_subject (grade_id, section, subject_id)";
        if (mysqli_query($data, $add_constraint)) {
            echo "<p style='color: green;'>✅ Unique constraint updated successfully!</p>";
        }
        
        // Set default section for existing records
        $update_existing = "UPDATE grade_subject_assignments SET section = 'A' WHERE section IS NULL OR section = ''";
        if (mysqli_query($data, $update_existing)) {
            $affected = mysqli_affected_rows($data);
            echo "<p style='color: green;'>✅ Updated $affected existing records with default section 'A'</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Error adding section column: " . mysqli_error($data) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Section column already exists in grade_subject_assignments table.</p>";
}

// 6. Insert sample grade-section-subject assignments
echo "<p>Inserting sample grade-section-subject assignments...</p>";

$sample_assignments = [
    // Grade 8A
    ['grade' => '8', 'section' => 'A', 'subject' => 'English Language', 'required' => true, 'credits' => 1, 'description' => 'Core English for Grade 8A'],
    ['grade' => '8', 'section' => 'A', 'subject' => 'Mathematics', 'required' => true, 'credits' => 1, 'description' => 'Core Mathematics for Grade 8A'],
    ['grade' => '8', 'section' => 'A', 'subject' => 'Science', 'required' => true, 'credits' => 1, 'description' => 'Core Science for Grade 8A'],
    ['grade' => '8', 'section' => 'A', 'subject' => 'Religious Education', 'required' => true, 'credits' => 1, 'description' => 'Religious Education for Grade 8A'],
    ['grade' => '8', 'section' => 'A', 'subject' => 'Physical Education', 'required' => true, 'credits' => 1, 'description' => 'Physical Education for Grade 8A'],
    ['grade' => '8', 'section' => 'A', 'subject' => 'Information and Communication Technology', 'required' => false, 'credits' => 1, 'description' => 'ICT for Grade 8A'],
    ['grade' => '8', 'section' => 'A', 'subject' => 'Geography', 'required' => false, 'credits' => 1, 'description' => 'Geography for Grade 8A'],
    
    // Grade 8B
    ['grade' => '8', 'section' => 'B', 'subject' => 'English Language', 'required' => true, 'credits' => 1, 'description' => 'Core English for Grade 8B'],
    ['grade' => '8', 'section' => 'B', 'subject' => 'Mathematics', 'required' => true, 'credits' => 1, 'description' => 'Core Mathematics for Grade 8B'],
    ['grade' => '8', 'section' => 'B', 'subject' => 'Science', 'required' => true, 'credits' => 1, 'description' => 'Core Science for Grade 8B'],
    ['grade' => '8', 'section' => 'B', 'subject' => 'Religious Education', 'required' => true, 'credits' => 1, 'description' => 'Religious Education for Grade 8B'],
    ['grade' => '8', 'section' => 'B', 'subject' => 'Physical Education', 'required' => true, 'credits' => 1, 'description' => 'Physical Education for Grade 8B'],
    ['grade' => '8', 'section' => 'B', 'subject' => 'Information and Communication Technology', 'required' => false, 'credits' => 1, 'description' => 'ICT for Grade 8B'],
    ['grade' => '8', 'section' => 'B', 'subject' => 'History', 'required' => false, 'credits' => 1, 'description' => 'History for Grade 8B'],
    
    // Grade 8C
    ['grade' => '8', 'section' => 'C', 'subject' => 'English Language', 'required' => true, 'credits' => 1, 'description' => 'Core English for Grade 8C'],
    ['grade' => '8', 'section' => 'C', 'subject' => 'Mathematics', 'required' => true, 'credits' => 1, 'description' => 'Core Mathematics for Grade 8C'],
    ['grade' => '8', 'section' => 'C', 'subject' => 'Science', 'required' => true, 'credits' => 1, 'description' => 'Core Science for Grade 8C'],
    ['grade' => '8', 'section' => 'C', 'subject' => 'Religious Education', 'required' => true, 'credits' => 1, 'description' => 'Religious Education for Grade 8C'],
    ['grade' => '8', 'section' => 'C', 'subject' => 'Physical Education', 'required' => true, 'credits' => 1, 'description' => 'Physical Education for Grade 8C'],
    ['grade' => '8', 'section' => 'C', 'subject' => 'Information and Communication Technology', 'required' => false, 'credits' => 1, 'description' => 'ICT for Grade 8C'],
    ['grade' => '8', 'section' => 'C', 'subject' => 'Art', 'required' => false, 'credits' => 1, 'description' => 'Art for Grade 8C'],
    
    // Grade 9A
    ['grade' => '9', 'section' => 'A', 'subject' => 'English Language', 'required' => true, 'credits' => 1, 'description' => 'Core English for Grade 9A'],
    ['grade' => '9', 'section' => 'A', 'subject' => 'Mathematics', 'required' => true, 'credits' => 1, 'description' => 'Core Mathematics for Grade 9A'],
    ['grade' => '9', 'section' => 'A', 'subject' => 'Science', 'required' => true, 'credits' => 1, 'description' => 'Core Science for Grade 9A'],
    ['grade' => '9', 'section' => 'A', 'subject' => 'Religious Education', 'required' => true, 'credits' => 1, 'description' => 'Religious Education for Grade 9A'],
    ['grade' => '9', 'section' => 'A', 'subject' => 'Physical Education', 'required' => true, 'credits' => 1, 'description' => 'Physical Education for Grade 9A'],
    ['grade' => '9', 'section' => 'A', 'subject' => 'Information and Communication Technology', 'required' => false, 'credits' => 1, 'description' => 'ICT for Grade 9A'],
    ['grade' => '9', 'section' => 'A', 'subject' => 'Geography', 'required' => false, 'credits' => 1, 'description' => 'Geography for Grade 9A'],
    ['grade' => '9', 'section' => 'A', 'subject' => 'Art', 'required' => false, 'credits' => 1, 'description' => 'Art for Grade 9A'],
    
    // Grade 10A
    ['grade' => '10', 'section' => 'A', 'subject' => 'English Language', 'required' => true, 'credits' => 1, 'description' => 'Core English for Grade 10A'],
    ['grade' => '10', 'section' => 'A', 'subject' => 'Mathematics', 'required' => true, 'credits' => 1, 'description' => 'Core Mathematics for Grade 10A'],
    ['grade' => '10', 'section' => 'A', 'subject' => 'Physics', 'required' => false, 'credits' => 1, 'description' => 'Physics for Grade 10A'],
    ['grade' => '10', 'section' => 'A', 'subject' => 'Chemistry', 'required' => false, 'credits' => 1, 'description' => 'Chemistry for Grade 10A'],
    ['grade' => '10', 'section' => 'A', 'subject' => 'Biology', 'required' => false, 'credits' => 1, 'description' => 'Biology for Grade 10A'],
    ['grade' => '10', 'section' => 'A', 'subject' => 'Religious Education', 'required' => true, 'credits' => 1, 'description' => 'Religious Education for Grade 10A'],
    ['grade' => '10', 'section' => 'A', 'subject' => 'Physical Education', 'required' => true, 'credits' => 1, 'description' => 'Physical Education for Grade 10A'],
    ['grade' => '10', 'section' => 'A', 'subject' => 'Computer Science', 'required' => false, 'credits' => 1, 'description' => 'Computer Science for Grade 10A']
];

$inserted_count = 0;
foreach ($sample_assignments as $assignment) {
    $grade_query = "SELECT id FROM grades WHERE name = ?";
    $stmt = mysqli_prepare($data, $grade_query);
    mysqli_stmt_bind_param($stmt, "s", $assignment['grade']);
    mysqli_stmt_execute($stmt);
    $grade_result = mysqli_stmt_get_result($stmt);
    $grade = mysqli_fetch_assoc($grade_result);
    
    $subject_query = "SELECT id FROM subjects WHERE name = ?";
    $stmt = mysqli_prepare($data, $subject_query);
    mysqli_stmt_bind_param($stmt, "s", $assignment['subject']);
    mysqli_stmt_execute($stmt);
    $subject_result = mysqli_stmt_get_result($stmt);
    $subject = mysqli_fetch_assoc($subject_result);
    
    if ($grade && $subject) {
        $insert_sql = "INSERT IGNORE INTO grade_subject_assignments (grade_id, section, subject_id, is_required, is_elective, credits, description) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($data, $insert_sql);
        $is_required = $assignment['required'] ? 1 : 0;
        $is_elective = $assignment['required'] ? 0 : 1;
        mysqli_stmt_bind_param($stmt, "isiissi", $grade['id'], $assignment['section'], $subject['id'], $is_required, $is_elective, $assignment['credits'], $assignment['description']);
        
        if (mysqli_stmt_execute($stmt) && mysqli_affected_rows($data) > 0) {
            $inserted_count++;
        }
    }
}

echo "<p style='color: green;'>✅ Inserted $inserted_count sample grade-section-subject assignments!</p>";

// 7. Create sample students with grade and section
echo "<p>Creating sample students with grade and section assignments...</p>";

$sample_students = [
    ['username' => 'student8a1', 'email' => 'student8a1@school.com', 'password' => 'password123', 'grade' => '8', 'section' => 'A', 'full_name' => 'John Doe', 'phone' => '1234567890', 'address' => '123 Main St', 'gender' => 'Male'],
    ['username' => 'student8a2', 'email' => 'student8a2@school.com', 'password' => 'password123', 'grade' => '8', 'section' => 'A', 'full_name' => 'Jane Smith', 'phone' => '1234567891', 'address' => '124 Main St', 'gender' => 'Female'],
    ['username' => 'student8b1', 'email' => 'student8b1@school.com', 'password' => 'password123', 'grade' => '8', 'section' => 'B', 'full_name' => 'Mike Johnson', 'phone' => '1234567892', 'address' => '125 Main St', 'gender' => 'Male'],
    ['username' => 'student8c1', 'email' => 'student8c1@school.com', 'password' => 'password123', 'grade' => '8', 'section' => 'C', 'full_name' => 'Sarah Wilson', 'phone' => '1234567893', 'address' => '126 Main St', 'gender' => 'Female'],
    ['username' => 'student9a1', 'email' => 'student9a1@school.com', 'password' => 'password123', 'grade' => '9', 'section' => 'A', 'full_name' => 'Tom Brown', 'phone' => '1234567894', 'address' => '127 Main St', 'gender' => 'Male'],
    ['username' => 'student10a1', 'email' => 'student10a1@school.com', 'password' => 'password123', 'grade' => '10', 'section' => 'A', 'full_name' => 'Lisa Davis', 'phone' => '1234567895', 'address' => '128 Main St', 'gender' => 'Female']
];

$students_created = 0;
foreach ($sample_students as $student_data) {
    // Check if student already exists
    $check_sql = "SELECT id FROM students WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($data, $check_sql);
    mysqli_stmt_bind_param($stmt, "ss", $student_data['username'], $student_data['email']);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($check_result) == 0) {
        // Get grade_id
        $grade_query = "SELECT id FROM grades WHERE name = ?";
        $stmt = mysqli_prepare($data, $grade_query);
        mysqli_stmt_bind_param($stmt, "s", $student_data['grade']);
        mysqli_stmt_execute($stmt);
        $grade_result = mysqli_stmt_get_result($stmt);
        $grade = mysqli_fetch_assoc($grade_result);
        
        if ($grade) {
            $hashed_password = password_hash($student_data['password'], PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO students (username, email, password, grade_id, section, full_name, phone, address, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($data, $insert_sql);
            mysqli_stmt_bind_param($stmt, "sssisssss", $student_data['username'], $student_data['email'], $hashed_password, $grade['id'], $student_data['section'], $student_data['full_name'], $student_data['phone'], $student_data['address'], $student_data['gender']);
            
            if (mysqli_stmt_execute($stmt)) {
                $students_created++;
            }
        }
    }
}

echo "<p style='color: green;'>✅ Created $students_created sample students!</p>";

// 8. Show summary
echo "<h3>Setup Summary:</h3>";
echo "<ul>";
echo "<li>✅ Students table has grade_id and section columns</li>";
echo "<li>✅ Grades table has " . mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM grades"))[0] . " grades</li>";
echo "<li>✅ Subjects table has " . mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM subjects"))[0] . " subjects</li>";
echo "<li>✅ Grade-subject assignments table has " . mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM grade_subject_assignments"))[0] . " assignments</li>";
echo "<li>✅ Students table has " . mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM students"))[0] . " students</li>";
echo "</ul>";

echo "<h3>Test Students:</h3>";
echo "<p>You can now test the system with these sample students:</p>";
echo "<ul>";
foreach ($sample_students as $student) {
    echo "<li><strong>{$student['username']}</strong> - Grade {$student['grade']} Section {$student['section']} - Password: {$student['password']}</li>";
}
echo "</ul>";

echo "<p style='color: green; font-weight: bold;'>🎉 Setup completed successfully! Students can now view courses based on their grade and section.</p>";
?> 