<?php
include("dbConfig.php");


$playerHistoryID = $_POST['playerID'];

echo $playerHistoryID;


$con = mysql_connect($server, $username, $password);
if (!$con)
{
	//SHould send ajax failure
	die('Could not connect to DB');
}

mysql_select_db($database, $con);

$sql="UPDATE playerhistory
		SET statusID=2
		WHERE ID='".$playerHistoryID."'";
		
if (!mysql_query($sql,$con))
{
	die('Error: ' . mysql_error());
}

echo "Success!";


?>