<?php
session_start();
if(!isset($_SESSION['user_id']) or $_SESSION['user_id'] != 1) {
	exit;
}

require("classes/_autoload.php");
$system = new System();
$system->dbConnect();
$village = 'Leaf';
if($_POST['message']) {
	$user_name = $system->clean($_POST['user_name']);
	$message = $system->clean($_POST['message']);
	$title = $system->clean($_POST['title']);
	$query = "INSERT INTO `chat` (`user_name`, `message`, `title`, `village`, `time`, `staff_level`) 
		VALUES ('$user_name', '$message', '$title', '$village', UNIX_TIMESTAMP(), '3');";
	$system->query($query);
}

echo "<div style='text-align:center;'>
<form action='event_social.php' method='post'>
Username<br />
<input type='text' name='user_name' /><br />
<br />
Title<br />
<input type='text' name='title' /><br />
<br />
<textarea name='message' style='width:400px;height:75px;'></textarea>
<br />
<input type='submit' value='post' />
</form></div>";
?>