<?php
/* 
File: 		settings.php
Coder:		Levi Meahan
Created:	08/24/2013
Revised:	08/24/2013 by Levi Meahan
Purpose:	Functions for allowing user to modify settings/preferences
Algorithm:	See master_plan.html
*/

function userSettings() {
	global $system;
	
	global $player;
	
	global $self_link;
	$max_journal_length = 1000;
	// Forbidden seal increase
    if($player->staff_level && !$player->forbidden_seal_loaded) {
        $psuedoSeal = new ForbiddenSeal($system, ForbiddenSeal::$STAFF_SEAL_LEVEL);
        $psuedoSeal->setBenefits();
        $max_journal_length = $psuedoSeal->journal_size;
    }
	if($player->forbidden_seal_loaded && $player->forbidden_seal->level != 0) {
        $max_journal_length = $player->forbidden_seal->journal_size;
	}
	
	$layouts = array('shadow_ribbon', 'geisha', 'classic_blue', 'blue_scroll', 'rainbow_road');
	if($system->environment == 'dev') {
	    $layouts[] = 'cextralite';
	}

	require_once "profile.php";
	renderProfileSubmenu();

	if(!empty($_POST['change_avatar'])) {
		$avatar_link = trim($_POST['avatar_link']);
		try {
			if($player->checkBan(StaffManager::BAN_TYPE_AVATAR)) {
                throw new Exception("You are currently banned from changing your avatar.");
            }
		
			if(strlen($avatar_link) < 5) {
				throw new Exception("Please enter an avatar link!");
			}
			$avatar_link = $system->clean($avatar_link);
			
			if(!getimagesize($avatar_link)) {
				throw new Exception("Image does not exist!");
			}
			
			$suffix_array = explode(".", $avatar_link);
			$suffix = $suffix_array[count($suffix_array) - 1];
			unset($suffix_array);
			$content = file_get_contents($avatar_link); 
			$temp_filename = "./images/avatars/{$player->user_name}.$suffix";
			$handle = fopen($temp_filename, "w+"); 
			fwrite($handle, $content); 
			fclose($handle);
            if(filesize($temp_filename) > User::AVATAR_MAX_FILE_SIZE) {
				$filesize = round(filesize($temp_filename) / 1024);
				throw new Exception("Image is too large! Size {$filesize}kb, maximum is " . $player->getAvatarFileSize());
			}

			$player->avatar_link = $avatar_link;
			$system->message("Avatar updated!");
			
			unlink($temp_filename); 		
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if(!empty($_POST['change_password'])) {
		$password = trim($_POST['current_password']);
		$new_password = trim($_POST['new_password']);
		$confirm_password = trim($_POST['confirm_new_password']);
		
		$result = $system->query("SELECT `password` FROM `users` WHERE `user_id`='{$player->user_id}' LIMIT 1");
		$result = $system->db_fetch($result);
		
		try {
			if(!$system->verify_password($password, $result['password'])) {
				throw new Exception("Current password is incorrect!");
			}
			
			$password = $new_password;
			
			if(strlen($password) < User::MIN_PASSWORD_LENGTH) {
				throw new Exception("Please enter a password longer than 3 characters!");
			}
			
			if(preg_match('/[0-9]/', $password) == false) {
				throw new Exception("Password must include at least one number!");
			}
			if(preg_match('/[A-Z]/', $password) == false) {
				throw new Exception("Password must include at least one capital letter!");
			}
			if(preg_match('/[a-z]/', $password) == false) {
				throw new Exception("Password must include at least one lowercase letter!");
			}
			$common_passwords = array(
				'Password1'
			);
			foreach($common_passwords as $pword) {
				if($pword == $password) {
					throw new Exception("This password is too common, please choose a more unique password!");
				}
			}
			
			if($password != $confirm_password) {
				throw new Exception("The passwords do not match!");
			}
		
			$password = $system->hash_password($password);
			$system->query("UPDATE `users` SET `password`='$password' WHERE `user_id`='{$player->user_id}' LIMIT 1");
			if($system->db_last_affected_rows >= 1) {
				$system->message("Password updated!");
			}
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if(!empty($_POST['change_journal'])) {
		try {
            $journal_length = strlen(preg_replace('/[\\n\\r]+/', '', trim($_POST['journal'])));
            if($journal_length > $max_journal_length) {
                throw new Exception("Journal is too long! " . $journal_length . "/{$max_journal_length} characters");
            }

            $journal = $system->clean($_POST['journal']);

			if($player->checkBan(StaffManager::BAN_TYPE_JOURNAL)) {
				throw new Exception("You are currently banned from changing your journal.");
			}
			
			$system->query("UPDATE `journals` SET `journal`='$journal' WHERE `user_id`='{$player->user_id}' LIMIT 1");
			if($system->db_last_affected_rows == 1) {
				$system->message("Journal updated!");
			}
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if(!empty($_POST['blacklist_add']) or !empty($_POST['blacklist_remove'])) {
		$blacklist_username = $system->clean(trim($_POST['blacklist_name']));
		$result = $system->query("SELECT `user_id`, `user_name`, `staff_level` FROM `users` WHERE `user_name`='{$blacklist_username}'");
		try {
			if($system->db_last_num_rows == 0) {
				throw new Exception("User doesn't exist or check your spelling!");
			}
			else {
				$blacklist_user = $system->db_fetch($result);
			}
			if($blacklist_user['staff_level'] >= User::STAFF_MODERATOR) {
				throw new Exception("You are unable to blacklist staff members!");
			}
			if($player->user_id == $blacklist_user['user_id']) {
				throw new Exception("You cannot blacklist yourself!");
			}
			if(isset($_POST['blacklist_add'])) {
				if (!empty($player->blacklist) && array_key_exists($blacklist_user['user_id'], $player->blacklist)) {
					throw new Exception("User already in your blacklist!");
				}
				$player->blacklist[$blacklist_user['user_id']][$blacklist_user['user_id']] = $blacklist_user;
				$system->message("{$blacklist_user['user_name']} added to blacklist.");
			}
			else {
				if($player->blacklist[$blacklist_user['user_id']]) {
					unset($player->blacklist[$blacklist_user['user_id']]);
					$system->message("{$blacklist_user['user_name']} has been removed from your blacklist.");
				}
				else {
					$system->message("{$blacklist_user['user_name']} is not on your blacklist");
				}
			}
			
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if($user_remove = $_GET['blacklist_remove'] ?? null) {
		$user_remove = abs((int) $user_remove);

		try {
			$user_exists = $player->blacklist[$user_remove];

            $message = ($user_exists) ? "{$player->blacklist[$user_remove][$user_remove]['user_name']} has been removed from your blacklist" : "This user is not on your blacklist.";

			if($user_exists) {
				unset($player->blacklist[$user_remove]);
			}
			
			$system->message($message);

		}
		catch(Exception $e) {
			echo $e->getMessage();
		}
		$system->printMessage();
	}
	else if(!empty($_POST['change_layout'])) {
		$layout = $system->clean($_POST['layout']);
		if(array_search($layout, $layouts) === false) {
			$layout = null;
		}
		
		if(!$layout) {
			$system->message("Invalid layout choice!");
			$system->printMessage();
		}
		else {
			$query = "UPDATE `users` SET `layout`='$layout' WHERE `user_id`='$player->user_id' LIMIT 1";
			$system->query($query);
			$system->message("Layout updated!
                <script type='text/javascript'>setTimeout('window.location.assign(window.location.href)', 2000);</script>");
			$system->printMessage();
		}
	}

    // Fetch journal info
	$result = $system->query("SELECT `journal` FROM `journals` WHERE `user_id` = '{$player->user_id}' LIMIT 1");
	if($system->db_last_num_rows == 0) {
		$journal = '';
		$system->query("INSERT INTO `journals` (`user_id`, `journal`) VALUES('{$player->user_id}', '')");
	}
	else {
		$result = $system->db_fetch($result);
		$journal = $result['journal'];
	}

    // Fetch blacklist data
    if(!empty($player->blacklist)) {
        $list = "";
        $i = 0;
        foreach ($player->blacklist as $id => $name) {
            $i++;
            // var_dump($name);
            $list .= "<a href='{$system->links['members']}&user={$name[$id]['user_name']}'>{$name[$id]['user_name']}</a><sup>(<a href='$self_link&blacklist_remove=$id'>x</a>)</sup>";
            if(count($player->blacklist) > $i) {
                $list .= ", ";
            }
        }
    }

    // Account details
    if(isset($_GET['view'])) {
        switch($_GET['view']) {
            case 'account':
                $warnings = $player->getOfficialWarnings();
                $warning = false;
                $bans = false;
                $ban_result = $system->query("SELECT * FROM `user_record` WHERE `user_id`='{$player->user_id}' AND `record_type` IN ('"
                . StaffManager::RECORD_BAN_ISSUED . "', '" . StaffManager::RECORD_BAN_REMOVED . "') ORDER BY `time` DESC");
                if($system->db_last_num_rows) {
                    while($ban = $system->db_fetch($ban_result)) {
                        $bans[] = $ban;
                    }
                }

                if(isset($_GET['warning_id'])) {
                    $warning = $player->getOfficialWarning((int)$_GET['warning_id']);
                }
                break;
        }
    }

    require_once('templates/settings.php');
}
