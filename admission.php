
<?php

session_start();

if (!isset($_SESSION['username']))
{
header("location:login.php");
exit();
}
elseif($_SESSION['usertype']=='student')
{
	header("location:login.php");
	exit();
	
}

$host="localhost";
$user="root"; 
$password="";
$db="schoolproject";

$data=mysqli_connect($host, $user, $password, $db);
 
$sql = "SELECT * FROM admission";

$result = mysqli_query($data, $sql);
?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Admin Dashboard</title>
   
   <?php

include 'admin_css.php';


   ?>

	 
</head>
<body>

	<?php


    include 'adminsidebar.php';

	?> 
	<center>

<div class="content">
    <h1 class="text-center mb-4">Applied For Admission</h1>

    <div class="d-flex justify-content-center">
        <table class="table table-bordered" style="width: 80%;">
            <tr>
                <th style="padding: 10px; font-size: 10px;">Name</th>
                <th style="padding: 10px; font-size: 10px;">Email</th>
                <th style="padding: 10px; font-size: 10px;">Phone</th>
            </tr>

            <?php while ($info = $result->fetch_assoc()) { ?>
                <tr>
                    <td style='padding: 8px;'><?php echo $info['name']; ?></td>
                    <td style='padding: 8px;'><?php echo $info['email']; ?></td>
                    <td style='padding: 8px;'><?php echo $info['phone']; ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>

</center>
  
  </div>

</body>

</html>

