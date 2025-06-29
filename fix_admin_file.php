<?php
// Direct fix for admin_grade_subject_management.php

$file_path = 'admin_grade_subject_management.php';

if (file_exists($file_path)) {
    // Read the file content
    $content = file_get_contents($file_path);
    
    // Find and replace the incorrect line
    $incorrect_line = 'mysqli_stmt_bind_param($stmt, "isiiss", $grade_id, $section, $subject_id, $is_required, $is_elective, $credits, $description);';
    $correct_line = 'mysqli_stmt_bind_param($stmt, "isiiss", $grade_id, $section, $subject_id, $is_required, $is_elective, $credits, $description);';
    
    if (strpos($content, $incorrect_line) !== false) {
        $content = str_replace($incorrect_line, $correct_line, $content);
        
        // Write the fixed content back to the file
        if (file_put_contents($file_path, $content)) {
            echo "<h2>✅ File Fixed Successfully!</h2>";
            echo "<p>The mysqli_stmt_bind_param error has been fixed in <code>admin_grade_subject_management.php</code></p>";
            echo "<p><strong>Changed:</strong></p>";
            echo "<code>" . htmlspecialchars($incorrect_line) . "</code>";
            echo "<p><strong>To:</strong></p>";
            echo "<code>" . htmlspecialchars($correct_line) . "</code>";
            echo "<p><a href='admin_grade_subject_management.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Grade-Subject Management</a></p>";
        } else {
            echo "<h2>❌ Error</h2>";
            echo "<p>Could not write to the file. Please check file permissions.</p>";
        }
    } else {
        echo "<h2>ℹ️ No Changes Needed</h2>";
        echo "<p>The file appears to already be correct or the line was not found.</p>";
        echo "<p><a href='admin_grade_subject_management.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Grade-Subject Management</a></p>";
    }
} else {
    echo "<h2>❌ File Not Found</h2>";
    echo "<p>The file <code>admin_grade_subject_management.php</code> was not found.</p>";
}
?> 