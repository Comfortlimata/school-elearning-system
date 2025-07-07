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

echo "<h2>Creating Teacher-Student Messages Table</h2>";

// Create teacher_student_messages table
$create_messages_sql = "CREATE TABLE IF NOT EXISTS teacher_student_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    student_id INT NOT NULL,
    message TEXT NOT NULL,
    sender_type ENUM('teacher', 'student') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teacher(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_teacher_student (teacher_id, student_id),
    INDEX idx_created_at (created_at)
)";

if (mysqli_query($data, $create_messages_sql)) {
    echo "<p style='color: green;'>✅ Teacher-student messages table created successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating messages table: " . mysqli_error($data) . "</p>";
}

// Insert sample messages
echo "<h3>Inserting Sample Messages</h3>";

$sample_messages = [
    [
        'teacher_id' => 1,
        'student_id' => 1,
        'message' => 'Hello! I hope you are doing well with your studies. Please let me know if you need any help with the assignments.',
        'sender_type' => 'teacher'
    ],
    [
        'teacher_id' => 1,
        'student_id' => 2,
        'message' => 'Great work on your recent assignment! Keep up the excellent progress.',
        'sender_type' => 'teacher'
    ],
    [
        'teacher_id' => 2,
        'student_id' => 1,
        'message' => 'Thank you for the clarification on the homework. I understand it better now.',
        'sender_type' => 'student'
    ]
];

foreach ($sample_messages as $msg) {
    $insert_msg_sql = "INSERT INTO teacher_student_messages (teacher_id, student_id, message, sender_type) 
                       VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($data, $insert_msg_sql);
    mysqli_stmt_bind_param($stmt, "iiss", $msg['teacher_id'], $msg['student_id'], $msg['message'], $msg['sender_type']);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p>✅ Added sample message</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to add message: " . mysqli_error($data) . "</p>";
    }
}

echo "<h3>Teacher-Student Messages System Created Successfully!</h3>";
echo "<p>✅ Messages table - for communication between teachers and students</p>";
echo "<p>✅ Read/unread status tracking</p>";
echo "<p>✅ Timestamp tracking</p>";

echo "<h3>Features:</h3>";
echo "<ul>";
echo "<li>Teachers can send messages to students</li>";
echo "<li>Students can reply to teachers</li>";
echo "<li>Message read status tracking</li>";
echo "<li>Automatic timestamp recording</li>";
echo "</ul>";

echo "<p><a href='teacher_students.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Teacher Students Page</a></p>";

mysqli_close($data);
?> 