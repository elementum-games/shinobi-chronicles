<?php
/**
 * @var System $system
 * @var User $player
 * @var NewsManager $NewsManager
 * @var LoginManager $LoginManager
 * @var string $login_error_text
 * @var string $register_error_text
 * @var string $reset_error_text
 * @var string $login_message_text
 * @var string $initial_home_view
 * @var array $home_links
 * @var array $register_pre_fill
 */

?>

<div id="homeReactContainer"></div>
<link rel="stylesheet" type="text/css" href="<?= $system->getCssFileLink("ui_components/src/home/Home.css") ?>" />
<script type="module" src="<?= $system->getReactFile("home/Home") ?>"></script>
<script type="text/javascript">
    const homeContainer = document.querySelector("#homeReactContainer");

    window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Home, {
                homeLinks: <?= json_encode($LoginManager->home_links) ?>,
                isLoggedIn: "<?= isset($player) ?>",
                isAdmin: "<?= isset($player) && $player->hasAdminPanel() ?>",
                version: "<?= System::VERSION_NAME ?>",
                versionNumber: "<?= System::VERSION_NUMBER ?>",
                initialView: "<?= $LoginManager->initial_home_view ?>",
                loginURL: "<?= $system->router->base_url ?>",
                registerURL: "<?= $system->router->base_url ?>",
                loginErrorText: "<?= $LoginManager->login_error_text ?>",
                loginUserNotActive: <?= (int) $LoginManager->login_user_not_active ?>,
                registerErrorText: "<?= $LoginManager->register_error_text ?>",
                resetErrorText: "<?= $LoginManager->reset_error_text ?>",
                loginMessageText: "<?= $LoginManager->login_message_text ?>",
                registerPreFill: <?= json_encode($LoginManager->register_prefill) ?>,
                initialNewsPosts: <?= json_encode(NewsAPIPresenter::newsPostResponse($NewsManager, $system)) ?>,
                SC_OPEN: <?= (int) $system->SC_OPEN ?>,
            }),
            homeContainer
        );
    })
</script>