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

// Check if status column exists
$check_status = mysqli_query($data, "SHOW COLUMNS FROM admission LIKE 'status'");
if (mysqli_num_rows($check_status) == 0) {
    // Add status column
    $add_status = "ALTER TABLE admission ADD COLUMN status VARCHAR(20) DEFAULT 'Pending'";
    if (mysqli_query($data, $add_status)) {
        echo "<p style='color: green;'>✅ Status column added successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error adding status column: " . mysqli_error($data) . "</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ️ Status column already exists</p>";
}

// Check if created_at column exists
$check_created_at = mysqli_query($data, "SHOW COLUMNS FROM admission LIKE 'created_at'");
if (mysqli_num_rows($check_created_at) == 0) {
    // Add created_at column
    $add_created_at = "ALTER TABLE admission ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    if (mysqli_query($data, $add_created_at)) {
        echo "<p style='color: green;'>✅ Created_at column added successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error adding created_at column: " . mysqli_error($data) . "</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ️ Created_at column already exists</p>";
}

// Check if id column exists (primary key)
$check_id = mysqli_query($data, "SHOW COLUMNS FROM admission LIKE 'id'");
if (mysqli_num_rows($check_id) == 0) {
    // Add id column as primary key
    $add_id = "ALTER TABLE admission ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST";
    if (mysqli_query($data, $add_id)) {
        echo "<p style='color: green;'>✅ ID column added successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error adding ID column: " . mysqli_error($data) . "</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ️ ID column already exists</p>";
}

// Update existing records to have 'Pending' status if they don't have one
$update_status = "UPDATE admission SET status = 'Pending' WHERE status IS NULL OR status = ''";
if (mysqli_query($data, $update_status)) {
    echo "<p style='color: green;'>✅ Updated existing records with default status</p>";
} else {
    echo "<p style='color: red;'>❌ Error updating existing records: " . mysqli_error($data) . "</p>";
}

// Show current table structure
echo "<h3>Current Admission Table Structure:</h3>";
$structure = mysqli_query($data, "DESCRIBE admission");
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

echo "<br><p><a href='admission.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admission Management</a></p>";
?> 