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

// Create website_content table
$sql = "CREATE TABLE IF NOT EXISTS website_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_section (section)
)";

if (mysqli_query($data, $sql)) {
    echo "Website content table created successfully!<br>";
    
    // Insert default content
    $default_content = [
        ['hero', 'Excellence in Education', 'Empowering students with knowledge, skills, and values for a brighter future. Join our community of learners and discover your potential.'],
        ['about', 'About Miles e-School Academy', 'At Miles e-School Academy, we believe that learning is a journey — and sometimes, it takes a few extra miles! Our students don\'t just stop at the basics; they zoom past the ordinary with curiosity, creativity, and a healthy dose of determination.'],
        ['stats', 'Statistics', '{"students":"500+","teachers":"25+","experience":"15+","success_rate":"95%"}']
    ];
    
    foreach ($default_content as $content) {
        $insert_sql = "INSERT INTO website_content (section, title, content) VALUES (?, ?, ?) 
                      ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content)";
        $stmt = mysqli_prepare($data, $insert_sql);
        mysqli_stmt_bind_param($stmt, "sss", $content[0], $content[1], $content[2]);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "Default content for {$content[0]} section inserted successfully!<br>";
        } else {
            echo "Error inserting content for {$content[0]} section: " . mysqli_error($data) . "<br>";
        }
    }
    
} else {
    echo "Error creating table: " . mysqli_error($data);
}

mysqli_close($data);
echo "<br><a href='adminhome.php'>Go to Admin Dashboard</a>";
?> 