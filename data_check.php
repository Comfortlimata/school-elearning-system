 <?php

 $host="localhost";

 $user="root";

 $password="";

 $db="schoolproject";


 $data = mysqli_connect($host, $user, $password, $db);

  if($data==false)
{
    die("connection");
}


 if(isset($_POST['apply']))

 
    $data_name=$_POST['name'];
    $data_email=$_POST['email'];
    $data_phone=$_POST['phone'];
     
 if (isset($_POST['apply'])) {
    $data_name=$_POST['name'];
    $data_email=$_POST['email'];
    $data_phone=$_POST['phone'];

   $sql = "INSERT INTO admission (name, email, phone) VALUES ('$data_name', '$data_email', '$data_phone')";
   
    $result=mysqli_query($data, $sql);

    if ($result) {
        echo "Your Application Sent Successful";
    } else {
        echo "Apply failed";
    } // <-- Ensure this closing bracket exists
} // <-- Make sure this bracket exists and closes the `if` block properly


     












 ?> 





