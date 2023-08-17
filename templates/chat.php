<?php
/**
 * @var System          $system
 * @var User $player
 * @var ChatManager $chatManager
 * @var array $initialChatPostsResponse from ChatApiPresenter::loadPostsResponse
 * @var int $initialChatPostId
 * @var Layout $layout
 */
?>
<?php if ($system->layout->usesV2Interface()): ?>
    <link rel="stylesheet" type="text/css" href="<?= $system->getCssFileLink("ui_components/src/chat/Chat_new.css") ?>" />
    <div id="chatReactContainer"></div>
    <script type="module" src="<?= $system->getReactFile("chat/Chat_new") ?>"></script>
<?php else: ?>
    <link rel="stylesheet" type="text/css" href="<?= $system->getCssFileLink("ui_components/src/chat/Chat.css") ?>" />
    <div id="chatReactContainer"></div>
    <script type="module" src="<?= $system->getReactFile("chat/Chat") ?>"></script>
<?php endif; ?>
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
                initialPostId: <?= $initialChatPostId ?? "null" ?>,
                initialNextPagePostId: initialPostsResponse.nextPagePostId,
                initialPreviousPagePostId: initialPostsResponse.previousPagePostId,
                initialLatestPostId: initialPostsResponse.latestPostId,
                maxPostLength: <?= $chatManager->maxPostLength() ?>,
                isModerator: Boolean(<?= (int)$player->staff_manager->isModerator() ?>),
                initialBanInfo: initialBanInfo,
                memes: <?= json_encode($system->getMemes()) ?>,
            }),
            chatContainer
        );
    })
</script>