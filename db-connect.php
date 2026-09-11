<?php

$dbHost = "is3-dev.ict.ru.ac.za";       
$dbName = "kaizenco";
$dbUsername = "G23C4492";
$dbPassword = "ChaEne24!";

 
 

$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
 
/* These options configure HOW PDO behaves: */
$options = [
    
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
 
    
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
 
try {
 
    
    $pdo = new PDO($dsn, $dbUsername, $dbPassword, $options);
 
} catch (PDOException $e) {
 
   
    die('Database connection failed: ' . $e->getMessage());
 
}