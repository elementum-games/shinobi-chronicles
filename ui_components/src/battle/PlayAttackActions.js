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
    const travelTimePerTile = 500;

    const startingTileIndex = attack.pathSegments[0].tileIndex;
    const endingTileIndex = attack.pathSegments[attack.pathSegments.length - 1].tileIndex;

    const startingTileRect = getBoundingRectForTile(startingTileIndex);
    const endingTileRect = getBoundingRectForTile(endingTileIndex);

    const direction = startingTileIndex > fighterLocations[attack.fighterId] ? "right" : "left";

    // move attack start 0.5 tile closer to caster
    const leftOffset = (fighterLocations[attack.fighterId] - startingTileIndex) * 0.5 * tileSize;

    const leftDifference = endingTileRect.left - startingTileRect.left;

    const durationMs = Math.abs(endingTileIndex - startingTileIndex) * travelTimePerTile;

    return <>
        <style>
            {`
                @keyframes attack_${attackIndex} {
                    0% {
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
                src='/images/battle/fireball.png'
                className={`projectile ${direction}`}
                style={{ width: 50, height: 50 }}
            />
        </div>
    </>
}