// @flow strict
import { FighterAvatar } from "./FighterAvatar.js";

import type { FighterType, BattleFieldTileType, JutsuType } from "./battleSchema.js";

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
    const [tilesToDisplay, setTilesToDisplay] = React.useState(tiles);

    const [containerSize, setContainerSize] = React.useState(null);

    const containerRef = React.useRef(null);
    const setContainerRef = (el) => {
        containerRef.current = el;
    };

    React.useEffect(() => {
        if(containerRef.current == null) {
            setContainerSize(containerRef.current);
        }
        else {
            setContainerSize({
                width: containerRef.current.offsetWidth,
                height: containerRef.current.offsetHeight
            });
        }
    }, [containerRef.current])

    const tileSize = 80;

    return (
        <div className={`tilesContainer`} style={{ height: tileSize }} ref={setContainerRef}>
            {containerSize != null &&
                <BattleFieldTiles
                    containerSize={containerSize}
                    tileSize={tileSize}
                    tiles={tilesToDisplay}
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


type BattleFieldTilesProps = {|
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
function BattleFieldTiles({
    containerSize,
    tileSize,
    tiles,
    player,
    fighters,
    fighterLocations,
    isMovementPhase,
    selectedJutsu,
    onTileSelect
}) {
    const [hoveredTile, setHoveredTile] = React.useState(null);

    const fightersForIds = (ids: $ReadOnlyArray<string>) => {
        return ids.map(id => fighters[ id ]).filter(Boolean)
    };

    const playerLocation = fighterLocations[ player.id ];
    if (playerLocation == null) {
        throw new Error("Invalid player location!");
    }

    const distanceToPlayer = (tileIndex: number) => {
        return Math.abs(tileIndex - playerLocation);
    };
    const shouldShowAttackTarget = (tile) => {
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
    };
    const shouldShowAttackPreview = (tile) => {
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
    };

    // Render magic
    const fighterDisplaySize = 25;

    const freeWidth = containerSize.width - (tileSize * tiles.length);
    const freeHeight = containerSize.height - tileSize;
    if(freeWidth < 0) {
        throw new Error("Rendering too many tiles!");
    }
    if(freeHeight < 0) {
        throw new Error("Container is not tall enough!");
    }

    const tileHorizontalGap = freeWidth / (tiles.length + 1); // + 1 so we have an equal margin to the right of the last tile
    const tileVerticalGap = freeHeight / 2;

    function getBoundingRectForTile(tileIndex) {
        /* Display index is based on which tiles are displayed, while tile index is the absolute index
         if the fighters have moved to the right a few times, we might display tile indexes 4-9 like this:
         4 5 6 7 8 9

         But we need to do our calculations relative to what's visible, so tile index 4 here is tile display index 0
       */
        const tileDisplayIndex = tiles.findIndex(t => t.index === tileIndex);
        if(tileDisplayIndex === -1) {
            throw new Error(`Invalid tile! ${tileIndex}`);
        }

        const cumulativeLeftPadding = tileHorizontalGap * (tileDisplayIndex + 1);
        const cumulativeTileWidth = tileSize * tileDisplayIndex;

        return {
            width: tileSize,
            height: tileSize,
            top: tileVerticalGap,
            left: cumulativeTileWidth + cumulativeLeftPadding,
        };
    }

    const fighterIdsByTile = {};
    Object.keys(fighterLocations).forEach(fighterId => {
        const tileIndex = fighterLocations[fighterId];

        if(fighterIdsByTile[tileIndex] == null) {
            fighterIdsByTile[tileIndex] = [];
        }

        fighterIdsByTile[tileIndex].push(fighterId);
    });

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

    return <React.Fragment>
        {tiles.map((tile) => {
            const tileBoundingRect = getBoundingRectForTile(tile.index);

            return <div className="tileContainer" style={tileBoundingRect} key={tile.index}>
                <BattleFieldTile
                    index={tile.index}
                    fighters={fightersForIds(tile.fighterIds)}
                    canMoveTo={isMovementPhase/* && !tile.fighterIds.includes(player.id)*/}
                    showAttackTarget={shouldShowAttackTarget(tile)}
                    showAttackPreview={shouldShowAttackPreview(tile)}
                    onSelect={() => onTileSelect(tile.index)}
                    onMouseEnter={() => setHoveredTile(tile.index)}
                    onMouseLeave={() => setHoveredTile(null)}
                />
            </div>;
        })}
        {Object.keys(fighterIdsByTile).map((tileIndexStr) => {
            const tileIndex = parseInt(tileIndexStr);

            const fighters = fightersForIds(fighterIdsByTile[tileIndex]);
            if(fighters.length > 4) {
                throw new Error(`Too many fighters on tile ${tileIndex}!`);
            }

            const tileBoundingRect = getBoundingRectForTile(tileIndex);

            const fighterPositionsOnTile = distributeFightersOnTile(fighters);

            return fighters.map(fighter => (
                <div
                    key={fighter.id}
                    className={`tileFighter ${fighter.isAlly ? 'ally' : 'enemy'}`}
                    style={{
                        top: fighterPositionsOnTile[fighter.id].top + tileBoundingRect.top,
                        left: fighterPositionsOnTile[fighter.id].left + tileBoundingRect.left,
                        width: fighterDisplaySize,
                        height: fighterDisplaySize,
                    }}
                >
                    <FighterAvatar
                        displaySize={fighterDisplaySize}
                        fighterName={fighter.name}
                        avatarLink={fighter.avatarLink}
                        maxAvatarSize={20}
                        includeContainer={false}
                    />
                </div>
            ));
        })}
    </React.Fragment>;
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

