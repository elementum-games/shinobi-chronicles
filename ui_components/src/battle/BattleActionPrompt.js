// @flow strict

import AttackActionPrompt from "./AttackActionPrompt.js";

import type { BattleType as BattleData } from "./battleSchema.js";
import type { AttackFormFields } from "./AttackActionPrompt.js";

export default function BattleActionPrompt({ battle }: { +battle: BattleData }): React$Node {
    if(battle.isComplete) {
        return null;
    }

    const [selectedAttack, setSelectedAttack] = React.useState<AttackFormFields>({
        handSeals: [],
        jutsuId: -1,
        jutsuCategory: 'ninjutsu',
        jutsuType: 'ninjutsu',
        weaponId: 0,
    });

    const updateSelectedAttack = (newSelectedAttack: $Shape<AttackFormFields>) => {
        setSelectedAttack(prevSelectedAttack => ({
            ...prevSelectedAttack,
            ...newSelectedAttack
        }));
    }

    const opponent = battle.fighters[battle.opponentId];

    const renderPhaseComponent = () => {
        if(battle.isPreparationPhase) {
            return null;
            /*<?php require 'templates/battle/prep_phase_action_prompt.php'; ?>*/
        }
        else if(battle.isMovementPhase) {
            return (
                <tr>
                    <td style={{ textAlign: "center" }}>
                        <em>Select a tile above</em>
                    </td>
                </tr>
            );
        }
        else if(battle.isAttackPhase) {
            return <AttackActionPrompt 
                battle={battle}
                selectedAttack={selectedAttack}
                updateSelectedAttack={updateSelectedAttack}
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
        prompt = "Select Movement Action";
    }
    else if(battle.isAttackPhase) {
        if((selectedAttack.jutsuCategory === 'ninjutsu' || selectedAttack.jutsuCategory === 'genjutsu') && selectedAttack.handSeals.length < 1) {
            prompt = "Select Jutsu";
        }
        else if(selectedAttack.jutsuId === -1) {
            prompt = "Select Jutsu";
        }
        else {
            prompt = "Select a Target (above)"
        }
    }

    const handleSubmit = () => {
        console.log("submit");
    };


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
                <p style={{ display: "block", textAlign: "center", margin: "auto auto 5px" }}>
                    <input type='submit' value='Submit' onClick={handleSubmit} />
                </p>
                <b>{battle.turnSecondsRemaining}</b> seconds remaining
            </td>
        </tr>
        </tbody>
    </table>;
}