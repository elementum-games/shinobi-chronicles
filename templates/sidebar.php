<?php
/**
 * @var System $system
 * @var User $player
 */

$UserAPIManager = new UserAPIManager($system, $player);
$NavigationAPIManager = NavigationAPIManager::loadNavigationAPIManager($system, $player);

?>

<link rel="stylesheet" type="text/css" href="<?= $system->getCssFileLink("ui_components/src/sidebar/Sidebar.css") ?>" />
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
                },
                navigationAPIData: {
                    userMenu: <?= json_encode(NavigationAPIPresenter::menuLinksResponse($NavigationAPIManager->getUserMenu())) ?>,
                    activityMenu: <?= json_encode(NavigationAPIPresenter::menuLinksResponse($NavigationAPIManager->getActivityMenu())) ?>,
                    villageMenu: <?= json_encode(NavigationAPIPresenter::menuLinksResponse($NavigationAPIManager->getVillageMenu())) ?>,
                    staffMenu: <?= json_encode(NavigationAPIPresenter::menuLinksResponse($NavigationAPIManager->getStaffMenu())) ?>,
                },
                userAPIData: {
                    playerData: <?= json_encode(UserAPIPresenter::playerDataResponse(player: $player, rank_names: RankManager::fetchNames($system))) ?>,
                    playerResources: <?= json_encode(UserAPIPresenter::playerResourcesResponse(player: $player)) ?>,
                    playerSettings: <?= json_encode(UserAPIPresenter::playerSettingsResponse(player: $player)) ?>
                },
            }),
            sidebarContainer
        );
    });
</script>