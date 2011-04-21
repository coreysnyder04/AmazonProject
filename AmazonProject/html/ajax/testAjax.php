<?php
include("dbConfig.php");

$name = $_POST['name'];
$time    = $_POST['time'];     

$loggedIn = false;
$isAdmin = false;

echo "Hello World - ".$name." - ".$time;
if($loggedIn){
	echo "<br> User Is logged in";	
}else{
	echo "<br> User Is NOT logged in";	
}
if($isAdmin){
	echo "<br> User Is admin";	
}else{
	echo "<br> User Is NOT admin";	
}