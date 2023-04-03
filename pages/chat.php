<?php
/*
File: 		chat.php
Coder:		Levi Meahan
Update By:  Hitori
Created:	02/26/2013
Revised:	1/27/2023 by Hitori
Purpose:	Function for displaying and allowing users to post messages to tavern chat
Algorithm:	See master_plan.html
*/
function chat() {

    require_once 'classes/ReportManager.php';
	global $system;
	global $player;
	global $self_link;

	if($player->checkBan(StaffManager::BAN_TYPE_CHAT)) {
        $ban_type = StaffManager::BAN_TYPE_CHAT;
        $expire_int = $player->ban_data[$ban_type];
        $ban_expire = ($expire_int == StaffManager::PERM_BAN_VALUE ? $expire_int : $system->time_remaining($player->ban_data[StaffManager::BAN_TYPE_CHAT] - time()));
        require 'templates/ban_info.php';
        return true;
    }

	// Validate post and submit to DB
    $chat_max_post_length = System::CHAT_MAX_POST_LENGTH;
    //Increase chat length limit for seal users & staff members
    if($player->staff_level && $player->forbidden_seal->level == 0) {
        $chat_max_post_length = ForbiddenSeal::$benefits[ForbiddenSeal::$STAFF_SEAL_LEVEL]['chat_post_size'];
    }
    if($player->forbidden_seal->level != 0) {
        $chat_max_post_length = $player->forbidden_seal->chat_post_size;
    }

    if(!isset($_SESSION['quick_reply'])) {
        $_SESSION['quick_reply'] = true;
    }
	if(isset($_POST['post'])) {
    //If user has seal or is of staff, give them their words
		$chat_max_post_length += $player->forbidden_seal ? 100 : 0;
        $message_length = strlen(preg_replace('/[\\n\\r]+/', '', trim($_POST['post'])));
		$message = $system->clean(stripslashes($_POST['post']));

        if(isset($_POST['quick_reply']) && $_SESSION['quick_reply'] == false) {
            $_SESSION['quick_reply'] = true;
        }
        else if(!isset($_POST['quick_reply']) && $_SESSION['quick_reply'] == true) {
            $_SESSION['quick_reply'] = false;
        }

		try {
			$result = $system->query("SELECT `message` FROM `chat` WHERE `user_name` = '$player->user_name' ORDER BY  `post_id` DESC LIMIT 1");
			if($system->db_last_num_rows) {
				$post = $system->db_fetch($result);
				if($post['message'] == $message) {
					throw new Exception("You cannot post the same message twice in a row!");
				}
			}
			if($message_length < 3) {
				throw new Exception("Message is too short!");
			}
			if($message_length > $chat_max_post_length) {
				throw new Exception("Message is too long!");
			}
            //Failsafe, prevent posting if ban
            if($player->checkBan(StaffManager::BAN_TYPE_CHAT)) {
                throw new Exception("You are currently banned from the chat!");
            }

			$title = $player->rank->name;
			$staff_level = $player->staff_level;
			$supported_colors = $player->getNameColors();

			$user_color = '';
            if(isset($supported_colors[$player->chat_color])) {
                $user_color = $supported_colors[$player->chat_color];
            }

			$sql = "INSERT INTO `chat`
                    (`user_name`, `message`, `title`, `village`, `staff_level`, `user_color`, `time`, `edited`) VALUES
                           ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')";
			$system->query(sprintf(
			    $sql, $player->user_name, $message, $title, $player->village->name, $staff_level, $user_color, time(), 0
            ));
			if($system->db_last_affected_rows) {
				$system->message("Message posted!");
			}
		} catch(Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if(isset($_GET['delete']) && $player->staff_manager->isModerator()) {
		$delete = (int) $system->clean($_GET['delete']);
		$result = $system->query("DELETE FROM `chat` WHERE `post_id` = $delete LIMIT 1");
		$return_message = ($system->db_last_affected_rows) ? "Post deleted!" : "Error deleting post!";
		$system->message($return_message);
		$system->printMessage();
	}
	$min = 0;
	$max_posts = 15;

    //Pagination
	if(isset($_GET['min'])) {
		$min = $system->clean($_GET['min']);
		if(!is_numeric($min)) {
			$min = 0;
		}
		else if($min > 200) {
			$min = 200;
		}
        else if($min < 0) {
            $min = 0;
        }
	}
    $previous = ($min - $max_posts > 0 ? $min - $max_posts : 0);
    $refresh = ($previous > 0) ? "&no-refresh=1" : "";
    $next = $min + $max_posts;

    $max_id = 0;
    $result = $system->query("SELECT COUNT(`post_id`) as `count` FROM `chat`");
    if($system->db_last_num_rows) {
        $max_id = $system->db_fetch($result)['count'] - $max_posts;
    }
    if($next > $max_id) {
        $next = $max_id;
    }

    //Set post query limit
	$post_limit = $min . ',' . $max_posts;

    $posts = [];
	$result = $system->query("SELECT * FROM `chat` ORDER BY `post_id` DESC LIMIT $post_limit");
    if($system->db_last_num_rows) {
        while($post = $system->db_fetch($result)) {
            //Skip post if user blacklisted
            $blacklisted = false;
            foreach($player->blacklist as $id => $blacklist) {
                if($post['user_name'] == $blacklist[$id]['user_name']) {
                    $blacklisted = true;
                    break;
                }
            }

            //Base data
            $post['avatar'] = './images/default_avatar.png';
            //Fetch user data
            $user_data = false;
            $user_result = $system->query("SELECT `staff_level`, `premium_credits_purchased`, `chat_effect`, `avatar_link` FROM `users`
                WHERE `user_name` = '{$system->clean($post['user_name'])}'");
            if($system->db_last_num_rows) {
                $user_data = $system->db_fetch($user_result);
                //If blacklisted block content, only if blacklisted user is not currently a staff member
                if($blacklisted && $user_data['staff_level'] == StaffManager::STAFF_NONE) {
                    continue;
                }
            }
            else {
                if($blacklisted) {
                    continue;
                }
            }

            //Format posts
            $statusType = "userLink";
            if($user_data != false) {
                $statusType .= ($user_data['premium_credits_purchased'] && $user_data['chat_effect'] == 'sparkles') ? " premiumUser" : "";
                $post['avatar'] = $user_data['avatar_link'];
            }
            $post['status_type'] = $statusType;

            $class = "chat";
            if(isset($post['user_color'])) {
                $class .= " " . $post['user_color'];
            }
            $post['class'] = $class;

            if($post['staff_level']) {
                $post['staff_banner'] = $system->SC_STAFF_COLORS[$post['staff_level']];
            }

            $time = time() - $post['time'];
            if($time >= 86400) {
                $time_string = floor($time/86400) . " day(s) ago";
            }
            elseif($time >= 3600) {
                $time_string = floor($time/3600) . " hour(s) ago";
            }
            else {
                $mins = floor($time/60);
                if($mins < 1) {
                    $mins = 1;
                }
                $time_string = "$mins min(s) ago";
            }

            $post['time_string'] = $time_string;

            if($player->censor_explicit_language) {
                $post['message'] = $system->explicitLanguageReplace($post['message']);
            }
            $post['message'] = nl2br($system->html_parse($post['message'], false, true));

            $posts[] = $post;
        }
    }

    require 'templates/chat.php';
}
