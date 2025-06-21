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

echo "<h2>Teachers Table Structure</h2>";

// Check table structure
$structure_sql = "DESCRIBE teachers";
$structure_result = mysqli_query($data, $structure_sql);

if ($structure_result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($structure_result)) {
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
} else {
    echo "Error checking table structure: " . mysqli_error($data);
}

echo "<br><h2>Current Teachers Data</h2>";

// Check current teachers data
$teachers_sql = "SELECT id, name, email, specialization, password FROM teachers LIMIT 5";
$teachers_result = mysqli_query($data, $teachers_sql);

if ($teachers_result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Specialization</th><th>Password Status</th></tr>";
    
    while ($row = mysqli_fetch_assoc($teachers_result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['specialization']) . "</td>";
        echo "<td>";
        if (!empty($row['password'])) {
            echo "<span style='color: green;'>Password Set</span>";
        } else {
            echo "<span style='color: red;'>No Password</span>";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error checking teachers data: " . mysqli_error($data);
}

echo "<br><h2>Next Steps</h2>";

// Check if password column exists
$check_password_sql = "SHOW COLUMNS FROM teachers LIKE 'password'";
$check_result = mysqli_query($data, $check_password_sql);

if (mysqli_num_rows($check_result) > 0) {
    echo "<p style='color: green;'>✅ Password column already exists!</p>";
    echo "<p>You can now set passwords using this SQL:</p>";
    echo "<pre style='background: #f4f4f4; padding: 10px; border-radius: 5px;'>";
    echo "UPDATE teachers SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' \n";
    echo "WHERE password = '' OR password IS NULL;";
    echo "</pre>";
    echo "<p>This will set the password 'teacher123' for all teachers who don't have a password yet.</p>";
} else {
    echo "<p style='color: red;'>❌ Password column does not exist!</p>";
    echo "<p>You need to add it first:</p>";
    echo "<pre style='background: #f4f4f4; padding: 10px; border-radius: 5px;'>";
    echo "ALTER TABLE teachers ADD COLUMN password VARCHAR(255) NOT NULL DEFAULT '' AFTER email;";
    echo "</pre>";
}

mysqli_close($data);
echo "<br><a href='adminhome.php'>Go to Admin Dashboard</a>";
?> 