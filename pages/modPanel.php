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
			if(array_search($ban_type, StaffManager::$ban_menu_items) === false) {
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

            if(!$avatar_ban && !$avatar_remove && !$journal_ban && !$journal_remove) {
                throw new Exception("Please select an option!");
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
    elseif(!empty($_GET['official_warning'])) {
        try {
            $display_menu = false;
            $content = '';
            $user_name = $system->clean($_GET['official_warning']);
            $user_data = $player->staff_manager->getUserByName($user_name);

            if(!$user_data) {
                throw new Exception("Invalid user!");
            }

            if(isset($_POST['send_official_warning'])) {
                $content = $system->clean(trim($_POST['content']));
                //Official warnings follow same rules as banning
                if(!$player->staff_manager->canIssueOW($user_data['staff_level'])) {
                    throw new Exception("You do not have permission to send warnings to this user!");
                }
                if(strlen($content) < StaffManager::OW_MIN) {
                    throw new Exception("Warning must be at least " . StaffManager::OW_MIN . " characters long.");
                }
                if(strlen($content) > StaffManager::OW_MAX) {
                    throw new Exception("Warning may not exceed " . StaffManager::OW_MAX . " characters long.");
                }

                //Send Official Warning
                if($player->staff_manager->sendOW($content, $user_data)) {
                    $system->message("Official Warning sent!");
                    $display_menu = true;
                }
                else {
                    $system->message("Error sending warning.");
                }
            }
        }catch (Exception $e) {
            $system->message($e->getMessage());
        }

        if(!$display_menu) {
            require 'templates/staff/mod/official_warning.php';
        }
        $system->printMessage();
    }
	// Locked out users [panel upgrade done -Hitori]
	if(!empty($_GET['unlock_account']) && $player->staff_manager->isHeadModerator()) {
		$user_id = (int)$system->clean($_GET['unlock_account']);
		$result = $system->query("UPDATE `users` SET `failed_logins`=0 WHERE `user_id`='$user_id' LIMIT 1");
		if($system->db_last_affected_rows > 0) {
            $player->staff_manager->staffLog(StaffManager::STAFF_LOG_HEAD_MOD, "{$player->user_name} ({$player->user_id}) unlocked account ID {$user_id}.");
			$system->message("Account unlocked!");
		}
		else {
			$system->message("Invalid account!");
		}
		$system->printMessage();
	}
	// HM actions
	if($player->staff_manager->isHeadModerator()) {
		// Ban IP [mod panel upgrade done -Hitori]
		if(!empty($_POST['ban_ip'])) {
			try {
				$ip_address = $system->clean($_POST['ip_address']);
				if($player->staff_manager->getBannedIP($ip_address)) {
					throw new Exception("IP address has already been banned!");
				}
				$system->query("INSERT INTO `banned_ips` (`ip_address`, `ban_level`) VALUES ('$ip_address', 2)");
				if($system->db_last_affected_rows == 1) {
                    $player->staff_manager->staffLog(StaffManager::STAFF_LOG_HEAD_MOD,
                        "{$player->user_name}({$player->user_id}) banned IP Address: $ip_address.");
					$system->message("IP address '$ip_address' banned!");
				}
				else {
					$system->message("Error banning IP address '$ip_address'!");
				}
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
		}
		// Social/game/pm unban [mod panel upgrade done -Hitori]
		if(!empty($_POST['unban'])) {
			try {
				$user_name = $system->clean($_POST['user_name']);
                $unban_type = $system->clean($_POST['ban_type']);
                $user_data = $player->staff_manager->getUserByName($user_name);

                //Check if user exists
                if(!isset($_POST['user_name'])) {
                    throw new Exception("Invalid username!");
                }
				if(!$user_data) {
					throw new Exception("Invalid username!");
				}
                //Check if unban can be performed by user
                $player->staff_manager->canUnbanUser($user_data);
                //Check if ban exists
                $ban_data = json_decode($user_data['ban_data'], true);
                if(empty($ban_data) || !isset($ban_data[$unban_type])) {
                    throw new Exception("$user_name does not currently have a " . ucwords($unban_type) . " Ban!");
                }
                //Check if unban data is correct
                if(!in_array($unban_type, StaffManager::$ban_menu_items)) {
                    throw new Exception("Invalid ban type!");
                }

				// Run query if confirmed
				if(!isset($_POST['confirm'])) {
					require 'templates/staff/mod/unban_confirm.php';
				}
				else {
					if($player->staff_manager->unbanUser($unban_type, $user_data)) {
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
		// Unban IP [mod penal upgrade complete -Hitori]
		if(!empty($_POST['unban_ip'])) {
			try {
				$ip_address = $system->clean($_POST['ip_address']);
				if(!$player->staff_manager->getBannedIP($ip_address)) {
					throw new Exception("IP address is not banned!");
				}
				$system->query("DELETE FROM `banned_ips` WHERE `ip_address`='$ip_address' LIMIT 1");
				if($system->db_last_affected_rows == 1) {
                    $player->staff_manager->staffLog(StaffManager::STAFF_LOG_HEAD_MOD,
                        "{$player->user_name}({$player->user_id}) unbanned IP: $ip_address.");
					$system->message("IP address '$ip_address' unbanned!");
				}
				else {
					$system->message("Error unbanning IP address '$ip_address'!");
				}
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
		}
		// Journal/avatar/profile song unban [mod panel upgrade compelte -Hitori]
		else if(!empty($_POST['profile_unban'])) {
			try {
                $unban_avatar = (isset($_POST['journal'])) ?? false;
                $unban_journal = (isset($_POST['avatar'])) ?? false;

				$to_unban = [];
                if($unban_avatar) {
                    $to_unban[] = StaffManager::BAN_TYPE_AVATAR;
                }
                if($unban_journal) {
                    $to_unban[] = StaffManager::BAN_TYPE_JOURNAL;
                }

                //Check if unban set
                if(empty($to_unban)) {
                    throw new Exception("Select an option to unban!");
                }
                // Check username
				$user_name = $system->clean($_POST['user_name']);
                $user_data = $player->staff_manager->getUserByName($user_name);
				if(!$user_data) {
					throw new Exception("Invalid username!");
				}
                $player->staff_manager->canUnbanUser($user_data);

                if($player->staff_manager->unbanUser($to_unban, $user_data)) {
                    $message_string = "{$user_data['user_name']}'s ";
                    $appendAnd = false;
                    if($unban_avatar) {
                        $message_string .= "avatar ";
                        $appendAnd = true;
                    }
                    if($unban_journal) {
                        if($appendAnd) {
                            $message_string .= "& ";
                        }
                        $message_string .= "journal ";
                    }

                    if($appendAnd) {
                        $message_string .= "bans ";
                    }
                    else {
                        $message_string .= "ban ";
                    }
                    $message_string .= "have been removed.";
                    $system->message($message_string);
                }
                else {
                    $system->message("Error removing avatar/journal ban.");
                }
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
		}
		// Global message [mod panel upgrade complete -Hitori]
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
                $player->staff_manager->staffLog(StaffManager::STAFF_LOG_HEAD_MOD, "$player->user_name($player->user_id) posted global: <br />"
                . $message);
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


    //Banned Users [Mod panel rework complete -Hitori]
	if($view == 'banned_users') {
        $banned_users = $player->staff_manager->getBannedUsers();
        require 'templates/staff/mod/banned_users.php';
	}
    //Locked out users [Mod panel rework complete -Hitori]
	else if($view == 'locked_out_users') {
        $locked_out_users = $player->staff_manager->getLockedUsers();
		require 'templates/staff/mod/locked_out_users.php';
	}
    //New with mod panel upgrade -Hitori
    else if($view == 'banned_ips' && $player->staff_manager->isHeadModerator()) {
        $banned_ips = [];
        $result = $system->query("SELECT * FROM `banned_ips`");
        if($system->db_last_num_rows) {
            while($ip = $system->db_fetch($result)) {
                $banned_ips[] = $ip;
            }
        }

        require 'templates/staff/mod/banned_ips.php';
    }
    //Multi accounts
    else if($view == 'multi_accounts' && $player->staff_manager->isHeadModerator()) {
        if(isset($_GET['action']) && isset($_GET['user_id'])) {
            try {
                $action = $_GET['action'];
                $user_id = (int)($_GET['user_id']);
                if(!in_array($action, StaffManager::$multi_statuses)) {
                    throw new Exception("Invalid status: $action!");
                }
                if(!$player->staff_manager->getUserByID($user_id)) {
                    throw new Exception("UID: $user_id not found!");
                }

                if($player->staff_manager->manageMulti($user_id, $action)) {
                    $system->message("Multi-list updated!");
                }
                else {
                    $system->message("Error updating multi-list!");
                }
            } catch (Exception $e) {
                $system->message($e->getMessage());
            }
        }
        $self_link .= "&view=multi_accounts";

        $accounts = [];
        $to_check = [];
        $query_type = 'current_ip';
        $query_types = ['current_ip', 'last_ip', 'email', 'password'];

        //Multi type
        if(isset($_GET['type'])) {
            $query_type = $system->clean($_GET['type']);
        }
        //Only allow specified multi checks
        if(!in_array($query_type, $query_types)) {
            $query_type = 'current_ip';
        }

        $result = $system->query("SELECT 
            `$query_type`, COUNT(`$query_type`)
        FROM 
             `users` 
        GROUP BY 
             `$query_type`
        HAVING 
            COUNT(`$query_type`) > 2");
        if($system->db_last_num_rows) {
            $to_check = $system->db_fetch_all($result);
        }

        if(!empty($to_check)) {
            $query = "SELECT `user_id`, `user_name`, `password`, `current_ip`, `last_ip`, `email` FROM `users` WHERE `$query_type` IN (";
            foreach ($to_check as $val) {
                $query .= "'" . $val[$query_type] . "', ";
            }
            $query = substr($query, 0, strlen($query) - 2) . ") ORDER BY `$query_type` DESC";
            $result2 = $system->query($query);
            if ($system->db_last_num_rows) {
                while($account = $system->db_fetch($result2)) {
                    $account['multi_status'] = $player->staff_manager->checkMultiStatus($account['user_id']);
                    $accounts[] = $account;
                }
            }
        }

        if($system->message) {
            $system->printMessage();
        }
        require 'templates/staff/mod/multi_accounts.php';
    }
    //Mod logs
    else if($view == 'mod_logs' && $player->staff_manager->isHeadModerator()) {
        $self_link .= "&view=mod_logs";
        $limit = 25;
        $offset = 0;
        $max = $player->staff_manager->getStaffLogs('staff_logs', StaffManager::STAFF_LOG_MOD, 0, $limit, true) - $limit;

        if(isset($_GET['offset'])) {
            $offset = (int) $_GET['offset'];
            if($offset < 0) {
                $offset = 0;
            }
            if($offset > $max) {
                $offset = $max;
            }
        }
        $next = $offset + $limit;
        $previous = $offset - $limit;
        if($next > $max) {
            $next = $max;
        }
        if($previous < 0) {
            $previous = 0;
        }

        $logs = $player->staff_manager->getStaffLogs('staff_logs', StaffManager::STAFF_LOG_MOD, $offset, $limit);
        require_once 'templates/staff/mod/mod_logs.php';
    }
    //Main menu display [Mod panel rework complete -Hitori]
	else if($display_menu) {
		// Mod actions
		require 'templates/staff/mod/mod_menu.php';

		// HM actions
		if($player->staff_manager->isHeadModerator()) {
			require 'templates/staff/mod/head_mod_menu.php';
		}
	}
}