<?php
/* 
File: 		healingShop.php
Coder:		Levi Meahan
Created:	10/05/2013
Revised:	10/05/2013 by Levi Meahan
Purpose:	Functions for healing (ramen shop)
Algorithm:	See master_plan.html
*/
function healingShop() {
	require("variables.php");
	global $system;
	global $player;
	global $self_link;

	$health[1] = 200;
	$health[2] = 1500;
	$health[3] = 10000;
	$health[4] = 40000;
	$health[6] = 40000;

	$healing['vegetable']['cost'] = $player->rank * 5;
	$healing['vegetable']['amount'] = $health[$player->rank] * 0.1;

	$healing['pork']['cost'] = $player->rank * 20;
	$healing['pork']['amount'] = $health[$player->rank] * 0.4;

	$healing['deluxe']['cost'] = $player->rank * 45;
	$healing['deluxe']['amount'] = $health[$player->rank] * 0.8;

	if($_GET['heal']) {
		try {
			$heal = $system->clean($_GET['heal']);
			if(!isset($healing[$heal])) {
				throw new Exception("Invalid choice!");
			}
			if($player->money < $healing[$heal]['cost']) {
				throw new Exception("You do not have enough money!");
			}
			$player->money -= $healing[$heal]['cost'];
			$player->health += $healing[$heal]['amount'];
			if($player->health > $player->max_health) {
				$player->health = $player->max_health;
			}
		} catch (Exception $e) {
			$system->messsage($e->getMessage());
			$system->printMessage();
		}
	}
	echo "<table class='table'><tr><th>Ichikawa Ramen</th></tr>
	<tr><td style='text-align:center;'>
	Welcome to Ichikawa Ramen. Our nutritious ramen is just the thing your body needs to recover after a long day of training or fighting.
	Our prices are below.<br />
	<br />
	<label style='width:9em;font-weight:bold;'>Your Money:</label> 
		&yen;{$player->money}<br />
	<label style='width:9em;font-weight:bold;'>Health:</label>" . 
			sprintf("%.2f", $player->health) . '/' . sprintf("%.2f", $player->max_health) . 
	"</td></tr>";
	echo "<tr><td style='text-align:center;'>";
	foreach($healing as $level=>$heal) {
		echo "<a href='$self_link&heal={$level}'><span class='button' style='width:10em;'>" . ucwords($level) . " ramen</span></a>
			&nbsp;&nbsp;&nbsp;({$heal['amount']} health, -&yen;{$heal['cost']}) <br />";
	}
	echo "</td></tr></table>";
}
?>