import { FighterAvatar } from "./FighterAvatar.js";
export default function BattleField({
  player,
  fighters,
  tiles,
  fighterLocations,
  selectedJutsu,
  isMovementPhase,
  onTileSelect
}) {
  const [tilesToDisplay, setTilesToDisplay] = React.useState(tiles);
  const [containerSize, setContainerSize] = React.useState(null);
  const containerRef = React.useRef(null);

  const setContainerRef = el => {
    console.log(containerRef.current, el);
    containerRef.current = el;
  };

  React.useEffect(() => {
    if (containerRef.current == null) {
      setContainerSize(containerRef.current);
    } else {
      setContainerSize({
        width: containerRef.current.offsetWidth,
        height: containerRef.current.offsetHeight
      });
    }
  }, [containerRef.current]);
  return /*#__PURE__*/React.createElement("div", {
    className: `tilesContainer`,
    ref: setContainerRef
  }, containerSize != null && /*#__PURE__*/React.createElement(BattleFieldTiles, {
    containerSize: containerSize,
    tiles: tilesToDisplay,
    player: player,
    fighters: fighters,
    fighterLocations: fighterLocations,
    isMovementPhase: isMovementPhase,
    selectedJutsu: selectedJutsu,
    onTileSelect: onTileSelect
  }));
}

function BattleFieldTiles({
  containerSize,
  tiles,
  player,
  fighters,
  fighterLocations,
  isMovementPhase,
  selectedJutsu,
  onTileSelect
}) {
  const [hoveredTile, setHoveredTile] = React.useState(null);
  const tileSize = 70;

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

  const shouldShowAttackTarget = tile => {
    if (selectedJutsu == null) {
      return false;
    }

    if (selectedJutsu.targetType === "tile") {
      return selectedJutsu ? distanceToPlayer(tile.index) <= selectedJutsu.range : false;
    } else if (selectedJutsu.targetType === "fighter_id") {
      return true;
    } else if (selectedJutsu.targetType === "direction") {
      if (tile.index === playerLocation) {
        return false;
      }

      return selectedJutsu ? distanceToPlayer(tile.index) <= selectedJutsu.range : false;
    }

    return false;
  };

  const shouldShowAttackPreview = tile => {
    if (selectedJutsu == null || selectedJutsu.targetType !== "direction") {
      return false;
    }

    if (hoveredTile == null) {
      return false;
    }

    return distanceToPlayer(tile.index) <= selectedJutsu.range && distanceToPlayer(hoveredTile) <= selectedJutsu.range && (hoveredTile > playerLocation && tile.index > playerLocation || hoveredTile < playerLocation && tile.index < playerLocation);
  };

  const freeWidth = containerSize.width - tileSize * tiles.length;
  const freeHeight = containerSize.height - tileSize;

  if (freeWidth < 0) {
    throw new Error("Rendering too many tiles!");
  }

  if (freeHeight < 0) {
    throw new Error("Container is not tall enough!");
  }

  const leftPadding = freeWidth / (tiles.length + 1); // + 1 so we have an equal margin to the right of the last tile

  const topPadding = freeHeight / 2;
  console.log("left padding", leftPadding);
  return /*#__PURE__*/React.createElement(React.Fragment, null, tiles.map((tile, i) => {
    const cumulativeLeftPadding = leftPadding * (i + 1);
    const cumulativeTileWidth = tileSize * i;
    const tileStyles = {
      width: tileSize,
      height: tileSize,
      top: topPadding,
      left: cumulativeTileWidth + cumulativeLeftPadding
    };
    return /*#__PURE__*/React.createElement("div", {
      className: "tileContainer",
      style: tileStyles,
      key: tile.index
    }, /*#__PURE__*/React.createElement(BattleFieldTile, {
      index: tile.index,
      fighters: fightersForIds(tile.fighterIds),
      canMoveTo: isMovementPhase
      /* && !tile.fighterIds.includes(player.id)*/
      ,
      showAttackTarget: shouldShowAttackTarget(tile),
      showAttackPreview: shouldShowAttackPreview(tile),
      onSelect: () => onTileSelect(tile.index),
      onMouseEnter: () => setHoveredTile(tile.index),
      onMouseLeave: () => setHoveredTile(null)
    }));
  }));
}

function BattleFieldTile({
  index,
  fighters,
  canMoveTo,
  showAttackTarget,
  showAttackPreview,
  onSelect,
  onMouseEnter,
  onMouseLeave
}) {
  const classes = ['tile'];

  if (canMoveTo) {
    classes.push('movementTarget');
  }

  if (showAttackTarget) {
    classes.push('attackTarget');
  }

  if (showAttackPreview) {
    classes.push('attackPreview');
  }

  return /*#__PURE__*/React.createElement("div", {
    className: classes.join(' '),
    onClick: canMoveTo || showAttackTarget ? onSelect : null,
    onMouseEnter: onMouseEnter,
    onMouseLeave: onMouseLeave
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