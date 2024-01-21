<?php
/**
 * @var System $system
 * @var User $player
 * @var string $login_error_text
 * @var string $register_error_text
 * @var string $reset_error_text
 * @var string $login_message_text
 * @var string $initial_home_view
 * @var array $home_links
 * @var array $register_pre_fill
 */

$NewsManager = new NewsManager($system, $player ?? null);

?>

<div id="homeReactContainer"></div>
<link rel="stylesheet" type="text/css" href="<?= $system->getCssFileLink("ui_components/src/home/Home.css") ?>" />
<script type="module" src="<?= $system->getReactFile("home/Home") ?>"></script>
<script type="text/javascript">
    const homeContainer = document.querySelector("#homeReactContainer");

    window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Home, {
                homeLinks: <?= json_encode($system->homeVars['links']) ?>,
                isLoggedIn: "<?= isset($player) ?>",
                isAdmin: "<?= isset($player) ? $player->hasAdminPanel() : false ?>",
                version: "<?= System::VERSION_NAME ?>",
                versionNumber: "<?= System::VERSION_NUMBER ?>",
                initialView: "<?= $system->homeVars['view'] ?>",
                loginURL: "<?= $system->homeVars['links']['login_url'] ?>",
                registerURL: "<?= $system->homeVars['links']['register_url'] ?>",
                loginErrorText: "<?= $system->homeVars['errors']['login'] ?>",
                registerErrorText: "<?= $system->homeVars['errors']['register'] ?>",
                resetErrorText: "<?= $systme->homeVars['errors']['reset'] ?>",
                loginMessageText: "<?= $system->homeVars['messages']['login'] ?>",
                registerPreFill: <?= json_encode($system->homeVars['register_prefill']) ?>,
                initialNewsPosts: <?= json_encode(NewsAPIPresenter::newsPostResponse($NewsManager, $system)) ?>,
                scOpen: <?= (int) $system->SC_OPEN ?>,
                reopenTimeWindow: "<?= $system->getMaintenenceEndTime() ?>",
            }),
            homeContainer
        );
    })
</script>
