


<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login Form</title>

	<link rel="stylesheet" type="text/css" href="style.css">

	<!-- Bootstrap CSS (MaxCDN) -->
	 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

	<!-- Bootstrap JavaScript and Popper.js -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.bundle.min.js" integrity="sha384-lif1I5EcQzNXM7mAExQPYzndBdb7DpGojirQ59IIVX5Cp3xEVT2GAo3FbXpvgoqS" crossorigin="anonymous"></script>
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
    				<input type="text" name="username">
    			</div>
    			<div>
    				<label class="label_deg">Password</label>
    				<input type="password" name="password">
    			</div>
    			<div>
    				<input class="btn btn-primary" type="submit" name="submit" value="Login" class="btn btn-primary">
    			</div>
    		</form>
    	</div>
    </center>
</body>
</html>