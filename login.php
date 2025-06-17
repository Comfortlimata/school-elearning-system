<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Form</title>

    <link rel="stylesheet" type="text/css" href="style.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body background="row.jpg" class="body_deg">
    <center>
        <div class="form_deg">
            <center class="title_deg">
                Login Form
                <h4>
                <?php
                error_reporting(0);
                session_start();
                
                echo $_SESSION['loginMessage'];
                unset($_SESSION['loginMessage']);
                ?>
            </h4>
            </center>

            <form action="login_check.php" method="POST" class="login_form">
                <div>
                    <label class="label_deg">Username</label>
                    <input type="text" name="username" required>
                </div>
                <div>
                    <label class="label_deg">Password</label>
                    <input type="password" name="password" required>
                </div>
                <div>
                    <label class="label_deg">Login as</label>
                    <select name="usertype" id="usertype" class="form-control" required>
                        <option value="admin">Admin</option>
                        <option value="student">Student</option>
                    </select>
                </div>
                <div id="programField" style="display: none;">
                    <label class="label_deg">Program</label>
                    <select name="program" class="form-control">
                        <option value="Computer Science">Computer Science</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Mathematics">Mathematics</option>
                        <option value="Business Administration">Business Administration</option>
                    </select>
                </div>
                <div>
                    <input class="btn btn-primary" type="submit" name="submit" value="Login">
                </div>
            </form>
        </div>
    </center>

    <script>
        // Show or hide the program dropdown based on user type
        const userType = document.getElementById('usertype');
        const programField = document.getElementById('programField');

        userType.addEventListener('change', () => {
            programField.style.display = userType.value === 'student' ? 'block' : 'none';
        });
    </script>
</body>
</html>