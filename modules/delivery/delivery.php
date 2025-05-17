<?php
ob_start(); 
// $connection = mysqli_connect("localhost:3307", "root", "");
// $db = mysqli_select_db($connection, 'demo');
include "connect.php"; 
include '../../includes/connection.php';
if($_SESSION['name']==''){
	header("location:deliverylogin.php");
}
$name=$_SESSION['name'];
$city=$_SESSION['city'];
$ch=curl_init();
curl_setopt($ch,CURLOPT_URL,"http://ip-api.com/json");
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
$result=curl_exec($ch);
$result=json_decode($result);
// $city= $result->city;
// echo $city;

$id=$_SESSION['Did'];



?>

<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script language="JavaScript" src="http://www.geoplugin.net/javascript.gp" type="text/javascript"></script>

<link rel="stylesheet" href="delivery.css">


</head>
<body>
<header>
        <div class="logo">Plates<b style="color: #06C167;">4All</b></div>
        <div class="hamburger">
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
        </div>
        <nav class="nav-bar">
            <ul>
                <li><a href="#home" class="active">Home</a></li>
                <li><a href="deliverymyord.php" >My orders</a></li>
                <li ><a href="logout.php"  >Logout</a></li> 
            </ul>
        </nav>
    </header>
    <br>
    <script>
        hamburger=document.querySelector(".hamburger");
        hamburger.onclick =function(){
            navBar=document.querySelector(".nav-bar");
            navBar.classList.toggle("active");
        }
    </script>
<?php

// echo var\_export(unserialize(file\_get\_contents('[http://www.geoplugin.net/php.gp?ip=103.113.190.19](http://www.geoplugin.net/php.gp?ip=103.113.190.19)')));
// echo "Your city: {\$city}\n";

// \$city = "<script language=javascript> document.write(geoplugin\_city());</script>";
// \$scity=\$city;
?> <h2><center>Welcome <?php echo"$name";?></center></h2>

<div class="itm">
    <img src="../img/delivery.gif" alt="delivery gif" width="400" height="400"> 
</div>



</body>
</html>