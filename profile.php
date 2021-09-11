<?php
/* 
File: 		profile.php
Coder:		Levi Meahan
Created:	02/26/2013
Revised:	08/24/2013 by Levi Meahan
Purpose:	Functions for displaying user profile
Algorithm:	See master_plan.html
*/

function userProfile() {
	require("variables.php");
	global $system;

	global $player;
	global $self_link;
	
	// Submenu
	if($player->rank > 1) {				
		echo "<div class='submenu'>
		<ul class='submenu'>
			<li style='width:25.5%;'><a href='{$self_link}'>Character</a></li>
			<li style='width:25.5%;'><a href='{$self_link}&page=send_money'>Send Money</a></li>
		";
		if ($player->rank > 2) {
			echo "
				<li style='width:25.5%;'><a href='{$self_link}&page=send_ak'>Send AK</a></li>
			";
		}
		echo"
				</ul>
			</div>
			<div class='submenuMargin'></div>
		";
	}
	
	
	// Level up/rank up checks
	$exp_needed = $player->exp_per_level * (($player->level + 1) - $player->base_level) + ($player->base_stats * 10);
	
	// Admin override
	if($player->staff_level >= $SC_ADMINISTRATOR) {
		$SC_MAX_RANK = 4;
	}
	
	// Level up
	if($player->level < $player->max_level && $player->exp >= $exp_needed) {
		if($player->battle_id) {
			echo "<p style='text-align:center;font-style:italic;$extra_style'>
				You must be out of battle to level up.</p>";
		}
		else {
			require("levelUp.php");
			levelUp();
			$exp_needed = $player->exp_per_level * (($player->level + 1) - $player->base_level) + $player->base_exp;
		}
	}
	// Rank up
	else if($player->level >= $player->max_level && $player->exp >= $exp_needed && $player->rank < $SC_MAX_RANK) {

		if($player->battle_id > 0 or !$player->in_village) {
			echo "<p style='text-align:center;font-style:italic;$extra_style'>
				You must be out of battle and in your village to rank up.</p>";
		}
		else if($_GET['rankup']) {
			require("levelUp.php");
			rankUp();
			return true;
		}
		else {
			echo "<p style='text-align:center;font-size:1.1em;$extra_style'>
				<a style='text-decoration:none;' href='$self_link&rankup=1'>Take exam for the next rank</a>
			</p>";
		}
	}	
	
	$page = 'profile';
	if(isset($_GET['page'])) {
		switch($_GET['page']) {
			case 'send_money':
				if($player->rank > 1) {
					$page = 'send_money';
				}
				break;
			case 'send_ak':
				if($player->rank > 2) {
					$page = 'send_ak';
				}
				break;
		}
	}
	
	// Process input
	if(isset($_POST['send_currency'])) {
		$recipient = $system->clean($_POST['recipient']);
		$amount = (int)$system->clean($_POST['amount']);
		
		try {
			if(strtolower($recipient) == strtolower($player->user_name)) {
				throw new Exception("You cannot send money/AK to yourself!");
			}
			$result = $system->query("SELECT `user_id`, `user_name` FROM `users` WHERE `user_name`='$recipient' LIMIT 1");
			if(! $system->db_num_rows) {
				throw new Exception("Invalid user!");
			}
			else {
				$recipient = $system->db_fetch($result);
			}
			if(isset($_POST['yen'])) {
				$type = 'money';
			}
			else if(isset($_POST['kunai'])) {
				$type = 'premium_credits';
			}
			else {
				throw new Exception("Invalid Currency Type!");
			}
			if($amount <= 0) {
				throw new Exception("Invalid amount!");
			}
			if($amount > $player->$type) {
				throw new Exception("You do not have that much money/AK!");
			}
			$player->$type -= $amount;
			$system->query("UPDATE `users` SET `{$type}`=`{$type}` + $amount WHERE `user_id`='{$recipient['user_id']}' LIMIT 1");
			if($type == 'money') {
				$system->send_pm('Currency Transfer System', $recipient['user_id'], 'Money Received', $player->user_name . " has sent you &yen;$amount.");
			}
			else {
				$system->send_pm('Currency Transfer System', $recipient['user_id'], 'AK Received', $player->user_name . " has sent you $amount Ancient Kunai.");
			}
			$system->message("Currency sent!");
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	if($page == 'send_money' || $page == 'send_ak') {

		$type = ($page == 'send_money') ? "Money" : "AK";
		$currency = ($type == 'Money') ? "money" : "premium_credits";
		$hidden = ($type == 'Money') ? "yen" : "kunai";

		echo "<table class='table'><tr><th>Send {$type}</th></tr>
		<tr><td style='text-align:center;'>
		<form action='{$self_link}&page={$page}' method='post'>
		<b>Your {$type}:</b> {$player->$currency}<br />
		<br />
		Send {$type} to:<br />
		<input type='hidden' name='{$hidden}' value='1'/>
		<input type='text' name='recipient' /><br />
		Amount:<br />
		<input type='text' name='amount' /><br />
		<input type='submit' name='send_currency' value='Send {$type}' />
		</form>
		</td></tr></table>";
	}
	else if($page == 'profile') {
		$avatar_size = '125px';
		if($player->forbidden_seal) {
			$avatar_size = '175px';
		}
		echo "<table class='profile_table table'>

		<tr><td style='width:50%;text-align:center;'>
		<span style='font-size:1.3em;font-family:\"tempus sans itc\";font-weight:bold;'>" . $player->user_name . "</span><br />
		<img src='{$player->avatar_link}' style='margin-top:5px;max-width:$avatar_size;max-height:$avatar_size;' /><br />
		</td>";
		
		$exp_percent = ($player->exp_per_level - ($exp_needed - $player->exp)) / $player->exp_per_level * 100;
		if($exp_percent < 0) {
			$exp_percent = 0;
		}
		else if($exp_percent > 100) {
			$exp_percent = 100;
		}	
		$exp_width = round($exp_percent * 2);
		
		
		
		$health_percent = round(($player->health / $player->max_health) * 100);
		$chakra_percent = round(($player->chakra / $player->max_chakra) * 100);
		$stamina_percent = round(($player->stamina / $player->max_stamina) * 100);

		echo "<td style='width:50%;'>
		<label style='width:6.7em;'>Health:</label>" . 
			sprintf("%.2f", $player->health) . '/' . sprintf("%.2f", $player->max_health) . "<br />" .
			"<div style='height:6px;width:250px;border-style:solid;border-width:1px;'>" .
			"<div style='background-color:#C00000;height:6px;width:" . $health_percent . "%;' /></div>" . "</div>" .
		"<label style='width:6.7em;'>Chakra:</label>" . 
			sprintf("%.2f", $player->chakra) . '/' . sprintf("%.2f", $player->max_chakra) . "<br />" .
			"<div style='height:6px;width:250px;border-style:solid;border-width:1px;'>" .
			"<div style='background-color:#0000B0;height:6px;width:" . $chakra_percent . "%;' /></div>" . "</div>" .
		"<label style='width:6.7em;'>Stamina:</label>" . 
			sprintf("%.2f", $player->stamina) . '/' . sprintf("%.2f", $player->max_stamina) . "<br />" .
			"<div style='height:6px;width:250px;border-style:solid;border-width:1px;'>" .
			"<div style='background-color:#00B000;height:6px;width:" . $stamina_percent . "%;' /></div>" . "</div>" .
		"<br />
		Regeneration rate: " . $player->regen_rate;
		$regen_cut = 0;
		if($player->battle_id or isset($_SESSION['ai_id'])) {
			$regen_cut = round(($player->regen_rate + $player->regen_boost) * 0.7, 1);
		}
		
		if($player->regen_boost) {
			echo " (+" . $player->regen_boost . ") " . ($regen_cut ? "<span style='color:#8A0000;'>(-{$regen_cut})</span> " : "") .
			"-> <span style='color:#00C000;'>" . ($player->regen_rate + $player->regen_boost - $regen_cut) . "</span>";
		}
		else if(isset($regen_cut)) {
		
		}
              echo "<br />";

              // First attempt:
              // echo "<label style='width:9.2em;'>Regen Timer:</label>" . (time() - $player->last_update - 60) * -1;

              $time_since_last_regen = time() - $player->last_update;
              echo "<label style='width:9.2em;'>Regen Timer:</label>" . (60 - $time_since_last_regen) .
              "</td></tr>";
		
		$exp_remaining = $exp_needed - $player->exp;
		if($exp_remaining < 0) {
			$exp_remaining = 0;
		}	
		$label_width = '7.1em';
		$clan_positions = array(
					1 => 'Leader',
					2 => 'Elder 1',
					3 => 'Elder 2',
		);
		echo "<tr><td style='width:50%;'>
		<label style='width:$label_width;'>Level:</label> $player->level<br />
		<label style='width:$label_width;'>Rank:</label> $player->rank_name<br />" .
		($player->clan ? "
			<label style='width:$label_width;'>Clan:</label> {$player->clan['name']}
			<br /> " .
			($player->clan_office ? "
			<label style='width:$label_width;'>Clan Rank:</label> {$clan_positions[$player->clan_office]}
			<br />" : '') . "
		" : '') .
		"<label style='width:$label_width;'>Exp:</label> $player->exp<br />
		<label style='width:$label_width;'>Next level in:</label> " . $exp_remaining . " exp<br />
		<div style='height:6px;width:200px;border-style:solid;border-width:1px;'>" .
			"<div style='background-color:#FFD700;height:6px;width:" . $exp_width . "px;' /></div>" . "</div>" .
		"<br />
		<label style='width:$label_width;'>Gender:</label> $player->gender<br />
		<label style='width:$label_width;'>Village:</label> $player->village<br />
		<label style='width:$label_width;'>Location:</label> $player->location<br />
		<label style='width:$label_width;'>Money:</label> &yen;" . $player->money . "<br />
		<label style='width:$label_width;'>Ancient Kunai:</label> " . $player->premium_credits . "<br />
		<label style='width:$label_width;'>Ancient Kunai purchased:</label> " . $player->premium_credits_purchased . "<br /> 
		
		<br />
		<label style='width:$label_width;'>PvP wins:</label>		$player->pvp_wins<br />
		<label style='width:$label_width;'>PvP losses:</label> 	$player->pvp_losses<br />
		<label style='width:$label_width;'>AI wins:</label>		$player->ai_wins<br />
		<label style='width:$label_width;'>AI losses:</label>		$player->ai_losses<br />
		</td>
		
		<td style='width:50%;'>
		<label style='width:9.2em;'>Total stats:</label>" . 
			sprintf("%.2f", $player->total_stats) . '/' . sprintf("%.2f", $player->stat_cap) . "<br />
		<br />
		<label style='width:9.2em;'>Bloodline:</label>" . ($player->bloodline_id ? $player->bloodline_name : 'None') . "</br />";
		if($player->bloodline_id) {
			echo "<label style='width:9.2em;'>Bloodline skill:</label>$player->bloodline_skill</label><br />";
		}
		
		if($player->elements) {
			echo "<br /><label style='width:9.2em;'>Element" . (count($player->elements) > 1 ? 's' : '') . ":</label>" . implode(', ', $player->elements) . "</label><br />";
		}
		
		echo "<br />
		 <label style='width:9.2em;'>Ninjutsu skill:</label>" . $player->ninjutsu_skill . "<br />
		<label style='width:9.2em;'>Genjutsu skill:</label>" . $player->genjutsu_skill . "<br />
		<label style='width:9.2em;'>Taijutsu skill:</label>" . $player->taijutsu_skill . "<br />
		<br />
		<label style='width:9.2em;'>Cast speed:</label>" . sprintf("%.2f", $player->cast_speed) . "<br />
		<label style='width:9.2em;'>Speed:</label>" . sprintf("%.2f", $player->speed) . "<br />
		<label style='width:9.2em;'>Intelligence:</label>" . sprintf("%.2f", $player->intelligence) . "<br />
		<label style='width:9.2em;'>Willpower:</label>" . sprintf("%.2f", $player->willpower) . "<br />
		</td></tr></table>";
	}
}
