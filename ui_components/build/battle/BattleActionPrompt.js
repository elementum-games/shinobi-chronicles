import AttackActionPrompt from "./AttackActionPrompt.js";
export default function BattleActionPrompt({
  battle,
  isAttackSelected,
  attackInput,
  updateAttackInput
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
      return /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
        style: {
          textAlign: "center"
        }
      }, /*#__PURE__*/React.createElement("em", null, "Select a tile above")));
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
    prompt = "Select Movement Action";
  } else if (battle.isAttackPhase) {
    if (isAttackSelected) {
      prompt = "Select a Target (above)";
    } else {
      prompt = "Select Jutsu";
    }
  }

  const handleSubmit = () => {
    console.log("submit");
  };

  return /*#__PURE__*/React.createElement("table", {
    className: "table",
    style: {
      marginTop: 0
    }
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, prompt)), !battle.playerActionSubmitted ? renderPhaseComponent() : /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", null, "Please wait for ", opponent.name, " to select an action.")), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
    style: {
      textAlign: "center"
    }
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      display: "block",
      textAlign: "center",
      margin: "auto auto 5px"
    }
  }, /*#__PURE__*/React.createElement("input", {
    type: "submit",
    value: "Submit",
    onClick: handleSubmit
  })), /*#__PURE__*/React.createElement("b", null, battle.turnSecondsRemaining), " seconds remaining"))));
}