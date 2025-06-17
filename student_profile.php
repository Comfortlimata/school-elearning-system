
 
<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
} elseif ($_SESSION['usertype'] == 'admin') {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$data = mysqli_connect($host, $user, $password, $db);

if (!$data) {
    die("Connection failed: " . mysqli_connect_error());
}

$name = $_SESSION['username'];

// Get current user info
$sql = "SELECT * FROM user WHERE username='$name'";
$result = mysqli_query($data, $sql);
$info = mysqli_fetch_assoc($result);

// Update profile
if (isset($_POST['update_student'])) {
    $s_email = $_POST['email'];
    $s_phone = $_POST['phone'];
    $s_password = $_POST['password']; // Store plain for now; see note below to hash

    // UPDATE query
    $sql2="UPDATE user SET email='$s_email', phone='$s_phone', password='$s_password' WHERE username='$name'";
    $result2=mysqli_query($data, $sql2);

    if ($result2) {
        echo "<script>alert('Profile updated successfully.'); window.location.href='update_profile.php';</script>";
        exit();
    } else {
        echo "Update Failed: " . mysqli_error($data);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Dashboard</title>

    <link rel="stylesheet" type="text/css" href="admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php include 'student_css.php'; ?>

    <style>
        .label {
            display: inline-block;
            text-align: right;
            width: 200px;
            padding-top: 20px;
            padding-bottom: 15px;
        }
        .div_deg {
            background-color: skyblue;
            width: 500px;
            padding: 70px 20px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<?php include 'student_sidebar.php'; ?>

<div class="content">
    <center>
        <h1>Update Profile</h1>
        <br><br>

        <form method="post" action="update_student.php">
            <div class="div_deg">

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($info['email']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($info['phone']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="text" class="form-control" name="password" value="<?php echo htmlspecialchars($info['password']); ?>" required>
                </div>

                <div>
                    <input type="submit" class="btn btn-primary" name="update_" value="Update Student">
                </div>
            </div>
        </form>
    </center>
</div>

</body>
</html>