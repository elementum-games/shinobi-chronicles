// @flow strict

import AttackActionPrompt from "./AttackActionPrompt.js";

import type { BattleType as BattleData } from "./battleSchema.js";

export default function BattleActionPrompt({ battle }: { +battle: BattleData }) {
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
            return (
                <tr>
                    <td style={{ textAlign: "center" }}>
                        <em>Select a tile above</em>
                    </td>
                </tr>
            );
        }
        else if(battle.isAttackPhase) {
            return <AttackActionPrompt battle={battle} />;
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

    return <table className='table' style={{ marginTop: 0 }}>
        <tbody>
        <tr>
            <th>
                Select {battle.currentPhaseLabel} Action
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