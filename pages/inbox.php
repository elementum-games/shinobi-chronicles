<?php
/**
* @var System $system
* @var User $player
*
**/

function inbox(): void {
	/*
	-Create conversation with players (Individual and multiple)
		- Send a new message instead of creating a convo if a convo already exists
	-View List of Conversations
	-View Conversation
	-Leave Conversations
	-Change Title of Conversation
	-Remove someone from conversation
	-Report Message

	*/

	global $system;
	global $player;
	
	// Default load
	$convo_count = Inbox::conversationCountForUser($system, $player->user_id);
	$convo_count_max = Inbox::maxConvosAllowed($player->forbidden_seal, $player->staff_level);

	require 'templates/inbox.php';
}

/**
 * @param System $system
 * @param User   $player
 * @return InboxAPIResponse
 */
function LoadConvoList(System $system, User $player): InboxAPIResponse {
	$response = new InboxAPIResponse();

	try {		
		// error management
		if ($system->message) {
			$response->errors[] = $system->message;
			return $response;
		}
		
		$response->response_data = Inbox::allConvosForUser($system, $player->user_id);
	} catch (Exception $e) {
		$response->errors[] = $e->getMessage();
	}

	return $response;
}

/**
 * @param System     $system
 * @param User       $player
 * @param int|string $convo_id
 * @return InboxAPIResponse
 */
function ViewConvo(System $system, User $player, int|string $convo_id): InboxAPIResponse {
    $response = new InboxAPIResponse();

    try {
		$inbox = new InboxManager($system, $player);

		// check if the convo is a system message
		if (in_array($convo_id, Inbox::SYSTEM_MESSAGE_CODES)) {
			$requested_system = array_search($convo_id, Inbox::SYSTEM_MESSAGE_CODES);

			$convo_data = Inbox::getSystemConvo($system, $requested_system, $player->user_id);
			// set all messages to unread
			if (!empty($convo_data)) {
				Inbox::updateUnreadSystemAlert($system, $requested_system, $player->user_id);
			}
			// return
			$response->response_data = $convo_data;
			return $response;
		}
		
		// check if the convo exists
		$exists = Inbox::checkConvo($system, $convo_id);
		if (!$exists) {
			$response->errors[] = 'This conversation does not exist';
			return $response;
		}
		
		// Check if the player is allowed in this conversation
		$convo_allowed = Inbox::verifyAccessToConvo($system, $convo_id, $player->user_id);
		if (!$convo_allowed) {
			$response->errors[] = 'This conversation does not exist';
			return $response;
		}

		// error management
		if ($system->message) {
			$response->errors[] = $system->message;
			return $response;
		}

		// update the last viewed for the player
		Inbox::updateLastViewedForUser($system, $convo_id, $player->user_id);

		// get convo data
		$response->response_data = $inbox->getConversation($convo_id);
	
	} catch (Exception $e) {
		$response->errors[] = $e->getMessage();
	}

	return $response;
}

/**
 * @param System     $system
 * @param User       $player
 * @param int|string $convo_id
 * @param string     $message
 * @return InboxAPIResponse
 */
function SendMessage(System $system, User $player, int|string $convo_id, string $message): InboxAPIResponse {
	$response = new InboxAPIResponse();
	$inbox = new InboxManager($system, $player);
	try {		
		// check if the convo exists
		$exists = Inbox::checkConvo($system, $convo_id);
		if (!$exists) {
			$response->errors[] = 'This conversation does not exist';
			return $response;
		}

		// Check if the player is allowed in this conversation
		$convo_allowed = Inbox::verifyAccessToConvo($system, $convo_id, $player->user_id);
		if (!$convo_allowed) {
			$response->errors[] = 'This conversation does not exist';
			return $response;
		}
		
		// Check if the message is too thicc
		$max_message_length = ($player->forbidden_seal || $player->staff_level)
            ? Inbox::MAX_MESSAGE_LENGTH_SEAL
            : Inbox::MAX_MESSAGE_LENGTH;
		if (strlen($message) > $max_message_length) {
			$response->errors[] = 'Message exceeds ' . $max_message_length . ' characters';
			return $response;
		}
		
		// Check if the message is too small
		if (strlen($message) < Inbox::MIN_MESSAGE_LENGTH) {
			$response->errors[] = 'Message must be longer than ' . Inbox::MIN_MESSAGE_LENGTH . ' characters';
			return $response;
		}

		// error management
		if ($system->message) {
			$response->errors[] = $system->message;
			return $response;
		}

		// Send message
		$result = Inbox::sendMessage($system, $convo_id, $player->user_id, $message);
		if ($result) {
			$response->response_data[] = true;
		} else {
			$response->errors[] = 'Unknown error sending message';
			return $response;
		}
		
	} catch (Exception $e) {
		$response->errors[] = $e->getMessage();
	}

	return $response;
}

/**
 * @param System     $system
 * @param User       $player
 * @param int|string $convo_id
 * @param string     $new_title
 * @return InboxAPIResponse
 */
function ChangeTitle(System $system, User $player, int|string $convo_id, string $new_title): InboxAPIResponse {
	$response = new InboxAPIResponse();
	try {
		// check if the convo exists
		$exists = Inbox::checkConvo($system, $convo_id);
		if (!$exists) {
			$response->errors[] = 'This conversation does not exist';
			return $response;
		}
		// Check if the Title is too short
		// if (empty($new_title)) {
		// 	$response->errors[] = 'Title cannot be empty';
		// 	return $response;
		// }
		// check if the title is too long or too short
		if (!Inbox::checkTitleLength($new_title)) {
			$response->errors[] = 'Title cannot exceed ' . Inbox::MAX_TITLE_LENGTH . ' characters';
			return $response;
		}
		// check if the player is the convo owner
		if ($player->user_id !== Inbox::getConvoOwner($system, $convo_id)) {
			$response->errors[] = 'You are not the owner of this conversation';
			return $response;
		}
		// error management
		if ($system->message) {
			$response->errors[] = $system->message;
			return $response;
		}

		// change title
		$response->response_data[] = Inbox::updateTitle($system, $convo_id, $new_title);

	} catch (Exception $e) {
		$response->errors[] = $e->getMessage();
	}

	return $response;
}

/**
 * @param System     $system
 * @param User       $player
 * @param int|string $convo_id
 * @param string     $new_player
 * @return InboxAPIResponse
 */
function AddPlayer(System $system, User $player, int|string $convo_id, string $new_player): InboxAPIResponse {
	$response = new InboxAPIResponse();
	try {
		// check if the convo exists
		$exists = Inbox::checkConvo($system, $convo_id);
		if (!$exists) {
			$response->errors[] = 'This conversation does not exist';
			return $response;
		}

		// check if the player is the convo owner
		if ($player->user_id !== Inbox::getConvoOwner($system, $convo_id)) {
			$response->errors[] = 'You are not the owner of this conversation';
			return $response;
		}
		// check if the player exists
		$new_user_data = Inbox::getUserData($system, $new_player);
		if (!$new_user_data) {
			$response->errors[] = 'Player does not exist';
			return $response;
		}

		// Check blacklists
		// get all members in a convo
		$convo_members = Inbox::getConvoMembers($system, $convo_id);
		if (!$convo_members) {
			$response->errors[] = 'No members in this conversation';
			return $response;
		}

		// check if the player is already in the convo
		if (Inbox::checkIfUserInConvo($convo_members, $new_user_data->user_id)) {
			$response->errors[] = $new_player . ' is already in this conversation';
			return $response;
		}

		// get the blacklist for the new member
		$convo_members[] = $new_user_data;
		// check each members blacklist
		if (Inbox::checkBlacklist($convo_members)) {
			$response->errors[] = 'Blacklist active';
			return $response;
		}

		// check if the new player is allowed more convos
		$current_count = Inbox::conversationCountForUser($system, $new_user_data->user_id);
		$max_allowed = Inbox::maxConvosAllowed($new_user_data->forbidden_seal, $new_user_data->staff_level);
		if ($current_count >= $max_allowed) {
			$response->errors[] = $new_player . '\'s inbox is full';
			return $response;
		}
		
		// error management
		if ($system->message) {
			$response->errors[] = $system->message;
			return $response;
		}

		// add player
		if (Inbox::addUserToConvo($system, $convo_id, $new_user_data->user_id)) {
			$response->response_data[] = true;
		} else {
			$response->errors[] = 'Fatal error adding player';
		}

	} catch (Exception $e) {
		$response->errors[] = $e->getMessage();
	}

	return $response;
}

/**
 * @param System     $system
 * @param User       $player
 * @param int|string $convo_id
 * @param            $remove_player
 * @return InboxAPIResponse
 */
function RemovePlayer(System $system, User $player, int|string $convo_id, $remove_player): InboxAPIResponse {
	$response = new InboxAPIResponse();

	try {
		// check if the player exists
		$remove_player_data = Inbox::getUserData($system, $remove_player);
		if (!$remove_player_data) {
			$response->errors[] = 'This player does not exist';
			return $response;
		}

		// check if the convo exists
		$exists = Inbox::checkConvo($system, $convo_id);
		if (!$exists) {
			$response->errors[] = 'This conversation does not exist';
			return $response;
		}

		// get the convo owner
		$owner_id = Inbox::getConvoOwner($system, $convo_id);

		// check if the player is not the convo owner
		if ($player->user_id != $owner_id) {
			$response->errors[] = 'You are not the owner of this conversation!';
			return $response;
		}

		// check if the removed player is the owner
		if ($owner_id == $remove_player_data->user_id) {
			$response->errors[] = 'You cannot remove the owner of this conversation!';
			return $response;
		}

		// get all members in a convo
		$convo_members = Inbox::getConvoMembers($system, $convo_id);
		if (!$convo_members) {
			$response->errors[] = 'No members in this conversation';
			return $response;
		}

		// check if the player is already in the convo
		if (!Inbox::checkIfUserInConvo($convo_members, $remove_player_data->user_id)) {
			$response->errors[] = $remove_player_data . ' is not in this conversation!';
			return $response;
		}

		// error management
		if ($system->message) {
			$response->errors[] = $system->message;
			return $response;
		}

		// check if there are more members in this conversation
		// mark the convo as deleted and remove the player
		if (count($convo_members) === 2) {
			$response->errors[] = 'You must leave this conversation to remove this user';
			return $response;
		}

		// remove player from convo
		if (Inbox::removePlayerFromConvo($system, $convo_id, $remove_player_data->user_id)) {
			$response->response_data[] = 'Removed';
		} else {
			$response->errors[] = 'Fatal error removing player';
			return $response;
		}

	} catch (Exception $e) {
		$response->errors[] = $e->getMessage();
	}

	return $response;
}

/**
 * @param System     $system
 * @param User       $player
 * @param int|string $convo_id
 * @return InboxAPIResponse
 */
function LeaveConversation(System $system, User $player, int|string $convo_id): InboxAPIResponse {
	$response = new InboxAPIResponse();
	$inbox = new InboxManager($system, $player);
	try {
		// check if the convo exists
		$exists = Inbox::checkConvo($system, $convo_id);
		if (!$exists) {
			$response->errors[] = 'This conversation does not exist';
			return $response;
		}
		// get all members in a convo
		$convo_members = Inbox::getConvoMembers($system, $convo_id);
		if (!$convo_members) {
			$response->errors[] = 'No members in this conversation';
			return $response;
		}
		// check if the player is in the convo
		if (!Inbox::checkIfUserInConvo($convo_members, $player->user_id)) {
			$response->errors[] = 'You are not part of this conversation!';
			return $response;
		}

		// error management
		if ($system->message) {
			$response->errors[] = $system->message;
			return $response;
		}		
		
		// get the convo owner
		$owner_id = Inbox::getConvoOwner($system, $convo_id);
		// remove conversation
		if ($inbox->leaveConversation($convo_id, $owner_id, $convo_members)) {
			$response->response_data[] = true;
		} else {
			$response->errors[] = 'Fatal error leaving the conversation';
		}

	} catch (Exception $e) {
		$response->errors[] = $e->getMessage();
	}

	return $response;
}

/**
 * @param System      $system
 * @param User        $player
 * @param string      $members
 * @param string|null $title
 * @param string      $message
 * @return InboxAPIResponse
 */
function CreateNewConvo(System $system, User $player, string $members, ?string $title, string $message): InboxAPIResponse {
	$response = new InboxAPIResponse();

	try {
		// check if the message is too long or too short
		if (!Inbox::checkMessageLength($message, $player->forbidden_seal, $player->staff_level)) {
			$response->errors[] = 'Message exceeds ' . Inbox::checkMaxMessageLength($player->forbidden_seal, $player->staff_level) . ' characters';
			return $response;
		} else if (strlen($message) < Inbox::MIN_MESSAGE_LENGTH) {
			$response->errors[] = 'Message must be atleast ' . Inbox::MIN_MESSAGE_LENGTH . ' characters';
			return $response;
		}

		// check if the title is too long
		if (strlen($title) > Inbox::MAX_TITLE_LENGTH) {
			$response->errors[] = 'Title exceeds ' . Inbox::MAX_TITLE_LENGTH . ' characters';
			return $response;
		}
		// Check if it's the same player
		if (strtolower($members) == strtolower($player->user_name)) {
			$response->errors[] = 'You cannot start a conversation with yourself...?';
			return $response;
		}

		$all_player_data = [];
		// check if each member exists
		$convo_members = array_map('trim', explode(',', $members));
		$convo_members[] = $player->user_name;
		foreach($convo_members as $member) {
			$player_data = Inbox::getUserData($system, $member);
			if (!$player_data) {
				$response->errors[] = $member . ' does not exist';
				return $response;
			}

            // check the count of active convos for a user
			if ($player_data->getConvoCount($system) >= $player_data->max_convos_allowed) {
				$response->errors[] = $player_data->user_name . '\'s inbox is full!';
				return $response;
			}
			$all_player_data[] = $player_data;
		}

		// check all blacklists
		if (Inbox::checkBlacklist($all_player_data)) {
			$response->errors[] = 'Blacklist is active';
			return $response;
		}

		// create convo
		$convo_id = Inbox::createConversation($system, $player->user_id, $title);
		if (!$convo_id) {
			$response->errors[] = 'Fatal error creating the conversation';
			return $response;
		}

		// add users to the convo
		foreach($all_player_data as $player_data) {
			if (!Inbox::addUserToConvo($system, $convo_id, $player_data->user_id)) {
				$response->errors[] = 'Fatal error add players to the conversation';
				return $response;
			}
		}

		// send the message
		if (Inbox::sendMessage($system, $convo_id, $player->user_id, $message)) {
			$response->response_data[] = 'Success';
		} else {
			$response->errors[] = 'Fatal error sending message!';
		}
		
		// error management
		if ($system->message) {
			$response->errors[] = $system->message;
			return $response;
		}		
		
	} catch (Exception $e) {
		$response->errors[] = $e->getMessage();
	}

	return $response;
}

/**
 * @param System     $system
 * @param User       $player
 * @param int|string $convo_id
 * @param int        $timestamp
 * @return InboxAPIResponse
 */
function CheckForNewMessages(System $system, User $player, int|string $convo_id, int $timestamp): InboxAPIResponse {
	$response = new InboxAPIResponse();

	try {
		// check if the convo is a system message
		if (in_array($convo_id, Inbox::SYSTEM_MESSAGE_CODES)) {
			$requested_system = array_search($convo_id, Inbox::SYSTEM_MESSAGE_CODES);
			$convo_data = Inbox::getSystemConvo($system, $requested_system, $player->user_id);
			// set all messages to unread
			if (!empty($convo_data)) {
				Inbox::updateUnreadSystemAlert($system, $requested_system, $player->user_id);
			}
			// return
			$response->response_data = $convo_data;
			return $response;
		}
		
		// check if the convo exists
		$exists = Inbox::checkConvo($system, $convo_id);
		if (!$exists) {
			$response->errors[] = 'This conversation does not exist';
			return $response;
		}
		
		// Check if the player is allowed in this conversation
		$convo_allowed = Inbox::verifyAccessToConvo($system, $convo_id, $player->user_id);
		if (!$convo_allowed) {
			$response->errors[] = 'This conversation does not exist';
			return $response;
		}

		// error management
		if ($system->message) {
			$response->errors[] = $system->message;
			return $response;
		}

		// get new messages
		$response->response_data['new_messages'] = Inbox::getMessages($system, $player, $convo_id, $timestamp);
		// update the users last view if there are new messages
		if ($response->response_data['new_messages']) {
			Inbox::updateLastViewedForUser($system, $convo_id, $player->user_id);
		}

	} catch (Exception $e) {
		$response->errors[] = $e->getMessage();
	}

	return $response;
}

/**
 * @param System     $system
 * @param User       $player
 * @param int|string $convo_id
 * @param int        $oldest_message_id
 * @return InboxAPIResponse
 */
function LoadNextPage(System $system, User $player, int|string $convo_id, int $oldest_message_id): InboxAPIResponse {
	$response = new InboxAPIResponse();

	try {
		// check if the convo exists
		$exists = Inbox::checkConvo($system, $convo_id);
		if (!$exists) {
			$response->errors[] = 'This conversation does not exist';
			return $response;
		}

		// Check if the player is allowed in this conversation
		$convo_allowed = Inbox::verifyAccessToConvo($system, $convo_id, $player->user_id);
		if (!$convo_allowed) {
			$response->errors[] = 'This conversation does not exist';
			return $response;
		}

		// error management
		if ($system->message) {
			$response->errors[] = $system->message;
			return $response;
		}
		// get older messages
		$response->response_data['older_messages'] = Inbox::getMessages(
            system: $system,
            user: $player,
            convo_id: $convo_id,
            message_id: $oldest_message_id
        );
		if ($response->response_data['older_messages']) {
			$response->response_data['older_messages'][0]['focusTarget'] = true;
		}
	} catch (Exception $e) {
		$response->errors[] = $e->getMessage();
	}

	return $response;
}