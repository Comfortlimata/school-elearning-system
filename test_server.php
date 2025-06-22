<?php
echo "<h1>Server Test</h1>";
echo "<p>If you can see this, the server is working!</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP version: " . phpversion() . "</p>";

// Test database connection
$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$data = mysqli_connect($host, $user, $password, $db);

if ($data) {
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test if tables exist
    $tables = ['students', 'teacher', 'courses', 'student_grades', 'student_assignments', 'student_schedule', 'teacher_assignments'];
    
    foreach ($tables as $table) {
        $result = mysqli_query($data, "SHOW TABLES LIKE '$table'");
        if (mysqli_num_rows($result) > 0) {
            echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>❌ Table '$table' does not exist</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Database connection failed: " . mysqli_connect_error() . "</p>";
}

echo "<h2>Available Files:</h2>";
echo "<ul>";
$files = [
    'login.php' => 'Login Page',
    'teacherhome.php' => 'Teacher Home',
    'studenthome.php' => 'Student Home',
    'adminhome.php' => 'Admin Home',
    'teacher_content_management.php' => 'Teacher Content Management',
    'teacher_grade_management.php' => 'Teacher Grade Management',
    'student_schedule.php' => 'Student Schedule',
    'student_assignments.php' => 'Student Assignments',
    'student_results.php' => 'Student Results'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "<li><a href='$file' style='color: green;'>✅ $description ($file)</a></li>";
    } else {
        echo "<li style='color: red;'>❌ $description ($file) - File not found</li>";
    }
}
echo "</ul>";

echo "<h2>Quick Links:</h2>";
echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>Login Page</a></p>";
echo "<p><a href='create_student_tables.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>Create Database Tables</a></p>";
echo "<p><a href='create_course_materials_table.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>Create Materials Table</a></p>";
?> 