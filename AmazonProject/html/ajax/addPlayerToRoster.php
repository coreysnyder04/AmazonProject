<?php
include("dbConfig.php");


$playerID = $_POST['playerID'];
$teamHistoryID = $_POST['teamHistID'];
$jerseyNumber = $_POST['jersey'];
$userID = $_POST['userID'];
$IP = $_POST['userIP'];
$email = $_POST['email'];
$phone  = $_POST['phone'];


$con = mysql_connect($server, $username, $password);
if (!$con)
{
	//SHould send ajax failure
	die('Could not connect to DB');
}

mysql_select_db($database, $con);

$playerID = mysql_real_escape_string($playerID);
$teamHistoryID = mysql_real_escape_string($teamHistoryID);
$jerseyNumber = mysql_real_escape_string($jerseyNumber);
$IP = mysql_real_escape_string($IP);
$userID = mysql_real_escape_string($userID);
$email = mysql_real_escape_string($email);
$phone = mysql_real_escape_string($phone);

$sql="INSERT INTO playerhistory (playerID, teamHistoryID, jerseyNumber, IP, userID, phone, email)
		VALUES ('".$playerID."', '".$teamHistoryID."', '".$jerseyNumber."', '".$IP."', '".$userID."','".$phone."','".$email."')";
//echo $sql;
if (!mysql_query($sql,$con))
{
	die('Error: ' . mysql_error());
}
//echo "Successfully Added Player!";



?>