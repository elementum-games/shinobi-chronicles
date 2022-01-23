<?php
  /**
   * @var System $system
   * @var Battle $battle
 */
?>
<div id="battleReactContainer"></div>
<script type="text/javascript" src="<?= $system->getReactFile("battle/Battle") ?>"></script>
<!--suppress JSUnresolvedVariable, JSUnresolvedFunction -->
<script type="text/javascript">
    const battleId = <?= $battle->battle_id ?>;
    const battleContainer = document.querySelector("#battleReactContainer");
    ReactDOM.render(
        React.createElement(Battle, { battleId: battleId }),
        battleContainer
    );
</script>