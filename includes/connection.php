

<?php
//change mysqli_connect(host_name,username, password); 
$connection = mysqli_connect('localhost', 'root', 'Nithu2130');

$db = mysqli_select_db($connection, 'food_waste_management');
?>