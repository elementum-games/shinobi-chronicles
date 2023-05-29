<?php
/*
File: 		premium.php
Coder:		Levi Meahan
Created:	04/30/2014
Revised:	04/30/2014 by Levi Meahan
Purpose:	Function for premium credit shop. Resets, name changes, bloodline re-rolls, etc
*/

function premium() {
	global $system;

	global $player;

	global $self_link;

	global $RANK_NAMES;

	$costs['name_change'] = 15;
	$costs['gender_change'] = 10;
	$costs['bloodline'][1] = 80;
	$costs['bloodline'][2] = 60;
	$costs['bloodline'][3] = 40;
	$costs['bloodline'][4] = 20;
    $costs['forbidden_seal_monthly_cost'] = [
        1 => 5,
        2 => 15
    ];
    $costs['forbidden_seal'] = [
        1 => [
            30 => $costs['forbidden_seal_monthly_cost'][1],
            60 => $costs['forbidden_seal_monthly_cost'][1] * 2,
            90 => $costs['forbidden_seal_monthly_cost'][1] * 3
        ],
        2 => [
            30 => $costs['forbidden_seal_monthly_cost'][2],
            60 => $costs['forbidden_seal_monthly_cost'][2] * 2,
            90 => $costs['forbidden_seal_monthly_cost'][2] * 3
        ]
    ];
	$costs['element_change'] = 10;
	$costs['village_change'] = 5 * $player->village_changes;
	$costs['clan_change'] = 5 * $player->clan_changes;
	if($costs['village_change'] > 40) {
		$costs['village_change'] = 40;
	}
	if($costs['clan_change'] > 40) {
		$costs['clan_change'] = 40;
	}

    $costs['reset_ai_battles'] = 10;
    $costs['reset_pvp_battles'] = 20;

    // Stat Transfers
    $stat_transfer_points_per_min = 10;
    $stat_transfer_points_per_ak = 300;

    if($player->rank_num >= 3) {
        $stat_transfer_points_per_min += 5;
        $stat_transfer_points_per_ak = 450;
    }
    if($player->rank_num >= 4) {
        $stat_transfer_points_per_min += 5;
        $stat_transfer_points_per_ak = 600;
    }

    $stat_transfer_points_per_min += $player->forbidden_seal->stat_transfer_boost;
    $stat_transfer_points_per_ak += $player->forbidden_seal->extra_stat_transfer_points_per_ak;

    // Free stat transfers
    $free_stat_change_timer = 86400;
    $max_free_stat_change_amount = 100;
    if(System::currentYear() === 2023 && System::currentMonth() === 3 && System::currentDay() < 19) {
        $free_stat_change_timer = 4 * 86400;
        $max_free_stat_change_amount = 40000;
        $stat_transfer_points_per_min *= 10;
    }

    $free_stat_change_cooldown_left = $player->last_free_stat_change - (time() - $free_stat_change_timer);
    $free_stat_change_timer_hours = $free_stat_change_timer / 3600;

    // Clans
	$available_clans = array();

	if($player->clan) {

		$system->query("SELECT `clan_id`, `name` FROM `clans` WHERE `village` = '{$player->village->name}' AND `clan_id` != '{$player->clan->id}' AND `bloodline_only` = '0'");

		while($village_clans = $system->db_fetch()) {
			$available_clans[$village_clans['clan_id']] = stripslashes($village_clans['name']);
		}

	}

	if($player->bloodline_id && $player->clan->id != $player->bloodline->clan_id) {
		$system->query(sprintf("SELECT `clan_id`, `name` FROM `clans` WHERE `clan_id` = '%d'", $player->bloodline->clan_id));
		$result = $system->db_fetch();
		$available_clans[$result['clan_id']] = stripslashes($result['name']);
	}

	if(isset($_POST['user_reset'])) {
		try {
            if($player->team) {
                throw new Exception("You must leave your team before resetting!");
            }
            if($player->clan_office) {
                throw new Exception("You must resign from your clan office first!");
            }
            if (SenseiManager::isSensei($player->user_id, $system)) {
                throw new Exception("You must resign from being a sensei first!");
            }
			if(!isset($_POST['confirm_reset'])) {
                $confirmation_type = "confirm_reset";
                $confirmation_string = "Are you sure you want to reset your character? You will lose all your stats,
                bloodline, rank and clan. You will keep your money.";
                $submit_value = "user_reset";
                $button_value = "Reset my Account";

				require 'templates/premium/purchase_confirmation.php';
			}
            else {
                $player->level = 1;
                $player->level = 1;
                $player->rank_num = 1;
                $player->health = 100;
                $player->max_health = 100;
                $player->stamina = 100;
                $player->max_stamina = 100;
                $player->chakra = 100;
                $player->max_chakra = 100;
                $player->regen_rate = User::BASE_REGEN;
                $player->exp = User::BASE_EXP;
                $player->bloodline_id = 0;
                $player->bloodline_name = '';
                $player->clan = null;
                $player->clan_id = 0;
                $player->location = $player->village_location;
                $player->pvp_wins = 0;
                $player->pvp_losses = 0;
                $player->ai_wins = 0;
                $player->ai_losses = 0;
                $player->monthly_pvp = 0;
                $player->ninjutsu_skill = 10;
                $player->genjutsu_skill = 10;
                $player->taijutsu_skill = 10;
                $player->bloodline_skill = 0;
                $player->cast_speed = 5;
                $player->speed = 5;
                $player->intelligence = 5;
                $player->willpower = 5;

                //Bug fix: Elements previously was not cleared. -- Shadekun
                $player->elements = array();
                $player->missions_completed = array(); //Reset missions complete -- Hitori

                $player->exam_stage = 0;

                $player->updateData();

                $system->query("DELETE FROM `user_bloodlines` WHERE `user_id`='$player->user_id'");
                $system->query("UPDATE `user_inventory` SET
				`jutsu` = '',
				`items` = '',
				`bloodline_jutsu` = '',
				`equipped_jutsu` = '',
				`equipped_items` = ''
				WHERE `user_id`='$player->user_id'");


                require 'templates/premium/character_reset_complete.php';
                return true;
            }
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}
	else if(isset($_POST['name_change'])) {
		$new_name = $system->clean($_POST['new_name']);
		$akCost = $costs['name_change'];
        $deductNameChanges = 1;
		try {
			if(!$player->username_changes and $player->getPremiumCredits() < $akCost) {
				throw new Exception("You do not have enough Ancient Kunai!");
			}
			if(strlen($new_name) < User::MIN_NAME_LENGTH || strlen($new_name) > User::MAX_NAME_LENGTH) {
				throw new Exception("New user name is to short/long! Please enter a name between "
                    . User::MIN_NAME_LENGTH . " and " . User::MAX_NAME_LENGTH . " characters long.");
			}
			if($player->user_name == $new_name) {
				throw new Exception("Please select a different name than your current one.");
			}
			if(!preg_match('/^[a-zA-Z0-9_-]+$/', $new_name)) {
				throw new Exception("Only alphanumeric characters, dashes, and underscores are allowed in usernames!");
			}

			if($system->explicitLanguageCheck($new_name)) {
				throw new Exception("Inappropriate language is not allowed in usernames!");
			}

			if($player->username_changes > 0){
				$akCost = 0;
			}
            elseif(strtolower($player->user_name) == strtolower($new_name)) {
                $akCost = 0;
                $deductNameChanges = 0;
                $_POST['confirm_name_change'] = 1; //Bypass need to confirm
            }
            else {
                $deductNameChanges = 0;
            }

			$result = $system->query("SELECT `user_name` FROM `users` WHERE `user_name`='$new_name' LIMIT 1");
			if($system->db_last_num_rows) {
				$result = $system->db_fetch();
                if(strtolower($result['user_name']) == strtolower($new_name) && $result['user_name'] != $player->user_name) {
                    throw new Exception("Username already in use!");
                }
			}

			if(!isset($_POST['confirm_name_change'])) {
                $confirmation_type = 'confirm_name_change';
                $confirmation_string = "Are you sure you want to change your username?<br />
                Doing this will also change your login name to the name you select.<br />
                Changing your name will make it available for use to anyone!";
                $additional_form_data = ['new_name' => ['input_type'=>'text', 'value'=>$new_name]];
                $submit_value = "name_change";
                $button_value = "Confirm Change";

                require 'templates/premium/purchase_confirmation.php';
			}
            else {
                $sql = "UPDATE `users` SET `user_name` = '%s', `username_changes` = `username_changes` - %d WHERE `user_id` = %d LIMIT 1;";
                $system->query(sprintf($sql, $new_name, $deductNameChanges, $player->user_id));
                $player->subtractPremiumCredits($akCost, "Username change");

                $system->message("You have changed your name to $new_name.");
            }
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}

	// Gender change
	else if(isset($_POST['change_gender'])) {
        try {
            $new_gender = $system->clean($_POST['new_gender']);
            $akCost = $costs['gender_change'];
            if($player->getPremiumCredits() < $akCost) {
            throw new Exception("You do not have enough Ancient Kunai!");
            }
            if($player->gender == $new_gender) {
            throw new Exception("You are already a {$new_gender}!");
            }
            if(!in_array($new_gender, User::$genders, true)) {
            throw new Exception("Invalid gender!");
            }

            //Confirm purchase
            if(!isset($_POST['confirm_gender_change'])) {
                $confirmation_type = "confirm_gender_change";
                $confirmation_string = "Are you sure you want to change your gender to $new_gender?";
                $additional_form_data = ['new_gender' => ['input_type'=>'hidden', 'value'=>$new_gender]];
                $submit_value = 'change_gender';
                $button_value = 'Change Gender';

                require 'templates/premium/purchase_confirmation.php';
            }
            //Complete purchase
            else {
                $system->message("You have changed your gender to $new_gender.");
                $player->subtractPremiumCredits($akCost, "Gender change to {$new_gender}");
                $player->gender = $new_gender;
                $player->updateData();
            }
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
    }
	else if(isset($_POST['stat_reset'])) {
		try {
			$stat = $system->clean($_POST['stat']);
			if(array_search($stat, $player->stats) === false) {
				throw new Exception("Invalid stat!");
			}

			// Amount to reset to
			$reset_amount = 5;
			if(strpos($stat, 'skill')) {
				$reset_amount = 10;
			}

			if(!isset($_POST['confirm_stat_reset'])) {
                $confirmation_type = "confirm_stat_reset";
                $confirmation_string = "Are you sure you want to reset your " . system::unSlug($stat) .
                    " from {$player->{$stat}} to $reset_amount?";
                $additional_form_data = ['stat' => ['input_type'=>'hidden', 'value'=>$stat]];
                $submit_value = 'stat_reset';
                $button_value = 'Confirm Reset';
                require 'templates/premium/purchase_confirmation.php';
			}
            else {
                $exp = ($player->{$stat} - $reset_amount) * 10;

                $player->{$stat} = $reset_amount;
                $player->exp -= $exp;
                $system->message("You have reset your " . System::unSlug($stat). " to $reset_amount.");
            }
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if(isset($_POST['stat_allocate'])) {
		try {
			$original_stat = $system->clean($_POST['original_stat']);
			$target_stat = $system->clean($_POST['target_stat']);
			if(array_search($original_stat, $player->stats) === false) {
				throw new Exception("Invalid original stat!");
			}
			if(array_search($target_stat, $player->stats) === false) {
				throw new Exception("Invalid target stat!");
			}

            // Check for same stat
            if($original_stat == $target_stat) {
                throw new Exception("You cannot transfer points to the same stat!");
            }

            // Amount to reset to
            $reset_amount = 5;
            if(str_contains($original_stat, 'skill')) {
                $reset_amount = 10;
            }

			// Transfer amount
			$transfer_amount = (int)$system->clean($_POST['transfer_amount']);

			if($transfer_amount < 1) {
				throw new Exception("Invalid transfer amount!");
			}
			if($transfer_amount > $player->{$original_stat} - $reset_amount) {
				throw new Exception("Invalid transfer amount!");
			}

            $is_free_stat_change = $transfer_amount <= $max_free_stat_change_amount && $free_stat_change_cooldown_left <= 0;

			if($is_free_stat_change) {
				$akCost = 0;
			}
            else {
				$akCost = 1 + floor($transfer_amount / $stat_transfer_points_per_ak);
			}
            //Int and Willpower are free
            if($original_stat == 'intelligence' || $original_stat == 'willpower') {
                $akCost = 0;
            }

			if($player->getPremiumCredits() < $akCost) {
				throw new Exception("You do not have enough Ancient Kunai!");
			}

			$time = $transfer_amount / $stat_transfer_points_per_min;

			// Check for minimum stat amount
			if($player->{$original_stat} <= $reset_amount) {
				throw new Exception("Stat is already at the minimum!");
			}

			// Check for player training
			if($player->train_time) {
				throw new Exception("Please finish or cancel your training!");
			}

			 if(!isset($_POST['confirm_stat_reset'])) {
                 $confirmation_type = 'confirm_stat_reset';
                 $confirmation_string = "Are you sure you want to transfer $transfer_amount " . System::unSlug($original_stat) .
                 " to " . System::unSlug($target_stat) . "?<br />" . System::unSlug($original_stat) . ": {$player->{$original_stat}} -> " .
                 ($player->{$original_stat}-$transfer_amount) . "<br />" . System::unSlug($target_stat). ": {$player->{$target_stat}} -> " .
                 ($player->{$target_stat}+$transfer_amount) . "<br /> This will take " . System::timeRemaining($time*60, 'long', true, true);
                 $additional_form_data = [
                     'original_stat' => ['input_type' => 'hidden', 'value' => $original_stat],
                     'target_stat' => ['input_type' => 'hidden', 'value' => $target_stat],
                     'transfer_amount' => ['input_type' => 'hidden', 'value' => $transfer_amount],
                 ];
                 $submit_value = 'stat_allocate';
                 $button_value = 'Confirm Transfer';

                 require 'templates/premium/purchase_confirmation.php';
			}
            else {
                 $player->subtractPremiumCredits($akCost, "Transferred {$transfer_amount} {$original_stat} to {$target_stat}");

                 $exp = $transfer_amount * 10;
                 $player->exp -= $exp;
                 $player->{$original_stat} -= $transfer_amount;

                 $player->train_type = $target_stat;
                 $player->train_gain = $transfer_amount;
                 $player->train_time = time() + ($time * 60);

                 if ($is_free_stat_change) {
                     $player->last_free_stat_change = time();
                 }

                 $player->updateData();
                 require 'templates/premium/stat_transfer_confirmation.php';
             }
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
    else if(isset($_POST['reset_ai_battles'])) {
        try {
            $cost = $costs['reset_ai_battles'];
            if($player->getPremiumCredits() < $cost) {
                throw new Exception("You do not have enough Ancient Kunai!");
            }

            if(!isset($_POST['confirm_ai_battle_reset'])) {
                $confirmation_type = "confirm_ai_battle_reset";
                $confirmation_string = "Are you sure you want to reset your AI Battle Win/Losses?";
                $submit_value = 'reset_ai_battles';
                $button_value = 'Confirm Reset';
                require 'templates/premium/purchase_confirmation.php';
            }
            else {
                $player->subtractPremiumCredits($cost, 'reset_ai_battles');
                $player->ai_wins = 0;
                $player->ai_losses = 0;

                $system->message("You have reset your AI wins and losses to 0.");
            }
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
        $system->printMessage();
    }
    else if(isset($_POST['reset_pvp_battles'])) {
        try {
            $cost = $costs['reset_pvp_battles'];
            if($player->getPremiumCredits() < $cost) {
                throw new Exception("You do not have enough Ancient Kunai!");
            }

            if(!isset($_POST['confirm_pvp_battle_reset'])) {
                $confirmation_type = "confirm_pvp_battle_reset";
                $confirmation_string = "Are you sure you want to reset your PvP Battle Win/Losses?";
                $submit_value = 'reset_pvp_battles';
                $button_value = 'Confirm Reset';
                require 'templates/premium/purchase_confirmation.php';
            }
            else {
                $player->subtractPremiumCredits($cost, 'reset_pvp_battles');
                $player->pvp_wins = 0;
                $player->pvp_losses = 0;

                $system->message("You have reset your PvP wins and losses to 0.");
            }
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
        $system->printMessage();
    }
	else if(isset($_POST['purchase_bloodline'])) {
		try {
            $self_link .= '&view=bloodlines';
            $bloodline_id = (int) $_POST['bloodline_id'];
			$result = $system->query("SELECT `bloodline_id`, `name`, `clan_id`, `rank` FROM `bloodlines`
				WHERE `bloodline_id`='$bloodline_id' AND `rank` < 5 ORDER BY `rank` ASC");

            //BL not found
			if($system->db_last_num_rows == 0) {
				throw new Exception("Invalid bloodline!");
			}
            //Load BL data
            $result = $system->db_fetch($result);
            $akCost = $costs['bloodline'][$result['rank']];
            $bloodline_name = $result['name'];

            //Confirm purchase
            if(!isset($_POST['confirm_bloodline_purchase'])) {
                $confirmation_type = 'confirm_bloodline_purchase';
                $confirmation_string = "Are you sure you want to purchase the Bloodline $bloodline_name?";
                $additional_form_data = [
                    'bloodline_id' => ['input_type' => 'hidden', 'value'=>$bloodline_id]
                ];
                $submit_value = 'purchase_bloodline';
                $button_value = 'Receive Bloodline';
                //If player has bloodline, add warning
                if($player->bloodline) {
                    $confirmation_string .= "<br /><b>WARNING:</b><br />
                    Purchasing the Bloodline $bloodline_name will result in the loss of your current Bloodline
                    {$player->bloodline_name}. This will result in loss of all Bloodline jutsu level and unlocks and 10%
                    of your Bloodline skill!<br />
                    <b>This process can not be undone!</b><br />
                    If you are part of a clan, you may also be removed from any office and be assigned a new clan.";
                }
                require_once 'templates/premium/purchase_confirmation.php';
            }
            else {
                if ($player->bloodline_id == $bloodline_id) {
                    throw new Exception("You already have this bloodline!");
                }
                if ($player->getPremiumCredits() < $akCost) {
                    throw new Exception("You do not have enough Ancient Kunai!");
                }
                //Check clan office detail & remove player from clan data if present
                if ($player->clan && $player->clan->leader_id == $player->user_id) {
                    $system->query("UPDATE `clans` SET `leader` = '0' WHERE `clan_id` = '{$player->clan->id}'");
                } else if ($player->clan && $player->clan->elder_1_id == $player->user_id) {
                    $system->query("UPDATE `clans` SET `elder_1` = '0' WHERE `clan_id` = '{$player->clan->id}'");
                } else if ($player->clan && $player->clan->elder_2_id == $player->user_id) {
                    $system->query("UPDATE `clans` SET `elder_2` = '0' WHERE `clan_id` = '{$player->clan->id}'");
                }
                //Remove office from player data if present
                if ($player->clan_office) {
                    $player->clan_office = 0;
                }

                //Process purchase
                $player->subtractPremiumCredits($akCost, "Purchased bloodline {$bloodline_name} (#$bloodline_id)");

                // Give bloodline
                $status = Bloodline::giveBloodline(
                    system: $system,
                    bloodline_id: $bloodline_id,
                    user_id: $player->user_id,
                    display: false
                );

                $message = "You now have the bloodline <b>$bloodline_name</b>.";

                // Set clan
                $clan_id = $result['clan_id'];
                $result = $system->query("SELECT `name` FROM `clans` WHERE `clan_id` = '$clan_id' LIMIT 1");
                if ($system->db_last_num_rows > 0) {
                    $clan_result = $system->db_fetch($result);


                    $player->clan = Clan::loadFromId($system, $clan_id);
                    $player->clan_id = $clan_id;
                    $message .= "<br />With your new bloodline you have been kicked out of your previous clan, and have been accepted by
				    the " . $clan_result['name'] . " Clan.";
                }

                require 'templates/premium/bloodline_purchase_confirmation.php';
            }
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}
	else if(isset($_POST['forbidden_seal'])) {
        try {
            $seal_level = (int)$_POST['seal_level'];
            $seal_length = (int)$_POST['seal_length'];

            //Check for valid seal level
            if(!isset(ForbiddenSeal::$forbidden_seals[$seal_level]) || $seal_level === 0) {
                throw new Exception("Invalid forbidden seal!");
            }
            //Check seal lengths
            if(!isset($costs['forbidden_seal'][$seal_level][$seal_length])) {
                throw new Exception("Invalid seal length!");
            }
            $akCost = $costs['forbidden_seal'][$seal_level][$seal_length];
            //Check cost
            if($player->getPremiumCredits() < $akCost) {
                throw new Exception("You do not have enough Ancient Kunai!");
            }

            //Extend seal
            if($player->forbidden_seal->level == $seal_level) {
                $player->subtractPremiumCredits($akCost, "Extended {$player->forbidden_seal->name} by {$seal_length} days.");
                $player->forbidden_seal->addSeal($seal_level, $seal_length);
                $system->message("Seal extended!");
            }
            //Overwrite seal
            elseif($player->forbidden_seal->level > 0) {
                $overwrite = isset($_POST['confirm_seal_overwrite']);
                // Confirm change in seal... time will not be reimbursed
                if(!isset($_POST['change_forbidden_seal'])) {
                    $confirmation_type = 'change_forbidden_seal';
                    // Convert remaining premium time to days and calculate AK value
                    $akCredit = $player->forbidden_seal->calcRemainingCredit();
                    // Adjust purchase cost with minimum 0
                    $akCost -= $akCredit;
                    if ($akCost < 0) {
                       $akCost = 0;
                     }
                    $confirmation_string = "Are you sure you would like to change from your {$player->forbidden_seal->name}?<br />
                    You will lose {$system->time_remaining($player->forbidden_seal->seal_time_remaining)} of premium time.<br />
                    Up to {$akCredit} Ancient Kunai will be credited toward your purchase from existing premium time.<br />
                    <b>This can not be undone!</b>";
                    $additional_form_data = [
                        'seal_level' => ['input_type' => 'hidden', 'value' => $seal_level],
                        'seal_length' => ['input_type' => 'hidden', 'value' => $seal_length],
                    ];
                    $submit_value = 'forbidden_seal';
                    $button_value = 'Confirm Seal Change';
                    require 'templates/premium/purchase_confirmation.php';
                }
                else {
                    $message = "Purchased " . ForbiddenSeal::$forbidden_seals[$seal_level] . " seal for {$seal_length} days.";
                    if($overwrite) {
                        $message .= " This purchase removed {$system->time_remaining($player->forbidden_seal->seal_time_remaining)}" .
                            " of their {$player->forbidden_seal->name}.";
                    }
                    // Recalculate adjusted akCost
                    if ($player->forbidden_seal->level > 0) {
                        $akCredit = $player->forbidden_seal->calcRemainingCredit();
                        $akCost -= $akCredit;
                        if ($akCost < 0) {
                            $akCost = 0;
                        }
                    }
                    $player->subtractPremiumCredits($akCost, $message);
                    $player->forbidden_seal->addSeal($seal_level, $seal_length);
                    $system->message("You changed your seal!");
                }
            }
            //New seal
            else {
                $player->subtractPremiumCredits($akCost, "Purchased " . ForbiddenSeal::$forbidden_seals[$seal_level]
                    . " for {$seal_length} days.");
                //Load blank seal
                $player->forbidden_seal = new ForbiddenSeal($system, 0, 0);
                //Set new seal
                $player->forbidden_seal->addSeal($seal_level, $seal_length);
                //Load benefits for displaying market & storing in db
                $player->forbidden_seal->setBenefits();
                $player->forbidden_seal_loaded = true;
                $system->message("Seal infused!");
            }
        } catch (Exception $e) {
            $system->message($e->getMessage());
            $system->printMessage();
        }
	}
	else if(isset($_POST['change_color']) && $player->canChangeChatColor()) {
		$color = $system->clean($_POST['name_color']);

        // Premium effect
        $chat_effect = (isset($_POST['chat_effect']) ? $system->clean($_POST['chat_effect']) : "");

        if($player->premium_credits_purchased && in_array($chat_effect, ["", "sparkles"])) {
            $player->chat_effect = $chat_effect;
        }
		switch($color) {
			case 'blue':
			case 'pink':
			case 'black':
				$player->chat_color = $color;
				$system->message("Color changed!");
				break;
			case 'gold':
				if(!$player->premium_credits_purchased && !$player->isHeadAdmin()) {
					$system->message("Invalid color!");
					break;
				}
				$player->chat_color = $color;
				$system->message("Color changed!");
				break;
			 case 'green':
                if(!$player->isModerator()) {
                    $system->message("Invalid color!");
                    break;
                }
                $player->chat_color = $color;
                $system->message("Color changed");
                break;
			case 'teal':
				if (!$player->isHeadModerator()) {
					$system->message("Invalid color!");
					break;
				}
				$player->chat_color = $color;
				$system->message("Color changed");
				break;
            case 'purple':
                if (!$player->isContentAdmin()) {
                    $system->message("Invalid color!");
                    break;
                }
                $player->chat_color = $color;
                $system->message("Color changed");
                break;
			case 'red':
				if(!$player->isUserAdmin()) {
					$system->message("Invalid color!");
					break;
				}
				$player->chat_color = $color;
				$system->message("Color changed");
			break;
			/* End Shadekun edit */
			default:
				$system->message("Invalid color!");
		}
		$system->printMessage();
	}
	else if(isset($_POST['change_element']) && $player->rank_num >= 3) {
        try {
            $akCost = $costs['element_change'];
			$current_element = $system->clean($_POST['current_element']);
			$new_element = $system->clean($_POST['new_element']);
            //Player already has new element
            if(in_array($new_element, $player->elements)) {
                throw new Exception("You already attuned to the $new_element element!");
            }
            //Check player's current element is valid
            switch($player->elements[$current_element]) {
                case Jutsu::ELEMENT_FIRE:
                case Jutsu::ELEMENT_WIND:
                case Jutsu::ELEMENT_LIGHTNING:
                case Jutsu::ELEMENT_EARTH:
                case Jutsu::ELEMENT_WATER:
                    break;
                default:
                    throw new Exception("The $current_element element ({$player->elements[$current_element]}) is invalid!");
            }
            //Check that new element is valid
            switch($new_element) {
                case Jutsu::ELEMENT_FIRE:
                case Jutsu::ELEMENT_WIND:
                case Jutsu::ELEMENT_LIGHTNING:
                case Jutsu::ELEMENT_EARTH:
                case Jutsu::ELEMENT_WATER:
                    break;
                default:
                    throw new Exception("New element $new_element is invalid!");
            }
            //Check cost
            if($player->getPremiumCredits() < $akCost) {
                throw new Exception("You do not have enough Ancient Kunai!");
            }
            //Confirm purchase
            if(!isset($_POST['confirm_chakra_element_change'])) {
                $confirmation_type = 'confirm_chakra_element_change';
                $confirmation_string = "Are you sure you want to <b>forget the {$player->elements[$current_element]} nature</b>
                and any jutsus you have to <b>attune to the $new_element nature</b>?<br />
                <br />
                <b>(IMPORTANT: This is non-reversable once completed! If you want to return to your original element you
                will have to pay another fee. You will forget any elemental jutsu you currently have of this nature.)</b>";
                $additional_form_data = [
                    'current_element' => ['input_type' => 'hidden', 'value' => $current_element],
                    'new_element' => ['input_type' => 'hidden', 'value' => $new_element],
                ];
                $submit_value = 'change_element';
                $button_value = 'Change Element';

                require 'templates/premium/purchase_confirmation.php';
            }
            else {
                //Display purchase information
                $message = '';
                switch ($new_element) {
                    case Jutsu::ELEMENT_FIRE:
                        $message = "With the image of blazing fires in your mind, you flow chakra from your stomach,
                    down through your legs and into the seal on the floor. Suddenly one of the pedestals bursts into
                    fire, breaking your focus, and the elders smile and say:<br /
                    <br />\"Congratulations, you now have the Fire element. Fire is the embodiment of
                    consuming destruction, that devours anything in its path. Your Fire jutsu will be strong against Wind jutsu, as
                    they will only fan the flames and strengthen your jutsu. However, you must be careful against Water jutsu, as they
                    can extinguish your fires.\"
                    <br />";
                        break;
                    case Jutsu::ELEMENT_WIND:
                        $message = "Picturing a tempestuous tornado, you flow chakra from your stomach,
                    down through your legs and into the seal on the floor. You feel a disturbance in the room and
                    suddenly realize that a small whirlwind has formed around one of the pedestals, and the elders smile and say:<br /
                    <br />\"Congratulations, you have the Wind element. Wind is the sharpest out of all chakra natures,
                    and can slice through anything when used properly. Your Wind chakra will be strong against
                    Lightning jutsu, as you can cut and dissipate it, but you will be weak against Fire jutsu,
                    because your wind only serves to fan their flames and make them stronger.\"
                    <br />";
                        break;
                    case Jutsu::ELEMENT_LIGHTNING:
                        $message = "Imagining the feel of electricity coursing through your veins, you flow chakra from your stomach,
                    down through your legs and into the seal on the floor. Suddenly you feel a charge in the air and
                    one of the pedestals begins to spark with crackling electricity, and the elders smile and say:<br />
                    <br />\"Congratulations, you have the Lightning element. Lightning embodies pure speed, and skilled users of
                    this element physically augment themselves to swiftly strike through almost anything. Your Lightning
                    jutsu will be strong against Earth as your speed can slice straight through the slower techniques of Earth,
                    but you must be careful against Wind jutsu as they will dissipate your Lightning.\"
                    <br />";
                        break;
                    case Jutsu::ELEMENT_EARTH:
                        $message = "Envisioning stone as hard as the temple you are sitting in, you flow chakra from your stomach,
                    down through your legs and into the seal on the floor. Suddenly dirt from nowhere begins to fall off one of the
                    pedestals and the elders smile and say:<br />
                    <br />\"Congratulations, you have the Earth element. Earth
                    is the hardiest of elements and can protect you or your teammates from enemy attacks. Your Earth jutsu will be
                    strong against Water jutsu, as you can turn the water to mud and render it useless, but you will be weak to
                    Lightning jutsu, as they are one of the few types that can swiftly evade and strike through your techniques.\"
                    <br />";
                        break;
                    case Jutsu::ELEMENT_WATER:
                        $message = "With thoughts of splashing rivers flowing through your mind, you flow chakra from your stomach,
                    down through your legs and into the seal on the floor. Suddenly a small geyser erupts from one of
                    the pedestals, and the elders smile and say:<br />
                    <br />\"Congratulations, you have the Water element. Water is a versatile element that can control the flow
                    of the battle, trapping enemies or launching large-scale attacks at them. Your Water jutsu will be strong against
                    Fire jutsu because you can extinguish them, but Earth jutsu can turn your water into mud and render it useless.\"
                    <br />";
                        break;
                }

                require 'templates/premium/chakra_purchase_confirmation.php';

                // Process purchase
                $player->subtractPremiumCredits(
                    $akCost,
                    "Changed {$current_element} element from {$player->elements[$current_element]} to $new_element"
                );

                $player->getInventory();

                // Chuunin element change
                if ($player->rank_num >= 3 && $current_element === 'first') {
                    foreach ($player->jutsu as $jutsu) {
                        if ($jutsu->element == $player->elements[$current_element]) {
                            $player->removeJutsu($jutsu->id);
                        }
                    }
                    $player->elements['first'] = $new_element;
                }
                // Jonin+ element change
                else if ($player->rank_num >= 4 && $current_element === 'second') {
                    foreach ($player->jutsu as $jutsu) {
                        if ($jutsu->element == $player->elements[$current_element]) {
                            $player->removeJutsu($jutsu->id);
                        }
                    }
                    $player->elements['second'] = $new_element;
                }

                $player->updateData();
                $player->updateInventory();
            }
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
        $system->printMessage();
    }
	else if(isset($_POST['change_village']) && $player->rank_num >= 2) {
		$village = $_POST['new_village'];
        $akCost = $costs['village_change'];
		try {
			if($village == $player->village->name) {
				throw new Exception("Invalid village!");
			}

			switch($village) {
				case 'Stone':
				case 'Cloud':
				case 'Leaf':
				case 'Sand':
				case 'Mist':
					break;
				default:
					throw new Exception("Invalid village!");
					break;
			}

			if($player->team) {
				$debug = ($player->layout == 'classic_blue') ? "<br /><br />" : "";
				throw new Exception($debug . "You must leave your team first!");
			}

			if($player->getPremiumCredits() < $akCost) {
				throw new Exception("You do not have enough Ancient Kunai!");
			}

            if ($player->sensei_id) {
                if ($player->rank_num < 3) {
                    throw new Exception("You must leave your sensei first!");
                }
            }
            if (SenseiManager::isSensei($player->user_id, $system)) {
                if (SenseiManager::hasStudents($player->user_id, $system)) {
                    throw new Exception("You must leave your students first!");
                }
            }

			if(!isset($_POST['confirm_village_change'])) {
                $confirmation_type = 'confirm_village_change';
                $confirmation_string = "Are you sure you want to move from the {$player->village->name} village to the $village
                village? You will be kicked out of your clan and placed in a random clan in the new village.<br />
                <b>(IMPORTANT: This is non-reversable once completed, if you want to return to your original village
                you will have to pay a higher transfer fee)</b>";
                $additional_form_data = [
                    'new_village' => ['input_type' => 'hidden', 'value' => $village]
                ];
                $submit_value = 'change_village';
                $button_value = 'Change Village';
                require 'templates/premium/purchase_confirmation.php';
			}
            else {
                //Update clan data if player holds a seat
                if ($player->clan->leader_id == $player->user_id) {
                    $system->query("UPDATE `clans` SET `leader` = '0' WHERE `clan_id` = '{$player->clan->id}'");
                }
                else if ($player->clan->elder_1_id == $player->user_id) {
                    $system->query("UPDATE `clans` SET `elder_1` = '0' WHERE `clan_id` = '{$player->clan->id}'");
                }
                else if ($player->clan->elder_2_id == $player->user_id) {
                    $system->query("UPDATE `clans` SET `elder_2` = '0' WHERE `clan_id` = '{$player->clan->id}'");
                }
                //Remove clan seat from player if they hold seat
                if ($player->clan_office) {
                    $player->clan_office = 0;
                }

                //Remove active student applications
                if (SenseiManager::isSensei($player->user_id, $system)) {
                    SenseiManager::closeApplicationsBySensei($player->user_id, $system);
                }
                else if ($player->rank_num < 3) {
                    SenseiManager::closeApplicationsByStudent($player->user_id, $system);
                }

                // Cost
                $player->subtractPremiumCredits($akCost, "Changed villages from {$player->village->name} to $village");
                $player->village_changes++;

                // Village
                $player->village = new Village($system, $village);

                // Clan
                $result = $system->query("SELECT `clan_id`, `name` FROM `clans`
                    WHERE `village`='{$player->village->name}' AND `bloodline_only`='0'");
                if ($system->db_last_num_rows == 0) {
                    $result = $system->query("SELECT `clan_id`, `name` FROM `clans` WHERE `bloodline_only`='0'");
                }

                if (!$system->db_last_num_rows) {
                    throw new Exception("No clans available!");
                }

                $clans = array();
                $count = 0;
                while ($row = $system->db_fetch($result)) {
                    $clans[$row['clan_id']] = $row;
                    $count++;
                }

                $query = "SELECT ";
                $x = 0;
                foreach ($clans as $id => $clan) {
                    $query .= "SUM(IF(`clan_id` = $id, 1, 0)) as `$id`";
                    $x++;
                    if ($x < $count) {
                        $query .= ', ';
                    }
                }
                $query .= " FROM `users`";

                $clan_counts = array();
                $result = $system->query($query);
                $row = $system->db_fetch($result);
                $total_users = 0;
                foreach ($row as $id => $user_count) {
                    $clan_counts[$id] = $user_count;
                    $total_users += $user_count;
                }

                $average_users = round($total_users / $count);

                $clan_rolls = array();
                foreach ($clans as $id => $clan) {
                    $entries = 4;
                    if ($clan_counts[$id] > $average_users) {
                        $entries--;
                        if ($clan_counts[$id] / 3 > $average_users) {
                            $entries--;
                        }


                    }
                    for ($i = 0; $i < $entries; $i++) {
                        $clan_rolls[] = $id;
                    }

                    $clan_id = $clan_rolls[mt_rand(0, count($clan_rolls) - 1)];

                    $player->clan = Clan::loadFromId($system, $clan_id);
                    $player->clan_id = $clan_id;
                    $clan_name = $clans[$clan_id]['name'];

                    $system->message("You have moved to the $village village, and been placed in the $clan_name clan.");
                    $player->location->x = $player->village->coords->x;
                    $player->location->y = $player->village->coords->y;
                    $player->location->map_id = $player->village->coords->map_id;
                }
            }
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if(isset($_POST['change_clan']) && $player->rank_num >= 2) {
		$new_clan_id = abs((int) $_POST['clan_change_id']);
        $akCost = $costs['clan_change'];
		try {
            //Check if clan exists and playe not in clan
			$clan_exists = in_array($new_clan_id, array_keys($available_clans));
			if( ($new_clan_id == $player->clan->id) || !$clan_exists) {
				throw new Exception("Invalid clan!");
			}

			if($player->getPremiumCredits() < $akCost) {
				throw new Exception("You do not have enough Ancient Kunai!");
			}

			$clan_name = $available_clans[$new_clan_id];

			if(!isset($_POST['confirm_clan_change'])) {
                $confirmation_type = 'confirm_clan_change';
                $confirmation_string = "Are you sure you want to move from the {$player->clan->name} clan to the
                $clan_name clan?<br /><br />
                <b>(IMPORTANT: This is non-reversable once completed, if you want to return to your original clan you
                will have to pay a higher transfer fee)</b><br />";
                $additional_form_data = ['clan_change_id' => ['input_type' => 'hidden', 'value' => $new_clan_id]];
                $submit_value = 'change_clan';
                $button_value = 'Change Clan';
                require 'templates/premium/purchase_confirmation.php';
			}
            else {
                // Cost
                $player->subtractPremiumCredits(
                    $akCost,
                    "Changed clan from {$player->clan->name} ({$player->clan->id}) to $clan_name ({$new_clan_id})"
                );
                $player->clan_changes++;

                // Remove player from clan data, if seat held
                if ($player->clan->leader_id == $player->user_id) {
                    $system->query("UPDATE `clans` SET `leader` = '0' WHERE `clan_id` = '{$player->clan->id}'");
                }
                else if ($player->clan->elder_1_id == $player->user_id) {
                    $system->query("UPDATE `clans` SET `elder_1` = '0' WHERE `clan_id` = '{$player->clan->id}'");
                }
                else if ($player->clan->elder_2_id == $player->user_id) {
                    $system->query("UPDATE `clans` SET `elder_2` = '0' WHERE `clan_id` = '{$player->clan->id}'");
                }
                //Remove seat from player if held
                if($player->clan_office) {
                    $player->clan_office = 0;
                }
                //Set new clan
                $player->clan->id = $new_clan_id;
                $system->message("You have moved to the $clan_name clan.");
            }
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}

    $view = 'character_changes';
    if($player->getPremiumCredits() == 0) {
        $view = 'buy_kunai';
    }
    if(isset($_GET['view'])) {
        $view = $_GET['view'];
    }

    $kunai_per_dollar = System::KUNAI_PER_DOLLAR;

    // Select all for bloodline list
    $bloodlines = array();
    $result = $system->query("SELECT * FROM `bloodlines` WHERE `rank` < 5 ORDER BY `rank` ASC");
    if($system->db_last_num_rows > 0) {
        while($row = $system->db_fetch($result)) {
            // Prep json encoded members for use in BL list
            $row['passive_boosts'] = json_decode($row['passive_boosts']);
            $row['combat_boosts'] = json_decode($row['combat_boosts']);
            $row['jutsu'] = json_decode($row['jutsu']);
            // Add BL to list
            $bloodlines[$row['rank']][$row['bloodline_id']] = $row;
        }
    }

    $name_colors = $player->getNameColors();

    // Buying shards
    if($system->environment == System::ENVIRONMENT_DEV) {
        $paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
        $paypal_business_id = 'lsmjudoka@lmvisions.com';
    }
    else {
        $paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
        $paypal_business_id = 'lsmjudoka05@yahoo.com';
    }
    $paypal_listener_url = $system->router->base_url . 'paypal_listener.php';

    //Load premium seals
    $baseDisplay = ForbiddenSeal::$benefits[0];
    $twinSeal = new ForbiddenSeal($system, 1);
    $twinSeal->setBenefits();
    $fourDragonSeal = new ForbiddenSeal($system, 2);
    $fourDragonSeal->setBenefits();

    require "templates/premium/premium.php";
    return true;
}

function premiumCreditExchange() {
	global $system;

	global $player;

	global $self_link;
	$self_link .= '&view=buy_kunai';

    $price_min = 1.0;
	$price_max = 20.0;

	// Create offer
	if(isset($_POST['new_offer'])) {
		try {
            $premium_credits = (int) $_POST['premium_credits'];
            $money = round($_POST['money'], 1);

            if(!is_numeric($premium_credits)) {
                throw new Exception("Invalid Ancient Kunai amount!");
            }
            if($premium_credits < 1) {
                throw new Exception("Offer must contain at least one (1) Ancient Kunai!");
            }
            if(!is_numeric($money)) {
                throw new Exception("Invalid yen amount!");
            }
            if($money < $price_min || $money > $price_max) {
                throw new Exception("Offer must be between &yen;" . $price_min * 1000 . " & &yen;" . $price_max * 1000 . " each!");
            }

            // Adjust money value for processing and insertion into market
            $money = $premium_credits * $money * 1000;

            // Check financing
            if ($player->getPremiumCredits() < $premium_credits) {
                throw new Exception("You do not have enough Ancient Kunai!");
            }
            // Subtract premium_credits from user
            $player->subtractPremiumCredits($premium_credits, "Placed AK for sale on exchange");
            $player->updateData();

            //Add offer to market
            $system->query("INSERT INTO `premium_credit_exchange` (`seller`, `premium_credits`, `money`)
			VALUES ('$player->user_id', '$premium_credits', '$money')");
            if ($system->db_last_affected_rows > 0) {
                $system->message("Offer placed!");
            }
            else {
                $system->message("Error placing offer.");
            }
			$system->printMessage();
		} catch (Exception $e) {
			$system->message($e->getMessage());
			$system->printMessage();
		}
	}

	// Purchase offer
	else if(isset($_GET['purchase'])) {
		try {
			// Validate input for offer id
			$id = (int)$system->clean($_GET['purchase']);
			$result = $system->query("SELECT * FROM `premium_credit_exchange` WHERE `id`='$id' LIMIT 1");
			if($system->db_last_num_rows == 0) {
				 throw new Exception("Invalid offer!");
			}

            //Load offer
			$offer = $system->db_fetch($result);

            //Check if offer is completed
            if($offer['completed'] == 1) {
                throw new Exception("This offer has already been processed!");
            }

            // Check user has enough money
            if($player->getMoney() < $offer['money']) {
                throw new Exception("You do not have enough money!");
            }
            // Process payment
            $player->subtractMoney($offer['money'], "Purchased AK from exchange.");
            $player->addPremiumCredits($offer['premium_credits'], "Purchased AK from exchange.");
            $player->updateData();

			// Run purchase and log [NOTE: Updating first is to avoid as much server lag and possibility for glitching]
			$system->query("UPDATE `premium_credit_exchange` SET `completed`='1' WHERE `id`='$id' LIMIT 1");

            $result = $system->query("SELECT `money` FROM `users` WHERE `user_id`='{$offer['seller']}' LIMIT 1");
            $current_balance = $system->db_fetch($result)['money'] ?? null;

			$system->query("UPDATE `users` SET `money`=`money` + {$offer['money']} WHERE `user_id`='{$offer['seller']}'");

            $system->currencyLog(
                character_id: $offer['seller'],
                currency_type: System::CURRENCY_TYPE_MONEY,
                previous_balance: $current_balance,
                new_balance: $current_balance + $offer['money'],
                transaction_amount: $offer['money'],
                transaction_description: "Sold credits on AK exchange"
            );

            $log_data = "ID# {$offer['id']}; #{$offer['seller']} to #{$player->user_id} ({$player->user_name}) :: "
            . "{$offer['premium_credits']} AK for &yen;{$offer['money']}";
            $alert_message = "{$player->user_name} has purchased {$offer['premium_credits']} AK for &yen;{$offer['money']}.";

            //Add system log
			$system->log("Kunai Exchange", "Completed Sale", $log_data);
            //Notify seller of purchase
			Inbox::sendAlert($system, Inbox::ALERT_AK_OFFER_COMPLETED, $player->user_id, $offer['seller'], $alert_message);

			$system->message("Ancient Kunai purchased!");
			$system->printMessage();
		} catch(Exception $e) {
			$system->message($e->getMessage());
			$system->printMessage();
		}
	}

	// Cancel offer
	else if(isset($_GET['cancel'])) {
		try {
			// Validate input for offer id
			$id = (int)$system->clean($_GET['cancel']);
			$result = $system->query("SELECT * FROM `premium_credit_exchange` WHERE `id`='$id' LIMIT 1");
			if($system->db_last_num_rows == 0) {
				 throw new Exception("Invalid offer!");
			}

			$offer = $system->db_fetch($result);

            // Offer complete
            if($offer['completed']) {
                throw new Exception("This offer has already been processed!");
            }

			// Check offer belongs to user
			if($player->user_id != $offer['seller']) {
				throw new Exception("This is not your offer!");
			}

			// Cancel log [NOTE: Updating first is to avoid as much server lag and possibility for glitching]
			$system->query("UPDATE `premium_credit_exchange` SET `completed`='1' WHERE `id`='$id' LIMIT 1");

            $player->addPremiumCredits($offer['premium_credits'], "Cancelled AK offer on exchange");
            $player->updateData();

            $log_data = "ID# {$offer['id']}; {$offer['seller']} - Cancelled :: "
            . "{$offer['premium_credits']} for &yen;{$offer['money']}";
			$system->log("Kunai Exchange", "Cancelled Offer", $log_data);

			$system->message("Offer cancelled!");
			$system->printMessage();
		} catch(Exception $e) {
			$system->message($e->getMessage());
			$system->printMessage();
		}
	}

    $query = "SELECT * FROM `premium_credit_exchange` WHERE `completed`='0' ORDER BY `id` DESC";
	$result = $system->query($query);

	$credit_users = array();
    $offers = array();

	//If there are offers in the database
	if($system->db_last_num_rows) {
		while($row = $system->db_fetch($result)) {
            //Fetch seller information if not already done
			if(!in_array($row['seller'], $credit_users))
			{
				$user_result = $system->query("SELECT `user_name` FROM `users` WHERE `user_id`='{$row['seller']}'");
				$user_info = $system->db_fetch($user_result);
				$credit_users[$row['seller']] = $user_info['user_name'];
			}
            $row['seller_name'] = $credit_users[$row['seller']];
            $offers[] = $row;
		}
	}

    // View offers
    require 'templates/premium/premium_market_table.php';
}
