<?php
// FIX FOR add_teacher.php - Replace the problematic section

// The issue is in the bind_param call. Here's the correct version:

// SQL has 17 placeholders:
$sql = "INSERT INTO teachers (
    name, email, phone, specialization, qualification, experience_years, 
    bio, image, department, office_location, office_hours, linkedin_url, 
    twitter_url, facebook_url, salary, hire_date, status
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($data, $sql);

// FIXED: Exactly 17 parameters with correct type string
mysqli_stmt_bind_param($stmt, "sssssissssssssds", 
    $name,           // 1. s
    $email,          // 2. s
    $phone,          // 3. s
    $specialization, // 4. s
    $qualification,  // 5. s
    $experience_years, // 6. i
    $bio,            // 7. s
    $image_path,     // 8. s
    $department,     // 9. s
    $office_location, // 10. s
    $office_hours,   // 11. s
    $linkedin_url,   // 12. s
    $twitter_url,    // 13. s
    $facebook_url,   // 14. s
    $salary,         // 15. d
    $hire_date,      // 16. s
    $status          // 17. s
);

// Total: 17 parameters, type string: "sssssissssssssds" (17 characters)
?> 