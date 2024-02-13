<?php
/**
 * @var System $system
 * @var User   $player
 * @var ?SpecialMission $special_mission
 */
?>
<link rel='stylesheet' type='text/css' href='<?= $system->getCssFileLink("ui_components/src/special_missions/SpecialMission.css") ?>' />
<div id="specialMissionReactContainer"></div>
<script type="module" src="<?= $system->getReactFile("special_missions/SpecialMission") ?>"></script>
<!--suppress JSUnresolvedVariable, JSUnresolvedFunction -->
<script type="text/javascript">
    const specialMissionContainer = document.querySelector("#specialMissionReactContainer");

    window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(SpecialMission, {
                selfLink: "<?= $system->router->getUrl('special_missions') ?>",
                missionEventDurationMs: <?= SpecialMission::EVENT_DURATION_MS ?>,
                specialMissionId: <?= $player->special_mission_id ?? 0 ?>,
                initialMissionData: <?= json_encode($special_mission) ?>,
            }),
            specialMissionContainer
        );
    })
</script>

