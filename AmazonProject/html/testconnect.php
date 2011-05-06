<?php
//Connect To Database
$hostname='jonryan2.db.3849060.hostedresource.com';
$username='jonryan2';
$password='Am450Ndata####';
$dbname='jonryan2';
$usertable='ItemLookups';
$yourfield = 'itemID';

mysql_connect($hostname,$username, $password) OR DIE ('Unable to connect to database! Please try again later.');
mysql_select_db($dbname);

$query = 'SELECT * FROM ' . $usertable;
$result = mysql_query($query);
if($result) {
    while($row = mysql_fetch_array($result)){
        $name = $row[$yourfield];
        echo 'Name: ' . $name;
    }
}

?> 