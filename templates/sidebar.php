<?php
/**
 * @var System $system
 * @var User $player
 */
?>

<?php
function sidebar(): void {
	global $system;
	global $player;

    try {
        $sidebarManager = new SidebarManager($system, $player);
    } catch (Exception $e) {
        $system->message($e->getMessage());
    }

    $system->printMessage();
}
?>

<link rel="stylesheet" type="text/css" href="ui_components/src/sidebar/Sidebar.css" />

<div id="sidebarContainer" class="d-in_block"></div>
<script type="module" src="<?= $system->getReactFile("sidebar/Sidebar") ?>"></script>
<script>
    const sidebarContainer = document.querySelector("#sidebarContainer");

        window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Sidebar, {
                links: {
                    navigation_api: "<?= $system->router->api_links['navigation'] ?>",
                    user_api: "<?= $system->router->api_links['user'] ?>",
                    logout_link: "<?= $system->router->base_url . "?logout=1" ?>",
                },
                logoutTimer: "<?= (System::LOGOUT_LIMIT * 60) - (time() - $player->last_login) ?>",
            }),
            sidebarContainer
        );
    });
</script>