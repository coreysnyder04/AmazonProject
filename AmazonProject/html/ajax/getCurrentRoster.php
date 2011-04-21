<?php
include("dbConfig.php");
	
	$teamHistoryID = $_POST['teamHistoryID'];
	
	
	
	echo "<table summary='Roster' title='Roster'>
			<thead>
				<tr>
					<th colspan='99' class='header'><h3>Roster</h3></th>
				</tr>
				<tr>
					<th>#</th>
					<th>Last Name</th>
					<th>First Name</th>
					<th>E-mail</th>
					<th>Phone</th>
					<th>Update</th>
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
	
$sql="SELECT p.id,ph.ID playerHistoryID, ph.jerseyNumber,p.firstName, p.lastName, th.teamName, ph.email, ph.phone, l.name,s.name
		FROM teamhistory th
		INNER JOIN playerhistory ph ON ph.teamHistoryID=th.ID
		INNER JOIN players p on p.ID=ph.playerID
		INNER JOIN leagues l on l.ID=th.leagueID
		INNER JOIN sessions s on s.ID=th.sessionID
		WHERE th.ID='".$teamHistoryID."'
		AND ph.statusID = '1'
		ORDER BY jerseyNumber";

	//echo $sql;
	
	$result = mysql_query($sql);		
	
	
	while($row = mysql_fetch_array($result))
	{
		
	echo "<tr id='".$row['playerHistoryID']."' class='teamRosterRow'>";
	
	echo "<td id='".$row['playerHistoryID']."-jersey'>".$row['jerseyNumber']."</td>";
	echo "<td id='".$row['playerHistoryID']."-lName'>".$row['lastName']."</td>";
	echo "<td id='".$row['playerHistoryID']."-fName'>".$row['firstName']."</td>";
	echo "<td id='".$row['playerHistoryID']."-email'>".$row['email']."</td>";
	echo "<td id='".$row['playerHistoryID']."-phone'>".$row['phone']."</td>";
	echo "<td id='".$row['playerHistoryID']."-update' class='updatePlayer'><input type='button' value='Update'/></td>";
	echo "<td><input class='deletePlayer' id='".$row['playerHistoryID']."-delete' type='button' value='Delete'/></td>";
	
	echo "</tr>";
	}
	
	echo "</tbody>
		</table>
		<br/>";

?>