<?php
    $hostname= "is3-dev.ict.ru.ac.za";
    $user = "G24M6516";
    $password="MbuMuf23!";
    $dbname= "kaizenco";
    
    $conn= new mysqli($hostname, $user, $password, $dbname);
    if($conn->connect_error){
        die("Connection failed" .$conn->connect_error);
    }else{
        echo "successfuly connected";
    }
    ?>