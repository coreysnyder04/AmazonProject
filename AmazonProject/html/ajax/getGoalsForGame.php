<?php
include("dbConfig.php");

$teamHistoryID = $_POST['teamHistoryID'];
$gameID = $_POST['gameID'];



echo "<button class='ui-state-default ui-corner-all' id='addGoal'>Add Goal</button>
		<table title='Goals' summary='Goals Scored'>
			<thead>
				<tr>
					<th class='header' colspan='99'><h3>Goals</h3></th>
				</tr><tr>
					<th>Period</th>
					<th>Time</th>
					<th>Scorer</th>
					<th>Assist 1</th>
					<th>Assist 2</th>
					<th>Goal Type</th>
					<th>E.N.</th>
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

$sql="SELECT ga.period,ga.time, ga.ID as rowID, CONCAT(skaterp.firstName, ' ' ,skaterp.lastName) Scorer, CONCAT(assist1p.firstName, ' ' ,assist1p.lastName) Assist1, CONCAT(assist2p.firstName, ' ' ,assist2p.lastName) Assist2, gat.description GoalType, 
		ga.emptyNet
		FROM gameactivities ga
		
		INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
		
		INNER JOIN playerrosters skaterpr on ga.skaterID=skaterpr.ID
		INNER JOIN playerhistory skaterph on skaterph.ID=skaterpr.playerHistoryID
		INNER JOIN players skaterp on skaterp.ID=skaterph.playerID
		INNER JOIN playerhistory ph on ph.ID=skaterpr.playerHistoryID
		
		LEFT OUTER JOIN playerrosters assist1pr on assist1pr.ID=ga.assist1ID
		LEFT OUTER JOIN playerhistory assist1ph on assist1ph.ID=assist1pr.playerHistoryID
		LEFT OUTER JOIN players assist1p on assist1p.ID=assist1ph.playerID
		
		LEFT OUTER JOIN playerrosters assist2pr on assist2pr.ID=ga.assist2ID
		LEFT OUTER JOIN playerhistory assist2ph on assist2ph.ID=assist2pr.playerHistoryID
		LEFT OUTER JOIN players assist2p on assist2p.ID=assist2ph.playerID
		
		WHERE skaterpr.gameID='".$gameID."'
		AND ph.teamHistoryID='".$teamHistoryID."'
		AND gat.type=1";

$result = mysql_query($sql);		

while($row = mysql_fetch_array($result))
{
	
	echo "<tr id='".$row['rowID']."' class='teamRosterRow'>";
	if( $row['period'] == "4"){ echo "<td>OT</td>"; }
	else { echo "<td>".$row['period']."</td>";}
	echo "<td>".$row['time']."</td>";
	echo "<td>".$row['Scorer']."</td>";
	echo "<td>".$row['Assist1']."</td>";
	echo "<td>".$row['Assist2']."</td>";
	echo "<td>".$row['GoalType']."</td>";
	if( $row['emptyNet'] == "1"){ echo "<td>X</td>"; }
	else { echo "<td></td>";}
	//echo "<td><input class='editGoal' type='button' value='Edit'/></td>";
	echo "<td><input class='deleteGoal' type='button' value='Delete'/></td>";
	
	echo "</tr>";
}
		
				
echo "</tbody>
		</table>";


?>