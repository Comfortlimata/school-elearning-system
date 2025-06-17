<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
} elseif ($_SESSION['usertype'] == 'student') {
    header("location:login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";

$data = mysqli_connect($host, $user, $password, $db);

$sql = "SELECT * FROM admission";

$result = mysqli_query($data, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admission Section</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .content {
            padding: 40px 20px;
            text-align: center;
        }

        h1 {
            color: #007bff;
            margin-bottom: 30px;
        }

        .table {
            width: 80%;
            margin: 0 auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        th {
            background-color: #007bff;
            color: white;
            font-size: 16px;
            padding: 10px;
        }

        td {
            padding: 8px;
            font-size: 14px;
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
        <h1>Applied For Admission</h1>

        <table class="table table-bordered">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
            </tr>

            <?php
            while ($info = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$info['name']}</td>
                        <td>{$info['email']}</td>
                        <td>{$info['phone']}</td>
                      </tr>";
            }
            ?>
        </table>

        <button class="back-btn" onclick="window.location.href='adminhome.php'">Back to Dashboard</button>
    </div>

</body>

</html>