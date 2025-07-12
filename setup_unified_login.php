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

echo "<h2>Setting Up Unified Login System</h2>";

// Create teacher table
$create_sql = "CREATE TABLE IF NOT EXISTS teacher (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($data, $create_sql)) {
    echo "<p style='color: green;'>✅ Teacher table created!</p>";
} else {
    echo "<p style='color: red;'>❌ Error: " . mysqli_error($data) . "</p>";
    exit();
}

// Add sample teachers
$teachers = [
            ['username' => 'sarah.johnson', 'password' => 'teacher123', 'email' => 'sarah.johnson@comfortacademy.edu', 'name' => 'Dr. Sarah Johnson', 'specialization' => 'Mathematics'],
            ['username' => 'michael.chen', 'password' => 'teacher123', 'email' => 'michael.chen@comfortacademy.edu', 'name' => 'Prof. Michael Chen', 'specialization' => 'Physics'],
            ['username' => 'emily.rodriguez', 'password' => 'teacher123', 'email' => 'emily.rodriguez@comfortacademy.edu', 'name' => 'Ms. Emily Rodriguez', 'specialization' => 'Statistics'],
            ['username' => 'james.wilson', 'password' => 'teacher123', 'email' => 'james.wilson@comfortacademy.edu', 'name' => 'Dr. James Wilson', 'specialization' => 'Computer Science'],
            ['username' => 'lisa.thompson', 'password' => 'teacher123', 'email' => 'lisa.thompson@comfortacademy.edu', 'name' => 'Prof. Lisa Thompson', 'specialization' => 'Business Administration']
];

foreach ($teachers as $teacher) {
    $check_sql = "SELECT id FROM teacher WHERE username = ?";
    $check_stmt = mysqli_prepare($data, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $teacher['username']);
    mysqli_stmt_execute($check_stmt);
    
    if (mysqli_num_rows(mysqli_stmt_get_result($check_stmt)) == 0) {
        $hashed_password = password_hash($teacher['password'], PASSWORD_DEFAULT);
        $insert_sql = "INSERT INTO teacher (username, password, email, name, specialization) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($data, $insert_sql);
        mysqli_stmt_bind_param($stmt, "sssss", $teacher['username'], $hashed_password, $teacher['email'], $teacher['name'], $teacher['specialization']);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<p>✅ Added: {$teacher['name']}</p>";
        }
    }
}

// Show teachers
echo "<h3>Available Teachers:</h3>";
$show_sql = "SELECT username, name, specialization FROM teacher";
$show_result = mysqli_query($data, $show_sql);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Username</th><th>Name</th><th>Specialization</th><th>Password</th></tr>";

while ($teacher = mysqli_fetch_assoc($show_result)) {
    echo "<tr>";
    echo "<td>{$teacher['username']}</td>";
    echo "<td>{$teacher['name']}</td>";
    echo "<td>{$teacher['specialization']}</td>";
    echo "<td><strong>teacher123</strong></td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>How to Login:</h3>";
echo "<ol>";
echo "<li>Go to <a href='login.php'>login.php</a></li>";
echo "<li>Select 'Teacher' as user type</li>";
echo "<li>Enter any username from the table above</li>";
echo "<li>Enter password: teacher123</li>";
echo "<li>Click Sign In</li>";
echo "</ol>";

echo "<p><a href='login.php' style='background: blue; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";

mysqli_close($data);
?> 