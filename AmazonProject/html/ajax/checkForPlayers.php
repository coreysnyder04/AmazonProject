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

$sql="SELECT p.ID, p.firstName,p.lastName,th.teamName, l.name leagueName
		FROM players p
		LEFT OUTER JOIN playerhistory ph on ph.playerID=p.ID
		LEFT OUTER JOIN teamhistory th on th.ID=ph.teamHistoryID
		LEFT OUTER JOIN leagues l on l.ID=th.leagueID
		WHERE firstName='".$firstName."'
		AND lastName='".$lastName."'
		GROUP BY p.ID";
		
$result = mysql_query($sql);

$matches= "false";

while($row = mysql_fetch_array($result))
{
	$matches = "true"; 
}

echo $matches;


?>