<?php

# Begin standard auth
require "../classes/_autoload.php";

$system = new System();
$system->is_api_request = true;

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData(User::UPDATE_NOTHING);

    if($player->checkBan(StaffManager::BAN_TYPE_CHAT)) {
        API::exitWithData(
            data: ChatAPIPresenter::banInfoResponse(system: $system, player: $player),
            errors: [],
            debug_messages: []
        );
    }

    $chatManager = new ChatManager($system, $player);

    $request = $_POST['request'] ?? 'load_posts';

    switch($request) {
        case 'load_posts':
            API::exitWithData($chatManager->loadPosts(), [], []);
        case 'submit_post':
            $message = $system->clean($_POST['message']);

            API::exitWithData(
                data: $chatManager->submitPost($message),
                errors: [],
                debug_messages: []
            );
        case 'delete_post':
            $post_id = (int)$_POST['post_id'];

            API::exitWithData(
                data: $chatManager->deletePost($post_id),
                errors: [],
                debug_messages: []
            );
    }
} catch(Exception $e) {
    API::exitWithError($e->getMessage());
}
# End standard auth




