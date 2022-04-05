import { FighterAvatar } from "./FighterAvatar.js";
export default function BattleField({
  player,
  fighters,
  tiles,
  fighterLocations,
  jutsuToSelectTarget,
  isMovementPhase,
  onTileSelect
}) {
  const fightersForIds = ids => {
    return ids.map(id => fighters[id]).filter(Boolean);
  };

  const playerLocation = fighterLocations[player.id];

  if (playerLocation == null) {
    throw new Error("Invalid player location!");
  }

  const distanceToPlayer = tileIndex => {
    return Math.abs(tileIndex - playerLocation);
  };

  return /*#__PURE__*/React.createElement("div", {
    className: `tilesContainer`
  }, tiles.map(tile => /*#__PURE__*/React.createElement(BattleFieldTile, {
    key: tile.index,
    index: tile.index,
    fighters: fightersForIds(tile.fighterIds),
    canMoveTo: isMovementPhase && !tile.fighterIds.includes(player.id),
    canAttack: jutsuToSelectTarget ? distanceToPlayer(tile.index) <= jutsuToSelectTarget.range : false,
    onSelect: () => onTileSelect(tile.index)
  })));
}

function BattleFieldTile({
  index,
  fighters,
  canMoveTo,
  canAttack,
  onSelect
}) {
  const classes = ['tile'];

  if (canMoveTo) {
    classes.push('movementTarget');
  }

  if (canAttack) {
    classes.push('attackTarget');
  }

  return /*#__PURE__*/React.createElement("div", {
    className: classes.join(' '),
    onClick: canMoveTo || canAttack ? onSelect : null
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