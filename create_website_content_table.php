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

echo "<h2>Creating Website Content Table</h2>";

// Create website_content table
$create_table_sql = "
CREATE TABLE IF NOT EXISTS website_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hero_title VARCHAR(255) NOT NULL DEFAULT 'Excellence in Education',
    hero_subtitle TEXT NOT NULL DEFAULT 'Empowering students with knowledge, skills, and values for a brighter future.',
    about_title VARCHAR(255) NOT NULL DEFAULT 'Welcome to Miles e-School Academy',
    about_content TEXT NOT NULL DEFAULT 'At Miles e-School Academy, we believe that learning is a journey — and sometimes, it takes a few extra miles! Our students don\'t just stop at the basics; they zoom past the ordinary with curiosity, creativity, and a healthy dose of determination.',
    contact_address TEXT NOT NULL DEFAULT '123 Education Street, Academic District, City, Country',
    contact_phone VARCHAR(50) NOT NULL DEFAULT '+1 (555) 123-4567',
    contact_email VARCHAR(100) NOT NULL DEFAULT 'info@milesacademy.edu',
    contact_hours TEXT NOT NULL DEFAULT 'Monday - Friday: 8:00 AM - 6:00 PM\nSaturday: 9:00 AM - 2:00 PM',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($data, $create_table_sql)) {
    echo "<p style='color: green;'>✅ Website content table created successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating table: " . mysqli_error($data) . "</p>";
}

// Insert default content if table is empty
$check_content = mysqli_query($data, "SELECT COUNT(*) as count FROM website_content");
$row = mysqli_fetch_assoc($check_content);

if ($row['count'] == 0) {
    $insert_default = "
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
    
    if (mysqli_query($data, $insert_default)) {
        echo "<p style='color: green;'>✅ Default content inserted successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error inserting default content: " . mysqli_error($data) . "</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ️ Content already exists in the table</p>";
}

// Show table structure
echo "<h3>Website Content Table Structure:</h3>";
$structure = mysqli_query($data, "DESCRIBE website_content");
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = mysqli_fetch_assoc($structure)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Show current content
echo "<h3>Current Content:</h3>";
$content = mysqli_query($data, "SELECT * FROM website_content WHERE id = 1");
if ($content && mysqli_num_rows($content) > 0) {
    $content_data = mysqli_fetch_assoc($content);
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px;'>";
    echo "<h4>Hero Section:</h4>";
    echo "<p><strong>Title:</strong> " . htmlspecialchars($content_data['hero_title']) . "</p>";
    echo "<p><strong>Subtitle:</strong> " . htmlspecialchars($content_data['hero_subtitle']) . "</p>";
    
    echo "<h4>About Section:</h4>";
    echo "<p><strong>Title:</strong> " . htmlspecialchars($content_data['about_title']) . "</p>";
    echo "<p><strong>Content:</strong> " . htmlspecialchars($content_data['about_content']) . "</p>";
    
    echo "<h4>Contact Information:</h4>";
    echo "<p><strong>Address:</strong> " . nl2br(htmlspecialchars($content_data['contact_address'])) . "</p>";
    echo "<p><strong>Phone:</strong> " . htmlspecialchars($content_data['contact_phone']) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($content_data['contact_email']) . "</p>";
    echo "<p><strong>Hours:</strong> " . nl2br(htmlspecialchars($content_data['contact_hours'])) . "</p>";
    echo "</div>";
}

echo "<br><p><a href='content_management.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Content Management</a></p>";
?> 