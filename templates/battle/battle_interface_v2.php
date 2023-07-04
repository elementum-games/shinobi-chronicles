<?php
  /**
   * @var System          $system
   * @var BattleManagerV2 $battleManager
   * @var BattleV2        $battle
   *
 */
?>
<link rel="stylesheet" type="text/css" href="<?= $system->getCssFileLink("ui_components/src/battle/Battle.css") ?>" />
<div id="battleReactContainer"></div>
<script type="module" src="<?= $system->getReactFile("battle/Battle") ?>"></script>
<!--suppress JSUnresolvedVariable, JSUnresolvedFunction -->
<script type="text/javascript">
    const battleContainer = document.querySelector("#battleReactContainer");

    const battle = <?= json_encode($battleManager->getApiResponse()) ?>;
    const membersLink = "<?= $system->router->links['members'] ?>";
    const battleApiLink = "<?= $system->router->api_links['battle'] ?>";

    window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Battle, {
                battle: battle,
                battleApiLink: battleApiLink,
                membersLink: membersLink
            }),
            battleContainer
        );
    })
</script>