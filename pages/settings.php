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

	// Forbidden seal increase
    if($player->staff_level && $player->forbidden_seal->level == 0) {
        $psuedoSeal = new ForbiddenSeal($system, ForbiddenSeal::$STAFF_SEAL_LEVEL);
        $psuedoSeal->setBenefits();
        $max_journal_length = $psuedoSeal->journal_size;
    }
	else {
        $max_journal_length = $player->forbidden_seal->journal_size;
	}

    $layouts = array('shadow_ribbon', 'geisha', 'new_geisha', 'classic_blue', 'blue_scroll', 'rainbow_road');
	if($system->environment == 'dev') {
	    $layouts[] = 'cextralite';
		$layouts[] = 'sumu';
	}

    if (!empty($_POST['change_avatar'])) {
        $avatar_link = trim($_POST['avatar_link']);
        try {
            if ($player->checkBan(StaffManager::BAN_TYPE_AVATAR)) {
                throw new RuntimeException("You are currently banned from changing your avatar.");
            }

            if (strlen($avatar_link) < 5) {
                throw new RuntimeException("Please enter an avatar link!");
            }
            $avatar_link = $system->db->clean($avatar_link);

            if (!getimagesize($avatar_link)) {
                throw new RuntimeException("Image does not exist!");
            }

            $avatar_filesize = getAvatarFileSize($avatar_link);

            if ($avatar_filesize > $player->forbidden_seal->avatar_filesize) {
                $filesize_display = round($avatar_filesize / 1024);
                throw new RuntimeException("Image is too large! Size {$filesize_display}KB, maximum is " . $player->getAvatarFileSizeDisplay('kb'));
            }

            $player->avatar_link = $avatar_link;
            $system->message("Avatar updated!");
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
        $system->printMessage();
    } else if (!empty($_POST['change_password'])) {
        $password = trim($_POST['current_password']);
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_new_password']);

        $result = $system->db->query("SELECT `password` FROM `users` WHERE `user_id`='{$player->user_id}' LIMIT 1");
        $result = $system->db->fetch($result);

        try {
            if (!$system->verify_password($password, $result['password'])) {
                throw new RuntimeException("Current password is incorrect!");
            }

            $password = $new_password;

            if (strlen($password) < User::MIN_PASSWORD_LENGTH) {
                throw new RuntimeException("Please enter a password longer than 3 characters!");
            }

            if (preg_match('/[0-9]/', $password) == false) {
                throw new RuntimeException("Password must include at least one number!");
            }
            if (preg_match('/[A-Z]/', $password) == false) {
                throw new RuntimeException("Password must include at least one capital letter!");
            }
            if (preg_match('/[a-z]/', $password) == false) {
                throw new RuntimeException("Password must include at least one lowercase letter!");
            }
            $common_passwords = array(
                'Password1'
            );
            foreach ($common_passwords as $pword) {
                if ($pword == $password) {
                    throw new RuntimeException("This password is too common, please choose a more unique password!");
                }
            }

            if ($password != $confirm_password) {
                throw new RuntimeException("The passwords do not match!");
            }

            $password = $system->hash_password($password);
            $system->db->query("UPDATE `users` SET `password`='$password' WHERE `user_id`='{$player->user_id}' LIMIT 1");
            if ($system->db->last_affected_rows >= 1) {
                $system->message("Password updated!");
            }
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
        $system->printMessage();
    } else if (!empty($_POST['change_journal'])) {
        try {
            $journal_length = strlen(preg_replace('/[\\n\\r]+/', '', trim($_POST['journal'])));
            if ($journal_length > $max_journal_length) {
                throw new RuntimeException("Journal is too long! " . $journal_length . "/{$max_journal_length} characters");
            }

            $journal = $system->db->clean($_POST['journal']);

            if ($player->checkBan(StaffManager::BAN_TYPE_JOURNAL)) {
                throw new RuntimeException("You are currently banned from changing your journal.");
            }

            $system->db->query(
                "UPDATE `journals` SET `journal`='$journal' WHERE `user_id`='{$player->user_id}' LIMIT 1"
            );
            if ($system->db->last_affected_rows == 1) {
                $system->message("Journal updated!");
            }
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
        $system->printMessage();
    } else if (!empty($_POST['blacklist_add']) or !empty($_POST['blacklist_remove'])) {
        $blacklist_username = $system->db->clean(trim($_POST['blacklist_name']));
        $result = $system->db->query(
            "SELECT `user_id`, `user_name`, `staff_level` FROM `users` WHERE `user_name`='{$blacklist_username}'"
        );
        try {
            if ($system->db->last_num_rows == 0) {
                throw new RuntimeException("User doesn't exist or check your spelling!");
            } else {
                $blacklist_user = $system->db->fetch($result);
            }
            if ($blacklist_user['staff_level'] >= User::STAFF_MODERATOR) {
                throw new RuntimeException("You are unable to blacklist staff members!");
            }
            if ($player->user_id == $blacklist_user['user_id']) {
                throw new RuntimeException("You cannot blacklist yourself!");
            }
            if (isset($_POST['blacklist_add'])) {
                if (!empty($player->blacklist) && array_key_exists($blacklist_user['user_id'], $player->blacklist)) {
                    throw new RuntimeException("User already in your blacklist!");
                }
                $player->blacklist[$blacklist_user['user_id']][$blacklist_user['user_id']] = $blacklist_user;
                $system->message("{$blacklist_user['user_name']} added to blacklist.");
            } else {
                if ($player->blacklist[$blacklist_user['user_id']]) {
                    unset($player->blacklist[$blacklist_user['user_id']]);
                    $system->message("{$blacklist_user['user_name']} has been removed from your blacklist.");
                } else {
                    $system->message("{$blacklist_user['user_name']} is not on your blacklist");
                }
            }

        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
        $system->printMessage();
    } else if ($user_remove = $_GET['blacklist_remove'] ?? null) {
        $user_remove = abs((int) $user_remove);

        try {
            $user_exists = $player->blacklist[$user_remove];

            $message = ($user_exists) ? "{$player->blacklist[$user_remove][$user_remove]['user_name']} has been removed from your blacklist" : "This user is not on your blacklist.";

            if ($user_exists) {
                unset($player->blacklist[$user_remove]);
            }

            $system->message($message);

        } catch (RuntimeException $e) {
            echo $e->getMessage();
        }
        $system->printMessage();
    } else if (!empty($_POST['change_layout'])) {
        $layout = $system->db->clean($_POST['layout']);
        if (array_search($layout, $layouts) === false) {
            $layout = null;
        }

        if (!$layout) {
            $system->message("Invalid layout choice!");
            $system->printMessage();
        } else {
            $query = "UPDATE `users` SET `layout`='$layout' WHERE `user_id`='$player->user_id' LIMIT 1";
            $system->db->query($query);
            $system->message("Layout updated!
                <script type='text/javascript'>setTimeout('window.location.assign(window.location.href)', 2000);</script>");
            $system->printMessage();
        }
    } else if (!empty($_POST['change_avatar_style'])) {
        $style = $system->db->clean($_POST['avatar_style']);
        if ($player->setAvatarStyle($style)) {
            $system->message("Avatar style updated!");
        } else {
            $system->message("No change detected, check your selection and try again.");
        }

        $system->printMessage();
    } else if (!empty($_POST['change_avatar_frame'])) {
        $style = $system->db->clean($_POST['avatar_frame']);
        if ($player->setAvatarFrame($style)) {
            $system->message("Avatar frame updated!");
        } else {
            $system->message("No change detected, check your selection and try again.");
        }

        $system->printMessage();
    } else if (!empty($_POST['change_sidebar_position'])) {
        $position = $system->db->clean($_POST['sidebar_position']);
        if ($player->setSidebarPosition($position)) {
            $system->message("Sidebar position updated!");
        } else {
            $system->message("No change detected, check your selection and try again.");
        }

        $system->printMessage();
    } else if (!empty($_POST['change_sidebar_collapse'])) {
        $collapse = $system->db->clean($_POST['sidebar_collapse']);
        if ($player->setSidebarCollapse($collapse)) {
            $system->message("Sidebar collapse updated!");
        } else {
            $system->message("No change detected, check your selection and try again.");
        }

        $system->printMessage();

    } else if (!empty($_POST['change_enable_alerts'])) {
        $enable = $system->db->clean($_POST['enable_alerts']);
        if ($player->setEnableAlerts((bool)$enable)) {
            $system->message("Alert settings updated!");
        } else {
            $system->message("No change detected, check your selection and try again.");
        }
        $system->printMessage();
    } else if (!empty($_POST['change_card_image'])) {
        $image = $system->db->clean($_POST['card_image']);
        if ($player->setCardImage($image)) {
            $system->message("Card updated!");
        } else {
            $system->message("No change detected, check your selection and try again.");
        }

        $system->printMessage();
    } else if (!empty($_POST['change_banner_image'])) {
        $image = $system->db->clean($_POST['banner_image']);
        if ($player->setBannerImage($image)) {
            $system->message("Banner updated!");
        } else {
            $system->message("No change detected, check your selection and try again.");
        }

        $system->printMessage();
    }
    else if(!empty($_POST['level_rank_up'])) {
        $level_up = isset($_POST['level_up']);
        $rank_up = isset($_POST['rank_up']);
        $data_changed = false;
        if($rank_up != $player->rank_up) {
            $data_changed = true;
            $player->rank_up = $rank_up;
        }
        if($level_up != $player->level_up) {
            $data_changed = true;
            $player->level_up = $level_up;
        }

        if($data_changed) {
            $system->message("Rank and level settings updated!");
        }
        else {
            $system->message("You must change rank or level settings!");
        }

        $system->printMessage();
    }
    else if(!empty($_POST['censor_explicit_language'])) {
        if($_POST['censor_explicit_language'] == 'on') {
            $player->censor_explicit_language = true;
        }
        else if($_POST['censor_explicit_language'] == 'off') {
            $player->censor_explicit_language = false;
        }

        $system->message("Censor explicit language preference set to <b>" . ($player->censor_explicit_language ? "on" : "off") . "</b>.");
        $system->printMessage();
    }

    // Fetch journal info
	$result = $system->db->query("SELECT `journal` FROM `journals` WHERE `user_id` = '{$player->user_id}' LIMIT 1");
	if($system->db->last_num_rows == 0) {
		$journal = '';
		$system->db->query("INSERT INTO `journals` (`user_id`, `journal`) VALUES('{$player->user_id}', '')");
	}
	else {
		$result = $system->db->fetch($result);
		$journal = $result['journal'];
	}

    // Fetch blacklist data
    if(!empty($player->blacklist)) {
        $list = "";
        $i = 0;
        foreach ($player->blacklist as $id => $name) {
            $i++;
            // var_dump($name);
            $list .= "<a href='{$system->router->links['members']}&user={$name[$id]['user_name']}'>{$name[$id]['user_name']}</a><sup>(<a href='$self_link&blacklist_remove=$id'>x</a>)</sup>";
            if(count($player->blacklist) > $i) {
                $list .= ", ";
            }
        }
    }

	// Temp settings
    $sidebar_position = $player->getSidebarPosition();
    $sidebar_collapse = $player->getSidebarCollapse();
    $avatar_style = $player->getAvatarStyle();
    $avatar_styles = $player->forbidden_seal->avatar_styles;
    $avatar_frame = $player->getAvatarFrame();
    $avatar_frames = ['avy_frame_default' => 'Default', 'avy_frame_none' => 'None', 'avy_frame_shadow' => 'Shadow'];
    $enable_alerts = $player->getEnableAlerts();
    $card_image = $player->getCardImage();
    $banner_image = $player->getBannerImage();
    $supported_colors = $player->getNameColors();
    $user_color = '';
    if (isset($supported_colors[$player->chat_color])) {
        $user_color = $supported_colors[$player->chat_color];
    } else {
        $user_color = 'normalUser';
    }

    require_once('templates/settings.php');
}

function getAvatarFileSize(string $avatar_link): bool|int {
    $suffix_array = explode(".", $avatar_link);
    $suffix = $suffix_array[count($suffix_array) - 1];
    unset($suffix_array);

    $content = file_get_contents($avatar_link);
    $temp_filename = "./images/avatars/" . uniqid(more_entropy: true) . $suffix;

    $handle = fopen($temp_filename, "w+");
    fwrite($handle, $content);
    fclose($handle);

    $size = filesize($temp_filename);

    unlink($temp_filename);

    return $size;
}