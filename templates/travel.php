<?php
/**
 * @var System $system
 * @var User $player
 */
?>

<link rel="stylesheet" type="text/css" href="ui_components/src/travel/Travel.css" />
<div id="travelContainer"></div>
<script type="module" src="<?= $system->router->base_url ?><?= $system->getReactFile("travel/Travel") ?>"></script>
<script>
    const travelContainer = document.querySelector("#travelContainer");
    const travelPageLink = "<?= $system->router->links['travel'] ?>";
    const travelAPILink = "<?= $system->router->api_links['travel'] ?>";

    window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Travel, {
                travelAPILink: travelAPILink,
                travelPageLink: travelPageLink,
                missionLink: "<?= $system->router->links['mission'] ?>",
                membersLink: "<?= $system->router->links['members'] ?>",
                attackLink: "<?= $system->router->links['battle'] ?>",
                playerId: <?= $player->user_id ?>,
                playerRank: <?= $player->rank_num ?>
            }),
            travelContainer
        );
    });
</script>