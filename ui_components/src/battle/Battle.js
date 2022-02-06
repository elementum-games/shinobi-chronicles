// @flow strict-local

import { FighterDisplay } from "./FighterDisplay.js";
import { BattleField } from "./BattleField.js";

import type { BattleType as BattleData } from "./battleSchema.js";

type Props = {|
    +battle: BattleData,
    +membersLink: string,
|};

function Battle({
    battle,
    membersLink
}: Props) {
    return <div>
        <FightersAndField
            player={battle.fighters[battle.playerId]}
            opponent={battle.fighters[battle.opponentId]}
            isSpectating={false}
            fighters={battle.fighters}
            field={battle.field}
            membersLink={membersLink}
        />
    </div>;
}

function FightersAndField({
    player,
    opponent,
    membersLink,
    isSpectating,
    fighters,
    field
}) {
    return (
        <table className='table'>
            <tbody>
            <tr>
                <th style={{ width: "50%" }}>
                    <a href={`${membersLink}}&user=${player.name}`} style={{ textDecoration: "none" }}>
                        {player.name}
                    </a>
                </th>
                <th style={{ width: "50%" }}>
                    {opponent.isNpc ?
                        opponent.name
                        :
                        <a href={`${membersLink}}&user=${opponent.name}`} style={{ textDecoration: "none" }}>
                            {opponent.name}
                        </a>
                    }
                </th>
            </tr>
            <tr>
                <td>
                    <FighterDisplay
                        fighter={player}
                        showChakra={isSpectating}
                    />
                </td>
                <td>
                    <FighterDisplay
                        fighter={opponent}
                        isOpponent={true}
                        showChakra={false}
                    />
                </td>
            </tr>
            <tr>
                <td colSpan='2'>
                    <BattleField
                        fighters={fighters}
                        tiles={field.tiles}
                    />
                </td>
            </tr>
            </tbody>
        </table>
    );
}

window.Battle = Battle;