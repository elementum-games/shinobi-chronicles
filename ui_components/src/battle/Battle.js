// @flow strict-local

import FighterDisplay from "./FighterDisplay.js";
import BattleField from "./BattleField.js";
import BattleLog from "./BattleLog.js";
import BattleActionPrompt from "./BattleActionPrompt.js";

import type { BattleType as BattleData } from "./battleSchema.js";
import { buildFormData } from "../utils/formData.js";

type Props = {|
    +battle: BattleData,
    +battleApiLink: string,
    +membersLink: string,
|};

async function postData(url = '', data = {}) {
    // Default options are marked with *
    const response = await fetch(url, {
        method: 'POST',
        mode: 'cors', // no-cors, *cors, same-origin
        cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
        credentials: 'same-origin', // include, *same-origin, omit
        redirect: 'follow', // manual, *follow, error
        referrerPolicy: 'no-referrer',
        body: buildFormData(data)
    });
    return response.json();
}

function Battle({
    battle: initialBattle,
    battleApiLink,
    membersLink
}: Props) {
    const [battle, setBattle] = React.useState(initialBattle);

    const handleTileSelect = (tileIndex) => {
        console.log('selected tile', tileIndex);

        postData(
            battleApiLink,
            {
                submit_movement_action: "yes",
                selected_tile: tileIndex
            }
        )
        .then(response => {
            if (response.data.battle != null) {
                setBattle(response.data.battle);
            }
        });
    };

    return <div>
        <FightersAndField
            battle={battle}
            membersLink={membersLink}
            onTileSelect={handleTileSelect}
        />
        {battle.isSpectating && <SpectateStatus/>}
        {!battle.isSpectating && !battle.isComplete && <BattleActionPrompt battle={battle}/>}
        {battle.lastTurnText != null && <BattleLog lastTurnText={battle.lastTurnText}/>}
    </div>;
}

// Fighters and Field
type FightersAndFieldProps = {|
    +battle: BattleData,
    +membersLink: string,
    +onTileSelect: (tileIndex: number) => void,
|};

function FightersAndField({
    battle,
    membersLink,
    onTileSelect
}: FightersAndFieldProps) {
    const player = battle.fighters[ battle.playerId ];
    const opponent = battle.fighters[ battle.opponentId ];

    const { fighters, field, isSpectating, isMovementPhase } = battle;

    const handleTileSelect = (tileIndex) => {
        onTileSelect(tileIndex);
    };

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
                        showChakra={!isSpectating}
                    />
                </td>
                <td>
                    <FighterDisplay
                        fighter={opponent}
                        isOpponent={true}
                        showChakra={!isSpectating}
                    />
                </td>
            </tr>
            <tr>
                <td colSpan='2'>
                    <BattleField
                        player={player}
                        fighters={fighters}
                        tiles={field.tiles}
                        isSelectingTile={isMovementPhase}
                        onTileSelect={handleTileSelect}
                    />
                </td>
            </tr>
            </tbody>
        </table>
    );
}


function SpectateStatus() {
    return <div>
        Spectate Status
    </div>;

    /*
        <table class='table' style='margin-top:2px;'>
        <tr><td style='text-align:center;'>
            <?php if($battle->winner == Battle::TEAM1): ?>
               <?=  $battle->player1->getName() ?> won!
            <?php elseif($battle->winner == Battle::TEAM2): ?>
                <?= $battle->player2->getName() ?> won!
            <?php elseif($battle->winner == Battle::DRAW): ?>
                Fight ended in a draw.
            <?php else: ?>
                <b><?= $battle->timeRemaining() ?></b> seconds remaining<br />
                <a href='<?= $refresh_link ?>'>Refresh</a>
            <?php endif; ?>
        </td></tr>
    </table>

     */
}


window.Battle = Battle;