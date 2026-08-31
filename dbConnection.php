<?php

$hostname = "is3-dev.ict.ru.ac.za";
$user = "G24G5573";
$password = "GunShu23!";
$dbname = "kaizenco";

$conn = new mysqli($hostname, $user, $password, $dbname);

if ($conn->connect_error){
        die("Database connection failed". $conn->connect_error);
}else{
        echo "Database connection successfully established";

}   

?>         