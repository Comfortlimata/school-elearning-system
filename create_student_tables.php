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

echo "<h2>Creating Student Management Tables</h2>";

// Create student_grades table
$create_grades_sql = "CREATE TABLE IF NOT EXISTS student_grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    assignment_name VARCHAR(255) NOT NULL,
    grade VARCHAR(10),
    score DECIMAL(5,2),
    max_score DECIMAL(5,2) DEFAULT 100,
    submitted_date DATE,
    graded_date DATE,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_student_course (student_id, course_id)
)";

if (mysqli_query($data, $create_grades_sql)) {
    echo "<p style='color: green;'>✅ Student grades table created successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating grades table: " . mysqli_error($data) . "</p>";
}

// Create student_assignments table
$create_assignments_sql = "CREATE TABLE IF NOT EXISTS student_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATE NOT NULL,
    status ENUM('pending', 'submitted', 'graded', 'overdue') DEFAULT 'pending',
    submission_file VARCHAR(255),
    submission_date DATETIME,
    grade VARCHAR(10),
    score DECIMAL(5,2),
    max_score DECIMAL(5,2) DEFAULT 100,
    teacher_comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_student_status (student_id, status),
    INDEX idx_due_date (due_date)
)";

if (mysqli_query($data, $create_assignments_sql)) {
    echo "<p style='color: green;'>✅ Student assignments table created successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating assignments table: " . mysqli_error($data) . "</p>";
}

// Create student_schedule table
$create_schedule_sql = "CREATE TABLE IF NOT EXISTS student_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(50),
    teacher_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teacher(id) ON DELETE SET NULL,
    INDEX idx_student_day (student_id, day_of_week),
    INDEX idx_teacher (teacher_id)
)";

if (mysqli_query($data, $create_schedule_sql)) {
    echo "<p style='color: green;'>✅ Student schedule table created successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating schedule table: " . mysqli_error($data) . "</p>";
}

// Create teacher_assignments table (for teachers to create assignments)
$create_teacher_assignments_sql = "CREATE TABLE IF NOT EXISTS teacher_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATE NOT NULL,
    max_score DECIMAL(5,2) DEFAULT 100,
    assignment_file VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teacher(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_teacher_course (teacher_id, course_id),
    INDEX idx_due_date (due_date)
)";

if (mysqli_query($data, $create_teacher_assignments_sql)) {
    echo "<p style='color: green;'>✅ Teacher assignments table created successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating teacher assignments table: " . mysqli_error($data) . "</p>";
}

// Insert sample data for testing
echo "<h3>Inserting Sample Data</h3>";

// Sample grades data
$sample_grades = [
    ['student_id' => 1, 'course_id' => 1, 'assignment_name' => 'Mathematics Quiz 1', 'grade' => 'A', 'score' => 85, 'submitted_date' => '2024-01-10'],
    ['student_id' => 1, 'course_id' => 2, 'assignment_name' => 'Physics Lab Report', 'grade' => 'B+', 'score' => 78, 'submitted_date' => '2024-01-12'],
    ['student_id' => 1, 'course_id' => 3, 'assignment_name' => 'Computer Science Project', 'grade' => 'A-', 'score' => 82, 'submitted_date' => '2024-01-15']
];

foreach ($sample_grades as $grade) {
    $insert_grade_sql = "INSERT INTO student_grades (student_id, course_id, assignment_name, grade, score, submitted_date) 
                        VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($data, $insert_grade_sql);
    mysqli_stmt_bind_param($stmt, "iissds", $grade['student_id'], $grade['course_id'], $grade['assignment_name'], $grade['grade'], $grade['score'], $grade['submitted_date']);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p>✅ Added sample grade: {$grade['assignment_name']}</p>";
    }
}

// Sample schedule data
$sample_schedule = [
    ['student_id' => 1, 'course_id' => 1, 'day_of_week' => 'Monday', 'start_time' => '09:00:00', 'end_time' => '10:30:00', 'room' => 'Room 201', 'teacher_id' => 1],
    ['student_id' => 1, 'course_id' => 2, 'day_of_week' => 'Monday', 'start_time' => '11:00:00', 'end_time' => '12:30:00', 'room' => 'Room 305', 'teacher_id' => 2],
    ['student_id' => 1, 'course_id' => 3, 'day_of_week' => 'Monday', 'start_time' => '14:00:00', 'end_time' => '15:30:00', 'room' => 'Room 401', 'teacher_id' => 3],
    ['student_id' => 1, 'course_id' => 1, 'day_of_week' => 'Tuesday', 'start_time' => '09:00:00', 'end_time' => '10:30:00', 'room' => 'Room 201', 'teacher_id' => 1],
    ['student_id' => 1, 'course_id' => 2, 'day_of_week' => 'Wednesday', 'start_time' => '11:00:00', 'end_time' => '12:30:00', 'room' => 'Room 305', 'teacher_id' => 2]
];

foreach ($sample_schedule as $schedule) {
    $insert_schedule_sql = "INSERT INTO student_schedule (student_id, course_id, day_of_week, start_time, end_time, room, teacher_id) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($data, $insert_schedule_sql);
    mysqli_stmt_bind_param($stmt, "iissssi", $schedule['student_id'], $schedule['course_id'], $schedule['day_of_week'], $schedule['start_time'], $schedule['end_time'], $schedule['room'], $schedule['teacher_id']);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p>✅ Added sample schedule: {$schedule['day_of_week']} - {$schedule['start_time']}</p>";
    }
}

echo "<h3>Tables Created Successfully!</h3>";
echo "<p>✅ Student grades table - for tracking academic performance</p>";
echo "<p>✅ Student assignments table - for assignment submission and tracking</p>";
echo "<p>✅ Student schedule table - for class schedules (teachers can update)</p>";
echo "<p>✅ Teacher assignments table - for teachers to create assignments</p>";

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Teachers can now create assignments using the teacher assignments table</li>";
echo "<li>Students can submit assignments and view their grades</li>";
echo "<li>Schedule can be managed by teachers and viewed by students</li>";
echo "<li>All student pages are now functional with database support</li>";
echo "</ol>";

echo "<p><a href='studenthome.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Student Dashboard</a></p>";

mysqli_close($data);
?> 