// @flow strict-local

import FighterDisplay from "./FighterDisplay.js";
import BattleField from "./BattleField.js";
import BattleLog from "./BattleLog.js";
import BattleActionPrompt from "./BattleActionPrompt.js";

import type { BattleType as BattleData } from "./battleSchema.js";
import { apiFetch } from "../utils/network.js";
import type { AttackInputFields } from "./AttackActionPrompt.js";

type Props = {|
    +battle: BattleData,
    +battleApiLink: string,
    +membersLink: string,
|};

function Battle({
    battle: initialBattle,
    battleApiLink,
    membersLink
}: Props) {
    // STATE
    const [battle, setBattle] = React.useState(initialBattle);
    const [attackInput, setAttackInput] = React.useState<AttackInputFields>({
        handSeals: [],
        jutsuId: -1,
        jutsuCategory: 'ninjutsu',
        jutsuType: 'ninjutsu',
        weaponId: 0,
        targetTileIndex: null
    });

    // DERIVED STATE
    const isAttackSelected = battle.isAttackPhase && (attackInput.jutsuId !== -1 || attackInput.handSeals.length > 0);
    const isSelectingTile = battle.isMovementPhase || isAttackSelected;

    // STATE MUTATORS
    const updateAttackInput = (newAttackInput: $Shape<AttackInputFields>) => {
        setAttackInput(prevSelectedAttack => ({
            ...prevSelectedAttack,
            ...newAttackInput
        }));
    }

    // ACTIONS
    const handleTileSelect = (tileIndex) => {
        console.log('selected tile', tileIndex);

        if(battle.isMovementPhase) {
            apiFetch(
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
        }
        else if(isAttackSelected) {
            apiFetch(
                battleApiLink,
                {
                    submit_attack: "1",
                    hand_seals: attackInput.handSeals,
                    jutsu_id: attackInput.jutsuId,
                    jutsu_category: attackInput.jutsuCategory,
                    weapon_id: attackInput.weaponId,
                    target_tile: tileIndex
                }
            )
            .then(response => {
                if (response.data.battle != null) {
                    setBattle(response.data.battle);
                }
            });
        }
    };

    return <div>
        <FightersAndField
            battle={battle}
            membersLink={membersLink}
            isSelectingTile={isSelectingTile}
            onTileSelect={handleTileSelect}
        />
        {battle.isSpectating && <SpectateStatus/>}
        {!battle.isSpectating && !battle.isComplete && (
            <BattleActionPrompt
                battle={battle}
                attackInput={attackInput}
                updateAttackInput={updateAttackInput}
                isAttackSelected={isSelectingTile}
            />
        )}
        {battle.lastTurnText != null && <BattleLog lastTurnText={battle.lastTurnText}/>}
    </div>;
}

// Fighters and Field
type FightersAndFieldProps = {|
    +battle: BattleData,
    +membersLink: string,
    +isSelectingTile: boolean,
    +onTileSelect: (tileIndex: number) => void,
|};

function FightersAndField({
    battle,
    membersLink,
    isSelectingTile,
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
                        isSelectingTile={isSelectingTile}
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