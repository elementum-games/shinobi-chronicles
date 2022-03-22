import { FighterAvatar } from "./FighterAvatar.js";
export default function BattleField({
  player,
  fighters,
  tiles,
  isSelectingTile,
  onTileSelect
}) {
  const fightersForIds = ids => {
    return ids.map(id => fighters[id]).filter(Boolean);
  };

  return /*#__PURE__*/React.createElement("div", {
    className: `tilesContainer`
  }, tiles.map(tile => /*#__PURE__*/React.createElement(BattleFieldTile, {
    key: tile.index,
    index: tile.index,
    fighters: fightersForIds(tile.fighterIds),
    isPlayerTile: tile.fighterIds.includes(player.id),
    isMovementPhase: isSelectingTile,
    onSelect: () => onTileSelect(tile.index)
  })));
}

function BattleFieldTile({
  index,
  fighters,
  isPlayerTile,
  isMovementPhase,
  onSelect
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: `tile ${isMovementPhase && !isPlayerTile ? 'movementActive' : ''}`,
    onClick: onSelect
  }, /*#__PURE__*/React.createElement("span", {
    className: "tileIndex"
  }, index), fighters.map((fighter, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    className: `tileFighter ${fighter.isAlly ? 'ally' : 'enemy'}`
  }, /*#__PURE__*/React.createElement(FighterAvatar, {
    fighterName: fighter.name,
    avatarLink: fighter.avatarLink,
    maxAvatarSize: 20,
    includeContainer: false
  }))));
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