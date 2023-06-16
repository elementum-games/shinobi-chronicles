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
    window.addAttackToRender = addAttackToRender;

    if(prevTurnNumber !== turnNumber) {
        setPrevTurnNumber(turnNumber);
        setAttacksToRender([]);

        if(lastTurnLog.isAttackPhase) {
            Object.keys(lastTurnLog.fighterActions).forEach(key => {
                addAttackToRender(lastTurnLog.fighterActions[key]);
            });
        }
    }

    /*React.useEffect(() => {
        let testAttack = {
            "fighterId": "T1:U:1",
            "actionDescription": "lsmjudoka performs a swift punch to Chuunin Expert's head.",
            "pathSegments": [
                {
                    "tileIndex": -7,
                    "rawDamage": 113659.2,
                    "timeArrived": 1
                }
            ],
            "hits": [],
            "effectHits": [
                {
                    "casterId": "T1:U:1",
                    "targetId": "T1:U:1",
                    "type": "heal",
                    "description": "lsmjudoka heals 1353.03 health"
                }
            ],
            "newEffectAnnouncements": [],
            "jutsuElement": "None",
            "jutsuType": "taijutsu",
            "jutsuUseType": "physical",
            "jutsuTargetType": "tile"
        }

        setTimeout(() => addAttackToRender(testAttack), 500);
    }, []);*/

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
            else if(attack.jutsuUseType === 'physical') {
                return <PhysicalAttack
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
        "Water": '/images/battle/wave1.png',
        // https://www.freepik.com/premium-vector/blue-ocean-waves-sea-wave-water-surface-seamless-pattern-cartoon-splash-power-waters-nature-travel-beach-journey-vector-set-design-elements_31593378.htm
        "Lightning": '/images/battle/lightning_hori_wave.gif',
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
                        z-index: 4;
                    }
                    101% {
                        transform: translateX(${leftDifference}px);
                        opacity: 0;
                        z-index: -1;
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


type PhysicalAttackProps = {|
    +attackIndex: number,
    +attack: FighterActionLogType,
    +tileSize: number,
    +getBoundingRectForTile: (tileIndex: number) => BoundingRect,
    +fighterLocations: { [ key: string ]: number },
|};
function PhysicalAttack({
    attackIndex,
    attack,
    tileSize,
    getBoundingRectForTile,
    fighterLocations
}: PhysicalAttackProps) {
    const initialTravelTime = 1000;
    const travelTimePerTile = 500;

    const startingTileIndex = attack.pathSegments[0].tileIndex;
    const endingTileIndex = attack.pathSegments[attack.pathSegments.length - 1].tileIndex;

    const startingTileRect = getBoundingRectForTile(startingTileIndex);
    const endingTileRect = getBoundingRectForTile(endingTileIndex);

    const direction = startingTileIndex > fighterLocations[attack.fighterId] ? "right" : "left";

    // move attack start 0.5 tile closer to caster
    const leftOffset = (fighterLocations[attack.fighterId] - startingTileIndex) * 0.5 * tileSize;

    const leftDifference = endingTileRect.left - (startingTileRect.left + leftOffset);

    const durationMs = initialTravelTime + (Math.abs(endingTileIndex - startingTileIndex) * travelTimePerTile);

    const images = {
        "Punch": '/images/battle/taijutsu/fist1.png',
        "Kick": '/images/battle/taijutsu/foot1.png',
    };

    /*const attackImage = images[attack.jutsuElement];*/
    const attackImage = images["Kick"];

    return <>
        <style>
            {`
                @keyframes attack_${attackIndex} {
                    0% {
                        transform: translateX(0px);
                        opacity: 0;
                    }
                    15% {
                        transform: translateX(0px);
                        opacity: 1;
                    }
                    20% {
                        transform: translateX(0px);
                        opacity: 1;
                    }
                    45% {
                        transform: translateX(${leftDifference * -0.2}px);
                        opacity: 1;
                    }
                    60% {
                        transform: translateX(${leftDifference * -0.2}px);
                        opacity: 1;
                    }
                    70% {
                        transform: translateX(${leftDifference * 0.9}px);
                        opacity: 1;
                    }
                    85% {
                        transform: translateX(${leftDifference}px);
                        opacity: 1;
                    }
                    99% {
                        transform: translateX(${leftDifference}px);
                        opacity: 0;
                        z-index: 4;
                    }
                    100% {
                        transform: translateX(${leftDifference}px);
                        opacity: 0;
                        z-index: -1;
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
                className={`physical ${direction}`}
            />
        </div>
    </>
}