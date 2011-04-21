<?php
include("dbConfig.php");
include("validation.php");

$con = mysql_connect($server, $username, $password);
if (!$con)
{
	//SHould send ajax failure
	die('Could not connect to DB');
}

mysql_select_db($database, $con);

$sql = "select pr.gameID
from gameactivities ga
left outer join playerrosters pr on pr.ID = ga.skaterID
group by pr.gameID";

$result = mysql_query($sql);


while($row = mysql_fetch_array($result))
{
	$gameID = $row['gameID'];
	$updateSQL = "UPDATE games
			SET hasDetails='1'
			WHERE ID = ".$gameID;
	if (!mysql_query($updateSQL,$con))
	{
		echo 'MYSQL Error: ' . mysql_error();
	}else{
		echo "Game ".$gameID." updated! <br/>";
	}
}


?>