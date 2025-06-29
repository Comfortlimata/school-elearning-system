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

echo "<h2>Updating Admission Table Structure</h2>";

// Check if the admission table exists
$check_table = "SHOW TABLES LIKE 'admission'";
$table_exists = mysqli_query($data, $check_table);

if (mysqli_num_rows($table_exists) == 0) {
    // Create the admission table if it doesn't exist
    $create_table = "CREATE TABLE admission (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        grade VARCHAR(100) NOT NULL,
        section VARCHAR(10) NOT NULL,
        message TEXT NOT NULL,
        status VARCHAR(50) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (mysqli_query($data, $create_table)) {
        echo "<p style='color: green;'>✓ Admission table created successfully with grade and section fields.</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating admission table: " . mysqli_error($data) . "</p>";
    }
} else {
    // Check if program column exists
    $check_program = "SHOW COLUMNS FROM admission LIKE 'program'";
    $program_exists = mysqli_query($data, $check_program);
    
    if (mysqli_num_rows($program_exists) > 0) {
        // Add grade column if it doesn't exist
        $check_grade = "SHOW COLUMNS FROM admission LIKE 'grade'";
        $grade_exists = mysqli_query($data, $check_grade);
        
        if (mysqli_num_rows($grade_exists) == 0) {
            $add_grade = "ALTER TABLE admission ADD COLUMN grade VARCHAR(100) AFTER phone";
            if (mysqli_query($data, $add_grade)) {
                echo "<p style='color: green;'>✓ Grade column added successfully.</p>";
            } else {
                echo "<p style='color: red;'>✗ Error adding grade column: " . mysqli_error($data) . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ Grade column already exists.</p>";
        }
        
        // Add section column if it doesn't exist
        $check_section = "SHOW COLUMNS FROM admission LIKE 'section'";
        $section_exists = mysqli_query($data, $check_section);
        
        if (mysqli_num_rows($section_exists) == 0) {
            $add_section = "ALTER TABLE admission ADD COLUMN section VARCHAR(10) AFTER grade";
            if (mysqli_query($data, $add_section)) {
                echo "<p style='color: green;'>✓ Section column added successfully.</p>";
            } else {
                echo "<p style='color: red;'>✗ Error adding section column: " . mysqli_error($data) . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ Section column already exists.</p>";
        }
        
        // Copy data from program to grade (if needed)
        $copy_data = "UPDATE admission SET grade = program WHERE grade IS NULL OR grade = ''";
        if (mysqli_query($data, $copy_data)) {
            echo "<p style='color: green;'>✓ Data copied from program to grade column.</p>";
        } else {
            echo "<p style='color: red;'>✗ Error copying data: " . mysqli_error($data) . "</p>";
        }
        
        // Drop the program column
        $drop_program = "ALTER TABLE admission DROP COLUMN program";
        if (mysqli_query($data, $drop_program)) {
            echo "<p style='color: green;'>✓ Program column dropped successfully.</p>";
        } else {
            echo "<p style='color: red;'>✗ Error dropping program column: " . mysqli_error($data) . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ Program column doesn't exist. Checking for grade and section columns...</p>";
        
        // Check if grade column exists
        $check_grade = "SHOW COLUMNS FROM admission LIKE 'grade'";
        $grade_exists = mysqli_query($data, $check_grade);
        
        if (mysqli_num_rows($grade_exists) == 0) {
            $add_grade = "ALTER TABLE admission ADD COLUMN grade VARCHAR(100) AFTER phone";
            if (mysqli_query($data, $add_grade)) {
                echo "<p style='color: green;'>✓ Grade column added successfully.</p>";
            } else {
                echo "<p style='color: red;'>✗ Error adding grade column: " . mysqli_error($data) . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ Grade column already exists.</p>";
        }
        
        // Check if section column exists
        $check_section = "SHOW COLUMNS FROM admission LIKE 'section'";
        $section_exists = mysqli_query($data, $check_section);
        
        if (mysqli_num_rows($section_exists) == 0) {
            $add_section = "ALTER TABLE admission ADD COLUMN section VARCHAR(10) AFTER grade";
            if (mysqli_query($data, $add_section)) {
                echo "<p style='color: green;'>✓ Section column added successfully.</p>";
            } else {
                echo "<p style='color: red;'>✗ Error adding section column: " . mysqli_error($data) . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ Section column already exists.</p>";
        }
    }
}

// Also update the students table if needed
$check_students_program = "SHOW COLUMNS FROM students LIKE 'program'";
$students_program_exists = mysqli_query($data, $check_students_program);

if (mysqli_num_rows($students_program_exists) > 0) {
    // Check if grade column exists in students table
    $check_students_grade = "SHOW COLUMNS FROM students LIKE 'grade'";
    $students_grade_exists = mysqli_query($data, $check_students_grade);
    
    if (mysqli_num_rows($students_grade_exists) == 0) {
        $add_students_grade = "ALTER TABLE students ADD COLUMN grade VARCHAR(100) AFTER password";
        if (mysqli_query($data, $add_students_grade)) {
            echo "<p style='color: green;'>✓ Grade column added to students table successfully.</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding grade column to students table: " . mysqli_error($data) . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ Grade column already exists in students table.</p>";
    }
    
    // Copy data from program to grade in students table
    $copy_students_data = "UPDATE students SET grade = program WHERE grade IS NULL OR grade = ''";
    if (mysqli_query($data, $copy_students_data)) {
        echo "<p style='color: green;'>✓ Data copied from program to grade in students table.</p>";
    } else {
        echo "<p style='color: red;'>✗ Error copying data in students table: " . mysqli_error($data) . "</p>";
    }
    
    // Drop the program column from students table
    $drop_students_program = "ALTER TABLE students DROP COLUMN program";
    if (mysqli_query($data, $drop_students_program)) {
        echo "<p style='color: green;'>✓ Program column dropped from students table successfully.</p>";
    } else {
        echo "<p style='color: red;'>✗ Error dropping program column from students table: " . mysqli_error($data) . "</p>";
    }
}

echo "<h3>Database Update Complete!</h3>";
echo "<p><a href='index.php'>Return to Home Page</a></p>";
echo "<p><a href='admission.php'>Go to Admin Admission</a></p>";
?> 