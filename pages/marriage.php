<?php

function marriage() {
    global $system;

    global $player;
    global $self_link;

    // Check for user proposal
    $proposal_sent = false;
    $result = $system->query("SELECT `user_name`, `user_id` FROM `users` WHERE `spouse`='-{$player->user_id}' LIMIT 1");
    if($system->db_last_num_rows) {
        $proposal_sent = true;
        $proposal_user = $system->db_fetch($result);
    }

    if(isset($_POST['propose'])) {
        try {
            $to_marry = $system->clean($_POST['user_name']);

            $result = $system->query("SELECT `user_id`, `user_name`, `spouse` FROM `users` WHERE `user_name`='{$to_marry}' LIMIT 1");

            if(!$system->db_last_num_rows) {
                throw new Exception("Invalid user!");
            }

            $user_to_marry = $system->db_fetch($result);

            // Only one proposal allowed
            if($proposal_sent) {
                throw new Exception("You must cancel your current proposal!");
            }
            // Cannot self marry
            if($user_to_marry['user_id'] == $player->user_id && !$player->isUserAdmin()) {
                throw new Exception("You cannot marry yourself!");
            }
            // Existing proposal/marriage
            if($user_to_marry['spouse'] != 0) {
                $to_pend = ($user_to_marry['spouse'] < 0) ? "pending marriage!" : "spouse!";

                throw new Exception("{$user_to_marry['user_name']} already has a " . $to_pend);
            }

            $result = $system->query("SELECT `user_name` FROM `users` WHERE `spouse`='-{$user_to_marry['user_id']}'");
            if($system->db_last_num_rows) {
                throw new Exception("{$user_to_marry['user_name']} has a pending marriage!");
            }

            // Blacklist check
            $blacklist = $system->query("SELECT `blocked_ids` FROM `blacklist` WHERE `user_id`='{$user_to_marry['user_id']}' LIMIT 1");
            if($system->db_last_num_rows != 0){
                $blacklist = $system->db_fetch($blacklist);
                $blacklist = json_decode($blacklist['blocked_ids'], true);

                if(array_key_exists($player->user_id, $blacklist)) {
                    throw new Exception("{$user_to_marry['user_name']} has chosen not to receive marriage proposals!");
                }
            }

            if(array_key_exists($user_to_marry['user_id'], $player->blacklist)) {
                throw new Exception("You cannot send proposals to users on your blacklist!");
            }

            // Send proposal
            $system->query("UPDATE `users` SET `spouse`='-$player->user_id' WHERE `user_id`='{$user_to_marry['user_id']}' LIMIT 1");
            if($system->db_last_affected_rows) {
                $proposal_sent = true;
                $proposal_user = [
                    'user_id' => $user_to_marry['user_id'],
                    'user_name' => $user_to_marry['user_name'],
                ];
                $system->message("You have proposed to {$user_to_marry['user_name']}!");
            }
            else {
                $system->message("Error proposing!");
            }
        }catch (Exception $e) {
            $system->message($e->getMessage());
        }
    }
    if(isset($_POST['cancel_proposal']) && $proposal_sent) {
        //Cancel request use from above result
        $system->query("UPDATE `users` SET `spouse`='0' WHERE `user_id`='{$proposal_user['user_id']}' LIMIT 1");
        if($system->db_last_affected_rows) {
            $proposal_sent = false;
            $system->message("You have cancelled you proposal!");
        }
        else {
            $system->message("Error cancelling proposal!");
        }
    }

    if(isset($_POST['accept_proposal'])) {
        try {
            $proposer_id = substr($player->spouse, 1);
            $accept_time = time();

            $result = $system->query("SELECT `user_name` FROM `users` WHERE `user_id`='$proposer_id' LIMIT 1");

            if (!$system->db_last_num_rows) {
                throw new Exception("Invalid proposal id!");
            }

            $user_name = $system->db_fetch($result)['user_name'];


            $system->query("UPDATE `users` SET `spouse`='{$player->user_id}', `marriage_time`='$accept_time' 
                WHERE `user_id`='$proposer_id' LIMIT 1");
            if(!$system->db_last_affected_rows) {
                throw new Exception("Error accepting proposal!");
            }

            $player->spouse = $proposer_id;
            $player->marriage_time = $accept_time;
            $player->updateData();

            $system->send_pm($player->user_id, $proposer_id, "Your Proposal",
                "I accept!", $player->staff_level);

            $system->message("You got hitched!");
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
    }
    if(isset($_POST['deny_proposal'])) {
        $proposer_id = substr($player->spouse, 1);

        $result = $system->query("SELECT `user_name` FROM `users` WHERE `user_id`='$proposer_id' LIMIT 1");

        $user_name = false;
        if($system->db_last_num_rows) {
            $user_name = $system->db_fetch($result)['user_name'];
            $system->query("UPDATE `users` SET `spouse`='0' WHERE `user_id`='$proposer_id' LIMIT 1");
        }

        $player->spouse = 0;
        $player->updateData();

        if($user_name) {
            $system->send_pm($player->user_id, $proposer_id, 'Your Proposal',
                "I cannot accept your proposal.", $player->staff_level);
            $system->message("You have rejected $user_name's proposal!");
        }
        else {
            $system->message("Proposal rejected!");
        }
    }

    if(isset($_POST['confirm_divorce'])) {
        try {
            $result = $system->query("SELECT user_id, user_name, spouse FROM users WHERE user_id ='$player->spouse' LIMIT 1");
            /*if ($system->db_last_num_rows) {
                $current_marriage = $system->db_fetch($result);

                // All data matches, process divorce.
                if (intval($current_marriage['spouse']) === $player->user_id) {
                    $system->query("UPDATE `users` SET `spouse`='0', `marriage_time`='0' WHERE `user_id`='$player->spouse' LIMIT 1");
                    if (!$system->db_last_affected_rows) {
                        throw new Exception("Error processing divorce!");
                    }
                }
            }*/

            if ($system->db_last_num_rows) {
                $players_spouse = $system->db_fetch($result);

                // Both Players are married to eachother. Clear marriage.
                if (intval($players_spouse['spouse']) === $player->user_id) {
                    $system->query("UPDATE `users` SET `spouse`='0', `marriage_time`='0' WHERE `user_id`='$player->spouse' LIMIT 1");
                    if (!$system->db_last_affected_rows) {
                        throw new Exception("Error processing divorce!");
                    }
                }
                else {
                    // Handle weird chains of marriage. A -> B, B -> C, C -> A
                    $spouses_spouse_result = $system->query("SELECT user_id, user_name, spouse FROM users WHERE user_id =" . $players_spouse['spouse'] . " LIMIT 1");
                    if (!$system->db_last_num_rows) {
                        throw new Exception("Failed to fetch spouse information");
                    }

                    // Spouses Spouse = Player married to the spouse of player B -> C Chain
                    $players_spouses_spouse = $system->db_fetch($spouses_spouse_result);

                    /* Player's spouse is mixed up. Both players are not married to each other.
                    Clear player's spouse marriage chain. */
                    if (intval($players_spouse['user_id']) !== intval($players_spouses_spouse['spouse'])) {
                        // Clear the marriage of the player's spouse.
                        $system->query(
                            "UPDATE `users` SET `spouse`='0', `marriage_time`='0' 
                                    WHERE `user_id`='$player->spouse' 
                                    OR `user_id`=" . $players_spouse['spouse'] . " LIMIT 2"
                        );
                        if (!$system->db_last_affected_rows) {
                            throw new Exception("Error processing divorce!");
                        }
                    }

                    // Final C -> A check, is anyone else married to the player.
                    $spouse_player_result = $system->query(
                        "SELECT user_id, user_name, spouse FROM users WHERE spouse ='$player->user_id' LIMIT 1"
                    );

                    // Another player has an unexpected marriage with player
                    if ($system->db_last_num_rows) {
                        $spouse_player = $system->db_fetch($spouse_player_result);

                        $system->query("UPDATE `users` SET `spouse`='0', `marriage_time`='0' WHERE `user_id`=" . $spouse_player['user_id'] . " LIMIT 1");
                        if (!$system->db_last_affected_rows) {
                            throw new Exception("Error processing divorce!");
                        }
                    }
                }
            }

            $system->message("You have divorced $player->spouse_name!");

            $player->spouse = 0;
            $player->spouse_name = '';
            $player->marriage_time = 0;
            $player->updateData();
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
    }


    // If user has a proposal, load potential spouses information
    if($player->spouse < 0) {
        $proposer_id = substr($player->spouse, 1);
        $proposal_result = $system->query("SELECT `user_id`, `user_name` FROM `users` WHERE `user_id`='$proposer_id' LIMIT 1");

        if(!$system->db_last_num_rows) {
            $proposal_user = false;
            $system->message("Invalid proposal!");
        }

        $proposal_user = $system->db_fetch($proposal_result);
    }

    // Load spouse data
    $spouseError = false;
    if($player->spouse > 0) {
        $result = $system->query("SELECT `user_name`, `location`, `money`, `premium_credits` FROM `users` WHERE `user_id`='$player->spouse' LIMIT 1");
        if(!$system->db_last_num_rows) {
            $spouseError = true;
        }
        $spouse = $system->db_fetch($result);
    }

    if($system->message && !$system->message_displayed) {
        $system->printMessage();
    }
    require 'templates/marriage.php';
}