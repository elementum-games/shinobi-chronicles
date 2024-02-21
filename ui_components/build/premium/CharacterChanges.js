import { useModal } from "../utils/modalContext.js";
import { PurchaseConfirmation } from "./PurchaseConfirmation.js";
export function CharacterChanges({
  handlePremiumPurchase,
  playerData,
  costs,
  genders,
  skills
}) {
  const {
    openModal
  } = useModal();
  const [newUsername, setName] = React.useState("");
  const [statResetName, setStatReset] = React.useState(skills[0]);
  const [newGender, setGender] = React.useState(genders[0]);
  const handleNameFieldChange = event => {
    setName(event.target.value);
  };
  const handleStatResetChange = event => {
    setStatReset(event.target.value);
  };
  const handleGenderFieldChange = event => {
    setGender(event.target.value);
  };
  function formatSkillName(skillName) {
    let nameArray = skillName.split("_");
    let returnName = '';
    nameArray.map(function (namePos) {
      returnName += namePos[0].toUpperCase() + namePos.substring(1) + ' ';
    });
    return returnName.substring(0, returnName.length - 1);
  }

  /*React.useEffect(() => {
      const testInterval = setInterval(() => {
          console.log(
          "PD: " + playerData +
          "\r\nSRN: " + statResetName + "\r\nNCFN: " + newUsername
          + "\r\nGenders: " + genders + "\r\nGender Select: " + newGender
          + "\r\nSkills: " + skills);
      }, 1000);
        return () => clearInterval(testInterval);
  })*/

  return /*#__PURE__*/React.createElement("div", {
    className: "purchaseContainer"
  }, /*#__PURE__*/React.createElement("div", {
    className: "box-secondary halfWidth center"
  }, /*#__PURE__*/React.createElement("b", null, "Reset Character"), "You can reset your to a level 1 Akademi-sai.", /*#__PURE__*/React.createElement("br", null), "This change is free and can not be reversed.", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement(PurchaseConfirmation, {
    text: "Are you certain you wish to reset your character?",
    buttonValue: "reset",
    onConfirm: () => handlePremiumPurchase('reset_char')
  })), /*#__PURE__*/React.createElement("div", {
    className: "box-secondary halfWidth center"
  }, /*#__PURE__*/React.createElement("b", null, "Individual Stat Reset"), "You can reset an individual stat to free up space in your total stat cap.", /*#__PURE__*/React.createElement("br", null), "This change is free and can be used to allow further training.", /*#__PURE__*/React.createElement("select", {
    className: "purchaseSelectField",
    onChange: handleStatResetChange
  }, skills.map(function (name) {
    return /*#__PURE__*/React.createElement("option", {
      key: name,
      value: name
    }, formatSkillName(name));
  })), /*#__PURE__*/React.createElement(PurchaseConfirmation, {
    text: "Are you certain you wish to reset your " + formatSkillName(statResetName) + "?",
    buttonValue: "reset",
    onConfirm: () => handlePremiumPurchase('reset_stat', {
      stat: statResetName
    })
  })), /*#__PURE__*/React.createElement("div", {
    className: "box-secondary halfWidth center"
  }, /*#__PURE__*/React.createElement("b", null, "Reset AI Battles"), "Reset AI wins and losses to 0.", /*#__PURE__*/React.createElement("br", null), "Costs ", costs.ai_count_reset, " Ancient Kunai", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement(PurchaseConfirmation, {
    text: "Are you certain you wish to reset your AI wins and losses?",
    buttonValue: "reset",
    onConfirm: () => handlePremiumPurchase('reset_ai_battles')
  })), /*#__PURE__*/React.createElement("div", {
    className: "box-secondary halfWidth center"
  }, /*#__PURE__*/React.createElement("b", null, "Reset PvP battles"), "You can reset your PvP wins and losses to 0.", /*#__PURE__*/React.createElement("br", null), "Costs ", costs.pvp_count_reset, " Anicent Kunai", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement(PurchaseConfirmation, {
    text: "Are you certain you wish to reset your PvP wins and losses?",
    buttonValue: "reset",
    onConfirm: () => handlePremiumPurchase('reset_pvp_battles')
  })), /*#__PURE__*/React.createElement("div", {
    className: "box-secondary halfWidth center"
  }, /*#__PURE__*/React.createElement("b", null, "Change Name"), "You can change your character name for free once.", /*#__PURE__*/React.createElement("br", null), "Each change afterward costs ", /*#__PURE__*/React.createElement("br", null), "Name case changes are free (example: name1 => NaMe1).", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("input", {
    className: "purchaseTextField",
    type: "text",
    onChange: handleNameFieldChange
  }), /*#__PURE__*/React.createElement(PurchaseConfirmation, {
    text: "Are you certain you wish to change your name from " + playerData.user_name + " to " + newUsername + "?",
    buttonValue: "change",
    onConfirm: () => handlePremiumPurchase('change_name', {
      name: newUsername
    })
  })), /*#__PURE__*/React.createElement("div", {
    className: "box-secondary halfWidth center"
  }, /*#__PURE__*/React.createElement("b", null, "Change Gender"), "You can change your characters gender for ", /*#__PURE__*/React.createElement("br", null), "('None' gender will not be displayed on view profile)", /*#__PURE__*/React.createElement("select", {
    className: "purchaseSelectField",
    onChange: handleGenderFieldChange
  }, genders.map(function (name) {
    return /*#__PURE__*/React.createElement("option", {
      key: name,
      value: name
    }, formatSkillName(name));
  })), /*#__PURE__*/React.createElement(PurchaseConfirmation, {
    text: "Are you certain you wish to change your gender from " + playerData.gender + " to " + newGender + "?",
    buttonValue: "change",
    onConfirm: () => handlePremiumPurchase('gender_change', {
      gender: newGender
    })
  })), /*#__PURE__*/React.createElement("div", {
    className: "box-secondary fullWidth center"
  }, /*#__PURE__*/React.createElement("b", null, "Change Village"), "This is changing village....", /*#__PURE__*/React.createElement("br", null), "This is just to show what full width would look like for now...", /*#__PURE__*/React.createElement(PurchaseConfirmation, {
    text: "Are you certain you would like to change your village?",
    buttonValue: "change",
    onConfirm: () => handlePremiumPurchase('change_village')
  })));
}