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
	global $system;

	global $player;

	global $villages;

	global $self_link;

	if(!empty($_GET['travel'])) {
		$target_x = $player->x;
		$target_y = $player->y;

		$ignore_travel_restrictions = $player->isHeadAdmin();

		try {
            switch($_GET['travel']) {
                case 'north':
                    if($player->y <= 1 && !$ignore_travel_restrictions) {
                        throw new Exception("You cannot travel farther this way!");
                    }

                    $target_y--;
                    break;
                case 'south':
                    if($player->y >= System::MAP_SIZE_Y && !$ignore_travel_restrictions) {
                        throw new Exception("You cannot travel farther this way!");
                    }

                    $target_y++;
                    break;
                case 'east':
                    if($player->x >= System::MAP_SIZE_X && !$ignore_travel_restrictions) {
                        throw new Exception("You cannot travel farther this way!");
                    }

                    $target_x++;
                    break;
                case 'west':
                    if($player->x <= 1 && !$ignore_travel_restrictions) {
                        throw new Exception("You cannot travel farther this way!");
                    }

                    $target_x--;
                    break;
                default:
                    break;
            }
            $location = $target_x . "." . $target_y;
            
            if(isset($villages[$location]) && $location !== $player->village_location && !$ignore_travel_restrictions) {
                throw new Exception("You cannot travel into another village!");
            }

            if($player->last_death > time() - 15 && !$ignore_travel_restrictions) {
                throw new Exception("You died within the last 15 seconds, please wait " .
                    (($player->last_death + 15) - time()) . " more seconds before moving.");
            }

            $player->location = $location;
            $player->y = $target_y;
            $player->x = $target_x;

            if($player->mission_id && $player->mission_stage['action_type'] == 'combat') {
                $mission = new Mission($player->mission_id, $player);
                if($mission->mission_type == 5) {
                    $mission->nextStage($player->mission_stage['stage_id'] = 4);
                    $player->mission_stage['mission_money'] /= 2;
                    throw new Exception("Mission failed! Return to village.");
                }
            }

            $player->updateData();
        } catch(Exception $e) {
            $system->message($e->getMessage());
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

	echo "<table id='scoutTable' class='table' style='width:98%;'>
    <tr><th colspan='5'>Your location: {$player->location}" .
	(isset($villages[$player->location]) ? " (" . $villages[$player->location]['name'] . " Village)" : "") .
	"</th></tr>
	<tr><td colspan='5' style='text-align:center;'>";
	if($player->mission_id) {
		if($player->mission_stage['action_type'] == 'travel' or $player->mission_stage['action_type'] == 'search') {
            if($player->location === $player->mission_stage['action_data']) {
				echo "<a href='{$system->links['mission']}'><p class='button' style='margin-top:5px;'>Go to Mission Location</p></a><br />";
			}
		}
	}

	echo "<span style='font-style:italic;margin-bottom:3px;display:inline-block;font-size:0.9em;'>
        (Use WASD, arrow keys, or the arrows below)
    </span>";


    echo "<div class='travelContainer'>
        <div class='mapContainer'>" . renderMap($player, $villages, $icons) . "</div>
        <a class='travelButton north' href='$self_link&travel=north'><span class='upArrow'></span></a>
        <a class='travelButton west' href='$self_link&travel=west'><span class='leftArrow'></span></a>
        <a class='travelButton east' href='$self_link&travel=east'><span class='rightArrow'></span></a>
        <a class='travelButton south' href='$self_link&travel=south'><span class='downArrow'></span></a>
    </div>";
	echo "</td></tr>";

	require("scoutArea.php");
	scoutArea(true, false);

	echo "</table>";

}


function renderMap($player, $villages, $icons) {
    $output = "";
    $output .= "<table class='map' 
            style='padding:0;border:1px solid #000;border-collapse:collapse;border-spacing:0;border-radius:0;'>";
    for($y = 1; $y <= System::MAP_SIZE_Y; $y++) {
        $output .= "<tr>";
        for($x = 1; $x <= System::MAP_SIZE_X; $x++) {
            $key = $x . '.' . $y;
            if(isset($villages[$key])) {
                $output .= "<td class='village' style='background-image: url(./images/village_icons/" . $icons[$villages[$key]['count']] . ');';
                if($key == $player->village_location) {
                    $output .= "background-color:#FFEF30;";
                }
                $output .= "'>";
            }
            else {
                $output .= "<td>";
            }
            $output .= ($y == $player->y && $x == $player->x ? "<img src='./images/ninja_head.png' />" : "&nbsp;") . "</td>";
        }
        $output .= "</tr>";
    }
    $output .= "</table>";

    return $output;
}

