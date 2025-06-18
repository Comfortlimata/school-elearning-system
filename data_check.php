<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$data = mysqli_connect($host, $user, $password, $db);

if (!$data) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Handle form submission
if (isset($_POST['apply'])) {
    // Sanitize user input
    $name = mysqli_real_escape_string($data, $_POST['name']);
    $email = mysqli_real_escape_string($data, $_POST['email']);
    $phone = mysqli_real_escape_string($data, $_POST['phone']);
    $program = mysqli_real_escape_string($data, $_POST['program']);
    $message = mysqli_real_escape_string($data, $_POST['message']);

    // Insert data into the database
    $sql = "INSERT INTO admission (name, email, phone, program, message) VALUES ('$name', '$email', '$phone', '$program', '$message')";
    $result = mysqli_query($data, $sql);

    if ($result) {
        echo "<script>alert('Admission application submitted successfully.');</script>";
    } else {
        echo "<script>alert('Error submitting application: " . mysqli_error($data) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <?php include 'admin_css.php'; ?>
</head>
<body>
<div class="content">
    <div class="d-flex justify-content-center">
        <table class="table table-bordered" style="width: 80%;">
            <tr>
                <th style="padding: 10px; font-size: 10px;">Name</th>
                <th style="padding: 10px; font-size: 10px;">Email</th>
                <th style="padding: 10px; font-size: 10px;">Phone</th>
                <th style="padding: 10px; font-size: 10px;">Program</th>
                <th style="padding: 10px; font-size: 10px;">Message</th>
            </tr>
            <?php while ($info = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td style='padding: 8px;'><?php echo htmlspecialchars($info['name']); ?></td>
                    <td style='padding: 8px;'><?php echo htmlspecialchars($info['email']); ?></td>
                    <td style='padding: 8px;'><?php echo htmlspecialchars($info['phone']); ?></td>
                    <td style='padding: 8px;'><?php echo htmlspecialchars($info['program']); ?></td>
                    <td style='padding: 8px;'><?php echo nl2br(htmlspecialchars($info['message'])); ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>
</body>
</html>