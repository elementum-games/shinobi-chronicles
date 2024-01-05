<?php

require_once __DIR__ . '/../classes/RankManager.php';

function team() {
    global $system;
    global $player;
    global $self_link;

    $RANK_NAMES = RankManager::fetchNames($system);

    //Process these requests prior to display to avoid having to reload page
    if(isset($_POST['create_team'])) {
        $name = $system->db->clean($_POST['name']);
        $name_len = strlen($name);
        $min_name_len = Team::MIN_NAME_LENGTH;
        $max_name_len = Team::MAX_NAME_LENGTH;

        try {
            // Name validation
            if($name_len < $min_name_len) {
                throw new RuntimeException("Please enter a name longer than " . ($min_name_len - 1) . " characters!");
            }
            if($name_len > $max_name_len) {
                throw new RuntimeException("Please enter a name shorter than " . ($max_name_len + 1) . " characters!");
            }
            if(!preg_match('/^[a-zA-Z0-9 _-]+$/', $name)) {
                throw new RuntimeException("Only alphanumeric characters, dashes, space, and underscores are allowed in names!");
            }

            //Check for at least 3 letters
            $letter_count = 0;
            $symbol_count = 0;
            for($i = 0; $i < $name_len; $i++) {
                if(ctype_alpha($name[$i])) {
                    $letter_count++;
                }
                else {
                    $symbol_count++;
                }
            }
            if($symbol_count >= $letter_count) {
                throw new RuntimeException("Name must be more than half letters!");
            }

            //Explicit language
            if($system->explicitLanguageCheck($name)) {
                throw new RuntimeException("Inappropriate language is not allowed in team names!");
            }

            //Check if team exists
            $system->db->query("SELECT `team_id` FROM `teams` WHERE `name`='$name' LIMIT 1");
            if($system->db->last_num_rows > 0) {
                throw new RuntimeException("Team name already in use!");
            }

            //Create team
            $created = Team::create(
                $system,
                $name,
                $player->village->name,
                $player->user_id
            );

            if($created) {
                $system->message("Team created!");
                $team_id = $system->db->last_insert_id;
                $player->team = Team::findById($system, $team_id);

            }
            else {
                $system->message("Error create team! If this continues, contact support.");
            }
        }catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }
    else if(isset($_GET['accept_invite'])) {
        try {
            $team_id = $player->team_invite;
            $team = Team::findById($system, $team_id);
            if($team == null) {
                throw new RuntimeException("Invalid team!");
            }

            $team->addMember($player);

            $system->message("You have joined <b>{$team->name}</b>!");
        }catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }
    else if(isset($_GET['decline_invite'])) {
        $player->team_invite = 0;
        $system->db->query("UPDATE `users` SET `team_id`=0 WHERE `user_id`='{$player->user_id}' LIMIT 1");
        $system->message("Invite declined!");
    }
    else if(isset($_GET['leave_team']) && $player->team) {
        try {
            $members = $player->team->members;
            $player_position = false;
            $count = 0;

            //Count number of members
            foreach($members as $position => $member) {
                if($member != 0) {
                    $count++;
                }

                if($member == $player->user_id) {
                    $player_position = $position;
                }
            }

            //Leader must transfer leadership before leaving
            if($player->user_id == $player->team->leader && $count > 1) {
                throw new RuntimeException("You must first transfer leadership!");
            }

            if(!isset($_GET['leave_confirm'])) {
                require "templates/team/leave_confirm.php";
            }
            else {
                //Delete team if leader only
                if($count == 1) {
                    $system->db->query("DELETE FROM `teams` WHERE `team_id`='{$player->team->id}' LIMIT 1");
                    if($system->db->last_affected_rows > 0) {
                        $system->message("You have left your team.");
                        $player->team = null;
                    }
                    else {
                        $system->message("Error leaving team!");
                    }
                }
                //Shift member ids
                else {
                    if($player_position !== false) {
                        unset($members[$player_position]);
                        $members[] = 0;
                    }
                    $members = json_encode($members);

                    // Update team
                    $system->db->query(
                        "UPDATE `teams` SET `members`='$members' WHERE `team_id`='{$player->team->id}' LIMIT 1"
                    );
                    if($system->db->last_affected_rows > 0) {
                        $system->message("You have left your team.");
                        $player->team = null;
                    }
                    else {
                        $system->message("Error leaving team!");
                    }
                }
            }
        }catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }

    if(!$player->team) {
        $team_invited_to = null;
        if($player->team_invite) {
            $team_id = $player->team_invite;
            $team_invited_to = Team::findById($system, $team_id);

            if($team_invited_to == null) {
                echo "Invalid team.";
                $player->team_invite = 0;
            }
            else {
                $team_invited_to_leader = $team_invited_to->fetchLeader();
            }
        }

        //Display message
        if($system->message && !$system->message_displayed) {
            $system->printMessage();
        }
        require "templates/team/create_team.php";
    }
    else {
        if(isset($_GET['join_mission']) && $player->team->mission_id) {
            if ($player->mission_id) {
                $system->message("You are already on a mission!");
            }
            else {
                $mission_id = $player->team->mission_id;
                $mission = new Mission($mission_id, $player, $player->team);

                $player->mission_id = $mission_id;
                $player->log(User::LOG_MISSION, "Team Mission ID #{$mission_id}");

                $system->message("Mission joined!");

                // Create notification
                $result = $system->db->query("SELECT `name` FROM `missions` WHERE `mission_id` = {$mission_id}");
                $mission_name = $system->db->fetch($result)['name'];
                require_once __DIR__ . '/../classes/notification/NotificationManager.php';
                if ($player->mission_stage['action_type'] == 'travel') {
                    $mission_location = TravelCoords::fromDbString($player->mission_stage['action_data']);
                    $new_notification = new MissionNotificationDto(
                        type: "mission_team",
                        message: $mission_name . ": Travel to " . $mission_location->x . ":" . $mission_location->y,
                        user_id: $player->user_id,
                        created: time(),
                        mission_rank: "Team",
                        alert: false,
                    );
                    NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
                }
                else {
                    require_once __DIR__ . '/../classes/notification/NotificationManager.php';
                    $new_notification = new MissionNotificationDto(
                        type: "mission_team",
                        message: $mission_name . " in progress",
                        user_id: $player->user_id,
                        created: time(),
                        mission_rank: "Team",
                        alert: false,
                    );
                    NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
                }
            }
        }

        //Leader controls
        if($player->user_id == $player->team->leader) {
            if(isset($_POST['transfer_leader'])) {
                try {
                    $new_leader = (int) $_POST['user_id'];
                    $team = $player->team;

                    if($new_leader == $team->leader || $new_leader == 0) {
                        throw new RuntimeException("You must select a new leader!");
                    }
                    if(!in_array($new_leader, $team->members)) {
                        throw new RuntimeException("Invalid replacement!");
                    }

                    $result = $system->db->query(
                        "SELECT `user_name` FROM `users` WHERE `user_id`='{$new_leader}' LIMIT 1"
                    );
                    if(!$system->db->last_num_rows) {
                        throw new RuntimeException("Invalid leader!");
                    }
                    $new_leader_name = $system->db->fetch($result)['user_name'];

                    if(!isset($_POST['confirm'])) {
                        require "templates/team/transfer_confirm.php";
                    }
                    else {
                        $system->db->query(
                            "UPDATE `teams` SET `leader`='{$new_leader}' WHERE `team_id`='{$player->team->id}' LIMIT 1"
                        );
                        if($system->db->last_affected_rows) {
                            $player->team->leader = $new_leader;
                            $system->message("Leadership transferred!");
                        }
                        else {
                            $system->messsage("Error transferring leadership!");
                        }
                    }
                } catch (RuntimeException $e) {
                    $system->message($e->getMessage());
                }
            }
            else if(isset($_POST['set_boost'])) {
                try {
                    $boost_type = $system->db->clean($_POST['boost_type']);
                    $boost_size = $system->db->clean($_POST['boost_size']);

                    $allowed_boosts = Team::$allowed_boosts;

                    if(!isset($allowed_boosts[$boost_type])) {
                        throw new RuntimeException("Invalid boost!");
                    }
                    if(!isset($allowed_boosts[$boost_type][$boost_size])) {
                        throw new RuntimeException("Invalid boost length!");
                    }

                    $player->team->setBoost($boost_type, $boost_size);
                    $system->message("Boost set!");
                }catch (RuntimeException $e) {
                    $system->message($e->getMessage());
                }
            }
            else if(isset($_GET['invite'])) {
                try {
                    $username = $system->db->clean($_GET['user_name']);
                    $result = $system->db->query(
                        "SELECT `user_id`, `rank`, `team_id`, `village` FROM `users` WHERE `user_name`='{$username}' LIMIT 1"
                    );

                    if($system->db->last_num_rows == 0) {
                        throw new RuntimeException("Invalid user!");
                    }

                    $user_data = $system->db->fetch($result);

                    if($user_data['rank'] < Team::MIN_RANK) {
                        throw new RuntimeException("Team members must be a " . $RANK_NAMES[Team::MIN_RANK] . " or higher!");
                    }
                    if($user_data['village'] != $player->village->name) {
                        throw new RuntimeException("You can only invite members from your village! ");
                    }
                    if(!empty($user_data['team_id'])) {
                        throw new RuntimeException("Player is already in a team or is invited to one!");
                    }

                    $system->db->query(
                        "UPDATE `users` SET `team_id`='invite:{$player->team->id}' WHERE `user_id`='{$user_data['user_id']}' LIMIT 1"
                    );
                    if($system->db->last_affected_rows) {
                        $system->message("Player invited!");
                    }
                    else {
                        $system->message("Error inviting player!");
                    }
                }catch (RuntimeException $e) {
                    $system->message($e->getMessage());
                }
            }
            else if(isset($_POST['kick'])) {
                try{
                    $user_to_kick = (int) $_POST['user_id'];

                    $members = $player->team->members;
                    $kick_key = false;
                    $count = 0;

                    //Count members
                    foreach($members as $member_key => $member_id) {
                        if($member_id != 0) {
                            $count++;
                        }
                        if($member_id == $user_to_kick) {
                            $kick_key = $member_key;
                        }
                    }

                    if($kick_key === false) {
                        throw new RuntimeException("Invalid user key!");
                    }

                    $result = $system->db->query(
                        "SELECT `user_id`, `user_name` FROM `users` WHERE `user_id`='{$user_to_kick}' LIMIT 1"
                    );
                    if($system->db->last_num_rows == 0) {
                        throw new RuntimeException("Invalid user!");
                    }
                    $user_name = $system->db->fetch($result)['user_name'];

                    if(!isset($_POST['kick_confirm'])) {
                        require "templates/team/kick_confirm.php";
                    }
                    else {
                        unset($members[$kick_key]);
                        $members[] = 0;

                        $player->team->members = $members;
                        $members = json_encode($members);

                        $system->db->query(
                            "UPDATE `teams` SET `members`='{$members}' WHERE `team_id`='{$player->team->id}' LIMIT 1"
                        );
                        $system->db->query("UPDATE `users` SET `team_id`='0' WHERE `user_id`='{$user_to_kick}' LIMIT 1");

                        if($system->db->last_affected_rows > 0) {
                            $system->message("You have kicked <b>$user_name</b>!");
                        }
                        else {
                            $system->message("Error kicking <b>$user_name</b>!");
                        }
                    }
                }catch (RuntimeException $e) {
                    $system->message($e->getMessage());
                }
            }
            else if(isset($_POST['logo_link'])) {
                try {
                    $logo_link = $system->db->clean($_POST['logo_link']);
                    $system->db->query(
                        "UPDATE `teams` SET `logo`='{$logo_link}' WHERE `team_id`='{$player->team->id}' LIMIT 1"
                    );

                    if($system->db->last_affected_rows) {
                        $system->message("Logo updated!");
                    }
                    else {
                        $system->message("Error updating logo!");
                    }
                }catch (RuntimeException $e) {
                    $system->message($e->getMessage());
                }
            }
            else if(isset($_POST['start_mission'])) {
                try {
                    $mission_id = (int) $_POST['mission_id'];
                    $result = $system->db->query(
                        "SELECT `mission_id` FROM `missions` WHERE `mission_id`='{$mission_id}' AND `mission_type`=3 LIMIT 1"
                    );

                    if($system->db->last_num_rows == 0) {
                        throw new RuntimeException("Invalid mission!");
                    }
                    if($player->team->mission_id) {
                        throw new RuntimeException("Team is already on a mission!");
                    }
                    if($player->mission_id) {
                        throw new RuntimeException("You are already on a mission!");
                    }

                    $player->team->mission_id = $mission_id;
                    $mission = new Mission($mission_id, $player, $player->team);

                    $player->mission_id = $mission_id;
                    $system->db->query(
                        "UPDATE `teams` SET `mission_id`='{$mission_id}' WHERE `team_id`='{$player->team->id}' LIMIT 1"
                    );
                    if($system->db->last_affected_rows) {
                        $system->message("Mission started!");
                        // Create notification
                        $result = $system->db->query("SELECT `name` FROM `missions` WHERE `mission_id` = {$mission_id}");
                        $mission_name = $system->db->fetch($result)['name'];
                        require_once __DIR__ . '/../classes/notification/NotificationManager.php';
                        if ($player->mission_stage['action_type'] == 'travel') {
                            $mission_location = TravelCoords::fromDbString($player->mission_stage['action_data']);
                            $new_notification = new MissionNotificationDto(
                                type: "mission_team",
                                message: $mission_name . ": Travel to " . $mission_location->x . ":" . $mission_location->y,
                                user_id: $player->user_id,
                                created: time(),
                                mission_rank: "Team",
                                alert: false,
                            );
                            NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
                        } else {
                            require_once __DIR__ . '/../classes/notification/NotificationManager.php';
                            $new_notification = new MissionNotificationDto(
                                type: "mission_team",
                                message: $mission_name . " in progress",
                                user_id: $player->user_id,
                                created: time(),
                                mission_rank: "Team",
                                alert: false,
                            );
                            NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
                        }
                    }
                    else {
                        $system->message("Error starting mission!");
                    }
                }catch (RuntimeException $e) {
                    $system->message($e->getMessage());
                }
            }
            else if(isset($_GET['cancel_mission'])) {
                try {
                    $mission_id = $player->team->mission_id;

                    if($mission_id == 0) {
                        throw new RuntimeException("Your team is not performing a mission!");
                    }

                    if(!isset($_GET['confirm'])) {
                        require "templates/team/cancel_mission_confirm.php";
                    }
                    else {

                        $system->db->query(
                            "UPDATE `teams` SET `mission_id`='0', `mission_stage`='' WHERE `team_id`='{$player->team->id}' LIMIT 1"
                        );
                        $system->db->query(
                            "UPDATE `users` SET `mission_id`='0' WHERE `team_id`='{$player->team->id}' AND `mission_id`='{$mission_id}'"
                        );

                        $player->team->mission_id = 0;

                        if ($player->mission_id == $mission_id) {
                            $player->clearMission();
                        }

                        $system->message("Mission cancelled!");
                    }
                }catch (RuntimeException $e){
                    $system->message($e->getMessage());
                }
            }
        }

        //Fetch team data
        $result = $system->db->query(
            "SELECT `user_name`, `avatar_link`, `forbidden_seal` FROM `users` WHERE `user_id`='{$player->team->leader}' LIMIT 1"
        );
        if($system->db->last_num_rows) {
            $result = $system->db->fetch($result);
            $leader_name = $result['user_name'];
            $leader_avatar = $result['avatar_link'];
            $leader_avatar_size = User::AVATAR_MAX_SIZE;
            if(is_object(json_decode($result['forbidden_seal']))) {
                $result['forbidden_seal'] = json_decode($result['forbidden_seal'], true);
                $pseudoSeal = new ForbiddenSeal($system, $result['forbidden_seal']['level'], $result['forbidden_seal']['time']);
                $pseudoSeal->setBenefits();
                $leader_avatar_size = $pseudoSeal->avatar_size;
            }
        }
        else {
            $leader_name = 'None';
            $leader_avatar = './images/default_avatar.png';
        }

        $result = $system->db->query("SELECT `mission_id`, `name`, `rank` FROM `missions` WHERE `mission_type`=3");
        $available_missions = [];
        while($row = $system->db->fetch($result)) {
            $available_missions[] = $row;
        }

        // Current mission display
        $team_mission_name = null;
        if($player->team->mission_id) {
            $result = $system->db->query(
                "SELECT `name` FROM `missions` WHERE `mission_id`={$player->team->mission_id} LIMIT 1"
            );
            $team_mission_name = $system->db->fetch($result)['name'];
        }

        // Members
        $user_ids = implode(',', $player->team->members);
        $result = $system->db->query(
            "SELECT `user_id`, `user_name`, `rank`, `level`, `monthly_pvp` FROM `users`
            WHERE `user_id` IN ($user_ids) ORDER BY `rank` DESC, `level` DESC"
        );
        $team_members = $system->db->fetch_all($result);

        // Leader tools
        if($player->user_id == $player->team->leader) {
            $self = false;
            $count = 0;
        }

        //Display message
        if($system->message && !$system->message_displayed) {
            $system->printMessage();
        }
        require "templates/team/team.php";
    }
}