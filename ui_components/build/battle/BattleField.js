import { FighterAvatar } from "./FighterAvatar.js";
import { PlayAttackActions } from "./PlayAttackActions.js";
const transitionTimeMs = 600;
export default function BattleField({
  player,
  fighters,
  tiles,
  fighterLocations,
  selectedJutsu,
  isMovementPhase,
  lastTurnLog,
  onTileSelect
}) {
  debug('--- render(BattleField) ---');
  const [containerSize, setContainerSize] = React.useState(null);
  const containerRef = React.useRef(null);

  const setContainerRef = el => {
    if (el == null || containerRef.current === el) {
      return;
    }

    containerRef.current = el;
    setContainerSize({
      width: containerRef.current.offsetWidth,
      height: containerRef.current.offsetHeight
    });
  };

  const tileSize = 80;
  return /*#__PURE__*/React.createElement("div", {
    className: `battleFieldContainer`,
    style: {
      height: tileSize + 10
    },
    ref: setContainerRef
  }, containerSize != null && /*#__PURE__*/React.createElement(BattleFieldContent, {
    containerSize: containerSize,
    tileSize: tileSize,
    tiles: tiles,
    player: player,
    fighters: fighters,
    fighterLocations: fighterLocations,
    isMovementPhase: isMovementPhase,
    lastTurnLog: lastTurnLog,
    selectedJutsu: selectedJutsu,
    onTileSelect: onTileSelect
  }));
}

function BattleFieldContent({
  containerSize,
  tileSize,
  tiles,
  player,
  fighters,
  fighterLocations,
  isMovementPhase,
  lastTurnLog,
  selectedJutsu,
  onTileSelect
}) {
  const tilesContainerEdgeBuffer = tileSize * 5;
  /*
   Why a ref for tilesToDisplay? We need to use latest tilesToDisplay value inside the tile transition hook without
   re-triggering the transition when tilesToDisplay gets set to an intermediate state during the transition process.
   */

  const [tilesToDisplay, _setTilesToDisplay] = React.useState(tiles);
  const latestTilesToDisplayRef = React.useRef(tilesToDisplay);

  function setTilesToDisplay(newTilesToDisplay) {
    _setTilesToDisplay(newTilesToDisplay);

    latestTilesToDisplayRef.current = newTilesToDisplay;
  }

  const [leftmostVisibleTileIndex, setLeftmostVisibleTileIndex] = React.useState(tiles[0].index);
  const [numVisibleTiles, setNumVisibleTiles] = React.useState(tiles.length);
  const [manualCameraTilesOffset, setManualCameraTilesOffset] = React.useState(0);
  const [disableTransitions, setDisableTransitions] = React.useState(false);
  debug('----------------');
  /* Transition tile display
        Example 1:
          Starting tiles
          | 3 4 5 6 7 |
            Add new tiles
      1 2 | 3 4 5 6 7 |
            Animate container over
          1 2 | 3 4 5 6 7 |
            Move(animated) to new visible set
          | 1 2 3 4 5 | 6 7
            Remove old tiles
          | 1 2 3 4 5 |
        Example 2:
          Starting tiles
          | 3 4 5 6 7 |
            Add new tiles
      1 2 | 3 4 5 6 7 |
            Animate container over
          1 2 | 3 4 5 6 7 |
            Disable transitions and update to tiles
          | 1 2 3 4 5 6 |
   */

  React.useEffect(() => {
    const latestTilesToDisplay = latestTilesToDisplayRef.current;
    debug('transition tiles', latestTilesToDisplay, tiles);

    if (JSON.stringify(tiles) === JSON.stringify(latestTilesToDisplay)) {
      debug('tiles and tilesToDisplay are the same');
      return;
    }

    let leftIndex = latestTilesToDisplay[0].index;
    let rightIndex = latestTilesToDisplay.slice(-1)[0].index;
    let newLeftIndex = tiles[0].index;
    let newRightIndex = tiles.slice(-1)[0].index; // Add new tiles

    if (newLeftIndex < leftIndex) {
      let newTiles = tiles.slice(0, leftIndex - newLeftIndex);
      debug('newTiles (left)', newTiles);
      setTilesToDisplay([...newTiles, ...latestTilesToDisplay]);
    } else if (newRightIndex > rightIndex) {
      const numNewTiles = newRightIndex - rightIndex;
      let newTiles = tiles.slice(tiles.length - numNewTiles);
      debug('newTiles (right)', newTiles);
      setTilesToDisplay([...latestTilesToDisplay, ...newTiles]);
    } else {
      debug('indexes are the same');
      setTilesToDisplay(tiles);
      return;
    } // Animated scroll container to show new tiles


    const scrollContainerDelay = transitionTimeMs + 100;
    setTimeout(() => {
      debug('Scroll container to new tiles', leftIndex, newLeftIndex);
      setManualCameraTilesOffset(newLeftIndex - leftIndex);
      setNumVisibleTiles(tiles.length);
    }, scrollContainerDelay); // Turn off transitions and snap battlefield to new state

    setTimeout(() => {
      debug('Switch visible tiles to new visible set', leftIndex, newLeftIndex);
      setDisableTransitions(true);
      requestAnimationFrame(() => {
        setManualCameraTilesOffset(0);
        setLeftmostVisibleTileIndex(newLeftIndex);
        setTilesToDisplay(tiles);
        requestAnimationFrame(() => {
          setDisableTransitions(false);
        });
      });
    }, scrollContainerDelay + transitionTimeMs + 100);
  }, [tiles]);
  const freeWidth = containerSize.width - tileSize * numVisibleTiles;
  const freeHeight = containerSize.height - tileSize;

  if (freeWidth < 0) {
    throw new Error("Rendering too many tiles!");
  }

  if (freeHeight < 0) {
    throw new Error("Container is not tall enough!");
  }

  const tileHorizontalGap = freeWidth / (numVisibleTiles + 1); // + 1 so we have an equal margin to the right of the last tile

  const offsetPerTile = tileHorizontalGap + tileSize;

  function getBoundingRectForTile(tileIndex) {
    /* Display index is based on which tiles are displayed, while tile index is the absolute index
     if the fighters have moved to the right a few times, we might display tile indexes 4-9 like this:
     4 5 6 7 8 9
       But we need to do our calculations relative to what's visible, so tile index 4 here is tile display index 0
    */
    const tileDisplayIndex = tileIndex - leftmostVisibleTileIndex;
    const leftOffset = offsetPerTile * tileDisplayIndex + tileHorizontalGap;
    return {
      width: tileSize,
      height: tileSize,
      top: 0,
      left: tilesContainerEdgeBuffer + leftOffset
    };
  }

  const offset = manualCameraTilesOffset * offsetPerTile;
  const leftCameraPosition = (tilesContainerEdgeBuffer + offset) * -1;
  return /*#__PURE__*/React.createElement("div", {
    className: "tilesContainer",
    style: {
      width: containerSize.width + tilesContainerEdgeBuffer * 2,
      transform: `translateX(${leftCameraPosition}px)`,
      transition: disableTransitions ? "" : `transform ${transitionTimeMs}ms ease-in-out`
    }
  }, /*#__PURE__*/React.createElement(BattleFieldTiles, {
    tilesToDisplay: tilesToDisplay,
    getBoundingRectForTile: getBoundingRectForTile,
    selectedJutsu: selectedJutsu,
    playerLocation: fighterLocations[player.id],
    isMovementPhase: isMovementPhase,
    disableTransitions: disableTransitions,
    onTileSelect: onTileSelect
  }), /*#__PURE__*/React.createElement(BattleFieldFighters, {
    tileSize: tileSize,
    fighters: fighters,
    fighterLocations: fighterLocations,
    disableTransitions: disableTransitions,
    getBoundingRectForTile: getBoundingRectForTile
  }), /*#__PURE__*/React.createElement(PlayAttackActions, {
    lastTurnLog: lastTurnLog,
    tileSize: tileSize,
    fighterLocations: fighterLocations,
    getBoundingRectForTile: getBoundingRectForTile
  }));
}

function BattleFieldTiles({
  tilesToDisplay,
  getBoundingRectForTile,
  selectedJutsu,
  playerLocation,
  isMovementPhase,
  disableTransitions,
  onTileSelect
}) {
  const [hoveredTile, setHoveredTile] = React.useState(null);

  function distanceToPlayer(tileIndex) {
    return Math.abs(tileIndex - playerLocation);
  }

  function shouldShowAttackTarget(tile) {
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
  }

  function shouldShowAttackPreview(tile) {
    if (selectedJutsu == null || selectedJutsu.targetType !== "direction") {
      return false;
    }

    if (hoveredTile == null) {
      return false;
    }

    return distanceToPlayer(tile.index) <= selectedJutsu.range && distanceToPlayer(hoveredTile) <= selectedJutsu.range && (hoveredTile > playerLocation && tile.index > playerLocation || hoveredTile < playerLocation && tile.index < playerLocation);
  }

  return /*#__PURE__*/React.createElement(React.Fragment, null, tilesToDisplay.map(tile => {
    const tileBoundingRect = getBoundingRectForTile(tile.index);
    return /*#__PURE__*/React.createElement("div", {
      key: `tile:${tile.index}`,
      className: "tileContainer",
      style: {
        top: tileBoundingRect.top,
        left: tileBoundingRect.left,
        width: tileBoundingRect.width,
        height: tileBoundingRect.height,
        transition: disableTransitions ? "" : `left ${transitionTimeMs}ms linear`
      }
    }, /*#__PURE__*/React.createElement(BattleFieldTile, {
      index: tile.index,
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
  }, index));
}

function BattleFieldFighters({
  tileSize,
  fighters,
  fighterLocations,
  disableTransitions,
  getBoundingRectForTile
}) {
  const fighterDisplaySize = 30;

  const fightersForIds = ids => {
    return ids.map(id => fighters[id]).filter(Boolean);
  };

  function distributeFightersOnTile(fightersOnTile) {
    const allyFighters = fightersOnTile.filter(fighter => fighter.isAlly);
    const enemyFighters = fightersOnTile.filter(fighter => !fighter.isAlly);
    const fighterLocationsOnTile = {};
    const spacingWhenTwoFighters = (tileSize - fighterDisplaySize * 2) / 3;
    const spacingWhenOneFighter = (tileSize - fighterDisplaySize) / 2; // Two teams, spread horizontally

    let allyLeftPosition, enemyLeftPosition;

    if (allyFighters.length > 0 && enemyFighters.length > 0) {
      allyLeftPosition = spacingWhenTwoFighters;
      enemyLeftPosition = spacingWhenTwoFighters + fighterDisplaySize + spacingWhenTwoFighters;
    } // One team, center horizontally
    else {
      allyLeftPosition = spacingWhenOneFighter;
      enemyLeftPosition = spacingWhenOneFighter;
    }

    if (allyFighters.length === 2) {
      fighterLocationsOnTile[allyFighters[0].id] = {
        top: spacingWhenTwoFighters,
        left: allyLeftPosition
      };
      fighterLocationsOnTile[allyFighters[1].id] = {
        top: spacingWhenTwoFighters + fighterDisplaySize + spacingWhenTwoFighters,
        left: allyLeftPosition
      };
    } else if (allyFighters.length === 1) {
      fighterLocationsOnTile[allyFighters[0].id] = {
        top: spacingWhenOneFighter,
        left: allyLeftPosition
      };
    }

    if (enemyFighters.length === 2) {
      fighterLocationsOnTile[enemyFighters[0].id] = {
        top: spacingWhenTwoFighters,
        left: enemyLeftPosition
      };
      fighterLocationsOnTile[enemyFighters[1].id] = {
        top: spacingWhenTwoFighters + fighterDisplaySize + spacingWhenTwoFighters,
        left: enemyLeftPosition
      };
    } else if (enemyFighters.length === 1) {
      fighterLocationsOnTile[enemyFighters[0].id] = {
        top: spacingWhenOneFighter,
        left: enemyLeftPosition
      };
    }

    return fighterLocationsOnTile;
  }

  const fighterIdsByTile = {};
  Object.keys(fighterLocations).forEach(fighterId => {
    const tileIndex = fighterLocations[fighterId];

    if (fighterIdsByTile[tileIndex] == null) {
      fighterIdsByTile[tileIndex] = [];
    }

    fighterIdsByTile[tileIndex].push(fighterId);
  });
  const fighterPositions = {};
  Object.keys(fighterIdsByTile).forEach(tileIndexStr => {
    const tileIndex = parseInt(tileIndexStr);
    const fightersOnTile = fightersForIds(fighterIdsByTile[tileIndex]);

    if (fightersOnTile.length > 4) {
      throw new Error(`Too many fighters on tile ${tileIndex}!`);
    }

    const tileBoundingRect = getBoundingRectForTile(tileIndex);
    const fighterPositionsOnTile = distributeFightersOnTile(fightersOnTile);
    fightersOnTile.forEach(fighter => {
      fighterPositions[fighter.id] = {
        top: fighterPositionsOnTile[fighter.id].top + tileBoundingRect.top,
        left: fighterPositionsOnTile[fighter.id].left + tileBoundingRect.left,
        width: fighterDisplaySize,
        height: fighterDisplaySize
      };
    });
  }); // We want to ensure fighters always render in the same order so each fighter has a stable DOM element. This means
  // when they move, the CSS transition will smoothly move them to the new position instead of them switching to a new
  // DOM element and snapping to the new location

  const fighterIds = Object.keys(fighters).sort();
  return /*#__PURE__*/React.createElement(React.Fragment, null, fighterIds.map(fighterId => {
    const fighter = fighters[fighterId];
    const transition = disableTransitions ? "" : `top ${transitionTimeMs}ms linear, left ${transitionTimeMs}ms linear`;
    return /*#__PURE__*/React.createElement("div", {
      key: `fighter:${fighterId}`,
      className: `tileFighter ${fighter.isAlly ? 'ally' : 'enemy'}`,
      style: { ...fighterPositions[fighter.id],
        transition: transition
      }
    }, /*#__PURE__*/React.createElement(FighterAvatar, {
      displaySize: fighterDisplaySize,
      fighterName: fighter.name,
      avatarLink: fighter.avatarLink,
      maxAvatarSize: 20,
      includeContainer: false
    }));
  }));
}

function debug(...contents) {
  //if(window.debug) {
  console.log(...contents); // }
}