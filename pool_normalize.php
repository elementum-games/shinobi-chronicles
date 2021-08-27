<?php
session_start();

if(!isset($_SESSION['user_id'])) {
	exit;
}

require("variables.php");
require("classes.php");

$system = new SystemFunctions();
$system->dbConnect();

$player = new User($_SESSION['user_id']);

if($player->staff_level < $SC_HEAD_ADMINISTRATOR) {
	exit;
}


$result = $system->query("SELECT `rank_id`, `health_gain`, `pool_gain`, `base_level`, `max_level` FROM `ranks`");
$ranks = array();
while($row = $system->db_fetch($result)) {
	$ranks[$row['rank_id']] = $row;
}


$level = 1;
$health = 100;
$pools = 100;

foreach($ranks as $id => $rank) {
	if($id > 3) {
		break;
	}
	
	for($i = $rank['base_level']; $i <= $rank['max_level']; $i++) {
		echo "Level: $level - Health: $health - Chakra/Stamina: $pools<br />";
		$level++;
		$health += $rank['health_gain'];
		$pools += $rank['pool_gain'];
		
		$system->query("UPDATE `users` SET `max_health`='$health', `max_chakra`='$pools', `max_stamina`='$pools' WHERE `level`='$level'");
	}
}



