import AttackActionPrompt from "./AttackActionPrompt.js";
export default function BattleActionPrompt({
  battle
}) {
  if (battle.isComplete) {
    return null;
  }

  const [selectedAttack, setSelectedAttack] = React.useState({
    handSeals: [],
    jutsuId: -1,
    jutsuCategory: 'ninjutsu',
    jutsuType: 'ninjutsu',
    weaponId: 0
  });

  const updateSelectedAttack = newSelectedAttack => {
    setSelectedAttack(prevSelectedAttack => ({ ...prevSelectedAttack,
      ...newSelectedAttack
    }));
  };

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
        selectedAttack: selectedAttack,
        updateSelectedAttack: updateSelectedAttack
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
    if ((selectedAttack.jutsuCategory === 'ninjutsu' || selectedAttack.jutsuCategory === 'genjutsu') && selectedAttack.handSeals.length < 1) {
      prompt = "Select Jutsu";
    } else if (selectedAttack.jutsuId === -1) {
      prompt = "Select Jutsu";
    } else {
      prompt = "Select a Target (above)";
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