// @flow strict

import AttackActionPrompt from "./AttackActionPrompt.js";

import type { BattleType as BattleData } from "./battleSchema.js";
import type { AttackInputFields } from "./AttackActionPrompt.js";

type Props = {
    +battle: BattleData,
    +isAttackSelected: boolean,
    +attackInput: AttackInputFields,
    +updateAttackInput: ($Shape<AttackInputFields>) => void,
};

export default function BattleActionPrompt({
    battle,
    isAttackSelected,
    attackInput,
    updateAttackInput,
}: Props): React$Node {
    if(battle.isComplete) {
        return null;
    }

    const opponent = battle.fighters[battle.opponentId];

    const renderPhaseComponent = () => {
        if(battle.isPreparationPhase) {
            return null;
            /*<?php require 'templates/battle/prep_phase_action_prompt.php'; ?>*/
        }
        else if(battle.isMovementPhase) {
            return null;
        }
        else if(battle.isAttackPhase) {
            return <AttackActionPrompt 
                battle={battle}
                selectedAttack={attackInput}
                updateSelectedAttack={updateAttackInput}
            />;
        }
        else {
            return (
                <tr>
                    <td>
                        invalid phase
                    </td>
                </tr>
            );
        }
    };

    let prompt = '';
    if(battle.isPreparationPhase) {
        prompt = "Select pre-fight actions";
    }
    else if(battle.isMovementPhase) {
        prompt = "Select Movement Action (above)";
    }
    else if(battle.isAttackPhase) {
        if(isAttackSelected) {
            prompt = "Select a Target (above)"
        }
        else {
            prompt = "Select Jutsu";
        }
    }

    return <table className='table' style={{ marginTop: 0 }}>
        <tbody>
        <tr>
            <th>
                {prompt}
            </th>
        </tr>

        {!battle.playerActionSubmitted ?
            renderPhaseComponent()
            :
            <tr>
                <td>Please wait for {opponent.name} to select an action.</td>
            </tr>
        }

        <tr>
            <td style={{ textAlign: "center" }}>
                <b>{battle.turnSecondsRemaining}</b> seconds remaining
            </td>
        </tr>
        </tbody>
    </table>;
}