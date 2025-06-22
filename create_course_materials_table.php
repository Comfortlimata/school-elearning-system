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

echo "<h2>Creating Course Materials Table</h2>";

// Create course_materials table
$create_materials_sql = "CREATE TABLE IF NOT EXISTS course_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT,
    file_type VARCHAR(50),
    download_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teacher(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_teacher_course (teacher_id, course_id),
    INDEX idx_created_at (created_at)
)";

if (mysqli_query($data, $create_materials_sql)) {
    echo "<p style='color: green;'>✅ Course materials table created successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating materials table: " . mysqli_error($data) . "</p>";
}

// Insert sample materials data
echo "<h3>Inserting Sample Materials</h3>";

$sample_materials = [
    [
        'teacher_id' => 1,
        'course_id' => 1,
        'title' => 'Mathematics Chapter 1 Notes',
        'description' => 'Comprehensive notes covering basic algebra concepts',
        'file_path' => 'uploads/sample_math_notes.pdf',
        'file_name' => 'Mathematics_Chapter_1.pdf',
        'file_size' => 2048576,
        'file_type' => 'pdf'
    ],
    [
        'teacher_id' => 2,
        'course_id' => 2,
        'title' => 'Physics Lab Manual',
        'description' => 'Laboratory manual for physics experiments',
        'file_path' => 'uploads/physics_lab_manual.pdf',
        'file_name' => 'Physics_Lab_Manual.pdf',
        'file_size' => 1536000,
        'file_type' => 'pdf'
    ],
    [
        'teacher_id' => 3,
        'course_id' => 3,
        'title' => 'Programming Assignment Guidelines',
        'description' => 'Guidelines for programming assignments and projects',
        'file_path' => 'uploads/programming_guidelines.docx',
        'file_name' => 'Programming_Guidelines.docx',
        'file_size' => 512000,
        'file_type' => 'docx'
    ]
];

foreach ($sample_materials as $material) {
    $insert_material_sql = "INSERT INTO course_materials (teacher_id, course_id, title, description, file_path, file_name, file_size, file_type) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($data, $insert_material_sql);
    mysqli_stmt_bind_param($stmt, "iissssis", $material['teacher_id'], $material['course_id'], $material['title'], $material['description'], $material['file_path'], $material['file_name'], $material['file_size'], $material['file_type']);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p>✅ Added sample material: {$material['title']}</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to add material: " . mysqli_error($data) . "</p>";
    }
}

echo "<h3>Course Materials Table Created Successfully!</h3>";
echo "<p>✅ Course materials table - for teachers to upload study materials</p>";
echo "<p>✅ File upload support with size and type validation</p>";
echo "<p>✅ Download tracking and material management</p>";

echo "<h3>Features:</h3>";
echo "<ul>";
echo "<li>Teachers can upload course materials (PDF, DOC, DOCX, PPT, PPTX, TXT, JPG, PNG)</li>";
echo "<li>File size validation (max 10MB)</li>";
echo "<li>Download tracking for analytics</li>";
echo "<li>Material categorization by course</li>";
echo "<li>Active/inactive material status</li>";
echo "</ul>";

echo "<p><a href='teacher_content_management.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Teacher Content Management</a></p>";

mysqli_close($data);
?> 