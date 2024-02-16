<?php

function marriage() {
    global $system;

    global $player;
    global $self_link;

    // Check for user proposal
    $proposal_sent = false;
    $result = $system->db->query(
        "SELECT `user_name`, `user_id` FROM `users` WHERE `spouse`='-{$player->user_id}' LIMIT 1"
    );
    if($system->db->last_num_rows) {
        $proposal_sent = true;
        $proposal_user = $system->db->fetch($result);
    }

    if(isset($_POST['propose'])) {
        try {
            $to_marry = $system->db->clean($_POST['user_name']);

            $result = $system->db->query(
                "SELECT `user_id`, `user_name`, `spouse` FROM `users` WHERE `user_name`='{$to_marry}' LIMIT 1"
            );

            if(!$system->db->last_num_rows) {
                throw new RuntimeException("Invalid user!");
            }

            $user_to_marry = $system->db->fetch($result);

            // Only one proposal allowed
            if($proposal_sent) {
                throw new RuntimeException("You must cancel your current proposal!");
            }
            // Cannot self marry
            if($user_to_marry['user_id'] == $player->user_id && !$player->isUserAdmin()) {
                throw new RuntimeException("You cannot marry yourself!");
            }
            // Existing proposal/marriage
            if($user_to_marry['spouse'] != 0) {
                $to_pend = ($user_to_marry['spouse'] < 0) ? "pending marriage!" : "spouse!";

                throw new RuntimeException("{$user_to_marry['user_name']} already has a " . $to_pend);
            }

            $result = $system->db->query(
                "SELECT `user_name` FROM `users` WHERE `spouse`='-{$user_to_marry['user_id']}'"
            );
            if($system->db->last_num_rows) {
                throw new RuntimeException("{$user_to_marry['user_name']} has a pending marriage!");
            }

            // Blacklist checks
            $user_to_marry_blacklist = new Blacklist(
                system: $system,
                user_id: $user_to_marry['user_id']
            );
            if($user_to_marry_blacklist->userBlocked($player->user_id)) {
                throw new RuntimeException("{$user_to_marry['user_name']} has chosen not to receive marriage proposals!");
            }

            if($player->blacklist->userBlocked($user_to_marry['user_id'])) {
                throw new RuntimeException("You cannot send proposals to users on your blacklist!");
            }

            // Send proposal
            $system->db->query(
                "UPDATE `users` SET `spouse`='-$player->user_id' WHERE `user_id`='{$user_to_marry['user_id']}' LIMIT 1"
            );
            if($system->db->last_affected_rows) {
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
        }catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }
    if(isset($_POST['cancel_proposal']) && $proposal_sent) {
        //Cancel request use from above result
        $system->db->query("UPDATE `users` SET `spouse`='0' WHERE `user_id`='{$proposal_user['user_id']}' LIMIT 1");
        if($system->db->last_affected_rows) {
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

            $result = $system->db->query("SELECT `user_name` FROM `users` WHERE `user_id`='$proposer_id' LIMIT 1");

            if (!$system->db->last_num_rows) {
                throw new RuntimeException("Invalid proposal id!");
            }

            $user_name = $system->db->fetch($result)['user_name'];


            $system->db->query(
                "UPDATE `users` SET `spouse`='{$player->user_id}', `marriage_time`='$accept_time' 
                    WHERE `user_id`='$proposer_id' LIMIT 1"
            );
            if(!$system->db->last_affected_rows) {
                throw new RuntimeException("Error accepting proposal!");
            }

            $player->spouse = $proposer_id;
            $player->marriage_time = $accept_time;
            $player->updateData();

            $system->message("You got hitched!");
        } catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }
    if(isset($_POST['deny_proposal'])) {
        $proposer_id = substr($player->spouse, 1);

        $result = $system->db->query("SELECT `user_name` FROM `users` WHERE `user_id`='$proposer_id' LIMIT 1");

        $user_name = false;
        if($system->db->last_num_rows) {
            $user_name = $system->db->fetch($result)['user_name'];
            $system->db->query("UPDATE `users` SET `spouse`='0' WHERE `user_id`='$proposer_id' LIMIT 1");
        }

        $player->spouse = 0;
        $player->updateData();

        if($user_name) {
            $system->message("You have rejected $user_name's proposal!");
        }
        else {
            $system->message("Proposal rejected!");
        }
    }

    if(isset($_POST['confirm_divorce'])) {
        try {
            $result = $system->db->query(
                "SELECT user_id, user_name, spouse FROM users WHERE user_id ='$player->spouse' LIMIT 1"
            );
            /*if ($system->db->last_num_rows) {
                $current_marriage = $system->db->fetch($result);

                // All data matches, process divorce.
                if (intval($current_marriage['spouse']) === $player->user_id) {
                    $system->db->query("UPDATE `users` SET `spouse`='0', `marriage_time`='0' WHERE `user_id`='$player->spouse' LIMIT 1");
                    if (!$system->db->last_affected_rows) {
                        throw new RuntimeException("Error processing divorce!");
                    }
                }
            }*/

            if ($system->db->last_num_rows) {
                $players_spouse = $system->db->fetch($result);

                // Both Players are married to eachother. Clear marriage.
                if (intval($players_spouse['spouse']) === $player->user_id) {
                    $system->db->query(
                        "UPDATE `users` SET `spouse`='0', `marriage_time`='0' WHERE `user_id`='$player->spouse' LIMIT 1"
                    );
                    if (!$system->db->last_affected_rows) {
                        throw new RuntimeException("Error processing divorce!");
                    }
                }
                else {
                    // Handle weird chains of marriage. A -> B, B -> C, C -> A
                    $spouses_spouse_result = $system->db->query(
                        "SELECT user_id, user_name, spouse FROM users WHERE user_id =" . $players_spouse['spouse'] . " LIMIT 1"
                    );
                    if (!$system->db->last_num_rows) {
                        throw new RuntimeException("Failed to fetch spouse information");
                    }

                    // Spouses Spouse = Player married to the spouse of player B -> C Chain
                    $players_spouses_spouse = $system->db->fetch($spouses_spouse_result);

                    /* Player's spouse is mixed up. Both players are not married to each other.
                    Clear player's spouse marriage chain. */
                    if (intval($players_spouse['user_id']) !== intval($players_spouses_spouse['spouse'])) {
                        // Clear the marriage of the player's spouse.
                        $system->db->query(
                            "UPDATE `users` SET `spouse`='0', `marriage_time`='0' 
                                    WHERE `user_id`='$player->spouse' 
                                    OR `user_id`=" . $players_spouse['spouse'] . " LIMIT 2"
                        );
                        if (!$system->db->last_affected_rows) {
                            throw new RuntimeException("Error processing divorce!");
                        }
                    }

                    // Final C -> A check, is anyone else married to the player.
                    $spouse_player_result = $system->db->query(
                        "SELECT user_id, user_name, spouse FROM users WHERE spouse ='$player->user_id' LIMIT 1"
                    );

                    // Another player has an unexpected marriage with player
                    if ($system->db->last_num_rows) {
                        $spouse_player = $system->db->fetch($spouse_player_result);

                        $system->db->query(
                            "UPDATE `users` SET `spouse`='0', `marriage_time`='0' WHERE `user_id`=" . $spouse_player['user_id'] . " LIMIT 1"
                        );
                        if (!$system->db->last_affected_rows) {
                            throw new RuntimeException("Error processing divorce!");
                        }
                    }
                }
            }

            $system->message("You have divorced $player->spouse_name!");

            $player->spouse = 0;
            $player->spouse_name = '';
            $player->marriage_time = 0;
            $player->updateData();
        } catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }


    // If user has a proposal, load potential spouses information
    if($player->spouse < 0) {
        $proposer_id = substr($player->spouse, 1);
        $proposal_result = $system->db->query(
            "SELECT `user_id`, `user_name` FROM `users` WHERE `user_id`='$proposer_id' LIMIT 1"
        );

        if(!$system->db->last_num_rows) {
            $proposal_user = false;
            $system->message("Invalid proposal!");
        }

        $proposal_user = $system->db->fetch($proposal_result);
    }

    // Load spouse data
    $spouseError = false;
    if($player->spouse > 0) {
        $result = $system->db->query(
            "SELECT `user_name`, `location`, `money`, `premium_credits` FROM `users` WHERE `user_id`='$player->spouse' LIMIT 1"
        );
        if(!$system->db->last_num_rows) {
            $spouseError = true;
        }
        $spouse = $system->db->fetch($result);
    }

    if($system->message && !$system->message_displayed) {
        $system->printMessage();
    }
    require 'templates/marriage.php';
}