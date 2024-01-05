<?php

require_once __DIR__ . '/../classes/RankManager.php';

/**
 * @throws RuntimeException
 */
function bloodline() {
	global $system;
	/** @var User */
	global $player;
	global $self_link;

	$RANK_NAMES = RankManager::fetchNames($system);

	if(!$player->bloodline_id) {
		$system->message("You do not have a bloodline!");
		$system->printMessage();
		return false;
	}
	$base_bloodline = Bloodline::loadFromId($system, $player->bloodline_id);
	$player->getInventory();

	// Learn jutsu
	if(isset($_GET['learn_jutsu'])) {
		$jutsu_id = (int)$_GET['learn_jutsu'];
		try {
			if(!isset($base_bloodline->jutsu[$jutsu_id])) {
				throw new RuntimeException("Invalid jutsu!");
			}
			if(isset($player->bloodline->jutsu[$jutsu_id])) {
				throw new RuntimeException("You already know this jutsu!");
			}
			if($base_bloodline->jutsu[$jutsu_id]->rank > $player->rank_num) {
				throw new RuntimeException("You are not high enough rank to learn this jutsu!");
			}
			// Parent jutsu check
			if($base_bloodline->jutsu[$jutsu_id]->parent_jutsu) {
				$id = $base_bloodline->jutsu[$jutsu_id]->parent_jutsu - 1;
				if(!isset($player->bloodline->jutsu[$id])) {
					throw new RuntimeException("You need to learn " . $base_bloodline->jutsu[$id]->name . " first!");
				}
				if($player->bloodline->jutsu[$id]->level < 50) {
					throw new RuntimeException("You are not skilled enough with " . $player->bloodline->jutsu[$id]->name .
						"! (Level " . $player->bloodline->jutsu[$id]->level . "/50)");
				}
			}
			$player->bloodline->jutsu[$jutsu_id] = $base_bloodline->jutsu[$jutsu_id];
			$player->bloodline->jutsu[$jutsu_id]->id = $jutsu_id;
            if ($system->isDevEnvironment()) {
                $player->bloodline->jutsu[$jutsu_id]->level = 100;
            } else {
                $player->bloodline->jutsu[$jutsu_id]->level = 1;
            }
			$player->bloodline->jutsu[$jutsu_id]->exp = 0;
			$player->updateInventory();
			$system->message("Learned " . $base_bloodline->jutsu[$jutsu_id]->name . "!");
		} catch (RuntimeException $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	echo "<style>
	label {
		display: inline-block;
	}
	</style>";

    echo "<table class='table' style='width: 125px''><tr><td style='text-align: center'><a style='tab-index: 0' href='" . $system->router->getUrl("profile") . "'>Back to Profile</a></td></tr></table>";

	echo "<table class='table'><tr><th>Your Bloodline</th></tr>
		<tr><td>";
	$bloodline_ranks = array(4 => 'Lesser', 3 => 'Common', 2 => 'Elite', 1 => 'Legendary', 5 => 'Admin');
	echo "<label style='width: 8.8em;font-weight:bold;'>Name:</label>" . $player->bloodline->name . "<br />
		<label style='width: 8.8em;font-weight:bold;'>Rank:</label>" . $bloodline_ranks[$player->bloodline->rank] . "<br />
		<label style='width: 8.8em;font-weight:bold;'>Bloodline skill:</label>" . $player->bloodline_skill . "<br />";
	echo "<br />";


	$estimated_jutsu_power = $player->rank_num;
	$estimated_jutsu_power += round($player->total_stats / $player->rank->max_level_stats, 3);
	if($estimated_jutsu_power > $player->rank_num + 1) {
		$estimated_jutsu_power = $player->rank_num + 1;
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
			'text' => "[BL_SKILL] * [RATIO] -> <span class='amount'>[AMOUNT] extra Ninjutsu offense</span><br><span style='padding-left: 15px; font-size: smaller; font-style: italic'>Boost gradually decreases to 75% strength as Bloodline Skill exceeds Ninjutsu skill</span>"
		),
		'taijutsu_boost' => array(
			'text' => "[BL_SKILL] * [RATIO] -> <span class='amount'>[AMOUNT] extra Taijutsu offense</span><br><span style='padding-left: 15px; font-size: smaller; font-style: italic'>Boost gradually  decreases to 75% strength as Bloodline Skill exceeds Taijutsu skill</span>"
		),
		'genjutsu_boost' => array(
			'text' => "[BL_SKILL] * [RATIO] -> <span class='amount'>[AMOUNT] extra Genjutsu offense</span><br><span style='padding-left: 15px; font-size: smaller; font-style: italic'>Boost gradually  decreases to 75% strength as Bloodline Skill exceeds Genjutsu skill</span>"
		),
		'heal' => array(
            'text' => "[BL_SKILL] * [RATIO] / [AMOUNT2] (stat total) -> <span class='amount'>[AMOUNT]% damage recovery</span><br><span style='padding-left: 15px; font-size: smaller; font-style: italic'>(assuming opponent of equal total stats)</span>"
		),
		'ninjutsu_resist' => array(
			'text' => "[BL_SKILL] * [RATIO] -> <span class='amount'>[AMOUNT] less Ninjutsu damage taken</span>"
		),
		'genjutsu_resist' => array(
			'text' => "[BL_SKILL] * [RATIO] -> <span class='amount'>[AMOUNT] less Genjutsu damage taken</span>"
		),
		'taijutsu_resist' => array(
			'text' => "[BL_SKILL] * [RATIO] -> <span class='amount'>[AMOUNT] less Taijutsu damage taken</span>"
		),
		'damage_resist' => array(
			'text' => "[BL_SKILL] * [RATIO] / [AMOUNT2] (stat total) -> <span class='amount'>[AMOUNT]% less damage taken</span><br><span style='padding-left: 15px; font-size: smaller; font-style: italic'>(assuming opponent of equal total stats)</span>"
		),
		'speed_boost' => array(
			'text' => "[BL_SKILL] * [RATIO] -> <span class='amount'> [AMOUNT] extra Speed</span>"
		),
		'cast_speed_boost' => array(
			'text' => "[BL_SKILL] * [RATIO] -> <span class='amount'> [AMOUNT] extra Cast Speed</span>"
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
	$bloodline_skill = $player->bloodline_skill < 100 ? 100 : $player->bloodline_skill;

	echo "
	<style>
	span.amount {
		color:#00C000;
	}
	</style>";
	$search_array = array('[BL_SKILL]', '[RATIO]', '[AMOUNT]', '[PERCENTAGE]', '[AMOUNT2]');
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

			switch($boost['effect']) {
				case 'ninjutsu_boost':
                    $player_offense = Fighter::BASE_OFFENSE + ($player->ninjutsu_skill * Fighter::SKILL_OFFENSE_RATIO);
					$replace_array[3] = round($boost['effect_amount'] / $player_offense, 0) * 100;
                    $replace_array[2] *= 10;
					$replace_array[1] *= 10;
					break;
				case 'taijutsu_boost':
                    $player_offense = Fighter::BASE_OFFENSE + ($player->taijutsu_skill * Fighter::SKILL_OFFENSE_RATIO);
                    $replace_array[3] = round($boost['effect_amount'] / $player_offense, 0) * 100;
                    $replace_array[2] *= 10;
					$replace_array[1] *= 10;
					break;
				case 'genjutsu_boost':
                    $player_offense = Fighter::BASE_OFFENSE + ($player->genjutsu_skill * Fighter::SKILL_OFFENSE_RATIO);
                    $replace_array[3] = round($boost['effect_amount'] / $player_offense, 0) * 100;
                    $replace_array[2] *= 10;
					$replace_array[1] *= 10;
					break;
                case 'taijutsu_resist':
                case 'genjutsu_resist':
                case 'ninjutsu_resist':
					$replace_array[2] = round(
                        ($boost['effect_amount'] * Fighter::BLOODLINE_DEFENSE_MULTIPLIER) / Fighter::BASE_DEFENSE,
                        0
                    );
                    break;
				case 'damage_resist':
                    $replace_array[2] = round(($replace_array[2] / $player->total_stats) * 100, 0);
                    $replace_array[4] = $player->total_stats;
                    break;
                case 'heal':
                    $replace_array[2] = round(($replace_array[2] / $player->total_stats) * 100, 0);
                    $replace_array[4] = $player->total_stats;
                    break;
            }

            $replace_array[1] = round($replace_array[1], 3);
            $replace_array[2] = round($replace_array[2], 0);

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
            foreach ($jutsu->effects as $effect) {
                if ($effect->effect && $effect->effect != 'none') {
                    echo "<label style='width:6.5em;'>Effect:</label>" .
                        System::unSlug($effect->effect) . ' (' . round($effect->effect_amount, 0) . '%) ' . ' - ' . $effect->effect_length . " turns<br />";
                }
            }
			echo "<label style='width:6.5em;'>Jutsu type:</label>" . ucwords($jutsu->jutsu_type) . "<br />
			<label style='width:6.5em;'>Power:</label>" . round($jutsu->power, 1) . "<br />

			<label style='width:6.5em;'>Level:</label>" . $jutsu->level . "<br />
			<label style='width:6.5em;'>Exp:</label>" . $jutsu->exp . "<br />";
			echo "<br />";
			echo "<label style='width:6.5em;float:left;'>Description:</label>
			<p style='display:inline-block;width:37.1em;margin:0;'>" . $jutsu->description . "</p><br>
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
						<p style='display:inline-block;margin:0;width:37.1em;'>" . $jutsu->description . "</p>
					<br style='clear:both;' />
				<label style='width:6.5em;'>Jutsu type:</label>" . ucwords($jutsu->jutsu_type) . "<br />
				</div>
				<p style='text-align:right;margin:0;'><a href='$self_link&learn_jutsu=$id'>Learn</a></p>
			</td></tr>";
		}
	}
	echo "</td></tr></table>";
}
