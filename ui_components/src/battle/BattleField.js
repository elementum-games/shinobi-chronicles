// @flow strict
import { FighterAvatar } from "./FighterAvatar.js";

import type { FighterType, BattleFieldTileType, JutsuType } from "./battleSchema.js";

type Props = {|
    +player: FighterType,
    +fighters: { [key: string]: FighterType },
    +fighterLocations: { [key: string]: number },
    +tiles: $ReadOnlyArray<BattleFieldTileType>,
    +jutsuToSelectTarget: ?JutsuType,
    +isMovementPhase: boolean,
    +onTileSelect: (tileIndex: number) => void,
|};

export default function BattleField({
    player,
    fighters,
    tiles,
    fighterLocations,
    jutsuToSelectTarget,
    isMovementPhase,
    onTileSelect
}: Props): React$Node {
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

    return (
        <div className={`tilesContainer`}>
            {tiles.map((tile) => (
                <BattleFieldTile
                    key={tile.index}
                    index={tile.index}
                    fighters={fightersForIds(tile.fighterIds)}
                    canMoveTo={isMovementPhase/* && !tile.fighterIds.includes(player.id)*/}
                    canAttack={
                        jutsuToSelectTarget
                            ? distanceToPlayer(tile.index) <= jutsuToSelectTarget.range
                            : false
                    }
                    onSelect={() => onTileSelect(tile.index)}
                />
            ))}
        </div>
    )
}

function BattleFieldTile({
    index,
    fighters,
    canMoveTo,
    canAttack,
    onSelect
}) {
    const classes = ['tile'];
    if(canMoveTo) {
        classes.push('movementTarget');
    }
    if(canAttack) {
        classes.push('attackTarget');
    }

    return (
        <div
            className={classes.join(' ')}
            onClick={(canMoveTo || canAttack) ? onSelect : null}
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

