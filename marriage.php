<?php

function marriage() {
    global $system;

    global $player;
    global $self_link;

    if(isset($_POST['propose'])) {
        try {
            $to_marry = $system->clean($_POST['user_name']);

            $result = $system->query("SELECT `user_id`, `user_name`, `spouse` FROM `users` WHERE `user_name`='{$to_marry}' LIMIT 1");

            if(!$system->db_last_num_rows) {
                throw new Exception("Invalid user!");
            }

            $user_to_marry = $system->db_fetch($result);

            // Cannot self marry
            if($user_to_marry['user_id'] == $player->user_id && !$player->isUserAdmin() || !$player->isHeadAdmin()) {
                throw new Exception("You cannot marry yourself!");
            }
            // Existing proposal/marriage
            if($user_to_marry['spouse'] != 0) {
                $to_pend = ($user_to_marry['spouse'] < 0) ? "pending marriage!" : "spouse!";

                throw new Exception("{$user_to_marry['user_name']} already has a " . $to_pend);
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

            // Send proposal
            $system->query("UPDATE `users` SET `spouse`='-$player->user_id' WHERE `user_id`='{$user_to_marry['user_id']}' LIMIT 1");
            if($system->db_last_affected_rows) {
                $player->spouse = -$user_to_marry['user_id'];

                $system->message("You have proposed to {$user_to_marry['user_name']}!");
            }
            else {
                $system->message("Error proposing!");
            }
        }catch (Exception $e) {
            $system->message($e->getMessage());
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
            $system->query("UPDATE `users` SET `spouse`='0', `marriage_time`='0' WHERE `user_id`='$player->spouse' LIMIT 1");
            if(!$system->db_last_affected_rows) {
                throw new Exception("Error processing divorce!");
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