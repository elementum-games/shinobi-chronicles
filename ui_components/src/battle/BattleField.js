// @flow strict
import { FighterAvatar } from "./FighterAvatar.js";

import type { FighterType, BattleFieldTileType, JutsuType } from "./battleSchema.js";

type Props = {|
    +player: FighterType,
    +fighters: { [key: string]: FighterType },
    +fighterLocations: { [key: string]: number },
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
    const [hoveredTile, setHoveredTile] = React.useState(null);

    const fightersForIds = (ids: $ReadOnlyArray<string>) => {
        return ids.map(id => fighters[id]).filter(Boolean)
    };

    const playerLocation = fighterLocations[player.id];
    if(playerLocation == null) {
        throw new Error("Invalid player location!");
    }

    const distanceToPlayer = (tileIndex: number) => {
        return Math.abs(tileIndex - playerLocation);
    };

    const shouldShowAttackTarget = (tile) => {
        if(selectedJutsu == null) {
            return false;
        }

        if(selectedJutsu.targetType === "tile") {
            return selectedJutsu
                ? distanceToPlayer(tile.index) <= selectedJutsu.range
                : false
        }
        else if(selectedJutsu.targetType === "fighter_id") {
            return true;
        }
        else if(selectedJutsu.targetType === "direction") {
            if(tile.index === playerLocation) {
                return false;
            }

            return selectedJutsu
                ? distanceToPlayer(tile.index) <= selectedJutsu.range
                : false
        }

        return false;
    };
    const shouldShowAttackPreview = (tile) => {
        if(selectedJutsu == null || selectedJutsu.targetType !== "direction") {
            return false;
        }
        if(hoveredTile == null) {
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

    return (
        <div className={`tilesContainer`}>
            {tiles.map((tile) => (
                <BattleFieldTile
                    key={tile.index}
                    index={tile.index}
                    fighters={fightersForIds(tile.fighterIds)}
                    canMoveTo={isMovementPhase/* && !tile.fighterIds.includes(player.id)*/}
                    showAttackTarget={shouldShowAttackTarget(tile)}
                    showAttackPreview={shouldShowAttackPreview(tile)}
                    onSelect={() => onTileSelect(tile.index)}
                    onMouseEnter={() => setHoveredTile(tile.index)}
                    onMouseLeave={() => setHoveredTile(null)}
                />
            ))}
        </div>
    )
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
    if(canMoveTo) {
        classes.push('movementTarget');
    }
    if(showAttackTarget) {
        classes.push('attackTarget');
    }
    if(showAttackPreview) {
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

