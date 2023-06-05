// @flow

import type { BattleLogType, BoundingRect, FighterActionLogType } from "./battleSchema.js";

type PlayAttackActionsProps = {|
    +lastTurnLog: ?BattleLogType,
    +tileSize: number,
    +fighterLocations: { [ key: string ]: number },
    +getBoundingRectForTile: (tileIndex: number) => BoundingRect,
|};
export function PlayAttackActions({
    lastTurnLog,
    tileSize,
    fighterLocations,
    getBoundingRectForTile
}: PlayAttackActionsProps): React$Node {
    const turnNumber = lastTurnLog?.turnNumber || 0;
    const [prevTurnNumber, setPrevTurnNumber] = React.useState(turnNumber);
    const [attacksToRender, setAttacksToRender] = React.useState<$ReadOnlyArray<FighterActionLogType>>([]);

    if(lastTurnLog == null) {
        return null;
    }

    function addAttackToRender(attack: FighterActionLogType) {
        setAttacksToRender(prevValue => ([
            ...prevValue,
            attack
        ]));
    }

    if(prevTurnNumber !== turnNumber) {
        setPrevTurnNumber(turnNumber);
        setAttacksToRender([]);

        if(lastTurnLog.isAttackPhase) {
            Object.keys(lastTurnLog.fighterActions).forEach(key => {
                addAttackToRender(lastTurnLog.fighterActions[key]);
            });
        }
    }

    console.log('attacksToRender', attacksToRender);

    return <>
        {attacksToRender.map((attack: FighterActionLogType, i) => {
            if(attack.jutsuUseType === 'projectile') {
                return <ProjectileAttack
                    key={`attack:${i}`}
                    attackIndex={i}
                    attack={attack}
                    tileSize={tileSize}
                    getBoundingRectForTile={getBoundingRectForTile}
                    fighterLocations={fighterLocations}
                />
            }
        })}
    </>;
}

type ProjectileAttackProps = {|
    +attackIndex: number,
    +attack: FighterActionLogType,
    +tileSize: number,
    +getBoundingRectForTile: (tileIndex: number) => BoundingRect,
    +fighterLocations: { [ key: string ]: number },
|};
function ProjectileAttack({
    attackIndex,
    attack,
    tileSize,
    getBoundingRectForTile,
    fighterLocations
}: ProjectileAttackProps) {
    const initialTravelTime = 200;
    const travelTimePerTile = 400;

    const startingTileIndex = attack.pathSegments[0].tileIndex;
    const endingTileIndex = attack.pathSegments[attack.pathSegments.length - 1].tileIndex;

    const startingTileRect = getBoundingRectForTile(startingTileIndex);
    const endingTileRect = getBoundingRectForTile(endingTileIndex);

    const direction = startingTileIndex > fighterLocations[attack.fighterId] ? "right" : "left";

    // move attack start 0.5 tile closer to caster
    const leftOffset = (fighterLocations[attack.fighterId] - startingTileIndex) * 0.5 * tileSize;

    const leftDifference = endingTileRect.left - (startingTileRect.left + leftOffset);

    const durationMs = initialTravelTime + (Math.abs(endingTileIndex - startingTileIndex) * travelTimePerTile);

    const ninjutsuElementImages = {
        "Fire": '/images/battle/fireball.png',
        "Earth": '/images/battle/rock.png',
        "Wind": '/images/battle/wind_tornado_300px.gif',
        "Water": '/images/battle/fireball.png',
        "Lightning": '/images/battle/fireball.png',
        "None": '/images/battle/fireball.png',
    };

    const attackImage = ninjutsuElementImages[attack.jutsuElement];
    // TODO: Taijutsu/Genjutsu

    return <>
        <style>
            {`
                @keyframes attack_${attackIndex} {
                    0% {
                        transform: translateX(0px);
                        opacity: 0;
                    }
                    10% {
                        transform: translateX(0px);
                        opacity: 1;
                    }
                    85% {
                        transform: translateX(${leftDifference}px);
                        opacity: 1;
                    }
                    100% {
                        transform: translateX(${leftDifference}px);
                        opacity: 0;
                    }
                }
            `}
        </style>
        <div
            className="attackDisplay"
            style={{
                top: startingTileRect.top,
                left: startingTileRect.left + leftOffset,
                width: startingTileRect.width,
                height: startingTileRect.height,
                animationName: `attack_${attackIndex}`,
                animationDuration: `${durationMs}ms`,
                animationFillMode: "forwards",
                animationTimingFunction: "linear"
            }}
        >
            <img
                src={attackImage}
                className={`projectile ${direction}`}
            />
        </div>
    </>
}