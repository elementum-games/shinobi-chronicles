import AttackActionPrompt from "./AttackActionPrompt.js";
export default function BattleActionPrompt({
  battle,
  isAttackSelected,
  attackInput,
  updateAttackInput,
  forfeitBattle
}) {
  if (battle.isComplete) {
    return null;
  }

  const opponent = battle.fighters[battle.opponentId];

  const renderPhaseComponent = () => {
    if (battle.isPreparationPhase) {
      return null;
      /*<?php require 'templates/battle/prep_phase_action_prompt.php'; ?>*/
    } else if (battle.isMovementPhase) {
      return null;
    } else if (battle.isAttackPhase) {
      return /*#__PURE__*/React.createElement(AttackActionPrompt, {
        battle: battle,
        selectedAttack: attackInput,
        updateSelectedAttack: updateAttackInput
      });
    } else {
      return /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", null, "invalid phase"));
    }
  };

  let prompt = '';

  if (battle.isPreparationPhase) {
    prompt = "Select pre-fight actions";
  } else if (battle.isMovementPhase) {
    prompt = "Select Movement Action (above)";
  } else if (battle.isAttackPhase) {
    if (isAttackSelected) {
      prompt = "Select a Target (above)";
    } else {
      prompt = "Select Jutsu";
    }
  }

  return /*#__PURE__*/React.createElement("table", {
    className: "table",
    style: {
      marginTop: 0
    }
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, prompt)), !battle.playerActionSubmitted ? renderPhaseComponent() : /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", null, "Please wait for ", opponent.name, " to select an action.")), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
    style: {
      textAlign: "center"
    }
  }, /*#__PURE__*/React.createElement(TimeRemaining, {
    turnSecondsRemaining: battle.turnSecondsRemaining,
    turnCount: battle.turnCount
  }), /*#__PURE__*/React.createElement("button", {
    onClick: forfeitBattle
  }, "Forfeit")))));
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
      setSecondsRemaining(prevSeconds => prevSeconds <= 0 ? 0 : prevSeconds - 1);
    };

    const intervalId = setInterval(decrementTimeRemaining, 1000);
    return () => clearInterval(intervalId);
  }, []);
  return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("b", null, secondsRemaining), " seconds remaining");
}