<?php
session_start();
error_reporting(0);

// Redirect unauthorized users
if (!isset($_SESSION['username']) || $_SESSION['usertype'] == 'student') {
    header("Location: login.php");
    exit();
}

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$data = new mysqli($host, $user, $password, $db);

if ($data->connect_error) {
    die("Connection failed: " . $data->connect_error);
}

// Use prepared statement for security
$stmt = $data->prepare("SELECT id, username, email, phone, password FROM user WHERE usertype = ?");
$usertype = "student";
$stmt->bind_param("s", $usertype);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>View Student</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        body,
        html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f7f9fc;
        }

        .content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            padding: 40px 20px;
        }

        h1 {
            color: #007bff;
            margin-bottom: 30px;
        }

        .message {
            margin-bottom: 20px;
            color: green;
            font-weight: 600;
        }

        .table-wrapper {
            width: 100%;
            max-width: 900px;
            overflow-x: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th,
        td {
            padding: 15px 20px;
        }

        th {
            background-color: #007bff;
            color: white;
            font-size: 18px;
        }

        td {
            background-color: #d0e7ff;
            font-size: 16px;
        }

        a.btn {
            padding: 8px 14px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
        }

        a.btn-danger {
            background-color: #dc3545;
            color: white;
            transition: background-color 0.3s ease;
        }

        a.btn-danger:hover {
            background-color: #b52a37;
        }

        a.btn-primary {
            background-color: #007bff;
            color: white;
            transition: background-color 0.3s ease;
        }

        a.btn-primary:hover {
            background-color: #0056b3;
        }

        .back-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .back-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

    <div class="content">

        <h1>Student Data</h1>

        <?php if (!empty($_SESSION['message'])) : ?>
            <div class="message">
                <?php
                echo htmlspecialchars($_SESSION['message']);
                unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="table-wrapper">
            <table border="1">
                <thead>
                    <tr>
                        <th>UserName</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Password</th>
                        <th>Delete</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($info = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?= htmlspecialchars($info['username']) ?></td>
                            <td><?= htmlspecialchars($info['email']) ?></td>
                            <td><?= htmlspecialchars($info['phone']) ?></td>
                            <td><?= htmlspecialchars($info['password']) ?></td>
                            <td>
                                <a onclick="return confirm('Are you sure to Delete this?')" class="btn btn-danger" href="delete.php?student_id=<?= urlencode($info['id']) ?>">Delete</a>
                            </td>
                            <td>
                                <a class="btn btn-primary" href="Update_student.php?student_id=<?= urlencode($info['id']) ?>">Update</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <button class="back-btn" onclick="window.location.href='adminhome.php'">Back to Dashboard</button>

    </div>

</body>

</html>

<?php
$stmt->close();
$data->close();
?>