<?php
include("dbConfig.php");


$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];


$con = mysql_connect($server, $username, $password);
if (!$con)
{
	//SHould send ajax failure
	die('Could not connect to DB');
}

mysql_select_db($database, $con);
/*
$sql="select ph.jerseynumber, p.id, p.firstname,p.lastname,th.teamname, l.name leaguename
		from players p
		left outer join playerhistory ph on ph.playerid=p.id
		left outer join teamhistory th on th.id=ph.teamhistoryid
		left outer join leagues l on l.id=th.leagueid
		where firstname='".$firstname."'
		and lastname='".$lastname."'";
*/
$sql="SELECT ph.jerseyNumber, p.firstName,p.lastName,th.teamName, l.name leagueName, p.ID
		FROM players p
		LEFT OUTER JOIN playerhistory ph on ph.playerID=p.ID
		LEFT OUTER JOIN teamhistory th on th.ID=ph.teamHistoryID
		LEFT OUTER JOIN leagues l on l.ID=th.leagueID
		WHERE firstName='".$firstName."'
		AND lastName='".$lastName."'
		GROUP BY p.ID";

//echo $sql;
$result = mysql_query($sql);		

echo "<table summary='existing PLayers' title='Existing PLayers'>
		<thead>
		<tr>
		<th colspan='99' class='header'><h3>Existing PLayers</h3></th>
		</tr>
		<tr>
		<th>#</th>
		<th>Last Name</th>
		<th>First Name</th>
		<th>Team</th>
		<th>League</th>
		<th>Use</th>
		</tr>
		</thead>
		<tbody>";

while($row = mysql_fetch_array($result))
{
		
	echo "<tr id='".$row['ID']."' class='existingPlayerRow'>";
	
	echo "<td id='".$row['ID']."-jersey'>".$row['jerseyNumber']."</td>";
	echo "<td id='".$row['ID']."-lName'>".$row['lastName']."</td>";
	echo "<td id='".$row['ID']."-fName'>".$row['firstName']."</td>";
	echo "<td id='".$row['ID']."-email'>".$row['teamName']."</td>";
	echo "<td id='".$row['ID']."-phone'>".$row['leagueName']."</td>";
	echo "<td class='userPlayer'><input id='".$row['ID']."-delete' type='button' value='Use Me!'/></td>";
	
	echo "</tr>";
}

echo "</tbody>
		</table>
		<br/>";


?>