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

    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $usertype = $_POST['usertype'] ?? '';

    if (empty($username) || empty($password) || empty($usertype)) {
        $_SESSION['loginMessage'] = "Please fill in all fields.";
        header("Location: login.php");
        exit();
    }

    // Admin login
    if ($usertype === 'admin') {
        $sql = "SELECT * FROM user WHERE username = '$username'";
    }
    // Teacher login
    elseif ($usertype === 'teacher') {
        $sql = "SELECT * FROM teacher WHERE username = '$username'";
    }
    // Student login
    elseif ($usertype === 'student') {
        $sql = "SELECT * FROM students WHERE username = '$username'";
    }
    // Invalid type
    else {
        $_SESSION['loginMessage'] = "Invalid user type.";
        header("Location: login.php");
        exit();
    }

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Password check (plain-text or hashed — adjust as needed)
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['usertype'] = $usertype;

            if ($usertype === 'admin') {
                // Check if adminhome.php exists
                if (file_exists('adminhome.php')) {
                    header("Location: adminhome.php");
                } else {
                    $_SESSION['loginMessage'] = "Admin dashboard not found. Please contact administrator.";
                    header("Location: login.php");
                }
            } elseif ($usertype === 'teacher') {
                $_SESSION['teacher_id'] = $user['id'];
                $_SESSION['teacher_email'] = $user['email'] ?? '';
                $_SESSION['teacher_name'] = $user['name'] ?? $user['full_name'] ?? '';
                $_SESSION['specialization'] = $user['specialization'] ?? '';
                
                // Check if teacherhome.php exists
                if (file_exists('teacherhome.php')) {
                    header("Location: teacherhome.php");
                } else {
                    $_SESSION['loginMessage'] = "Teacher dashboard not found. Please contact administrator.";
                    header("Location: login.php");
                }
            } elseif ($usertype === 'student') {
                $_SESSION['student_id'] = $user['id'];
                $_SESSION['grade_id'] = $user['grade_id'];
                $_SESSION['section'] = $user['section'] ?? '';
                // Optionally, fetch grade name for display
                $grade_name = '';
                $grade_res = mysqli_query($conn, "SELECT name FROM grades WHERE id = " . intval($user['grade_id']));
                if ($grade_res && mysqli_num_rows($grade_res) > 0) {
                    $grade_row = mysqli_fetch_assoc($grade_res);
                    $grade_name = $grade_row['name'];
                }
                $_SESSION['grade_name'] = $grade_name;
                // Check if studenthome.php exists
                if (file_exists('studenthome.php')) {
                    header("Location: studenthome.php");
                } else {
                    $_SESSION['loginMessage'] = "Student dashboard not found. Please contact administrator.";
                    header("Location: login.php");
                }
            }
            exit();
        } else {
            $_SESSION['loginMessage'] = "Invalid password.";
        }
    } else {
        if ($usertype === 'teacher') {
            $_SESSION['loginMessage'] = "Teacher not found or account inactive.";
        } else {
            $_SESSION['loginMessage'] = "Invalid login details.";
        }
    }

    header("Location: login.php");
    exit();
}
?>
