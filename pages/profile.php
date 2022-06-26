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
    global $system;

    global $player;
    global $self_link;

    // Submenu
    renderProfileSubmenu();

    // Level up/rank up checks
    $exp_needed = $player->expForNextLevel();

    // Level up
    if($player->level < $player->max_level && $player->exp >= $exp_needed) {
        if($player->battle_id) {
            echo "<p style='text-align:center;font-style:italic;'>
				You must be out of battle to level up.</p>";
        }
        else {
            require("levelUp.php");
            levelUp();
            $exp_needed = $player->expForNextLevel();
        }
    }
    // Rank up
    else if($player->level >= $player->max_level && $player->exp >= $exp_needed && $player->rank < System::SC_MAX_RANK) {
        if($player->battle_id > 0 or !$player->in_village) {
            echo "<p style='text-align:center;font-style:italic;'>
				You must be out of battle and in your village to rank up.</p>";
        }
        else {
            if($player->exam_stage) {
                $prompt = "Resume exam for the next rank";
            }
            else {
                $prompt = "Take exam for the next rank";
            }

            echo "<p style='text-align:center;font-size:1.1em;'>
				<a class='button' style='padding:5px 10px 4px;margin-bottom:0;text-decoration:none;' href='{$system->links['rankup']}'>{$prompt}</a>
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
                if($player->rank > 1) {
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
            if(!$system->db_last_num_rows) {
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
            if($amount <= 0 && !$player->isHeadAdmin()) {
                throw new Exception("Invalid amount!");
            }
            if($amount > $player->$type) {
                throw new Exception("You do not have that much money/AK!");
            }
            $player->$type -= $amount;
            $system->query("UPDATE `users` SET `{$type}`=`{$type}` + $amount WHERE `user_id`='{$recipient['user_id']}' LIMIT 1");
            if($type == 'money') {
                $system->log(
                    'money_transfer',
                    'Money Sent',
                    "{$amount} yen - #{$player->user_id} ($player->user_name) to #{$recipient['user_id']}"
                );
                $system->send_pm('Currency Transfer System', $recipient['user_id'], 'Money Received', $player->user_name . " has sent you &yen;$amount.");
            }
            else {
                $system->log(
                    'premium_credit_transfer',
                    'Premium Credits Sent',
                    "{$amount} AK - #{$player->user_id} ($player->user_name) to #{$recipient['user_id']}"
                );
                $system->send_pm('Currency Transfer System', $recipient['user_id'], 'AK Received', $player->user_name . " has sent you $amount Ancient Kunai.");
            }

            $system->message("Currency sent!");
        } catch(Exception $e) {
            $system->message($e->getMessage());
        }
        $system->printMessage();
    }
    if($page == 'send_money' || $page == 'send_ak') {
        $type = ($page == 'send_money') ? "Money" : "AK";
        $currency = ($type == 'Money') ? "money" : "premium_credits";
        $hidden = ($type == 'Money') ? "yen" : "kunai";

        $recipient = !empty($_GET['recipient']) ? $_GET['recipient'] : '';

        echo "<table class='table'><tr><th>Send {$type}</th></tr>
		<tr><td style='text-align:center;'>
		<form action='{$self_link}&page={$page}' method='post'>
		<b>Your {$type}:</b> {$player->$currency}<br />
		<br />
		Send {$type} to:<br />
		<input type='hidden' name='{$hidden}' value='1'/>
		<input type='text' name='recipient' value='{$recipient}' /><br />
		Amount:<br />
		<input type='text' name='amount' /><br />
		<input type='submit' name='send_currency' value='Send {$type}' />
		</form>
		</td></tr></table>";
    }
    else if($page == 'profile') {
        echo "<table class='profile_table table'>

		<tr><td style='width:50%;text-align:center;'>
		<span style='font-size:1.3em;font-family:\"tempus sans itc\";font-weight:bold;'>" . $player->user_name . "</span><br />
		" . $system->imageCheck($player->avatar_link, $player->getAvatarSize()) . "
		<br />
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
            "<span id='health'>" . sprintf("%.2f", $player->health) . '/' . sprintf("%.2f", $player->max_health) . "</span><br />" .

            "<div style='height:6px;width:250px;border-style:solid;border-width:1px;'>" .
            "<div id='healthbar' style='background-color:#C00000;height:6px;width:" . $health_percent . "%;' /></div>" . "</div>" .
            "<label style='width:6.7em;'>Chakra:</label>" .
            "<span id='chakra'>" . sprintf("%.2f", $player->chakra) . '/' . sprintf("%.2f", $player->max_chakra) . "</span><br />" .

            "<div style='height:6px;width:250px;border-style:solid;border-width:1px;'>" .
            "<div id='chakrabar' style='background-color:#0000B0;height:6px;width:" . $chakra_percent . "%;' /></div>" . "</div>" .
            "<label style='width:6.7em;'>Stamina:</label>" .
            "<span id='stamina'>" . sprintf("%.2f", $player->stamina) . '/' . sprintf("%.2f", $player->max_stamina) . "</span><br />" .

            "<div style='height:6px;width:250px;border-style:solid;border-width:1px;'>" .
            "<div id='staminabar' style='background-color:#00B000;height:6px;width:" . $stamina_percent . "%;' /></div>" . "</div>" .
            "<br />
		Regeneration Rate: " . $player->regen_rate;

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

        //regen timer script - can be moved to its own script.js file
        echo "
		<script>
		var remainingtime = " . (59 - $time_since_last_regen) . ";

		var health = {$player->health};
		var max_health = {$player->max_health};

		var chakra = {$player->chakra};
		var max_chakra = {$player->max_chakra};

		var stamina = {$player->stamina};
		var max_stamina = {$player->max_stamina};

		var regen = {$player->regen_rate} + {$player->regen_boost}; //no regen cut

		setInterval(() => {

			document.getElementById('regentimer').innerHTML = remainingtime; //minus 1 to compensate for lag


			if(remainingtime <= 0){
				remainingtime = 60;

				if((health + regen) >= max_health){
					health = max_health;
				} else {
					health += regen; //health ignores regen boost
				}

				if((chakra + regen) >= max_chakra){
					chakra = max_chakra;
				} else {
					chakra += regen;
				}

				if((stamina + regen) >= max_stamina){
					stamina = max_stamina;
				} else {
					stamina += regen;
				}

			//update health amounts / bars
			document.getElementById('health').innerHTML = health.toFixed(2) + '/' + max_health.toFixed(2);
			document.getElementById('healthbar').style.width = ( health / max_health )*100 + '%';

			document.getElementById('chakra').innerHTML = chakra.toFixed(2) + '/' + max_chakra.toFixed(2);
			document.getElementById('chakrabar').style.width = ( chakra / max_chakra )*100 + '%';

			document.getElementById('stamina').innerHTML = stamina.toFixed(2) + '/' + max_stamina.toFixed(2);
			document.getElementById('staminabar').style.width = ( stamina / max_stamina )*100 + '%';
			}

			remainingtime--;

		}, 1000);

		//for some reason every other tick the javascript regen is ahead of the actual regen?
		//can't figure out why? its like the Regen changes every other minute in intervals or it doubles
		//can't find the error with my script
		//can't seem to find out where the error is, need help.

		</script>
		";

        echo "<label style='width:9.2em;'>Regen Timer:</label>
		<span id='regentimer'>" . (60 - $time_since_last_regen) . "</span>


		</td>
		</tr>";

        $exp_remaining = $exp_needed - $player->exp;
        if($exp_remaining < 0) {
            $exp_remaining = 0;
        }
        $label_width = '7.1em';
        $clan_positions = [
            1 => 'Leader',
            2 => 'Elder 1',
            3 => 'Elder 2',
        ];
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
		
		<label style='width:$label_width;'>Spouse:</label> ";
        if($player->spouse > 0) {
            echo "<a href='{$system->links['members']}&user={$player->spouse_name}'>" . $player->spouse_name . "</a><br />
            <label style='width:$label_width;'>Anniversary:</label> " . Date('F j, Y', $player->marriage_time) . "<br />";
        }
        else {
            echo "None<br />";
        }

		echo "<br />
        <label style='width:$label_width;'>Gender:</label> $player->gender<br />
		<label style='width:$label_width;'>Village:</label> $player->village<br />
		<label style='width:$label_width;'>Location:</label> $player->location<br />
		<label style='width:$label_width;'>Money:</label> &yen;" . $player->money . "<br />
		<label style='width:$label_width;'>Ancient Kunai:</label> " . $player->premium_credits . "<br />
		<label style='width:$label_width;'>Ancient Kunai purchased:</label> " . $player->premium_credits_purchased . "<br />

		<br />
		<label style='width:$label_width;'>PvP wins:</label>		$player->pvp_wins<br />
		<label style='width:$label_width;'>PvP losses:</label> 	$player->pvp_losses<br />
		<label style='width:$label_width;'>NPC wins:</label>		$player->ai_wins<br />
		<label style='width:$label_width;'>NPC losses:</label>		$player->ai_losses<br />
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
		<br />
		<b>Missions Completed:</b><br />
		&nbsp;&nbsp;<label style='width:5em;'>" . Mission::$rank_names[Mission::RANK_D] . ":</label>
		    " . (($player->missions_completed[Mission::RANK_D]) ??'0') . "
		    <br />
		&nbsp;&nbsp;<label style='width:5em;'>" . Mission::$rank_names[Mission::RANK_C] . ":</label>
		    " . (($player->missions_completed[Mission::RANK_C]) ?? '0') . "
		    <br />
		&nbsp;&nbsp;<label style='width:5em;'>" . Mission::$rank_names[Mission::RANK_B] . ":</label>
		    " . (($player->missions_completed[Mission::RANK_B]) ?? '0') . "
		    <br />
		&nbsp;&nbsp;<label style='width:5em;'>" . Mission::$rank_names[Mission::RANK_A] . ":</label>
		    " . (($player->missions_completed[Mission::RANK_A]) ?? '0') . "
		    <br />
		&nbsp;&nbsp;<label style='width:5em;'>" . Mission::$rank_names[Mission::RANK_S] . ":</label>
		    " . (($player->missions_completed[Mission::RANK_S]) ?? '0') . "
		    <br />
		</td></tr></table>";

        echo "
		<div class='contentDiv'>
			<h2 class='contentDivHeader'>Daily Tasks</h2>

			<div id='dailyTaskWrapper'>";

        foreach($player->daily_tasks as $daily_task) {

            $dt_progress = 0;
            if($daily_task->progress != 0) {
                $dt_progress = $daily_task->progress / $daily_task->amount * 100;
            }
            $dt_status_class_name = ($daily_task->complete ? 'Complete' : 'NotComplete');

            echo "
				<div class='dailyTask'>
					<div class='dailyTaskTitle'>
						" . $daily_task->name . "
					</div>
					<div class='dailyTaskGoal'>
						<span>Task:</span>
						<span>" . $daily_task->getPrompt() . "</span>
					</div>
					<div class='dailyTaskDifficulty'>
						<span>Difficulty:</span>
						<span class='dailyTask" . $daily_task->difficulty . "'>" . $daily_task->difficulty . "</span>
					</div>
					<div class='dailyTaskReward'>
						<span>Reward:</span>
						<span>¥" . $daily_task->reward . "</span>
					</div>
					<div class='dailyTaskProgress'>
						<div class='dailyTaskProgressBar dailyTask" . $dt_status_class_name . "'>
							<div style='width: " . $dt_progress . "%;'></div>
						</div>
					</div>
					<div class='dailyTaskProgressCaption'>
						<span>" . $daily_task->progress . "</span> / <span>" . $daily_task->amount . "</span>
					</div>
				</div>";
        }

        $dt_time_remaining = System::timeRemaining($player->daily_tasks_reset + (60 * 60 * 24) - time(), 'short', false, true);

        echo "</div>
			
			<div class='contentDivCaption'>
				<span>Time Remaining:</span>
				<span id='dailyTaskTimer'>" . $dt_time_remaining . " left
				</span>
			</div>

			<script type='text/javascript'>
				let stringValue = " . ($player->daily_tasks_reset + (60 * 60 * 24) - time()) . ";
				let targetSpan = document.getElementById('dailyTaskTimer');
				setInterval(() => {
					stringValue--;
					let stringTime = timeRemaining(stringValue, 'short', false, true);
					targetSpan.innerHTML = stringTime + ' left';
				}, 1000);
			</script>
		</div>";
    }
}

function renderProfileSubmenu() {
    global $system;
    global $player;
    global $self_link;

    $submenu_links = [
        [
            'link' => $system->links['profile'],
            'title' => 'Character',
        ],
        [
            'link' => $system->links['settings'],
            'title' => 'Settings',
        ],
    ];
    if($player->rank > 1) {
        $submenu_links[] = [
            'link' => $system->links['profile'] . "&page=send_money",
            'title' => 'Send Money',
        ];
        $submenu_links[] = [
            'link' => $system->links['profile'] . "&page=send_ak",
            'title' => 'Send AK',
        ];
    }
    if($player->bloodline_id) {
        $submenu_links[] = [
            'link' => $system->links['bloodline'],
            'title' => 'Bloodline',
        ];
    }

    echo "<div class='submenu'>
    <ul class='submenu'>";
    $submenu_link_width = round(100 / count($submenu_links), 1);
    foreach($submenu_links as $link) {
        echo "<li style='width:{$submenu_link_width}%;'><a href='{$link['link']}'>{$link['title']}</a></li>";
    }
    echo "</ul>
    </div>
    <div class='submenuMargin'></div>
    ";
}
