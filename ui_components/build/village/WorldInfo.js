import { apiFetch } from "../utils/network.js";
import { StrategicInfoItem } from "./StrategicInfoItem.js";
import { ModalProvider } from "../utils/modalContext.js";
export function WorldInfo({
  villageName,
  strategicDataState,
  getVillageIcon,
  StrategicInfoItem,
  getPolicyDisplayData
}) {
  const [strategicDisplayLeft, setStrategicDisplayLeft] = React.useState(strategicDataState.find(item => item.village.name == villageName));
  const [strategicDisplayRight, setStrategicDisplayRight] = React.useState(strategicDataState.find(item => item.village.name != villageName));
  return /*#__PURE__*/React.createElement("div", {
    className: "worldInfo_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "row first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Strategic information"), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_container"
  }, /*#__PURE__*/React.createElement(StrategicInfoItem, {
    strategicInfoData: strategicDisplayLeft,
    getPolicyDisplayData: getPolicyDisplayData
  }), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_navigation",
    style: {
      marginTop: "155px"
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_navigation_village_buttons"
  }, villageName != "Stone" && /*#__PURE__*/React.createElement("div", {
    className: strategicDisplayRight.village.village_id == 1 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setStrategicDisplayRight(strategicDataState[0])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(1),
    className: "strategic_info_nav_button_icon"
  }))), villageName != "Cloud" && /*#__PURE__*/React.createElement("div", {
    className: strategicDisplayRight.village.village_id == 2 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setStrategicDisplayRight(strategicDataState[1])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(2),
    className: "strategic_info_nav_button_icon"
  }))), villageName != "Leaf" && /*#__PURE__*/React.createElement("div", {
    className: strategicDisplayRight.village.village_id == 3 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setStrategicDisplayRight(strategicDataState[2])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(3),
    className: "strategic_info_nav_button_icon"
  }))), villageName != "Sand" && /*#__PURE__*/React.createElement("div", {
    className: strategicDisplayRight.village.village_id == 4 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setStrategicDisplayRight(strategicDataState[3])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(4),
    className: "strategic_info_nav_button_icon"
  }))), villageName != "Mist" && /*#__PURE__*/React.createElement("div", {
    className: strategicDisplayRight.village.village_id == 5 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setStrategicDisplayRight(strategicDataState[4])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(5),
    className: "strategic_info_nav_button_icon"
  }))))), /*#__PURE__*/React.createElement(StrategicInfoItem, {
    strategicInfoData: strategicDisplayRight,
    getPolicyDisplayData: getPolicyDisplayData
  })))));
}