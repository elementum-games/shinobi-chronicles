<?php

function levelUp() {
	global $player;

	if($player->level < $player->rank->max_level) {
		$player->level++;
		$player->exp = $player->total_stats * 10;

		$player->max_health += $player->rank->health_gain;
		$player->max_chakra += $player->rank->pool_gain;
		$player->max_stamina += $player->rank->pool_gain;

		$player->health = $player->max_health;
		$player->chakra = $player->max_chakra;
		$player->stamina = $player->max_stamina;

		require 'templates/level_rank_up/level_up_message.php';
	}
}

/**
 * @return bool
 * @throws Exception
 */
function rankUp(): bool {
	global $system;

	global $player;

	$self_link = $system->router->links['rankup'];

    if(!$player->rank_up) {
        $system->message("You must re-enable rank ups in the <a href='{$system->router->links['settings']}'>Settings</a> page.");
        $system->printMessage();
        return true;
    }
	if($player->exam_stage > 0 && !empty($_GET['abandon_exam'])) {
	    $player->exam_stage = 0;
        if($player->rank_num == 3) {
            $player->clearMission();
        }
        if(isset($_SESSION['exam_begin'])) {
            unset($_SESSION['exam_begin']);
        }
        if(isset($_SESSION['genin_jutsu_fails'])) {
            unset($_SESSION['genin_jutsu_fails']);
        }

        $system->message("Understanding that this is not your time, you bow out of the exam. Maybe next time
            will be your chance!<br />
            <a href='{$system->router->links['profile']}'>Continue</a>");
        $system->printMessage();
        return true;
    }

	// Can't even access this page if in a different type of battle
	if($player->battle_id && $player->exam_stage == 0) {
	    $player->battle_id = 0;
        $system->message("Your exam battle has concluded. <a href='{$system->router->links['profile']}'>Continue</a>");
        return true;
    }

    if($player->level < $player->rank->max_level || $player->exp < $player->expForNextLevel() || $player->rank_num >= System::SC_MAX_RANK) {
        $player->exam_stage = 0;
        $system->message("You are not eligible for a rankup exam. <a href='{$system->router->links['profile']}'>Continue</a>");
        return true;
    }

    $rankManager = new RankManager($system);
    $rankManager->loadRanks();

	// Akademi-sei -> Genin
	if($player->rank_num == 1) {
        geninExam($system, $player, $rankManager);
	}
	// Genin -> Chuunin
	else if($player->rank_num == 2) {
        chuuninExam($system, $player, $rankManager);
	}
	// Chuunin -> Jonin
	else if($player->rank_num == 3) {
        if($player->in_village) {
            joninExam($system, $player, $rankManager);
        }
        else {
            $system->message("You must be inside the {$player->village->name} Village to access this page.");
            $system->printMessage();
        }
	}

	return true;
}

function geninExam(System $system, User $player, RankManager $rankManager) {
    global $self_link;

    $new_rank = $player->rank_num + 1;
    $exam_name = $rankManager->ranks[$new_rank]->name . " Exam";
    $exam_time_limit = 300;
    $bloodline_roll_chance = System::BLOODLINE_ROLL_CHANCE;

    $player->getInventory();

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

    // Resume exam from afk, logout, etc.
    if($player->exam_stage > 0 && !isset($_SESSION['exam_begin'])) {
        //Allow continuance of exam due to exiting browser/logging out but with reduced time limit based on stage
        $_SESSION['exam_begin'] = time() - ($player->exam_stage * 60);
        // Allow 2 fails beginning on stage 1, 1 fail beginning on stage 2, stage 3 must not have fails
        $_SESSION['genin_jutsu_fails'] = $player->exam_stage - 1;
    }

    // Exam timeout
    if($player->exam_stage && isset($_SESSION['exam_begin']) && $player->exam_stage < 4) {
        $time_remaining = ($_SESSION['exam_begin'] + $exam_time_limit) - time();
        if($time_remaining < 1) {
            $system->message("You have run out of time and failed the $exam_name!");
            $system->printMessage();
            unset($_SESSION['exam_begin']);
            unset($_SESSION['genin_jutsu_fails']);
            $player->exam_stage = 0;
        }
    }

    // Input
    if(!empty($_POST['hand_seals']) && $player->exam_stage > 0) {
        $hand_seals = $_POST['hand_seals'];
        // Jutsu success
        if($hand_seals == $jutsu_data[$player->exam_stage]['hand_seals'] && $player->hasJutsu($jutsu_data[$player->exam_stage]['jutsu_id'])) {
            $player->exam_stage++;
        }
        // Jutsu fail
        else {
            $_SESSION['genin_jutsu_fails'] += 1;
            if($_SESSION['genin_jutsu_fails'] < 3) {
                $system->message("You attempted to perform " . $jutsu_data[$player->exam_stage]['name'] . " but failed.");
            }
            // Three jutsu fails, end exam
            else {
                $system->message("Having failed to perform a jutsu 3 times, you have failed the $exam_name!");
                $player->exam_stage = 0;
                unset($_SESSION['genin_jutsu_fails']);
                unset($_SESSION['exam_begin']);
            }
        }
    }

    // Begin exam request
    if(!empty($_POST['begin_exam']) && $player->exam_stage == 0) {
        $_SESSION['exam_begin'] = time();
        $_SESSION['genin_jutsu_fails'] = 0;
        $player->exam_stage = 1;
    }

    // Display
    $system->printMessage();
    $prompt = '';
    switch($player->exam_stage) {
        case 1:
            $prompt = 'Please demonstrate ' . $jutsu_data[1]['name'] . ':';
            break;
        case 2:
            $prompt = 'Please demonstrate ' . $jutsu_data[2]['name'] . ':';
            break;
        case 3:
            $prompt = 'Please demonstrate ' . $jutsu_data[3]['name'] . ':';
            break;
        default:
            break;
    }

    if(!$player->exam_stage) {
        require 'templates/level_rank_up/exam_assignment.php';
    }
    else if($player->exam_stage >= 1 && $player->exam_stage <= 3) {
        //Check time limit
        $time_remaining = ($_SESSION['exam_begin'] + $exam_time_limit) - time();
        //Abandon Exam
        require 'templates/level_rank_up/abandon_exam.php';
        $submitted_hand_seals = $_POST['hand_seals'] ?? '';
        require 'templates/level_rank_up/genin_exam.php';
    }
    else if($player->exam_stage == 4) {
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
						WHERE `village`='{$player->village->name}' AND `rank`='$bloodline_rank'");
                if($system->db_last_num_rows == 0) {
                    $result = $system->query("SELECT `bloodline_id`, `clan_id`, `name` FROM `bloodlines`
						WHERE `village`='{$player->village->name}' AND `rank` < 5");
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
                        $player->clan = Clan::loadFromId($system, $bloodlines[$bloodline_id]['clan_id']);
                        $player->clan_id = $player->clan->id;
                        $player->clan_office = 0;
                    }

                    // Give bloodline
                    Bloodline::giveBloodline(
                        system: $system,
                        bloodline_id: $bloodline_id,
                        user_id: $player->user_id
                    );
                }
            }

            // Clan roll(failsafe if no bloodlines were found on bl roll)
            if(!$bloodline_rolled) {
                $result = $system->query("SELECT `clan_id`, `name` FROM `clans`
						WHERE `village`='{$player->village->name}' AND `bloodline_only`='0'");
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

                $player->clan = Clan::loadFromId($system, $clan_id);
                $player->clan_id = $clan_id;
                $clan_name = $clans[$clan_id]['name'];
            }

            $rankManager->increasePlayerRank($player);

            if($bloodline_rolled) {
                $bloodline_display = "Suddenly the elder senses something inside of you, and you feel a surge in power
					as a strange force wells up inside you, answering the call of the elder's chakra. The elder withdraws $gender hand
					and smiles, saying calmly 'You have the <b>$bloodline_name</b>. This is the
					bloodline of the <b>$clan_name</b> clan. From this day forward you shall join them, and study with them to train
					your bloodline to its fullest potential.'<br />
					<p style='text-align:center;'><a href='{$system->router->links['profile']}'>Continue</a></p>";
            }
            else {
                $bloodline_display = "After focusing deeply for several minutes, the elder withdraws $gender hand and says 'It appears you do not
					have a bloodline. You are free to train as you wish, we will place you in the <b>$clan_name</b> clan. Train
					hard with them and become a strong ninja for your clan and village.'<br />
					<p style='text-align:center;'><a href='{$system->router->links['profile']}'>Continue</a></p>";
            }
        } catch(Exception $e) {
            $system->message($e->getMessage());
        }
        $system->printMessage();
        require 'templates/level_rank_up/genin_exam_graduation.php';
    }
}

/**
 * @throws Exception
 */
function chuuninExam(System $system, User $player, RankManager $rankManager): bool {
    global $self_link;
    $exam_name = $rankManager->ranks[$player->rank_num + 1]->name . " Exam";
    $CHUUNIN_STAGE_WRITTEN = 1;
    $CHUUNIN_STAGE_SURVIVAL_START = 2;
    $CHUUNIN_STAGE_SURVIVAL_MIDDLE = 3;
    $CHUUNIN_STAGE_SURVIVAL_END = 4;
    $CHUUNIN_STAGE_DUEL = 5;
    $CHUUNIN_STAGE_PASS = 6;

    // Begin exam
    if(isset($_POST['begin_exam'])) {
        $player->exam_stage = $CHUUNIN_STAGE_WRITTEN;
    }
    // Display exam introduction
    if(!$player->exam_stage) {
        require 'templates/level_rank_up/exam_assignment.php';
        return true;
    }

    // Input
    if($player->exam_stage == $CHUUNIN_STAGE_WRITTEN && isset($_POST['written_exam'])) {
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
                "<a href='{$system->router->links['profile']}'>Continue</a>");
            $system->printMessage();
            $player->exam_stage = 0;
            return false;
        }
        $system->printMessage();
    }

    // Display
    $system->printMessage();
    if($player->exam_stage > 0 && $player->exam_stage < $CHUUNIN_STAGE_PASS) {
        require 'templates/level_rank_up/abandon_exam.php';
    }

    if($player->exam_stage < $CHUUNIN_STAGE_PASS) {
        if($player->exam_stage == $CHUUNIN_STAGE_WRITTEN) {
            require 'templates/level_rank_up/chuunin_written_exam.php';
        }
        if($player->exam_stage >= $CHUUNIN_STAGE_SURVIVAL_START && $player->exam_stage <= $CHUUNIN_STAGE_SURVIVAL_END) {
            $opponents[$CHUUNIN_STAGE_SURVIVAL_START] = 6;
            $opponents[$CHUUNIN_STAGE_SURVIVAL_MIDDLE] = 5;
            $opponents[$CHUUNIN_STAGE_SURVIVAL_END] = 10;

            require 'templates/level_rank_up/chuunin_survival.php';

            if(!$player->battle_id) {
                try {
                    $opponent = new NPC($system, $opponents[$player->exam_stage]);
                    $opponent->loadData();
                    if($system->USE_NEW_BATTLES) {
                        BattleV2::start($system, $player, $opponent, Battle::TYPE_AI_RANKUP);
                    }
                    else {
                        Battle::start($system, $player, $opponent, Battle::TYPE_AI_RANKUP);
                    }
                } catch(Exception $e) {
                    $system->message($e->getMessage());
                    $system->printMessage();
                    return false;
                }
            }

            if($system->USE_NEW_BATTLES) {
                $battle = BattleManagerV2::init($system, $player, $player->battle_id);
            }
            else {
                $battle = BattleManager::init($system, $player, $player->battle_id);
            }

            $battle->checkInputAndRunTurn();

            $battle->renderBattle();

            if(!$battle->isComplete()) {
                return true;
            }

            $player->battle_id = 0;
            if($battle->isPlayerWinner()) {
                $player->exam_stage++;
                if($player->exam_stage == $CHUUNIN_STAGE_SURVIVAL_END) {
                    $battle_result = "You defeated your opponent, who had the scroll you needed, and have advanced to the
                    next stage of the exam. You can take a short break and heal up if you want, or continue to the
                    final battle.<br />
                    <a href='{$system->router->links['profile']}'>Take a break</a>
                    <a href='$self_link'>Continue</a>";
                }
                else {
                    $battle_result = "You defeated your opponent, but they did not have the scroll you needed.
                    You must keep going and fight someone else.<br /><a href='$self_link'>Continue</a>";
                }
                require 'templates/level_rank_up/chuunin_survival_battle_results.php';
            }
            else if($battle->isOpponentWinner()) {
                $player->exam_stage = 0;
                $battle_result = "You have been defeated. You have failed the $exam_name.<br />
					<a href='{$system->router->links['profile']}'>Continue</a>";
                require 'templates/level_rank_up/chuunin_survival_battle_results.php';
                return false;
            }
            else if($battle->isDraw()) {
                $player->exam_stage = 0;
                $battle_result = "The battle ended in a draw. You were unable to continue the exam and failed.<br />
                <a href='{$system->router->links['profile']}'>Continue</a>";

                return false;
            }
        }
        else if($player->exam_stage == $CHUUNIN_STAGE_DUEL) {
            $opponent_id = 11;

            require 'templates/level_rank_up/chuunin_battle_exam.php';

            if(!$player->battle_id) {
                try {
                    $opponent = new NPC($system, $opponent_id);
                    $opponent->loadData();

                    if($system->USE_NEW_BATTLES) {
                        BattleV2::start($system, $player, $opponent, Battle::TYPE_AI_RANKUP);
                    }
                    else {
                        Battle::start($system, $player, $opponent, Battle::TYPE_AI_RANKUP);
                    }
                } catch(Exception $e) {
                    $system->message($e->getMessage());
                    $system->printMessage();
                    return false;
                }
            }

            if($system->USE_NEW_BATTLES) {
                $battle = BattleManagerV2::init($system, $player, $player->battle_id);
            }
            else {
                $battle = BattleManager::init($system, $player, $player->battle_id);
            }
            $battle->checkInputAndRunTurn();

            $battle->renderBattle();
            if(!$battle->isComplete()) {
                return true;
            }

            $result = processChuuninExamFightEnd($system, $battle, $player, $CHUUNIN_STAGE_SURVIVAL_START, $CHUUNIN_STAGE_SURVIVAL_END, $CHUUNIN_STAGE_DUEL);
            $battle_result = $system->parseMarkdown($result);
            require_once 'templates/level_rank_up/chuunin_survival_battle_results.php';
        }
    }
    else if($player->exam_stage == $CHUUNIN_STAGE_PASS) {
        try {
            $element = $_POST['element'] ?? false;

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
                require 'templates/level_rank_up/chuunin_exam_graduation.php';
                return false;
            }
            else {
                switch($element) {
                    case 'Fire':
                        $element_display = "With the image of blazing fires in your mind, you focus on the paper and flow chakra from your stomach,
							through your arms, out your fingertips and into the paper. Suddenly it erupts into flame, and you drop it
							in shock. The elders smile and say \"Congratulations, you have the Fire element. Fire is the embodiment of
							consuming destruction, that devours anything in its path. Your Fire jutsu will be strong against Wind jutsu, as
							they will only fan the flames and strengthen your jutsu. However, you must be careful against Water jutsu, as they
							can extinguish your fires.\"<br />
							<a href='{$system->router->links['profile']}'>Continue</a>";
                        break;
                    case 'Wind':
                        $element_display = "Picturing a tempestuous tornado, you focus on the paper and flow chakra from your stomach,
							through your arms, out your fingertips and into the paper. At first nothing seems to happen, but then in an
							instant the paper splits clean in half. The elders smile and say \"Congratulations, you have the Wind element. Wind
							is the sharpest out of all chakra natures, and can slice through anything when used properly. Your Wind chakra
							will be strong against Lightning jutsu, as you can cut and dissipate it, but you will be weak against Fire jutsu,
							because your wind only serves to fan their flames and make them stronger.\"
							<br />
							<a href='{$system->router->links['profile']}'>Continue</a>";
                        break;
                    case 'Lightning':
                        $element_display = "Imagining the feel of electricity coursing through your veins, you focus on the paper and flow chakra
							from your stomach, through your arms, out your fingertips and into the paper. With a slight shock the
							paper crumples into a ball, and the elders smile and say
							\"Congratulations, you have the Lightning element. Lightning embodies pure speed, and skilled users of
							this element physically augment themselves to swiftly strike through almost anything. Your Lightning
							jutsu will be strong against Earth as your speed can slice straight through the slower techniques of Earth,
							but you must be careful against Wind jutsu as they will dissipate your Lightning.\"
							<br />
							<a href='{$system->router->links['profile']}'>Continue</a>";
                        break;
                    case 'Earth':
                        $element_display = "Firmly planting your feet in the dirt and embracing the feel of it, you focus on the paper and flow
							chakra from your stomach, through your arms, out your fingertips and into the paper. The paper gradually turns
							into dirt and crumbles away, and the elders smile and say \"Congratulations, you have the Earth element. Earth
							is the hardiest of elements and can protect you or your teammates from enemy attacks. Your Earth jutsu will be
							strong against Water jutsu, as you can turn the water to mud and render it useless, but you will be weak to
							Lightning jutsu, as they are one of the few types that can swiftly evade and strike through your techniques.\"
							<br />
							<a href='{$system->router->links['profile']}'>Continue</a>";
                        break;
                    case 'Water':
                        $element_display = "With thoughts of splashing rivers flowing through your mind, you focus on the paper and flow chakra
							from your stomach, through your arms, out your fingertips and into the paper. The paper gradually moistens
							and then turns completely soaked with water. The elders smile and say
							\"Congratulations, you have the Water element. Water is a versatile element that can control the flow
							of the battle, trapping enemies or launching large-scale attacks at them. Your Water jutsu will be strong against
							Fire jutsu because you can extinguish them, but Earth jutsu can turn your water into mud and render it useless.\"
							<br />
							<a href='{$system->router->links['profile']}'>Continue</a>";
                        break;
                }
            }

            //Exam complete, element selected
            $player->elements = array('first' => $element);
            $player->exam_stage = 0;
            if ($player->sensei_id != 0) {
                // increase graduated count
                SenseiManager::incrementGraduatedCount($player->sensei_id, $player->user_id, $system);
                // remove student
                SenseiManager::removeStudent($player->sensei_id, $player->user_id, $system);
                $player->sensei_id = 0;
            }
            else {
                // close applications
                SenseiManager::closeApplicationsByStudent($player->user_id, $system);
            }
            $rankManager->increasePlayerRank($player);
            require 'templates/level_rank_up/chuunin_exam_graduation.php';
        } catch(Exception $e) {
            $system->message($e->getMessage());
            $system->printMessage();
        }
    }

    return true;
}

/**
 * @throws Exception
 */
function processChuuninExamFightEnd(System $system, BattleManager $battle, User $player, $CHUUNIN_STAGE_SURVIVAL_START,
        $CHUUNIN_STAGE_SURVIVAL_END, $CHUUNIN_STAGE_DUEL): bool|string {
    global $self_link;

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
                    . "[Take a break]({$system->router->links['profile']})"
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
                . "[Continue]({$system->router->links['profile']})";
        }
        else if($battle->isDraw()) {
            $player->exam_stage = 0;

            return "The battle ended in a draw. You were unable to continue the exam and failed.[br]"
                . "[Continue]({$system->router->links['profile']})";
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
                . "[Continue]({$system->router->links['profile']})";
        }
        else if($battle->isDraw()) {
            $player->exam_stage = 0;
            return "The battle ended in a draw. You were unable to continue the exam and failed.[br]"
                . "[Continue]({$system->router->links['profile']})";
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
    $exam_name = $rankManager->ranks[$player->rank_num + 1]->name . " Exam";

    // Begin exam
    if(!empty($_POST)) {
        if($player->exam_stage == 0) {
            if(isset($_POST['begin_exam'])) {
                $player->exam_stage = $STAGE_MISSION;
                $mission_id = RankManager::JONIN_MISSION_ID;
                $mission = new Mission($mission_id, $player);
                $player->mission_id = $mission_id;
            }
        }
    }

    if(!$player->exam_stage && !$player->mission_id) {
        require_once 'templates/level_rank_up/exam_assignment.php';
        return false;
    }
    elseif($player->mission_id && $player->mission_id != RankManager::JONIN_MISSION_ID) {
        $system->message("You must complete your current mission!");
        $system->printMessage();
        return false;
    }

    // Display
    $system->printMessage();
    if($player->exam_stage == $STAGE_MISSION) {
        require 'templates/level_rank_up/abandon_exam.php';
        require("missions.php");
        missions();
    }

    if($player->exam_stage == $STAGE_PASS) {
        try {
            $elements = array('Fire', 'Wind', 'Lightning', 'Earth', 'Water');
            unset($elements[array_search($player->elements['first'], $elements)]);

            $element = $_POST['element'] ?? false;
            if(!in_array($element, $elements)) {
                $element = false;
            }

            if(isset($_POST['select_chakra']) && $element == false) {
                if($_POST['element'] == $player->elements['first']) {
                    $system->message("You already have the " . $_POST['element'] . " chakra nature!");
                }
                else {
                    $system->message("Invalid chakra nature!");
                }
            }

            // Display
            if(!$element) {
                $system->printMessage();
                require 'templates/level_rank_up/jonin_exam_graduation.php';
                return false;
            }
            else {
                switch($element) {
                    case 'Fire':
                        $element_display = "With the image of blazing fires in your mind, you flow chakra from your stomach,
							down through your legs and into the seal on the floor. Suddenly one of the pedestals bursts into
							fire, breaking your focus. The elders smile and say \"Congratulations, you now have the Fire element. Fire is the embodiment of
							consuming destruction, that devours anything in its path. Your Fire jutsu will be strong against Wind jutsu, as
							they will only fan the flames and strengthen your jutsu. However, you must be careful against Water jutsu, as they
							can extinguish your fires.\"<br />
							<a href='{$system->router->links['profile']}'>Continue</a>";
                        break;
                    case 'Wind':
                        $element_display = "Picturing a tempestuous tornado, you flow chakra from your stomach,
							down through your legs and into the seal on the floor. You feel a disturbance in the room and
							suddenly realize that a small whirlwind has formed around one of the pedestals. The elders smile and
							say \"Congratulations, you have the Wind element. Wind is the sharpest out of all chakra natures,
							and can slice through anything when used properly. Your Wind chakra will be strong against
							Lightning jutsu, as you can cut and dissipate it, but you will be weak against Fire jutsu,
							because your wind only serves to fan their flames and make them stronger.\"
							<br />
							<a href='{$system->router->links['profile']}'>Continue</a>";
                        break;
                    case 'Lightning':
                        $element_display = "Imagining the feel of electricity coursing through your veins, you flow chakra from your stomach,
							down through your legs and into the seal on the floor. Suddenly you feel a charge in the air and
							one of the pedestals begins to spark with crackling electricity.
							\"Congratulations, you have the Lightning element. Lightning embodies pure speed, and skilled users of
							this element physically augment themselves to swiftly strike through almost anything. Your Lightning
							jutsu will be strong against Earth as your speed can slice straight through the slower techniques of Earth,
							but you must be careful against Wind jutsu as they will dissipate your Lightning.\"
							<br />
							<a href='{$system->router->links['profile']}'>Continue</a>";
                        break;
                    case 'Earth':
                        $element_display = "Envisioning stone as hard as the temple you are sitting in, you flow chakra from your stomach,
							down through your legs and into the seal on the floor. Suddenly dirt from nowhere begins to fall off one of the
							pedstals, and the elders smile and say \"Congratulations, you have the Earth element. Earth
							is the hardiest of elements and can protect you or your teammates from enemy attacks. Your Earth jutsu will be
							strong against Water jutsu, as you can turn the water to mud and render it useless, but you will be weak to
							Lightning jutsu, as they are one of the few types that can swiftly evade and strike through your techniques.\"
							<br />
							<a href='{$system->router->links['profile']}'>Continue</a>";
                        break;
                    case 'Water':
                        $element_display = "With thoughts of splashing rivers flowing through your mind, you flow chakra from your stomach,
							down through your legs and into the seal on the floor. Suddenly a small geyser erupts from one of
							the pedestals, and the elders smile and say
							\"Congratulations, you have the Water element. Water is a versatile element that can control the flow
							of the battle, trapping enemies or launching large-scale attacks at them. Your Water jutsu will be strong against
							Fire jutsu because you can extinguish them, but Earth jutsu can turn your water into mud and render it useless.\"
							<br />
							<a href='{$system->router->links['profile']}'>Continue</a>";
                        break;
                }
            }

            $player->elements['second'] = $element;
            $player->exam_stage = 0;
            $rankManager->increasePlayerRank($player);
            require 'templates/level_rank_up/jonin_exam_graduation.php';
        } catch(Exception $e) {
            echo "<tr><td style='text-align:center;'>" . $e->getMessage() . "</td></tr>";
        }

    }

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
        if($system->USE_NEW_BATTLES) {
            $battle = BattleManagerV2::init($system, $player, $player->battle_id);
        }
        else {
            $battle = BattleManager::init($system, $player, $player->battle_id);
        }
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