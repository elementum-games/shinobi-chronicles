<?php

# Begin standard auth
require "../classes/_autoload.php";

$system = new System();
$system->is_api_request = true;

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData(User::UPDATE_NOTHING);
} catch(Exception $e) {
    API::exitWithError($e->getMessage());
}
# End standard auth

try {
    include __DIR__ . '/../pages/inbox.php';

    $request = $system->clean($_POST['request']);
    switch($request) {
        case 'LoadConvoList':
            $response = LoadConvoList($system, $player);
            break;

        case 'ViewConvo':
            $requested_convo_id = $system->clean($_POST['requested_convo_id']);

            $response = ViewConvo($system, $player, $requested_convo_id);
            break;

        case 'LoadNextPage':
            $requested_convo_id = $system->clean($_POST['requested_convo_id']);
            $oldest_message_id = $system->clean($_POST['oldest_message_id']);

            $response = LoadNextPage($system, $player, $requested_convo_id, $oldest_message_id);
            break;

        case 'CheckForNewMessages':
            $requested_convo_id = $system->clean($_POST['requested_convo_id']);
            $timestamp = $system->clean($_POST['timestamp']);

            $response = CheckForNewMessages($system, $player, $requested_convo_id, $timestamp);
            break;

        case 'SendMessage':
            $requested_convo_id = $system->clean($_POST['requested_convo_id']);
            $message = $system->clean($_POST['message']);

            $response = SendMessage($system, $player, $requested_convo_id, $message);
            break;

        case 'ChangeTitle':
            $requested_convo_id = $system->clean($_POST['requested_convo_id']);
            $new_title = $system->clean($_POST['new_title']);

            $response = ChangeTitle($system, $player, $requested_convo_id, $new_title);
            break;

        case 'AddPlayer':
            $requested_convo_id = $system->clean($_POST['requested_convo_id']);
            $new_player = $system->clean($_POST['new_player']);

            $response = AddPlayer($system, $player, $requested_convo_id, $new_player);
            break;

        case 'RemovePlayer':
            $requested_convo_id = $system->clean($_POST['requested_convo_id']);
            $remove_player = $system->clean($_POST['remove_player']);

            $response = RemovePlayer($system, $player, $requested_convo_id, $remove_player);
            break;

        case 'LeaveConversation':
            $requested_convo_id = $system->clean($_POST['requested_convo_id']);

            $response = LeaveConversation($system, $player, $requested_convo_id);
            break;

        case 'CreateNewConvo':
            $members = $system->clean($_POST['members']);
            $title = $system->clean($_POST['title']);
            $message = $system->clean($_POST['message']);

            $response = CreateNewConvo($system, $player, $members, $title, $message);
            break;
        default:
            API::exitWithError("Invalid request!");
    }

    API::exitWithData(
        data: [
            'request' => $request,
            'response_data' => $response->response_data
        ],
        errors: $response->errors,
        debug_messages: $system->debug_messages,
    );
} catch (Throwable $e) {
    API::exitWithError($e->getMessage());
}

