<?php
/**
 * @var System $system
 * @var User $player
 */
?>

<link rel="stylesheet" type="text/css" href="<?= $system->getCssFileLink("ui_components/src/village/Village.css") ?>" />
<div id="villageReactContainer"></div>
<script type="module" src="<?= $system->getReactFile("village/Village") ?>"></script>
<!--suppress JSUnresolvedVariable, JSUnresolvedFunction -->
<script type="text/javascript">
    const villageContainer = document.querySelector("#villageReactContainer");

    window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Village, {
                playerID: <?= $player->user_id ?>,
                playerSeat: <?= json_encode($player->village_seat) ?>,
                villageName: "<?= $player->village->name ?>",
                villageAPI: "<?= $system->router->api_links['village'] ?>",
                policyData: <?= json_encode(VillageApiPresenter::policyDataResponse($system, $player)) ?>,
                populationData: <?= json_encode(VillageApiPresenter::populationDataResponse($system, $player)) ?>,
                seatData: <?= json_encode(VillageApiPresenter::seatDataResponse($system, $player)) ?>,
                pointsData: <?= json_encode(VillageApiPresenter::pointsDataResponse($system, $player)) ?>,
                diplomacyData: <?= json_encode(VillageApiPresenter::diplomacyDataResponse($system, $player)) ?>,
                resourceData: <?= json_encode(VillageApiPresenter::resourceDataResponse($system, $player, 1)) ?>,
                clanData: <?= json_encode(VillageApiPresenter::clanDataResponse($system, $player)) ?>,
                proposalData: <?= json_encode(VillageApiPresenter::proposalDataResponse($system, $player)) ?>,
                strategicData: <?= json_encode(VillageApiPresenter::strategicDataResponse($system)) ?>,
                challengeData: <?= json_encode(VillageApiPresenter::challengeDataResponse($system, $player)) ?>,
                warLogData: <?= json_encode(VillageApiPresenter::warLogDataResponse($system, $player)) ?>,
            }),
            villageContainer
        );
    })
</script>
