// @flow strict

import type { AttackHitLogType, BattleLogType, FighterActionLogType } from "./battleSchema.js";

type Props = {|
    +lastTurnLog: ?BattleLogType,
    +leftFighterId: string,
    +rightFighterId: string,
|};

export default function BattleLog({ lastTurnLog, leftFighterId, rightFighterId }: Props): React$Node {
    if(lastTurnLog == null) {
        return null;
    }

    return <table className='table'>
        <tbody>
        <tr>
            <th>Last turn</th>
        </tr>
        <tr>
            <td>
                <div style={{
                    display: "inline-flex",
                    width: "49.9%",
                    justifyContent: "center",
                    borderRight: "1px solid rgba(0,0,0,0.1)"
                }}>
                    {Object.keys(lastTurnLog.fighterActions)
                        .filter(fighterId => fighterId === leftFighterId)
                        .map((fighterId, i) => {
                            return <FighterAction
                                key={`battleLog:fighterAction:${fighterId}`}
                                action={lastTurnLog.fighterActions[ fighterId ]}
                                isAttackPhase={lastTurnLog.isAttackPhase}
                            />;
                        })
                    }
                </div>
                <div style={{
                    display: "inline-flex",
                    width: "49.9%",
                    justifyContent: "center"
                }}>
                    {Object.keys(lastTurnLog.fighterActions)
                        .filter(fighterId => fighterId === rightFighterId)
                        .map((fighterId, i) => {
                            return <FighterAction
                                key={`battleLog:fighterAction:${fighterId}`}
                                action={lastTurnLog.fighterActions[ fighterId ]}
                                isAttackPhase={lastTurnLog.isAttackPhase}
                            />;
                        })
                    }
                </div>
            </td>
        </tr>
        </tbody>
    </table>;
}

function FighterAction({
    action,
    isAttackPhase
}: {|
    +action: FighterActionLogType,
    +isAttackPhase: boolean
|}) {
    return (
        <div className="fighterActions">
            <p className="actionDescription">{action.actionDescription}</p>
            {action.hits.map((hit: AttackHitLogType, i) => {
                return (
                    <p key={i} className={`hit ${hit.damageType}Damage`} style={{fontWeight: 'bold'}}>
                        {hit.attackerName} deals {hit.damage} {hit.damageType} damage to {hit.targetName}.
                    </p>
                );
            })}
            {isAttackPhase && action.hits.length < 1 &&
                <p style={{fontStyle: 'italic'}}>
                    The attack missed.
                </p>
            }
            {action.effectHits.map((effectHit, i) => (
                <p
                    key={`effectHit:${i}`}
                    className={`effectHit ${styleForEffectHitType(effectHit.type)}`}
                >
                    {effectHit.description}
                </p>
            ))}
            {action.newEffectAnnouncements.map((announcement, i) => (
                <p className="effectAnnouncement">
                    {announcement}
                </p>
            ))}
        </div>
    );
}

function styleForEffectHitType(effectHitType: FighterActionLogType["effectHits"][number]["type"]) {
    switch(effectHitType) {
        case 'heal':
            return 'heal';
        case 'ninjutsu_damage':
            return 'ninjutsuDamage';
        case 'taijutsu_damage':
            return 'taijutsuDamage';
        case 'genjutsu_damage':
            return 'genjutsuDamage';
        default:
            return '';
    }
}