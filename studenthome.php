<?php

session_start();

if(!isset($_SESSION['username']))
{
header("location:login.php");
exit();
}
elseif($_SESSION['usertype']=='adimin')
{
header("location:login.php");
exit(); 

}


?>



<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Admin Dashboard</title>

	<link rel="stylesheet" type="text/css" href="admin.css ">

	<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<!-- Bootstrap JavaScript & Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

	<header class="header">

		<a href="">StudentDashboard</a>

		<div class="logout">

		<a href="logout.php"class="btn btn-primary">Logout</a>

		</div>
		
	</header>

  <aside>

  	<ul>
  		<li>
  			<a href="">My Courses</a>
  		</li>

  		<li>
  		   <a href="">My Results</a>
  		   </li>   	

  	</ul>
  
  </aside>

</body>

</html>