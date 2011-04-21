<?php
include("dbConfig.php");
include("validation.php");

$data = file_get_contents('php://input');
//echo $data;
$data = json_decode($data, true);

$gameID = $data["gameID"];
$teamID = $data["teamID"];
$userID = $data["userID"];
$userIP = $data["userIP"];
$checkIns = $data["checkIns"];
$teamHistoryID = $data["teamID"];

$message = "";
$message .= validateNumber($gameID, 10, "GameID");
$message .= validateNumber($teamID, 10, "teamID");
$message .= validateNumber($userID, 10, "userID");
$message .= validateNumber($teamHistoryID, 10, "TeamID");


if(strlen($message) > 0){
	$errorHeader = "<div style='padding: 0pt 0.7em;' class='ui-state-error ui-corner-all'><p><span style='float: left; margin-right: 0.3em;' class='ui-icon ui-icon-alert'></span><strong>Alert:</strong> Action not completed because:</p><ul>";
	$errorFooter = "</ul></div>";
	echo $errorHeader.$message.$errorFooter;
	return;
}

$con = mysql_connect($server, $username, $password);
if (!$con)
{
	//Should send ajax failure
	die('Could not connect to DB');
}

mysql_select_db($database, $con);

//Also, the fixed query which is used to get the list of people for the "Check In" page w/ their checked in stats & jersey #, is that updated on the Excel Doc

$gameID = mysql_real_escape_string($gameID);
$teamID = mysql_real_escape_string($teamID);
$userID = mysql_real_escape_string($userID);
$userIP = mysql_real_escape_string($userIP);
$teamHistoryID = mysql_real_escape_string($teamHistoryID);

$select = "SELECT pr.ID, pr.playerHistoryID
		FROM playerrosters pr
		INNER JOIN playerhistory ph on ph.ID=pr.playerHistoryID
		WHERE pr.gameID='".$gameID."'
		and ph.teamHistoryID='".$teamHistoryID."'";

$result = mysql_query($select);		

while($row = mysql_fetch_array($result)) //Loop across previously checked in players
{
	$playerID = $row['playerHistoryID'];
	$playerRosterID = $row['ID'];
	$permFound = false;
	$foundAt = "";
	
	echo $playerID."^".$playerRosterID;
	
	foreach ($checkIns as $key => $value){ //Search to see if this player id was re-checked in.
		$found = array_search($playerID, $value); //Save the key of where it was found
		if($found){
			$permFound = true;
			$foundAt = $key;
		}
	}
	echo "FOUND-".$foundAt."^".$permFound."<br>";
	if($permFound){ //If player is being re-checked in
		echo "Removing as to not re-check in player";
		unset($checkIns[$foundAt]);//remove from our check-ins array;
	}else{ //If player is not being re-checked in, but was previously checked in.
		echo "Player is not being checked in, but was previously.";
		//Check out this player for this game.
		$delete = "DELETE FROM playerrosters
			WHERE ID='".$row['ID']."'";

		if (!mysql_query($delete,$con))
		{
			die('Error: ' . mysql_error());
		}
		//Remove this player from any Goals/Assists
		$deleteGoal = "DELETE FROM gameactivities
				WHERE skaterID='".$playerRosterID."'";
		if (!mysql_query($deleteGoal,$con))
		{
			die('Error: ' . mysql_error());
		}
		$deleteGoal = "UPDATE gameactivities
				SET assist1ID=NULL
				WHERE assist1ID='".$playerRosterID."'";
		if (!mysql_query($deleteGoal,$con))
		{
			die('Error: ' . mysql_error());
		}		
		$deleteGoal = "UPDATE gameactivities
				SET assist2ID=NULL
				WHERE assist2ID='".$playerRosterID."'";
		if (!mysql_query($deleteGoal,$con))
		{
			die('Error: ' . mysql_error());
		}	
		
	}
}

print_r($checkIns);

foreach ($checkIns as $key => $value){
	$playerID = $checkIns[$key]["playerID"];
	$jerseyNum = $checkIns[$key]["jerseyNum"];
	
	//Run SQL INSERT STMT TO PUT IN VALUES
	
	$sql = "INSERT INTO playerrosters (playerHistoryID, jerseyNumber, gameID, IP, userID)
			VALUES ('".$playerID."','".$jerseyNum."','".$gameID."','".$userIP."','".$userID."')";
 	
	if (!mysql_query($sql,$con))
	{
		die('Error: ' . mysql_error());
	}	
}	

?>