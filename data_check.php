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
    $grade = mysqli_real_escape_string($data, $_POST['grade']);
    $section = mysqli_real_escape_string($data, $_POST['section']);
    $message = mysqli_real_escape_string($data, $_POST['message']);

    // Insert data into the database with status and created_at
    $sql = "INSERT INTO admission (name, email, phone, grade, section, message, status, created_at) 
            VALUES ('$name', '$email', '$phone', '$grade', '$section', '$message', 'Pending', NOW())";
    $result = mysqli_query($data, $sql);

    if ($result) {
        $_SESSION['message'] = "Admission application submitted successfully!";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['message'] = "Error submitting application: " . mysqli_error($data);
        header("Location: index.php");
        exit();
    }
}

// Get all admission data for display
$sql = "SELECT * FROM admission ORDER BY created_at DESC";
$result = mysqli_query($data, $sql);

if (!$result) {
    die("Error fetching data: " . mysqli_error($data));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admission Applications - Admin Dashboard</title>
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
                <th style="padding: 10px; font-size: 10px;">Grade</th>
                <th style="padding: 10px; font-size: 10px;">Section</th>
                <th style="padding: 10px; font-size: 10px;">Message</th>
                <th style="padding: 10px; font-size: 10px;">Status</th>
                <th style="padding: 10px; font-size: 10px;">Date Applied</th>
            </tr>
            <?php 
            if (mysqli_num_rows($result) > 0) {
                while ($info = mysqli_fetch_assoc($result)) { 
            ?>
                <tr>
                    <td style='padding: 8px;'><?php echo htmlspecialchars($info['name']); ?></td>
                    <td style='padding: 8px;'><?php echo htmlspecialchars($info['email']); ?></td>
                    <td style='padding: 8px;'><?php echo htmlspecialchars($info['phone']); ?></td>
                    <td style='padding: 8px;'><?php echo htmlspecialchars($info['grade']); ?></td>
                    <td style='padding: 8px;'><?php echo htmlspecialchars($info['section']); ?></td>
                    <td style='padding: 8px;'><?php echo nl2br(htmlspecialchars($info['message'])); ?></td>
                    <td style='padding: 8px;'>
                        <?php 
                        $status = $info['status'] ?? 'Pending';
                        $status_class = '';
                        switch($status) {
                            case 'Approved': $status_class = 'badge bg-success'; break;
                            case 'Rejected': $status_class = 'badge bg-danger'; break;
                            default: $status_class = 'badge bg-warning'; break;
                        }
                        ?>
                        <span class="<?php echo $status_class; ?>"><?php echo $status; ?></span>
                    </td>
                    <td style='padding: 8px;'>
                        <?php echo date('M d, Y', strtotime($info['created_at'] ?? 'now')); ?>
                    </td>
                </tr>
            <?php 
                }
            } else {
                echo '<tr><td colspan="7" class="text-center">No applications found</td></tr>';
            }
            ?>
        </table>
    </div>
    
    <div class="text-center mt-3">
        <a href="index.php" class="btn btn-primary">Back to Home</a>
        <a href="admission.php" class="btn btn-secondary">Go to Admin Admission</a>
    </div>
</div>
</body>
</html>