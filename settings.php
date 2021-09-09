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
	require("variables.php");
	global $system;

	global $player;

	global $self_link;
	$max_journal_length = 1000;
	// Forbidden seal increase
	if($player->forbidden_seal or $player->staff_level >= $SC_HEAD_MODERATOR) {
		$max_journal_length = 2000;
	}

	$layouts = array('shadow_ribbon', 'geisha', 'classic_blue');
	if($ENVIRONMENT == 'dev') {
	  $layouts[] = 'cextralite';
	}

	if($_POST['change_avatar']) {
		$avatar_link = trim($_POST['avatar_link']);
		try {
			if($player->avatar_ban) {
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
			if(filesize($temp_filename) > 512000) {
				$filesize = round(filesize($temp_filename) / 1024);
				throw new Exception("Image is too large! Size {$filesize}kb, maximum is 500kb");
			}

			$player->avatar_link = $avatar_link;
			$system->message("Avatar updated!");

			unlink($temp_filename);
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if($_POST['change_password']) {
		$password = trim($_POST['current_password']);
		$new_password = trim($_POST['new_password']);
		$confirm_password = trim($_POST['confirm_new_password']);

		$result = $system->query("SELECT `password` FROM `users` WHERE `user_id`='{$player->user_id}' LIMIT 1");
		$result = $system->db_fetch($result);

		try {
			if($system->verify_password($password, $result['password'])) {
				throw new Exception("Current password is incorrect!");
			}

			$password = $new_password;

			if(strlen($password) < $min_password_length) {
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
			if($system->db_affected_rows >= 1) {
				$system->message("Password updated!");
			}
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if($_POST['change_song']) {
		$profile_song = trim($_POST['profile_song']);
		try {
			if($player->song_ban) {
				throw new Exception("You are currently banned from changing your profile song.");
			}
			if(strlen($profile_song) < 1) {
				throw new Exception("Please enter a song link!");
			}
			if(preg_match_all('/\.(mp3|wav|ogg)/i', $profile_song) == false) {
				throw new Exception("Invaild song link!");
			}
			$system->query("UPDATE `users` SET `profile_song`='{$profile_song}' WHERE `user_id`='{$player->user_id}' LIMIT 1");
			if($system->db_affected_rows == 1) {
				$player->profile_song = $profile_song;
				$system->message("Profile song updated!");
			}
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if($_POST['change_journal']) {
		$journal = $system->clean(trim($_POST['journal']));
		try {
			if($player->journal_ban) {
				throw new Exception("You are currently banned from changing your avatar.");
			}

			if(strlen($journal) > $max_journal_length) {
				throw new Exception("Journal is too long! " . strlen($journal) . "/{$max_journal_length} characters");
			}

			$system->query("UPDATE `journals` SET `journal`='$journal' WHERE `user_id`='{$player->user_id}' LIMIT 1");
			if($system->db_affected_rows == 1) {
				$system->message("Journal updated!");
			}
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if($_POST['blacklist_add'] or $_POST['blacklist_remove']) {
		$blacklist_username = $system->clean(trim($_POST['blacklist_name']));
		$result = $system->query("SELECT `user_id`, `user_name`, `staff_level` FROM `users` WHERE `user_name`='{$blacklist_username}'");
		try {
			if($system->db_num_rows == 0) {
				throw new Exception("User doesn't exist or check your spelling!");
			}
			else {
				$blacklist_user = $system->db_fetch($result);
			}
			if($blacklist_user['staff_level'] >= $SC_MODERATOR) {
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
	else if($user_remove = $_GET['blacklist_remove']) {
		$user_remove = abs((int) $user_remove);

		try {
			$user_exists = $player->blacklist[$user_remove];

			$message = ($user_exists) ? "{$player->blacklist[$user_remove]['user_name']} has been removed from your blacklist" : "This user is not on your blacklist.";

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

	else if($_POST['change_layout']) {
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
			$system->message("Layout updated!<script type='text/javascript'>setTimeout('window.location.reload();', 2000);</script>");
			$system->printMessage();
		}
	}

	echo "<table class='table'>
	<tr><th>Avatar</th></tr>
	<tr><td style='text-align:center;'>
		<div style='float:left;width:200px;'>
			<img src='{$player->avatar_link}' style='max-width:150px;max-height:150px;' />
		</div>
		<div>
			<b>Avatar info:</b><br />
				Avatar must be hosted on another website<br />
				Default limit: " . ($player->forbidden_seal ? '175x175' : '125x125') . " pixels<br />
				Avatar can be larger than the limit, but it will be resized<br />
				Max filesize: 500kb<br />

		</div>
		<br style='clear:both;' />
		<br />";
		if(!$player->avatar_ban) {
			echo "<form action='$self_link' method='post'>
			<input type='text' name='avatar_link' value='{$player->avatar_link}' style='width:250px;' /><br />
			<input type='submit' name='change_avatar' value='Change' />
			</form>";
		}
		else {
			echo "<p>You are currently banned from changing your avatar.</p>";
		}

	echo "</td></tr>
	<tr><th>Password</th></tr>
	<tr><td>
		<form action='$self_link' method='post'>
		<label for='current_password' style='width:150px;'>Current password:</label>
			<input type='password' name='current_password' /><br />
		<label for='new_password' style='width:150px;'>New password:</label>
			<input type='password' name='new_password' /><br />
		<label for='confirm_new_password' style='width:150px;'>Confirm new password:</label>
			<input type='password' name='confirm_new_password' /><br />
		<p style='text-align:center;'>
			<input type='submit' name='change_password' value='Change' />
		</p>
		</form>
	</td></tr>";

	$result = $system->query("SELECT `journal` FROM `journals` WHERE `user_id` = '{$player->user_id}' LIMIT 1");
	if($system->db_num_rows == 0) {
		$journal = '';
		$system->query("INSERT INTO `journals` (`user_id`, `journal`) VALUES('{$player->user_id}', '')");
	}
	else {
		$result = $system->db_fetch($result);
		$journal = $result['journal'];
	}

	echo "<tr><th>Layout</th></tr>
	<tr><td>
	<form action='$self_link' method='post'>
	<select name='layout'>";
	foreach($layouts as $layout) {
		echo "<option value='$layout' " . ($player->layout == $layout ? "selected='selected'" : "") .
			" >" . ucwords(str_replace("_", " ", $layout)) . "</option>";
	}
	echo "</select>
	<input type='submit' name='change_layout' value='Change' />
	</form>
	</td></tr>";

	// TODO: Somehow $system->audioType got lost
	/*echo "<tr><th>Profile Song</th></tr>
	<tr><td style='text-align: center;'>
	<p>Player only supports links ending in: .mp3, .ogg, or .wav.</p>
	<audio controls>";
	echo "{$system->audioType($player->profile_song)}";
	echo
		"Your browser does not support the audio element.
	</audio>
	<br />";
	if(!$player->song_ban) {
	echo "<br />
	<form action='$self_link' method='post'>
		<input type='text' name='profile_song' value='{$player->profile_song}' style='width:250px;' /><br />
		<input type='submit' name='change_song' value='Change' />
	</form>";
	}
	else {
		echo "</p>You are currenly banned from editing your profile song.</p>";
	}
	echo "</td></tr>";*/


	echo "<tr><th>Journal</th></tr>
	<tr><td style='text-align:center;'>
	<i>(Images will be resized down to a max of " . ($player->forbidden_seal ? '500x500' : '300x200') . ")</i>";
	if(!$player->journal_ban) {
		echo "<form action='$self_link' method='post'>
		<textarea style='height:200px;width:500px;' name='journal'>" . stripslashes($journal) . "</textarea>
		<br />
		<input type='submit' name='change_journal' value='Update' />
		</form>";
	}
	else {
		echo "<p>You are currently banned from editing your journal.</p>";
	}
	echo "</td></tr>";

	//Blacklist
	echo "<tr><th>Blacklist</th></tr>
		<tr><td style='text-align:center;'>
	";
	if(!empty($player->blacklist)) {
		$list = "";
		$i = 0;
		foreach ($player->blacklist as $id => $name) {
			$i++;
			$list .= "<a href='$members_link&user={$name['user_name']}'>{$name['user_name']}</a><sup>(<a href='$self_link&blacklist_remove=$id'>x</a>)</sup>";
			if(count($player->blacklist) > $i) {
				$list .= ", ";
			}
		}
		echo "$list";
	}
	else {
		echo "<p style='text-align:center;'>No blocked users!</p>";
	}
	echo "
	<br />
	<form action='$self_link' method='post'>
		<input type='text' name='blacklist_name' style='width:250px;' /> <br />
		<input type='submit' name='blacklist_add' value='Add' />
		<input type='submit' name='blacklist_remove' value='Remove' />
	</form>
	</td></tr>
	";

	echo "</tr></table>";

}
