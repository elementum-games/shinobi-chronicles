<?php
/**
 * @var System $system
 * @var User $player
 */

$UserAPIManager = new UserAPIManager($system, $player);

?>

<link rel="stylesheet" type="text/css" href="<?= $system->getCssFileLink("ui_components/src/hotbar/Hotbar.css") ?>" />

<div id="hotbarContainer"></div>
<script type="module" src="<?= $system->getReactFile("hotbar/Hotbar") ?>"></script>
<script>
    const hotbarContainer = document.querySelector("#hotbarContainer");

        window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Hotbar, {
                links: {
                    user_api: "<?= $system->router->api_links['user'] ?>",
                    training: "<?= $system->router->links['training'] ?>",
                    arena: "<?= $system->router->links['arena'] ?>",
                    mission: "<?= $system->router->links['mission'] ?>",
                    special_missions: "<?= $system->router->links['special_missions'] ?>",
                    healingShop: "<?= $system->router->links['healingShop'] ?>",
                    base_url: "<?= $system->router->base_url ?>"
                },
                userAPIData: {
                    playerData: <?= json_encode(UserAPIPresenter::playerDataResponse(player: $player, rank_names: RankManager::fetchNames($system))) ?>,
                    missionData: <?= json_encode(UserAPIPresenter::missionDataResponse(userManager: $UserAPIManager)) ?>,
                    aiData: <?= json_encode(UserAPIPresenter::aiDataResponse(userManager: $UserAPIManager)) ?>,
                },
            }),
            hotbarContainer
        );
    });
</script>