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

echo "<h2>Creating Comprehensive Teacher Table</h2>";

// Drop existing teacher table if it exists
$drop_sql = "DROP TABLE IF EXISTS teacher";
mysqli_query($data, $drop_sql);
echo "<p>✅ Dropped existing teacher table</p>";

// Create comprehensive teacher table
$create_sql = "CREATE TABLE teacher (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    specialization VARCHAR(100) NOT NULL,
    qualification VARCHAR(100) NOT NULL,
    experience_years INT DEFAULT 0,
    bio TEXT,
    image VARCHAR(255) DEFAULT 'default_teacher.jpg',
    linkedin_url VARCHAR(255),
    twitter_url VARCHAR(255),
    facebook_url VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    hire_date DATE,
    salary DECIMAL(10,2),
    department VARCHAR(100),
    office_location VARCHAR(100),
    office_hours TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($data, $create_sql)) {
    echo "<p style='color: green;'>✅ Comprehensive teacher table created!</p>";
} else {
    echo "<p style='color: red;'>❌ Error: " . mysqli_error($data) . "</p>";
    exit();
}

// Add sample teachers
$teachers = [
    [
        'username' => 'sarah.johnson',
        'password' => 'teacher123',
        'name' => 'Dr. Sarah Johnson',
        'email' => 'sarah.johnson@milesacademy.edu',
        'phone' => '+1 (555) 123-4567',
        'specialization' => 'Mathematics',
        'qualification' => 'Ph.D. in Mathematics',
        'experience_years' => 15,
        'bio' => 'Dr. Sarah Johnson is a distinguished mathematics professor with over 15 years of experience.',
        'image' => 'picture1.jpg',
        'linkedin_url' => 'https://linkedin.com/in/sarahjohnson',
        'department' => 'Mathematics Department',
        'office_location' => 'Room 201, Science Building',
        'office_hours' => 'Monday - Friday: 9:00 AM - 5:00 PM'
    ],
    [
        'username' => 'michael.chen',
        'password' => 'teacher123',
        'name' => 'Prof. Michael Chen',
        'email' => 'michael.chen@milesacademy.edu',
        'phone' => '+1 (555) 234-5678',
        'specialization' => 'Physics',
        'qualification' => 'Ph.D. in Physics',
        'experience_years' => 12,
        'bio' => 'Professor Michael Chen is an expert in physics and mechanics.',
        'image' => 'picture2.jpg',
        'linkedin_url' => 'https://linkedin.com/in/michaelchen',
        'department' => 'Physics Department',
        'office_location' => 'Room 305, Science Building',
        'office_hours' => 'Monday - Friday: 10:00 AM - 6:00 PM'
    ],
    [
        'username' => 'emily.rodriguez',
        'password' => 'teacher123',
        'name' => 'Ms. Emily Rodriguez',
        'email' => 'emily.rodriguez@milesacademy.edu',
        'phone' => '+1 (555) 345-6789',
        'specialization' => 'Statistics',
        'qualification' => 'M.Sc. in Statistics',
        'experience_years' => 8,
        'bio' => 'Ms. Emily Rodriguez is a statistics instructor with expertise in probability theory.',
        'image' => 'picture3.jpg',
        'linkedin_url' => 'https://linkedin.com/in/emilyrodriguez',
        'department' => 'Statistics Department',
        'office_location' => 'Room 102, Science Building',
        'office_hours' => 'Monday - Friday: 8:00 AM - 4:00 PM'
    ],
    [
        'username' => 'james.wilson',
        'password' => 'teacher123',
        'name' => 'Dr. James Wilson',
        'email' => 'james.wilson@milesacademy.edu',
        'phone' => '+1 (555) 456-7890',
        'specialization' => 'Computer Science',
        'qualification' => 'Ph.D. in Computer Science',
        'experience_years' => 10,
        'bio' => 'Dr. James Wilson is a computer science professor specializing in algorithms.',
        'image' => 'picture1.jpg',
        'linkedin_url' => 'https://linkedin.com/in/jameswilson',
        'department' => 'Computer Science Department',
        'office_location' => 'Room 401, Technology Building',
        'office_hours' => 'Monday - Friday: 9:00 AM - 5:00 PM'
    ],
    [
        'username' => 'lisa.thompson',
        'password' => 'teacher123',
        'name' => 'Prof. Lisa Thompson',
        'email' => 'lisa.thompson@milesacademy.edu',
        'phone' => '+1 (555) 567-8901',
        'specialization' => 'Business Administration',
        'qualification' => 'MBA, Ph.D. in Business',
        'experience_years' => 14,
        'bio' => 'Professor Lisa Thompson is a business administration expert.',
        'image' => 'picture2.jpg',
        'linkedin_url' => 'https://linkedin.com/in/lisathompson',
        'department' => 'Business Department',
        'office_location' => 'Room 201, Business Building',
        'office_hours' => 'Monday - Friday: 10:00 AM - 6:00 PM'
    ]
];

echo "<h3>Adding Sample Teachers</h3>";

foreach ($teachers as $teacher) {
    $hashed_password = password_hash($teacher['password'], PASSWORD_DEFAULT);
    
    $insert_sql = "INSERT INTO teacher (
        username, password, name, email, phone, specialization, qualification, 
        experience_years, bio, image, linkedin_url, department, office_location, office_hours
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($data, $insert_sql);
    mysqli_stmt_bind_param($stmt, "sssssssissssss", 
        $teacher['username'], $hashed_password, $teacher['name'], $teacher['email'], 
        $teacher['phone'], $teacher['specialization'], $teacher['qualification'], 
        $teacher['experience_years'], $teacher['bio'], $teacher['image'], 
        $teacher['linkedin_url'], $teacher['department'], $teacher['office_location'], 
        $teacher['office_hours']
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p>✅ Added: {$teacher['name']}</p>";
    }
}

// Show teachers
echo "<h3>Available Teachers:</h3>";
$show_sql = "SELECT username, name, specialization, department FROM teacher";
$show_result = mysqli_query($data, $show_sql);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Username</th><th>Name</th><th>Specialization</th><th>Department</th><th>Password</th></tr>";

while ($teacher = mysqli_fetch_assoc($show_result)) {
    echo "<tr>";
    echo "<td>{$teacher['username']}</td>";
    echo "<td>{$teacher['name']}</td>";
    echo "<td>{$teacher['specialization']}</td>";
    echo "<td>{$teacher['department']}</td>";
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