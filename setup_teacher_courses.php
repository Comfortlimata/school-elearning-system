<?php
// Setup script for Teacher Course Management System
// Run this once to ensure all necessary tables exist

$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$data = mysqli_connect($host, $user, $password, $db);

if (!$data) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "<h2>Setting up Teacher Course Management System...</h2>";

// 1. Create courses table if it doesn't exist
$create_courses_table = "CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL,
    course_code VARCHAR(50) NOT NULL,
    course_description TEXT,
    program VARCHAR(100) NOT NULL,
    teacher_id INT,
    document_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teacher(id) ON DELETE SET NULL
)";

if (mysqli_query($data, $create_courses_table)) {
    echo "✅ Courses table created/verified successfully!<br>";
} else {
    echo "❌ Error creating courses table: " . mysqli_error($data) . "<br>";
}

// 2. Create teacher_student_messages table if it doesn't exist
$create_messages_table = "CREATE TABLE IF NOT EXISTS teacher_student_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    student_id INT NOT NULL,
    message TEXT NOT NULL,
    sender_type ENUM('teacher', 'student') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teacher(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
)";

if (mysqli_query($data, $create_messages_table)) {
    echo "✅ Teacher-student messages table created/verified successfully!<br>";
} else {
    echo "❌ Error creating messages table: " . mysqli_error($data) . "<br>";
}

// 3. Add teacher_id column to courses table if it doesn't exist
$check_teacher_id_column = "SHOW COLUMNS FROM courses LIKE 'teacher_id'";
$result = mysqli_query($data, $check_teacher_id_column);

if (mysqli_num_rows($result) == 0) {
    $add_teacher_id_column = "ALTER TABLE courses ADD COLUMN teacher_id INT, ADD FOREIGN KEY (teacher_id) REFERENCES teacher(id) ON DELETE SET NULL";
    if (mysqli_query($data, $add_teacher_id_column)) {
        echo "✅ Added teacher_id column to courses table!<br>";
    } else {
        echo "❌ Error adding teacher_id column: " . mysqli_error($data) . "<br>";
    }
} else {
    echo "✅ teacher_id column already exists in courses table!<br>";
}

// 4. Insert sample courses if table is empty
$check_courses = "SELECT COUNT(*) as count FROM courses";
$result = mysqli_query($data, $check_courses);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    $sample_courses = [
        [
            'course_name' => 'IGCSE Cambridge Mathematics (0580)',
            'course_code' => 'MATH0580',
            'course_description' => 'Comprehensive mathematics course covering algebra, geometry, and statistics.',
            'program' => 'Mathematics'
        ],
        [
            'course_name' => 'AS - Probability & Statistics (9709)',
            'course_code' => 'MATH9709-PS',
            'course_description' => 'Advanced study of probability theory and statistical analysis.',
            'program' => 'Mathematics'
        ],
        [
            'course_name' => 'AS - Mechanics (9709)',
            'course_code' => 'MATH9709-M',
            'course_description' => 'Physics-based mathematics focusing on motion and forces.',
            'program' => 'Mathematics'
        ],
        [
            'course_name' => 'AS - Pure Mathematics 1',
            'course_code' => 'MATH9709-PM1',
            'course_description' => 'Foundation course in pure mathematics and mathematical reasoning.',
            'program' => 'Mathematics'
        ],
        [
            'course_name' => 'A Level - Pure Mathematics 3 (9709)',
            'course_code' => 'MATH9709-PM3',
            'course_description' => 'Advanced pure mathematics for university preparation.',
            'program' => 'Mathematics'
        ],
        [
            'course_name' => 'Computer Science Fundamentals',
            'course_code' => 'CS101',
            'course_description' => 'Introduction to programming, algorithms, and computer systems.',
            'program' => 'Computer Science'
        ],
        [
            'course_name' => 'Programming in Python',
            'course_code' => 'CS102',
            'course_description' => 'Learn Python programming language and its applications.',
            'program' => 'Computer Science'
        ],
        [
            'course_name' => 'Data Structures and Algorithms',
            'course_code' => 'CS201',
            'course_description' => 'Advanced programming concepts and problem-solving techniques.',
            'program' => 'Computer Science'
        ]
    ];

    foreach ($sample_courses as $course) {
        $insert_course = "INSERT INTO courses (course_name, course_code, course_description, program) 
                          VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($data, $insert_course);
        mysqli_stmt_bind_param($stmt, "ssss", $course['course_name'], $course['course_code'], $course['course_description'], $course['program']);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "✅ Added course: {$course['course_name']}<br>";
        } else {
            echo "❌ Error adding course {$course['course_name']}: " . mysqli_error($data) . "<br>";
        }
    }
} else {
    echo "✅ Courses table already has data!<br>";
}

// 5. Create indexes for better performance
$indexes = [
    "CREATE INDEX IF NOT EXISTS idx_courses_teacher ON courses(teacher_id)",
    "CREATE INDEX IF NOT EXISTS idx_courses_program ON courses(program)",
    "CREATE INDEX IF NOT EXISTS idx_messages_teacher ON teacher_student_messages(teacher_id)",
    "CREATE INDEX IF NOT EXISTS idx_messages_student ON teacher_student_messages(student_id)",
    "CREATE INDEX IF NOT EXISTS idx_messages_created ON teacher_student_messages(created_at)"
];

foreach ($indexes as $index) {
    if (mysqli_query($data, $index)) {
        echo "✅ Index created successfully!<br>";
    } else {
        echo "⚠️ Index creation skipped (may already exist): " . mysqli_error($data) . "<br>";
    }
}

echo "<br><h3>🎉 Setup Complete!</h3>";
echo "<p>Your Teacher Course Management System is now ready to use.</p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>Go to Admin Dashboard → Add Teacher to assign courses to teachers</li>";
echo "<li>Teachers can now see their assigned courses and communicate with enrolled students</li>";
echo "<li>Students will appear in teacher's course page based on their program matching the teacher's courses</li>";
echo "</ul>";

echo "<p><a href='adminhome.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Dashboard</a></p>";

mysqli_close($data);
?> 