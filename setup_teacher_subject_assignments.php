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

echo "<h2>Setting up Teacher-Subject Assignments</h2>";

// 1. Check if teacher_grade_subjects table exists
$check_table = "SHOW TABLES LIKE 'teacher_grade_subjects'";
$table_exists = mysqli_query($data, $check_table);

if (mysqli_num_rows($table_exists) == 0) {
    echo "<p style='color: orange;'>⚠️ Teacher-grade-subjects table does not exist. Creating it...</p>";
    
    $create_table = "CREATE TABLE IF NOT EXISTS teacher_grade_subjects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        teacher_id INT NOT NULL,
        grade_id INT NOT NULL,
        subject_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (teacher_id) REFERENCES teacher(id) ON DELETE CASCADE,
        FOREIGN KEY (grade_id) REFERENCES grades(id) ON DELETE CASCADE,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
        UNIQUE KEY unique_teacher_grade_subject (teacher_id, grade_id, subject_id),
        INDEX idx_teacher_id (teacher_id),
        INDEX idx_grade_id (grade_id),
        INDEX idx_subject_id (subject_id)
    )";
    
    if (mysqli_query($data, $create_table)) {
        echo "<p style='color: green;'>✅ Teacher-grade-subjects table created successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error creating table: " . mysqli_error($data) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Teacher-grade-subjects table already exists.</p>";
}

// 2. Get existing teachers
$teachers_sql = "SELECT id, name, specialization FROM teacher ORDER BY name";
$teachers_result = mysqli_query($data, $teachers_sql);

if (!$teachers_result) {
    echo "<p style='color: red;'>❌ Error fetching teachers: " . mysqli_error($data) . "</p>";
    exit();
}

$teachers = [];
while ($teacher = mysqli_fetch_assoc($teachers_result)) {
    $teachers[] = $teacher;
}

if (empty($teachers)) {
    echo "<p style='color: orange;'>⚠️ No teachers found. Please add teachers first.</p>";
    exit();
}

// 3. Get existing grades and subjects
$grades_sql = "SELECT id, name FROM grades ORDER BY name";
$grades_result = mysqli_query($data, $grades_sql);
$grades = [];
while ($grade = mysqli_fetch_assoc($grades_result)) {
    $grades[] = $grade;
}

$subjects_sql = "SELECT id, name FROM subjects ORDER BY name";
$subjects_result = mysqli_query($data, $subjects_sql);
$subjects = [];
while ($subject = mysqli_fetch_assoc($subjects_result)) {
    $subjects[] = $subject;
}

echo "<p>Found " . count($teachers) . " teachers, " . count($grades) . " grades, and " . count($subjects) . " subjects.</p>";

// 4. Create sample teacher-subject assignments based on specialization
echo "<h3>Creating Teacher-Subject Assignments</h3>";

$assignments_created = 0;

foreach ($teachers as $teacher) {
    $specialization = strtolower($teacher['specialization'] ?? '');
    
    // Define subject assignments based on teacher specialization
    $assigned_subjects = [];
    
    if (strpos($specialization, 'english') !== false || strpos($specialization, 'language') !== false) {
        $assigned_subjects = ['English Language', 'Literature'];
    } elseif (strpos($specialization, 'math') !== false) {
        $assigned_subjects = ['Mathematics'];
    } elseif (strpos($specialization, 'science') !== false) {
        $assigned_subjects = ['Science', 'Physics', 'Chemistry', 'Biology'];
    } elseif (strpos($specialization, 'computer') !== false || strpos($specialization, 'ict') !== false) {
        $assigned_subjects = ['Information and Communication Technology', 'Computer Science'];
    } elseif (strpos($specialization, 'physics') !== false) {
        $assigned_subjects = ['Physics'];
    } elseif (strpos($specialization, 'chemistry') !== false) {
        $assigned_subjects = ['Chemistry'];
    } elseif (strpos($specialization, 'biology') !== false) {
        $assigned_subjects = ['Biology'];
    } elseif (strpos($specialization, 'economics') !== false) {
        $assigned_subjects = ['Economics', 'Business Studies'];
    } elseif (strpos($specialization, 'business') !== false) {
        $assigned_subjects = ['Business Studies', 'Economics'];
    } elseif (strpos($specialization, 'geography') !== false) {
        $assigned_subjects = ['Geography'];
    } elseif (strpos($specialization, 'history') !== false) {
        $assigned_subjects = ['History'];
    } elseif (strpos($specialization, 'religious') !== false) {
        $assigned_subjects = ['Religious Education'];
    } elseif (strpos($specialization, 'physical') !== false || strpos($specialization, 'pe') !== false) {
        $assigned_subjects = ['Physical Education'];
    } elseif (strpos($specialization, 'art') !== false) {
        $assigned_subjects = ['Art'];
    } elseif (strpos($specialization, 'music') !== false) {
        $assigned_subjects = ['Music'];
    } elseif (strpos($specialization, 'french') !== false) {
        $assigned_subjects = ['French'];
    } elseif (strpos($specialization, 'spanish') !== false) {
        $assigned_subjects = ['Spanish'];
    } else {
        // Default assignment for teachers without specific specialization
        $assigned_subjects = ['English Language', 'Mathematics', 'Science'];
    }
    
    echo "<p><strong>{$teacher['name']}</strong> (Specialization: {$teacher['specialization']}) - Assigning: " . implode(', ', $assigned_subjects) . "</p>";
    
    // Assign subjects to all grades for this teacher
    foreach ($grades as $grade) {
        foreach ($assigned_subjects as $subject_name) {
            // Find the subject ID
            $subject_id = null;
            foreach ($subjects as $subject) {
                if (strcasecmp($subject['name'], $subject_name) === 0) {
                    $subject_id = $subject['id'];
                    break;
                }
            }
            
            if ($subject_id) {
                // Check if assignment already exists
                $check_sql = "SELECT id FROM teacher_grade_subjects WHERE teacher_id = ? AND grade_id = ? AND subject_id = ?";
                $stmt = mysqli_prepare($data, $check_sql);
                mysqli_stmt_bind_param($stmt, "iii", $teacher['id'], $grade['id'], $subject_id);
                mysqli_stmt_execute($stmt);
                $check_result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($check_result) == 0) {
                    // Insert new assignment
                    $insert_sql = "INSERT INTO teacher_grade_subjects (teacher_id, grade_id, subject_id) VALUES (?, ?, ?)";
                    $stmt = mysqli_prepare($data, $insert_sql);
                    mysqli_stmt_bind_param($stmt, "iii", $teacher['id'], $grade['id'], $subject_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $assignments_created++;
                    } else {
                        echo "<p style='color: red;'>❌ Error assigning {$subject_name} to {$teacher['name']} for Grade {$grade['name']}: " . mysqli_error($data) . "</p>";
                    }
                }
            }
        }
    }
}

echo "<p style='color: green;'>✅ Created $assignments_created teacher-subject assignments!</p>";

// 5. Show summary
echo "<h3>Setup Summary:</h3>";
echo "<ul>";
echo "<li>✅ Teacher-grade-subjects table: " . (mysqli_num_rows($table_exists) > 0 ? 'Exists' : 'Created') . "</li>";
echo "<li>✅ Teachers found: " . count($teachers) . "</li>";
echo "<li>✅ Grades found: " . count($grades) . "</li>";
echo "<li>✅ Subjects found: " . count($subjects) . "</li>";
echo "<li>✅ Teacher-subject assignments created: $assignments_created</li>";
echo "</ul>";

// 6. Show current assignments
echo "<h3>Current Teacher-Subject Assignments:</h3>";
$current_assignments_sql = "SELECT t.name as teacher_name, t.specialization, g.name as grade_name, s.name as subject_name 
                           FROM teacher_grade_subjects tgs 
                           JOIN teacher t ON tgs.teacher_id = t.id 
                           JOIN grades g ON tgs.grade_id = g.id 
                           JOIN subjects s ON tgs.subject_id = s.id 
                           ORDER BY t.name, g.name, s.name";
$current_assignments_result = mysqli_query($data, $current_assignments_sql);

if ($current_assignments_result && mysqli_num_rows($current_assignments_result) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 1rem;'>";
    echo "<tr style='background-color: #f8f9fa;'>";
    echo "<th style='padding: 0.5rem;'>Teacher</th>";
    echo "<th style='padding: 0.5rem;'>Specialization</th>";
    echo "<th style='padding: 0.5rem;'>Grade</th>";
    echo "<th style='padding: 0.5rem;'>Subject</th>";
    echo "</tr>";
    
    while ($assignment = mysqli_fetch_assoc($current_assignments_result)) {
        echo "<tr>";
        echo "<td style='padding: 0.5rem;'>" . htmlspecialchars($assignment['teacher_name']) . "</td>";
        echo "<td style='padding: 0.5rem;'>" . htmlspecialchars($assignment['specialization']) . "</td>";
        echo "<td style='padding: 0.5rem;'>" . htmlspecialchars($assignment['grade_name']) . "</td>";
        echo "<td style='padding: 0.5rem;'>" . htmlspecialchars($assignment['subject_name']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>No teacher-subject assignments found.</p>";
}

echo "<p style='color: green; font-weight: bold;'>🎉 Teacher-subject assignment setup completed! Teachers can now view their assigned subjects.</p>";
?> 