<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Fixing Content Management</h2>";

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$data = mysqli_connect($host, $user, $password, $db);

if (!$data) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "<p>✅ Database connected successfully!</p>";

// Create website_content table (without default values for TEXT columns)
$create_table = "
CREATE TABLE IF NOT EXISTS website_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hero_title VARCHAR(255) NOT NULL DEFAULT 'Excellence in Education',
    hero_subtitle TEXT NOT NULL,
    about_title VARCHAR(255) NOT NULL DEFAULT 'Welcome to Miles e-School Academy',
    about_content TEXT NOT NULL,
    contact_address TEXT NOT NULL,
    contact_phone VARCHAR(50) NOT NULL DEFAULT '+1 (555) 123-4567',
    contact_email VARCHAR(100) NOT NULL DEFAULT 'info@milesacademy.edu',
    contact_hours TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($data, $create_table)) {
    echo "<p style='color: green;'>✅ Website content table created successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating table: " . mysqli_error($data) . "</p>";
}

// Check if content exists
$check = mysqli_query($data, "SELECT COUNT(*) as count FROM website_content");
$row = mysqli_fetch_assoc($check);

if ($row['count'] == 0) {
    // Insert default content
    $insert = "
    INSERT INTO website_content (
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
        'Welcome to Miles e-School Academy',
        'At Miles e-School Academy, we believe that learning is a journey — and sometimes, it takes a few extra miles! Our students don\'t just stop at the basics; they zoom past the ordinary with curiosity, creativity, and a healthy dose of determination.',
        '123 Education Street, Academic District, City, Country',
        '+1 (555) 123-4567',
        'info@milesacademy.edu',
        'Monday - Friday: 8:00 AM - 6:00 PM\nSaturday: 9:00 AM - 2:00 PM'
    )";
    
    if (mysqli_query($data, $insert)) {
        echo "<p style='color: green;'>✅ Default content inserted successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error inserting content: " . mysqli_error($data) . "</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ️ Content already exists in the table</p>";
}

echo "<br><p><strong>Content Management should now work!</strong></p>";
echo "<p><a href='content_management.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Content Management</a></p>";
echo "<p><a href='adminhome.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Dashboard</a></p>";
?> 