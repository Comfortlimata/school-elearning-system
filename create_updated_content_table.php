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

// Drop existing table if exists
$drop_sql = "DROP TABLE IF EXISTS website_content";
mysqli_query($data, $drop_sql);

// Create updated website_content table
$sql = "CREATE TABLE website_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hero_title VARCHAR(255) NOT NULL DEFAULT 'Excellence in Education',
    hero_subtitle TEXT NOT NULL,
    about_title VARCHAR(255) NOT NULL DEFAULT 'About Comfort e-School Academy',
    about_content TEXT NOT NULL,
    contact_address TEXT NOT NULL,
    contact_phone VARCHAR(50) NOT NULL,
    contact_email VARCHAR(100) NOT NULL,
    contact_hours TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($data, $sql)) {
    echo "Updated website content table created successfully!<br>";
    
    // Insert default content
    $insert_sql = "INSERT INTO website_content (
        hero_title, 
        hero_subtitle, 
        about_title, 
        about_content, 
        contact_address, 
        contact_phone, 
        contact_email, 
        contact_hours
    ) VALUES (
        'Excellence in Education',
        'Empowering students with knowledge, skills, and values for a brighter future. Join our community of learners and discover your potential.',
        'About Comfort e-School Academy',
        'At Comfort e-School Academy, we believe that learning is a journey — and sometimes, it takes a few extra miles! Our students don\'t just stop at the basics; they zoom past the ordinary with curiosity, creativity, and a healthy dose of determination.',
        '123 Education Street, Academic District, City, Country',
        '+1 (555) 123-4567',
        'info@comfortacademy.edu',
        'Monday - Friday: 8:00 AM - 6:00 PM\nSaturday: 9:00 AM - 2:00 PM'
    )";
    
    if (mysqli_query($data, $insert_sql)) {
        echo "Default content inserted successfully!<br>";
    } else {
        echo "Error inserting default content: " . mysqli_error($data) . "<br>";
    }
    
} else {
    echo "Error creating table: " . mysqli_error($data);
}

mysqli_close($data);
echo "<br><a href='adminhome.php'>Go to Admin Dashboard</a>";
?> 