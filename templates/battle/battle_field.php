<?php
    /**
     * @var System $system;
     * @var BattleManager $battleManager
     * @var Battle $battle
     * @var Fighter $player
     * @var Fighter $opponent
     *
     * @var string $self_link
     * @var string $refresh_link
     */

    require_once 'templates/battle/fighter_avatar.php';
?>

<style type='text/css'>
    .tilesContainer {
        display: flex;
        justify-content: space-evenly;
        padding: 10px 0;
    }
    .tile {
        height: 70px;
        width: 70px;

        display: inline-flex;
        position: relative;
        align-items: center;
        justify-content: center;

        background: #948d78;
        border-radius: 35px;
        border: 1px solid #807a68;
    }
    .tileIndex {
        position: absolute;
        bottom: 1px;
        left: 0;
        right: 0;
        text-align: center;

        color: white;
        font-size: 8px;
        opacity: 0.7;
    }
    .tileFighter {
        height: 30px;
        width: 30px;
        display: flex;
        align-items: center;
        justify-content: center;

        background: rgba(255,255,255,0.5);
        border-radius: 15px;
        border: 1px solid rgba(0,0,0,0.4);
        color: black;
    }

    .tileFighter.ally {
        background: rgba(0,0,255,0.4);
    }
    .tileFighter.enemy {
        background: rgba(255,0,0,0.4);
    }
</style>

<?php
    function renderTileFighter(Fighter $fighter, bool $is_ally) {
        ?>
        <div class='tileFighter <?= $is_ally ? 'ally' : 'enemy' ?>'>
            <?php renderAvatarImage($fighter, 20) ?>
        </div>
        <?php
    }

    $tiles = $battleManager->field->getDisplayTiles();
?>

<div class='tilesContainer'>
    <?php foreach($tiles as $index => $tile): ?>
        <div class='tile' id='tile<?= $index ?>'>
            <span class='tileIndex'><?= $index ?></span>
            <?php
                foreach($tile->fighters as $fighter) {
                    /** @var Fighter $fighter */
                    renderTileFighter($fighter, $fighter->combat_id == $player->combat_id);
                }
            ?>
        </div>
    <?php endforeach; ?>
</div>
<form action="<?= $self_link ?>" method="POST" id="battle_field_form">
    <input type="hidden" name="foo" value="bar" />
</form>

<script type='text/javascript'>
    const form = document.getElementById('battle_field_form');
    const tiles = document.querySelectorAll('.tile');
</script>
