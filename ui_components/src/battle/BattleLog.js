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
                        <p key={i} style={{ borderBottom: '1px solid rgba(0,0,0,0.5)' }}>
                            {action.actionDescription}
                        </p>
                    );
                })}
            </td>
        </tr>
        </tbody>
    </table>;
}