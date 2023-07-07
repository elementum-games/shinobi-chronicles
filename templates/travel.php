<?php
/**
 * @var System $system
 * @var User $player
 */
?>

<link rel="stylesheet" type="text/css" href="<?= $system->getCssFileLink("ui_components/src/travel/Travel.css") ?>" />
<div id="travelContainer"></div>
<script type="module" src="<?= $system->getReactFile("travel/Travel") ?>"></script>
<script>
    const travelContainer = document.querySelector("#travelContainer");

    window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Travel, {
                travelAPILink: "<?= $system->router->api_links['travel'] ?>",
                travelPageLink: "<?= $system->router->links['travel'] ?>",
                battleAPILink: "<?= $system->router->api_links['battle'] ?>",
                missionLink: "<?= $system->router->links['mission'] ?>",
                membersLink: "<?= $system->router->links['members'] ?>",
                attackLink: "<?= $system->router->links['battle'] ?>",
                playerId: <?= $player->user_id ?>,
                travelCooldownMs: <?= Travel::TRAVEL_DELAY_MOVEMENT ?>,
            }),
            travelContainer
        );
    });
</script>