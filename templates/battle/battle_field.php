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

        background: #0A2060;
        border-radius: 35px;
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
    }

    .avatar {
        max-height: 20px;
        max-width: 20px;
    }
</style>

<?php $tiles = $battleManager->field->getDisplayTiles(); ?>

<div class='tilesContainer'>
    <?php foreach($tiles as $index => $tile): ?>
        <div class='tile'>
            <span class='tileIndex'><?= $index ?></span>
            <?php foreach($tile->fighters as $fighter): ?>
                <?php /** @var Fighter $fighter */ ?>
                <div class='tileFighter'>
                    <img class='avatar' src='<?= $fighter->avatar_link ?>' />
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>


<div
