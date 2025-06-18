<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
} elseif ($_SESSION['usertype'] == 'students') {
    header("location:login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$data = mysqli_connect($host, $user, $password, $db);

if (!$data) {
    die("Database connection failed: " . mysqli_connect_error());
}

$sql = "SELECT * FROM admission";
$result = mysqli_query($data, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 40px;
        }
        .table {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        th, td {
            text-align: center;
            vertical-align: middle;
        }
        .home-btn {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="text-center">
        <h1 class="mb-4">Applied For Admission</h1>
        <a href="adminhome.php" class="btn btn-secondary">Back to Home</a>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-primary">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Program</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($info = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($info['name']); ?></td>
                        <td><?php echo htmlspecialchars($info['email']); ?></td>
                        <td><?php echo htmlspecialchars($info['phone']); ?></td>
                        <td><?php echo htmlspecialchars($info['program']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($info['message'])); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>