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

if (isset($player)) {
    $NewsManager = new NewsManager($system, $player);
}
else $NewsManager = new NewsManager($system);

?>

<div id="homeReactContainer"></div>
<link rel="stylesheet" type="text/css" href="<?= $system->getCssFileLink("ui_components/src/home/Home.css") ?>" />
<script type="module" src="<?= $system->getReactFile("home/Home") ?>"></script>
<script type="text/javascript">
    const homeContainer = document.querySelector("#homeReactContainer");

    window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Home, {
                homeLinks: <?= json_encode($home_links) ?>,
                isLoggedIn: "<?= isset($player) ?>",
                isAdmin: "<?= isset($player) ? $player->hasAdminPanel() : false ?>",
                version: "<?= System::VERSION_NAME ?>",
                initialView: "<?= $initial_home_view ?>",
                loginURL: "<?= $system->router->base_url ?>",
                registerURL: "<?= $system->router->base_url ?>",
                loginErrorText: "<?= $login_error_text ?>",
                registerErrorText: "<?= $register_error_text ?>",
                resetErrorText: "<?= $reset_error_text ?>",
                loginMessageText: "<?= $login_message_text ?>",
                registerPreFill: <?= json_encode($register_pre_fill) ?>,
                initialNewsPosts: <?= json_encode(NewsAPIPresenter::newsPostResponse($NewsManager, $system)) ?>,
            }),
            homeContainer
        );
    })
</script>