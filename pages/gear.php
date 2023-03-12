<?php

function gear(): void {
	global $system;

	global $player;

	global $self_link;

	$player->getInventory();

	$max_equipped_armor = 2;
	$max_equipped_weapons = 1;

	if($player->rank_num >= 3) {
		$max_equipped_armor++;
		$max_equipped_weapons++;
	}
	if($player->forbidden_seal_loaded && $player->forbidden_seal->level != 0) {
		$max_equipped_armor += $player->forbidden_seal->extra_armor_equips;
		$max_equipped_weapons += $player->forbidden_seal->extra_weapon_equips;
	}

	if(isset($_POST['equip_item'])) {
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
	else if(isset($_GET['use_item'])) {
		$item_id = (int)$system->clean($_GET['use_item']);
		try {
			if(!$player->checkInventory($item_id, 'item') or $player->items[$item_id]['use_type'] != 3) {
				throw new Exception("Invalid item!");
			}

			if($player->health >= $player->max_health) {
				throw new Exception("Your health is already maxed out!");
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

	echo "<table id='equipment gear' class='table'>";

	echo "<tr class='threeColumns'>
		<th style='width:33%;'>Weapons</th>
		<th style='width:33%;'>Armor</th>
		<th style='width:33%;'>Consumables</th>
	</tr>";


	// Use type: 1 = weapon, 2 = armor, 3 = consumable
	echo "<tr class='threeColumns'><td>";
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

    if($player->items) {
        $header_displayed = false;
        foreach($player->items as $item) {
            if($item['use_type'] == 4) {
                if(!$header_displayed) {
                    echo "<tr><th colspan='3'>Special</th></tr>";
                    $header_displayed = true;
                }
                echo "<td colspan='3'>";
                echo sprintf("%s <sup style='font-size:9px;'>(%s %s)</sup> <br />", $item['name'], $item['effect_amount'], $item['effect']);
                echo "</td>";
            }
        }
    }



	echo "
	<form action='$self_link' method='post' style='margin:0;'>
	<tr class='twoHeaders'>
		<th colspan='2'>Equipped Gear</th>
		<th>Use Items</th>
	</tr>";

	echo "<tr class='threeColumns'>";


	$item_count = 1;
	echo "<td class='fullwidth' style='width:33%;'>";
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

	echo "<td class='fullwidth' style='width:33%;'>";
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

	echo "<td class='fullwidth' style='text-align:center;'>";
	foreach($player->items as $id => $item) {
		if($item['use_type'] != 3) {
			continue;
		}

		if($item['quantity'] <= 0) {
			continue;
		}

		echo "<a href='$self_link&use_item=$id'><span class='button' style='min-width:8em;'>" . $item['name'] . '<br />';
		echo "<span style='font-weight:normal;'>Amount: {$item['quantity']}</span><br/>";
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
