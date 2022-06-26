<?php
/*	File:		bloodline.php
	Author:		Levi Meahan
	Created:	04/10/2014
*/
function bloodline() {
	global $system;
	/** @var User */
	global $player;
	global $self_link;
	global $RANK_NAMES;
	if(!$player->bloodline_id) {
		$system->message("You do not have a bloodline!");
		$system->printMessage();
		return false;
	}
	$base_bloodline = new Bloodline($player->bloodline_id);
	$player->getInventory();

    require_once "profile.php";
    renderProfileSubmenu();

	// Learn jutsu
	if(isset($_GET['learn_jutsu'])) {
		$jutsu_id = (int)$_GET['learn_jutsu'];
		try {
			if(!isset($base_bloodline->jutsu[$jutsu_id])) {
				throw new Exception("Invalid jutsu!");
			}
			if(isset($player->bloodline->jutsu[$jutsu_id])) {
				throw new Exception("You already know this jutsu!");
			}
			if($base_bloodline->jutsu[$jutsu_id]->rank > $player->rank) {
				throw new Exception("You are not high enough rank to learn this jutsu!");
			}
			// Parent jutsu check
			if($base_bloodline->jutsu[$jutsu_id]->parent_jutsu) {
				$id = $base_bloodline->jutsu[$jutsu_id]->parent_jutsu - 1;
				if(!isset($player->bloodline->jutsu[$id])) {
					throw new Exception("You need to learn " . $base_bloodline->jutsu[$id]->name . " first!");
				}
				if($player->bloodline->jutsu[$id]->level < 50) {
					throw new Exception("You are not skilled enough with " . $player->bloodline->jutsu[$id]->name .
						"! (Level " . $player->bloodline->jutsu[$id]->level . "/50)");
				}
			}
			$player->bloodline->jutsu[$jutsu_id] = $base_bloodline->jutsu[$jutsu_id];
			$player->bloodline->jutsu[$jutsu_id]->id = $jutsu_id;
			$player->bloodline->jutsu[$jutsu_id]->level = 1;
			$player->bloodline->jutsu[$jutsu_id]->exp = 0;
			$player->updateInventory();
			$system->message("Learned " . $base_bloodline->jutsu[$jutsu_id]->name . "!");
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	echo "<style>
	label {
		display: inline-block;
	}
	</style>";
	echo "<table class='table'><tr><th>Your Bloodline</th></tr>
		<tr><td>";
	$bloodline_ranks = array(4 => 'Lesser', 3 => 'Common', 2 => 'Elite', 1 => 'Legendary', 5 => 'Admin');
	echo "<label style='width: 8.8em;font-weight:bold;'>Name:</label>" . $player->bloodline->name . "<br />
		<label style='width: 8.8em;font-weight:bold;'>Rank:</label>" . $bloodline_ranks[$player->bloodline->rank] . "<br />
		<label style='width: 8.8em;font-weight:bold;'>Bloodline skill:</label>" . $player->bloodline_skill . "<br />";
	echo "<br />";


	$estimated_jutsu_power = $player->rank;
	$estimated_jutsu_power += round($player->total_stats / $player->stats_max_level, 3);
	if($estimated_jutsu_power > $player->rank + 1) {
		$estimated_jutsu_power = $player->rank + 1;
	}
	$estimated_offense = 35 * $estimated_jutsu_power;
	$estimated_defense = 50 + ($player->total_stats * 0.01);

	$boosts = array(
		'scout_range' => array(
			'text' => "<span class='amount'>[AMOUNT] extra square(s)</span>"
		),
		'stealth' => array(
			'text' => "<span class='amount'>[AMOUNT] square(s) reduced detection</span>"
		),
		'regen' => array(
			'text' => "[BL_SKILL] -> [RATIO]% of base regen -> <span class='amount'>[AMOUNT] per minute</span>",
			'ratio_multiplier' => 100
		),
		'ninjutsu_boost' => array(
			'text' => "[BL_SKILL] * [RATIO] -> <span class='amount'>[AMOUNT] extra ninjutsu offense (+[PERCENTAGE]%)</span>"
		),
		'taijutsu_boost' => array(
			'text' => "[BL_SKILL] * [RATIO] -> <span class='amount'>[AMOUNT] extra taijutsu offense (+[PERCENTAGE]%)</span>"
		),
		'genjutsu_boost' => array(
			'text' => "[BL_SKILL] * [RATIO] -> <span class='amount'>[AMOUNT] extra genjutsu offense (+[PERCENTAGE]%)</span>"
		),
		'heal' => array(
			'text' => "[BL_SKILL] * [RATIO] -> <span class='amount'>[AMOUNT] per turn</span>"
		),
		'ninjutsu_resist' => array(
			'text' => "[BL_SKILL] * [RATIO] -> [random/defense] -> <span class='amount'>[AMOUNT] less ninjutsu damage taken</span>"
		),			
		'genjutsu_resist' => array(
			'text' => "[BL_SKILL] * [RATIO] -> [random/defense] -> <span class='amount'>[AMOUNT] less genjutsu damage taken</span>"
		),
		'taijutsu_resist' => array(
			'text' => "[BL_SKILL] * [RATIO] -> [random/defense] -> <span class='amount'>[AMOUNT] less taijutsu damage taken</span>"
		),			
		'speed_boost' => array(
			'text' => "[BL_SKILL] * [RATIO] -> <span class='amount'> [AMOUNT] extra speed</span>"
		),
		'cast_speed_boost' => array(
			'text' => "[BL_SKILL] * [RATIO] -> <span class='amount'> [AMOUNT] extra cast speed</span>"
		),
		'endurance_boost' => array(
			'text' => "<span style='color:#00A0C0;'>R.I.P. Endurance 2014-2014, gone but not forgotten :/</span>"
		),
		'intelligence_boost' => array(
			'text' => "[BL_SKILL] * [RATIO] -> <span class='amount'> [AMOUNT] extra intelligence</span>"
		),
		'willpower_boost' => array(
			'text' => "[BL_SKILL] * [RATIO] -> <span class='amount'> [AMOUNT] extra willpower</span>"
		)
	);
	$bloodline_skill = 100 + $player->bloodline_skill;

	echo "
	<style>
	span.amount {
		color:#00C000;
	}	
	</style>";
	$search_array = array('[BL_SKILL]', '[RATIO]', '[AMOUNT]', '[PERCENTAGE]');
	if($player->bloodline->passive_boosts) {
		echo "
		<label style='font-weight:bold;'>Passive boosts</label>
		<br />
		<div style='margin-left:2em;margin-top:7px;'>";
		foreach($player->bloodline->passive_boosts as $boost) {
			$replace_array = array($bloodline_skill, $boost['power'], $boost['effect_amount']);
			if(isset($boosts[$boost['effect']]['ratio_multiplier'])) {
				$replace_array[1] *= $boosts[$boost['effect']]['ratio_multiplier'];
			}
			if(isset($boosts[$boost['effect']]['amount_multiplier'])) {
				$replace_array[2] *= $boosts[$boost['effect']]['amount_multiplier'];
			}
			echo "<label style='width:9em;'>" . ucwords(str_replace('_', ' ', $boost['effect'])) . ":</label>" .
				str_replace($search_array, $replace_array, $boosts[$boost['effect']]['text']);
			// Extra text
			switch($boost['effect']) {
				case 'scout_range':
				case 'stealth':
					if(isset($boost['progress'])) {
						echo " (" . $boost['progress'] . "/100% to next square)";
					}
					break;
				default:
					break;
			}
			echo "<br />";
		}
		echo "</div><br />";
	}
	if($player->bloodline->combat_boosts) {
		echo "<label style='font-weight:bold;'>Combat boosts</label>
		<br />
		<div style='margin-left:2em;margin-top:7px;'>";
		foreach($player->bloodline->combat_boosts as $boost) {
			$replace_array = array($bloodline_skill, $boost['power'], $boost['effect_amount'], 0);
			if(isset($boosts[$boost['effect']]['ratio_multiplier'])) {
				$replace_array[1] *= $boosts[$boost['effect']]['ratio_multiplier'];
			}
			if(isset($boosts[$boost['effect']]['amount_multiplier'])) {
				$replace_array[2] *= $boosts[$boost['effect']]['amount_multiplier'];
			}
			$replace_array[2] = round($replace_array[2], 2);
			switch($boost['effect']) {
				case 'ninjutsu_boost':
					$replace_array[3] = round($boost['effect_amount'] / (35 + $player->ninjutsu_skill * 0.10), 2) * 100;
					break;
				case 'taijutsu_boost':
					$replace_array[3] = round($boost['effect_amount'] / (35 + $player->taijutsu_skill * 0.10), 2) * 100;
					break;
				case 'genjutsu_boost':
					$replace_array[3] = round($boost['effect_amount'] / (35 + $player->genjutsu_skill * 0.10), 2) * 100;
					break;
				case 'ninjutsu_resist':
					$replace_array[2] = round(($boost['effect_amount'] * 35) /
					(50 + $player->ninjutsu_skill * 0.02), 2);
					break;
				case 'taijutsu_resist':
					$replace_array[2] = round(($boost['effect_amount'] * 35) /
					(50 + $player->taijutsu_skill * 0.02), 2);
					break;
				case 'genjutsu_resist':
					$replace_array[2] = round(($boost['effect_amount'] * 35) /
					(50 + $player->genjutsu_skill * 0.02), 2);
					break;
			}
			echo "<label style='width:9em;'>" . ucwords(str_replace('_', ' ', $boost['effect'])) . ":</label>" .
				str_replace($search_array, $replace_array, $boosts[$boost['effect']]['text']) . "<br />";
		}
		echo "</div><br />";
	}
	if($player->bloodline->jutsu) {
		echo "<label style='font-weight:bold;'>Jutsu:</label>
		<br />
		<div style='margin-left:2em;margin-top:7px;'>";
		foreach($player->bloodline->jutsu as $jutsu) {
			echo "<label style='font-weight:bold;'>" . $jutsu->name . "</label><br />
			<label style='width:6.5em;'>Rank:</label>" . $RANK_NAMES[$jutsu->rank] . "<br />";
			if($jutsu->element != 'none') {
				echo "<label style='width:6.5em;'>Element:</label>" . $jutsu->element . "<br />";
			}
			echo "<label style='width:6.5em;'>Use cost:</label>" . $jutsu->use_cost . "<br />";
			if($jutsu->cooldown) {
				echo "<label style='width:6.5em;'>Cooldown:</label>" . $jutsu->cooldown . " turn(s)<br />";
			}
			if($jutsu->effect) {
				echo "<label style='width:6.5em;'>Effect:</label>" . 
					ucwords(str_replace('_', ' ', $jutsu->effect)) . ' - ' . $jutsu->effect_length . " turns<br />";
			}
			echo "<label style='width:6.5em;'>Jutsu type:</label>" . ucwords($jutsu->jutsu_type) . "<br />
			<label style='width:6.5em;'>Power:</label>" . round($jutsu->power, 1) . "<br />

			<label style='width:6.5em;'>Level:</label>" . $jutsu->level . "<br />
			<label style='width:6.5em;'>Exp:</label>" . $jutsu->exp . "<br />";
			echo "<br /><br />";
			echo "<label style='width:6.5em;float:left;'>Description:</label>
			<p style='display:inline-block;width:37.1em;margin:0;'>" . $jutsu->description . "</p>
			<br style='margin:0;clear:both;' />";
			
		}
		echo "</div>";
	}
	echo "</td></tr>";

	if(!$player->bloodline->jutsu or count($player->bloodline->jutsu) < count($base_bloodline->jutsu)) {
		echo "<tr><th>Learn Jutsu</th></tr>";
		foreach($base_bloodline->jutsu as $id=>$jutsu) {
			if(isset($player->bloodline->jutsu[$id])) {
				continue;
			}
			echo "<tr><td>
				<span style='font-weight:bold;'>" . $jutsu->name . "</span><br />
				<div style='margin-left:2em;'>
					<label style='width:6.5em;'>Rank:</label>" . $RANK_NAMES[$jutsu->rank] . "<br />";
					if($jutsu->parent_jutsu) {
						echo "<label style='width:6.5em;'>Parent Jutsu:</label>" . 
							$base_bloodline->jutsu[($jutsu->parent_jutsu - 1)]->name . "<br />";
					}
					if($jutsu->element != 'none') {
						echo "<label style='width:6.5em;'>Element:</label>" . $jutsu->element . "<br />";
					}
					echo "<label style='width:6.5em;'>Use cost:</label>" . $jutsu->use_cost . "<br />
					<label style='width:6.5em;float:left;'>Description:</label> 
						<p style='display:inline-block;margin:0px;width:37.1em;'>" . $jutsu->description . "</p>
					<br style='clear:both;' />
				<label style='width:6.5em;'>Jutsu type:</label>" . ucwords($jutsu->jutsu_type) . "<br />
				</div>
				<p style='text-align:right;margin:0px;'><a href='$self_link&learn_jutsu=$id'>Learn</a></p>
			</td></tr>";
		}
	}
	echo "</td></tr></table>";
}
