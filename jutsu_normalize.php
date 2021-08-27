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


// Rank 1: 1 + (use cost / 20)
// Rank 2: 2 + ((use cost - 15) / 20)
// Rank 3: 3 + (use cost / 50)

// R1: 8-12  (4 + (jutsu_power * 4))
// R2: 15-40 (15 + (jutsu_power - 2) * 25)
// R3: 45-180 (45 + (jutsu_power - 3) * 135)

//$system->query("UPDATE `jutsu` SET `use_cost`= ROUND(4 + `power` * 4) WHERE `rank`=1");
//$system->query("UPDATE `jutsu` SET `use_cost`= ROUND(13 + (`power` - 2) * 25) WHERE `rank`=2");
$system->query("UPDATE `jutsu` SET `use_cost`= ROUND(45 + (`power` - 3) * 135) WHERE `rank`=3");

	
	



