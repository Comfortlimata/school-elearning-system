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

echo "<h2>Setting Teacher Passwords</h2>";

// Hash for 'teacher123'
$hashed_password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

// Update teachers who don't have passwords
$update_sql = "UPDATE teachers SET password = ? WHERE password = '' OR password IS NULL";
$stmt = mysqli_prepare($data, $update_sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $hashed_password);
    $result = mysqli_stmt_execute($stmt);
    
    if ($result) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        echo "<p style='color: green;'>✅ Successfully updated $affected_rows teacher(s) with password 'teacher123'</p>";
    } else {
        echo "<p style='color: red;'>❌ Error updating passwords: " . mysqli_stmt_error($stmt) . "</p>";
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "<p style='color: red;'>❌ Error preparing statement: " . mysqli_error($data) . "</p>";
}

// Show current status
echo "<br><h3>Current Teacher Password Status:</h3>";

$check_sql = "SELECT id, name, email, CASE WHEN password = '' OR password IS NULL THEN 'No Password' ELSE 'Password Set' END as password_status FROM teachers";
$check_result = mysqli_query($data, $check_sql);

if ($check_result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Password Status</th></tr>";
    
    while ($row = mysqli_fetch_assoc($check_result)) {
        $status_color = ($row['password_status'] == 'Password Set') ? 'green' : 'red';
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td style='color: $status_color;'>" . $row['password_status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Error checking status: " . mysqli_error($data) . "</p>";
}

echo "<br><h3>Login Information:</h3>";
echo "<p>Teachers can now login using:</p>";
echo "<ul>";
echo "<li><strong>Email:</strong> Their email address</li>";
echo "<li><strong>Password:</strong> teacher123</li>";
echo "</ul>";

echo "<br><a href='teacher_login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Teacher Login</a>";
echo "<br><br><a href='adminhome.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Dashboard</a>";

mysqli_close($data);
?> 