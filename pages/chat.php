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

	global $system;
	global $ajax;
	global $player;
	global $self_link;

	if($player->checkBan(StaffManager::BAN_TYPE_CHAT)) {
        $ban_type = StaffManager::BAN_TYPE_CHAT;
        $expire_int = $player->ban_data[$ban_type];
        $ban_expire = ($expire_int == StaffManager::PERM_BAN_VALUE ? $expire_int : $system->time_remaining($player->ban_data[StaffManager::BAN_TYPE_CHAT] - time()));
        require 'templates/ban_info.php';
        return true;
    }

    //Load staff manager
    if($player->staff_manager === false) {
        $player->loadStaffManager();
    }

	// Validate post and submit to DB
    $chat_max_post_length = System::CHAT_MAX_POST_LENGTH;
    //Increase chat length limit for seal users & staff members
    if($player->staff_level && !$player->forbidden_seal instanceof ForbiddenSeal) {
        $chat_max_post_length = ForbiddenSeal::$benefits[ForbiddenSeal::$STAFF_SEAL_LEVEL]['chat_post_size'];
    }
    if($player->forbidden_seal_loaded && $player->forbidden_seal->level != 0) {
        $chat_max_post_length = $player->forbidden_seal->chat_post_size;
    }

	if(isset($_POST['post'])) {
    //If user has seal or is of staff, give them their words
		$chat_max_post_length += $player->forbidden_seal ? 100 : 0;
        $message_length = strlen(preg_replace('/[\\n\\r]+/', '', trim($_POST['post'])));
		$message = $system->clean(stripslashes($_POST['post']));
    
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

			$title = $player->rank_name;
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
			    $sql, $player->user_name, $message, $title, $player->village, $staff_level, $user_color, time(), 0
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
	if(isset($_GET['min'])) {
		$min = $system->clean($_GET['min']);
		if(!is_numeric($min)) {
			$min = 0;
		}
		else if($min > 200) {
			$min = 200;
		}
	}
	$post_limit = $min . ',' . $max_posts;
	$result = $system->query("SELECT * FROM `chat` ORDER BY `post_id` DESC LIMIT $post_limit");
	// Chat event close
	if(false) {
		$system->message("Chat is currently crying in a corner. Please check back later.");
		$system->printMessage();
		return true;
	}
	// Form to post message
	if(!$ajax) {
		echo "<div class='submenu'>
		<ul class='submenu'>";
			if(isset($_GET['no_refresh'])) {
				echo "<li style='width:100%;'><a href='$self_link'>Turn Auto Chat On</a></li>";
			}
			else {
				echo "<li style='width:100%;'><a href='$self_link&no_refresh=1'>Turn Auto Chat Off</a></li>";
			}
		echo "</ul>
		</div>
		<div class='submenuMargin'></div>";
		echo "
		<script type='text/javascript'>
		var shiftPressed = false;
		$(document).ready(function(){
			$('#chatMessage').keypress(function( event ) {
				if (event.which == 13 && !event.shiftKey && $('#quickReply').prop('checked')) {
					$('#chatSubmit').trigger('click');
				}
			});
            $('#chatMessage').keyup(function (evt) {
                if(this.value.length >= $chat_max_post_length - 20)
                {
                    let remaining = $chat_max_post_length - this.textLength;
                    $('#remainingCharacters').text('Characters remaining: ' + remaining + ' out of ' + $chat_max_post_length);
                }
                else 
                {
                    $('#remainingCharacters').text('');
                }
            })
		});
		</script>";
		// Quick reply
		if(!isset($_SESSION['quick_reply'])) {
			$_SESSION['quick_reply'] = 1;
		}
		if(isset($_POST['chat_submit'])) {
			if(empty($_POST['quick_reply'])) {
				$_SESSION['quick_reply'] = 0;
			}
			else {
				$_SESSION['quick_reply'] = 1;
			}
		}
		echo "<table id='chat_input_table' class='table'>
			<tr><th>Post Message</th></tr>
			<tr><td style='text-align:center;'>
			<form action='$self_link' method='post'>
				<textarea id='chatMessage' name='post' style='width:375px;height:100px;' minlength='3' maxlength='". $chat_max_post_length ."'></textarea><br />
				<input type='checkbox' id='quickReply' name='quick_reply' value='1' " .
				($_SESSION['quick_reply'] ? "checked='checked'" : '') .
				"/> Quick reply<br />
				<span id='remainingCharacters' class='red'></span>
				<br />
				<input id='chatSubmit' name='chat_submit' type='submit' value='Post'/>
			</form>
			</td></tr>
		</table>";
		if(!isset($_GET['no_refresh'])) {
			echo "<script type='text/javascript'>
			var refreshID;
			$(document).ready(function(){
				refreshID = setInterval('javascript:$(\'#socialPosts\').load(\'$self_link&request_type=ajax\');', 3000);
			});
			</script>";
		}
		echo "<div id='socialPosts'>";
	}
	// Table with chat posts
	echo "<table class='table' style='width:98%;'>
		<tr>
			<th style='width:28%;'>Users</th>
			<th style='width:61%;'>Message</th>
			<th style='width:10%;'>Time</th>
		</tr>";
		if(! $system->db_last_num_rows) {
			echo "<tr><td colspan='2' style='text-align:center;'>No posts!</td></tr>";
		}
		while($post = $system->db_fetch($result)) {
			$user_result = $system->query("SELECT `premium_credits_purchased`, `chat_effect`, `avatar_link` FROM `users`
                WHERE `user_name` = '{$system->clean($post['user_name'])}'");
			$userData = $system->db_fetch($user_result);

            $statusType = "userLink ";
            $statusType .= ($userData['premium_credits_purchased'] && $userData['chat_effect'] == 'sparkles') ? "premiumUser" : "";
            $class = "chat ";
            if(isset($post['user_color'])) {
                $class .= $post['user_color'];
            }

			/*If User is Blocked, Skip their Echo'd Post!*/
			$isBlocked = false;
			foreach($player->blacklist as $id => $blacklist){
				//if post has same username as someone in their blacklist
				if($post['user_name'] == $blacklist[$id]['user_name']){
					// echo "".$post['user_name']." <- Fuck this guy!";
					$isBlocked = true;
				}
			}
			//skip post
			if($isBlocked){
				$isBlocked = false; //just in case?
				continue;
			}

      $message = $system->explicitLanguageReplace($post['message']);
      $message = $system->html_parse(stripslashes($message), false, true);

			echo "
				<tr class='chat_msg' >
					<td style='text-align:center;'>
					<div id='user_data_container' style='display: flex;flex-direction:row'>
					    <div style='flex-shrink:0;'>
					        <img style='max-height:45px;max-width:45px;' src='{$userData['avatar_link']}' />
                        </div>
						<div style='display:block;flex-grow:1;'>
							<a style='display:inline-block;' href='{$system->links['members']}&user={$post['user_name']}' class='$class $statusType'>{$post['user_name']}</a><br />
							<p style='margin: 1px 0 3px;'>
                                <img src='./images/village_icons/" . strtolower($post['village']) . ".png' alt='{$post['village']} Village'
                                    style='max-width:20px;max-height:20px;vertical-align:text-bottom;'  title='{$post['village']} Village' /> " .
                            stripslashes($post['title']) . "</p>
						</div>
					</div>";

                    if($post['staff_level']) {
                        $color = $system->SC_STAFF_COLORS[$post['staff_level']];
                        echo "<p class='staffMember' style='background-color: {$color['staffColor']}'>{$color['staffBanner']}</p>";
                    }
                echo "</td>
				<td class='chatmsg' style='text-align:center;padding:4px;white-space:pre-wrap;'>" . $message . "</td>";
				$post_time = time() - $post['time'];
				$post_minutes = ceil($post_time / 60);
				$post_hours = floor($post_minutes / 60);
				if($post_hours) {
					$post_minutes -= $post_hours * 60;
					if($post_minutes < 10) {
						$post_minutes = "0" . $post_minutes;
					}
					$posted = $post_hours . ":" . $post_minutes . "<br /> ago";
				}
				else {
					$posted = $post_minutes . " min(s) ago";
				}

				echo "<td style='text-align:center;font-style:italic;'>
                    <div style='margin-bottom: 2px;'>{$posted}</div>";

                    if($player->staff_manager->isModerator()) {
                        echo sprintf("<a class='imageLink' href='$self_link&delete=%d'><img src='../images/delete_icon.png' style='max-width:20px;max-height:20px;' /></a>", $post['post_id']);
                    }
                    echo "<a class='imageLink' href='{$system->links['report']}&report_type=3&content_id=" . $post['post_id'] . "'>
					    <img src='../images/report_icon.png' style='max-width:20px;max-height:20px;' /></a>
                    </td>";
				echo "</tr>";
		}
	echo "</table>";
	// Pagination
	echo "<p style='text-align:center;'>";
	if($min > 0) {
		$prev = $min - $max_posts;
		if($prev <= 0) {
			$prev = 0;
			$refresh = '';
		}
		else {
			$refresh = '&no_refresh=1';
		}
		echo "<a href='$self_link&min=$prev{$refresh}'>Previous</a>";
	}

	$posts_per_page = 10;

	$result = $system->query("SELECT COUNT(`post_id`) as `count` FROM `chat`");
	$result = $system->db_fetch();
	if($result['count'] >= 200) {
		$result['count'] = 200;
	}
	if($min + $posts_per_page < $result['count']) {
		if($min > 0) {
			echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
		}
		$next = $min + $max_posts;
		echo "<a href='$self_link&min=$next&no_refresh=1'>Next</a>";
	}
	echo "</p>";
	if(!$ajax) {
		echo "</div>";
	}
}
