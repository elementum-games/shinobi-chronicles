<?php

/**
 * @throws Exception
 */
function chat(): void {
    global $system;
    global $player;

    $chatManager = new ChatManager($system, $player);
    $initialChatPostsResponse = $chatManager->loadPosts();

    require 'templates/chat.php';
}