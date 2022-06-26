// @flow strict

type Props = {|
    +lastTurnText: string
|};

export default function BattleLog({ lastTurnText }: Props): React$Node {
    const textSegments = lastTurnText.split('[hr]');

    return <table className='table'>
        <tbody>
        <tr>
            <th>Last turn</th>
        </tr>
        <tr>
            <td style={{ textAlign: "center"}}>
                {textSegments.map((segment, i) => (
                    <p key={i}>{segment}</p>
                ))}
            </td>
        </tr>
        </tbody>
    </table>;
}