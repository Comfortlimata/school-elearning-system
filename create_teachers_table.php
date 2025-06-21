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

// Create teachers table
$sql = "CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
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

if (mysqli_query($data, $sql)) {
    echo "Teachers table created successfully!<br>";
    
    // Insert sample teachers
    $sample_teachers = [
        [
            'name' => 'Dr. Sarah Johnson',
            'email' => 'sarah.johnson@milesacademy.edu',
            'phone' => '+1 (555) 123-4567',
            'specialization' => 'Mathematics',
            'qualification' => 'Ph.D. in Mathematics',
            'experience_years' => 15,
            'bio' => 'Dr. Sarah Johnson is a distinguished mathematics professor with over 15 years of experience in teaching advanced mathematics courses. She specializes in calculus, linear algebra, and mathematical modeling.',
            'image' => 'picture1.jpg',
            'linkedin_url' => 'https://linkedin.com/in/sarahjohnson',
            'department' => 'Mathematics Department',
            'office_location' => 'Room 201, Science Building',
            'office_hours' => 'Monday - Friday: 9:00 AM - 5:00 PM'
        ],
        [
            'name' => 'Prof. Michael Chen',
            'email' => 'michael.chen@milesacademy.edu',
            'phone' => '+1 (555) 234-5678',
            'specialization' => 'Physics',
            'qualification' => 'Ph.D. in Physics',
            'experience_years' => 12,
            'bio' => 'Professor Michael Chen is an expert in physics and mechanics with extensive research experience in quantum mechanics and classical physics.',
            'image' => 'picture2.jpg',
            'linkedin_url' => 'https://linkedin.com/in/michaelchen',
            'department' => 'Physics Department',
            'office_location' => 'Room 305, Science Building',
            'office_hours' => 'Monday - Friday: 10:00 AM - 6:00 PM'
        ],
        [
            'name' => 'Ms. Emily Rodriguez',
            'email' => 'emily.rodriguez@milesacademy.edu',
            'phone' => '+1 (555) 345-6789',
            'specialization' => 'Statistics',
            'qualification' => 'M.Sc. in Statistics',
            'experience_years' => 8,
            'bio' => 'Ms. Emily Rodriguez is a statistics instructor with expertise in probability theory, statistical analysis, and data science applications.',
            'image' => 'picture3.jpg',
            'linkedin_url' => 'https://linkedin.com/in/emilyrodriguez',
            'department' => 'Statistics Department',
            'office_location' => 'Room 102, Science Building',
            'office_hours' => 'Monday - Friday: 8:00 AM - 4:00 PM'
        ],
        [
            'name' => 'Dr. James Wilson',
            'email' => 'james.wilson@milesacademy.edu',
            'phone' => '+1 (555) 456-7890',
            'specialization' => 'Computer Science',
            'qualification' => 'Ph.D. in Computer Science',
            'experience_years' => 10,
            'bio' => 'Dr. James Wilson is a computer science professor specializing in algorithms, data structures, and software engineering.',
            'image' => 'picture1.jpg',
            'linkedin_url' => 'https://linkedin.com/in/jameswilson',
            'department' => 'Computer Science Department',
            'office_location' => 'Room 401, Technology Building',
            'office_hours' => 'Monday - Friday: 9:00 AM - 5:00 PM'
        ],
        [
            'name' => 'Prof. Lisa Thompson',
            'email' => 'lisa.thompson@milesacademy.edu',
            'phone' => '+1 (555) 567-8901',
            'specialization' => 'Business Administration',
            'qualification' => 'MBA, Ph.D. in Business',
            'experience_years' => 14,
            'bio' => 'Professor Lisa Thompson is a business administration expert with extensive experience in management, marketing, and strategic planning.',
            'image' => 'picture2.jpg',
            'linkedin_url' => 'https://linkedin.com/in/lisathompson',
            'department' => 'Business Department',
            'office_location' => 'Room 201, Business Building',
            'office_hours' => 'Monday - Friday: 10:00 AM - 6:00 PM'
        ]
    ];
    
    foreach ($sample_teachers as $teacher) {
        $insert_sql = "INSERT INTO teachers (
            name, email, phone, specialization, qualification, experience_years, 
            bio, image, linkedin_url, department, office_location, office_hours
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($data, $insert_sql);
        mysqli_stmt_bind_param($stmt, "sssssissssss", 
            $teacher['name'], $teacher['email'], $teacher['phone'], 
            $teacher['specialization'], $teacher['qualification'], $teacher['experience_years'],
            $teacher['bio'], $teacher['image'], $teacher['linkedin_url'],
            $teacher['department'], $teacher['office_location'], $teacher['office_hours']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            echo "Sample teacher {$teacher['name']} added successfully!<br>";
        } else {
            echo "Error adding teacher {$teacher['name']}: " . mysqli_error($data) . "<br>";
        }
    }
    
} else {
    echo "Error creating teachers table: " . mysqli_error($data);
}

mysqli_close($data);
echo "<br><a href='adminhome.php'>Go to Admin Dashboard</a>";
?> 