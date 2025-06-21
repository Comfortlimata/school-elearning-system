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

// Add password column to teachers table
$add_password_sql = "ALTER TABLE teachers ADD COLUMN password VARCHAR(255) NOT NULL DEFAULT '' AFTER email";

if (mysqli_query($data, $add_password_sql)) {
    echo "Password column added successfully!<br>";
    
    // Update existing teachers with hashed passwords
    $default_password = 'teacher123';
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
    
    $update_sql = "UPDATE teachers SET password = ? WHERE password = '' OR password IS NULL";
    $stmt = mysqli_prepare($data, $update_sql);
    mysqli_stmt_bind_param($stmt, "s", $hashed_password);
    
    if (mysqli_stmt_execute($stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        echo "Updated $affected_rows teachers with default password: '$default_password'<br>";
    } else {
        echo "Error updating passwords: " . mysqli_error($data) . "<br>";
    }
    
} else {
    echo "Error adding password column: " . mysqli_error($data) . "<br>";
}

// Show current teachers and their emails
echo "<br><h3>Current Teachers:</h3>";
$teachers_sql = "SELECT name, email, specialization FROM teachers";
$teachers_result = mysqli_query($data, $teachers_sql);

if (mysqli_num_rows($teachers_result) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Name</th><th>Email</th><th>Specialization</th><th>Default Password</th></tr>";
    
    while ($teacher = mysqli_fetch_assoc($teachers_result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($teacher['name']) . "</td>";
        echo "<td>" . htmlspecialchars($teacher['email']) . "</td>";
        echo "<td>" . htmlspecialchars($teacher['specialization']) . "</td>";
        echo "<td>teacher123</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No teachers found in database.";
}

mysqli_close($data);
echo "<br><br><a href='adminhome.php'>Go to Admin Dashboard</a>";
?> 