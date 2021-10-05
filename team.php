<?php
/* 
File: 		team.php
Coder:		Levi Meahan
Created:	05/27/2014
Revised:	05/27/2014 by Levi Meahan
Purpose:	Functions for team management and activties
Algorithm:	See master_plan.html
*/

function team() {
	global $system;

	global $player;

	global $self_link;
	$self_id = 24;
	global $RANK_NAMES;
	
	if(!$player->team) {
		createTeam();
		return false;
	}

	if(isset($_GET['leave_team'])) {
		$members = $player->team->members;
		$self = false;
		$count = 0;
		foreach($members as $id => $member) {
			if($member != 0) {
				$count++;
			}
			
			if($member == $player->user_id) {
				$self = $id;
			}
		}
		if($self !== false) {
			unset($members[$self]);
		}	
		
		if(!isset($_POST['leave_team'])) {
			echo "<table class='table'><tr><th>Leave Team</th></tr>
			<tr><td style='text-align:center;'>
			Are you sure you want to leave <b>" . $player->team->name . "</b>?<br />
			<form action='$self_link&leave_team=1' method='post'>";
			
			if($player->user_id == $player->team->leader) {
				if($count > 1) {		
					$user_ids = implode(',', $members);
					$result = $system->query("SELECT `user_id`, `user_name` FROM `users` 
						WHERE `user_id` IN ($user_ids)");
					
					echo "Give leader spot to <select name='new_leader'>";
					while($row = $system->db_fetch($result)) {
						echo "<option value='{$row['user_id']}'>{$row['user_name']}</option>";
					}
					echo "</select>";
				}
			}
			
			echo "<input type='submit' name='leave_team' value='Leave Team' />
			</form>
			</td></tr></table>";
			return true;
		}
		
		try {
			// Leader check
			if($player->user_id == $player->team->leader) {
				if($count > 1) {
					$new_leader = (int)$system->clean($_POST['new_leader']);
					if(array_search($new_leader, $members) === false) {
						throw new Exception("Invalid new leader!");
					}
				}
			}
			
			// delete team if only one member
			if($count == 1) {
				$result = $system->query("DELETE FROM `teams` WHERE `team_id`={$player->team->id} LIMIT 1");
				if($system->db_last_affected_rows > 0) {
					$system->message("You have left your team. <a href='$self_link'>Continue</a>");
					$system->printMessage();
					$player->team = null;
					return true;
				}
				else {
					throw new Exception("Error leaving team!");
				}
			}
			// Shift member ids
			else {
				if($self !== false) {
					$members[] = 0;
				}
				$members = json_encode($members);
				$query = "UPDATE `teams` SET `members`='$members'";
				if($player->user_id == $player->team->leader) {
					$query .= ", `leader`='$new_leader'";
				}
				$query .= "WHERE `team_id`={$player->team->id}";
				$system->query($query);
				if($system->db_last_affected_rows > 0) {
					$system->message("You have left your team. <a href='$self_link'>Continue</a>");
					$system->printMessage();
					$player->team = null;
					return true;
				}
				else {
					throw new Exception("Error leaving team!");
				}
			}
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if (isset($_POST['set_boost'], $_POST['set_amount'])) {

		$boost_valid = true;
		$boost = $system->clean($_POST['set_boost']);
		$amount = $system->clean($_POST['set_amount']);

		$allowed_boosts = Team::$allowed_boosts;
		
		if ($player->user_id != $player->team->leader) {
			$system->message('You are not the leader of this team!');
			$boost_valid = false;
		}

		if (!array_key_exists($boost, $allowed_boosts)) {
			$system->message('This boost does not exist!');
			$boost_valid = false;
		}

		if (!array_key_exists($amount, $allowed_boosts[$boost]['Amount'])) {
			$system->message('You cannot boost by this amount!');
			$boost_valid = false;
		}

		if ($allowed_boosts[$boost]['Amount'][$amount]['Cost'] > $player->team->points) {
			$system->message('Your team does not have enough points for this boost!');
			$boost_valid = false;
		}
		
		if ($boost_valid) {
			$new_points = $player->team->points - $allowed_boosts[$boost]['Amount'][$amount]['Cost'];
			$boost_time = time();
			try {
				$result = $system->query("UPDATE `teams` SET 
                    `boost`='{$boost}', 
                    `boost_amount`='{$amount}', 
                    `points`='{$new_points}', 
                    `boost_time`='{$boost_time}' 
                    WHERE `team_id`='{$player->team->id}' ");
				$system->message('Boost set!');
			}
			catch (Exception $e) {
				$system->message($e->getMessage());
			}
		}
		
		
		$system->printMessage();

	}
	else if(isset($_GET['join_mission']) && $player->team->mission_id) {
		$mission_id = $player->team->mission_id;
		$mission = new Mission($mission_id, $player, $player->team);
	
		$player->mission_id = $mission_id;
		
		$system->message("Mission joined!");
		$system->printMessage();
	}
	// Controls
	else if($player->user_id == $player->team->leader) {
		if(isset($_GET['invite'])) {
			$user_name = $system->clean($_GET['user_name']);
			try {
				$result = $system->query("SELECT `user_id`, `rank`, `team_id`, `village` FROM `users` WHERE `user_name`='$user_name'");
				if($system->db_last_num_rows == 0) {
					throw new Exception("Invalid user!");
				}
				$user_data = $system->db_fetch($result);
				
				if($user_data['rank'] < 3) {
					throw new Exception("Player must be Chuunin or higher!");
				}
				
				if($user_data['village'] != $player->village) {
					throw new Exception("Player must be in the same village!");
				}
				
				if(!empty($user_data['team_id'])) {
					throw new Exception("Player is already in a team/invited to one!");
				}
				
				$result = $system->query("UPDATE `users` SET `team_id`='invite:{$player->team->id}' 
					WHERE `user_id`='{$user_data['user_id']}' LIMIT 1");
					
				$system->message("Player invited!");
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
			$system->printMessage();
		}
		else if(isset($_POST['kick'])) {
			$kick = (int)$system->clean($_POST['user_id']);
			
			$members = $player->team->members;
			$kick_key = false;
			$count = 0;
			foreach($members as $id => $member) {
				if($member != 0) {
					$count++;
				}
				
				if($member == $kick) {
					$kick_key = $id;
				}
			}
			
			try {
				if(!$kick_key) {
					throw new Exception("Invalid user!");
				}
				
				$result = $system->query("SELECT `user_name` FROM `users` WHERE `user_id`='$kick'");
				if($system->db_last_num_rows == 0) {
					throw new Exception("Invalid user!");
				}
				$user_name = $system->db_fetch($result)['user_name'];
				
				if(!isset($_GET['confirm'])) {
					echo "<table class='table'><tr><th>Leave Team</th></tr>
					<tr><td style='text-align:center;'>
					Are you sure you want to kick <b>$user_name</b> from the team?<br />
					<form action='$self_link&confirm=1' method='post'>
					<input type='hidden' name='user_id' value='$kick' />
					<input type='submit' name='kick' value='Kick Member' />
					</form>
					</td></tr></table>";
				}
				else {
					unset($members[$kick_key]);
					$members[] = 0;
					
					$player->team->members = $members;
					$members = json_encode($members);
					
					$query = "UPDATE `teams` SET `members`='$members' WHERE `team_id`={$player->team->id}";
					$system->query($query);
					
					$query = "UPDATE `users` SET `team_id`=0 WHERE `user_id`='$kick' LIMIT 1";
					$system->query($query);
					
					if($system->db_last_affected_rows > 0) {
						$system->message("You have kicked <b>$user_name</b>.");
					}
					else {
						throw new Exception("Error kicking <b>$user_name</b>!");
					}	
				}
			} catch(Exception $e) {
				$system->message($e->getMessage());
			}
			$system->printMessage();
						
		}
		else if(isset($_POST['logo_link'])) {
			$avatar_link = $system->clean($_POST['logo_link']);
			try {
				$system->query("UPDATE `teams` SET `logo`='{$avatar_link}' WHERE `team_id`={$player->team->id} LIMIT 1");
				$player->team->logo = $avatar_link;
				$system->message("Logo updated!");
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
			$system->printMessage();
		}
		else if(isset($_POST['start_mission'])) {
			$mission_id = (int)$system->clean($_POST['mission_id']);
			$result = $system->query("SELECT `mission_id` FROM `missions` WHERE `mission_id`=$mission_id AND `mission_type`=3");
			if($system->db_last_num_rows == 0) {
				$system->message("Invalid mission!");
			}
			else if($player->team->mission_id) {
				$system->message("Team is already on a mission!");
			}
			else if($player->mission_id) {
				$system->message("You are already on a solo mission!");
			}
			else {
				$player->team->mission_id = $mission_id;
				$mission = new Mission($mission_id, $player, $player->team);
			
				$player->mission_id = $mission_id;
				
				$system->query("UPDATE `teams` SET `mission_id`=$mission_id WHERE `team_id`={$player->team->id} LIMIT 1");
				$system->message("Mission started!");
			}
			$system->printMessage();
		}
		else if(isset($_GET['cancel_mission'])) {
			$mission_id = $player->team->mission_id;
			$result = $system->query("UPDATE `teams` SET `mission_id`=0, `mission_stage`='' WHERE `team_id`={$player->team->id} LIMIT 1");
			$result = $system->query("UPDATE `users` SET `mission_id`=0 WHERE `team_id`={$player->team->id} AND `mission_id`=$mission_id");
			
			$player->team->mission_id = 0;
			
			if($player->mission_id == $mission_id) {
				$player->clearMission();
			}
			
			$system->message("Mission cancelled.");
			$system->printMessage();
		}
	}
	
	$result = $system->query("SELECT `user_name`, `avatar_link`, `forbidden_seal` FROM `users` WHERE `user_id`='" . $player->team->leader . "' LIMIT 1");
	if($system->db_last_num_rows > 0) {
		$result = $system->db_fetch($result);
		$leader = $result['user_name'];
		$leader_avatar = $result['avatar_link'];
		$leader_avatar_size = '125px';
		if($result['forbidden_seal']) {
			$leader_avatar_size = '175px';
		}
	}
	else {
		$leader = 'None';
		$leader_avatar = './images/default_avatar.png';
	}

	if ($player->team->boost != 'none') {
		$boost_text = $player->team->boost . ' -- ' . $player->team->boost_amount . '%';
		$time_left = $player->team->boost_time + (60*60*24*7) - time();
		$boost_time = $system->timeRemaining($time_left, 'long');
	} else {
		$boost_text = 'none';
		$boost_time = 'n/a';
	}


    $result = $system->query("SELECT `mission_id`, `name`, `rank` FROM `missions` WHERE `mission_type`=3");
	$available_missions = [];
	while($row = $system->db_fetch($result)) {
	    $available_missions[] = $row;
    }

    // Current mission display
    $team_mission_name = null;
    if($player->team->mission_id) {
        $result = $system->query("SELECT `name` FROM `missions` WHERE `mission_id`={$player->team->mission_id} LIMIT 1");
        $team_mission_name = $system->db_fetch($result)['name'];
    }

    // Members
    $user_ids = implode(',', $player->team->members);
    $result = $system->query("SELECT `user_name`, `rank`, `level`, `monthly_pvp` FROM `users`
        WHERE `user_id` IN ($user_ids) ORDER BY `rank` DESC, `level` DESC");
    $team_members = $system->db_fetch_all($result);

    // Leader tools
    if($player->user_id == $player->team->leader) {
        $self = false;
        $count = 0;
    }

	require 'templates/team.php';
}

function createTeam(): bool {
	global $system;

	global $player;

	global $self_link;
	
	$min_name_length = Team::MIN_NAME_LENGTH;
	$max_name_length = Team::MAX_NAME_LENGTH;
	
	if(isset($_POST['create_team'])) {
		$name = $system->clean($_POST['name']);
		try {
		    // Name
            if(strlen($name) < $min_name_length) {
                throw new Exception("Please enter a name longer than " . ($min_name_length - 1) . " characters!");
            }
            if(strlen($name) > $max_name_length) {
                throw new Exception("Please enter a name shorter than " . ($max_name_length + 1) . " characters!");
            }

            if(!preg_match('/^[a-zA-Z0-9 _-]+$/', $name)) {
                throw new Exception("Only alphanumeric characters, dashes, spaces, and underscores are allowed in names!");
            }

            // check for at least 3 letters
            $letter_count = 0;
            $num_symbol_count = 0;
            for($i = 0; $i < strlen($name); $i++) {
                if(ctype_alpha($name[$i])) {
                    $letter_count++;
                }
                else {
                    $num_symbol_count++;
                }
            }
            if($num_symbol_count >= $letter_count) {
                throw new Exception("Name must be more than half letters!");
            }

            // Banned words
            if($system->explicitLanguageCheck($name)) {
                throw new Exception("Inappropriate language is not allowed in team name!");
            }

            // Check for name exising
            $system->query("SELECT `team_id` FROM `teams` WHERE `name`='$name' LIMIT 1");
            if($system->db_last_num_rows > 0) {
                throw new Exception("Name is already in use!");
            }

			$success = Team::create(
			    $system,
                $name,
                $player->village,
                $player->user_id,
            );

			if($success) {
				$system->message("Team created! <a href='$self_link'>Continue</a>");
				$player->team = new Team($system, $system->db_last_insert_id);
			}
			else {
				$system->message("There was an error creating your team.");
			}
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}
	else if(isset($_GET['accept_invite']) && $player->team_invite) {
		try {
			$team_id = $player->team_invite;
			$team = Team::findById($system, $team_id);
			if($team == null) {
				throw new Exception("Invalid team!");
			}

			$team->addMember($player);

			$system->message("You have joined <b>{$team->name}</b>. <a href='$self_link'>Continue</a>");
			$system->printMessage();
			return true;
		} catch (Exception $e) {
			$player->team_invite = 0;
			$system->query("UPDATE `users` SET `team_id`=0 WHERE `user_id`='$player->user_id' LIMIT 1");
			$system->message($e->getMessage());
		}
	}
	else if(isset($_GET['decline_invite']) && $player->team_invite) {
		$player->team_invite = 0;
		$system->query("UPDATE `users` SET `team_id`=0 WHERE `user_id`='$player->user_id' LIMIT 1");
		$system->message("Invite declined.");
	}

	$team_invited_to = null;
    if($player->team_invite) {
        $team_id = $player->team_invite;

        $team_invited_to = Team::findById($system, $team_id);
        if($team_invited_to == null) {
            $player->team_invite = 0;
        }
        else {
            $team_invited_to_leader = $team_invited_to->fetchLeader();
        }
    }

    require "templates/create_team.php";

    return true;
}