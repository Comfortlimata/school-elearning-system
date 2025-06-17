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

// Get student ID from URL
$student_id = $_GET['student_id'] ?? null;

if (!$student_id) {
    echo "Invalid student ID.";
    exit();
}

// Fetch student data
$stmt = $data->prepare("SELECT id, username, email, phone FROM user WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Student not found.";
    exit();
}

$student = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $update_stmt = $data->prepare("UPDATE user SET username = ?, email = ?, phone = ? WHERE id = ?");
    $update_stmt->bind_param("sssi", $username, $email, $phone, $student_id);

    if ($update_stmt->execute()) {
        $_SESSION['message'] = "Student updated successfully.";
        header("Location: view_student.php");
        exit();
    } else {
        echo "Failed to update student.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Update Student</title>

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

        .form-wrapper {
            width: 100%;
            max-width: 500px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        label {
            font-weight: bold;
            margin-bottom: 10px;
            display: block;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
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

        <h1>Update Student</h1>

        <div class="form-wrapper">
            <form method="POST">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($student['username']) ?>" required />

                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required />

                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($student['phone']) ?>" required />

                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>

        <button class="back-btn" onclick="window.location.href='adminhome.php'">Back to Dashboard</button>

    </div>

</body>

</html>

<?php
$stmt->close();
$data->close();
?>