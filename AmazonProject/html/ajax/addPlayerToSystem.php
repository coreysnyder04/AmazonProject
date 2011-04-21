<?php
include("dbConfig.php");
include("validation.php");

$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$IP = $_POST['userIP'];
$userID = $_POST['userID'];

$message = "";
$message .= validateName("First Name", $firstName);
$message .= validateName("Last Name", $lastName);
$message .= validateRowID($userID);

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
	$message .="<li>MYSQL Error: Could Not connect to DB</li>";
}

mysql_select_db($database, $con);

$firstName = mysql_real_escape_string($firstName);
$lastName = mysql_real_escape_string($lastName);
$IP = mysql_real_escape_string($IP);
$userID = mysql_real_escape_string($userID);
		
$sql="INSERT INTO players (firstName, lastName, IP, userID)
		VALUES ('".$firstName."','".$lastName."','".$IP."','".$userID."')";
		
if (!mysql_query($sql,$con))
{
	$message .='<li>MYSQL Error: ' . mysql_error() . "</li>";
}

if(strlen($message) > 0){
	$errorHeader = "<div style='padding: 0pt 0.7em;' class='ui-state-error ui-corner-all'><p><span style='float: left; margin-right: 0.3em;' class='ui-icon ui-icon-alert'></span><strong>Alert:</strong> Action not completed because:</p><ul>";
	$errorFooter = "</ul></div>";
	echo $errorHeader.$message.$errorFooter;
	return;
}


$sql="SELECT ID
		FROM players
		WHERE firstName='".$firstName."'
		AND lastName='".$lastName."'
		ORDER BY ID DESC
		LIMIT 1";

$result = mysql_query($sql);

$ID = false;

while($row = mysql_fetch_array($result))
{
	$ID = $row['ID'];
}

print($ID);
exit;

