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

// Create teacher_student_messages table
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
    echo "Teacher-student messages table created successfully!<br>";
} else {
    echo "Error creating messages table: " . mysqli_error($data) . "<br>";
}

// Create courses table if it doesn't exist
$create_courses_table = "CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL,
    course_code VARCHAR(50) NOT NULL,
    course_description TEXT,
    program VARCHAR(100) NOT NULL,
    document_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($data, $create_courses_table)) {
    echo "Courses table created successfully!<br>";
} else {
    echo "Error creating courses table: " . mysqli_error($data) . "<br>";
}

// Insert sample courses
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
        'course_name' => 'Computer Science Fundamentals',
        'course_code' => 'CS101',
        'course_description' => 'Introduction to programming, algorithms, and computer systems.',
        'program' => 'Computer Science'
    ]
];

foreach ($sample_courses as $course) {
    $insert_course = "INSERT IGNORE INTO courses (course_name, course_code, course_description, program) 
                      VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($data, $insert_course);
    mysqli_stmt_bind_param($stmt, "ssss", $course['course_name'], $course['course_code'], $course['course_description'], $course['program']);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Course '{$course['course_name']}' added successfully!<br>";
    } else {
        echo "Error adding course: " . mysqli_error($data) . "<br>";
    }
}

echo "<br>Setup completed! You can now use the teacher courses page with communication features.";
?> 