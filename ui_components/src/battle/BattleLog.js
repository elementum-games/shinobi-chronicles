// @flow strict

type Props = {|
    +lastTurnText: string
|};

export default function BattleLog({ lastTurnText }: Props) {
    return <table className='table'>
        <tbody>
        <tr>
            <th>Last turn</th>
        </tr>
        <tr>
            <td style={{ textAlign: "center"}}>{lastTurnText}</td>
        </tr>
        </tbody>
    </table>;
}