<?php
include("dbConfig.php");

	$teamHistoryID = $_POST['teamHistoryID'];
	$gameID = $_POST['gameID'];
	
	echo "<table summary='Check Ins' title='Check Ins'>
			<thead>
				<tr>
					<th colspan='99' class='header'><h3>Check Ins</h3></th>
				</tr>
				<tr>
					<th>Played</th>
					<th>#</th>
					<th>Name</th>
				</tr>
			</thead>
			<tbody>";
			

	$con = mysql_connect($server, $username, $password);
	if (!$con)
	{
		//SHould send ajax failure
		die('Could not connect to DB');
	}

	mysql_select_db($database, $con);
/*
$sql="SELECT ph.ID playerHistoryID,
		p.firstName,
		p.lastName,
		CASE WHEN pr.jerseyNumber IS NOT NULL AND pr.gameID='".$gameID."'
		THEN pr.jerseyNumber 
		ELSE ph.jerseyNumber END jerseyNumber,
		CASE WHEN pr.ID IS NOT NULL  AND pr.gameID='".$gameID."'
		THEN 1
		ELSE 0 END isPlayerCheckedIn
		FROM players p
		LEFT OUTER JOIN playerhistory ph on ph.playerID=p.ID
		LEFT OUTER JOIN playerrosters pr on pr.playerHistoryID=ph.ID
		WHERE ph.teamHistoryID='".$teamHistoryID."'
		AND ph.statusID=1
		GROUP BY pr.playerHistoryID
		ORDER BY jerseyNumber";
*/

$sql = "SELECT DISTINCT p.firstName,p.lastName,ph.ID playerHistoryID,
	CASE WHEN pr.jerseyNumber IS NOT NULL AND pr.gameID='".$gameID."'
			THEN pr.jerseyNumber 
			ELSE ph.jerseyNumber END jerseyNumber,
	CASE WHEN pr.ID IS NOT NULL  AND pr.gameID='".$gameID."'
			THEN 1
			ELSE 0 END isPlayerCheckedIn
	FROM playerhistory ph
	LEFT OUTER JOIN playerrosters pr on pr.playerHistoryID=ph.ID
	LEFT OUTER JOIN players p on p.ID=ph.playerID
	WHERE teamHistoryID='".$teamHistoryID."'
	AND ph.statusID=1
	AND pr.gameID='".$gameID."'
	UNION 
	SELECT DISTINCT p.firstName,p.lastName,ph.ID playerHistoryID,
	ph.jerseyNumber jerseyNumber,
	0 isPlayerCheckedIn
	FROM playerhistory ph
	LEFT OUTER JOIN players p on p.ID=ph.playerID
	WHERE teamHistoryID='".$teamHistoryID."'
	AND ph.statusID=1
	AND ph.ID NOT IN (SELECT ph.ID
	FROM playerhistory ph
	LEFT OUTER JOIN playerrosters pr on pr.playerHistoryID=ph.ID
	WHERE teamHistoryID='".$teamHistoryID."'
		AND pr.gameID='".$gameID."')
		ORDER BY jerseyNumber";


	//echo $sql;
	$result = mysql_query($sql);		
	
	
	
	
	while($row = mysql_fetch_array($result))
	{
	if($row["isPlayerCheckedIn"] == 1){ 
		$checked = "checked";
	}else{
		$checked = "";
	}
		
	echo "<tr class='teamRosterRow' id='".$row['playerHistoryID']."'>";
	echo "<td><input name='isPlaying' class='isPlaying' type='checkbox' class='isPlaying' ".$checked." /></td>";
	echo "<td><input name='playerNumber' class='playerNumber' type='text' value='".$row['jerseyNumber']."'/></td>";
	echo "<td>".$row['lastName'].", ".$row['firstName']."</td>";
	echo "<input type='hidden' name='playerID' value='".$row['playerHistoryID']."'/></tr>";
	}
	
	echo "</tbody>
		</table><br/><button id='submitForm' class='ui-state-default ui-corner-all'>Continue</button></form><button id='goToGameDetails' class='ui-state-default ui-corner-all'>Skip To Details -></button>";

?>