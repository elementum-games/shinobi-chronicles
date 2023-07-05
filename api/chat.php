<?php

# Begin standard auth
require "../classes/_autoload.php";

$system = API::init(row_lock: false);

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData(User::UPDATE_NOTHING);

    if($player->checkBan(StaffManager::BAN_TYPE_CHAT)) {
        API::exitWithData(
            data: ChatAPIPresenter::banInfoResponse(system: $system, player: $player),
            errors: [],
            debug_messages: [],
            system: $system
        );
    }

    $chatManager = new ChatManager($system, $player);

    $request = $_POST['request'] ?? 'load_posts';

    switch($request) {
        case 'load_posts':
            $starting_post_id = isset($_POST['starting_post_id'])
                ? (int)$_POST['starting_post_id']
                : null;

            API::exitWithData($chatManager->loadPosts($starting_post_id), [], [], system: $system);

        case 'submit_post':
            $message = $system->db->clean($_POST['message']);

            API::exitWithData(
                data: $chatManager->submitPost($message),
                errors: [],
                debug_messages: [],
                system: $system
            );
        case 'delete_post':
            $post_id = (int)$_POST['post_id'];

            API::exitWithData(
                data: $chatManager->deletePost($post_id),
                errors: [],
                debug_messages: [],
                system: $system,
            );
    }
} catch(RuntimeException $e) {
    API::exitWithException($e, system: $system);
}
# End standard auth




