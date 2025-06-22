<?php
// Test script to verify login flow
echo "<h2>Login Flow Test</h2>";

// Test database connection
$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$data = mysqli_connect($host, $user, $password, $db);

if (!$data) {
    echo "<p style='color: red;'>❌ Database connection failed: " . mysqli_connect_error() . "</p>";
    exit();
} else {
    echo "<p style='color: green;'>✅ Database connection successful</p>";
}

// Test if required tables exist
$tables = ['user', 'teacher', 'students', 'courses', 'admission'];
$missing_tables = [];

foreach ($tables as $table) {
    $result = mysqli_query($data, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) == 0) {
        $missing_tables[] = $table;
    }
}

if (empty($missing_tables)) {
    echo "<p style='color: green;'>✅ All required tables exist</p>";
} else {
    echo "<p style='color: red;'>❌ Missing tables: " . implode(', ', $missing_tables) . "</p>";
}

// Test if required files exist
$files = [
    'login.php',
    'login_check.php',
    'adminhome.php',
    'teacherhome.php',
    'studenthome.php',
    'logout.php',
    'index.php'
];

$missing_files = [];

foreach ($files as $file) {
    if (!file_exists($file)) {
        $missing_files[] = $file;
    }
}

if (empty($missing_files)) {
    echo "<p style='color: green;'>✅ All required files exist</p>";
} else {
    echo "<p style='color: red;'>❌ Missing files: " . implode(', ', $missing_files) . "</p>";
}

// Test teacher table structure
$teacher_columns = mysqli_query($data, "DESCRIBE teacher");
if ($teacher_columns) {
    $has_password = false;
    $has_username = false;
    
    while ($row = mysqli_fetch_assoc($teacher_columns)) {
        if ($row['Field'] == 'password') $has_password = true;
        if ($row['Field'] == 'username') $has_username = true;
    }
    
    if ($has_password && $has_username) {
        echo "<p style='color: green;'>✅ Teacher table has required columns (username, password)</p>";
    } else {
        echo "<p style='color: red;'>❌ Teacher table missing required columns</p>";
    }
}

// Test user counts
$admin_count = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM user"))[0];
$teacher_count = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM teacher"))[0];
$student_count = mysqli_fetch_array(mysqli_query($data, "SELECT COUNT(*) FROM students"))[0];

echo "<h3>User Counts:</h3>";
echo "<p>Admins: $admin_count</p>";
echo "<p>Teachers: $teacher_count</p>";
echo "<p>Students: $student_count</p>";

echo "<h3>Test Links:</h3>";
echo "<p><a href='index.php'>🏠 Go to Homepage</a></p>";
echo "<p><a href='login.php'>🔐 Go to Login</a></p>";
echo "<p><a href='test_server.php'>⚙️ System Status</a></p>";

echo "<h3>Summary:</h3>";
if (empty($missing_tables) && empty($missing_files)) {
    echo "<p style='color: green; font-weight: bold;'>🎉 All systems are ready! The login flow should work properly.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>⚠️ There are issues that need to be resolved before the login flow will work properly.</p>";
}
?> 