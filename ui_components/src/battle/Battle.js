// @flow strict-local

import FighterDisplay from "./FighterDisplay.js";
import BattleField from "./BattleField.js";
import BattleLog from "./BattleLog.js";
import BattleActionPrompt from "./BattleActionPrompt.js";

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
            battle={battle}
            membersLink={membersLink}
        />
        {battle.isSpectating && <SpectateStatus />}
        {!battle.isSpectating && !battle.isComplete && <BattleActionPrompt battle={battle} />}
        {battle.lastTurnText != null && <BattleLog lastTurnText={battle.lastTurnText} />}
    </div>;
}

// Fighters and Field
type FightersAndFieldProps = {|
    +battle: BattleData,
    +membersLink: String,
|};

function FightersAndField({
    battle,
    membersLink,
}: FightersAndFieldProps) {
    const player = battle.fighters[battle.playerId];
    const opponent = battle.fighters[battle.opponentId];

    const { fighters, field, isSpectating, isMovementPhase } = battle;

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
                        isMovementPhase={isMovementPhase}
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