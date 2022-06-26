<?php
/* 
	File: 		privateMessages.php
	Coder:		Shadekun
	Created:	08/09/2015
    Revised:    08/10/2015
                Notes:
                    Bug fix for User inbox displaying SQL error in fetching user names.
                    Query no longer runs if there are no 'actual' users within the box.
	Purpose:	Private messaging between users.
*/

class Messaging {

	const
		MIN_SUBJECT_LENGTH = 2,
		MIN_MESSAGE_LENGTH = 4,
		MAX_SUBJECT_LENGTH = 50,
		MAX_MESSAGE_LENGTH = 1000,
		SEAL_MAX_MESSAGE_LENGTH = 1500, //also same for moderators
		INBOX_LIMIT = 50,
		SEAL_INBOX_LIMIT = 75,
		STAFF_INBOX_LIMIT = 100;

	public $message_id;

    private
		$Messages,
		$Users,
		$constraints = [],
		$label_colors = [
			"#00C000",
			"#CBCB10",
			"#E00000"
		];

	function __construct() {
		global $system, $player, $self_link;

		$this->system = $system;
		$this->player = $player;
		$this->self_link = $self_link;
		$this->colors = $system->SC_STAFF_COLORS;

		$this->constraints['message_limit'] = ($player->staff_level || $player->forbidden_seal) ? self::SEAL_MAX_MESSAGE_LENGTH : self::MAX_MESSAGE_LENGTH;
		$this->constraints['inbox_limit'] = ($player->staff_level || $player->forbidden_seal) ? (($player->staff_level) ? self::STAFF_INBOX_LIMIT : self::SEAL_INBOX_LIMIT) : self::INBOX_LIMIT;

	}

	function validateForm() {
		$inbox_limit = $this->constraints['inbox_limit'];
		$subject = $this->system->clean(trim($_POST['subject']));
		$recipient = $this->system->clean(trim($_POST['recipient']));
		$message = $this->system->clean(trim($_POST['message']));
		try {
			// Check minimum length 
			if(strlen($subject) < self::MIN_SUBJECT_LENGTH) {
				// throw new Exception("Please enter a subject!");
				$subject = "[No Subject]";
			}
			if(strlen($recipient) < User::MIN_NAME_LENGTH) {
				throw new Exception("Please enter a recipient!");
			}			
			if(strlen($message) < self::MIN_MESSAGE_LENGTH) {
				throw new Exception("Please enter a message!");
			}
			// Check max length of subject/message
			if(strlen($subject) > self::MAX_SUBJECT_LENGTH) {
				throw new Exception(sprintf("Subject is too long! (%d/%d chars)", strlen($subject), self::MAX_SUBJECT_LENGTH));
			}
			if(strlen($message) > self::MAX_MESSAGE_LENGTH) {
				throw new Exception(sprintf("Message is too long! (%d/%d chars)", strlen($message), self::MAX_MESSAGE_LENGTH));
			}
			$result = $this->system->query("SELECT `user_id`, `user_name`, `staff_level`, `forbidden_seal` FROM `users` WHERE `user_name`='{$recipient}'");
			if(! $this->system->db_last_num_rows) {
				throw new Exception("User does not exist!");
			}
			$result = $this->system->db_fetch($result);
			if($result['forbidden_seal']) {
				$result['forbidden_seal'] = json_decode($result['forbidden_seal'], true);
			}
			/* Place Blacklist Here */
			if($recipient != $this->player->user_name){
				$blacklist = $this->system->query("SELECT `blocked_ids` FROM `blacklist` WHERE `user_id`='{$result['user_id']}' LIMIT 1");
				if($this->system->db_last_num_rows != 0){
					$blacklist = $this->system->db_fetch($blacklist);
					$blacklist = json_decode($blacklist['blocked_ids'], true);

					if(array_key_exists($this->player->user_id, $blacklist)) {
						throw new Exception("{$recipient} has blacklisted you, unable to send message.");
					}
					else if (array_key_exists($result['user_id'], $this->player->blacklist)) {
						throw new Exception("Unable to send a message to someone in your own blacklist.");
					}
				}
			}

			if(!$this->player->isModerator()) {
				$mc_result = $this->system->query("SELECT COUNT(`message_id`) as `message_count` FROM `private_messages` WHERE `recipient`='{$result['user_id']}' AND `message_read` < 2");
				$mc_result = $this->system->db_fetch($mc_result);
				$message_count = $mc_result['message_count'];
				$ErrorMsg = "User's inbox is full";
				if($message_count >= $inbox_limit && $result['staff_level'] < User::STAFF_MODERATOR) {
					if($result['forbidden_seal']) {
						if($message_count >= self::SEAL_INBOX_LIMIT) {
							throw new Exception($ErrorMsg);
						}
					}
					else {
						throw new Exception($ErrorMsg);
					}
				}
				else if($message_count >= self::STAFF_INBOX_LIMIT) {
					throw new Exception($ErrorMsg);
				}
			}

			$Message = ($this->system->send_pm($this->player->user_id, $result['user_id'], $subject, $message, $this->player->staff_level)) ? "Message sent!" : "Error sending message!";

			$this->inbox();
			$this->display('inbox');

			$this->system->message($Message);

		} catch (Exception $e) {
			$this->system->message($e->getMessage());
			$this->inbox();
			$this->display('inbox');
		}
		$this->system->printMessage();
	}
	
	function deleteMessage($msg_id = false) {

		if($msg_id) {
			$this->system->query(sprintf("UPDATE `private_messages` SET `message_read` = 2 WHERE `message_id` IN(%s) AND `recipient`='%d'", $msg_id, $this->player->user_id));
			$this->inbox();
		}
		else {
			if(!$this->message_id) {
				if( !empty($_POST['message_id']) && is_array($_POST['message_id']) ) {
					$this->message_id = implode(",", $_POST['message_id']);
					$this->message_id = $this->system->clean($this->message_id);
				}
				else {
				    $this->system->error("You have no messages selected!");
				    return;
                }
			}

			$this->system->query(sprintf("UPDATE `private_messages` SET `message_read` = 2 WHERE `message_id` IN(%s) AND `recipient`='%d'", $this->message_id, $this->player->user_id));
			$message = ($this->system->db_last_affected_rows) ? "Message deleted" : "Invalid message!";
			
			$this->inbox();
			$this->system->message($message);
			$this->system->printMessage();
		}

	}

	/**
	 * @param $type
	 * @param bool|false $report_link
     */
	function display($type, $report_link = false) {
        global $system;

		switch($type) {
			//Show up always
			case 'options':
				echo "
					<form action='$this->self_link&page=delete_message' method='post'>
						<div class='submenu'>
							<ul class='submenu'>
								<li style='width:32.7%;'><a href='{$this->self_link}&page=new_message'>Send Message</a></li>
								<li style='width:32.7%;'><a href='#' onclick='selectAllMessages();'>Select All</a></li>
								<li style='width:32.7%;'><input class='link' type='submit' style='' value='Delete Marked' /></li>
							</ul>
						</div>
					<div class='submenuMargin'></div>
				";
			break;

			//Show the form for sending a new message
			case 'form':

				echo "
					</form>
					<style>
						label {
							width: 90px;
							display:inline-block;
							font-weight:bold;
						}
					</style>
					<form action='{$this->self_link}&page=new_message' method='post'>
						<table class='table'>
							<tr>
								<th>Send New Message</th>
							</tr>
							<tr>
								<td>
									<p style='padding-left:20px;'>
										<label for='recipient'>Send to:</label>
											<input type='text' name='recipient' value='{$this->form_user}' /><br />
										<label for='subject'>Subject:</label>
											<input type='text' name='subject' value='{$this->form_subject}' /><br />
									</p>
									<p style='text-align:center;'>
										<label for='message'>Message</label>
									</p>
									<p style='text-align:center;'>
										<textarea style='height:225px;width:500px;' name='message'></textarea>
										<br /><br />
										<input type='submit' name='new_message' value='Send' />
									</p>
								</td>
							</tr>
						</table>
					</form>
				";

			break;

			//Show the private message
			case 'privateMessage':
				echo "
					<style>
						label {
							display: inline-block;
							width:70px;
							font-weight:bold;
						}
					</style>
					<table class='table'><tr><th>View Message</th></tr>
						<tr>
							<td style='text-align:center;'>
								<a href='{$this->self_link}&page=new_message&subject={$this->subject['send']}&sender={$this->sender}'>Reply</a>
							</td>
						</tr>
						<tr>
							<td>
								<label>Sender:</label>
									<a href='{$system->links['members']}&user={$this->sender}' class='userLink $this->staff'>{$this->sender}</a><br />
								<label>Subject:</label>
									{$this->subject['display']}<br />
								<label>Sent:</label>{$this->time}<br />
							</td>
						</tr>
						<tr>
							<td style='white-space:pre-wrap;'>" . $this->message . "</td>
						</tr>
						<tr>
							<td style='text-align:center;'>
								<a href='{$report_link}&report_type=2&content_id=$this->message_id'>Report Message</a>
							</td>
						</tr>
					</table>
				";
			break;

			//Display error message when message cannot be found
			case 'privateMessage:Error':

				echo "
					<table class='table'>
						<tr>
							<th>Error handling</th>
						</tr>
						<tr>
							<td style='text-align:center;'>There was an error processing your request.</td>
						</tr>
					</table>
				";

			break;

			//Displays standard inbox
			case 'inbox':

				echo "
					<table class='table'>
				";
				if(! $this->Messages) {
					echo "<tr><td style='text-align:center;' colspan='5'>No new messages</td></tr>";
				}
				else {
					echo "
						<tr class='table_multicolumns'>
							<th class='rowHeader' style='width:20%;'>Sender</th>
							<th class='rowHeader' style='width:40%;'>Subject</th>
							<th class='rowHeader' style='width:15%;'></th>
							<th class='rowHeader' colspan='2' style='text-shadow:0px 1px 1px #000000;color:{$this->label_color};'>{$this->msg_count} / {$this->constraints['inbox_limit']}</th>
						</tr>
					";
					$count = 0;
					foreach($this->Messages as $message) {	
						$class = '';
						if(is_int($count++ / 2)) {
							$class = 'row1';
						}
						else {
							$class = 'row2';
						}
						if($message['message_read'] == 0) {
							$class .= ' bold';
						}
						// Staff-level
						$staff = $this->staffColor($message['staff_level']);
						$persons_name = ($this->Users[$message['sender']]) ? $this->Users[$message['sender']] : $message['sender'];

						$sender = $this->Users[$message['sender']];

						if(! ctype_digit($persons_name)) {

							echo "
								<tr class='table_multicolumns'>
									<td style='text-align:center;width:20%;' class='$class'>
										<a href='{$system->links['members']}&user={$persons_name}' class='userLink $staff'>" . $persons_name . "</a>
									</td>
									<td style='text-align:center;width:30%;' class='$class'>" . stripslashes($message['subject']) . "</td>
									<td style='text-align:center;width:20%;' class='$class'>
										<a href='{$this->self_link}&page=view_message&message_id=" . $message['message_id'] . "'>Read</a>
									</td>
									<td style='text-align:center;width:20%;' class='$class'>
										<a href='{$this->self_link}&page=delete_message&message_id=" . $message['message_id'] . "'>Delete</a>
									</td>
									<td style='text-align:center;width:10%;' class='$class'>	
										<input type='checkbox' name='message_id[]' value='" . $message['message_id'] . "' />
									</td>
								</tr>
							";
						}
						else {
							$this->deleteMessage($message['message_id']);
						}

					}
				}
				echo "
						</table>
					</form>
				";

			break;

		}


	}

	function inbox() {

		$sql = sprintf("SELECT `message_id`, `sender`, `subject`, `message_read`, `staff_level` FROM `private_messages` WHERE `recipient` = '%d' AND `message_read` < 2 ORDER BY `message_id` DESC LIMIT 0, %d", $this->player->user_id, $this->constraints['inbox_limit'] + 50);
		$this->system->query($sql);

		$users = array();
		while($message = $this->system->db_fetch()) {
			if((! in_array($message['sender'], array_keys($users))) && ctype_digit($message['sender'])) {
				$users[$message['sender']] = $message['sender'];
			}
			$messages[] = $message;
		}

		if(! $this->system->db_last_num_rows) {
			$this->Messages = NULL;
		}
		else {

			$this->msg_count = $this->system->db_last_num_rows;

			$user_string = implode(",", $users);

			$user = $this->player;

			if($user_string) {
				$user_sql = sprintf("SELECT `user_id`, `user_name` FROM `users` WHERE `user_id` IN(%s)", $user_string);
				$query = $this->system->query($user_sql);

				while($user_fetch = $this->system->db_fetch()) {

					$users[$user_fetch['user_id']] = $user_fetch['user_name'];

				}
				$this->Users = $users;
			}

			$this->Messages = $messages;

			// Set count color
			$this->label_color = ($this->msg_count < ($this->constraints['inbox_limit'] - 10)) ? $this->label_colors[0] : ( ($this->msg_count < $this->constraints['inbox_limit']) ? $this->label_colors[1] : $this->label_colors[2]);

		}

	}

	function displayPrivateMessage() {

		$query = "SELECT * FROM `private_messages` WHERE `message_id` = '%d' AND `recipient` = '%d' AND `message_read` < 2 LIMIT 1";
		$queried = $this->system->query(sprintf($query, $this->message_id, $this->player->user_id));
		$message = $this->system->db_fetch();
		
		if(! $message) {
			return false;
		}
		
		$time_string = $this->system->timeAgo($message['time']);
		$sender = $message['sender'];
		
		if(ctype_digit($sender)) {
			$query = $this->system->query("SELECT `user_name` FROM `users` WHERE `user_id` = '{$message['sender']}' LIMIT 1");
			$user_info = $this->system->db_fetch($query);
			$sender = $user_info['user_name'];
		}
		
		$staff = $this->staffcolor($message['staff_level']);
		$pmsubject = stripslashes($message['subject']);
		$subject = $message['subject'];
		$msg = $this->system->html_parse($message['message'], false, false);
		
		if(substr($subject, 0, 3) != "RE:") {
			$subject = "RE: " . $subject;
		}
		
		if(! $message['read']) {
			$this->system->query("UPDATE `private_messages` SET `message_read`='1' WHERE `message_id`='{$this->message_id}' LIMIT 1");
		}

		// Update the class and unset any variables
		$this->sender = $sender;
		$this->subject = array('send' => $subject, 'display' => stripslashes($subject));
		$this->message = $msg;
		$this->time = $time_string;
		$this->staff = $this->staffColor($message['staff_level']);

		return true;

	}
	
	function staffColor($staff_level) {
		return ($staff_level) ? $this->colors[$staff_level]['pm_class'] : '';
	}

}

function privateMessages() {
	/*
	-send messages
	-view list of messages
	-read messages
	-delete messages
	DATA STRUCTURE
	-message_id
	-sender
	-recipient
	-subject
	-message
	-time
	-staff_level
	-message_read
	*/

	global $system;
	global $player;
	global $self_link;

	$Messaging = new Messaging();

	$Messaging->message_id		= isset($_GET['message_id']) 	? $system->clean($_GET['message_id'])	: NULL;
	$Messaging->form_user		= isset($_GET['sender']) 		? $system->clean($_GET['sender'])		: '';
	$Messaging->form_subject	= isset($_GET['subject']) 		? $system->clean($_GET['subject'])		: '';

	$Messaging->display('options');

	switch($_GET['page'] ?? '') {
		
		//For creating and sending new messages
		case 'new_message':
			(isset($_POST['new_message'])) ? $Messaging->validateForm() : $Messaging->display('form');
		break;

		//For viewing private messages	
		case 'view_message':
			($Messaging->displayPrivateMessage()) ? $Messaging->display('privateMessage', $system->links['members'], $system->links['report']) : $Messaging->display('privateMessage:Error');
		break;
		
		//For deleting messages
		case 'delete_message':
			$Messaging->deleteMessage();
			$Messaging->display('inbox');
		break;

		//For general viewing of inbox
		default:
			$Messaging->inbox();
			$Messaging->display('inbox');
		break;

	}

}

