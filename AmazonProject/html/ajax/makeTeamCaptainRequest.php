<?php
include("dbConfig.php");


$userID = $_POST['userID'];
$teamID = $_POST['teamID'];   
$userIP = $_POST['userIP']; 
$teamName = $_POST['teamName']; 
$leagueName = $_POST['leagueName']; 
$email= $_POST['email']; 

//echo $userID."^".$teamID."^".$userIP."^".$teamName."^".$leagueName."^".$email."<br>";

$con = mysql_connect('localhost', 'gallos_csnyder', '-*%fNIe@j*^#');
if (!$con)
{
	//SHould send ajax failure
	die('Could not connect to DB');
}

mysql_select_db($database, $con);

$sql="INSERT INTO teamcaptainhistory (UserID,TeamID,StatusID,UserIP)
VALUES ('".$userID."','".$teamID."',3,'".$userIP."')";
//echo $sql;
$result = mysql_query($sql);

//echo $result;
echo "Success! ";
sendEmail($email, $teamName, $leagueName);



function sendEmail($email, $teamName,$leagueName){
	$subject = "Captain Registration";
	$message = "Hello, <br><br>";
	$message .= "You have requested to be the Captain of the '".$teamName."' in '".$leagueName."'. Before I can approve the request, I need you to email us a picture of one of your score sheets. The sheet must be from the current season, and your team name and game date must be legible in the picture.  <br><br>";
	$message .= "If this is not possible you can shoot us an email and we can discuss different options.   <br><br>";
	$message .= "Please send your emails to: coreysnyder@centralohiohockey.com <br><br>";
	$message .= "Thanks,  <br>";
	$message .= "C.O.H. Dev Team <br><br>";
	$message .= "<br><br>";
	
	mail( $email, $subject,
			$message, "From: coreysnyder@centralohiohockey.com\nContent-Type: text/html" );
	echo "You should receive an email with further instructions.";
}


mysql_close($con);


?> 

