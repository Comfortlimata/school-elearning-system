<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = mysqli_connect("localhost", "root", "", "schoolproject");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if form fields are set
    if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['usertype'])) {
        $_SESSION['loginMessage'] = "Please fill in all fields.";
        header("Location: login.php");
        exit();
    }

    // Sanitize user input
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $usertype = mysqli_real_escape_string($conn, $_POST['usertype']);

    if ($usertype === 'admin') {
        // Admin login logic
        $sql = "SELECT * FROM user WHERE username = '$username' AND password = '$password'";
    } elseif ($usertype === 'student') {
        // Check if program is set
        if (!isset($_POST['program']) || empty($_POST['program'])) {
            $_SESSION['loginMessage'] = "Please select a program.";
            header("Location: login.php");
            exit();
        }

        $program = mysqli_real_escape_string($conn, $_POST['program']);
        $sql = "SELECT * FROM students WHERE username = '$username' AND password = '$password' AND program = '$program'";
    } else {
        $sql = '';
    }

    if ($sql !== '') {
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            die("Query error: " . mysqli_error($conn));
        }

        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['username'] = $user['username'];
            $_SESSION['usertype'] = $usertype;

            if ($usertype === 'admin') {
                header("Location: adminhome.php");
            } elseif ($usertype === 'student') {
                $_SESSION['program'] = $program; // Set the program session variable for students
                header("Location: studenthome.php");
            }
            exit();
        } else {
            $_SESSION['loginMessage'] = "Invalid username or password.";
            header("Location: login.php");
            exit();
        }
    }
}
?>