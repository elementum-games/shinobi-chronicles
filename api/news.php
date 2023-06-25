<?php

# Begin standard auth
require_once __DIR__ . "/../classes.php";

$system = API::init();

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData(User::UPDATE_REGEN);
} catch (Exception $e) {
    API::exitWithException($e, system: $system);
}
# End standard auth

try {
    // api requires a request
    if (isset($_POST['request'])) {
        $request = filter_input(INPUT_POST, 'request', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    } else {
        throw new RuntimeException('No request was made!');
    }

    $NewsAPIResponse = new NewsAPIResponse();
    $NewsManager = new NewsManager($system, $player);

    switch ($request) {
        case "getLatestPosts":
            $NewsAPIResponse->response_data = [
                'postData' => NewsAPIPresenter::newsPostResponse($NewsManager, $system),
            ];
            break;
        case "getNewsPosts":
            $num_posts = $system->db->clean($_POST['num_posts']);
            $NewsAPIResponse->response_data = [
                'postData' => NewsAPIPresenter::newsPostResponse($NewsManager, $system, $num_posts),
            ];
            break;
        case "saveNewsPost":
            $post_id = $system->db->clean($_POST['post_id']);
            $title = $system->db->clean($_POST['title']);
            $version = $system->db->clean($_POST['version']);
            $content = $system->db->clean($_POST['content']);
            $update = $system->db->clean($_POST['update']) === "true";
            $bugfix = $system->db->clean($_POST['bugfix']) === "true";
            $event = $system->db->clean($_POST['event']) === "true";
            $num_posts = $system->db->clean($_POST['num_posts']);
            $NewsAPIResponse->response_data = [
                'postData' => NewsAPIPresenter::savePostResponse($NewsManager, $system, $post_id, $title, $version, $content, $update, $bugfix, $event, $num_posts),
            ];
            break;
        default:
            API::exitWithError(message: "Invalid request!", system: $system);
    }

    API::exitWithData(
        data: $NewsAPIResponse->response_data,
        errors: $NewsAPIResponse->errors,
        debug_messages: $system->debug_messages,
        system: $system,
    );
} catch (Throwable $e) {
    API::exitWithException($e, system: $system);
}