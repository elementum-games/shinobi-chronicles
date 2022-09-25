// @flow strict

import type { AttackHitLogType, BattleLogType } from "./battleSchema.js";

type Props = {|
    +lastTurnLog: ?BattleLogType
|};

export default function BattleLog({ lastTurnLog }: Props): React$Node {
    if(lastTurnLog == null) {
        return null;
    }

    return <table className='table'>
        <tbody>
        <tr>
            <th>Last turn</th>
        </tr>
        <tr>
            <td style={{ textAlign: "center"}}>
                {Object.keys(lastTurnLog.fighterActions).map((fighterId, i) => {
                    const action = lastTurnLog.fighterActions[ fighterId ];

                    return (
                        <div key={i} style={{ borderBottom: '1px solid rgba(0,0,0,0.5)' }}>
                            <p>{action.actionDescription}</p>
                            <br />
                            {action.hits.map((hit: AttackHitLogType, i) => {
                                return (
                                    <p key={i} className={`${hit.damageType}Damage`} style={{fontWeight: 'bold'}}>
                                        {hit.attackerName} deals {hit.damage} {hit.damageType} damage to {hit.targetName}.
                                    </p>
                                );
                            })}
                            {lastTurnLog.isAttackPhase && action.hits.length < 1 &&
                                <p key={i} style={{fontStyle: 'italic'}}>
                                    The attack missed.
                                </p>
                            }
                            {action.appliedEffectDescriptions.map((description, i) => (
                                <p key={i}>{description}</p>
                            ))}
                            {action.newEffectAnnouncements.map((announcement, i) => (
                                <p key={i} style={{fontWeight: 'italic', marginTop: '3px'}}>
                                    {announcement}
                                </p>
                            ))}
                        </div>
                    );
                })}
            </td>
        </tr>
        </tbody>
    </table>;
}