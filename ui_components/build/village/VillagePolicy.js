import { useModal } from "../utils/modalContext.js";
import { getPolicyDisplayData } from "./villageUtils.js";
export default function VillagePolicy({
  policyDataState,
  playerSeatState,
  displayPolicyID,
  handlePrevPolicyClick,
  handleNextPolicyClick,
  handlePolicyChange,
  showPolicyControls = false
}) {
  const {
    openModal
  } = useModal();
  const policyDisplay = getPolicyDisplayData(displayPolicyID);
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "village_policy_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "village_policy_bonus_container"
  }, policyDisplay.bonuses.map((bonus, index) => /*#__PURE__*/React.createElement("div", {
    key: index,
    className: "policy_bonus_item"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "16",
    height: "16",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "25,20 50,45 25,70 0,45",
    fill: "#4a5e45"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "25,0 50,25 25,50 0,25",
    fill: "#6ab352"
  })), /*#__PURE__*/React.createElement("div", {
    className: "policy_bonus_text"
  }, bonus)))), showPolicyControls && displayPolicyID !== policyDataState.policy_id && /*#__PURE__*/React.createElement("div", {
    className: playerSeatState.seat_type === "kage" ? "village_policy_change_button" : "village_policy_change_button disabled",
    onClick: () => openModal({
      header: 'Confirmation',
      text: "Are you sure you want to change policies? You will be unable to select a new policy for 3 days.",
      ContentComponent: null,
      onConfirm: handlePolicyChange
    })
  }, "change policy"), /*#__PURE__*/React.createElement("div", {
    className: "village_policy_main_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "village_policy_main_inner"
  }, /*#__PURE__*/React.createElement("div", {
    className: "village_policy_banner",
    style: {
      backgroundImage: "url(" + policyDisplay.banner + ")"
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "village_policy_name_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "village_policy_name " + policyDisplay.glowClass
  }, policyDisplay.name)), /*#__PURE__*/React.createElement("div", {
    className: "village_policy_phrase"
  }, policyDisplay.phrase), /*#__PURE__*/React.createElement("div", {
    className: "village_policy_description"
  }, policyDisplay.description), showPolicyControls && displayPolicyID > 1 && /*#__PURE__*/React.createElement("div", {
    className: "village_policy_previous_wrapper"
  }, /*#__PURE__*/React.createElement("svg", {
    className: "previous_policy_button",
    width: "20",
    height: "20",
    viewBox: "0 0 100 100",
    onClick: handlePrevPolicyClick
  }, /*#__PURE__*/React.createElement("polygon", {
    className: "previous_policy_triangle_inner",
    points: "100,0 100,100 35,50"
  }), /*#__PURE__*/React.createElement("polygon", {
    className: "previous_policy_triangle_outer",
    points: "65,0 65,100 0,50"
  }))), showPolicyControls && displayPolicyID < 5 && /*#__PURE__*/React.createElement("div", {
    className: "village_policy_next_wrapper"
  }, /*#__PURE__*/React.createElement("svg", {
    className: "next_policy_button",
    width: "20",
    height: "20",
    viewBox: "0 0 100 100",
    onClick: handleNextPolicyClick
  }, /*#__PURE__*/React.createElement("polygon", {
    className: "next_policy_triangle_inner",
    points: "0,0 0,100 65,50"
  }), /*#__PURE__*/React.createElement("polygon", {
    className: "next_policy_triangle_outer",
    points: "35,0 35,100 100,50"
  }))))), /*#__PURE__*/React.createElement("div", {
    className: "village_policy_penalty_container"
  }, policyDisplay.penalties.map((penalty, index) => /*#__PURE__*/React.createElement("div", {
    key: index,
    className: "policy_penalty_item"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "16",
    height: "16",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "25,20 50,45 25,70 0,45",
    fill: "#4f1e1e"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "25,0 50,25 25,50 0,25",
    fill: "#ad4343"
  })), /*#__PURE__*/React.createElement("div", {
    className: "policy_penalty_text"
  }, penalty))))));
}