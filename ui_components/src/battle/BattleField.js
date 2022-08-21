// @flow strict
import { FighterAvatar } from "./FighterAvatar.js";

import type { FighterType, BattleFieldTileType, JutsuType } from "./battleSchema.js";

type BoundingRect = {|
    +top: number,
    +left: number,
    +width: number,
    +height: number,
|};

type Props = {|
    +player: FighterType,
    +fighters: { [ key: string ]: FighterType },
    +fighterLocations: { [ key: string ]: number },
    +tiles: $ReadOnlyArray<BattleFieldTileType>,
    +selectedJutsu: ?JutsuType,
    +isMovementPhase: boolean,
    +onTileSelect: (tileIndex: number) => void,
|};

export default function BattleField({
    player,
    fighters,
    tiles,
    fighterLocations,
    selectedJutsu,
    isMovementPhase,
    onTileSelect
}: Props): React$Node {
    debug('--- render(BattleField) ---');
    const [containerSize, setContainerSize] = React.useState(null);

    const containerRef = React.useRef(null);
    const setContainerRef = (el) => {
        if(el == null || containerRef.current === el) {
            return;
        }

        containerRef.current = el;
        setContainerSize({
            width: containerRef.current.offsetWidth,
            height: containerRef.current.offsetHeight
        });
    };

    const tileSize = 80;

    return (
        <div className={`battleFieldContainer`} style={{ height: tileSize }} ref={setContainerRef}>
            {containerSize != null &&
                <BattleFieldContent
                    containerSize={containerSize}
                    tileSize={tileSize}
                    tiles={tiles}
                    player={player}
                    fighters={fighters}
                    fighterLocations={fighterLocations}
                    isMovementPhase={isMovementPhase}
                    selectedJutsu={selectedJutsu}
                    onTileSelect={onTileSelect}
                />
            }
        </div>
    )
}


type BattleFieldContentProps = {|
    +containerSize: {|
        +width: number,
        +height: number,
    |},
    +tileSize: number,
    +tiles: $ReadOnlyArray<BattleFieldTileType>,
    +player: FighterType,
    +fighters: { [ key: string ]: FighterType },
    +fighterLocations: { [ key: string ]: number },
    +isMovementPhase: boolean,
    +selectedJutsu: ?JutsuType,
    +onTileSelect: (tileIndex: number) => void,
|};
function BattleFieldContent({
    containerSize,
    tileSize,
    tiles,
    player,
    fighters,
    fighterLocations,
    isMovementPhase,
    selectedJutsu,
    onTileSelect
}: BattleFieldContentProps) {
    const tilesContainerEdgeBuffer = tileSize * 5;
    const fighterMovementMaxDurationMs = 1000;

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
    const [rightmostVisibleTileIndex, setRightmostVisibleTileIndex] = React.useState(tiles.slice(-1)[0].index);

    debug('----------------');

    debug('leftmostVisibleTileIndex', leftmostVisibleTileIndex);
    debug('rightmostVisibleTileIndex', rightmostVisibleTileIndex);

    /* Transition tile display - Example:
            Starting tiles
            | 3 4 5 6 7 |

            Add new tiles
        1 2 | 3 4 5 6 7 |

            Move(animated) to new visible set
            | 1 2 3 4 5 | 6 7

            Remove old tiles
            | 1 2 3 4 5 |
     */
    React.useEffect(() => {
        const latestTilesToDisplay = latestTilesToDisplayRef.current;
        debug('transition tiles', latestTilesToDisplay, tiles);
        if(JSON.stringify(tiles) === JSON.stringify(latestTilesToDisplay)) {
            debug('tiles and tilesToDisplay are the same');
            return;
        }

        let leftIndex = latestTilesToDisplay[0].index;
        let rightIndex = latestTilesToDisplay.slice(-1)[0].index;

        let newLeftIndex = tiles[0].index;
        let newRightIndex = tiles.slice(-1)[0].index;

        // Add new tiles
        if(newLeftIndex < leftIndex) {
            let newTiles = tiles.slice(0, newLeftIndex - leftIndex);
            debug('newTiles (left)', newTiles);

            setTilesToDisplay([
                ...newTiles,
                ...latestTilesToDisplay
            ]);
        }
        else if(newRightIndex > rightIndex) {
            const numNewTiles = newRightIndex - rightIndex;
            let newTiles = tiles.slice(tiles.length - numNewTiles);
            debug('newTiles (right)', newTiles);

            setTilesToDisplay([
                ...latestTilesToDisplay,
                ...newTiles
            ]);
        }
        else {
            debug('indexes are the same');
            setTilesToDisplay(tiles);
            return;
        }

        // Move to new visible set
        setTimeout(() => {
            debug('Move to new visible set');
            setLeftmostVisibleTileIndex(newLeftIndex);
            setRightmostVisibleTileIndex(newRightIndex);

        }, fighterMovementMaxDurationMs);

        // Remove old tiles
        setTimeout(() => {
            debug('Remove old tiles');
            // setTilesToDisplay(tiles);
        }, fighterMovementMaxDurationMs + 500)
    }, [tiles]);

    const numVisibleTiles = (rightmostVisibleTileIndex - leftmostVisibleTileIndex) + 1;

    const freeWidth = containerSize.width - (tileSize * numVisibleTiles);
    const freeHeight = containerSize.height - tileSize;
    if(freeWidth < 0) {
        throw new Error("Rendering too many tiles!");
    }
    if(freeHeight < 0) {
        throw new Error("Container is not tall enough!");
    }

    const tileHorizontalGap = freeWidth / (numVisibleTiles + 1); // + 1 so we have an equal margin to the right of the last tile
    const offsetPerTile = tileHorizontalGap + tileSize;

    function getBoundingRectForTile(tileIndex): BoundingRect {
        /* Display index is based on which tiles are displayed, while tile index is the absolute index
         if the fighters have moved to the right a few times, we might display tile indexes 4-9 like this:
         4 5 6 7 8 9

         But we need to do our calculations relative to what's visible, so tile index 4 here is tile display index 0
       */
        const tileDisplayIndex = tileIndex - leftmostVisibleTileIndex;
        const leftOffset = (offsetPerTile * tileDisplayIndex) + tileHorizontalGap;

        return {
            width: tileSize,
            height: tileSize,
            top: 0,
            left: tilesContainerEdgeBuffer + leftOffset,
        };
    }

    debug('leftmostVisibleTileIndex', leftmostVisibleTileIndex);
    const leftmostVisibleTilePosition = getBoundingRectForTile(leftmostVisibleTileIndex);
    debug('leftmostVisibleTilePosition', leftmostVisibleTilePosition)

    //
    const leftCameraOffset = leftmostVisibleTilePosition.left - tileHorizontalGap;
    const leftCameraPosition = (tilesContainerEdgeBuffer * -1);
    debug('leftCameraPosition', leftCameraPosition);

    return <div
        className="tilesContainer"
        style={{
            width: containerSize.width + (tilesContainerEdgeBuffer * 2),
            transform: `translateX(${leftCameraPosition}px)`
        }}
    >
        <BattleFieldTiles
            tilesToDisplay={tilesToDisplay}
            getBoundingRectForTile={getBoundingRectForTile}
            selectedJutsu={selectedJutsu}
            playerLocation={fighterLocations[ player.id ]}
            isMovementPhase={isMovementPhase}
            onTileSelect={onTileSelect}
        />
        <BattleFieldFighters
            tileSize={tileSize}
            fighters={fighters}
            fighterLocations={fighterLocations}
            getBoundingRectForTile={getBoundingRectForTile}
        />
    </div>;
}

type BattleFieldTilesProps = {|
    +tilesToDisplay: $ReadOnlyArray<BattleFieldTileType>,
    +getBoundingRectForTile: (tileIndex: number) => BoundingRect,
    +selectedJutsu: ?JutsuType,
    +playerLocation: number,
    +isMovementPhase: boolean,
    +onTileSelect: (tileIndex: number) => void,
|};
function BattleFieldTiles({
    tilesToDisplay,
    getBoundingRectForTile,
    selectedJutsu,
    playerLocation,
    isMovementPhase,
    onTileSelect
}: BattleFieldTilesProps) {
    const [hoveredTile, setHoveredTile] = React.useState(null);

    function distanceToPlayer(tileIndex: number) {
        return Math.abs(tileIndex - playerLocation);
    }

    function shouldShowAttackTarget(tile) {
        if (selectedJutsu == null) {
            return false;
        }

        if (selectedJutsu.targetType === "tile") {
            return selectedJutsu
                ? distanceToPlayer(tile.index) <= selectedJutsu.range
                : false
        }
        else if (selectedJutsu.targetType === "fighter_id") {
            return true;
        }
        else if (selectedJutsu.targetType === "direction") {
            if (tile.index === playerLocation) {
                return false;
            }

            return selectedJutsu
                ? distanceToPlayer(tile.index) <= selectedJutsu.range
                : false
        }

        return false;
    }
    function shouldShowAttackPreview(tile: BattleFieldTileType) {
        if (selectedJutsu == null || selectedJutsu.targetType !== "direction") {
            return false;
        }
        if (hoveredTile == null) {
            return false;
        }

        return distanceToPlayer(tile.index) <= selectedJutsu.range &&
            distanceToPlayer(hoveredTile) <= selectedJutsu.range &&
            (
                (hoveredTile > playerLocation && tile.index > playerLocation)
                ||
                (hoveredTile < playerLocation && tile.index < playerLocation)
            );
    }

    return (
        <React.Fragment>
            {tilesToDisplay.map((tile) => {
                const tileBoundingRect = getBoundingRectForTile(tile.index);

                return <div className="tileContainer" style={tileBoundingRect} key={`tile:${tile.index}`}>
                    <BattleFieldTile
                        index={tile.index}
                        canMoveTo={isMovementPhase/* && !tile.fighterIds.includes(player.id)*/}
                        showAttackTarget={shouldShowAttackTarget(tile)}
                        showAttackPreview={shouldShowAttackPreview(tile)}
                        onSelect={() => onTileSelect(tile.index)}
                        onMouseEnter={() => setHoveredTile(tile.index)}
                        onMouseLeave={() => setHoveredTile(null)}
                    />
                </div>;
            })}
        </React.Fragment>
    );
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

    return (
        <div
            className={classes.join(' ')}
            onClick={(canMoveTo || showAttackTarget) ? onSelect : null}
            onMouseEnter={onMouseEnter}
            onMouseLeave={onMouseLeave}
        >
            <span className='tileIndex'>{index}</span>
        </div>
    );
}

type BattleFieldFightersProps = {|
    +tileSize: number,
    +fighters: { [key: string]: FighterType},
    +fighterLocations: { [ key: string ]: number },
    +getBoundingRectForTile: (tileIndex: number) => BoundingRect,
|};
function BattleFieldFighters({
    tileSize,
    fighters,
    fighterLocations,
    getBoundingRectForTile
}: BattleFieldFightersProps) {
    const fighterDisplaySize = 25;

    const fightersForIds = (ids: $ReadOnlyArray<string>) => {
        return ids.map(id => fighters[ id ]).filter(Boolean)
    };

    type Position = {| +top: number, +left: number |};
    function distributeFightersOnTile(fightersOnTile: $ReadOnlyArray<FighterType>): { [key: string]: Position} {
        const allyFighters = fightersOnTile.filter(fighter => fighter.isAlly);
        const enemyFighters = fightersOnTile.filter(fighter => !fighter.isAlly);

        const fighterLocationsOnTile = {};

        const spacingWhenTwoFighters = (tileSize - (fighterDisplaySize * 2)) / 3;
        const spacingWhenOneFighter = (tileSize - fighterDisplaySize) / 2;

        // Two teams, spread horizontally
        let allyLeftPosition, enemyLeftPosition;
        if(allyFighters.length > 0 && enemyFighters.length > 0) {
            allyLeftPosition = spacingWhenTwoFighters;
            enemyLeftPosition = spacingWhenTwoFighters + fighterDisplaySize + spacingWhenTwoFighters;
        }
        // One team, center horizontally
        else {
            allyLeftPosition = spacingWhenOneFighter;
            enemyLeftPosition = spacingWhenOneFighter;
        }

        if(allyFighters.length === 2) {
            fighterLocationsOnTile[allyFighters[0].id] = {
                top: spacingWhenTwoFighters,
                left: allyLeftPosition
            }
            fighterLocationsOnTile[allyFighters[1].id] = {
                top: spacingWhenTwoFighters + fighterDisplaySize + spacingWhenTwoFighters,
                left: allyLeftPosition
            }
        }
        else if(allyFighters.length === 1) {
            fighterLocationsOnTile[allyFighters[0].id] = {
                top: spacingWhenOneFighter,
                left: allyLeftPosition
            }
        }

        if(enemyFighters.length === 2) {
            fighterLocationsOnTile[enemyFighters[0].id] = {
                top: spacingWhenTwoFighters,
                left: enemyLeftPosition
            }
            fighterLocationsOnTile[enemyFighters[1].id] = {
                top: spacingWhenTwoFighters + fighterDisplaySize + spacingWhenTwoFighters,
                left: enemyLeftPosition
            }
        }
        else if(enemyFighters.length === 1) {
            fighterLocationsOnTile[enemyFighters[0].id] = {
                top: spacingWhenOneFighter,
                left: enemyLeftPosition
            }
        }

        return fighterLocationsOnTile;
    }

    const fighterIdsByTile = {};
    Object.keys(fighterLocations).forEach(fighterId => {
        const tileIndex = fighterLocations[fighterId];

        if(fighterIdsByTile[tileIndex] == null) {
            fighterIdsByTile[tileIndex] = [];
        }

        fighterIdsByTile[tileIndex].push(fighterId);
    });

    const fighterPositions = {};
    Object.keys(fighterIdsByTile).forEach((tileIndexStr) => {
        const tileIndex = parseInt(tileIndexStr);

        const fightersOnTile = fightersForIds(fighterIdsByTile[tileIndex]);
        if(fightersOnTile.length > 4) {
            throw new Error(`Too many fighters on tile ${tileIndex}!`);
        }

        const tileBoundingRect = getBoundingRectForTile(tileIndex);

        const fighterPositionsOnTile = distributeFightersOnTile(fightersOnTile);

        fightersOnTile.forEach(fighter => {
            fighterPositions[fighter.id] = {
                top: fighterPositionsOnTile[fighter.id].top + tileBoundingRect.top,
                left: fighterPositionsOnTile[fighter.id].left + tileBoundingRect.left,
                width: fighterDisplaySize,
                height: fighterDisplaySize,
            }
        })
    })

    // We want to ensure fighters always render in the same order so each fighter has a stable DOM element. This means
    // when they move, the CSS transition will smoothly move them to the new position instead of them switching to a new
    // DOM element and snapping to the new location
    const fighterIds = Object.keys(fighters).sort();

    return (
        <React.Fragment>
            {fighterIds.map(fighterId => {
                const fighter = fighters[fighterId];

                return (
                    <div
                        key={`fighter:${fighterId}`}
                        className={`tileFighter ${fighter.isAlly ? 'ally' : 'enemy'}`}
                        style={fighterPositions[fighter.id]}
                    >
                        <FighterAvatar
                            displaySize={fighterDisplaySize}
                            fighterName={fighter.name}
                            avatarLink={fighter.avatarLink}
                            maxAvatarSize={20}
                            includeContainer={false}
                        />
                    </div>
                );
            })}
        </React.Fragment>
    );
}

function debug(...contents) {
    //if(window.debug) {
    console.log(...contents);
    // }
}
