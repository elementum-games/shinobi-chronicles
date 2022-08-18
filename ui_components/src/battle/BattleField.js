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
        console.log(containerRef.current, el);
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

    return (
        <div className={`tilesContainer`} ref={setContainerRef}>
            {containerSize != null &&
                <BattleFieldTiles
                    containerSize={containerSize}
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

    const freeWidth = containerSize.width - (tileSize * tiles.length);
    const freeHeight = containerSize.height - tileSize;
    if(freeWidth < 0) {
        throw new Error("Rendering too many tiles!");
    }
    if(freeHeight < 0) {
        throw new Error("Container is not tall enough!");
    }

    const leftPadding = freeWidth / (tiles.length + 1); // + 1 so we have an equal margin to the right of the last tile
    const topPadding = freeHeight / 2;
    console.log("left padding", leftPadding);


    return <React.Fragment>
        {tiles.map((tile, i) => {
            const cumulativeLeftPadding = leftPadding * (i + 1);
            const cumulativeTileWidth = tileSize * i;

            const tileStyles = {
                width: tileSize,
                height: tileSize,
                top: topPadding,
                left: cumulativeTileWidth + cumulativeLeftPadding,
            };

            return <div className="tileContainer" style={tileStyles} key={tile.index}>
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
    </React.Fragment>;
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

    return (
        <div
            className={classes.join(' ')}
            onClick={(canMoveTo || showAttackTarget) ? onSelect : null}
            onMouseEnter={onMouseEnter}
            onMouseLeave={onMouseLeave}
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

