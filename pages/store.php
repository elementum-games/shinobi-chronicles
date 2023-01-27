<?php
/* 
File: 		store.php
Coder:		Levi Meahan
Created:	08/26/2013
Revised:	12/03/2013 by Levi Meahan
Purpose:	Function for store which users can buy jutsu, gear, and consumables in
Algorithm:	See master_plan.html
*/

function store() {
	global $system;

	global $player;
	global $self_link;
	global $RANK_NAMES;
	
	$store_name = '';
	if($player->rank == 1) {
		$store_name = 'Academy';
	}
	
	$player->getInventory();
	
	$max_consumables = User::MAX_CONSUMABLES;
	
	if(!empty($_GET['view'])) {
		$view = $_GET['view'];
	}
	else {
		$view = 'jutsu';
	}
	
	// Load jutsu/items
	if($view == 'jutsu') {
		$shop_jutsu = array();
		$result = $system->query("SELECT * FROM `jutsu` WHERE `purchase_type` = '2' AND `rank` <= '$player->rank' ORDER BY `rank` ASC, `purchase_cost` ASC");
		while($row = $system->db_fetch($result)) {
			$shop_jutsu[$row['jutsu_id']] = $row;
		}
	}
	else {
		$shop_items = array();
		$result = $system->query("SELECT * FROM `items` WHERE `purchase_type` = '1' AND `rank` <= '$player->rank' ORDER BY `rank` ASC, `purchase_cost` ASC");
		while($row = $system->db_fetch($result)) {
			$shop_items[$row['item_id']] = $row;
		}
	}
	
	if(isset($_GET['purchase_item'])) {
		// Use type of 3, okay to purchase more, increment quantity
		// Use type of 1-2, only okay to purchase one
		$item_id = $system->clean($_GET['purchase_item']);
		try {
			// Check if item exists
			if(!isset($shop_items[$item_id])) {
				throw new Exception("Invalid item!");
			}
			
			// check if already owned
			if($player->checkInventory($item_id, 'item') && $shop_items[$item_id]['use_type'] != 3) {
				throw new Exception("You already own this item!");
			}
			
			if (isset($_GET['max'])) { // Code for handling buying bulk
				$max_missing = 0;
				if ($player->checkInventory($item_id, 'item')) {
					$max_missing = $max_consumables - $player->items[$item_id]['quantity'];
				} else {
					$max_missing = $max_consumables;
				}

				if($player->items[$item_id]['quantity'] >= $max_consumables) {
					throw new Exception("Your supply of this item is already full!");
				}

				if ($player->getMoney() < $shop_items[$item_id]['purchase_cost'] * $max_missing) {
					throw new Exception("You do not have enough money to buy the max amount!");

				}
				$player->subtractMoney(
                    $shop_items[$item_id]['purchase_cost'] * $max_missing,
                    "Purchased {$max_missing} of item #{$item_id}"
                );
				$player->items[$item_id]['item_id'] = $item_id;
				$player->items[$item_id]['quantity'] = $max_consumables;

			} else { //code for handling single purchases
				// Check for money requirement
				if($player->getMoney() < $shop_items[$item_id]['purchase_cost']) {
					throw new Exception("You do not have enough money!");
				}
			
				// Check for max consumables
				if($player->checkInventory($item_id, 'item') && $shop_items[$item_id]['use_type'] == 3) {
					if($player->items[$item_id]['quantity'] >= $max_consumables) {
						throw new Exception("Your supply of this item is already full!");
					}
				}

				$player->subtractMoney($shop_items[$item_id]['purchase_cost'], "Purchased item #{$item_id}");
				
				if(($shop_items[$item_id]['use_type'] == 1 || $shop_items[$item_id]['use_type'] == 2) || !$player->checkInventory($item_id, 'item')) {
					$player->items[$item_id]['item_id'] = $item_id;
					$player->items[$item_id]['quantity'] = 1;
				}
				else if($shop_items[$item_id]['use_type'] == 3) {
					$player->items[$item_id]['quantity']++;
				}
			} 
			$system->message("Item purchased!");
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}
	else if(isset($_GET['purchase_jutsu'])) {
		$jutsu_id = $system->clean($_GET['purchase_jutsu']);
		try {
			// Check if jutsu exists
			if(!isset($shop_jutsu[$jutsu_id])) {
				throw new Exception("Invalid jutsu!");
			}
			
			// check if already owned
			if($player->checkInventory($jutsu_id, 'jutsu')) {
				throw new Exception("You have already learned this jutsu!");
			}
			
			// Check for money requirement
			if($player->getMoney() < $shop_jutsu[$jutsu_id]['purchase_cost']) {
				throw new Exception("You do not have enough money!");
			}
			
			// Parent jutsu check
			if($shop_jutsu[$jutsu_id]['parent_jutsu']) {
				$id = $shop_jutsu[$jutsu_id]['parent_jutsu'];
				if(!isset($player->jutsu[$id])) {
					throw new Exception("You need to learn " . $shop_jutsu[$id]['name'] . " first!");
				}
			}	
			
			// Element check
			if($shop_jutsu[$jutsu_id]['element'] != 'None') {
				if(!$player->elements or array_search($shop_jutsu[$jutsu_id]['element'], $player->elements) === false) {
					throw new Exception("You do not have the elemental chakra for this jutsu!");
				}
			}
			
			
			// Add to inventory
			$player->subtractMoney($shop_jutsu[$jutsu_id]['purchase_cost'], "Purchased jutsu #{$jutsu_id}");

			$player->jutsu_scrolls[$jutsu_id] = Jutsu::fromArray($jutsu_id, $shop_jutsu[$jutsu_id]);
			
			$system->message("Jutsu purchased!");
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}
	
	$player->updateInventory();
	

	// Display
	echo "<div class='submenu'>
	<ul class='submenu'>
		<li style='width:31%;'><a href='{$self_link}&view=jutsu'>Jutsu Scrolls</a></li>
		<li style='width:31%;'><a href='{$self_link}&view=gear'>Gear</a></li>
		<li style='width:36%;'><a href='{$self_link}&view=consumables'>Consumables</a></li>
	</ul>
	</div>
	<div class='submenuMargin'></div>";
	$system->printMessage();	
	
	// View single jutsu
	if(!empty($_GET['view_jutsu'])) {
		$jutsu_list = false;
		$jutsu_id = (int)$system->clean($_GET['view_jutsu']);
		if(!isset($shop_jutsu[$jutsu_id])) {
			$system->message("Invalid jutsu!");
			$system->printMessage();
		}
		else {
			$jutsu = $shop_jutsu[$jutsu_id];
			echo "<table class='table'>
			<tr><th>" . $jutsu['name'] . " (<a href='$self_link'>Return</a>)</th></tr>
			<tr><td>
				<label style='width:6.5em;'>Rank:</label>" . $RANK_NAMES[$jutsu['rank']] . "<br />";
				if($jutsu['parent_jutsu']) {
					echo "<label style='width:6.5em;'>Parent Jutsu:</label>" . 
						$shop_jutsu[$jutsu['parent_jutsu']]['name'] . "<br />";
				}
				if($jutsu['element'] != 'None') {
					echo "<label style='width:6.5em;'>Element:</label>";
					if($player->elements && array_search($jutsu['element'], $player->elements) !== false) {
						echo "<span style='color:#00C000;font-weight:bold;'>";
					}
					else {
						echo "<span style='color:#C00000;font-weight:bold;'>";
					}
					echo $jutsu['element'] . "</span><br />";
				}
				echo "<label style='width:6.5em;'>Use cost:</label>" . $jutsu['use_cost'] . "<br />";
				if($jutsu['cooldown']) {
					echo "<label style='width:6.5em;'>Cooldown:</label>" . $jutsu['cooldown'] . " turn(s)<br />";
				}
				if($jutsu['effect']) {
					
					echo "<label style='width:6.5em;'>Effect:</label>" . ucwords(str_replace('_', ' ', $jutsu['effect'])) . "<br />";
				}
				echo "<label style='width:6.5em;float:left;'>Description:</label> 
					<p style='display:inline-block;margin:0px;width:37.1em;'>" . $jutsu['description'] . "</p>
				<br style='clear:both;' />
				<label style='width:6.5em;'>Jutsu type:</label>" . ucwords($jutsu['jutsu_type']);
				$result = $system->query("SELECT `name` FROM `jutsu` WHERE `parent_jutsu`='$jutsu_id'");
				if($system->db_last_num_rows > 0) {
					echo "<br />
					<br /><label>Learn <b>" . $jutsu['name'] . "</b> to level 50 to unlock:</label>
						<p style='margin-left:10px;margin-top:5px;'>";
					while($row = $system->db_fetch($result)) {
						echo $row['name'] . "<br />";
					}
					echo "</p>";
				}
				
				echo "</td></tr></table>";
		}
		$view = false;
	}
	
	
	if($view == 'jutsu') {
		$jutsu_type = '';
		if(!empty($_GET['jutsu_type'])) {
			$jutsu_type = $_GET['jutsu_type'];
			switch($jutsu_type) {
				case 'ninjutsu':
				case 'taijutsu':
				case 'genjutsu':
					break;
				default:
					$jutsu_type = '';
					break;
			}
		}
		else {
		    if($player->ninjutsu_skill > $player->taijutsu_skill && $player->ninjutsu_skill > $player->genjutsu_skill) {
		        $jutsu_type = 'ninjutsu';
            }
		    else if($player->taijutsu_skill > $player->genjutsu_skill && $player->taijutsu_skill > $player->ninjutsu_skill) {
		        $jutsu_type = 'taijutsu';
            }
		    else if($player->genjutsu_skill > $player->taijutsu_skill && $player->genjutsu_skill > $player->ninjutsu_skill) {
		        $jutsu_type = 'genjutsu';
            }
        }

		$style = "style='text-decoration:none;'";

		echo "<table class='table'><tr><th>Jutsu Scrolls</th></tr>
		<tr><td style='text-align:center;'>You can buy Jutsu Scrolls in this section for any jutsu of your rank or below.
		Once you have purchased a scroll, go to the Jutsu page to learn the jutsu.<br />
		<br />
		<b>Your money:</b> &yen;{$player->getMoney()}</td></tr></table>

		<p style='text-align:center;margin-bottom:0;'>
			<a href='$self_link&view=jutsu&jutsu_type=ninjutsu' " . ($jutsu_type == 'ninjutsu' ? $style : "") . ">Ninjutsu</a> |
			<a href='$self_link&view=jutsu&jutsu_type=taijutsu' " . ($jutsu_type == 'taijutsu' ? $style : "") . ">Taijutsu</a> |
			<a href='$self_link&view=jutsu&jutsu_type=genjutsu' " . ($jutsu_type == 'genjutsu' ? $style : "") . ">Genjutsu</a>
		</p>

		<table class='table' style='margin-top:15px;'>
		<tr id='shop_table_header' >
			<th style='width:15%;'>Name</th>
			<th style='width:15%;'>Effect</th>
			<th style='width:10%;'>Type</th>
			<th style='width:10%;'>Cost</th>
			<th style='width:10%;'></th>
		</tr>";

		if(!$shop_jutsu) {
			echo "<tr><td colspan='5'>No jutsu found!</td></tr>";
		}
		else {
			$count = 0;

			$rank = current($shop_jutsu)['rank'];

			foreach($shop_jutsu as $id => $jutsu) {
				if($jutsu_type && $jutsu['jutsu_type'] != $jutsu_type) {
					continue;
				}

				if($player->checkInventory($jutsu['jutsu_id'], 'jutsu')) {
					continue;
				}
				if(isset($player->jutsu_scrolls[$jutsu['jutsu_id']])) {
					continue;
				}
				$count++;

				echo "<tr class='table_multicolumns'>
					<td style='width:30%;'><a href='$self_link&view=jutsu&view_jutsu=$id'>{$jutsu['name']}</a></td>
					<td style='width:25%;text-align:center;'>" . ucwords(str_replace('_', ' ', $jutsu['effect'])) . "</td>
					<td style='width:25%;text-align:center;'>" . ucwords(str_replace('_', ' ', $jutsu['jutsu_type'])) . "</td>
					<td style='width:26%;text-align:center;'>&yen;{$jutsu['purchase_cost']}</td>
					<td style='width:28%;text-align:center;'>
						<a href='$self_link&view=jutsu&purchase_jutsu={$jutsu['jutsu_id']}&jutsu_type={$jutsu['jutsu_type']}'>Purchase</a></td>
				</tr>";
			}

			if($count == 0) {
				echo "<tr><td colspan='4'>No jutsu available!</td></tr>";
			}
		}
		echo "</table>";
	}
	else if($view == 'gear' || $view == 'consumables') {
		// Set use type to passive(gear) or consumable - Default consumable
		$category = 'consumables';
		if($view == 'gear') {
			$category = 'gear';		
		}
		
		echo "<table class='table'><tr><th>" . ucwords($category) . "</th></tr>
		<tr><td style='text-align:center;'>You can buy armor/consumable items in this section for your rank or below.<br />
		<br />
		<b>Your money:</b> &yen;{$player->getMoney()}</td></tr></table>
		
		<table class='table'><tr>
			<th style='width:35%;'>Name</th>
			<th style='width:25%;'>Effect</th>
			<th style='width:20%;'>Cost</th>
			<th style='width:20%;'></th>
		</tr>";
		
		if(!$shop_items) {
			echo "<tr><td colspan='4'>No items found!</td></tr>";
		}
		else {
			$count = 0;
			foreach($shop_items as $item) {
				if($item['use_type'] == 3 && $category != 'consumables') {
					continue;
				}
				else if(($item['use_type'] == 1 || $item['use_type'] == 2) && $category != 'gear') {
					continue;
				}

				if($category != 'consumables' && $player->checkInventory($item['item_id'], 'item')) {
					continue;
				}

				$count++;

				if($category == 'consumables' && $player->checkInventory($item['item_id'], 'item')) {
					$owned = $player->items[$item['item_id']]['quantity'];
				}
				else {
					$owned = 0;
				}

				echo "<tr class='table_multicolumns' style='text-align:center;'>
					<td style='width:35%;'>{$item['name']}" .
					($owned ? "<br />(Owned: $owned/$max_consumables)" : "") .
					"</td>
					<td style='width:25%;'>" . ucwords(str_replace('_', ' ', $item['effect'])) . "</td>
					<td style='width:20%;'>&yen;{$item['purchase_cost']}</td>
					<td style='width:20%;'><a href='$self_link&view=$category&purchase_item={$item['item_id']}'>Purchase</a>" .
					($category == 'consumables' ? "/<br><a href='$self_link&view=$category&purchase_item={$item['item_id']}&max=true'>Purchase Max</a>" : "") .
					"</td>
				</tr>";

			}

			if($count == 0) {
				echo "<tr><td colspan='4'>No items available!</td></tr>";
			}
		}
		echo "</table>";
	}


}

?>
