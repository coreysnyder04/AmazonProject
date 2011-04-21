<?php
include("dbConfig.php");

$teamHistoryID = $_POST['teamHistoryID'];
$gameID = $_POST['gameID'];

//echo $teamHistoryID."^".$gameID;

echo "<button class='ui-state-default ui-corner-all' id='addPenalty'>Add Penalty</button>
		<table title='Penalties' summary='Penalties'>
			<thead>
				<tr>
					<th class='header' colspan='99'><h3>Penalties</h3></th>
				</tr>
				<tr>
					<th>Period</th>
					<th>Player</th>
					<th>Penalty</th>
					<th>Minutes</th>
					<th>Start</th>
					<th>Delete</th>
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


$sql = "SELECT ga.ID, ga.period Period,ga.time Start, CONCAT(skaterp.firstName, ' ' ,skaterp.lastName) Player, gat.description Penalty, gat.PIM

		FROM gameactivities ga
		INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID

		INNER JOIN playerrosters skaterpr on ga.skaterID=skaterpr.ID
		INNER JOIN playerhistory skaterph on skaterph.ID=skaterpr.playerHistoryID
		INNER JOIN players skaterp on skaterp.ID=skaterph.playerID
		INNER JOIN playerhistory ph on ph.ID=skaterpr.playerHistoryID

		WHERE skaterpr.gameID='".$gameID."'
		AND ph.teamHistoryID='".$teamHistoryID."'
		AND gat.type=2
		ORDER BY ga.period, ga.time";

$result = mysql_query($sql);		

while($row = mysql_fetch_array($result))
{
	
	echo "<tr id='".$row['ID']."' class='PenaltyRow'>";
	
	echo "<td>".$row['Period']."</td>";
	echo "<td>".$row['Player']."</td>";
	echo "<td>".$row['Penalty']."</td>";
	echo "<td>".$row['PIM']."</td>";
	echo "<td>".$row['Start']."</td>";
	echo "<td><input class='deletePenalty' type='button' value='Delete'/></td>";
	
	echo "</tr>";
}


echo"	</tbody>
		</table>";
		
?>