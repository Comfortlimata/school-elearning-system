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

echo "<h2>Creating New Teacher Authentication Table</h2>";

// Create the new teacher table for authentication
$create_sql = "CREATE TABLE IF NOT EXISTS teacher (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($data, $create_sql)) {
    echo "<p style='color: green;'>✅ Teacher table created successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating teacher table: " . mysqli_error($data) . "</p>";
    exit();
}

// Insert sample teachers with proper authentication credentials
$sample_teachers = [
    [
        'username' => 'sarah.johnson',
        'password' => 'teacher123',
        'email' => 'sarah.johnson@milesacademy.edu',
        'name' => 'Dr. Sarah Johnson',
        'specialization' => 'Mathematics'
    ],
    [
        'username' => 'michael.chen',
        'password' => 'teacher123',
        'email' => 'michael.chen@milesacademy.edu',
        'name' => 'Prof. Michael Chen',
        'specialization' => 'Physics'
    ],
    [
        'username' => 'emily.rodriguez',
        'password' => 'teacher123',
        'email' => 'emily.rodriguez@milesacademy.edu',
        'name' => 'Ms. Emily Rodriguez',
        'specialization' => 'Statistics'
    ],
    [
        'username' => 'james.wilson',
        'password' => 'teacher123',
        'email' => 'james.wilson@milesacademy.edu',
        'name' => 'Dr. James Wilson',
        'specialization' => 'Computer Science'
    ],
    [
        'username' => 'lisa.thompson',
        'password' => 'teacher123',
        'email' => 'lisa.thompson@milesacademy.edu',
        'name' => 'Prof. Lisa Thompson',
        'specialization' => 'Business Administration'
    ]
];

echo "<h3>Adding Sample Teachers</h3>";

foreach ($sample_teachers as $teacher) {
    // Hash the password
    $hashed_password = password_hash($teacher['password'], PASSWORD_DEFAULT);
    
    $insert_sql = "INSERT INTO teacher (username, password, email, name, specialization) 
                   VALUES (?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($data, $insert_sql);
    mysqli_stmt_bind_param($stmt, "sssss", 
        $teacher['username'], 
        $hashed_password, 
        $teacher['email'], 
        $teacher['name'], 
        $teacher['specialization']
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p>✅ Added teacher: {$teacher['name']} ({$teacher['username']})</p>";
    } else {
        echo "<p>❌ Error adding teacher {$teacher['name']}: " . mysqli_error($data) . "</p>";
    }
}

// Show the created teachers
echo "<h3>Current Teachers in Authentication Table</h3>";
$show_sql = "SELECT username, email, name, specialization, status FROM teacher ORDER BY name";
$show_result = mysqli_query($data, $show_sql);

if ($show_result && mysqli_num_rows($show_result) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Email</th>";
    echo "<th style='padding: 8px;'>Name</th>";
    echo "<th style='padding: 8px;'>Specialization</th>";
    echo "<th style='padding: 8px;'>Status</th>";
    echo "<th style='padding: 8px;'>Login Password</th>";
    echo "</tr>";
    
    while ($teacher = mysqli_fetch_assoc($show_result)) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($teacher['username']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($teacher['email']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($teacher['name']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($teacher['specialization']) . "</td>";
        echo "<td style='padding: 8px;'>" . $teacher['status'] . "</td>";
        echo "<td style='padding: 8px;'><strong>teacher123</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ No teachers found in the new table!</p>";
}

echo "<h3>Next Steps</h3>";
echo "<p>✅ Teacher authentication table created successfully!</p>";
echo "<p>You can now login with any of the teachers above using:</p>";
echo "<ul>";
echo "<li><strong>Username:</strong> Any username from the table</li>";
echo "<li><strong>Password:</strong> teacher123</li>";
echo "</ul>";
echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Unified Login</a></p>";

mysqli_close($data);
?> 