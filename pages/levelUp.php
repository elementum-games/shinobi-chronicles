<?php

function levelUp() {
	global $player;
	
	if($player->level < $player->max_level) {
		$player->level++;
		$player->exp = $player->total_stats * 10;

		$player->max_health += $player->health_gain;
		$player->max_chakra += $player->pool_gain;
		$player->max_stamina += $player->pool_gain;
		
		$player->health = $player->max_health;
		$player->chakra = $player->max_chakra;
		$player->stamina = $player->max_stamina;
		
		echo "<table class='table'><tr><th>Level Up</th></tr>
		<tr><td style='text-align:center;'>
		You have leveled up to level $player->level, gaining $player->health_gain health and $player->pool_gain chakra/stamina!
		</td></tr></table>";
	}
}

/**
 * @return bool
 * @throws Exception
 */
function rankUp(): bool {
	global $system;

	global $player;
	
	$self_link = $system->links['rankup'];

	if($player->exam_stage > 0 && !empty($_GET['abandon_exam'])) {
	    $player->exam_stage = 0;

        $system->message("Understanding that this is not your time, you bow out of the exam. Maybe next time
            will be your chance!<br />
            <a href='{$system->links['profile']}'>Continue</a>");
        $system->printMessage();
        return true;
    }

	// Can't even access this page if in a different type of battle
	if($player->battle_id && $player->exam_stage == 0) {
	    $player->battle_id = 0;
        $system->message("Your exam battle has concluded. <a href='{$system->links['profile']}'>Continue</a>");
        return true;
    }

    if($player->level < $player->max_level || $player->exp < $player->expForNextLevel() || $player->rank >= System::SC_MAX_RANK) {
        $player->exam_stage = 0;
        $system->message("You are not eligible for a rankup exam. <a href='{$system->links['profile']}'>Continue</a>");
        return true;
    }

    echo "<div class='submenu'>
        <ul class='submenu'>
            <li style='width:100%;'><a href='{$self_link}&abandon_exam=1'>Abandon Exam</a></li>
        </ul>
    </div>
    <div class='submenuMargin'></div>";

    $rankManager = new RankManager($system);
    $rankManager->loadRanks();

	// Akademi-sei -> Genin
	if($player->rank == 1) {
        geninExam($system, $player, $rankManager);
	}
	// Genin -> Chuunin
	else if($player->rank == 2) {
        chuuninExam($system, $player, $rankManager);
	}
	// Chuunin -> Jonin
	else if($player->rank == 3) {
		joninExam($system, $player, $rankManager);
	}

	return true;
}

function geninExam(System $system, User $player, RankManager $rankManager) {
    global $self_link;

    $new_rank = $player->rank + 1;
    $bloodline_roll_chance = System::BLOODLINE_ROLL_CHANCE;

    $player->getInventory();

    if(!$player->exam_stage) {
        $player->exam_stage = 1;
    }

    $replacement_jutsu_id = 4;
    $clone_jutsu_id = 87;
    $transform_jutsu_id = 12;
    $jutsu_ids = implode(",", [$replacement_jutsu_id, $clone_jutsu_id, $transform_jutsu_id]);

    $result = $system->query("SELECT `jutsu_id`, `name`, `hand_seals` FROM `jutsu` WHERE `jutsu_id` IN({$jutsu_ids})");
    $jutsu_data = array();
    $count = 1;
    while($row = $system->db_fetch($result)) {
        $jutsu_data[$count++] = $row;
    }

    // Input
    if(!empty($_POST['hand_seals'])) {
        $hand_seals = $_POST['hand_seals'];

        switch($player->exam_stage) {
            case 1:
                if($hand_seals == $jutsu_data[1]['hand_seals'] && $player->hasJutsu($jutsu_data[1]['jutsu_id'])) {
                    $player->exam_stage = 2;
                }
                else {
                    /*echo 'hand seals: ' . $hand_seals . ' / ' . $jutsu_data[1]['hand_seals'] . '<br />' .
                        'jutsu owned: ' . $player->checkInventory($jutsu_data[1]['jutsu_id'], 'equipped_jutsu') . '<br />';*/
                    $system->message("You attempted to perform " . $jutsu_data[1]['name'] . " but failed.");
                }
                break;
            case 2:
                if($hand_seals == $jutsu_data[2]['hand_seals'] && $player->hasJutsu($jutsu_data[2]['jutsu_id'])) {
                    $player->exam_stage = 3;
                }
                else {
                    $system->message("You attempted to perform " . $jutsu_data[2]['name'] . " but failed.");
                }
                break;
            case 3:
                if($hand_seals == $jutsu_data[3]['hand_seals'] && $player->hasJutsu($jutsu_data[3]['jutsu_id'])) {
                    $player->exam_stage = 4;
                }
                else {
                    $system->message("You attempted to perform " . $jutsu_data[3]['name'] . " but failed.");
                }
                break;
        }
    }

    // Display
    $system->printMessage();
    echo "<table class='table'><tr><th>" . $rankManager->ranks[$new_rank]->name . " Exam</th></tr>";
    $exam_instructions = '<tr><td>Welcome to the Genin Exam. In order to pass, you must demonstrate three jutsu: ' .
        $jutsu_data[1]['name'] . ', ' . $jutsu_data[2]['name'] . ', and ' . $jutsu_data[3]['name'] .
        '. See below for instructions on your current task.</td></tr>';

    $prompt = '';
    switch($player->exam_stage) {
        case 1:
            echo $exam_instructions;
            $prompt = 'Please demonstrate ' . $jutsu_data[1]['name'] . ':';
            break;
        case 2:
            echo $exam_instructions;
            $prompt = 'Please demonstrate ' . $jutsu_data[2]['name'] . ':';
            break;
        case 3:
            echo $exam_instructions;
            $prompt = 'Please demonstrate ' . $jutsu_data[3]['name'] . ':';
            break;
        case 4:
            break;
        default:
            echo 'lolwut';
            break;
    }

    if($player->exam_stage >= 1 && $player->exam_stage <= 3) {
        $gold_color = '#FDD017';
        echo "<tr><td>
			<div style='margin:0;position:relative;'>
			<style>
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
				margin: 0;
				text-align: center;
				height: 16px;
			}
			#handsealOverlay{
				width:100%;
				position:absolute;
				top:0;
				height:100%;
				background-color:rgba(255,255,255,0.9);
				z-index:50;
				display:none;
			}
			</style>
			<script type='text/javascript'>
			$(document).ready(function(){
				let hand_seals = [];
				
				$('#handSeals p img').click(function() {
					let parent = $(this).parent();
					let seal = parent.attr('data-handseal');
					
					// Select hand seal
					if(parent.attr('data-selected') === 'no') {
						parent.attr('data-selected', 'yes');
						$(this).css('border-color', '$gold_color');
						parent.children('.handsealNumber').show();
						
						hand_seals.splice(hand_seals.length, 0, seal);
					}
					// De-select handseal
					else if(parent.attr('data-selected') === 'yes') {
						parent.attr('data-selected', 'no');
						$(this).css('border-color', 'rgba(0,0,0,0)');
						parent.children('.handsealNumber').hide();
						
						for(let x in hand_seals) {
							if(hand_seals[x] === seal) {
								hand_seals.splice(x,1);
								break;
							}
						}
					}
					
				
					// Update display
					$('#hand_seal_input').val(hand_seals.join('-'));
					
					let id = '';
					for(let x in hand_seals) {
						id = 'handseal_' + hand_seals[x];
						$('#' + id).children('.handsealNumber').text((parseInt(x) + 1));
					}
				});
			});
			</script>
			
			<!--DIV START-->
			<p id='textPrompt' style='text-align:center;'>$prompt</p>
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
			<form action='$self_link' method='post'>
			<input type='hidden' id='hand_seal_input' name='hand_seals' value='{$_POST['hand_seals']}' />
			<p style='text-align:center;'>
				<input type='submit' name='attack' value='Submit' />
			</p>
			</form>
			</div>
			</td></tr>";
    }
    else if($player->exam_stage == 4) { // Pass
        try {
            $player->exam_stage = 0;
            $gender = $player->getPossessivePronoun();

            $bloodline_rolled = false;
            if(mt_rand(1, 99) < $bloodline_roll_chance) {
                $bloodline_rolled = true;
            }

            // Bloodline roll
            if($bloodline_rolled) {
                // Chances: 10% legendary, 20% elite, 40% common, 30% lesser
                $x = mt_rand(1, 100);
                if($x < 10) {
                    $bloodline_rank = 1;
                }
                else if($x < 30) {
                    $bloodline_rank = 2;
                }
                else if($x < 70) {
                    $bloodline_rank = 3;
                }
                else {
                    $bloodline_rank = 4;
                }

                // Delete current BL
                $system->query("DELETE FROM `user_bloodlines` WHERE `user_id`='$player->user_id'");
                $system->query("UPDATE `users` SET `bloodline_id`='0' WHERE `user_id`='$player->user_id'");

                // Pull bloodlines
                $result = $system->query("SELECT `bloodline_id`, `clan_id`, `name` FROM `bloodlines` 
						WHERE `village`='$player->village' AND `rank`='$bloodline_rank'");
                if($system->db_last_num_rows == 0) {
                    $result = $system->query("SELECT `bloodline_id`, `clan_id`, `name` FROM `bloodlines` 
						WHERE `village`='$player->village' AND `rank` < 5");
                }

                if($system->db_last_num_rows == 0) {
                    $bloodline_rolled = false;
                }

                // If no bloodlines were found, this flag will have been set to false
                if($bloodline_rolled) {
                    $bloodlines = array();
                    $count = 0;
                    while($row = $system->db_fetch($result)) {
                        $bloodlines[$row['bloodline_id']] = $row;
                        $count++;
                    }

                    $query = "SELECT ";
                    $x = 0;
                    foreach($bloodlines as $id => $bloodline) {
                        $query .= "SUM(IF(`bloodline_id` = $id, 1, 0)) as `$id`";
                        $x++;
                        if($x < $count) {
                            $query .= ', ';
                        }
                    }
                    $query .= " FROM `users`";

                    $bloodline_counts = array();
                    $result = $system->query($query);
                    $row = $system->db_fetch($result);
                    $total_users = 0;
                    foreach($row as $id => $user_count) {
                        $bloodline_counts[$id] = $user_count;
                        $total_users += $user_count;
                    }

                    $average_users = round($total_users / $count);

                    $bloodline_rolls = array();
                    foreach($bloodlines as $id => $bloodline) {
                        $entries = 4;
                        if($bloodline_counts[$id] > $average_users) {
                            $entries--;
                            if($bloodline_counts[$id] / 3 > $average_users) {
                                $entries--;
                            }


                        }
                        for($i = 0; $i < $entries; $i++) {
                            $bloodline_rolls[] = $id;
                        }
                    }

                    $bloodline_id = $bloodline_rolls[mt_rand(0, count($bloodline_rolls) - 1)];
                    $bloodline_name = $bloodlines[$bloodline_id]['name'];

                    $result = $system->query("SELECT `name` FROM `clans` WHERE `clan_id`='" . $bloodlines[$bloodline_id]['clan_id'] . "'");
                    if($system->db_last_num_rows > 0) {
                        $result = $system->db_fetch($result);
                        $clan_name = $result['name'];
                        $player->clan = array();
                        $player->clan['id'] = $bloodlines[$bloodline_id]['clan_id'];
                        $player->clan_office = 0;
                    }

                    // Give bloodline
                    require("adminPanel.php");
                    giveBloodline($bloodline_id, $player->user_id);
                }
            }

            // Clan roll(failsafe if no bloodlines were found on bl roll)
            if(!$bloodline_rolled) {
                $result = $system->query("SELECT `clan_id`, `name` FROM `clans` 
						WHERE `village`='$player->village' AND `bloodline_only`='0'");
                if($system->db_last_num_rows == 0) {
                    $result = $system->query("SELECT `clan_id`, `name` FROM `clans` 
						WHERE `bloodline_only`='0'");
                }

                if($system->db_last_num_rows == 0) {
                    throw new Exception("No clans available!");
                }

                $clans = array();
                $count = 0;
                while($row = $system->db_fetch($result)) {
                    $clans[$row['clan_id']] = $row;
                    $count++;
                }

                $query = "SELECT ";
                $x = 0;
                foreach($clans as $id => $clan) {
                    $query .= "SUM(IF(`clan_id` = $id, 1, 0)) as `$id`";
                    $x++;
                    if($x < $count) {
                        $query .= ', ';
                    }
                }
                $query .= " FROM `users`";

                $clan_counts = array();
                $result = $system->query($query);
                $row = $system->db_fetch($result);
                $total_users = 0;
                foreach($row as $id => $user_count) {
                    $clan_counts[$id] = $user_count;
                    $total_users += $user_count;
                }

                $average_users = round($total_users / $count);

                $clan_rolls = array();
                foreach($clans as $id => $clan) {
                    $entries = 4;
                    if($clan_counts[$id] > $average_users) {
                        $entries--;
                        if($clan_counts[$id] / 3 > $average_users) {
                            $entries--;
                        }


                    }
                    for($i = 0; $i < $entries; $i++) {
                        $clan_rolls[] = $id;
                    }
                }

                $clan_id = $clan_rolls[mt_rand(0, count($clan_rolls) - 1)];


                $player->clan = array();
                $player->clan['id'] = $clan_id;
                $clan_name = $clans[$clan_id]['name'];
            }

            $rankManager->increasePlayerRank($player);

            // Display
            echo "<tr><td>Congratulations, you have passed the Genin Exam!<br />
				<br />
				Now that you are able to perform ninjutsu, the village elders examine you to determine if you have a special power, a 
				Kekkei Genkai (Bloodline Limit ability). Closing $gender eyes an elder places $gender hand on your stomach
				and flows $gender chakra into you, resonating it with the core of your being to test for a bloodline.<br /><br />";

            if($bloodline_rolled) {
                echo "Suddenly the elder senses something inside of you, and you feel a surge in power
					as a strange force wells up inside you, answering the call of the elder's chakra. The elder withdraws $gender hand
					and smiles, saying calmly 'You have the <b>$bloodline_name</b>. This is the 
					bloodline of the <b>$clan_name</b> clan. From this day forward you shall join them, and study with them to train
					your bloodline to its fullest potential.'<br />
					<p style='text-align:center;'><a href='{$system->links['profile']}'>Continue</a></p>
					</td></tr>";
            }
            else {
                echo "After focusing deeply for several minutes, the elder withdraws $gender hand and says 'It appears you do not
					have a bloodline. You are free to train as you wish, we will place you in the <b>$clan_name</b> clan. Train 
					hard with them and become a strong ninja for your clan and village.'<br />
					<p style='text-align:center;'><a href='{$system->links['profile']}'>Continue</a></p>
					</td></tr>";
            }
        } catch(Exception $e) {
            echo "<tr><td style='text-align:center;'>" . $e->getMessage() . "</td></tr>";
        }
    }
    echo "</table>";
}

$CHUUNIN_STAGE_WRITTEN = 1;
$CHUUNIN_STAGE_SURVIVAL_START = 2;
$CHUUNIN_STAGE_SURVIVAL_MIDDLE = 2;
$CHUUNIN_STAGE_SURVIVAL_END = 4;
$CHUUNIN_STAGE_DUEL = 5;
$CHUUNIN_STAGE_PASS = 6;

/**
 * @throws Exception
 */
function chuuninExam(System $system, User $player, RankManager $rankManager): bool {
    global $self_link;

    global $CHUUNIN_STAGE_WRITTEN;
    global $CHUUNIN_STAGE_SURVIVAL_START;
    global $CHUUNIN_STAGE_SURVIVAL_MIDDLE;
    global $CHUUNIN_STAGE_SURVIVAL_END;
    global $CHUUNIN_STAGE_DUEL;
    global $CHUUNIN_STAGE_PASS;

    if(!$player->exam_stage) {
        $player->exam_stage = 1;
    }

    // Input
    if(!empty($_POST)) {
        if($player->exam_stage == $CHUUNIN_STAGE_WRITTEN) {
            $answer1 = $_POST['question1'];
            $answer2 = $_POST['question2'];
            $answer3 = $_POST['question3'];

            try {
                // Question 1 - Illusion jutsu is what type
                if($answer1 != 'genjutsu') {
                    throw new Exception('');
                }

                // Question 2 - Armor protects against what
                if($answer2 != 'taijutsu') {
                    throw new Exception('');
                }

                // Question 3 - Most villagers
                $result = $system->query("SELECT `name` FROM `villages`");
                $villages = array();
                while($row = $system->db_fetch($result)) {
                    $villages[] = $row;
                }

                $count_query = "SELECT ";
                foreach($villages as $id => $village) {
                    $count_query .= "COUNT(IF(`village` = '{$village['name']}', 1, NULL)) AS `" . $village['name'] . "`";
                    if($id < count($villages) - 1) {
                        $count_query .= ', ';
                    }
                }
                $count_query .= " FROM `users`";
                $result = $system->query($count_query);
                $village_counts = $system->db_fetch($result);
                $highest_village = 'Stone';
                foreach($village_counts as $id => $village) {
                    if($village > $village_counts[$highest_village]) {
                        $highest_village = $id;
                    }
                }

                if($answer3 != $highest_village) {
                    throw new Exception('');
                }

                $player->exam_stage = $CHUUNIN_STAGE_SURVIVAL_START;
                $system->message("You have passed stage 1!");
            } catch (Exception $e) {
                $system->message("Your answers were incorrect. You have failed the Chuunin exam. " . $e->getMessage() .
                    "<a href='{$system->links['profile']}'>Continue</a>");
                $system->printMessage();
                $player->exam_stage = 0;
                return false;
            }
            $system->printMessage();
        }
    }

    // Display
    $system->printMessage();
    echo "<table class='table'><tr><th>CHUUNIN EXAM</th></tr>";
    echo '<tr><td>Welcome to the Chuunin Exam. In order to pass, you must complete all three stages: The written
		exam, survival exam, and combat exam. See below for instructions on the current stage.</td></tr>';

    if($player->exam_stage < $CHUUNIN_STAGE_PASS) {
        // Written exam
        if($player->exam_stage == $CHUUNIN_STAGE_WRITTEN) {
            echo "<tr><th><b>Stage 1 - Written Exam</th></tr>
				<tr><td>
				<p style='text-align:center;'>Answer the following questions correctly to proceed:</p>
				<br />
				<form action='$self_link' method='post'>
				
				<b>What type of jutsu involves controlling the chakra in the opponent's mind to cause illusions, reducing their
				combat effectiveness, causing sensations of pain, or even completely immobilizing them?</b><br />
				<input type='radio' name='question1' value='ninjutsu' /> Ninjutsu<br />
				<input type='radio' name='question1' value='taijutsu' /> Taijutsu<br />
				<input type='radio' name='question1' value='genjutsu' /> Genjutsu<br />
				<input type='radio' name='question1' value='all' /> All of the above<br />
				<br />
				
				<b>Which type of jutsu can Armor (harden) protect you from?</b><br />
				<input type='radio' name='question2' value='ninjutsu' /> Ninjutsu<br />
				<input type='radio' name='question2' value='taijutsu' /> Taijutsu<br />
				<input type='radio' name='question2' value='genjutsu' /> Genjutsu<br />
				<input type='radio' name='question2' value='all' /> All of the above<br />
				<br />
				
				<b>What Ninja Village has the most villagers?</b><br />
				<input type='radio' name='question3' value='Leaf' /> Leaf<br />
				<input type='radio' name='question3' value='Cloud' /> Cloud<br />
				<input type='radio' name='question3' value='Stone' /> Stone<br />
				<input type='radio' name='question3' value='Mist' /> Mist<br />
				<input type='radio' name='question3' value='Sand' /> Sand<br />
				<br />
				
				<input type='submit' value='Submit Answers' />
				</form>
				
				</td></tr></table>";
        }
        else if($player->exam_stage >= $CHUUNIN_STAGE_SURVIVAL_START && $player->exam_stage <= $CHUUNIN_STAGE_SURVIVAL_END) {
            $opponents[$CHUUNIN_STAGE_SURVIVAL_START] = 6;
            $opponents[$CHUUNIN_STAGE_SURVIVAL_MIDDLE] = 5;
            $opponents[$CHUUNIN_STAGE_SURVIVAL_END] = 10;

            echo "<tr><th><b>Stage 2 - Survival Exam</th></tr>
				<tr><td style='text-align:center;'>
				You must fight your way through the forest of death, defeating other ninjas to acquire the exam scroll. Being defeated
				will result in failing the exam.
				</td></tr></table>";

            if(!$player->battle_id) {
                try {
                    $opponent = new NPC($system, $opponents[$player->exam_stage]);
                    $opponent->loadData();
                    Battle::start($system, $player, $opponent, Battle::TYPE_AI_RANKUP);
                } catch(Exception $e) {
                    $system->message($e->getMessage());
                    $system->printMessage();
                    return false;
                }
            }

            $battle = new BattleManager($system, $player, $player->battle_id);

            $battle->checkInputAndRunTurn();

            $battle->renderBattle();

            if(!$battle->isComplete()) {
                return true;
            }

            $player->battle_id = 0;
            if($battle->isPlayerWinner()) {
                $player->exam_stage++;

                echo "<table class='table'><tr><th>Battle Results</th></tr>
					<tr><td style='text-align:center;'>";
                if($player->exam_stage == $CHUUNIN_STAGE_SURVIVAL_END) {
                    echo "You defeated your opponent, who had the scroll you needed, and have advanced to the 
							next stage of the exam. You can take a short break and heal up if you want, or continue to the 
							final battle.<br />
							<a href='{$system->links['profile']}'>Take a break</a>
							<a href='$self_link'>Continue</a>";
                }
                else {
                    echo "You defeated your opponent, but they did not have the scroll you needed. You must keep going and fight 
						someone else.<br /><a href='$self_link'>Continue</a>";
                }

                echo "</td></tr></table>";
            }
            else if($battle->isOpponentWinner()) {
                $player->exam_stage = 0;

                echo "<table class='table'><tr><th>Battle Results</th></tr>
					<tr><td>You have been defeated. You have failed the chuunin exam.<br />
					<a href='{$system->links['profile']}'>Continue</a>
					</td></tr></table>";
                return false;
            }
            else if($battle->isDraw()) {
                $player->exam_stage = 0;

                echo "<table class='table'><tr><th>Battle Results</th></tr>
					<tr><td>The battle ended in a draw. You were unable to continue the exam and failed.<br />
					<a href='{$system->links['profile']}'>Continue</a>
					</td></tr></table>";
                return false;
            }
        }
        else if($player->exam_stage == $CHUUNIN_STAGE_DUEL) {
            $opponent_id = 11;
            echo "<tr><th><b>Stage 3 - Combat Exam</th></tr>
				<tr><td style='text-align:center;'>
				For your final exam, you must fight against one of the strongest exam participants in a duel.
				</td></tr></table>";

            if(!$player->battle_id) {
                try {
                    $opponent = new NPC($system, $opponent_id);
                    $opponent->loadData();
                    Battle::start($system, $player, $opponent, Battle::TYPE_AI_RANKUP);
                } catch(Exception $e) {
                    $system->message($e->getMessage());
                    $system->printMessage();
                    return false;
                }
            }

            $battle = new BattleManager($system, $player, $player->battle_id);
            $battle->checkInputAndRunTurn();

            $battle->renderBattle();
            if(!$battle->isComplete()) {
                return true;
            }

            $result = processChuuninExamFightEnd($system, $battle, $player);
            echo "<table class='table'><tr><th>Battle Results</th></tr>"
                . "<tr><td style='text-align:center;'>"
                . $system->parseMarkdown($result)
                . "</td></tr></table>";
        }
    }
    else if($player->exam_stage == $CHUUNIN_STAGE_PASS) { // Pass
        try {
            $element = $_POST['element'];

            switch($element) {
                case 'Fire':
                case 'Wind':
                case 'Lightning':
                case 'Earth':
                case 'Water':
                    break;
                case '':
                    $element = false;
                    break;
                default:
                    $element = false;
                    $system->message("Invalid element!");
                    break;
            }

            // Display
            if(!$element) {
                $system->printMessage();
                echo "<tr><td>Congratulations, you have passed the Chuunin Exam!<br />
					<br />
					After passing all three exams, you have been recognized as an official ninja of the $player->village Village. You now
					have more rights, and more responsibilities. You can create or join a team with up to 3 other ninja, as well as
					take on C-rank and B-rank missions. You can no longer normally train or do arena fights inside the village, as your skills are too destructive to be 
					safe around the civilians. With your new uniform you can carry an extra weapon and wear another piece of armor, and as
					your skills with jutsu progress you are able to keep an extra jutsu ready to use in combat.<br />
					<br />
					Your chakra has also reached the point where you are able to discover your first elemental chakra nature. The elders
					hand you a piece of chakra paper and instruct you to focus hard and infuse your chakra into the paper. The reaction
					will determine which your primary chakra nature type is.<br />
					<br />
					<form action='$self_link' method='post'>
					<p style='text-align:center;'>
						<b>Choose an element to focus on</b><br />
						<i>(Note: Choose carefully, this will determine your primary chakra nature, which cannot be 
						changed without AK)</i><br />
						<select name='element'>
							<option value='Fire'>Fire</option>
							<option value='Wind'>Wind</option>
							<option value='Lightning'>Lightning</option>
							<option value='Earth'>Earth</option>
							<option value='Water'>Water</option>
						</select><br />
						<input type='submit' value='Infuse Chakra' />
					</p>
					</form>
					</td></tr>
					</table>";

                return false;
            }
            else {
                echo "<tr><td style='text-align:center;'>";
                switch($element) {
                    case 'Fire':
                        echo "With the image of blazing fires in your mind, you focus on the paper and flow chakra from your stomach,
							through your arms, out your fingertips and into the paper. Suddenly it erupts into flame, and you drop it
							in shock. The elders smile and say \"Congratulations, you have the Fire element. Fire is the embodiment of
							consuming destruction, that devours anything in its path. Your Fire jutsu will be strong against Wind jutsu, as
							they will only fan the flames and strengthen your jutsu. However, you must be careful against Water jutsu, as they 
							can extinguish your fires.\"<br />
							<a href='{$system->links['profile']}'>Continue</a>";
                        break;
                    case 'Wind':
                        echo "Picturing a tempestuous tornado, you focus on the paper and flow chakra from your stomach,
							through your arms, out your fingertips and into the paper. At first nothing seems to happen, but then in an
							instant the paper splits clean in half. The elders smile and say \"Congratulations, you have the Wind element. Wind
							is the sharpest out of all chakra natures, and can slice through anything when used properly. Your Wind chakra
							will be strong against Lightning jutsu, as you can cut and dissipate it, but you will be weak against Fire jutsu,
							because your wind only serves to fan their flames and make them stronger.\"
							<br />
							<a href='{$system->links['profile']}'>Continue</a>";
                        break;
                    case 'Lightning':
                        echo "Imagining the feel of electricity coursing through your veins, you focus on the paper and flow chakra 
							from your stomach, through your arms, out your fingertips and into the paper. With a slight shock the
							paper crumples into a ball, and the elders smile and say 
							\"Congratulations, you have the Lightning element. Lightning embodies pure speed, and skilled users of
							this element physically augment themselves to swiftly strike through almost anything. Your Lightning
							jutsu will be strong against Earth as your speed can slice straight through the slower techniques of Earth,
							but you must be careful against Wind jutsu as they will dissipate your Lightning.\"
							<br />
							<a href='{$system->links['profile']}'>Continue</a>";
                        break;
                    case 'Earth':
                        echo "Firmly planting your feet in the dirt and embracing the feel of it, you focus on the paper and flow 
							chakra from your stomach, through your arms, out your fingertips and into the paper. The paper gradually turns 
							into dirt and crumbles away, and the elders smile and say \"Congratulations, you have the Earth element. Earth 
							is the hardiest of elements and can protect you or your teammates from enemy attacks. Your Earth jutsu will be 
							strong against Water jutsu, as you can turn the water to mud and render it useless, but you will be weak to 
							Lightning jutsu, as they are one of the few types that can swiftly evade and strike through your techniques.\"
							<br />
							<a href='{$system->links['profile']}'>Continue</a>";
                        break;
                    case 'Water':
                        echo "With thoughts of splashing rivers flowing through your mind, you focus on the paper and flow chakra 
							from your stomach, through your arms, out your fingertips and into the paper. The paper gradually moistens
							and then turns completely soaked with water. The elders smile and say 
							\"Congratulations, you have the Water element. Water is a versatile element that can control the flow
							of the battle, trapping enemies or launching large-scale attacks at them. Your Water jutsu will be strong against
							Fire jutsu because you can extinguish them, but Earth jutsu can turn your water into mud and render it useless.\"
							<br />
							<a href='{$system->links['profile']}'>Continue</a>";
                        break;
                }



                echo "</td></tr>";
            }

            $player->elements = array('first' => $element);

            $player->exam_stage = 0;

            $rankManager->increasePlayerRank($player);
        } catch(Exception $e) {
            echo "<tr><td style='text-align:center;'>" . $e->getMessage() . "</td></tr>";
        }
        echo "</table>";
    }

    return true;
}

/**
 * @throws Exception
 */
function processChuuninExamFightEnd(System $system, BattleManager $battle, User $player): bool|string {
    global $self_link;
    global $CHUUNIN_STAGE_SURVIVAL_START;
    global $CHUUNIN_STAGE_SURVIVAL_END;
    global $CHUUNIN_STAGE_DUEL;

    if(!$battle->isComplete()) {
        return "";
    }

    $player->battle_id = 0;

    if($player->exam_stage >= $CHUUNIN_STAGE_SURVIVAL_START && $player->exam_stage <= $CHUUNIN_STAGE_SURVIVAL_END) {
        if($battle->isPlayerWinner()) {
            $player->exam_stage++;

            if($player->exam_stage == $CHUUNIN_STAGE_SURVIVAL_END) {
                return "You defeated your opponent, who had the scroll you needed, and have advanced to the "
                    . "next stage of the exam. You can take a short break and heal up if you want, or continue to the "
                    . "final battle.[br]"
                    . "[Take a break]({$system->links['profile']})"
                    . "[Continue]({$self_link})";
            }
            else {
                return "You defeated your opponent, but they did not have the scroll you needed. You must keep "
                    . "going and fight someone else.[br]"
                    . "[Continue]({$self_link})";
            }
        }
        else if($battle->isOpponentWinner()) {
            $player->exam_stage = 0;

            return "You have been defeated. You have failed the chuunin exam.[br]"
                . "[Continue]({$system->links['profile']})";
        }
        else if($battle->isDraw()) {
            $player->exam_stage = 0;

            return "The battle ended in a draw. You were unable to continue the exam and failed.[br]"
                . "[Continue]({$system->links['profile']})";
        }
    }
    else if($player->exam_stage == $CHUUNIN_STAGE_DUEL) {
        $player->battle_id = 0;
        if($battle->isPlayerWinner()) {
            $player->exam_stage++;
            return "You defeated your opponent, and have passed the final test![br]"
                . "[Continue]({$self_link})";

        }
        else if($battle->isOpponentWinner()) {
            $player->exam_stage = 0;
            return "You have been defeated. You have failed the chuunin exam.[br]"
                . "[Continue]({$system->links['profile']})";
        }
        else if($battle->isDraw()) {
            $player->exam_stage = 0;
            return "The battle ended in a draw. You were unable to continue the exam and failed.[br]"
                . "[Continue]({$system->links['profile']})";
        }
    }

    return "";
}

/**
 * @throws Exception
 */
function joninExam(System $system, User $player, RankManager $rankManager): bool {
    global $self_link;

    $STAGE_MISSION = 1;
    $STAGE_PASS = 2;

    if(!$player->exam_stage) {
        $player->exam_stage = 1;
    }

    // Input
    if(!empty($_POST)) {
        if($player->exam_stage == 1) {
            if(isset($_POST['start_exam'])) {
                $mission_id = 10;

                $mission = new Mission($mission_id, $player);

                $player->mission_id = $mission_id;
                require("missions.php");
                missions();
                return true;
            }
        }
    }

    // Display
    $system->printMessage();
    echo "<table class='table'>
			<tr><th>JONIN EXAM</th></tr>";
    $exam_instructions = '<tr><td>Welcome to the Jonin Exam. In order to pass, you must complete a special mission
			assigned by the Kage.</td></tr>';

    if($player->exam_stage == $STAGE_MISSION) {
        echo $exam_instructions .
            "<tr><td style='text-align:center;'>
				<form action='$self_link' method='post'>
					<button type='submit' name='start_exam'>Start mission</button>
				</form>
				</td></tr>";
    }
    else if($player->exam_stage == $STAGE_PASS) { // Pass
        try {
            $elements = array('Fire', 'Wind', 'Lightning', 'Earth', 'Water');
            unset($elements[array_search($player->elements['first'], $elements)]);

            $element = $_POST['element'];
            if(!in_array($element, $elements)) {
                $element = false;
            }

            // Display
            if(!$element) {
                $system->printMessage();
                echo "<tr><td>Congratulations, you have passed the Jonin Exam!<br />
					<br />
					After passing the exam, you have been recognized as an expert ninja of the $player->village Village. You 
					can now train students, challenge your clan leader for their position, and access more powerful jutsu. 
					
					<!--With your new uniform you can carry 
					an extra weapon and wear another piece of armor, and as your skills with jutsu progress you are able to 
					keep an extra jutsu ready to use in combat.<br />-->
					
					<br />
					With the experience you have gained with your first element, your chakra is now refined enough to discover
					your second elemental chakra nature. For this exam you are taken to a temple deep within the village 
					and sat in the center of a large jutsu seal. Surrounding you are 5 pedestals that the jutsu seal runs to:
					The elders pull one away, disconnecting it from the jutsu. You are instructed to shut off your elemental 
					chakra and focus beyond it within yourself while infusing chakra into the seal, a difficult task but one you are 
					now capable of.<br />
					<br />
					<form action='$self_link' method='post'>
					<p style='text-align:center;'>
						<b>Choose an element to focus on</b><br />
						<i>(Note: Choose carefully, this will determine your secondary chakra nature, which cannot be 
						changed without AK)</i><br />
						<select name='element'>";

                foreach($elements as $elem) {
                    echo "<option value='$elem'>$elem</option>";
                }

                echo "</select><br />
						<input type='submit' value='Infuse Chakra' />
					</p>
					</form>
					</td></tr>
					</table>";

                return false;
            }
            else {
                echo "<tr><td style='text-align:center;'>";
                switch($element) {
                    case 'Fire':
                        echo "With the image of blazing fires in your mind, you flow chakra from your stomach,
							down through your legs and into the seal on the floor. Suddenly one of the pedestals bursts into 
							fire, breaking your focus. The elders smile and say \"Congratulations, you now have the Fire element. Fire is the embodiment of
							consuming destruction, that devours anything in its path. Your Fire jutsu will be strong against Wind jutsu, as
							they will only fan the flames and strengthen your jutsu. However, you must be careful against Water jutsu, as they 
							can extinguish your fires.\"<br />
							<a href='{$system->links['profile']}'>Continue</a>";
                        break;
                    case 'Wind':
                        echo "Picturing a tempestuous tornado, you flow chakra from your stomach,
							down through your legs and into the seal on the floor. You feel a disturbance in the room and
							suddenly realize that a small whirlwind has formed around one of the pedestals. The elders smile and 
							say \"Congratulations, you have the Wind element. Wind is the sharpest out of all chakra natures, 
							and can slice through anything when used properly. Your Wind chakra will be strong against 
							Lightning jutsu, as you can cut and dissipate it, but you will be weak against Fire jutsu,
							because your wind only serves to fan their flames and make them stronger.\"
							<br />
							<a href='{$system->links['profile']}'>Continue</a>";
                        break;
                    case 'Lightning':
                        echo "Imagining the feel of electricity coursing through your veins, you flow chakra from your stomach,
							down through your legs and into the seal on the floor. Suddenly you feel a charge in the air and
							one of the pedestals begins to spark with crackling electricity.
							\"Congratulations, you have the Lightning element. Lightning embodies pure speed, and skilled users of
							this element physically augment themselves to swiftly strike through almost anything. Your Lightning
							jutsu will be strong against Earth as your speed can slice straight through the slower techniques of Earth,
							but you must be careful against Wind jutsu as they will dissipate your Lightning.\"
							<br />
							<a href='{$system->links['profile']}'>Continue</a>";
                        break;
                    case 'Earth':
                        echo "Envisioning stone as hard as the temple you are sitting in, you flow chakra from your stomach,
							down through your legs and into the seal on the floor. Suddenly dirt from nowhere begins to fall off one of the 
							pedstals, and the elders smile and say \"Congratulations, you have the Earth element. Earth 
							is the hardiest of elements and can protect you or your teammates from enemy attacks. Your Earth jutsu will be 
							strong against Water jutsu, as you can turn the water to mud and render it useless, but you will be weak to 
							Lightning jutsu, as they are one of the few types that can swiftly evade and strike through your techniques.\"
							<br />
							<a href='{$system->links['profile']}'>Continue</a>";
                        break;
                    case 'Water':
                        echo "With thoughts of splashing rivers flowing through your mind, you flow chakra from your stomach,
							down through your legs and into the seal on the floor. Suddenly a small geyser erupts from one of
							the pedestals, and the elders smile and say 
							\"Congratulations, you have the Water element. Water is a versatile element that can control the flow
							of the battle, trapping enemies or launching large-scale attacks at them. Your Water jutsu will be strong against
							Fire jutsu because you can extinguish them, but Earth jutsu can turn your water into mud and render it useless.\"
							<br />
							<a href='{$system->links['profile']}'>Continue</a>";
                        break;
                }

                echo "</td></tr>";
            }

            $player->elements['second'] = $element;

            $player->exam_stage = 0;

            $rankManager->increasePlayerRank($player);
        } catch(Exception $e) {
            echo "<tr><td style='text-align:center;'>" . $e->getMessage() . "</td></tr>";
        }

    }

    echo "</table>";

    return true;
}

function rankupFightAPI(System $system, User $player): BattlePageAPIResponse {
    // Only chuunin exam has direct battles
    if($player->rank != 2) {
        return new BattlePageAPIResponse(errors: ["Battles only supported for Chuunin exam!"]);
    }

    if(!$player->battle_id) {
        return new BattlePageAPIResponse(errors: ["Player is not in battle!"]);
    }

    $response = new BattlePageAPIResponse();

    try {
        $battle = new BattleManager($system, $player, $player->battle_id);
        $battle->checkInputAndRunTurn();

        $response->battle_data = $battle->getApiResponse();

        if($battle->isComplete()) {
            $response->battle_result = processChuuninExamFightEnd($system, $battle, $player);
        }
    } catch(Exception $e) {
        $response->errors[] = $e->getMessage();
    }

    return $response;
}