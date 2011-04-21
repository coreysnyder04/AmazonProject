<?php
include("dbConfig.php");
include("validation.php");

ini_set( "display_errors", 0);

$userID = $_POST['userID'];
$IP = $_POST['userIP'];
$player = $_POST['player'];
$penalty = $_POST['penalty'];
$period = $_POST['period'];
$time = "00:".$_POST['time'];
$teamHistoryID = $_POST['teamHistoryID'];
$gameID = $_POST['gameID'];

$message = "";
$message .= validateNotEmpty($player, 4, "Player");
$message .= validateLength($time, 7, 8, "Time");

if(strlen($message) > 0){
	$errorHeader = "<div style='padding: 0pt 0.7em;' class='ui-state-error ui-corner-all'><p><span style='float: left; margin-right: 0.3em;' class='ui-icon ui-icon-alert'></span><strong>Alert:</strong> Action not completed because:</p><ul>";
	$errorFooter = "</ul></div>";
	echo $errorHeader.$message.$errorFooter;
	return;
}

//echo $userID."^".$IP."^".$player."^".$penalty."^".$period."^".$teamHistoryID."^".$gameID."^".$time."<br>";

$con = mysql_connect($server, $username, $password);
if (!$con)
{
	//SHould send ajax failure
	die('Could not connect to DB');
}

mysql_select_db($database, $con);

$sql="INSERT INTO gameactivities (period,time,gameActivityTypeID, skaterID, assist1ID, assist2ID, IP, userID, emptyNet)
		VALUES ('".$period."','".$time."','".$penalty."','".$player."','','','".$IP."','".$userID."','')";
	//echo $sql;
if (!mysql_query($sql,$con))
{
	die('Error: ' . mysql_error());
}

$sql2="UPDATE games
		SET hasDetails='1'
		WHERE ID = ".$gameID;
//echo $sql;
if (!mysql_query($sql2,$con))
{
	die('Error: ' . mysql_error());
}



?>