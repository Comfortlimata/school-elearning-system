<?php
session_start();

// Redirect unauthorized users
if (!isset($_SESSION['username']) || $_SESSION['usertype'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "schoolproject");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password for security
    $usertype = "student";

    // Check if the username already exists
    $check_query = "SELECT id FROM user WHERE username = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Username already exists. Please choose another one.');</script>";
    } else {
        // Insert the new student into the database
        $insert_query = "INSERT INTO user (username, email, phone, password, usertype) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sssss", $username, $email, $phone, $password, $usertype);

        if ($stmt->execute()) {
            echo "<script>alert('Student added successfully.');</script>";
        } else {
            echo "<script>alert('Failed to add student.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add Student</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
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
            text-align: center;
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

        /* Media Query for Mobile Devices */
        @media (max-width: 768px) {
            .form-wrapper {
                width: 90%;
                padding: 15px;
            }

            h1 {
                font-size: 24px;
            }

            .btn,
            .back-btn {
                font-size: 14px;
                padding: 8px 16px;
            }
        }
    </style>
</head>

<body>

    <div class="content">

        <h1>Add Student</h1>

        <div class="form-wrapper">
            <form method="POST">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required />

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required />

                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" required />

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required />

                <button type="submit" class="btn btn-primary">Add Student</button>
            </form>
        </div>

        <button class="back-btn" onclick="window.location.href='adminhome.php'">Back to Dashboard</button>

    </div>

</body>

</html>

<?php
$conn->close();
?>