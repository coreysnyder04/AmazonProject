<?php
include("dbConfig.php");
include("validation.php");

$userID = $_POST['ID'];
$teamAlias = $_POST['teamAlias'];

$message = "";
$message .= validateNumber($userID, 12, "ID");
$message .= validateName("Team Alias", $teamAlias);

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

mysql_select_db("philfoz_cohforum", $con);
//mysql_select_db("cohforum", $con);
		
$sql="UPDATE phpbb_users
		SET user_team = '".$teamAlias."'
		WHERE user_id='".$userID."'";
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
exit;

