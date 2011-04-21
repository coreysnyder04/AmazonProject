<?php
include("dbConfig.php");

$requestID = $_POST['requestID'];
$action = $_POST['action'];  //1- accept //5- decline	  

echo $requestID."^".$action;

$con = mysql_connect($server, $username, $password);
if (!$con)
{
	//SHould send ajax failure
	die('Could not connect to DB');
}

mysql_select_db($database, $con);

$sql="UPDATE teamcaptainhistory
		SET statusID='".$action."'
		WHERE ID='".$requestID."'";

if (!mysql_query($sql,$con))
{
	die('Error: ' . mysql_error());
}else{
	echo "Action Completed!";	
}


mysql_close($con);


?> 

