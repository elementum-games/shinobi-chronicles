<?php
/* 
File: 		travel.php
Coder:		Levi Meahan
Created:	10/27/2013
Revised:	10/27/2013 by Levi Meahan
Purpose:	Functions for travel page
Algorithm:	See master_plan.html
*/

function travel() {
	require("variables.php");
	global $system;

	global $player;

	global $villages;

	global $self_link;
	
	$map_size = 12;
	$map_size_x = 18;
	$map_size_y = 12;
	if($_GET['travel']) {
		$travel = $system->clean($_GET['travel']);
		$target_x = $player->x;
		$target_y = $player->y;
		switch($_GET['travel']) {
			case 'north':
				if($player->y <= 1) {
					$system->message("You cannot travel farther this way!");
					break;
				}
				
				$target_y--;
				break;
			case 'south':
				if($player->y >= $map_size_y) {
					$system->message("You cannot travel farther this way!");
					break;
				}
				
				$target_y++;
				break;
			case 'east':
				if($player->x >= $map_size_x) {
					$system->message("You cannot travel farther this way!");
					break;
				}
				
				$target_x++;
				break;
			case 'west':
				if($player->x <= 1) {
					$system->message("You cannot travel farther this way!");
					break;
				}
				
				$target_x--;
				break;
			default:
				break;
		}
		$location = $target_x . "." . $target_y;
		
		if(isset($villages[$location]) && $location != $player->village_location) {
			$system->message("You cannot travel into another village!");
		}
		else {
			$player->location = $location;
			$player->y = $target_y;
			$player->x = $target_x;
		}
		
		// Village check
		if($player->location == $player->village_location) {
			$player->in_village = true;
		}
		else {
			$player->in_village = false;
		}
	}
	
	echo "<style type='text/css'>
	#content table.map {
		margin-left:auto;
		margin-right:auto;
		/* width: 590px; */
		background: url(./images/travel_map.png);
		background-size: 100%;
		background-repeat: no-repeat;
	}
	#content table.map td {
		padding:1px;
		text-align:center;
		
		background-repeat: no-repeat;
		background-position:center;
		border-color: rgba(0, 0, 0, 0.2);
	}
	#content table.map td.village {
		border-color: #101010;
		background-color: rgba(0, 0, 0, 0.4);
	}
	</style>";
	
	
	// Get village locations
	$icons = array('stone.png', 'cloud.png', 'leaf.png', 'sand.png', 'mist.png');
	
	$system->printMessage();
	
	// Keyboard hotkeys
	echo "<script type='text/javascript'>
	var leftArrow = 37;
	var upArrow = 38;
	var rightArrow = 39;
	var downArrow = 40;
	
	var aUpper = 65;
	var aLower = 97;
	
	var wUpper = 87;
	var wLower = 119;
	
	var dUpper = 68;
	var dLower = 100;
	
	var sUpper = 83;
	var sLower = 115;
	
	var travelStarted = false;
	$(document).keyup(function(event){
		if(travelStarted) {
			return false;
		}
		
		var direction = '';
		if(event.which == leftArrow || event.which == aLower || event.which == aUpper) {
			direction = 'west';
		}
		else if(event.which == upArrow || event.which == wLower || event.which == wUpper) {
			direction = 'north';
		}
		else if(event.which == rightArrow || event.which == dLower || event.which == dUpper) {
			direction = 'east';
		}
		else if(event.which == downArrow || event.which == sLower || event.which == sUpper) {
			direction = 'south';
		}
		
		if(direction.length > 1) {
			window.location.href='$self_link&travel=' + direction;
			travelStarted = true;
		}	
	});
	
	</script>";
	
	echo "<table class='table'><tr><th>Your location: {$player->location}" .
	(isset($villages[$player->location]) ? " (" . $villages[$player->location]['name'] . " Village)" : "") .
	"</th></tr>
	<tr><td style='text-align:center;'>";
	if($player->mission_id) {
		if($player->mission_stage['action_type'] == 'travel' or $player->mission_stage['action_type'] == 'search') {
			if($player->location == $player->mission_stage['action_data']) {
				echo "<a href='$mission_link'><p class='button' style='margin-top:5px;'>Go to Mission Location</p></a><br />";
			}
		}
	}
	
	echo "<span style='font-style:italic;margin-bottom:3px;display:inline-block;font-style:0.9em;'>(Use WASD, arrow keys, or the arrows below)</span>
	<table class='map' cellspacing='0' style='padding:0px;border:1px solid #000;'>";
	for($y = 1; $y <= $map_size_y; $y++) {
		echo "<tr>";
		for($x = 1; $x <= $map_size_x; $x++) {
			$key = $x . '.' . $y;
			if(isset($villages[$key])) {
				echo "<td class='village' style='background-image: url(./images/village_icons/" . $icons[$villages[$key]['count']] . ');';
				if($key == $player->village_location) {
					echo "background-color:#FFEF30;";
				}
				echo "'>";
			}
			else {
				echo "<td>";
			}
			echo ($y == $player->y && $x == $player->x ? "<img src='./images/ninja_head.png' />" : "&nbsp;") . "</td>";
		}
		echo "</tr>";
	}
	echo "</table></td></tr>";
	
	echo "<tr><td style='text-align:center;'>
	<style type='text/css'>
	.upArrow, .leftArrow, .rightArrow, .downArrow {
		display: inline-block;
		height:32px;
		width:32px;
		margin:0px;
		background-size: 100%;
		background-repeat: no-repeat;
	}
	
	.upArrow {
		background-image: url(./images/icons/up.png);
	}
	.upArrow:hover {
		background-image: url(./images/icons/up_hover.png);
	}
	
	.leftArrow {
		background-image: url(./images/icons/left.png);
	}
	.leftArrow:hover {
		background-image: url(./images/icons/left_hover.png);
	}
	
	.downArrow {
		background-image: url(./images/icons/down.png);
	}
	.downArrow:hover {
		background-image: url(./images/icons/down_hover.png);
	}
	
	.rightArrow {
		background-image: url(./images/icons/right.png);
	}
	.rightArrow:hover {
		background-image: url(./images/icons/right_hover.png);
	}
	</style>
	<a href='$self_link&travel=north'><span class='upArrow'></span></a><br />
		<br />
	<a href='$self_link&travel=west'><span class='leftArrow'></span></a>
		<span style='display:inline-block;width:50px;'></span>
	<a href='$self_link&travel=east'><span class='rightArrow'></span></a><br />
		<br />
	<a href='$self_link&travel=south'><span class='downArrow'></span></a>
	
	</td></tr>
	</table>";

}

?>