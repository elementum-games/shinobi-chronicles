<?php
/**
 * @var System          $system
 * @var User $player
 * @var ChatManager $chatManager
 * @var array $initialChatPostsResponse from ChatApiPresenter::loadPostsResponse
 */
?>
<link rel="stylesheet" type="text/css" href="ui_components/src/chat/Chat.css" />
<div id="chatReactContainer"></div>
<script type="module" src="<?= $system->getReactFile("chat/Chat") ?>"></script>
<!--suppress JSUnresolvedVariable, JSUnresolvedFunction -->
<script type="text/javascript">
    const chatContainer = document.querySelector("#chatReactContainer");

    const initialPostsResponse = <?= json_encode($initialChatPostsResponse) ?>;
    const initialBanInfo = <?= json_encode(ChatAPIPresenter::banInfoResponse($system, $player)); ?>;

    window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Chat, {
                chatApiLink: "<?= $system->router->api_links['chat'] ?>",
                initialPosts: initialPostsResponse.posts,
                initialNextPageIndex: initialPostsResponse.nextPageIndex,
                initialMaxPostIndex: initialPostsResponse.maxPostIndex,
                maxPostLength: <?= $chatManager->maxPostLength() ?>,
                isModerator: <?= $player->isModerator() ?>,
                initialBanInfo
            }),
            chatContainer
        );
    })
</script>