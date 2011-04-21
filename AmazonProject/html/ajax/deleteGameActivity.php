<?php
include("dbConfig.php");
include("validation.php");


$activityID = $_POST['gameActivityID'];
$teamHistoryID = $_POST['teamHistID'];
$userID = $_POST['userID'];
$IP = $_POST['userIP'];

$message = "";
$message .= validateNumber($activityID, 10, "Activity");
$message .= validateNumber($teamHistoryID, 10, "Team ID");
$message .= validateNumber($userID, 10, "ID");

if(strlen($message) > 0){
	$errorHeader = "<div style='padding: 0pt 0.7em;' class='ui-state-error ui-corner-all'><p><span style='float: left; margin-right: 0.3em;' class='ui-icon ui-icon-alert'></span><strong>Alert:</strong> Action not completed because:</p><ul>";
	$errorFooter = "</ul></div>";
	echo $errorHeader.$message.$errorFooter;
	return;
}

$con = mysql_connect($server, $username, $password);
if (!$con)
{
	//SHould send ajax failure
	die('Could not connect to DB');
}

mysql_select_db($database, $con);

$activityID = mysql_real_escape_string($activityID);

$sql="DELETE FROM gameactivities WHERE ID='".$activityID."'";

if (!mysql_query($sql,$con))
{
	die('Error: ' . mysql_error());
}

?>