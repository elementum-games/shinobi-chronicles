<?php

require_once __DIR__ . '/../classes/PremiumShopManager.php';
require_once __DIR__ . '/../classes/notification/NotificationManager.php';

require_once 'templates/premium/purchase_confirmation.php';
require_once 'templates/premium/purchase_complete.php';

function premiumShop(): bool {
    global $system;
    global $player;
    global $self_link;
    
    $premiumShopManager = new PremiumShopManager($system, $player);

    // Clans
    $available_clans = array();

    if ($player->clan) {
        $system->db->query(
            "SELECT `clan_id`, `name` FROM `clans` WHERE `village` = '{$player->village->name}' AND `clan_id` != '{$player->clan->id}' AND `bloodline_only` = '0'"
        );

        while ($village_clans = $system->db->fetch()) {
            $available_clans[$village_clans['clan_id']] = stripslashes($village_clans['name']);
        }
    }

    if ($player->bloodline_id && $player->clan->id != $player->bloodline->clan_id) {
        $system->db->query(
            sprintf("SELECT `clan_id`, `name` FROM `clans` WHERE `clan_id` = '%d'", $player->bloodline->clan_id)
        );
        $result = $system->db->fetch();
        $available_clans[$result['clan_id']] = stripslashes($result['name']);
    }

    // Chat name colors
    $available_name_colors = $player->getNameColors();

    if (isset($_POST['user_reset'])) {
        try {
            $premiumShopManager->assertUserCanReset($player);

            if (!isset($_POST['confirm_reset'])) {
                $confirmation_type = "confirm_reset";
                $confirmation_string = "Are you sure you want to reset your character? You will lose all your stats,
                bloodline, rank and clan. You will keep your money.";
                $submit_value = "user_reset";
                $button_value = "Reset my Account";

                require 'templates/premium/purchase_confirmation.php';
                return true;
            }
            else {
                $premiumShopManager->resetUser($player);

                require 'templates/premium/character_reset_complete.php';
                return true;
            }
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
    }
    else if (isset($_POST['name_change'])) {
        $new_name = $system->db->clean($_POST['new_name']);

        try {
            if (!isset($_POST['confirm_name_change'])) {
                $premiumShopManager->assertUserCanChangeName($new_name);

                $confirmation_type = 'confirm_name_change';
                $confirmation_string = "Are you sure you want to change your username?<br />
                Doing this will also change your login name to the name you select.<br />
                Changing your name will make it available for use to anyone!";
                $additional_form_data = ['new_name' => ['input_type' => 'text', 'value' => $new_name]];
                $submit_value = "name_change";
                $button_value = "Confirm Change";

                require 'templates/premium/purchase_confirmation.php';
            } else {
               $result = $premiumShopManager->changeUserName($new_name);
            }
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
    }
    else if (isset($_POST['change_gender'])) {
        try {
            $new_gender = $system->db->clean($_POST['new_gender']);
            $ak_cost = $premiumShopManager->costs['gender_change'];
            if ($player->getPremiumCredits() < $ak_cost) {
                throw new RuntimeException("You do not have enough Ancient Kunai!");
            }
            if ($player->gender == $new_gender) {
                throw new RuntimeException("You are already a {$new_gender}!");
            }
            if (!in_array($new_gender, User::$genders, true)) {
                throw new RuntimeException("Invalid gender!");
            }

            //Confirm purchase
            if (!isset($_POST['confirm_gender_change'])) {
                $confirmation_type = "confirm_gender_change";
                $confirmation_string = "Are you sure you want to change your gender to $new_gender?";
                $additional_form_data = ['new_gender' => ['input_type' => 'hidden', 'value' => $new_gender]];
                $submit_value = 'change_gender';
                $button_value = 'Change Gender';

                require 'templates/premium/purchase_confirmation.php';
            }
            //Complete purchase
            else {
                $system->message("You have changed your gender to $new_gender.");
                $player->subtractPremiumCredits($ak_cost, "Gender change to {$new_gender}");
                $player->gender = $new_gender;
                $player->updateData();
            }
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
    }
    else if (isset($_POST['stat_reset'])) {
        try {
            $stat = $system->db->clean($_POST['stat']);
            if (!in_array($stat, $player->stats)) {
                throw new RuntimeException("Invalid stat!");
            }

            // Amount to reset to
            $reset_amount = 5;
            if (strpos($stat, 'skill')) {
                $reset_amount = 10;
            }

            if (!isset($_POST['confirm_stat_reset'])) {
                $confirmation_type = "confirm_stat_reset";
                $confirmation_string = "Are you sure you want to reset your " . system::unSlug($stat) .
                    " from {$player->{$stat}} to $reset_amount?";
                $additional_form_data = ['stat' => ['input_type' => 'hidden', 'value' => $stat]];
                $submit_value = 'stat_reset';
                $button_value = 'Confirm Reset';
                require 'templates/premium/purchase_confirmation.php';
            } else {
                $exp = ($player->{$stat} - $reset_amount) * 10;

                $player->{$stat} = $reset_amount;
                $player->exp -= $exp;
                $system->message("You have reset your " . System::unSlug($stat) . " to $reset_amount.");
            }
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
        $system->printMessage();
    }
    else if (isset($_POST['stat_allocate'])) {
        try {
            $original_stat = $system->db->clean($_POST['original_stat']);
            $target_stat = $system->db->clean($_POST['target_stat']);
            $transfer_amount = (int)$_POST['transfer_amount'];
            $transfer_speed = $system->db->clean($_POST['transfer_speed']);

            $time = $premiumShopManager->statTransferTime(
                $transfer_amount,
                $transfer_speed
            );

            if (!isset($_POST['confirm_stat_reset'])) {
                $premiumShopManager->assertUserCanTransferStat(
                    original_stat: $original_stat,
                    target_stat: $target_stat,
                    transfer_amount: $transfer_amount,
                    transfer_speed: $transfer_speed
                );

                $confirmation_type = 'confirm_stat_reset';
                $confirmation_string = "Are you sure you want to do a"
                    . ($transfer_speed == 'expedited' ? "n " : " ")
                    . "<b>" . System::unSlug($transfer_speed) . "</b>"
                    . " transfer of $transfer_amount " . System::unSlug($original_stat) .
                    " to " . System::unSlug($target_stat) . "?<br />"
                    . System::unSlug($original_stat) . ": {$player->{$original_stat}} -> "
                    . ($player->{$original_stat} - $transfer_amount) . "<br />"
                    . System::unSlug($target_stat) . ": {$player->{$target_stat}} -> "
                    . ($player->{$target_stat} + $transfer_amount) . "<br />"
                    . "Cost: {$premiumShopManager->statTransferPremiumCreditCost($transfer_amount,$transfer_speed)} AK / "
                    . "{$premiumShopManager->statTransferYenCost($transfer_amount, $transfer_speed)} yen<br />"
                    . " This will take "
                    . System::timeRemaining($time * 60, 'long', true, true);

                $additional_form_data = [
                    'original_stat' => ['input_type' => 'hidden', 'value' => $original_stat],
                    'target_stat' => ['input_type' => 'hidden', 'value' => $target_stat],
                    'transfer_amount' => ['input_type' => 'hidden', 'value' => $transfer_amount],
                    'transfer_speed' => ['input_type' => 'hidden', 'value' => $transfer_speed],
                ];

                $submit_value = 'stat_allocate';
                $button_value = 'Confirm Transfer';

                require 'templates/premium/purchase_confirmation.php';
            } else {
                $premiumShopManager->transferStat(
                    original_stat: $original_stat,
                    target_stat: $target_stat,
                    transfer_amount: $transfer_amount,
                    transfer_speed: $transfer_speed
                );

                require 'templates/premium/stat_transfer_confirmation.php';
            }
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
        $system->printMessage();
    }
    else if (isset($_POST['reset_ai_battles'])) {
        try {
            $cost = $premiumShopManager->costs['reset_ai_battles'];
            if ($player->getPremiumCredits() < $cost) {
                throw new RuntimeException("You do not have enough Ancient Kunai!");
            }

            if (!isset($_POST['confirm_ai_battle_reset'])) {
                $confirmation_type = "confirm_ai_battle_reset";
                $confirmation_string = "Are you sure you want to reset your AI Battle Win/Losses?";
                $submit_value = 'reset_ai_battles';
                $button_value = 'Confirm Reset';
                require 'templates/premium/purchase_confirmation.php';
            } else {
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
    else if (isset($_POST['reset_pvp_battles'])) {
        try {
            $cost = $premiumShopManager->costs['reset_pvp_battles'];
            if ($player->getPremiumCredits() < $cost) {
                throw new RuntimeException("You do not have enough Ancient Kunai!");
            }

            if (!isset($_POST['confirm_pvp_battle_reset'])) {
                $confirmation_type = "confirm_pvp_battle_reset";
                $confirmation_string = "Are you sure you want to reset your PvP Battle Win/Losses?";
                $submit_value = 'reset_pvp_battles';
                $button_value = 'Confirm Reset';
                require 'templates/premium/purchase_confirmation.php';
            } else {
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
    else if (isset($_POST['purchase_bloodline'])) {
        try {
            $self_link .= '&view=bloodlines';
            $bloodline_id = (int) $_POST['bloodline_id'];
            $result = $system->db->query(
                "SELECT `bloodline_id`, `name`, `clan_id`, `rank` FROM `bloodlines`
                    WHERE `bloodline_id`='$bloodline_id' AND `rank` < 5 ORDER BY `rank` ASC"
            );

            //BL not found
            if ($system->db->last_num_rows == 0) {
                throw new RuntimeException("Invalid bloodline!");
            }
            //Load BL data
            $result = $system->db->fetch($result);
            $ak_cost = $premiumShopManager->costs['bloodline'][$result['rank']];
            $bloodline_name = $result['name'];

            //Confirm purchase
            if (!isset($_POST['confirm_bloodline_purchase'])) {
                $confirmation_type = 'confirm_bloodline_purchase';
                $confirmation_string = "Are you sure you want to purchase the Bloodline $bloodline_name?";
                $additional_form_data = [
                    'bloodline_id' => ['input_type' => 'hidden', 'value' => $bloodline_id]
                ];
                $submit_value = 'purchase_bloodline';
                $button_value = 'Receive Bloodline';
                //If player has bloodline, add warning
                if ($player->bloodline) {
                    $confirmation_string .= "<br /><b>WARNING:</b><br />
                    Purchasing the Bloodline $bloodline_name will result in the loss of your current Bloodline
                    {$player->bloodline_name}. This will result in loss of all Bloodline jutsu level and unlocks and 10%
                    of your Bloodline skill!<br />
                    <b>This process can not be undone!</b><br />
                    If you are part of a clan, you may also be removed from any office and be assigned a new clan.";
                }
                require_once 'templates/premium/purchase_confirmation.php';
            } else {
                if ($player->bloodline_id == $bloodline_id) {
                    throw new RuntimeException("You already have this bloodline!");
                }
                if ($player->getPremiumCredits() < $ak_cost) {
                    throw new RuntimeException("You do not have enough Ancient Kunai!");
                }
                //Check clan office detail & remove player from clan data if present
                if ($player->clan && $player->clan->leader_id == $player->user_id) {
                    $system->db->query("UPDATE `clans` SET `leader` = '0' WHERE `clan_id` = '{$player->clan->id}'");
                } else if ($player->clan && $player->clan->elder_1_id == $player->user_id) {
                    $system->db->query("UPDATE `clans` SET `elder_1` = '0' WHERE `clan_id` = '{$player->clan->id}'");
                } else if ($player->clan && $player->clan->elder_2_id == $player->user_id) {
                    $system->db->query("UPDATE `clans` SET `elder_2` = '0' WHERE `clan_id` = '{$player->clan->id}'");
                }
                //Remove office from player data if present
                if ($player->clan_office) {
                    $player->clan_office = 0;
                }

                //Process purchase
                $player->subtractPremiumCredits($ak_cost, "Purchased bloodline {$bloodline_name} (#$bloodline_id)");

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
                $result = $system->db->query("SELECT `name` FROM `clans` WHERE `clan_id` = '$clan_id' LIMIT 1");
                if ($system->db->last_num_rows > 0) {
                    $clan_result = $system->db->fetch($result);


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
    else if (isset($_POST['forbidden_seal'])) {
        try {
            $seal_level = (int) $_POST['seal_level'];
            $seal_length = (int) $_POST['seal_length'];

            //Check for valid seal level
            if (!isset(ForbiddenSeal::$forbidden_seal_names[$seal_level]) || $seal_level === 0) {
                throw new RuntimeException("Invalid forbidden seal!");
            }
            //Check seal lengths
            if (!isset($premiumShopManager->costs['forbidden_seal'][$seal_level][$seal_length])) {
                throw new RuntimeException("Invalid seal length!");
            }
            $ak_cost = $premiumShopManager->costs['forbidden_seal'][$seal_level][$seal_length];
            //Check cost
            if ($player->getPremiumCredits() < $ak_cost) {
                throw new RuntimeException("You do not have enough Ancient Kunai!");
            }

            //Extend seal
            if ($player->forbidden_seal->level == $seal_level) {
                $player->subtractPremiumCredits($ak_cost, "Extended {$player->forbidden_seal->name} by {$seal_length} days.");
                $player->forbidden_seal->addSeal($seal_level, $seal_length);
                $system->message("Seal extended!");
            }
            //Overwrite seal
            elseif ($player->forbidden_seal->level > 0) {
                $overwrite = isset($_POST['confirm_seal_overwrite']);
                // Confirm change in seal... time will not be reimbursed
                if (!isset($_POST['change_forbidden_seal'])) {
                    $confirmation_type = 'change_forbidden_seal';
                    // Convert remaining premium time to days and calculate AK value
                    $akCredit = $player->forbidden_seal->calcRemainingCredit();
                    // Adjust purchase cost with minimum 0
                    $ak_cost -= $akCredit;
                    if ($ak_cost < 0) {
                        $ak_cost = 0;
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
                } else {
                    $message = "Purchased " . ForbiddenSeal::$forbidden_seal_names[$seal_level] . " seal for {$seal_length} days.";
                    if ($overwrite) {
                        $message .= " This purchase removed {$system->time_remaining($player->forbidden_seal->seal_time_remaining)}" .
                            " of their {$player->forbidden_seal->name}.";
                    }
                    // Recalculate adjusted akCost
                    if ($player->forbidden_seal->level > 0) {
                        $akCredit = $player->forbidden_seal->calcRemainingCredit();
                        $ak_cost -= $akCredit;
                        if ($ak_cost < 0) {
                            $ak_cost = 0;
                        }
                    }
                    $player->subtractPremiumCredits($ak_cost, $message);
                    $player->forbidden_seal->addSeal($seal_level, $seal_length);
                    $system->message("You changed your seal!");
                }
            }
            //New seal
            else {
                $player->subtractPremiumCredits($ak_cost, "Purchased " . ForbiddenSeal::$forbidden_seal_names[$seal_level]
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
    else if (isset($_POST['change_color']) && $player->canChangeChatColor()) {
        $color = $system->db->clean($_POST['name_color']);

        // Premium effect
        $chat_effect = (isset($_POST['chat_effect']) ? $system->db->clean($_POST['chat_effect']) : "");

        if ($player->premium_credits_purchased && in_array($chat_effect, ["", "sparkles"])) {
            $player->chat_effect = $chat_effect;
        }

        if (isset($available_name_colors[$color])) {
            $player->chat_color = $color;
            $system->message("Color changed!");
        } else {
            $system->message("Invalid color!");
        }

        $system->printMessage();
    }
    else if (isset($_POST['change_element']) && $player->rank_num >= 3) {
        try {
            $editing_element_index = (int)$_POST['editing_element_index'];
            $new_element = $system->db->clean($_POST['new_element']);

            $premiumShopManager->assertUserCanChangeElement($editing_element_index, $new_element);

            //Confirm purchase
            if (!isset($_POST['confirm_chakra_element_change'])) {
                $confirmation_string = "Are you sure you want to <b>forget the {$player->elements[$editing_element_index]} nature</b>
                and any jutsus you have to <b>attune to the $new_element nature</b>?<br />
                <br />
                <b>(IMPORTANT: This is non-reversable once completed! If you want to return to your original element you
                will have to pay another fee. You will forget any elemental jutsu you currently have of this nature.)</b>";

                renderPurchaseConfirmation(
                    purchase_type: 'change_element',
                    confirmation_type: 'confirm_chakra_element_change',
                    confirmation_string: $confirmation_string,
                    form_action_link: $self_link,
                    form_submit_prompt: 'Change Element',
                    additional_form_data: [
                        'editing_element_index' => ['input_type' => 'hidden', 'value' => $editing_element_index],
                        'new_element' => ['input_type' => 'hidden', 'value' => $new_element],
                    ],
                    ak_cost: $premiumShopManager->costs['element_change'],
                );
            }
            else {
                $result = $premiumShopManager->changeElement($editing_element_index, $new_element);

                renderPurchaseComplete("Chakra Element Change", $result->success_message);
            }
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
        $system->printMessage();
    }
    else if (isset($_POST['change_village']) && $player->rank_num >= 2) {
        $village = $_POST['new_village'];
        $ak_cost = $premiumShopManager->costs['village_change'];
        try {
            if ($village == $player->village->name) {
                throw new RuntimeException("Invalid village!");
            }

            switch ($village) {
                case 'Stone':
                case 'Cloud':
                case 'Leaf':
                case 'Sand':
                case 'Mist':
                    break;
                default:
                    throw new RuntimeException("Invalid village!");
                    break;
            }

            if ($player->team) {
                $debug = ($player->layout == 'classic_blue') ? "<br /><br />" : "";
                throw new RuntimeException($debug . "You must leave your team first!");
            }

            if ($player->getPremiumCredits() < $ak_cost) {
                throw new RuntimeException("You do not have enough Ancient Kunai!");
            }

            if ($player->sensei_id) {
                if ($player->rank_num < 3) {
                    throw new RuntimeException("You must leave your sensei first!");
                }
            }
            if (SenseiManager::isSensei($player->user_id, $system)) {
                if (SenseiManager::hasStudents($player->user_id, $system)) {
                    throw new RuntimeException("You must leave your students first!");
                }
            }

            if (!isset($_POST['confirm_village_change'])) {
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
            } else {
                //Update clan data if player holds a seat
                if ($player->clan->leader_id == $player->user_id) {
                    $system->db->query("UPDATE `clans` SET `leader` = '0' WHERE `clan_id` = '{$player->clan->id}'");
                } else if ($player->clan->elder_1_id == $player->user_id) {
                    $system->db->query("UPDATE `clans` SET `elder_1` = '0' WHERE `clan_id` = '{$player->clan->id}'");
                } else if ($player->clan->elder_2_id == $player->user_id) {
                    $system->db->query("UPDATE `clans` SET `elder_2` = '0' WHERE `clan_id` = '{$player->clan->id}'");
                }
                //Remove clan seat from player if they hold seat
                if ($player->clan_office) {
                    $player->clan_office = 0;
                }

                //Remove active student applications
                if (SenseiManager::isSensei($player->user_id, $system)) {
                    SenseiManager::closeApplicationsBySensei($player->user_id, $system);
                } else if ($player->rank_num < 3) {
                    SenseiManager::closeApplicationsByStudent($player->user_id, $system);
                }

                // Cost
                $player->subtractPremiumCredits($ak_cost, "Changed villages from {$player->village->name} to $village");
                $player->village_changes++;

                // Village
                $player->village = new Village($system, $village);

                // Clan
                $result = $system->db->query(
                    "SELECT `clan_id`, `name` FROM `clans`
                        WHERE `village`='{$player->village->name}' AND `bloodline_only`='0'"
                );
                if ($system->db->last_num_rows == 0) {
                    $result = $system->db->query("SELECT `clan_id`, `name` FROM `clans` WHERE `bloodline_only`='0'");
                }

                if (!$system->db->last_num_rows) {
                    throw new RuntimeException("No clans available!");
                }

                $clans = array();
                $count = 0;
                while ($row = $system->db->fetch($result)) {
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
                $result = $system->db->query($query);
                $row = $system->db->fetch($result);
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
    else if (isset($_POST['change_clan']) && $player->rank_num >= 2) {
        $new_clan_id = abs((int) $_POST['clan_change_id']);
        $ak_cost = $premiumShopManager->costs['clan_change'];
        try {
            //Check if clan exists and playe not in clan
            $clan_exists = in_array($new_clan_id, array_keys($available_clans));
            if (($new_clan_id == $player->clan->id) || !$clan_exists) {
                throw new RuntimeException("Invalid clan!");
            }

            if ($player->getPremiumCredits() < $ak_cost) {
                throw new RuntimeException("You do not have enough Ancient Kunai!");
            }

            $clan_name = $available_clans[$new_clan_id];

            if (!isset($_POST['confirm_clan_change'])) {
                $confirmation_type = 'confirm_clan_change';
                $confirmation_string = "Are you sure you want to move from the {$player->clan->name} clan to the
                $clan_name clan?<br /><br />
                <b>(IMPORTANT: This is non-reversable once completed, if you want to return to your original clan you
                will have to pay a higher transfer fee)</b><br />";
                $additional_form_data = ['clan_change_id' => ['input_type' => 'hidden', 'value' => $new_clan_id]];
                $submit_value = 'change_clan';
                $button_value = 'Change Clan';
                require 'templates/premium/purchase_confirmation.php';
            } else {
                // Cost
                $player->subtractPremiumCredits(
                    $ak_cost,
                    "Changed clan from {$player->clan->name} ({$player->clan->id}) to $clan_name ({$new_clan_id})"
                );
                $player->clan_changes++;

                // Remove player from clan data, if seat held
                if ($player->clan->leader_id == $player->user_id) {
                    $system->db->query("UPDATE `clans` SET `leader` = '0' WHERE `clan_id` = '{$player->clan->id}'");
                } else if ($player->clan->elder_1_id == $player->user_id) {
                    $system->db->query("UPDATE `clans` SET `elder_1` = '0' WHERE `clan_id` = '{$player->clan->id}'");
                } else if ($player->clan->elder_2_id == $player->user_id) {
                    $system->db->query("UPDATE `clans` SET `elder_2` = '0' WHERE `clan_id` = '{$player->clan->id}'");
                }
                //Remove seat from player if held
                if ($player->clan_office) {
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
    if ($player->getPremiumCredits() == 0) {
        $view = 'buy_kunai';
    }
    if (isset($_GET['view'])) {
        $view = $_GET['view'];
    }

    $kunai_per_dollar = System::KUNAI_PER_DOLLAR;

    // Select all for bloodline list
    $bloodlines = array();
    $result = $system->db->query("SELECT * FROM `bloodlines` WHERE `rank` < 5 ORDER BY `rank` ASC");
    if ($system->db->last_num_rows > 0) {
        while ($row = $system->db->fetch($result)) {
            // Prep json encoded members for use in BL list
            $row['passive_boosts'] = json_decode($row['passive_boosts']);
            $row['combat_boosts'] = json_decode($row['combat_boosts']);
            $row['jutsu'] = json_decode($row['jutsu']);
            // Add BL to list
            $bloodlines[$row['rank']][$row['bloodline_id']] = $row;
        }
    }

    // Buying shards
    if ($system->isDevEnvironment()) {
        $paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
        $paypal_business_id = 'lsmjudoka@lmvisions.com';
    } else {
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

function premiumCreditExchange()
{
    global $system;

    global $player;

    global $self_link;
    $self_link .= '&view=buy_kunai';

    $price_min = 1.0;
    $price_max = 20.0;

    // Create offer
    if (isset($_POST['new_offer'])) {
        try {
            $premium_credits = (int) $_POST['premium_credits'];
            $money = round($_POST['money'], 1);

            if (!is_numeric($premium_credits)) {
                throw new RuntimeException("Invalid Ancient Kunai amount!");
            }
            if ($premium_credits < 1) {
                throw new RuntimeException("Offer must contain at least one (1) Ancient Kunai!");
            }
            if (!is_numeric($money)) {
                throw new RuntimeException("Invalid yen amount!");
            }
            if ($money < $price_min || $money > $price_max) {
                throw new RuntimeException("Offer must be between &yen;" . $price_min * 1000 . " & &yen;" . $price_max * 1000 . " each!");
            }

            // Adjust money value for processing and insertion into market
            $money = $premium_credits * $money * 1000;

            // Check financing
            if ($player->getPremiumCredits() < $premium_credits) {
                throw new RuntimeException("You do not have enough Ancient Kunai!");
            }
            // Subtract premium_credits from user
            $player->subtractPremiumCredits($premium_credits, "Placed AK for sale on exchange");
            $player->updateData();

            //Add offer to market
            $system->db->query(
                "INSERT INTO `premium_credit_exchange` (`seller`, `premium_credits`, `money`)
                VALUES ('$player->user_id', '$premium_credits', '$money')"
            );
            if ($system->db->last_affected_rows > 0) {
                $system->message("Offer placed!");
            } else {
                $system->message("Error placing offer.");
            }
            $system->printMessage();
        } catch (Exception $e) {
            $system->message($e->getMessage());
            $system->printMessage();
        }
    }

    // Purchase offer
    else if (isset($_GET['purchase'])) {
        try {
            // Validate input for offer id
            $id = (int) $system->db->clean($_GET['purchase']);
            $result = $system->db->query("SELECT * FROM `premium_credit_exchange` WHERE `id`='$id' LIMIT 1");
            if ($system->db->last_num_rows == 0) {
                throw new RuntimeException("Invalid offer!");
            }

            //Load offer
            $offer = $system->db->fetch($result);

            //Check if offer is completed
            if ($offer['completed'] == 1) {
                throw new RuntimeException("This offer has already been processed!");
            }

            // Check user has enough money
            if ($player->getMoney() < $offer['money']) {
                throw new RuntimeException("You do not have enough money!");
            }
            // Process payment
            $player->subtractMoney($offer['money'], "Purchased AK from exchange.");
            $player->addPremiumCredits($offer['premium_credits'], "Purchased AK from exchange.");
            $player->updateData();

            // Run purchase and log [NOTE: Updating first is to avoid as much server lag and possibility for glitching]
            $system->db->query("UPDATE `premium_credit_exchange` SET `completed`='1' WHERE `id`='$id' LIMIT 1");

            $result = $system->db->query("SELECT `money` FROM `users` WHERE `user_id`='{$offer['seller']}' LIMIT 1");
            $current_balance = $system->db->fetch($result)['money'] ?? null;

            $system->db->query(
                "UPDATE `users` SET `money`=`money` + {$offer['money']} WHERE `user_id`='{$offer['seller']}'"
            );

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
        } catch (RuntimeException $e) {
            $system->message($e->getMessage());
            $system->printMessage();
        }
    }

    // Cancel offer
    else if (isset($_GET['cancel'])) {
        try {
            // Validate input for offer id
            $id = (int) $system->db->clean($_GET['cancel']);
            $result = $system->db->query("SELECT * FROM `premium_credit_exchange` WHERE `id`='$id' LIMIT 1");
            if ($system->db->last_num_rows == 0) {
                throw new RuntimeException("Invalid offer!");
            }

            $offer = $system->db->fetch($result);

            // Offer complete
            if ($offer['completed']) {
                throw new RuntimeException("This offer has already been processed!");
            }

            // Check offer belongs to user
            if ($player->user_id != $offer['seller']) {
                throw new RuntimeException("This is not your offer!");
            }

            // Cancel log [NOTE: Updating first is to avoid as much server lag and possibility for glitching]
            $system->db->query("UPDATE `premium_credit_exchange` SET `completed`='1' WHERE `id`='$id' LIMIT 1");

            $player->addPremiumCredits($offer['premium_credits'], "Cancelled AK offer on exchange");
            $player->updateData();

            $log_data = "ID# {$offer['id']}; {$offer['seller']} - Cancelled :: "
            . "{$offer['premium_credits']} for &yen;{$offer['money']}";
			$system->log("Kunai Exchange", "Cancelled Offer", $log_data);

			$system->message("Offer cancelled!");
			$system->printMessage();
		} catch(RuntimeException $e) {
			$system->message($e->getMessage());
			$system->printMessage();
		}
	}

    $query = "SELECT * FROM `premium_credit_exchange` WHERE `completed`='0' ORDER BY `id` DESC";
	$result = $system->db->query($query);

	$credit_users = array();
    $offers = array();

	//If there are offers in the database
	if($system->db->last_num_rows) {
		while($row = $system->db->fetch($result)) {
            //Fetch seller information if not already done
			if(!in_array($row['seller'], $credit_users))
			{
				$user_result = $system->db->query("SELECT `user_name` FROM `users` WHERE `user_id`='{$row['seller']}'");
				$user_info = $system->db->fetch($user_result);
				$credit_users[$row['seller']] = $user_info['user_name'];
			}
            $row['seller_name'] = $credit_users[$row['seller']];
            $offers[] = $row;
		}
	}

    // View offers
    require 'templates/premium/premium_market_table.php';
}