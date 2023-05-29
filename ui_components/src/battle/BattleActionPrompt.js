// @flow strict

import AttackActionPrompt from "./AttackActionPrompt.js";

import type { BattleType as BattleData } from "./battleSchema.js";
import type { AttackInputFields } from "./AttackActionPrompt.js";

type Props = {
    +battle: BattleData,
    +isAttackSelected: boolean,
    +attackInput: AttackInputFields,
    +updateAttackInput: ($Shape<AttackInputFields>) => void,
    +forfeitBattle: () => void,
};

export default function BattleActionPrompt({
    battle,
    isAttackSelected,
    attackInput,
    updateAttackInput,
    forfeitBattle,
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
                <TimeRemaining
                    turnSecondsRemaining={battle.turnSecondsRemaining}
                    turnCount={battle.turnCount}
                />
                <button onClick={forfeitBattle}>Forfeit</button>
            </td>
        </tr>
        </tbody>
    </table>;
}

function TimeRemaining({
    turnSecondsRemaining,
    turnCount
}) {
    const [secondsRemaining, setSecondsRemaining] = React.useState(turnSecondsRemaining);

    React.useEffect(() => {
        setSecondsRemaining(turnSecondsRemaining);
    }, [turnCount]);

    React.useEffect(() => {
        const decrementTimeRemaining = () => {
            setSecondsRemaining(prevSeconds => (
                prevSeconds <= 0
                    ? 0
                    : prevSeconds - 1
            ));
        };
        const intervalId = setInterval(decrementTimeRemaining, 1000);

        return () => clearInterval(intervalId);
    }, []);


    return <div>
        <b>{secondsRemaining}</b> seconds remaining
    </div>;
}
