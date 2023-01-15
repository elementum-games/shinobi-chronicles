<?php
/* arena.php
*/
function arena() {
	global $system;
	global $player;
	global $self_link;

	if($player->exam_stage > 0) {
		$system->message("You cannot access this page during the exam!");
		$system->printMessage();
		return false;
	}

	if($player->battle_id) {
        arenaFight();
	}
	else {
		$result = $system->query("SELECT `ai_id`, `name`, `level` FROM `ai_opponents`
			WHERE `rank` ='$player->rank' ORDER BY `level` ASC");

		// Addition by Kengetsu - Get access to AI if rank is higher than public max.
		if($player->rank > System::SC_MAX_RANK) {
			$result = $system->query("SELECT `ai_id`, `name`, `level` FROM `ai_opponents`
			WHERE `rank` ='" . System::SC_MAX_RANK . "' ORDER BY `level` ASC");
		}
		//End
		if($system->db_last_num_rows == 0) {
			$system->message("No AI opponents found!");
			$system->printMessage();
			return false;
		}
		$ai_opponents = array();
		while($row = $system->db_fetch($result)) {
			$ai_opponents[$row['ai_id']] = $row;
		}
		$fight_start = false;
		$fight_timer = 20;
		if(!empty($_GET['fight'])) {
			if($player->last_ai > time() - $fight_timer) {
				$system->message("Please wait " . ($player->last_ai - (time() - $fight_timer)) . " more seconds!");
			}
			else if(isset($ai_opponents[$_GET['fight']])) {


                try {
                    $ai_id = $_GET['fight'];
                    $ai = new AI($system, $ai_id);
                    $ai->loadData();
                    $ai->health = $ai->max_health;

                    $player->last_ai = time();
                    Battle::start($system, $player, $ai, Battle::TYPE_AI_ARENA);

                    arena();
                    $player->log(User::LOG_ARENA, "Opponent {$ai->id} ({$ai->getName()})");
                    return true;
                } catch(Exception $e) {
                    $system->message("Invalid opponent!");
                    $system->printMessage();
                }
			}
			else {
				$system->message("Invalid opponent!");
				$system->printMessage();
			}
		}

        $system->printMessage();
        echo "<table class='table'><tr><th>Choose Opponent</th></tr>
        <tr><td style='text-align: center;'>
        Welcome to the Arena. Here you can fight against various opponents for cash prizes. Please select your opponent below:
        </td></tr>
        <tr><td style='text-align: center;'>";
        foreach($ai_opponents as $ai) {
            echo "<a href='$self_link&fight={$ai['ai_id']}'>
                 <p class='button' style='margin-top:5px;'>" . $ai['name'] .
                    " <span style='font-weight:normal;'>(Level {$ai['level']})</span></p></a><br />";
        }
        echo "</td></tr></table>";
	}
}

function arenaFight(): bool {
    global $system;
    global $player;

    $stat_gain_chance = 26;

    try {
        $battle = new BattleManager($system, $player, $player->battle_id);

        $winner = $battle->checkTurn();

        $battle->renderBattle();

        if(!$battle->isComplete()) {
            return true;
        }
        else if($winner == $battle->player_side) {
            $stat_gain_display = false;
            $opponent = $battle->opponent;
            $money_gain = $battle->opponent->money;

            if($player->level > $opponent->level) {
                $level_difference = $player->level - $opponent->level;
                if($level_difference > 9) {
                    $level_difference = 9;
                }
                $money_gain = round($money_gain * (1 - $level_difference * 0.1));
                if($money_gain < 5) {
                    $money_gain = 5;
                }
            }
            // Stat gain
            if($player->level <= $opponent->level + 1) {
                $counts = array(
                    'bloodline' => 0,
                    'ninjutsu' => 0,
                    'taijutsu' => 0,
                    'genjutsu' => 0
                );
                $total_count = 0;
                if(is_array($battle->player_jutsu_used)) {
                    foreach(($battle->player_jutsu_used) as $id => $jutsu) {
                        if(strpos($id, 'BL_J') !== false) {
                            $counts['bloodline'] += $jutsu['count'];
                        }
                        else if($jutsu['jutsu_type'] == 'ninjutsu') {
                            $counts['ninjutsu'] += $jutsu['count'];
                        }
                        else if($jutsu['jutsu_type'] == 'taijutsu') {
                            $counts['taijutsu'] += $jutsu['count'];
                        }
                        else if($jutsu['jutsu_type'] == 'genjutsu') {
                            $counts['genjutsu'] += $jutsu['count'];
                        }
                        $total_count += $jutsu['count'];
                    }
                }

                if($opponent->level >= $player->level) {
                    $stat_gain_chance += 10;
                }
                if($total_count > 4) {
                    $stat_gain_chance += 15;
                }
                if($player->total_stats < $player->stat_cap && $stat_gain_chance >= mt_rand(1, 100)) {
                    $stat = '';
                    $highest_count = 0;
                    $highest_used_stats = array();
                    foreach($counts as $id => $count) {
                        if($count > $highest_count) {
                            $highest_count = $count;
                            $highest_used_stats = array($id);
                        }
                        else if($count == $highest_count) {
                            $highest_used_stats[] = $id;
                        }
                    }

                    // Ninjutsu
                    if(count($highest_used_stats) == 1) {
                        $stat = $highest_used_stats[0] . '_skill';
                    }
                    // Tie
                    else {
                        $highest_stat_num = 0;
                        $highest_stat = '';
                        foreach($highest_used_stats as $tied_stat) {
                            if($player->{$tied_stat . '_skill'} > $highest_stat_num) {
                                $highest_stat_num = $player->{$tied_stat . '_skill'};
                                $highest_stat = $tied_stat;
                            }
                        }
                        $stat = $highest_stat . '_skill';
                    }
                    $player->{$stat} += 1;
                    $player->exp += 10;
                    $stat_gain_display = '<br />During the fight you realized a way to use your ' . ucwords(explode('_', $stat)[0]) . ' a little
						more effectively.
						<br />You have gained 1 ' . ucwords(str_replace('_', ' ', $stat)) . '.';
                }
            }

            // TEAM BOOST AI GAINS
            if($player->team != null) {
                $boost_percent = $player->team->getAIMoneyBoostAmount();
                if($boost_percent != null) {
                    $boost_amount = ceil($boost_percent * $money_gain);
                    $money_gain += $boost_amount;
                }
            }

            $player->addMoney($money_gain, 'arena');

            echo "<table class='table'><tr><th>Battle Results</th></tr>
			<tr><td>You have defeated your arena opponent.<br />
			You have claimed your prize of &yen;$money_gain.";
            if($stat_gain_display) {
                echo $stat_gain_display;
            }
            echo "</td></tr></table>";
            $player->ai_wins++;
            $player->battle_id = 0;
            $player->last_pvp = time();

            // Daily Tasks
            foreach ($player->daily_tasks as $task) {
                if (!$task->complete && $task->activity == DailyTask::ACTIVITY_ARENA && $task->sub_task == DailyTask::SUB_TASK_WIN_FIGHT) {
                    $task->progress++;
                }
            }
        }
        else if($winner == $battle->opponent_side) {
            echo "<table class='table'><tr><th>Battle Results</th></tr>
			<tr><td>You have been defeated.
			</td></tr></table>";
			$player->health = 5;
            $player->ai_losses++;
			$player->moveToVillage();
            $player->battle_id = 0;
            $player->last_pvp = time();

            // Daily Tasks
            foreach ($player->daily_tasks as &$task) {
                if (!$task->complete && $task->activity == DailyTask::ACTIVITY_ARENA && $task->sub_task == DailyTask::SUB_TASK_COMPLETE) {
                    $task->progress++;
                }
            }
        }
        else if($winner == Battle::DRAW) {
            echo "<table class='table'><tr><th>Battle Results</th></tr>
			<tr><td>The battle ended in a draw. You receive no reward.
			</td></tr></table>";
			$player->health = 5;
			$player->moveToVillage();
            $player->battle_id = 0;
            $player->last_pvp = time();

            // Daily Tasks
            foreach ($player->daily_tasks as $task) {
                if (!$task->complete && $task->activity == DailyTask::ACTIVITY_ARENA && $task->sub_task == DailyTask::SUB_TASK_COMPLETE) {
                    $task->progress++;
                }
            }
        }
    } catch(Exception $e) {
        $system->message($e->getMessage());
        $system->printMessage();
        return false;
    }
    return true;
}
