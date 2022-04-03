// @flow strict
import { FighterAvatar } from "./FighterAvatar.js";

import type { FighterType, BattleFieldTileType } from "./battleSchema.js";

type Props = {|
    +player: FighterType,
    +fighters: { [key: string]: FighterType },
    +tiles: $ReadOnlyArray<BattleFieldTileType>,
    +isSelectingTile: boolean,
    +onTileSelect: (tileIndex: number) => void,
|};

export default function BattleField({
    player,
    fighters,
    tiles,
    isSelectingTile,
    onTileSelect
}: Props): React$Node {
    const fightersForIds = (ids: $ReadOnlyArray<string>) => {
        return ids.map(id => fighters[id]).filter(Boolean)
    };

    return (
        <div className={`tilesContainer`}>
            {tiles.map((tile) => (
                <BattleFieldTile
                    key={tile.index}
                    index={tile.index}
                    fighters={fightersForIds(tile.fighterIds)}
                    isSelecting={isSelectingTile && !tile.fighterIds.includes(player.id)}
                    onSelect={() => onTileSelect(tile.index)}
                />
            ))}
        </div>
    )
}

function BattleFieldTile({
    index,
    fighters,
    isSelecting,
    onSelect
}) {
    return (
        <div
            className={`tile ${(isSelecting) ? 'movementActive' : ''}`}
            onClick={onSelect}
        >
            <span className='tileIndex'>{index}</span>
            {fighters.map((fighter, i) => (
                <div key={i} className={`tileFighter ${fighter.isAlly ? 'ally' : 'enemy'}`}>
                    <FighterAvatar
                        fighterName={fighter.name}
                        avatarLink={fighter.avatarLink}
                        maxAvatarSize={20}
                        includeContainer={false}
                    />
                </div>
            ))}
        </div>
    );
}


/*<form action="<?= $self_link ?>" method="POST" id="battle_field_form">
    <input type="hidden" id="selected_tile_input" name="selected_tile" value="" />
    <input
        type="submit"
        id="submit"
        name="submit_movement_action"
        value="Submit"
        style='margin: 2px auto;<?= $battle->isMovementPhase() ? 'display:block;' : 'display:none;' ?>'
    disabled="disabled"
/>
</form>
*/


/*<script type='text/javascript'>
    const form = document.getElementById('battle_field_form');
    const selectedTileInput = document.getElementById('selected_tile_input');
    const submitButton = document.getElementById('submit');
    const tiles = document.querySelectorAll('.tile');

    const isMovementPhase = <?= $battle->isMovementPhase() ? 'true' : 'false' ?>;

    /!** @var {?Element} selectedTile *!/
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
    submitButton.removeAttribute('disabled');
})
    )
</script>*/
