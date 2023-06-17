<?php

/**
 * @throws Exception
 */
function chat(): void {
    global $system;
    global $player;
    $chatManager = new ChatManager($system, $player);
    if (isset($_GET['post_id'])) {
        $initialChatPostId = (int) $system->clean($_GET['post_id']);
        $initialChatPostsResponse = $chatManager->loadPosts($initialChatPostId);
    } else {
        $initialChatPostsResponse = $chatManager->loadPosts();
    }



    require 'templates/chat.php';
}