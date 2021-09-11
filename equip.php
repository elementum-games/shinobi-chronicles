<?php
/* 
File: 		equip.php
Coder:		Levi Meahan
Created:	09/04/2013
Revised:	04/22/2014 by Levi Meahan
Purpose:	Functions for equip where users can equip items and jutsu
Algorithm:	See master_plan.html
*/

function gear() {
	require("variables.php");
	global $system;

	global $player;
	
	global $self_link;
	
	$player->getInventory();
	
	$max_equipped_armor = 2;
	$max_equipped_weapons = 1;
	
	if($player->rank >= 3) {
		$max_equipped_armor++;
		$max_equipped_weapons++;
	}
	if($player->forbidden_seal && $player->forbidden_seal['level'] >= 2) {
		$max_equipped_armor++;
		$max_equipped_weapons++;
	}
	
	
		
	if($_POST['equip_item']) {
		$view = 'items';
		$items = $_POST['items'];
		$equipped_items = array();
		
		$equip_ok = true;
		$equipped_armor = 0;
		$equipped_weapons = 0;
		foreach($items as $id) {
			if($player->checkInventory($id, 'item') && $player->items[$id]['use_type'] != 3) {
				$equipped_items[] = $system->clean($id);
				if($player->items[$id]['use_type'] == 1) {
					$equipped_weapons++;
					if($equipped_weapons > $max_equipped_weapons) {
						$system->message("You can only have " . $max_equipped_weapons . " equipped!");
						$equip_ok = false;
					}
				}
				if($player->items[$id]['use_type'] == 2) {
					$equipped_armor++;
					if($equipped_armor > $max_equipped_armor) {
						$system->message("You can only have " . $max_equipped_armor . " equipped!");
						$equip_ok = false;
					}
				}
			}
		}
		
		if($equip_ok) {
			$player->equipped_items = $equipped_items;
			$system->message("Items equipped!");
		}
	}
	else if($_GET['use_item']) {
		$item_id = (int)$system->clean($_GET['use_item']);
		try {
			if(!$player->checkInventory($item_id, 'item') or $player->items[$item_id]['use_type'] != 3) {
				throw new Exception("Invalid item!");
			}
			
			if($player->items[$item_id]['quantity'] <= 0) {
				throw new Exception("You do not have any more of this item!");
			}
			
			$player->items[$item_id]['quantity']--;
			switch($player->items[$item_id]['effect']) {
				case 'heal':
					$player->health += $player->items[$item_id]['effect_amount'];
					if($player->health > $player->max_health) {
						$player->health = $player->max_health;
					}
					$system->message("Restored " . $player->items[$item_id]['effect_amount'] . " HP.");
					break;
				default:
					$player->items[$item_id]['quantity']++;
					break;
			}
			
			$player->updateInventory();
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}
	
	
	$system->printMessage();
	
	echo "<table class='table'>";
	
	echo "<tr>
		<th style='width:33%;'>Weapons</th>
		<th style='width:33%;'>Armor</th>
		<th style='width:33%;'>Consumables</th>
	</tr>";
	
	
	// Use type: 1 = weapon, 2 = armor, 3 = consumable
	echo "<tr><td>";
	if($player->items) {
		foreach($player->items as $item) {
			if($item['use_type'] != 1) {
				continue;
			}
			
			$item['effect'] = str_replace("_", " ", $item['effect']);

			echo sprintf("%s <sup style='font-size:9px;'>(%s %s)</sup> <br />", $item['name'], $item['effect_amount'], $item['effect']);
		}
	}
	echo "</td>";
	
	echo "<td>";
	if($player->items) {
		foreach($player->items as $item) {
			if($item['use_type'] != 2) {
				continue;
			}
			

			echo sprintf("%s <sup style='font-size:9px;'>(%s %s)</sup> <br />", $item['name'], $item['effect_amount'], $item['effect']);

		}
	}
	echo "</td>";
	
	echo "<td>";
	if($player->items) {
		foreach($player->items as $item) {
			if($item['use_type'] != 3) {
				continue;
			}
			
			echo sprintf("%s <sup style='font-size:9px;'>(%s %s)</sup> <br />", $item['name'], $item['effect_amount'], $item['effect']);
		}
	}
	echo "</td>";
	
	echo "</td></tr>";
	
	
	echo "
	<form action='$self_link' method='post' style='margin:0px;'>
	<tr>
		<th colspan='2'>Equipped Gear</th>
		<th>Use Items</th>
	</tr>";		
			
	echo "<tr>";
	
	
	$item_count = 1;
	echo "<td style='width:33%;'>";
	$equipped_weapons = array();
	for($i = 0; $i < $max_equipped_weapons; $i++) {
		$selected_displayed = false;
		echo "<select name='items[" . ($item_count++) . "]'>
		<option value='none'>None</option>";
		foreach($player->items as $item) {
			if($item['use_type'] != 1) {
				continue;
			}
			echo "<option value='{$item['item_id']}'";
			if(array_search($item['item_id'], $player->equipped_items) !== false && !isset($equipped_weapons[$item['item_id']])
			&& !$selected_displayed) {
				$selected_displayed = true;
				echo " selected='selected' ";
				$equipped_weapons[$item['item_id']] = $item['item_id'];
			}
			echo ">{$item['name']}</option>";
		}
		echo "</select><br />";
	}
	echo "</td>";
	
	echo "<td style='width:33%;'>";
	$equipped_armor = array();
	for($i = 0; $i < $max_equipped_armor; $i++) {
		$selected_displayed = false;
		echo "<select name='items[" . ($item_count++) . "]'>
		<option value='none'>None</option>";
		foreach($player->items as $item) {
			if($item['use_type'] != 2) {
				continue;
			}
			echo "<option value='{$item['item_id']}'";
			if(array_search($item['item_id'], $player->equipped_items) !== false && !isset($equipped_armor[$item['item_id']])
			&& !$selected_displayed) {
				$selected_displayed = true;
				echo " selected='selected' ";
				$equipped_armor[$item['item_id']] = $item['item_id'];
			}
			
			
			echo ">{$item['name']}</option>";
		}
		echo "</select><br />";
	}
	echo "</td>";
	
	echo "<td style='text-align:center;'>";
	foreach($player->items as $id => $item) {
		if($item['use_type'] != 3) {
			continue;
		}
		
		if($item['quantity'] <= 0) {
			continue;
		}
		
		echo "<a href='$self_link&use_item=$id'><span class='button' style='min-width:8em;'>" . $item['name'] . '<br />';
		if($item['effect'] == 'heal') {
			echo "<span style='font-weight:normal;'>(Heal " . $item['effect_amount'] . " HP)</span></span></a><br />";
		}
		echo "<br />";
	}
	echo "</select>
	</td>
	
	
	<br />
	<tr><td colspan='3' style='text-align:center;'>";
		
	echo "<input type='submit' name='equip_item' value='Equip' />
	</td></tr>
	</form>
	</table>";	
		
	$player->updateInventory();
}

function jutsu() {
	require("variables.php");
	global $system;

	global $player;
	
	global $self_link;
	
	$player->getInventory();
	
	$max_equipped_jutsu = 3;
	if($player->rank >= 3) {
		$max_equipped_jutsu++;
	}
	if($player->forbidden_seal && $player->forbidden_seal['level'] >= 2) {
		$max_equipped_jutsu++;
	}
	
	if($_POST['equip_jutsu']) {
		$jutsu = $_POST['jutsu'];
		$equipped_jutsu = array();
		
		try {
			$count = 0;
			$jutsu_types = array('ninjutsu', 'taijutsu', 'genjutsu');
			foreach($jutsu as $jutsu_data) {
				if($count >= $max_equipped_jutsu) {
					break;
				}
				
				$jutsu_array = explode('-', $jutsu_data);
				if($jutsu_array[0] == 'none') {
					continue;
				}
				
				if(array_search($jutsu_array[0], $jutsu_types) === false) {
					throw new Exception("Invalid jutsu type!");
					break;
				}
				if($player->checkInventory($jutsu_array[1], 'jutsu')) {		
					$equipped_jutsu[$count]['id'] = $system->clean($jutsu_array[1]);				
					$equipped_jutsu[$count]['type'] = $system->clean($jutsu_array[0]);
					$count++;
				}
			}
			
			$player->equipped_jutsu = $equipped_jutsu;
			$system->message("Jutsu equipped!");
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}
	
	if($_GET['learn_jutsu']) {
		$jutsu_id = (int)$_GET['learn_jutsu'];
		try {
			if(!isset($player->jutsu_scrolls[$jutsu_id])) {
				throw new Exception("Invalid jutsu!");
			}
			if($player->checkInventory($jutsu_id, 'jutsu')) {
				throw new Exception("You already know that jutsu!");
			}
		
		
			// Parent jutsu check
			if($player->jutsu_scrolls[$jutsu_id]['parent_jutsu']) {
				$id = $player->jutsu_scrolls[$jutsu_id]['parent_jutsu'];
				if(!isset($player->jutsu[$id])) {
					throw new Exception("You need to learn " . $player->jutsu[$id]['name'] . " first!");
				}
				
				if($player->jutsu[$id]['level'] < 50) {
					throw new Exception("You are not skilled enough with " . $player->jutsu[$id]['name'] . 
						"! (Level " . $player->jutsu[$id]['level'] . "/50)");
				}
			}
		
			$player->jutsu[$jutsu_id] = $player->jutsu_scrolls[$jutsu_id];
			$player->jutsu[$jutsu_id]['level'] = 1;
			$jutsu_name = $player->jutsu_scrolls[$jutsu_id]['name'];
			
			switch($player->jutsu[$jutsu_id]['jutsu_type']) {
				case 'ninjutsu':
					$player->ninjutsu_ids[] = $jutsu_id;
					break;
				case 'taijutsu':
					$player->taijutsu_ids[] = $jutsu_id;
					break;
				case 'genjutsu':
					$player->genjutsu_ids[] = $jutsu_id;
					break;
			}
			
			unset($player->jutsu_scrolls[$jutsu_id]);
			$system->message("You have learned $jutsu_name!");
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}
	
	$system->printMessage();
	echo "<table class='table'>";
	
	// View single jutsu details
	$jutsu_list = true;
	
	if($_GET['view_jutsu']) {
		$jutsu_list = false;
		$jutsu_id = (int)$system->clean($_GET['view_jutsu']);
		if(!isset($player->jutsu[$jutsu_id])) {
			$system->message("Invalid jutsu!");
			$system->printMessage();
		}
		else {
			$jutsu = $player->jutsu[$jutsu_id];
			echo "<tr><th>" . $jutsu['name'] . " (<a href='$self_link'>Return</a>)</th></tr>
			<tr><td>
				<label style='width:6.5em;'>Rank:</label>" . $jutsu['rank'] . "<br />";
				if($jutsu['element'] != 'none') {
					echo "<label style='width:6.5em;'>Element:</label>" . $jutsu['element'] . "<br />";
				}
				echo "<label style='width:6.5em;'>Use cost:</label>" . $jutsu['use_cost'] . "<br />";
				if($jutsu['jutsu_type'] != 'taijutsu') {
					echo "<label style='width:6.5em;'>Hand seals:</label>" . $jutsu['hand_seals'] . "<br />";
				}
				if($jutsu['cooldown']) {
					echo "<label style='width:6.5em;'>Cooldown:</label>" . $jutsu['cooldown'] . " turn(s)<br />";
				}
				if($jutsu['effect']) {
					echo "<label style='width:6.5em;'>Effect:</label>" . 
						ucwords(str_replace('_', ' ', $jutsu['effect'])) . " - " . $jutsu['effect_length'] . " turns<br />";
				}
				echo "<label style='width:6.5em;'>Jutsu type:</label>" . ucwords($jutsu['jutsu_type']) . "<br />
				<label style='width:6.5em;'>Power:</label>" . round($jutsu['power'], 1) . "<br />
				<label style='width:6.5em;'>Level:</label>" . $jutsu['level'] . "<br />
				<label style='width:6.5em;'>Exp:</label>" . $jutsu['exp'] . "<br />";
			
				echo "<label style='width:6.5em;float:left;'>Description:</label> 
					<p style='display:inline-block;margin:0px;width:37.1em;'>" . $jutsu['description'] . "</p>
					<br style='clear:both;' />";
				
				$result = $system->query("SELECT `name` FROM `jutsu` WHERE `parent_jutsu`='$jutsu_id'");
				if($system->db_num_rows > 0) {
					echo "<br />
					<br /><label>Learn <b>" . $jutsu['name'] . "</b> to level 50 to unlock:</label>
						<p style='margin-left:10px;margin-top:5px;'>";
					while($row = $system->db_fetch($result)) {
						echo $row['name'] . "<br />";
					}
					echo "</p>";
				}
			echo "</td></tr>";
		}
	}
	
	if($jutsu_list) {
		echo "<tr>
			<th style='width:33%;'>Ninjutsu</th>
			<th style='width:33%;'>Taijutsu</th>
			<th style='width:33%;'>Genjutsu</th>
		</tr>";
		
		$jutsu_array = array();
		
		echo "<tr><td>";
		if($player->ninjutsu_ids) {
			$sortedJutsu = array();
			foreach($player->ninjutsu_ids as $jutsu_id) {
				array_push($sortedJutsu, $player->jutsu[$jutsu_id]['rank']);
			}
			array_multisort($sortedJutsu, $player->ninjutsu_ids);
			foreach ($player->ninjutsu_ids as $jutsu_id) {
				echo "<a href='$self_link&view_jutsu=$jutsu_id' title='Level: {$player->jutsu[$jutsu_id]['level']}'>" . $player->jutsu[$jutsu_id]['name'] . "</a><br />";
			}
		}
		echo "</td>";
		
		echo "<td>";
		if($player->taijutsu_ids) {
			$sortedJutsu = array();
			foreach($player->taijutsu_ids as $jutsu_id) {
				array_push($sortedJutsu, $player->jutsu[$jutsu_id]['rank']);
			}
			array_multisort($sortedJutsu, $player->taijutsu_ids);
			foreach($player->taijutsu_ids as $jutsu_id) {
				echo "<a href='$self_link&view_jutsu=$jutsu_id' title='Level: {$player->jutsu[$jutsu_id]['level']}'>" . $player->jutsu[$jutsu_id]['name'] . "</a><br />";
			}
		}
		echo "</td>";
		
		echo "<td>";
		if($player->genjutsu_ids) {
			$sortedJutsu = array();
			foreach($player->genjutsu_ids as $jutsu_id) {
				array_push($sortedJutsu, $player->jutsu[$jutsu_id]['rank']);
			}
			array_multisort($sortedJutsu, $player->genjutsu_ids);
			foreach($player->genjutsu_ids as $jutsu_id) {
				echo "<a href='$self_link&view_jutsu=$jutsu_id' title='Level: {$player->jutsu[$jutsu_id]['level']}'>" . $player->jutsu[$jutsu_id]['name'] . "</a><br />";
			}
		}
		echo "</td></tr>";
		echo "<tr><th colspan='3'>Equipped Jutsu</th></tr>";	
				
		echo "<tr><td colspan='3'>
		<form action='$self_link' method='post'>
		<div style='text-align:center;'>";
		
		echo "<div style='display:inline-block;'>";
		$row_start = 1;
		for($i = 0; $i < $max_equipped_jutsu; $i++) {
			echo "<select name='jutsu[" . ($i + 1) . "]'>
			<option value='none' " . (!$player->equipped_jutsu ? "selected='selected'" : "") . ">None</option>";
			foreach($player->jutsu as $jutsu) {
				echo "<option value='{$jutsu['jutsu_type']}-{$jutsu['jutsu_id']}' " . 
					($jutsu['jutsu_id'] == $player->equipped_jutsu[$i]['id'] ? "selected='selected'" : "") . 
				">{$jutsu['name']}</option>";
			}
			echo "</select><br />";
			
			// Start second row
			if($row_start++ > 2) {
				echo "</div><div style='display:inline-block;'>";
				$row_start = 1;
			}
		}
		echo "</div><br />";
		
		echo "<input type='submit' name='equip_jutsu' value='Equip' />
		</div>
		</form>
		</tr>";	
		
		
		// Purchase jutsu
		if(!empty($player->jutsu_scrolls)) {
			echo "<tr><th colspan='3'>Jutsu scrolls</th></tr>";
			
			foreach($player->jutsu_scrolls as $id => $jutsu) {
				echo "<tr><td colspan='3'>
					<span style='font-weight:bold;'>" . $jutsu['name'] . "</span><br />
					<div style='margin-left:2em;'>
						<label style='width:6.5em;'>Rank:</label>" . $jutsu['rank'] . "<br />
						<label style='width:6.5em;'>Element:</label>" . $jutsu['element'] . "<br />
						<label style='width:6.5em;'>Use cost:</label>" . $jutsu['use_cost'] . "<br />" .
						($jutsu['cooldown'] ? "<label style='width:6.5em;'>Cooldown:</label>" . $jutsu['cooldown'] . " turn(s)<br />" : "") .
						"<label style='width:6.5em;float:left;'>Description:</label> 
						<p style='display:inline-block;margin:0px;width:37.1em;'>" . $jutsu['description'] . "</p>
						<br style='clear:both;' />
						<label style='width:6.5em;'>Jutsu type:</label>" . ucwords($jutsu['jutsu_type']) . "<br />
					</div>
					<p style='text-align:right;margin:0px;'><a href='$self_link&learn_jutsu=$id'>Learn</a></p>
				</td></tr>";
			}
		}
	}
		
	echo "</table>";
	
	$player->updateInventory();
}

?>
