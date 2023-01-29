<?php
/* 
File: 		modPanel.php
Coder:		Levi Meahan
Created:	12/09/2013
Revised:	12/09/2013 by Levi Meahan
Purpose:	Functions for mod bans/etc
Algorithm:	See master_plan.html
*/
function modPanel() {
    require_once 'classes/ReportManager.php';

	global $system;
	global $player;
	global $self_link;

    // Load staff manager
    if($player->staff_manager === false) {
        $player->loadStaffManager();
    }
	// Staff level check
	if(!$player->staff_manager->isModerator()) {
		return false;
	}
    // Ban lengths
    $ban_lengths = $player->staff_manager->getBanLengths();


	// $page = $_GET['page'];
	$display_menu = true;
	// Submenu
    require 'templates/staff/mod/mod_panel_header.php';

	// Social/game ban [panel upgrade done -Hitori]
	if(!empty($_POST['ban'])) {
		try {
			if(!isset($_POST['user_name'])) {
				throw new Exception("Invalid username!");
			}
			if(!isset($_POST['ban_type'])) {
				throw new Exception("Invalid ban type!");
			}
			if(!isset($_POST['ban_length'])) {
				throw new Exception("Invalid ban length!");
			}
			$user_name = $system->clean($_POST['user_name']);
			$ban_type = $system->clean($_POST['ban_type']);
			$ban_length = $system->clean($_POST['ban_length']);

            //Check ban type
			if(array_search($ban_type, StaffManager::$ban_types) === false) {
				throw new Exception("Invalid ban type!");
			}
            //Check ban length
            if(!isset($ban_lengths[$ban_length])) {
                throw new Exception("Invalid ban length!");
            }

            $user_data = $player->staff_manager->getUserByName($user_name);
			if($user_data == false) {
				throw new Exception("Invalid username!");
			}

            $player->staff_manager->canBanUser($ban_type, $ban_length, $user_data);

			// Run query if confirmed
			if(!isset($_POST['confirm'])) {
				require 'templates/staff/mod/ban_confirm.php';
			}
			else {
                if($player->staff_manager->banUser($ban_type, $ban_length, $user_data)) {
                    $system->message("$user_name has been " . ucwords($ban_type) . " banned!");
                }
                else {
                    $system->message("Error banning user!");
                }
			}
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}
	// Journal/avatar/profile song ban + remove [panel upgrade done - Hitori]
	else if(!empty($_POST['profile_ban'])) {
		try {
            $user_name = $system->clean($_POST['user_name']);
            $avatar_ban = false;
            $avatar_remove = false;
            $journal_ban = false;
            $journal_remove = false;

            $user_data = $player->staff_manager->getUserByName($user_name);
            if(!$user_data) {
                throw new Exception("Invalid user!");
            }

            if(isset($_POST['avatar'])) {
                foreach($_POST['avatar'] as $type) {
                    if($type == 'remove') {
                        $avatar_remove = true;
                    }
                    elseif($type == 'ban') {
                        $avatar_ban = true;
                    }
                }
            }
            if(isset($_POST['journal'])) {
                foreach($_POST['journal'] as $type) {
                    if($type == 'remove') {
                        $journal_remove = true;
                    }
                    elseif($type == 'ban') {
                        $journal_ban = true;
                    }
                }
            }

            //Check if avatar ban/remove can be performed
            if($avatar_ban || $avatar_remove) {
                $player->staff_manager->canBanUser(StaffManager::BAN_TYPE_AVATAR, StaffManager::PERM_BAN_VALUE, $user_data);
            }
            //Only need to check for journals if avatars have not been checked, same perms required
            if(($journal_ban || $journal_remove) && !$avatar_ban && !$avatar_remove) {
                $player->staff_manager->canBanUser(StaffManager::BAN_TYPE_JOURNAL, StaffManager::PERM_BAN_VALUE, $user_data);
            }

            $message_string = '';
            $userQuery = "UPDATE `users` SET ";
            $ban_data = json_decode($user_data['ban_data'], true);
            //Ban avatar
            if($avatar_ban) {
                $ban_data[StaffManager::BAN_TYPE_AVATAR] = StaffManager::PERM_BAN_VALUE;
                $message_string .= 'Avatar banned, ';
            }
            //Remove avatar
            if($avatar_remove) {
                $userQuery .= "`avatar_link`='./images/default_avatar.png', ";
                $message_string .= 'Avatar removed, ';
            }
            //Ban journal
            if($journal_ban) {
               $ban_data[StaffManager::BAN_TYPE_JOURNAL] = StaffManager::PERM_BAN_VALUE;
               $message_string .= 'Journal banned, ';
            }
            //Remove journal
            if($journal_remove) {
                $message_string .= 'Journal removed, ';
            }
            $userQuery .= " `ban_data`='" . json_encode($ban_data) . "' WHERE `user_id`='{$user_data['user_id']}' LIMIT 1";

            //These queries have to be managed this way
            //User the built-in banning system in the StaffManager will result
            //in either the avatar or journal bans being missed entirely.
            //Believe it is a latency issue where the second update is processed while the first one is still
            //processing and results in it being ultimately dropped. - Hitori
            if($message_string != '') {
                $system->query($userQuery);
                if($system->db_last_affected_rows) {
                    $staffLog = "{$player->user_name}({$player->user_id}) ";
                    if($avatar_remove) {
                        $staffLog .= "removed avatar, ";
                        $player->staff_manager->addRecord($user_data['user_id'], $user_data['user_name'], StaffManager::RECORD_NOTE, "Removed avatar.", false);
                    }
                    if($avatar_ban) {
                        $staffLog .= "banned avatar, ";
                        $player->staff_manager->addRecord($user_data['user_id'], $user_data['user_name'], StaffManager::RECORD_BAN_ISSUED, "Banned avatar.", false);
                    }
                    if($journal_ban) {
                        $staffLog .= "banned journal, ";
                        $player->staff_manager->addRecord($user_data['user_id'], $user_data['user_name'], StaffManager::RECORD_BAN_ISSUED, "Banned journal.", false);
                    }
                    $player->staff_manager->staffLog(StaffManager::STAFF_LOG_MOD, substr($staffLog, 0, strlen($staffLog)-2).".");
                }
                if($journal_remove) {
                    $system->query("UPDATE `journals` SET `journal`='' WHERE `user_id`='{$user_data['user_id']}' LIMIT 1");
                    $player->staff_manager->addRecord($user_data['user_id'], $user_data['user_name'], StaffManager::RECORD_NOTE, "Journal Removed");
                }
                $system->message(substr($message_string, 0, strlen($message_string) - 2) . ".");
            }
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}
	// View record [panel upgrade done -Hitori]
	else if(!empty($_GET['view_record'])) {
		try {
            //Query for user
			$user_name = $system->clean($_GET['view_record']);
			$result = $system->query("SELECT `user_id`, `user_name`, `staff_level` FROM `users` WHERE `user_name`='$user_name' LIMIT 1");
			if($system->db_last_num_rows == 0) {
				throw new Exception("Invalid user!");
			}

            //Fetch user data
			$result = $system->db_fetch($result);
			$user_id = $result['user_id'];
			$user_name = $result['user_name'];
			$staff_level = $result['staff_level'];

            //Check permission to view record
			if(!$player->staff_manager->canViewRecord($staff_level)) {
                throw new Exception("You do not have permission to view "
                . "{$player->staff_manager->getStaffLevelName($staff_level, 'long')} records!");
            }

            //Update self link
            $self_link .= "&view_record=$user_name";

            //Add record note
            if(!empty($_POST['add_note'])) {
                try {
                    $data = trim($system->clean($_POST['content']));
                    $record_type = StaffManager::RECORD_NOTE;

                    if (strlen($data) < StaffManager::RECORD_NOT_MIN_SIZE) {
                        throw new Exception("Record notes must be at least " . StaffManager::RECORD_NOT_MIN_SIZE . " characters long.");
                    }

                    $player->staff_manager->addRecord($user_id, $user_name, $record_type, $data);
                    if($system->db_last_insert_id) {
                        $system->message("Note added.");
                    }
                }catch (Exception $e) {
                    $system->message($e->getMessage());
                }
            }
            if(!empty($_POST['delete_record_note'])) {
                try {
                    $record_id = (int)$_POST['record_id'];
                    $user_id = (int)$_POST['user_id'];

                    if(!$player->staff_manager->isHeadModerator()) {
                        throw new Exception("You do not have permission to delete record notes!");
                    }

                    if ($player->staff_manager->manageRecord($record_id, $user_id, $user_name)) {
                        $system->message("Record deleted.");
                    } else {
                        $system->message("Error deleting record, or record already removed.");
                    }
                }catch (Exception $e) {
                    $system->message($e->getMessage());
                }
            }
            if(!empty($_POST['recover_record'])) {
                try {
                    $record_id = (int)$_POST['record_id'];
                    $user_id = (int)$_POST['user_id'];

                    if(!$player->staff_manager->isUserAdmin()) {
                        throw new Exception("You do not have permission to recover record notes!");
                    }

                    if ($player->staff_manager->manageRecord($record_id, $user_id, $user_name, false)) {
                        $system->message("Record recovered.");
                    } else {
                        $system->message("Error recovering record, or record already recovered.");
                    }
                }catch (Exception $e) {
                    $system->message($e->getMessage());
                }
            }

            //Fetch record details
            //Reports
			$result = $system->query("SELECT * FROM `reports` WHERE `user_id`='$user_id'");
			$reports = array();
			$user_ids = array();
			$users = [];
			while($row = $system->db_fetch($result)) {
				$reports[$row['report_id']] = $row;
				if($row['moderator_id']) {
					$users[$row['moderator_id']] = $row['moderator_id'];
				}
			}
            // Fetch user names of moderators
            if(count($users) > 0) {
                $user_ids_string = implode(',', $users);
                $result = $system->query("SELECT `user_id`, `user_name` FROM `users` WHERE `user_id` IN($user_ids_string)");
                while($row = $system->db_fetch($result)) {
                    $users[$row['user_id']] = $row['user_name'];
                }
            }

            //Record data
            $record = [];
            $record_result = $system->query("SELECT * FROM `user_record` WHERE `user_id`='$user_id' ORDER BY TIME DESC");
            if($system->db_last_num_rows) {
                while($data = $system->db_fetch($record_result)) {
                    $record[] = $data;
                }
            }

            //Display record
			require 'templates/staff/mod/view_record.php';

            //Do not display Mod/Head Mod menus
			$display_menu = false;
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	// Locked out users
	if(!empty($_GET['unlock_account']) && $player->staff_manager->isHeadModerator()) {
		$user_id = (int)$system->clean($_GET['unlock_account']);
		$result = $system->query("UPDATE `users` SET `failed_logins`=0 WHERE `user_id`='$user_id' LIMIT 1");
		if($system->db_last_affected_rows > 0) {
			$system->message("Account unlocked!");
		}
		else {
			$system->message("Invalid account!");
		}
		$system->printMessage();
	}
	// HM actions
	if($player->staff_manager->isHeadModerator()) {
		// Ban IP
		if(!empty($_POST['ban_ip'])) {
			try {
				$ip_address = $system->clean($_POST['ip_address']);
				$result = $system->query("SELECT `id` FROM `banned_ips` WHERE `ip_address`='$ip_address' LIMIT 1");
				if($system->db_last_num_rows > 0) {
					throw new Exception("IP address has already been banned!");
				}
				$system->query("INSERT INTO `banned_ips` (`ip_address`, `ban_level`) VALUES ('$ip_address', 2)");
				if($system->db_last_affected_rows == 1) {
					$system->message("IP address '$ip_address' banned!");
				}
				else {
					$system->message("Error banning IP address '$ip_address'!");
				}
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
		}
		// Social/game unban
		if(!empty($_POST['unban'])) {
			try {
				if(!isset($_POST['user_name'])) {
					throw new Exception("Invalid username!");
				}
				$user_name = $system->clean($_POST['user_name']);
				$result = $system->query("SELECT `user_id`, `user_name`, `staff_level`, `ban_type`, `ban_expire` FROM `users` WHERE `user_name`='$user_name'");
				if($system->db_last_num_rows == 0) {
					throw new Exception("Invalid username!");
				}
				$user_data = $system->db_fetch($result);
                // TODO: rewrite this logic to take content admins out of it
				if($user_data['staff_level'] >= $player->staff_level and !$player->isHeadAdmin()) {
					throw new Exception("You cannot unban fellow staff members!");
				}
				if(!$user_data['ban_type']) {
					throw new Exception("User is not banned!");
				}
				// Run query if confirmed
				if(!isset($_POST['confirm'])) {
					echo "<table class='table'><tr><th>Confirm Ban Removal</th></tr>
					<tr><td style='text-align:center;'>" .
					"Remove " . $user_data['user_name'] . "'s " . ucwords($user_data['ban_type']) . " ban?<br />" .
					"<form action='$self_link' method='post'>
					<input type='hidden' name='user_name' value='{$user_data['user_name']}' />
					<input type='hidden' name='confirm' value='1' />
					<input type='submit' name='unban' value='Confirm' />
					</form>
					</td></tr></table>";
				}
				else {
					$system->query("UPDATE `users` SET `ban_type`='', `ban_expire`='0' 
						WHERE `user_id`='{$user_data['user_id']}' LIMIT 1");
					if($system->db_last_affected_rows == 1) {
						$system->message("User unbanned!");
					}
					else {
						$system->message("Error unbanning user!");
					}
				}
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
		}
		// Unban IP
		if(!empty($_POST['unban_ip'])) {
			try {
				$ip_address = $system->clean($_POST['ip_address']);
				$result = $system->query("SELECT `id` FROM `banned_ips` WHERE `ip_address`='$ip_address' LIMIT 1");
				if($system->db_last_num_rows == 0) {
					throw new Exception("IP address is not banned!");
				}
				$system->query("DELETE FROM `banned_ips` WHERE `ip_address`='$ip_address' LIMIT 1");
				if($system->db_last_affected_rows == 1) {
					$system->message("IP address '$ip_address' unbanned!");
				}
				else {
					$system->message("Error unbanning IP address '$ip_address'!");
				}
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
		}
		// Journal/avatar/profile song unban
		else if(!empty($_POST['profile_unban'])) {
			try {
				if(!empty($_POST['journal'])) {
					$journal = $_POST['journal'];
				}
				if(!empty($_POST['avatar'])) {
					$avatar = $_POST['avatar'];
				}
				if(!empty($_POST['song'])) {
					$song = $_POST['song'];
				}
				$unban_journal = false;
				$unban_avatar = false;
				$unban_song = false;
				if($journal == 'unban') {
					$unban_journal = true;
				}
				if($avatar == 'unban') {
					$unban_avatar = true;
				}
				if($song == 'unban') {
					$unban_song = true;
				}
				if(!$unban_journal && !$unban_avatar && !$unban_song) {
					throw new Exception("Please select an option!");
				}
				// Check username
				$user_name = $system->clean($_POST['user_name']);
				$result = $system->query("SELECT `user_id`, `user_name`, `staff_level` FROM `users` WHERE `user_name`='$user_name'");
				if($system->db_last_num_rows == 0) {
					throw new Exception("Invalid username!");
				}
				$user_data = $system->db_fetch($result);
				// TODO: rewrite this logic to take content admins out of it
				if($user_data['staff_level'] >= $player->staff_level and !$player->isHeadAdmin()) {
					throw new Exception("You cannot unban fellow staff members!");
				}
				// Build query
				$add_comma = false;
				$query = "UPDATE `users` SET ";
				if($unban_journal) {
					$query .= "`journal_ban`='0'";
					$add_comma = true;
				}
				if($unban_avatar) {
					if($add_comma) {
						$query .= ", ";
						$add_comma = false;
					}
					$query .= "`avatar_ban`='0'";
					$add_comma = true;
				}
				if($unban_song) {
					if($add_comma) {
						$query .= ", ";
						$add_comma = false;
					}
					$query .= "`song_ban`='0'";
					$add_comma = true;
				}
				$query .= " WHERE `user_id` = '{$user_data['user_id']}' LIMIT 1";
				$system->query($query);
				// Set error flags
				$error = false;
				if($system->db_last_affected_rows == 0) {
					$error = true;
					if($unban_journal) {
						$ban_journal = -1;
					}
					if($unban_avatar) {
						$ban_avatar = -1;
					}
					if($unban_song) {
						$ban_avatar = -1;
					}
				}
				// Error message
				if($error) {
					if($unban_journal == -1 || $unban_avatar == -1 || $unban_song == -1) {
						$system->message("Error unbanning journal/avatar/profile song! (or it is already banned)");
					}
				}
				// Success message
				if(!$error) {
					$add_comma = false;
					$message = '';
					if($unban_journal) {
						$message .= "journal unbanned";
						$add_comma = true;
					}
					if($unban_avatar) {
						if($add_comma) {
							$message .= ', ';
						}
						$message .= "avatar unbanned";
						$add_comma = true;
					}
					if($unban_song) {
						if($add_comma) {
							$message .= ', ';
						}
						$message .= "profile song unbanned";
						$add_comma = true;
					}
					$message .= '!';
					$message = ucfirst($message);
					$system->message($message);
				}
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
		}
		// Unlock account
		else if(!empty($_GET['locked_out_users'])) {
		}
		// Global message
		else if(!empty($_POST['global_message'])) {
			$message = $system->clean($_POST['global_message']);
			try {
				if(strlen($message) < 5) {
					throw new Exception("Please enter a message!");
				}
				if(strlen($message) > 1000) {
					throw new Exception("Message is too long! (" . strlen($message) . "/1000 chars)");
				}
				$system->query("UPDATE `system_storage` SET `global_message`='$message', `time`='".time()."'");
				$system->query("UPDATE `users` SET `global_message_viewed`=0");
				$player->global_message_viewed = 0;
				$system->message("Message posted!");
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
			$system->printMessage();
		}
	}

	// Logged message
	$system->printMessage();

	// Display forms
    $view = $_GET['view'] ?? '';


    //Mod panel rework complete -Hitori
	if($view == 'banned_users') {
        $banned_users = $player->staff_manager->getBannedUsers();
        require 'templates/staff/mod/banned_users.php';
	}
    //TODO: Update locked out users for new mod panel
	else if($view == 'locked_out_users') {
        $locked_out_users = $player->staff_manager->getLockedUsers();
		require 'templates/staff/mod/locked_out_users.php';
	}
    //Mod panel rework complete -Hitori
	else if($display_menu) {
		// Mod actions
		require 'templates/staff/mod/mod_menu.php';

		// HM actions
		if($player->staff_manager->isHeadModerator()) {
			require 'templates/staff/mod/head_mod_menu.php';
		}
	}
}