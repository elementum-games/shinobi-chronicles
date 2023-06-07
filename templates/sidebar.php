<?php
/**
 * @var System $system
 * @var User $player
 */

$UserAPIManager = new UserAPIManager($system, $player);
$NavigationAPIManager = new NavigationAPIManager($system, $player);

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
                navigationAPIData: {
                    userMenu: <?= json_encode(NavigationAPIPresenter::menuLinksResponse($NavigationAPIManager->getUserMenu())) ?>,
                    activityMenu: <?= json_encode(NavigationAPIPresenter::menuLinksResponse($NavigationAPIManager->getActivityMenu())) ?>,
                    villageMenu: <?= json_encode(NavigationAPIPresenter::menuLinksResponse($NavigationAPIManager->getVillageMenu())) ?>,
                    staffMenu: <?= json_encode(NavigationAPIPresenter::menuLinksResponse($NavigationAPIManager->getStaffMenu())) ?>,
                },
                userAPIData: {
                    playerData: <?= json_encode(UserAPIPresenter::playerDataResponse(player: $player, rank_names: RankManager::fetchNames($system))) ?>,
                },
                logoutTimer: "<?= (System::LOGOUT_LIMIT * 60) - (time() - $player->last_login) ?>",
            }),
            sidebarContainer
        );
    });
</script>