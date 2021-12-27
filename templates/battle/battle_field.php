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

    .tilesContainer.movementActive .tile[data-player-tile="0"] {
        cursor: pointer;
    }
    .tilesContainer.movementActive .tile:hover[data-player-tile="0"] {
        background: #b1a990;
    }
    .tilesContainer.movementActive .tile.selected[data-player-tile="0"] {
        background: #b1a990;
        border-color: #5d594c;
        box-shadow: 0 0 3px 0 black;
    }
</style>

<?php
    function renderTileFighter(?Fighter $fighter, bool $is_ally) {
        if($fighter == null) {
            return;
        }
        ?>
        <div class='tileFighter <?= $is_ally ? 'ally' : 'enemy' ?>'>
            <?php renderAvatarImage($fighter, 20) ?>
        </div>
        <?php
    }

    $tiles = $battleManager->field->getDisplayTiles();
?>

<div class='tilesContainer <?= $battle->isMovementPhase() ? 'movementActive' : '' ?>'>
    <?php foreach($tiles as $index => $tile): ?>
        <div
            class='tile'
            id='tile<?= $index ?>'
            data-tile-index='<?= $index ?>'
            data-player-tile='<?=
                0 // default to 0, okay to let players select their own tile
                /* $battleManager->field->getFighterLocation($player->combat_id) == $index ? 1 : 0 */
            ?>'
        >
            <span class='tileIndex'><?= $index ?></span>
            <?php
                foreach($tile->fighter_ids as $fighter_id) {
                    renderTileFighter(
                            $battle->getFighter($fighter_id),
                            $fighter_id == $player->combat_id
                    );
                }
            ?>
        </div>
    <?php endforeach; ?>
</div>
<form action="<?= $self_link ?>" method="POST" id="battle_field_form">
    <input type="hidden" id="selected_tile_input" name="selected_tile" value="" />
    <input
        type="submit"
        id="submit"
        name="submit_movement_action"
        value="Submit"
        style='display:none;margin: 2px auto;'
    />
</form>

<script type='text/javascript'>
    const form = document.getElementById('battle_field_form');
    const selectedTileInput = document.getElementById('selected_tile_input');
    const submitButton = document.getElementById('submit');
    const tiles = document.querySelectorAll('.tile');

    /** @var {?Element} selectedTile */
    let selectedTile = null;

    tiles.forEach(tile =>
        tile.addEventListener('click', e => {
            console.log('clicked', tile.id, tile.getAttribute('data-tile-index'));
            if(parseInt(tile.getAttribute('data-player-tile')) === 1) {
                return;
            }

            if(selectedTile != null) {
                selectedTile.classList.remove('selected');
            }
            tile.classList.add('selected');

            selectedTile = tile;
            selectedTileInput.value = tile.getAttribute('data-tile-index');
            submitButton.style.display = 'block';
        })
    )
</script>
