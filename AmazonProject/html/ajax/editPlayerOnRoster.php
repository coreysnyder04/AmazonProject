<?php
include("dbConfig.php");

$playerHistoryID = $_POST['playerHistoryID'];
$jerseyNumber = $_POST['jersey'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$userID = $_POST['userID'];
$IP = $_POST['userIP'];

$con = mysql_connect($server, $username, $password);
if (!$con)
{
	//SHould send ajax failure
	die('Could not connect to DB');
}

mysql_select_db($database, $con);

$jerseyNumber = mysql_real_escape_string($jerseyNumber);
$email = mysql_real_escape_string($email);
$phone = mysql_real_escape_string($phone);
$IP = mysql_real_escape_string($IP);
$userID = mysql_real_escape_string($userID);
$playerHistoryID = mysql_real_escape_string($playerHistoryID);

$sql="UPDATE playerhistory
		SET jerseyNumber='".$jerseyNumber."',
		email='".$email."',
		phone='".$phone."',
		IP='".$IP."',
		userID='".$userID."',
		timestamp=NOW()
		WHERE ID='".$playerHistoryID."'";
		
if (!mysql_query($sql,$con))
{
	die('Error: ' . mysql_error());
}

echo "Success!";

?>