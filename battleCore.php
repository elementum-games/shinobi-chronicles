<?php
/* 
File: 		battleCore.php
Coder:		Levi Meahan
Created:	09/07/2013
Revised:	08/13/2014 by Levi Meahan
Purpose:	Functions for running AI and PvP battles
Algorithm:	See master_plan.html
*/

/*
FOR PURPOSES OF AI FIGHTS HUMAN IS ALWAYS PLAYER 1
DATA STRUCTURE
-battle_id
-battle_type (1 = AI, 2 = Spar, 3 = Fight, 4 = Challenge)
-player1
-player2
-winner
-battle_text
-active_effects
-turn
-turn_time
Player and opponent both attack
Second attack goes off even if first attack hits
Use jutsu
Show jutsu description used
Possible battle states
-User has just started battle
	-Display jutsu
-User has submitted attack
	-Validate, calculate damage, generate AI move
-Battle has ended (one or both fighters at or below 0 hp)
ALGORITHM
-Load battle data
-If winner, exit
-Check for move choice
	-Validate, calculate damage, get opponent's move, calculate damage
	-Check for and set winner if either health below 0 (-1 signifies a tie)
-Display
	-Health and opponent's health
	-Moves used if any
	-Move prompt if no winner
*/

/* TO-DO LIST 
	1) Damage Formula / Put functions for calculating raw damage dealt and taken in classes
	3) Make player items from inventory usable
	4) Make player and AI attacks chosen/validated through functions
*/

function battleAI(&$player, &$opponent) {
	require("variables.php");
	global $system;
	global $self_link;

	$player->getInventory();
	$opponent->loadData();
	
	// Load battle data
	$winner = false;
	$battle_text = '';
	
	// Load default attacks
	$default_attacks = array();
	$query = "SELECT * FROM `jutsu` WHERE `purchase_type`='1'";
	$result = $system->query($query);
	while($row = $system->db_fetch($result)) {
		$default_attacks[$row['jutsu_id']] = $row;
	}
	
	// Apply passive effects
	$effect_target = null;
	$effect_user = null;
	if(isset($_SESSION['active_effects'])) {
		$active_effects = $_SESSION['active_effects'];

		foreach($active_effects as $id => $effect) {
			if($system->debug['battle']) {
				echo "[$id]: " . $effect['effect'] . "(". $effect['effect_amount'] . ") -> " . $effect['target'] . "<br />";
			}
			if($effect['target'] == $player->id) {
				$effect_target =& $player;
			}
			else {
				$effect_target =& $opponent;
			}
			if($effect['user'] == $player->user_id) {
				$effect_user =& $player;
			}
			else {
				$effect_user =& $opponent;
			}
			applyPassiveEffects($effect_target, $effect_user, $effect);
		}
		unset($effect_target);
		unset($effect_user);
	}
	else {
		$active_effects = array();
		$_SESSION['active_effects'] = array();
	}
	
	// Apply genjutsu passive effects
	if(isset($_SESSION['active_genjutsu'])) {
		$active_genjutsu = $_SESSION['active_genjutsu'];
		foreach($active_genjutsu as $id => $genjutsu) {
			if($system->debug['battle']) {
				echo '[' . $id . '] = ' . $genjutsu['effect'] . '(' . $genjutsu['effect_amount'] . ') -> ' . $genjutsu['target'] . '<br />';
			}
			if($genjutsu['target'] == 'P' . $player->user_id) {
				$effect_target =& $player;
			}
			else {
				$effect_target =& $opponent;
			}
			if($genjutsu['user'] == $player->user_id) {
				$effect_user =& $player;
			}
			else {
				$effect_user =& $opponent;
			}
			applyPassiveEffects($effect_target, $effect_user, $genjutsu);
		}
	}
	else {
		$active_genjutsu = array();
		$_SESSION['active_genjutsu'] = array();
	}
	
	// Load cooldowns
	if(isset($_SESSION['jutsu_cooldowns'])) {
		$jutsu_cooldowns = $_SESSION['jutsu_cooldowns'];
	}
	else {
		$jutsu_cooldowns = array();
		$_SESSION['jutsu_cooldowns'] = array();
	}
	
	// Apply item passive effects
	if(!empty($player->equipped_armor)) {
		foreach($player->equipped_armor as $item_id) {
			if($player->checkInventory($item_id, 'item')) {
				$effect = array(
					'effect' => $player->items[$item_id]['effect'],
					'effect_amount' => $player->items[$item_id]['effect_amount']
				);
				applyPassiveEffects($player, $opponent, $effect, $player1_effect_display);
			}
		}
	}
	
	// Apply bloodline passive effects (REMOVED - Moved to classes.php)
	// Load jutsu used logs
	if(!empty($_SESSION['player_jutsu_used'])) {
		$player_jutsu_used = $_SESSION['player_jutsu_used'];
	}
	else {
		$player_jutsu_used = array();
	}	
	
	// Check for move choice
	if($_POST['attack']) {
		// Validate, calculate and apply damage	
		// $player_attack = $system->clean($_POST['attack_choice']);
		// $player_attack = explode('-', $player_attack);
		// $attack_type = $player_attack[0];
		// $attack_id = $player_attack[1];
		
		// Run player attack
		try {		
			$jutsu_type = $_POST['jutsu_type'];
			// Check for handseals if ninjutsu/genjutsu (+Layered genjutsu check)
			if($jutsu_type == 'ninjutsu' or $jutsu_type == 'genjutsu') {
				if(!$_POST['hand_seals']) {
					throw new Exception("Please enter hand seals!");
				}
				if(is_array($_POST['hand_seals'])) {
					$seals = array();
					foreach($_POST['seals'] as $seal) {
						if(!is_numeric($seal)) {
							break;
						}
						$seals[] = $seal;
					}
					$seal_string = implode('-', $seals);
				}
				else {
					$raw_seals = explode('-', $_POST['hand_seals']);
					$seals = array();
					foreach($raw_seals as $seal) {
						if(!is_numeric($seal)) {
							break;
						}
						$seals[] = $seal;
					}
					$seal_string = implode('-', $seals);
				}
				$jutsu_ok = false;
				$jutsu_id = 0;
				foreach($default_attacks as $id => $attack) {
					if($attack['hand_seals'] == $seal_string) {
						$jutsu_ok = true;
						$attack_id = $id;
						$purchase_type = 'default';
						$player_jutsu = $default_attacks[$attack_id];
						break;
					}
				}
				foreach($player->jutsu as $id => $jutsu) {
					if($jutsu['hand_seals'] == $seal_string) {
						$jutsu_ok = true;
						$attack_id = $id;
						$purchase_type = 'equipped';
						$player_jutsu = $player->jutsu[$attack_id];
						break;
					}
				}
				$jutsu_unique_id = 'J:' . $attack_id . ':' . $player->id;
				// Layered genjutsu check
				if($jutsu_ok && $jutsu_type == 'genjutsu' && !empty($player_jutsu['parent_jutsu'])) {
					$parent_genjutsu_id = $player->id . ':J' . $player_jutsu['parent_jutsu'];
					$parent_jutsu = $player->jutsu[$player_jutsu['parent_jutsu']];
					if(!isset($active_genjutsu[$parent_genjutsu_id]) or 
					$active_genjutsu[$parent_genjutsu_id]['turns'] == $parent_jutsu['effect_length']) {
						throw new Exception($parent_jutsu['name'] . 
							' must be active for 1 turn before using this jutsu!'
						);
					}
				}
			}
			// Check jutsu ID if taijutsu
			else if($jutsu_type == 'taijutsu') {
				$jutsu_ok = false;
				$jutsu_id = (int)$_POST['jutsu_id'];
				if(isset($default_attacks[$jutsu_id]) && $default_attacks[$jutsu_id]['jutsu_type'] == 'taijutsu') {
					$jutsu_ok = true;
					$attack_id = $jutsu_id;
					$purchase_type = 'default';
					$player_jutsu = $default_attacks[$attack_id];
				}
				if(isset($player->jutsu[$jutsu_id]) && $player->jutsu[$jutsu_id]['jutsu_type'] == 'taijutsu') {
					$jutsu_ok = true;
					$attack_id = $jutsu_id;
					$purchase_type = 'equipped';
					$player_jutsu = $player->jutsu[$attack_id];
				}
				$jutsu_unique_id = 'J:' . $attack_id . ':' . $player->id;
			}
			// Check BL jutsu ID if bloodline jutsu
			else if($jutsu_type == 'bloodline_jutsu' && $player->bloodline_id) {
				$jutsu_ok = false;
				$jutsu_id = (int)$_POST['jutsu_id'];
				if(isset($player->bloodline->jutsu[$jutsu_id])) {
					$jutsu_ok = true;
					$attack_id = $jutsu_id;
					$purchase_type = 'bloodline';
					$player_jutsu = $player->bloodline->jutsu[$attack_id];
				}
				$jutsu_unique_id = 'BL_J:' . $attack_id . ':' . $player->id;
			}
			else {
				throw new Exception("Invalid jutsu selection!");
			}
			// Check jutsu cooldown
			if($jutsu_ok && isset($jutsu_cooldowns[$jutsu_unique_id])) {
				throw new Exception("Cannot use that jutsu, it is on cooldown for " . $jutsu_cooldowns[$jutsu_unique_id] . " more turns!");
			}
			// Run usage check
			if(!$player->useJutsu($player_jutsu, $purchase_type . '_jutsu')) {
				throw new Exception($system->message);
			}
			// Run turn effects
			$effect_win = 0;
			$player_effect_display = '';
			$opponent_effect_display = '';
			$effect_display = '';
			if(!empty($active_effects)) {
				foreach($active_effects as $id => $effect) {
					if($effect['target'] == $player->id) {
						$effect_target =& $player;
						$effect_display =& $player_effect_display;
					}
					else {
						$effect_target =& $opponent;
						$effect_display =& $opponent_effect_display;
					}
					if($effect['user'] == $player->id) {
						$effect_user =& $player;
					}
					else {
						$effect_user =& $opponent;
					}
					applyActiveEffects($effect_target, $effect_user, $effect, $effect_display, $effect_win);
					$active_effects[$id]['turns']--;
					if($effect['turns'] <= 0) {
						unset($active_effects[$id]);
					}
				}
				unset($effect_target);
				unset($effect_user);
			}
			if(!empty($active_genjutsu)) {
				foreach($active_genjutsu as $id => $genjutsu) {
					if($genjutsu['target'] == 'P' . $player->user_id) {
						$effect_target =& $player;
						$effect_display =& $player_effect_display;
					}
					else {
						$effect_target =& $opponent;
						$effect_display =& $opponent_effect_display;
					}
					if($genjutsu['user'] == 'P' . $player->user_id) {
						$effect_user =& $player;
					}
					else {
						$effect_user =& $opponent;
					}
					applyActiveEffects($effect_target, $effect_user, $genjutsu, $effect_display, $effect_win);
					$active_genjutsu[$id]['turns']--;
					$active_genjutsu[$id]['power'] *= 0.9;
					if($genjutsu['turns'] <= 0) {
						unset($active_genjutsu[$id]);
					}
				}
			}
			if(!empty($player->bloodline->combat_boosts)) {
				// var_dump($player->bloodline->combat_boosts);
				foreach($player->bloodline->combat_boosts as $id=>$effect) {
					applyActiveEffects($player, $opponent, $effect, $player_effect_display, $effect_win);
				}
			}
			if(!empty($jutsu_cooldowns)) {
				foreach($jutsu_cooldowns as $id=>$cooldown) {
					$jutsu_cooldowns[$id]--;
					if($jutsu_cooldowns[$id] == 0) {
						unset($jutsu_cooldowns[$id]);
					}
				}
			}
			// Log jutsu
			if($jutsu_ok) {
				if(isset($player_jutsu_used[$jutsu_unique_id])) {
					$player_jutsu_used[$jutsu_unique_id]['count']++;
				}
				else {
					$player_jutsu_used[$jutsu_unique_id] = array();
					$player_jutsu_used[$jutsu_unique_id]['jutsu_type'] = $player_jutsu['jutsu_type'];
					$player_jutsu_used[$jutsu_unique_id]['count'] = 1;
				}
				$_SESSION['player_jutsu_used'] = $player_jutsu_used;
			}
			// Run jutsu
			if($jutsu_ok) {	
				// Calc player jutsu
				if($purchase_type == 'default') {		
					$jutsu = $default_attacks[$attack_id];
					$player_attack_type = $default_attacks[$attack_id]['jutsu_type'];
					$player_damage = $player->calcDamage($jutsu, 'default_jutsu');
					$jutsu_unique_id = 'J:' . $jutsu['jutsu_id'] . ':' . $player->id;
				}
				else if($purchase_type == 'bloodline') {
					$jutsu = $player->bloodline->jutsu[$attack_id];
					$player_attack_type = $player->bloodline->jutsu[$attack_id]['jutsu_type'];
					$player_damage = $player->calcDamage($player->bloodline->jutsu[$attack_id], 'bloodline_jutsu');
					$jutsu_unique_id = 'BL_J:' . $jutsu['jutsu_id'] . ':' . $player->id;
				}
				else {
					$jutsu = $player->jutsu[$attack_id];
					$player_attack_type = $player->jutsu[$attack_id]['jutsu_type'];
					$player_damage = $player->calcDamage($player->jutsu[$attack_id], 'equipped_jutsu');
					$jutsu_unique_id = 'J:' . $jutsu['jutsu_id'] . ':' . $player->id;
				}
				// Set weapon into jutsu data
				if($jutsu['jutsu_type'] == 'taijutsu' && $purchase_type != 'bloodline') {
					$weapon_id = (int)$_POST['weapon_id'];
					if($weapon_id && $player->checkInventory($weapon_id, 'item')) {
						if(array_search($weapon_id, $player->equipped_weapons) !== false) {					
							// Apply element to jutsu
							if($player->items[$weapon_id]['effect'] == 'element') {
								$jutsu['element'] = $player->elements['first'];
								$player_damage *= 1 + ($player->items[$weapon_id]['effect_amount'] / 100);
							}
							// Set effect in jutsu
							else {
								$jutsu['weapon_id'] = $weapon_id;
								$jutsu['weapon_effect'] = array(
									'power' => $jutsu['power'],
									'effect' => $player->items[$weapon_id]['effect'],
									'effect_length' => 2,
									'effect_amount' => $player->items[$weapon_id]['effect_amount'],
									'jutsu_type' => 'taijutsu'
								);
							}
						}
					}
				}
				
				// Buff jutsu (-1 = self effect move)
				if($jutsu['use_type'] == 'buff') {
					$jutsu['weapon_id'] = 0;
					$battle['player1_action'] == -1;
					$jutsu['effect_only'] = true;
				}
				
				// Barrier jutsu
				if($jutsu['use_type'] == 'barrier') {
					$jutsu['weapon_id'] = 0;
					$battle['player1_action'] == -1;
					$jutsu['effect_only'] = true;
				}
				
				// Weapon effect
				if(!empty($jutsu['weapon_id'])) {
					$effect_id = $player->id . ':W' . $jutsu['weapon_id'];
					setEffect($player, $opponent->id, $jutsu['weapon_effect'], $player_damage, $effect_id, $active_effects);
				}
				
				// Calc opponent jutsu
				$opponent_jutsu = $opponent->chooseMove();
				$opponent_damage = $opponent->calcDamage($opponent->current_move, 'equipped_jutsu');
				
				// Set cooldowns
				if($jutsu['cooldown'] > 0) {
					$jutsu_cooldowns[$jutsu_unique_id] = $jutsu['cooldown'];
				}
				
				// add opponent jutsu cooldown
				if($system->debug['battle']) {
					echo "PD: $player_damage<br />";
				}
				
				// Jutsu collision			
				$collision_text = '';				
				if(empty($jutsu['effect_only']) or $jutsu['use_type'] == 'barrier') {
					$collision_text = jutsuCollision($player, $opponent, $player_damage, $opponent_damage, $jutsu, $opponent->current_move);
				}
				
				// Set remaining barrier amounts
				if(isset($active_effects[$player->id . ':BARRIER'])) {
					if($player->barrier) {
						$active_effects[$player->id . ':BARRIER']['effect_amount'] = $player->barrier;
					}
					else {
						unset($active_effects[$player->id . ':BARRIER']);
					}
				}
				else if($player_jutsu['use_type'] == 'barrier' && $player->barrier) {
					$effect_id = $player->id . ':BARRIER';
					$barrier_jutsu = $player_jutsu;
					$barrier_jutsu['effect'] = 'barrier';
					$barrier_jutsu['effect_length'] = 1;
					setEffect($player, $player->id, $barrier_jutsu, $player->barrier, $effect_id, $active_effects);
				}
				if($system->debug['battle']) {
					echo "PD: $player_damage<br />";
				}
				
				// Set player jutsu effects 
				if($jutsu['jutsu_type'] == 'genjutsu' && empty($jutsu['effect_only'])) {
					$genjutsu_id = $player->id . ':J' . $jutsu['jutsu_id'];
					// Bloodline jutsu ID override
					if($purchase_type == 'bloodline') {
						$genjutsu_id = $player->id . ':BL_J' . $jutsu['jutsu_id'];
					}
					
					if($jutsu['effect'] == 'release_genjutsu') {
						$intelligence = ($player->intelligence + $player->intelligence_boost - $player->intelligence_nerf);
						if($intelligence <= 0) {
							$intelligence = 1;
						}
						$release_power = $intelligence * $jutsu['power'];
						foreach($active_genjutsu as $id => $genjutsu) {
							if($genjutsu['target'] == $player->id) {
								$r_power = $release_power * mt_rand(9, 11);
								$g_power = $genjutsu['power'] * mt_rand(9, 11);
								if($r_power > $g_power) {
									unset($active_genjutsu[$id]);
									$player_effect_display .= '[br][player] broke free from [opponent]\'s Genjutsu!';
								}
							}
						}
					}
					else {
						setEffect($player, $opponent->id, $jutsu, $player_damage, $genjutsu_id, $active_genjutsu);
					}
				}
				else if($jutsu['effect'] && $jutsu['effect'] != 'none') {
					$effect_id = $player->id . ':J' . $jutsu['jutsu_id'];
					// Bloodline jutsu ID override
					if($purchase_type == 'bloodline') {
						$effect_id = $player->id . ':BL_J' . $jutsu['jutsu_id'];
					}
					$target_id = $opponent->id;

					if($jutsu['use_type'] == 'buff' or $jutsu['use_type'] == 'barrier' or ($jutsu['use_type'] == 'projectile' && strpos($jutsu['effect'], '_boost')) ) {
						$target_id = $player->id;
					}

					setEffect($player, $target_id, $jutsu, $player_damage, $effect_id, $active_effects);
				}
				
				// Set opponent jutsu effects
				if($opponent_jutsu['jutsu_type'] == 'genjutsu') {
					$genjutsu_id = $opponent->id . ':J' . $opponent_jutsu['jutsu_id'];
					if($purchase_type == 'bloodline') {
						$genjutsu_id = $opponent->id . ':BL_J' . $opponent_jutsu['jutsu_id'];
					}
						
					if($jutsu['effect'] == 'release_genjutsu') {
						$intelligence = ($opponent->intelligence + $opponent->intelligence_boost - $opponent->intelligence_nerf);
						if($intelligence <= 0) {
							$intelligence = 1;
						}
						$release_power = $intelligence * $jutsu['power'];
						foreach($active_genjutsu as $id => $genjutsu) {
							if($genjutsu['target'] == $opponent->id) {
								$r_power = $release_power * mt_rand(9, 11);
								$g_power = $genjutsu['power'] * mt_rand(9, 11);
								if($r_power > $g_power) {
									unset($active_genjutsu[$id]);
									$player_effect_display .= '[br][player] broke free from [opponent]\'s Genjutsu!';
								}
							}
						}
					}
					else {
						setEffect($opponent, $player->id, $opponent_jutsu, $opponent_damage, $genjutsu_id, $active_genjutsu);
					}
				
				}
				else if($opponent_jutsu['effect'] && $opponent_jutsu['effect'] != 'none') {
					$effect_id = $opponent->id . ':J' . $opponent_jutsu['jutsu_id'];
					// Bloodline jutsu ID override
					if($purchase_type == 'bloodline') {
						$effect_id = $opponent->id . ':BL_J' . $opponent_jutsu['jutsu_id'];
					}
					$target_id = $player->id;

					if($opponent_jutsu['use_type'] == 'buff' or $opponent_jutsu['use_type'] == 'barrier' or 
					($opponent_jutsu['use_type'] == 'projectile' && strpos($opponent_jutsu['effect'], '_boost')) ) {
						$target_id = $opponent->id;
					}

					setEffect($opponent, $target_id, $opponent_jutsu, $opponent_damage, $effect_id, $active_effects);
				}
				
				// Apply player damage to opponent
				if($jutsu['jutsu_type'] != 'genjutsu' && !$jutsu['effect_only']) {
					$player_damage = $opponent->calcDamageTaken($player_damage, $attack_type);
					$opponent->health -= $player_damage;
					if($opponent->health <= 0) {
						$opponent->health = 0;
					}
				}
				
				// Apply opponent damage to player
				if($opponent_jutsu['jutsu_type'] != 'genjutsu') {
					$opponent_damage = $player->calcDamageTaken($opponent_damage, $opponent_jutsu['jutsu_type']);
					$player->health -= $opponent_damage;
					if($player->health <= 0) {
						$player->health = 0;
					}
				}
				// Set display text
				$battle_text .= $jutsu['battle_text'];
				if($jutsu['jutsu_type'] != 'genjutsu' && !$jutsu['effect_only']) {
					$battle_text .= " {$player->user_name} does {$player_damage} damage to {$opponent->name}.<br />";
				}
				if($player_effect_display) {
					$battle_text .= $player_effect_display;
				}
				if($collision_text) {
					$battle_text .= '<hr />' . $collision_text;
				}
				$battle_text .= "<hr />" . $opponent->name . ' ' . $opponent_jutsu['battle_text'];
				if($opponent_jutsu['jutsu_type'] != 'genjutsu') {
					$battle_text .= " {$opponent->name} does {$opponent_damage} damage to {$player->user_name}.<br />";
				}
				if($opponent_effect_display) {
					$battle_text .= $opponent_effect_display;
				}
			}
			else {
				// Calc opponent jutsu
				$opponent_damage = $opponent->calcDamage($opponent->moves[0], 'equipped_jutsu');
				// Set opponent jutsu effects
				$opponent_jutsu =& $opponent->moves[0];
				if($opponent_jutsu['jutsu_type'] == 'genjutsu') {
					$genjutsu_id = 'AI_J' . $opponent_jutsu['jutsu_id'];
					$genjutsu_user = 'A' . $opponent->ai_id;
					$genjutsu_target = 'P' . $player->user_id;
					// AI effect override
					$opponent_jutsu['effect'] = 'residual_damage';
					$opponent_jutsu['effect_amount'] = 50;
					$opponent_jutsu['effect_length'] = 2;
					if($jutsu['effect'] == 'residual_damage') {
						$jutsu['effect_amount'] = round($opponent_damage * ($opponent_jutsu['effect_amount'] / 100), 2);
					}
					$active_genjutsu[$genjutsu_id] = array(
						'user' => $genjutsu_user,
						'target' => $genjutsu_target,
						'turns' => $opponent_jutsu['effect_length'],
						'power' => $opponent->intelligence * $opponent_jutsu['power'],
						'effect' => $opponent_jutsu['effect'],
						'effect_amount' => $opponent_jutsu['effect_amount'],
						'effect_type' => $opponent_jutsu['jutsu_type']
					);
				}
				// Apply opponent damage to player
				if($opponent_jutsu['jutsu_type'] != 'genjutsu') {
					$opponent_damage = $player->calcDamageTaken($opponent_damage, $opponent_jutsu['jutsu_type']);
					$player->health -= $opponent_damage;
					if($player->health <= 0) {
						$player->health = 0;
					}
				}
				$battle_text .= "{$player->user_name} attempts to perform a jutsu, but it fails.<br />";
				if($player_effect_display) {
					$battle_text .= $player_effect_display;
				}
				$battle_text .= "<hr />" . $opponent->name . ' ' . $opponent_jutsu['battle_text'];
				if($opponent_jutsu['jutsu_type'] != 'genjutsu') {
					$battle_text .= " {$opponent->name} does {$opponent_damage} damage to {$player->user_name}.<br />";
				}
				if($opponent_effect_display) {
					$battle_text .= $opponent_effect_display;
				}
				// Set display text
			}
			$battle_text = str_replace('[br]', '<br />', $battle_text);
			$_SESSION['active_effects'] = $active_effects;
			$_SESSION['active_genjutsu'] = $active_genjutsu;
			$_SESSION['jutsu_cooldowns'] = $jutsu_cooldowns;
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		
		$system->printMessage();
		// Check for and set winner if either health below 0 (-1 signifies a tie)
		
		// Player lost
		if($player->health <= 0 && $opponent->health > 0) {
			$winner = 2;
		}	
		
		// Player won
		else if($player->health > 0 && $opponent->health <= 0) {
			$winner = 1;
		}
		
		// Tie
		else if($player->health <= 0 && $opponent->health <= 0) {
			$winner = -1;
		}		
	}
	
	// Update player's inventory
	$player->updateInventory();
	$opponent->updateData();
	
	// Sub-menu
	echo "<div class='submenu'>
	<ul class='submenu'>
		<li style='width:100%;'><a href='$self_link'>Refresh Battle</a></li>
	</ul>
	</div>
	<div class='submenuMargin'></div>";
	$system->printMessage();
	// Display
	echo "<table class='table'>
		<tr>
			<th style='width:50%;'>{$player->user_name}</th>
			<th style='width:50%;'>{$opponent->name}</th>
		</tr>";
	$health_percent = round(($player->health / $player->max_health) * 100);
	$chakra_percent = round(($player->chakra / $player->max_chakra) * 100);
	$stamina_percent = round(($player->stamina / $player->max_stamina) * 100);
	$avatar_size = '125px';
	if($player->forbidden_seal) {
		$avatar_size = '175px';
	}
	echo "<td>
	<img src='{$player->avatar_link}' style='display:block;max-width:$avatar_size;max-height:$avatar_size;margin:auto;' />
	<label style='width:7em;'>Health:</label>" . 
		sprintf("%.2f", $player->health) . '/' . sprintf("%.2f", $player->max_health) . "<br />" .
		"<div style='height:6px;width:250px;border-style:solid;border-width:1px;'>" .
		"<div style='background-color:#C00000;height:6px;width:" . $health_percent. "%;' /></div>" . "</div>" .
	"<label style='width:7em;'>Chakra:</label>" . 
		sprintf("%.2f", $player->chakra) . '/' . sprintf("%.2f", $player->max_chakra) . "<br />" .
		"<div style='height:6px;width:250px;border-style:solid;border-width:1px;'>" .
		"<div style='background-color:#0000B0;height:6px;width:" . $chakra_percent . "%;' /></div>" . "</div>" .
	"<label style='width:7em;'>Stamina:</label>" . 
		sprintf("%.2f", $player->stamina) . '/' . sprintf("%.2f", $player->max_stamina) . "<br />" .
		"<div style='height:6px;width:250px;border-style:solid;border-width:1px;'>" .
		"<div style='background-color:#00B000;height:6px;width:" . $stamina_percent . "%;' /></div>" . "</div>" .
	"</td>
	<td>";
	$opponent_health_percent = round(($opponent->health / $opponent->max_health) * 100);
	echo "
	<div style='width:140px;height:150px;margin:auto:'></div>
	<label style='width:6em;'>Health:</label>" . 
		sprintf("%.2f", $opponent->health) . '/' . sprintf("%.2f", $opponent->max_health) . "<br />" .
		"<div style='height:6px;width:250px;border-style:solid;border-width:1px;'>" .
		"<div style='background-color:#C00000;height:6px;width:" . $opponent_health_percent . "%;' /></div>" . "</div>";
	echo "</td></tr>";

	// Move prompt
    // Move prompt if no winner
    if(!$winner) {
        movePrompt($player, $default_attacks);
    }

	// Battle text
	$search_array = array("[player]", "[opponent]", 
		"[gender]", "[gender2]");
	$replace_array = array($player->user_name, $opponent->name, 
		($player->gender == 'Male' ? 'he' : 'she'), ($player->gender == 'Male' ? 'his' : 'her'));
	if($battle_text) {
		echo "<tr><td colspan='2'>" .
		stripslashes(str_replace($search_array, $replace_array, $battle_text)) .
		"</td></tr>";
	}

	echo "</table>";
	if($winner) {
		unset($_SESSION['active_effects']);
		unset($_SESSION['active_genjutsu']);
		unset($_SESSION['jutsu_cooldowns']);
		unset($_SESSION['ai_logic']);
		return $winner;
	}
	// Display
	return false;
}

function battlePvP(&$player, &$opponent, &$battle) {
	require("variables.php");
	global $system;
	/*
	five chakra natures
	Jutsu clash – elemental factors
	DATA STRUCTURE	
		player1
		player2
		player1_action (bool)
		player2_action (bool)
		player1_active_element
		player2_active_element
		player1_raw_damage
		player2_raw_damage
		player1_battle_text
		player2_battle_text
		turn_time
		winner (0 or player ID, -1 for tie)
	Two players
	Keep turn timer
	Both users must submit move by end of turn
	Moves happen same time
	Moves clash - damage comparison with advantage slanted towards elemental advantages
	First person to load page calculates damages dealt
	if both users have submitted move(player1_action and player2_action)
		run damage calcs, jutsu clash, blah blah blah
	else
	if both users have not submitted move (check player1_action and player2_action)
	-prompt user for turn or send message ("Please wait for other user")
	if player has not submitted move
		prompt for it
	*/
	$battle_text = '';

	// Create p1 and p2 references
	if($player->user_id == $battle['player1']) {
		$player1 =& $player;
		$player2 =& $opponent;
	}
	else if($player->user_id == $battle['player2']) {
		$player2 =& $player;
		$player1 =& $opponent;
	}
	$player1->getInventory();
	$player2->getInventory();

	// Apply passive effects
	$effect_target = null;
	$effect_user = null;
	$player1_effect_display = '';
	$player2_effect_display = '';
	$battle['active_effects'] = json_decode($battle['active_effects'], true);
	$battle['active_genjutsu'] = json_decode($battle['active_genjutsu'], true);

	// Jutsu passive effects
	if(is_array($battle['active_effects'])) {
		foreach($battle['active_effects'] as $id => $effect) {
			if($player->staff_level >= $SC_ADMINISTRATOR) {
				echo "[$id] " . $effect['effect'] . '(' . $effect['effect_amount'] . ') ->' . 
					$effect['target'] . '(' . $effect['turns'] . ' turns left)<br />';
			}
			if($effect['target'] == $player1->id) {
				$effect_target =& $player1;
			}
			else {
				$effect_target =& $player2;
			}
			if($effect['user'] == $player1->id) {
				$effect_user =& $player1;
			}
			else {
				$effect_user =& $player2;
			}
			applyPassiveEffects($effect_target, $effect_user, $effect);
		}
		unset($effect_target);
		unset($effect_user);
	}
	else {
		$battle['active_effects'] = array();
	}

	// Apply genjutsu passive effects
	if(is_array($battle['active_genjutsu'])) {
		foreach($battle['active_genjutsu'] as $id => $genjutsu) {
			if($genjutsu['target'] == $player1->id) {
				$effect_target =& $player1;
			}
			else {
				$effect_target =& $player2;
			}
			if($genjutsu['user'] == $player1->id) {
				$effect_user =& $player1;
			}
			else {
				$effect_user =& $player2;
			}
			applyPassiveEffects($effect_target, $effect_user, $effect);
		}
	}
	else {
		$battle['active_genjutsu'] = array();
	}

	// Apply item passive effects
	if(!empty($player1->equipped_armor)) {
		foreach($player1->equipped_armor as $item_id) {
			if($player1->checkInventory($item_id, 'item')) {
				$effect = array(
					'effect' => $player1->items[$item_id]['effect'],
					'effect_amount' => $player1->items[$item_id]['effect_amount']
				);
				applyPassiveEffects($player1, $player2, $effect, $player1_effect_display);
			}
		}
	}
	if(!empty($player2->equipped_armor)) {
		foreach($player2->equipped_armor as $item_id) {
			if($player2->checkInventory($item_id, 'item')) {
				$effect = array(
					'effect' => $player2->items[$item_id]['effect'],
					'effect_amount' => $player2->items[$item_id]['effect_amount']
				);
				applyPassiveEffects($player2, $player1, $effect, $player2_effect_display);
			}
		}
	}

	// Apply bloodline passive effects (REMOVED - Moved to classes.php)
	// Load cooldowns
	if(!empty($battle['jutsu_cooldowns'])) {
		$battle['jutsu_cooldowns'] = json_decode($battle['jutsu_cooldowns'], true);
	}
	else {
		$battle['jutsu_cooldowns'] = array();
	}

	// Load jutsu used logs
	if(!empty($battle['player1_jutsu_used'])) {
		$battle['player1_jutsu_used'] = json_decode($battle['player1_jutsu_used'], true);
	}
	else {
		$battle['player1_jutsu_used'] = array();
	}
	if(!empty($battle['player2_jutsu_used'])) {
		$battle['player2_jutsu_used'] = json_decode($battle['player2_jutsu_used'], true);
	}
	else {
		$battle['player2_jutsu_used'] = array();
	}

	// Load default attacks
    $default_attacks = array();
    $query = "SELECT * FROM `jutsu` WHERE `purchase_type`='1'";
    $result = $system->query($query);
    while($row = $system->db_fetch($result)) {
        $default_attacks[$row['jutsu_id']] = $row;
    }
    $turn_length = 60;

	// If turn is still active and user hasn't submitted their move, check for action
	if((time() - $battle['turn_time'] < $turn_length) && (!$battle[$battle['player_side'] . '_action'])) {
		if($_POST['attack']) {
			// Run player attack
			/* notes: Handseal-based jutsu can uniquely fail, triggering a failed_jutsu attack type */
			try {		
				$jutsu_type = $_POST['jutsu_type'];

				// Check for handseals if ninjutsu/genjutsu
				if($jutsu_type == 'ninjutsu' or $jutsu_type == 'genjutsu') {
					if(!$_POST['hand_seals']) {
						throw new Exception("Please enter hand seals!");
					}
					if(is_array($_POST['hand_seals'])) {
						$seals = array();
						foreach($_POST['seals'] as $seal) {
							if(!is_numeric($seal)) {
								break;
							}
							$seals[] = $seal;
						}
						$seal_string = implode('-', $seals);
					}
					else {
						$raw_seals = explode('-', $_POST['hand_seals']);
						$seals = array();
						foreach($raw_seals as $seal) {
							if(!is_numeric($seal)) {
								break;
							}
							$seals[] = $seal;
						}
						$seal_string = implode('-', $seals);
					}
					$jutsu_ok = false;
					$jutsu_id = 0;
					foreach($default_attacks as $id => $attack) {
						if($attack['hand_seals'] == $seal_string) {
							$jutsu_ok = true;
							$attack_id = $id;
							$purchase_type = 'default';
							$player_jutsu = $attack;
							break;
						}
					}
					foreach($player->jutsu as $id => $jutsu) {
						if($jutsu['hand_seals'] == $seal_string) {
							$jutsu_ok = true;
							$attack_id = $id;
							$purchase_type = 'equipped';
							$player_jutsu = $jutsu;
							break;
						}
					}
					$jutsu_unique_id = 'J:' . $attack_id . ':' . $player->id;
					// Layered genjutsu check
					if($jutsu_ok && $jutsu_type == 'genjutsu' && !empty($player_jutsu['parent_jutsu'])) {
						$parent_genjutsu_id = $player->id . ':J' . $player_jutsu['parent_jutsu'];
						$parent_jutsu = $player->jutsu[$player_jutsu['parent_jutsu']];
						if(!isset($battle['active_genjutsu'][$parent_genjutsu_id]) or 
						$battle['active_genjutsu'][$parent_genjutsu_id]['turns'] == $parent_jutsu['effect_length']) {
							throw new Exception($parent_jutsu['name'] . 
								' must be active for 1 turn before using this jutsu!'
							);
						}
					}		
				}

				// Check jutsu ID if taijutsu
				else if($jutsu_type == 'taijutsu') {
					$jutsu_ok = false;
					$jutsu_id = (int)$_POST['jutsu_id'];
					if(isset($default_attacks[$jutsu_id]) && $default_attacks[$jutsu_id]['jutsu_type'] == 'taijutsu') {
						$jutsu_ok = true;
						$attack_id = $jutsu_id;
						$purchase_type = 'default';
						$player_jutsu = $default_attacks[$jutsu_id];
					}
					if(isset($player->jutsu[$jutsu_id]) && $player->jutsu[$jutsu_id]['jutsu_type'] == 'taijutsu') {
						$jutsu_ok = true;
						$attack_id = $jutsu_id;
						$purchase_type = 'equipped';
						$player_jutsu = $player->jutsu[$jutsu_id];
					}
					$jutsu_unique_id = 'J:' . $attack_id . ':' . $player->id;		
				}

				// Check BL jutsu ID if bloodline jutsu
				else if($jutsu_type == 'bloodline_jutsu' && $player->bloodline_id) {
					$jutsu_ok = false;
					$jutsu_id = (int)$_POST['jutsu_id'];
					if(isset($player->bloodline->jutsu[$jutsu_id])) {
						$jutsu_ok = true;
						$attack_id = $jutsu_id;
						$purchase_type = 'bloodline';
						$player_jutsu = $player->bloodline->jutsu[$jutsu_id];
					}
					$jutsu_unique_id = 'BL_J:' . $attack_id . ':' . $player->id;
				}
				else {
					throw new Exception("Invalid jutsu selection!");
				}

				// Check jutsu cooldown
				if($jutsu_ok && isset($battle['jutsu_cooldowns'][$jutsu_unique_id])) {
					throw new Exception("Cannot use that jutsu, it is on cooldown for " . $battle['jutsu_cooldowns'][$jutsu_unique_id] . " more turns!");
				}
				if(!$jutsu_ok) {
					throw new Exception("Invalid jutsu!");
				}
				if(!$player->useJutsu($player_jutsu, $purchase_type . '_jutsu')) {
					throw new Exception($system->message);
				}	

				// Check for weapon if non-BL taijutsu
				$weapon_id = 0;
				if($jutsu_type == 'taijutsu' && $purchase_type != 'bloodline' && $_POST['weapon_id']) {
					$weapon_id = (int)$system->clean($_POST['weapon_id']);
					if($weapon_id && $player->checkInventory($weapon_id, 'item')) {
						if(array_search($weapon_id, $player->equipped_weapons) === false) {
							$weapon_id = 0;
						}
					}
					else {
						$weapon_id = 0;
					}
				}
				if($purchase_type == 'default') {
					if(!isset($default_attacks[$attack_id])) {
						throw new Exception("Invalid attack!");
					}
					$attack_type = 'default_jutsu';
				}
				else if($purchase_type == 'bloodline') {
					$attack_type = 'bloodline_jutsu';
				}
				else if(!$jutsu_ok) {
					$attack_id = 0;
					$attack_type = 'failed_jutsu';
				}
				else {
					$attack_type = 'equipped_jutsu';
				}

				// Log jutsu used
				if($jutsu_ok) {
					if($attack_type == 'default' or $attack_type == 'equipped_jutsu') {
					}
					else if($attack_type == 'bloodline_jutsu') {
					}
				}
				$battle[$battle['player_side'] . '_action'] = 1;
				$battle[$battle['player_side'] . '_jutsu_id'] = $attack_id;
				$battle[$battle['player_side'] . '_weapon_id'] = $weapon_id;
				$battle[$battle['player_side'] . '_attack_type'] = $attack_type;
				$system->query("UPDATE `battles` SET
					`{$battle['player_side']}_action` = 1,
					`{$battle['player_side']}_jutsu_id` = " . $attack_id. ",
					`{$battle['player_side']}_weapon_id` = " . $weapon_id. ",
					`{$battle['player_side']}_attack_type` = '$attack_type'
					WHERE `battle_id` = '{$battle['battle_id']}' LIMIT 1");
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
		}
	}

	// If time is up or both people have submitted moves, RUN TURN
	if((time() - $battle['turn_time'] > $turn_length) || ($battle['player1_action'] && $battle['player2_action'])) {
		if($battle['player1_action'] or $battle['player2_action']) {	
			$effect_win = false;

			// Run turn effects
			$effect_display = '';
			if(!empty($battle['active_effects'])) {
				foreach($battle['active_effects'] as $id => $effect) {
					if($effect['target'] == $player1->id) {
						$effect_target =& $player1;
						$effect_display =& $player1_effect_display;
					}
					elseif($effect['target'] == $player2->id) {
						$effect_target =& $player2;
						$effect_display =& $player2_effect_display;
					}
					if($effect['user'] == $player1->id) {
						$effect_user =& $player1;
					}
					else if($effect['user'] == $player2->id) {
						$effect_user =& $player2;
					}
					applyActiveEffects($effect_target, $effect_user, $effect, $effect_display, $effect_win);

                    $battle['active_effects'][$id]['turns']--;
					if($battle['active_effects'][$id]['turns'] <= 0) {
						unset($battle['active_effects'][$id]);
					}
				}
			}
			if(!empty($battle['active_genjutsu'])) {
				foreach($battle['active_genjutsu'] as $id => $genjutsu) {
					if($genjutsu['target'] == $player1->id) {
						$effect_target =& $player1;
						$effect_display =& $player1_effect_display;
					}
					else {
						$effect_target =& $player2;
						$effect_display =& $player2_effect_display;
					}
					if($genjutsu['user'] == $player1->id) {
						$effect_user =& $player1;
					}
					else {
						$effect_user =& $player2;
					}
					applyActiveEffects($effect_target, $effect_user, $genjutsu, $effect_display, $effect_win);
					$battle['active_genjutsu'][$id]['turns']--;
					$battle['active_genjutsu'][$id]['power'] *= 0.9;
					if($battle['active_genjutsu'][$id]['turns'] <= 0) {
						unset($battle['active_genjutsu'][$id]);
					}
					if(isset($genjutsu['first_turn'])) {
						unset($genjutsu['first_turn']);
					}
				}
			}

			// Bloodline active effects
			if(!empty($player1->bloodline->combat_boosts)) {
				foreach($player1->bloodline->combat_boosts as $id=>$effect) {
					applyActiveEffects($player1, $player2, $effect, $player1_effect_display, $effect_win);
				}
			}
			if(!empty($player2->bloodline->combat_boosts)) {
				foreach($player2->bloodline->combat_boosts as $id=>$effect) {
					applyActiveEffects($player2, $player1, $effect, $player2_effect_display, $effect_win);
				}
			}

			// Decrement cooldowns
			if(!empty($battle['jutsu_cooldowns'])) {
				foreach($battle['jutsu_cooldowns'] as $id=>$cooldown) {
					$battle['jutsu_cooldowns'][$id]--;
					if($battle['jutsu_cooldowns'][$id] == 0) {
						unset($battle['jutsu_cooldowns'][$id]);
					}
				}
			}

			// Check for each player's action
			$battle['battle_text'] = '';

            // Calculate damage
			$player1_damage = 0;
			$player2_damage = 0;
			$player1_battle_text = '';
			$player2_battle_text = '';
			if($battle['player1_action']) {
				if($battle['player1_attack_type'] == 'default_jutsu') {		
					$player1_jutsu = $default_attacks[$battle['player1_jutsu_id']];
					$player1_damage = $player1->calcDamage($player1_jutsu, 'default_jutsu');
					$player1_jutsu['unique_id'] = 'J:' . $player1_jutsu['jutsu_id'] . ':' . $player1->id;
				}
				else if($battle['player1_attack_type'] == 'equipped_jutsu') {
					$player1_jutsu = $player1->jutsu[$battle['player1_jutsu_id']];
					$player1_damage = $player1->calcDamage($player1_jutsu, 'equipped_jutsu');
					$player1_jutsu['unique_id'] = 'J:' . $player1_jutsu['jutsu_id'] . ':' . $player1->id;
				}
				else if($battle['player1_attack_type'] == 'bloodline_jutsu') {
					$player1_jutsu = $player1->bloodline->jutsu[$battle['player1_jutsu_id']];		
					$player1_damage = $player1->calcDamage($player1->bloodline->jutsu[$battle['player1_jutsu_id']], 'bloodline_jutsu');
					$player1_jutsu['unique_id'] = 'BL_J:' . $player1_jutsu['jutsu_id'] . ':' . $player1->id;
				}
				else if($battle['player1_attack_type'] == 'failed_jutsu') {
					$battle['player1_action'] = false;
				}
				// Set weapon data into jutsu
				if(($battle['player1_attack_type'] == 'default_jutsu' or $battle['player1_attack_type'] == 'equipped_jutsu')
				&& $player1_jutsu['jutsu_type'] == 'taijutsu' && $battle['player1_weapon_id']) {
					// Apply element to jutsu
					if($player1->items[$battle['player1_weapon_id']]['effect'] == 'element') {
						$player1_jutsu['element'] = $player1->elements['first'];
						$player1_damage *= 1 + ($player1->items[$battle['player1_weapon_id']]['effect_amount'] / 100);
					}
					// Set effect in jutsu
					else {
						$player1_jutsu['weapon_id'] = $battle['player1_weapon_id'];
						$player1_jutsu['weapon_effect'] = array(
							'power' => $player1_jutsu['power'],
							'effect' => $player1->items[$battle['player1_weapon_id']]['effect'],
							'effect_length' => 2,
							'effect_amount' => $player1->items[$battle['player1_weapon_id']]['effect_amount'],
							'jutsu_type' => 'taijutsu'
						);
					}
				}
			}
			if($battle['player2_action']) {
				if($battle['player2_attack_type'] == 'default_jutsu') {		
					$player2_jutsu = $default_attacks[$battle['player2_jutsu_id']];
					$player2_damage = $player2->calcDamage($player2_jutsu, 'default_jutsu');
					$player2_jutsu['unique_id'] = 'J:' . $player2_jutsu['jutsu_id'] . ':' . $player2->id;
				}
				else if($battle['player2_attack_type'] == 'equipped_jutsu') {
					$player2_jutsu = $player2->jutsu[$battle['player2_jutsu_id']];
					$player2_damage = $player2->calcDamage($player2_jutsu, 'equipped_jutsu');
					$player2_jutsu['unique_id'] = 'J:' . $player2_jutsu['jutsu_id'] . ':' . $player2->id;
				}
				else if($battle['player2_attack_type'] == 'bloodline_jutsu') {
					$player2_jutsu = $player2->bloodline->jutsu[$battle['player2_jutsu_id']];		
					$player2_damage = $player2->calcDamage($player2->bloodline->jutsu[$battle['player2_jutsu_id']], 'bloodline_jutsu');
					$player2_jutsu['unique_id'] = 'BL_J:' . $player2_jutsu['jutsu_id'] . ':' . $player2->id;
				}
				else if($battle['player2_attack_type'] == 'failed_jutsu') {
					$battle['player2_action'] = false;
				}
				// Set weapon data into jutsu
				if(($battle['player2_attack_type'] == 'default_jutsu' or $battle['player2_attack_type'] == 'equipped_jutsu')
				&& $player2_jutsu['jutsu_type'] == 'taijutsu' && $battle['player2_weapon_id']) {
					// Apply element to jutsu
					if($player2->items[$battle['player2_weapon_id']]['effect'] == 'element') {
						$player2_jutsu['element'] = $player2->elements['first'];
						$player2_damage *= 1 + ($player2->items[$battle['player2_weapon_id']]['effect_amount'] / 100);
					}
					// Set effect in jutsu
					else {
						$player2_jutsu['weapon_id'] = $battle['player2_weapon_id'];
						$player2_jutsu['weapon_effect'] = array(
							'power' => $player2_jutsu['power'],
							'effect' => $player2->items[$battle['player2_weapon_id']]['effect'],
							'effect_length' => 2,
							'effect_amount' => $player2->items[$battle['player2_weapon_id']]['effect_amount'],
							'jutsu_type' => 'taijutsu'
						);
					}
				}
			}

			// Buff jutsu (-1 = self effect move)
			if($player1_jutsu['use_type'] == 'buff') {
				$player1_jutsu['weapon_id'] = 0;
				$battle['player1_action'] == -1;
				$player1_jutsu['effect_only'] = true;
			}
			if($player2_jutsu['use_type'] == 'buff') {
				$player2_jutsu['weapon_id'] = 0;
				$battle['player2_action'] == -1;
				$player2_jutsu['effect_only'] = true;
			}

			// Barrier jutsu
			if($player1_jutsu['use_type'] == 'barrier') {
				$player1_jutsu['weapon_id'] = 0;
				$battle['player1_action'] == -1;
				$player1_jutsu['effect_only'] = true;
			}
			if($player2_jutsu['use_type'] == 'barrier') {
				$player2_jutsu['weapon_id'] = 0;
				$battle['player2_action'] == -1;
				$player2_jutsu['effect_only'] = true;
			}
			if($system->debug['battle']) {
				echo 'P1: ' . $player1_damage . ' / P2: ' . $player2_damage . '<br />';
			}

			// Collision
			if($battle['player1_action'] > 0 && $battle['player2_action'] > 0) {
				$collision_text = jutsuCollision($player1, $player2, $player1_damage, $player2_damage, $player1_jutsu, $player2_jutsu);
			}

			// Apply remaining barrier
			if(isset($battle['active_effects'][$player1->id . ':BARRIER'])) {
				if($player1->barrier) {
					$battle['active_effects'][$player1->id . ':BARRIER']['effect_amount'] = $player1->barrier;
				}
				else {
					unset($battle['active_effects'][$player1->id . ':BARRIER']);
				}
			}
			else if($player1_jutsu['use_type'] == 'barrier' && $player1->barrier) {
				$effect_id = $player1->id . ':BARRIER';
				$barrier_jutsu = $player1_jutsu;
				$barrier_jutsu['effect'] = 'barrier';
				$barrier_jutsu['effect_length'] = 1;
				setEffect($player1, $player1->id, $barrier_jutsu, $player1->barrier, $effect_id, $battle['active_effects']);
			}
			if(isset($battle['active_effects'][$player2->id . ':BARRIER'])) {
				if($player2->barrier) {
					$battle['active_effects'][$player2->id . ':BARRIER']['effect_amount'] = $player2->barrier;
				}
				else {
					unset($battle['active_effects'][$player2->id . ':BARRIER']);
				}
			}
			else if($player2_jutsu['use_type'] == 'barrier' && $player2->barrier) {
				$effect_id = $player2->id . ':BARRIER';
				$barrier_jutsu = $player2_jutsu;
				$barrier_jutsu['effect'] = 'barrier';
				$barrier_jutsu['effect_length'] = 1;
				setEffect($player2, $player2->id, $barrier_jutsu, $player2->barrier, $effect_id, $battle['active_effects']);
			}

			// Apply damage/effects and set display
			if($battle['player1_action']) {
				$player1_raw_damage = $player1_damage;
				if($player1_jutsu['jutsu_type'] != 'genjutsu' && empty($player1_jutsu['effect_only'])) {
					$player1_damage = $player2->calcDamageTaken($player1_damage, $player1_jutsu['jutsu_type']);
					$player2->health -= $player1_damage;
					if($player2->health < 0) {
						$player2->health = 0;
					}
				}

				// Weapon effect for taijutsu (IN PROGRESS)
				if($player1_jutsu['weapon_id']) {
					$effect_id = $player1->id . ':W' . $player1_jutsu['weapon_id'];
					if($player1->items[$battle['player1_weapon_id']]['effect'] != 'diffuse') {
						setEffect($player1, $player2->id, $player1_jutsu['weapon_effect'], 
							$player1_raw_damage, $effect_id, $battle['active_effects']);
					}
				}

				// Set cooldowns
				if($player1_jutsu['cooldown'] > 0) {
					$battle['jutsu_cooldowns'][$player1_jutsu['unique_id']] = $player1_jutsu['cooldown'];
				}

				// Genjutsu/effects
				if($player1_jutsu['jutsu_type'] == 'genjutsu' && $player1_jutsu['use_type'] != 'buff') {
					$genjutsu_id = $player1->id . ':J' . $player1_jutsu['jutsu_id'];
					// Bloodline jutsu ID override
					if($battle['player1_attack_type'] == 'bloodline_jutsu') {
						$genjutsu_id = $player1->id . ':BL_J' . $player1_jutsu['jutsu_id'];
					}

					if($player1_jutsu['effect'] == 'release_genjutsu') {
						$intelligence = ($player1->intelligence + $player1->intelligence_boost - $player1->intelligence_nerf);
						if($intelligence <= 0) {
							$intelligence = 1;
						}
						$release_power = $intelligence * $player1_jutsu['power'];
						foreach($battle['active_genjutsu'] as $id => $genjutsu) {
							if($genjutsu['target'] == $player1->id && !isset($genjutsu['first_turn'])) {
								$r_power = $release_power * mt_rand(9, 11);
								$g_power = $genjutsu['power'] * mt_rand(9, 11);
								if($r_power > $g_power) {
									unset($battle['active_genjutsu'][$id]);
									$player1_effect_display .= '[br][player] broke free from [opponent]\'s Genjutsu!';
								}
							}
						}
					}
					else  {
						setEffect($player1, $player2->id, $player1_jutsu, $player1_raw_damage, $genjutsu_id, $battle['active_genjutsu']);
					}
				}			
				else if($player1_jutsu['effect'] != 'none') {
					$effect_id = $player1->id . ':J' . $player1_jutsu['jutsu_id'];
					// Bloodline jutsu ID override
					if($battle['player1_attack_type'] == 'bloodline_jutsu') {
						$effect_id = $player->id . ':BL_J' . $player1_jutsu['jutsu_id'];
					}
					$target_id = $player2->id;
					if($player1_jutsu['use_type'] == 'buff' || ($player1_jutsu['use_type'] == 'projectile' && strpos($player1_jutsu['effect'], '_boost'))) {
						$target_id = $player1->id;
					}
					setEffect($player1, $target_id, $player1_jutsu, $player1_raw_damage, $effect_id, $battle['active_effects']);
				}			
				$text = $player1_jutsu['battle_text'];

                if($player1_jutsu['jutsu_type'] != 'genjutsu' && empty($player1_jutsu['effect_only'])) {
					$text .= '[br]-[player] does ' . sprintf("%.2f", $player1_damage) . ' damage to [opponent]-';
				}
				if($player1_effect_display) {
					$text .= $system->clean($player1_effect_display);
				}
				$battle['battle_text'] .= str_replace(
					array('[player]', '[opponent]', 
						'[gender]', '[gender2]'),
					array($player1->user_name, $player2->user_name,
						($player1->gender == 'Male' ? 'he' : 'she'), ($player1->gender == 'Male' ? 'his' : 'her')),
					$text);
			}
			else {
				// Failed jutsu or did nothing (display)
				if($battle['player1_attack_type'] == 'failed_jutsu') {
					$battle['battle_text'] .= $player1->user_name . ' attempted to perform a jutsu, but failed.';
				}
				else {
					$battle['battle_text'] .= $player1->user_name . ' stood still and did nothing.';
				}
				if($player1_effect_display) {
					$battle['battle_text'] .= str_replace(
						array('[player]', '[opponent]'),
						array($player1->user_name, $player2->user_name),
						$system->clean($player1_effect_display));
				}
			}
			if($collision_text) {
				$collision_text = str_replace(
					array('[player]', '[opponent]'), 
					array($player1->user_name, $player2->user_name), 
					$collision_text);
				$battle['battle_text'] .= '[br][hr]' . $system->clean($collision_text);
			}			
			$battle['battle_text'] .= '[br][hr]';

			// Apply damage/effects and set display
            if($battle['player2_action']) {
				$player2_raw_damage = $player2_damage;
				if($player2_jutsu['jutsu_type'] != 'genjutsu' && empty($player2_jutsu['effect_only'])) {
					$player2_damage = $player1->calcDamageTaken($player2_damage, $player2_jutsu['jutsu_type']);
					$player1->health -= $player2_damage;
					if($player1->health < 0) {
						$player1->health = 0;
					}
				}

				// Weapon effect for taijutsu (IN PROGRESS)
				if($player2_jutsu['weapon_id']) {
					$effect_id = $player2->id . ':W' . $player2_jutsu['weapon_id'];
					if($player1->items[$battle['player2_weapon_id']]['effect'] != 'diffuse') {
						setEffect($player2, $player1->id, $player2_jutus['weapon_effect'], 
							$player2_raw_damage, $effect_id, $battle['active_effects']);
					}
				}

				// Set cooldowns
				if($player2_jutsu['cooldown'] > 0) {
					$battle['jutsu_cooldowns'][$player2_jutsu['unique_id']] = $player2_jutsu['cooldown'];
				}

				// Genjutsu/effects
				if($player2_jutsu['jutsu_type'] == 'genjutsu' && $player2_jutsu['use_type'] != 'buff') {
					$genjutsu_id = $player2->id . ':J' . $player2_jutsu['jutsu_id'];
					// Bloodline jutsu ID override
					if($battle['player2_attack_type'] == 'bloodline_jutsu') {
						$genjutsu_id = $player2->id . ':BL_J' . $player2_jutsu['jutsu_id'];
					}

                    if($player2_jutsu['effect'] == 'release_genjutsu') {
						$intelligence = ($player2->intelligence + $player2->intelligence_boost - $player2->intelligence_nerf);
						if($intelligence <= 0) {
							$intelligence = 1;
						}
						$release_power = $intelligence * $player2_jutsu['power'];
						foreach($battle['active_genjutsu'] as $id => $genjutsu) {
							if($genjutsu['target'] == $player2->id && !isset($genjutsu['first_turn'])) {
								$r_power = $release_power * mt_rand(9, 11);
								$g_power = $genjutsu['power'] * mt_rand(9, 11);
								if($r_power > $g_power) {
									unset($battle['active_genjutsu'][$id]);
									$player1_effect_display .= '[br][player] broke free from [opponent]\'s Genjutsu!';
								}
							}
						}
					}
					else  {
						setEffect($player2, $player1->id, $player2_jutsu, $player2_raw_damage, $genjutsu_id, $battle['active_genjutsu']);
					}
				}			
				else if($player2_jutsu['effect'] != 'none') {
					$effect_id = $player2->id . ':J' . $player2_jutsu['jutsu_id'];
					// Bloodline jutsu ID override
					if($battle['player2_attack_type'] == 'bloodline_jutsu') {
						$effect_id = $player->id . ':BL_J' . $player2_jutsu['jutsu_id'];
					}
					$target_id = $player1->id;
					if($player2_jutsu['use_type'] == 'buff' || ($player2_jutsu['use_type'] == 'projectile' && strpos($player2_jutsu['effect'], '_boost'))) {
						$target_id = $player2->id;
					}
					setEffect($player2, $target_id, $player2_jutsu, $player2_raw_damage, $effect_id, $battle['active_effects']);
				}			

                $text = $player2_jutsu['battle_text'];
				if($player2_jutsu['jutsu_type'] != 'genjutsu' && empty($player2_jutsu['effect_only'])) {
					$text .= '[br]-[player] does ' . sprintf("%.2f", $player2_damage) . ' damage to [opponent]-';
				}
				if($player2_effect_display) {
					$text .= $system->clean($player2_effect_display);
				}
				$battle['battle_text'] .= str_replace(
					array('[player]', '[opponent]', 
						'[gender]', '[gender2]'),
					array($player2->user_name, $player1->user_name,
						($player2->gender == 'Male' ? 'he' : 'she'), ($player2->gender == 'Male' ? 'his' : 'her')),
					$text);
			}
			else {
				// Failed jutsu or did nothing (display)
				if($battle['player2_attack_type'] == 'failed_jutsu') {
					$battle['battle_text'] .= $player2->user_name . ' attempted to perform a jutsu, but failed.';
				}
				else {
					$battle['battle_text'] .= $player2->user_name . ' stood still and did nothing.';
				}
				if($player2_effect_display) {
					$battle['battle_text'] .= str_replace(
						array('[player]', '[opponent]'),
						array($player2->user_name, $player1->user_name),
						$system->clean($player2_effect_display));
				}
			}


            // Update battle
            $battle['turn_time'] = time();
			$battle['player1_action'] = 0;
			$battle['player2_action'] = 0;

            $query = "UPDATE `battles` SET
				`player1_action`='{$battle['player1_action']}',
				`player2_action`='{$battle['player2_action']}',
				`battle_text` = '{$battle['battle_text']}',
				`active_effects` = '" . json_encode($battle['active_effects']) . "',
				`active_genjutsu` = '" . json_encode($battle['active_genjutsu']) . "',
				`jutsu_cooldowns` = '" . json_encode($battle['jutsu_cooldowns']) . "',
				`turn_time`='{$battle['turn_time']}'
				WHERE `battle_id`='{$battle['battle_id']}' LIMIT 1";
			if($system->debug['battle'] && false) {
				echo $query . "<br /><br />";
			}
			$system->query($query);
			$player1->updateData();
			$player2->updateData();
		}
		// If neither player moved, update turn timer only
		else {
			$battle['turn_time'] = time();
			$system->query("UPDATE `battles` SET `turn_time`='{$battle['turn_time']}'
				WHERE `battle_id`='{$battle['battle_id']}' LIMIT 1");
		}
	}

	// Time is up - Player moved, opponent didn't
	// Time is up - Opponent moved, player didnt
	// Time is up - nobody moved
	else {
	}

	// Check for winner
	$winner = $battle['winner'];
	if(!$winner) {
		if($player->health <= 0 && $opponent->health > 0) {
			$winner = $opponent->user_id;
		}
		else if($opponent->health <= 0 && $player->health > 0) {
			$winner = $player->user_id;
		}
		else if($player->health <= 0 && $opponent->health <= 0) {
			$winner = -1;
		}
	}

	// Start display
	echo "<div class='submenu'>
	<ul class='submenu'>
		<li style='width:100%;'><a href='$self_link'>Refresh Battle</a></li>
	</ul>
	</div>
	<div class='submenuMargin'></div>";
	$system->printMessage();
	echo "<table class='table'>
		<tr>
			<th style='width:50%;'>{$player->user_name}</th>
			<th style='width:50%;'>{$opponent->user_name}</th>
		</tr>";
	$health_percent = round(($player->health / $player->max_health) * 100);
	$chakra_percent = round(($player->chakra / $player->max_chakra) * 100);
	$stamina_percent = round(($player->stamina / $player->max_stamina) * 100);
	$avatar_size = '125px';
	if($player->forbidden_seal) {
		$avatar_size = '175px';
	}
	echo "<td>
	<img src='{$player->avatar_link}' style='display:block;max-width:$avatar_size;max-height:$avatar_size;margin:auto;' />
	<label style='width:80px;'>Health:</label>" . 
		sprintf("%.2f", $player->health) . '/' . sprintf("%.2f", $player->max_health) . "<br />" .
		"<div style='height:6px;width:250px;border-style:solid;border-width:1px;'>" .
		"<div style='background-color:#C00000;height:6px;width:" . $health_percent . "%;' /></div>" . "</div>" .
	"<label style='width:80px;'>Chakra:</label>" . 
		sprintf("%.2f", $player->chakra) . '/' . sprintf("%.2f", $player->max_chakra) . "<br />" .
		"<div style='height:6px;width:250px;border-style:solid;border-width:1px;'>" .
		"<div style='background-color:#0000B0;height:6px;width:" . $chakra_percent . "%;' /></div>" . "</div>" .
	"<label style='width:80px;'>Stamina:</label>" . 
		sprintf("%.2f", $player->stamina) . '/' . sprintf("%.2f", $player->max_stamina) . "<br />" .
		"<div style='height:6px;width:250px;border-style:solid;border-width:1px;'>" .
		"<div style='background-color:#00B000;height:6px;width:" . $stamina_percent . "%;' /></div>" . "</div>" .
	"</td>
	<td>";
	$opponent_health_percent = round(($opponent->health / $opponent->max_health) * 100);
	$avatar_size = '125px';
	if($opponent->forbidden_seal) {
		$avatar_size = '175px';
	}
	echo "
	<img src='{$opponent->avatar_link}' style='display:block;max-width:$avatar_size;max-height:$avatar_size;margin:auto;' />
	<label style='width:80px;'>Health:</label>" . 
		sprintf("%.2f", $opponent->health) . '/' . sprintf("%.2f", $opponent->max_health) . "<br />" .
		"<div style='height:6px;width:250px;border-style:solid;border-width:1px;'>" .
		"<div style='background-color:#C00000;height:6px;width:" . $opponent_health_percent . "%;' /></div>" . "</div>";
	echo "</td></tr></table>";
	echo "<table class='table'>";

	// Battle text display
	if($battle['battle_text']) {
		$battle_text = $system->html_parse(stripslashes($battle['battle_text']));
		$battle_text = str_replace(array('[br]', '[hr]'), array('<br />', '<hr />'), $battle_text);
		echo "<tr><th colspan='2'>Last turn</th></tr>
		<tr><td style='text-align:center;' colspan='2'>" . $battle_text . "</td></tr>";
	}

	// Trigger win action or display action prompt
	if($winner) {
		if(!$battle['winner']) {
			$system->query("UPDATE `battles` SET `winner`='$winner' WHERE `battle_id`='{$battle['battle_id']}'");
		}
		echo "</table>";
		$player->updateInventory();
		return $winner;
	}
	else {
		// Prompt for move or display wait message
		echo "<tr><th colspan='2'>Select Action</th></tr>";
		if(!$battle[$battle['player_side'] . '_action']) {		
			movePrompt($player, $default_attacks);
		}
		else if(!$battle[$battle['opponent_side'] . '_action']) {
			echo "<tr><td colspan='2'>Please wait for $opponent->user_name to select an action.</td></tr>";
		}	

		// Turn timer
		echo "<tr><td style='text-align:center;' colspan='2'>
			Time remaining: " . ($turn_length - (time() - $battle['turn_time'])) . " seconds</td></tr>";
		$player->updateInventory();
	}

    echo "</table>";
	return false;
}

function movePrompt($player, $default_attacks) {
	global $self_link;
	// New interface	
	if(true) {
		$gold_color = '#FDD017';
		echo "<tr><td colspan='2'>
		<div style='margin:0px;position:relative;'>
		<style type='text/css'>
		#handSeals p {
			display: inline-block;
			width: 80px;
			height: 110px;
			margin: 4px;
			position:relative;
		}
		#handSeals img {
			height: 74px;
			width: 74px;
			position: relative;
			z-index: 1;
			border: 3px solid rgba(0,0,0,0);
			border-radius: 5px;
		}
		#handSeals .handsealNumber {
			display: none;
			width: 18px;
			position: absolute;
			z-index: 20;
			text-align: center;
			left: 31px;
			right: 31px;
			bottom: 35px;
			/* Style */
			font-size: 14px;
			font-weight: bold;
			background-color: $gold_color;
			border-radius: 10px;
		}
		#handSeals .handsealTooltip {
			display: block;
			margin: 0px;
			text-align: center;
			height: 16px;
		}
		#handsealOverlay{
			width:100%;
			position:absolute;
			top:0px;
			height:100%;
			background-color:rgba(255,255,255,0.9);
			z-index:50;
			display:none;
		}
		/* WEAPONS */
		#weapons {
			height: 236px;
			padding-left: 20px;
			padding-right: 20px;
		}
		#jutsu {
			padding-left: 5px;
		}
		#jutsu p {
			display:inline-block;
			margin:0px;
			vertical-align:top;
			margin-right:1%;
			text-align:center;
		}
		#jutsu .jutsuName {
			display: inline-block;
			padding: 5px 7px;
			margin-bottom: 10px;
			/* Style */
			background: linear-gradient(#EFEFEF, #E4E4E4);
			border: 1px solid #E0E0E0;
			border-radius: 15px;
			text-align:center;
			box-shadow: 0 0 4px 0 rgba(0,0,0,0);
		}
		#jutsu .jutsuName:last-child {
			margin-bottom: 1px;
		}
		#jutsu .jutsuName:hover {
			background: linear-gradient(#E4E4E4, #EFEFEF);
			cursor: pointer;
		}
		#weapons p.weapon {
			display: inline-block;
			padding: 8px 10px;
			margin-right: 15px;
			vertical-align:top;
			/* Style */
			background-color: rgba(255, 255, 255, 0.1);
			border: 1px solid #C0C0C0;
			border-radius: 10px;
			text-align:center;
			box-shadow: 0 0 4px 0 rgba(0,0,0,0);
		}
		#weapons p.weapon:last-child {
			margin-right: 1px;
		}
		#weapons p.weapon:hover {
			background: rgba(0, 0, 0, 0.1);
			cursor: pointer;
		}
		</style>
		<script type='text/javascript'>
		$(document).ready(function(){
			var hand_seals = new Array();
			var hand_seal_prompt = 'Please enter handseals (click jutsu name for hint):';
			var weapons_prompt = 'Please select a weapon to augment your Taijutsu with:';
			$('#handSeals p img').click(function() {
				var parent = $(this).parent();
				var seal = parent.attr('data-handseal');
				// Select hand seal
				if(parent.attr('data-selected') == 'no') {
					parent.attr('data-selected', 'yes');
					$(this).css('border-color', '$gold_color');
					parent.children('.handsealNumber').show();
					hand_seals.splice(hand_seals.length, 0, seal);
				}
				// De-select handseal
				else if(parent.attr('data-selected') == 'yes') {
					parent.attr('data-selected', 'no');
					$(this).css('border-color', 'rgba(0,0,0,0)');
					parent.children('.handsealNumber').hide();
					for(var x in hand_seals) {
						if(hand_seals[x] == seal) {
							hand_seals.splice(x,1);
							break;
						}
					}
				}
				// Update display
				$('#hand_seal_input').val(hand_seals.join('-'));
				var id = '';
				for(var x in hand_seals) {
					id = 'handseal_' + hand_seals[x];
					$('#' + id).children('.handsealNumber').text((parseInt(x) + 1));
				}
			});
			var currentlySelectedJutsu = false;
			var lastJutsu, firstJutsu = false;
			$('.jutsuName').click(function(){

				if(lastJutsu != this && firstJutsu) {

					var seals = $(lastJutsu).attr('data-handseals').split('-');
					for(var ay in seals) {
						if(!isNaN(parseInt(seals[ay]))) {
							id = 'handseal_' + seals[ay];
							$('#' + id + ' img').trigger('click');
						}
					}

					lastJutsu = this;

					var new_seals = $(lastJutsu).attr('data-handseals').split('-');
					for(var ayy in new_seals) {
						if(!isNaN(parseInt(new_seals[ayy]))) {
							id = 'handseal_' + new_seals[ayy];
							$('#' + id + ' img').trigger('click');
						}
					}
				
				}

				if(! firstJutsu) {
					lastJutsu = this;
					firstJutsu = true;
					var seals = $(lastJutsu).attr('data-handseals').split('-');
					for(var ay in seals) {
						if(!isNaN(parseInt(seals[ay]))) {
							id = 'handseal_' + seals[ay];
							$('#' + id + ' img').trigger('click');
						}
					}
				}

				if(currentlySelectedJutsu != false) {
					$(currentlySelectedJutsu).css('box-shadow', '0px');
				}
				currentlySelectedJutsu = this;
				$(currentlySelectedJutsu).css('box-shadow', '0px 0px 4px 0px #000000');
				$('.handsealTooltip').html('&nbsp;');
				var handseal_string = $(this).attr('data-handseals');
				var handseal_array = handseal_string.split('-');
				for(var x in handseal_array) {
					if(!isNaN(parseInt(handseal_array[x]))) {
						id = 'handseal_' + handseal_array[x];
						$('#' + id).children('.handsealTooltip').text((parseInt(x) + 1));
					}
				}
			});
			var currentlySelectedWeapon = $('p[data-id=0]');
			$('.weapon').click(function(){
				if(currentlySelectedWeapon != false) {
					$(currentlySelectedWeapon).css('box-shadow', '0px');
				}
				currentlySelectedWeapon = this;
				$(currentlySelectedWeapon).css('box-shadow', '0px 0px 4px 0px #000000');
				$('#weaponID').val( $(this).attr('data-id') );
			});
			var display_state = 'ninjutsu';
			$('#jutsu span.ninjutsu').click(function(){
				if(display_state != 'ninjutsu' && display_state != 'genjutsu') {
					$('#textPrompt').text(hand_seal_prompt);
					$('#weapons').hide();
					$('#handSeals').show();
					$('#handsealOverlay').fadeOut();
				}
				display_state = 'ninjutsu';
				$('#jutsuType').val('ninjutsu');
			});
			$('#jutsu span.genjutsu').click(function(){
				if(display_state != 'genjutsu' && display_state != 'ninjutsu') {
					$('#textPrompt').text(hand_seal_prompt);
					$('#weapons').hide();
					$('#handSeals').show();
					$('#handsealOverlay').fadeOut();
				}
				display_state = 'genjutsu';
				$('#jutsuType').val('genjutsu');
			});
			$('#jutsu span.taijutsu').click(function(){
				if(display_state != 'taijutsu') {	
					$('#textPrompt').text(weapons_prompt);
					$('#handSeals').hide();
					$('#weapons').show();
					if(display_state == 'bloodline_jutsu') {
						$('#handsealOverlay').fadeOut();
					}
				}
				display_state = 'taijutsu';
				$('#jutsuType').val('taijutsu');
				$('#jutsuID').val($(this).attr('data-id'));
			});
			$('#jutsu span.bloodline_jutsu').click(function(){
				if(display_state != 'bloodline_jutsu') {
					$('#handsealOverlay').fadeIn();
				}
				display_state = 'bloodline_jutsu';
				$('#jutsuType').val('bloodline_jutsu');
				$('#jutsuID').val($(this).attr('data-id'));
			});
		});
		</script>
		<!--DIV START-->
		<p id='textPrompt' style='text-align:center;'>Please enter handseals (click jutsu name for hint):</p>
		<div id='handSeals'>
		";
		for($i = 1; $i <= 12; $i++) {
			echo "<p id='handseal_$i' data-selected='no' data-handseal='$i'>
				<img src='./images/handseal_$i.png' draggable='false' />
				<span class='handsealNumber'>1</span>
				<span class='handsealTooltip'>&nbsp;</span>
			</p>";
			if($i == 6) {
				echo "<br />";
			}
		}
		echo "</div>
		<div id='weapons' style='display:none;'>
		<p class='weapon' data-id='0' style='box-shadow: 0px 0px 4px 0px #000000;margin-top:14px;'>
		<b>None</b>
		</p>
		";
		if(is_array($player->equipped_weapons)) {
			foreach($player->equipped_weapons as $item_id) {
				echo "<p class='weapon' data-id='$item_id'>" . 
					"<b>" . $player->items[$item_id]['name'] . "</b><br />" . 
					ucwords(str_replace('_', ' ', $player->items[$item_id]['effect'])) . 
					" (" . $player->items[$item_id]['effect_amount'] . "%)" . 
				"</p>";
			}
		}
		echo "</div>
		<div id='handsealOverlay'>
		</div>
		</td></tr>
		<tr><th colspan='2'>";
		if($player->bloodline_id) {
			$width = '24%';
		}
		else {
			$width = '32%';
		}
		echo "<span style='display:inline-block;width:$width;'>Ninjutsu</span>
			<span style='display:inline-block;width:$width;'>Taijutsu</span>
			<span style='display:inline-block;width:$width;'>Genjutsu</span>" .
			($player->bloodline_id ? "<span style='display:inline-block;width:$width;'>Bloodline</span>" : '');
		echo "</th></tr>
		<tr><td colspan='2'>
		<div id='jutsu'>";
		// Attack list
		$jutsu_types = array('ninjutsu', 'taijutsu', 'genjutsu');
		for($i = 0; $i < 3; $i++) {
			echo "<p style='width:$width;'>";
			foreach($default_attacks as $attack) {
				if($attack['jutsu_type'] != $jutsu_types[$i]) {
					continue;
				}
				echo "<span class='jutsuName {$jutsu_types[$i]}' data-handseals='" . 
					($attack['jutsu_type'] != 'taijutsu' ? $attack['hand_seals'] : '') . "' 
					data-id='{$attack['jutsu_id']}'>" . $attack['name'] . '</span><br />';
			}
			if(is_array($player->equipped_jutsu)) {
				foreach($player->equipped_jutsu as $jutsu) {
					if($player->jutsu[$jutsu['id']]['jutsu_type'] != $jutsu_types[$i]) {
						continue;
					}
					echo "<span class='jutsuName {$jutsu_types[$i]}' data-handseals='{$player->jutsu[$jutsu['id']]['hand_seals']}' 
						data-id='{$jutsu['id']}'>" . $player->jutsu[$jutsu['id']]['name'] . '</span><br />';
				}
			}
			echo "</p>";
		}
		// Display bloodline jutsu
		if($player->bloodline_id) {
			echo "<p style='width:$width;margin-right:0px;'>";
			if(!empty($player->bloodline->jutsu)) {
				foreach($player->bloodline->jutsu as $id => $jutsu) {
					echo "<span class='jutsuName bloodline_jutsu' data-handseals='" . $jutsu['hand_seals'] . "'" .
						"data-id='$id'>" . $jutsu['name'] . '</span><br />';
				}
			}
			echo "</p>";
		}
		echo "
		<form action='$self_link' method='post'>
		<input type='hidden' id='hand_seal_input' name='hand_seals' value='{$_POST['hand_seals']}' />
		<input type='hidden' id='jutsuType' name='jutsu_type' value='ninjutsu' />
		<input type='hidden' id='weaponID' name='weapon_id' value='0' />
		<input type='hidden' id='jutsuID' name='jutsu_id' value='' />
		<p style='display:block;text-align:center;margin:auto;'>
			<input type='submit' name='attack' value='Submit' />
		</p>
		</form>
		</div>";
		echo "</div>
		</td></tr>";
	}
	// No script
	else {			
		$seal_form = "<option value='none'>-</option>";
		for($i = 1; $i <= 12; $i++) {
			$seal_form .= "<option value='$i'>$i</option>";
		}
		echo "<tr><td colspan='2' style='text-align:center;'>
		<form action='$self_link' method='post'>
		<!-- NOSCRIPT -->";
		for($x = 1; $x <= 6; $x++) {
			echo "Seal $x: <select name='hand_seals[]'>
			<option value='-'>-</option>";
			for($y = 1; $y <= 12; $y++) {
				echo "Seal $x: <option value='$y'";
				if($seals && $seals[$x - 1] == $y) {
					echo " selected='selected'";
				}	
				echo ">$y</option>";
			}
			echo "</select>";
		}
		echo "<br />
		<hr />
		<br />";
		// Attack list
		foreach($default_attacks as $attack) {
			echo $attack['name'] . ' (' . str_replace('-', ', ', $attack['hand_seals']) . ')<br />';
		}
		// Display player moves
		if(is_array($player->equipped_jutsu)) {
			foreach($player->equipped_jutsu as $jutsu) {
				echo $player->jutsu[$jutsu['id']]['name'] . ' (' . str_replace('-', ', ', $player->jutsu[$jutsu['id']]['hand_seals']) . ')<br />';
			}
		}
		else {
			echo "lolwaT";
		}
		echo "<input type='submit' name='attack' value='Submit' />
		</form>
		</td></tr>";
	}	
}

function jutsuCollision(&$player, &$opponent, &$player_damage, &$opponent_damage, $player_jutsu, $opponent_jutsu) {
	$collision_text = '';
	/*
	$weapon = array(
						'power' => $player1_jutsu['power'],
						'effect' => $player1->items[$battle['player1_weapon_id']]['effect'],
						'effect_length' => 2,
						'effect_amount' => $player1->items[$battle['player1_weapon_id']]['effect_amount'],
						'jutsu_type' => 'taijutsu'
					);
	*/	

	// Elemental interactions
	if($player_jutsu['element'] && $opponent_jutsu['element']) {
		$player_jutsu['element'] = strtolower($player_jutsu['element']);
		$opponent_jutsu['element'] = strtolower($opponent_jutsu['element']);
		// Fire > Wind > Lightning > Earth > Water > Fire
		if($player_jutsu['element'] == 'fire') {
			if($opponent_jutsu['element'] == 'wind') {
				$opponent_damage *= 0.8;
			}
			else if($opponent_jutsu['element'] == 'water') {
				$player_damage *= 0.8;
			}
		}
		else if($player_jutsu['element'] == 'wind') {
			if($opponent_jutsu['element'] == 'lightning') {
				$opponent_damage *= 0.8;
			}
			else if($opponent_jutsu['element'] == 'fire') {
				$player_damage *= 0.8;
			}
		}
		else if($player_jutsu['element'] == 'lightning') {
			if($opponent_jutsu['element'] == 'earth') {
				$opponent_damage *= 0.8;
			}
			else if($opponent_jutsu['element'] == 'wind') {
				$player_damage *= 0.8;
			}
		}
		else if($player_jutsu['element'] == 'earth') {
			if($opponent_jutsu['element'] == 'water') {
				$opponent_damage *= 0.8;
			}
			else if($opponent_jutsu['element'] == 'lightning') {
				$player_damage *= 0.8;
			}
		}
		else if($player_jutsu['element'] == 'water') {
			if($opponent_jutsu['element'] == 'fire') {
				$opponent_damage *= 0.8;
			}
			else if($opponent_jutsu['element'] == 'earth') {
				$player_damage *= 0.8;
			}
		}
	}	

	// Barriers
	if($player_jutsu['use_type'] == 'barrier') {
		$player_jutsu['effect_amount'] = $player_damage;
		$player->barrier += $player_damage;
		$player_damage = 0;
	}
	if($opponent_jutsu['use_type'] == 'barrier') {
		$opponent_jutsu['effect_amount'] = $opponent_damage;
		$opponent->barrier += $opponent_damage;
		$opponent_damage = 0;
	}
	if($player->barrier && $opponent_jutsu['jutsu_type'] != 'genjutsu') {
		// Block damage from opponent's attack
		if($player->barrier >= $opponent_damage) {
			$block_amount = $opponent_damage;
		}
		else {
			$block_amount = $player->barrier;
		}
		$block_percent = ($opponent_damage >= 1) ? ($block_amount / $opponent_damage) * 100 : 100;
		$player->barrier -= $block_amount;
		$opponent_damage -= $block_amount;
		if($player->barrier < 0) {
			$player->barrier = 0; 
		}
		if($opponent_damage < 0) {
			$opponent_damage = 0;
		}
		// Set display
		$block_percent = round($block_percent, 1);
		$collision_text .= "[player]'s barrier blocked $block_percent% of [opponent]'s damage![br]";		
	}
	if($opponent->barrier && $player_jutsu['jutsu_type'] != 'genjutsu') {
		// Block damage from opponent's attack
		if($opponent->barrier >= $player_damage) {
			$block_amount = $player_damage;
		}
		else {
			$block_amount = $opponent->barrier;
		}
		$block_percent = ($player_damage >= 1) ? ($block_amount / $player_damage) * 100 : 100;
		$opponent->barrier -= $block_amount;
		$player_damage -= $block_amount;
		if($opponent->barrier < 0) {
			$opponent->barrier = 0;
		}
		if($player_damage < 0) {
			$player_damage = 0;
		}
		// Set display
		$block_percent = round($block_percent, 1);
		$collision_text .= "[opponent]'s barrier blocked $block_percent% of [player]'s damage![br]";	
	}

	// Quit if barrier was used by one person (no collision remaining)
	if($player_jutsu['use_type'] == 'barrier' or $opponent_jutsu['use_type'] == 'barrier') {
		if(isset($player->user_name)) {
			$player_name = $player->user_name;
		}
		else {
			$player_name = $player->name;
		}
		if(isset($opponent->user_name)) {
			$opponent_name = $opponent->user_name;
		}
		else {
			$opponent_name = $opponent->name;
		}
		$collision_text = str_replace(
			array('[player]', '[opponent]', 
				'[gender]', '[gender2]'),
			array($player_name, $opponent_name,
				($player->gender == 'Male' ? 'he' : 'she'), ($player->gender == 'Male' ? 'his' : 'her')),
			$collision_text);
        return $collision_text;
	}

	// Weapon diffuse (tai diffuse nin)
	if($player_jutsu['weapon_id'] && $player_jutsu['weapon_effect']['effect'] == 'diffuse') {
		if($opponent_damage <= 0){
				$player_diffuse_amount = 0;
		}
		else {
			$player_diffuse_amount = round(
				$player_damage / $opponent_damage * ($player_jutsu['weapon_effect']['effect_amount'] / 100),
				1
			);	
		}
	}
	if($opponent_jutsu['weapon_id'] && $opponent_jutsu['weapon_effect']['effect'] == 'diffuse') {
		if($player_damage <= 0){
				$opponent_diffuse_amount = 0;
		}
		else {
			$opponent_diffuse_amount = round(
				$opponent_damage / $player_damage * ($opponent_jutsu['weapon_effect']['effect_amount'] / 100),
				1
			);
		}
	}
	if(!empty($player_diffuse_amount)) {
		$opponent_damage *= 1 - $player_diffuse_amount;
		$collision_text .= "[player] diffused " . ($player_diffuse_amount * 100) . "% of [opponent]'s damage![br]";
	}
	if(!empty($opponent_diffuse_amount)) {
		$player_damage *= 1 - $opponent_diffuse_amount;
		$collision_text .= "[opponent] diffused " . ($opponent_diffuse_amount * 100) . "% of [player]'s damage![br]";
	}

    if($player_jutsu['jutsu_type'] == 'genjutsu' or $opponent_jutsu['jutsu_type'] == 'genjutsu') {
		return false;
	}

    $player_min_damage = $player_damage * 0.5;
	if($player_min_damage < 1) {
		$player_min_damage = 1;
	}
	$opponent_min_damage = $opponent_damage * 0.5;
	if($opponent_min_damage < 1) {
		$opponent_min_damage = 1;
	}

	// Apply buffs/nerfs
	$player_speed = $player->speed + $player->speed_boost - $player->speed_nerf;
	$player_speed = 50 + ($player_speed * 0.5);
	if($player_speed <= 0) {
		$player_speed = 1;
	}
	$player_cast_speed = $player->cast_speed + $player->cast_speed_boost - $player->cast_speed_nerf;
	$player_cast_speed = 50 + ($player_cast_speed * 0.5);
	if($player_cast_speed <= 0) {
		$player_cast_speed = 1;
	}

	$opponent_speed = $opponent->speed + $opponent->speed_boost - $opponent->speed_nerf;
	$opponent_speed = 50 + ($opponent_speed * 0.5);
	if($opponent_speed <= 0) {
		$opponent_speed = 1;
	}
	$opponent_cast_speed = $opponent->cast_speed + $opponent->cast_speed_boost - $opponent->cast_speed_nerf;
	$opponent_cast_speed = 50 + ($opponent_cast_speed * 0.5);
	if($opponent_cast_speed <= 0) {
		$opponent_cast_speed = 1;
	}

	// Ratios for damage reduction
	$speed_ratio = 0.8;
	$cast_speed_ratio = 0.8;
	$max_damage_reduction = 0.5;
	if($player_jutsu['jutsu_type'] == 'ninjutsu') {
		// Nin vs Nin
		if($opponent_jutsu['jutsu_type'] == 'ninjutsu') {			
			if($player_cast_speed >= $opponent_cast_speed) {
				$damage_reduction = ($player_cast_speed / $opponent_cast_speed) - 1.0;
				$damage_reduction = round($damage_reduction * $cast_speed_ratio, 2);
				if($damage_reduction > $max_damage_reduction) {
					$damage_reduction = $max_damage_reduction;
				}
				if($damage_reduction >= 0.01) {
					$opponent_damage *= 1 - $damage_reduction;
					$collision_text .= "[player] cast [gender2] jutsu before [opponent] cast, negating " . 
					($damage_reduction * 100) . "% of [opponent]'s damage!";	
				}
			}
			else {
				$damage_reduction = ($opponent_cast_speed / $player_cast_speed) - 1.0;
				$damage_reduction = round($damage_reduction * $cast_speed_ratio, 2);
				if($damage_reduction > $max_damage_reduction) {
					$damage_reduction = $max_damage_reduction;
				}
				if($damage_reduction >= 0.01) {
					$player_damage *= 1 - $damage_reduction;
					$collision_text .= "[opponent] cast their jutsu before [player] cast, negating " . 
						($damage_reduction * 100) . "% of [player]'s damage!";
				}
			}
		}
		// Nin vs Tai
		else if($opponent_jutsu['jutsu_type'] == 'taijutsu') {
			if($player_cast_speed >= $opponent_speed) {
				$damage_reduction = ($player_cast_speed / $opponent_speed) - 1.0;
				$damage_reduction = round($damage_reduction * $cast_speed_ratio, 2);
				if($damage_reduction > $max_damage_reduction) {
					$damage_reduction = $max_damage_reduction;
				}
				if($damage_reduction >= 0.01) {
					$opponent_damage *= 1 - $damage_reduction;
					$collision_text .= "[player] cast [gender2] jutsu before [opponent] attacked, negating " . ($damage_reduction * 100) . 
					"% of [opponent]'s damage!";	
				}
			}
			else {
				$damage_reduction = ($opponent_speed / $player_cast_speed) - 1.0;
				$damage_reduction = round($damage_reduction * $speed_ratio, 2);
				if($damage_reduction > $max_damage_reduction) {
					$damage_reduction = $max_damage_reduction;
				}
				if($damage_reduction >= 0.01) {
					$player_damage *= 1 - $damage_reduction;
					$collision_text .= "[opponent] swiftly evaded " . ($damage_reduction * 100) . "% of [player]'s damage!";
				}
			}		
		}
	}

	// Taijutsu clash
	else if($player_jutsu['jutsu_type'] == 'taijutsu') {
		// Tai vs Tai
		if($opponent_jutsu['jutsu_type'] == 'taijutsu') {
			if($player_speed >= $opponent_speed) {
				$damage_reduction = ($player_speed / $opponent_speed) - 1.0;
				$damage_reduction = round($damage_reduction * $speed_ratio, 2);
				if($damage_reduction > $max_damage_reduction) {
					$damage_reduction = $max_damage_reduction;
				}
				if($damage_reduction >= 0.01) {
					$opponent_damage *= 1 - $damage_reduction;
					$collision_text .= "[player] swiftly evaded " . ($damage_reduction * 100) . "% of [opponent]'s damage!";	
				}
			}
			else {
				$damage_reduction = ($opponent_speed / $player_speed) - 1.0;
				$damage_reduction = round($damage_reduction * $speed_ratio, 2);	
				if($damage_reduction > $max_damage_reduction) {
					$damage_reduction = $max_damage_reduction;
				}
				if($damage_reduction >= 0.01) {
					$player_damage *= 1 - $damage_reduction;
					$collision_text .= "[opponent] swiftly evaded " . ($damage_reduction * 100) . "% of [player]'s damage!";	
				}
			}
		}
		else if($opponent_jutsu['jutsu_type'] == 'ninjutsu') {
			if($player_speed >= $opponent_cast_speed) {
				$damage_reduction = ($player_speed / $opponent_cast_speed) - 1.0;
				$damage_reduction = round($damage_reduction * $speed_ratio, 2);
				if($damage_reduction > $max_damage_reduction) {
					$damage_reduction = $max_damage_reduction;
				}
				if($damage_reduction >= 0.01) {
					$opponent_damage *= 1 - $damage_reduction;
					$collision_text .= "[player] swiftly evaded " . ($damage_reduction * 100) . "% of [opponent]'s damage!";	
				}
			}
			else {
				$damage_reduction = ($opponent_cast_speed / $player_speed) - 1.0;
				$damage_reduction = round($damage_reduction * $cast_speed_ratio, 2);
				if($damage_reduction > $max_damage_reduction) {
					$damage_reduction = $max_damage_reduction;
				}
				if($damage_reduction >= 0.01) {
					$player_damage *= 1 - $damage_reduction;
					$collision_text .= "[opponent] cast their jutsu before [player] attacked, negating " . ($damage_reduction * 100) . 
						"% of [player]'s damage!";		
				}
			}
		}
	}

	// Parse text
	if(isset($player->user_name)) {
		$player_name = $player->user_name;
	}
	else {
		$player_name = $player->name;
	}
	if(isset($opponent->user_name)) {
		$opponent_name = $opponent->user_name;
	}
	else {
		$opponent_name = $opponent->name;
	}

    $collision_text = str_replace(
		array('[player]', '[opponent]', 
			'[gender]', '[gender2]'),
		array($player_name, $opponent_name,
			($player->gender == 'Male' ? 'he' : 'she'), ($player->gender == 'Male' ? 'his' : 'her')),
		$collision_text);
	return $collision_text;
}

function setEffect(&$user, $target_id, $jutsu, $damage, $effect_id, &$active_effects) {
	$apply_effect = true;
	$debug = false;
	
	$debuff_power = ($jutsu['power'] <= 0) ? 0 : $damage / $jutsu['power'] / 15;

	if($debug) {
		echo sprintf("JP: %s (%s)<br />", $jutsu['power'], $jutsu['effect']);
		echo sprintf("%s / %s<br />", $damage, $debuff_power);
	}

    if($jutsu['jutsu_type'] == 'genjutsu' && !empty($jutsu['parent_jutsu'])) {
		$parent_genjutsu_id = $user->id . ':J' . $jutsu['parent_jutsu'];
		if(!empty($active_effects[$parent_genjutsu_id]['layer_active'])) {
			$active_effects[$parent_genjutsu_id]['layer_active'] = true;
			$active_effects[$parent_genjutsu_id]['power'] *= 1.1;	
		}
		$jutsu['power'] *= 1.1;
		$jutsu['effect_amount'] *= 1.1;
	}

    switch($jutsu['effect']) {
		case 'residual_damage':	
		case 'ninjutsu_boost':
		case 'taijutsu_boost':
		case 'genjutsu_boost':
		case 'ninjutsu_nerf':
		case 'taijutsu_nerf':
		case 'genjutsu_nerf':
		case 'ninjutsu_resist':
		case 'taijutsu_resist':
		case 'genjutsu_resist':
			$jutsu['effect_amount'] = round($damage * ($jutsu['effect_amount'] / 100), 2);
			break;
		case 'absorb_chakra':
		case 'absorb_stamina':
			$jutsu['effect_amount'] = round($damage * ($jutsu['effect_amount'] / 600), 2);
			break;
		case 'drain_chakra':
		case 'drain_stamina':
			$jutsu['effect_amount'] = round($damage * ($jutsu['effect_amount'] / 300), 2);
			break;
		case 'speed_boost':
		case 'cast_speed_boost':
		case 'intelligence_boost':
		case 'willpower_boost':
		case 'cast_speed_nerf':
		case 'speed_nerf':
		case 'intelligence_nerf':
		case 'willpower_nerf':
			$jutsu['effect_amount'] = round($debuff_power * ($jutsu['effect_amount'] / 100), 2);
			break;
		case 'barrier':
			$jutsu['effect_amount'] = $damage;
			break;
		default:
			$apply_effect = false;
			break;
	}

    if($apply_effect) {
		$active_effects[$effect_id] = array(
			'user' => $user->id,
			'target' => $target_id,
			'turns' => $jutsu['effect_length'],
			'effect' => $jutsu['effect'],
			'effect_amount' => $jutsu['effect_amount'],
			'effect_type' => $jutsu['jutsu_type']
		);
		if($jutsu['jutsu_type'] == 'genjutsu') {
			$intelligence = ($user->intelligence + $user->intelligence_boost - $user->intelligence_nerf);
			if($intelligence <= 0) {
				$intelligence = 1;
			}
			$active_effects[$effect_id]['power'] = $intelligence * $jutsu['power'];
			$active_effects[$effect_id]['first_turn'] = true;
		}
	}
}

function applyPassiveEffects(&$target, &$attacker, &$effect, &$effect_display = '') {
	// Buffs
	if($effect['effect'] == 'ninjutsu_boost') {
		$target->ninjutsu_boost += $effect['effect_amount'];
	}
	else if($effect['effect'] == 'taijutsu_boost') {
		$target->taijutsu_boost += $effect['effect_amount'];
	}
	else if($effect['effect'] == 'genjutsu_boost') {
		$target->genjutsu_boost += $effect['effect_amount'];
	}
	else if($effect['effect'] == 'cast_speed_boost') {
		$target->cast_speed_boost += $effect['effect_amount'];
	}
	else if($effect['effect'] == 'speed_boost' or $effect['effect'] == 'lighten') {
		$target->speed_boost += $effect['effect_amount'];
	}
	else if($effect['effect'] == 'intelligence_boost') {
		$target->intelligence_boost += $effect['effect_amount'];
	}
	else if($effect['effect'] == 'endurance_boost') {
		$target->endurance_boost += $effect['effect_amount'];
	}
	else if($effect['effect'] == 'willpower_boost') {
		$target->willpower_boost += $effect['effect_amount'];
	}
	else if($effect['effect'] == 'ninjutsu_resist') {
		$target->ninjutsu_resist += $effect['effect_amount'];
	}
	else if($effect['effect'] == 'genjutsu_resist') {
		$target->genjutsu_resist += $effect['effect_amount'];
	}
	else if($effect['effect'] == 'taijutsu_resist' or $effect['effect'] == 'harden') {
		$target->taijutsu_resist += $effect['effect_amount'];
	}
	else if($effect['effect'] == 'barrier') {
		$target->barrier += $effect['effect_amount'];
	}
	// Debuffs
	$target_debuff_resist = 50 + ($target->willpower + $target->willpower_boost - $target->willpower_nerf) * 0.5;
	if($effect['effect'] == 'ninjutsu_nerf') {
		$target->ninjutsu_nerf += $effect['effect_amount'] / $target_debuff_resist;
	}
	else if($effect['effect'] == 'taijutsu_nerf') {
		$target->taijutsu_nerf += $effect['effect_amount'] / $target_debuff_resist;
	}
	else if($effect['effect'] == 'genjutsu_nerf') {
		$target->genjutsu_nerf += $effect['effect_amount'] / $target_debuff_resist;
	}
	else if($effect['effect'] == 'cast_speed_nerf') {
		$target->cast_speed_nerf += $effect['effect_amount'] / $target_debuff_resist;
	}
	else if($effect['effect'] == 'speed_nerf' or $effect['effect'] == 'cripple') {
		$target->speed_nerf += $effect['effect_amount'] / $target_debuff_resist;
	}
	else if($effect['effect'] == 'intelligence_nerf' or $effect['effect'] == 'daze') {
		$target->intelligence_nerf += $effect['effect_amount'] / $target_debuff_resist;
	}
	else if($effect['effect'] == 'endurance_nerf') {
		$target->endurance_nerf += $effect['effect_amount'] / $target_debuff_resist;
	}
	else if($effect['effect'] == 'willpower_nerf') {
		$target->willpower_nerf += $effect['effect_amount'] / $target_debuff_resist;
	}
	return false;
}

function applyActiveEffects(&$target, &$attacker, &$effect, &$effect_display, &$winner) {
	if($winner && $winner != $target->id) {
		return false;
	}
	if($effect['effect'] == 'residual_damage' || $effect['effect'] == 'bleed') {
		$damage = $target->calcDamageTaken($effect['effect_amount'], $effect['effect_type']);
		$effect_display .= '[br]-'. (isset($target->user_name) ? $target->user_name : $target->name) . 
			" takes $damage residual damage-";
		$target->health -= $damage;
		if($target->health < 0) {
			$target->health = 0;
		}
	}
	else if($effect['effect'] == 'heal') {
		$heal = $effect['effect_amount'];
		$effect_display .= '[br]-'. (isset($target->user_name) ? $target->user_name : $target->name) . 
			" heals $heal health-";
		$target->health += $heal;
		if($target->health > $target->max_health) {
			$target->health = $target->max_health;
		}
	}
	else if($effect['effect'] == 'drain_chakra') {
		$drain = $target->calcDamageTaken($effect['effect_amount'], $effect['effect_type']);
		$effect_display .= '[br]-'. $attacker->user_name . " drains $drain of " . 
			(isset($target->user_name) ? $target->user_name : $target->name) . "'s chakra-";
		$target->chakra -= $drain;
		if($target->chakra < 0) {
			$target->chakra = 0;
		}
	}
	else if($effect['effect'] == 'drain_stamina') {
		$drain = $target->calcDamageTaken($effect['effect_amount'], $effect['effect_type']);
		$effect_display .= '[br]-'. $attacker->user_name . " drains $drain of " . 
			(isset($target->user_name) ? $target->user_name : $target->name) . "'s stamina-";
		$target->stamina -= $drain;
		if($target->stamina < 0) {
			$target->stamina = 0;
		}
	}
	else if($effect['effect'] == 'absorb_chakra') {
		$drain = $target->calcDamageTaken($effect['effect_amount'], $effect['effect_type']);
		$effect_display .= '[br]-'. $attacker->user_name . " absorbs $drain of " . 
			(isset($target->user_name) ? $target->user_name : $target->name) . "'s chakra-";
		$target->chakra -= $drain;
		if($target->chakra < 0) {
			$target->chakra = 0;
		}
		$attacker->chakra += $drain;
		if($attacker->chakra > $attacker->max_chakra) {
			$attacker->chakra = $attacker->max_chakra;
		}
	}
	else if($effect['effect'] == 'absorb_stamina') {
		$drain = $target->calcDamageTaken($effect['effect_amount'], $effect['effect_type']);
		$effect_display .= '[br]-'. $attacker->user_name . " absorbs $drain of " . 
			(isset($target->user_name) ? $target->user_name : $target->name) . "'s stamina-";
		$target->stamina -= $drain;
		if($target->stamina < 0) {
			$target->stamina = 0;
		}
		$attacker->stamina += $drain;
		if($attacker->stamina > $attacker->max_stamina) {
			$attacker->stamina = $attacker->max_stamina;
		}
	}
	if($target->health <= 0) {
		$winner = $attacker->id;
	}
	return false;
}

function runTurnEffects() {
	return false;
}