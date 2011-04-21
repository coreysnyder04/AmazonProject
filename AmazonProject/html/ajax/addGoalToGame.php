<?php
include("dbConfig.php");
include("validation.php");

ini_set( "display_errors", 0);

$userID = $_POST['userID'];
$IP = $_POST['userIP'];
$emptyNet = $_POST['emptyNet'] || "";
$goal = $_POST['Goal'];
$A1 = $_POST['A1'];
$A2 = $_POST['A2'];
$goalType = $_POST['goalType'];
$period = $_POST['period'];
$time = $_POST['time'];
$teamHistoryID = $_POST['teamHistoryID'];
$gameID = $_POST['gameID'];

$time = rawurldecode($time);
$time = "00:".$time;

$message = "";
$message .= validateNotEmpty($goal, 4, "Goals");
$message .= validateGoalsAssists($goal, $A1, $A2);
$message .= validateLength($IP, 2, 15, "IP");
$message .= validateLength($userID, 1, 5, "User ID");
$message .= validateLength($A1, 0, 5, "Assist 1");
$message .= validateLength($A2, 0, 5, "Assist 2");
$message .= validateLength($period, 1, 3, "Period");
$message .= validateLength($time, 7, 8, "Time");
$message .= validateLength($teamHistoryID , 1, 10, "Team");
$message .= validateLength($gameID , 1, 10, "Game");
//$message .= validateTime($time);



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

$sql = "SELECT CASE WHEN COUNT(ga.ID) < actual.actualScore THEN 1 ELSE 0 END addGoal
		FROM gameactivities ga
		INNER JOIN playerrosters pr on pr.ID=ga.skaterID
		INNER JOIN playerhistory ph on ph.ID=pr.playerHistoryID
		INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
		INNER JOIN (
		SELECT ID gameID, '".$teamHistoryID."' teamID, CASE WHEN teamHomeID='".$teamHistoryID."' THEN scoreHome ELSE scoreAway END actualScore
		FROM games g
		WHERE (teamHomeID='".$teamHistoryID."' OR teamAwayID='".$teamHistoryID."')
		AND ID='".$gameID."'
		) actual on actual.gameID=pr.gameID AND actual.teamID=ph.teamHistoryID
		WHERE pr.gameID = '".$gameID."'
		AND ph.teamHistoryID='".$teamHistoryID."'
		AND gat.type = 1";
		
$Ok = "0";

$result = mysql_query($sql);	
while($row = mysql_fetch_array($result)) //Loop across previously checked in players
{
	$Ok = $row['addGoal'];
}


if($Ok === "0"){
		$errorHeader = "<div style='padding: 0pt 0.7em;' class='ui-state-error ui-corner-all'><p><span style='float: left; margin-right: 0.3em;' class='ui-icon ui-icon-alert'></span><strong>Alert:</strong> Action not completed because:</p><ul>";
		$errorFooter = "</ul></div>";
		echo $errorHeader."You have reached the maximum number of goals for this game.".$errorFooter;
		return;
}

$sql="INSERT INTO gameactivities (period,time,gameActivityTypeID, skaterID, assist1ID, assist2ID, IP, userID, emptyNet)
		VALUES ('".$period."','".$time."','".$goalType."','".$goal."','".$A1."','".$A2."','".$IP."','".$userID."','".$emptyNet."')";
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