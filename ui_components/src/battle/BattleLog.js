// @flow strict

import type { BattleLogType, FighterActionLogType } from "./battleSchema.js";

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
                            {action.hitDescriptions.map((description, i) => (
                                <p key={i} style={{fontWeight: 'bold'}}>{description}</p>
                            ))}
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