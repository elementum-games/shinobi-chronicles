import { apiFetch } from "../utils/network.js";
export function VillageUpgrades({
  buildingUpgradeDataState,
  setBuildingUpgradeDataState
}) {
  const [selectedBuilding, setSelectedBuilding] = React.useState(null);
  //const [selectedUpgrade, setSelectedUpgrade] = React.useState(null);
  const [hoveredUpgrade, setHoveredUpgrade] = React.useState(null);
  const getBuildingUpkeepString = building => {
    let materials_cost = 0;
    let food_cost = 0;
    let wealth_cost = 0;
    let return_string = "upkeep cost / day: ";
    building.upgrade_sets.forEach(upgrade_set => {
      upgrade_set.upgrades.forEach(upgrade => {
        if (upgrade.status == "active") {
          materials_cost += upgrade.materials_upkeep;
          food_cost += upgrade.food_upkeep;
          wealth_cost += upgrade.wealth_upkeep;
        }
      });
    });
    return_string += materials_cost + " materials, ";
    return_string += food_cost + " food, ";
    return_string += wealth_cost + " wealth ";
    return return_string;
  };
  /*const getUpgradeUpkeepString = () => {
      let materials_cost = 0;
      let food_cost = 0;
      let wealth_cost = 0;
      let return_string = "";
      if (materials_cost > 0) {
          return_string += materials_cost + " materials ";
      }
      if (food_cost > 0) {
          return_string += food_cost + " food ";
      }
      if (wealth_cost > 0) {
          return_string += wealth_cost + " wealth ";
      }
      return return_string;
  }*/
  function romanize(num) {
    switch (num) {
      case 1:
        return "I";
      case 2:
        return "II";
      case 3:
        return "III";
      case 4:
        return "IV";
      case 5:
        return "V";
      case 6:
        return "VI";
      case 7:
        return "VII";
      case 8:
        return "VIII";
      case 9:
        return "IX";
      case 10:
        return "X";
      default:
        return "I";
    }
  }
  const renderUpgradeItems = upgrade_set => upgrade_set.upgrades.map((upgrade, index) => /*#__PURE__*/React.createElement("div", {
    key: upgrade.key,
    className: `upgrade_item ${upgrade.requirements_met && upgrade.status === "locked" ? "available" : upgrade.status}`,
    onMouseEnter: () => setHoveredUpgrade(upgrade),
    onMouseLeave: () => setHoveredUpgrade(null)
  }, /*#__PURE__*/React.createElement("div", {
    className: "upgrade_item_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "upgrade_item_inner"
  }, /*#__PURE__*/React.createElement("div", {
    className: "upgrade_tier"
  }, romanize(index + 1))))));
  return /*#__PURE__*/React.createElement("div", {
    className: "upgradespage_container"
  }, /*#__PURE__*/React.createElement("svg", {
    height: "0",
    width: "0"
  }, /*#__PURE__*/React.createElement("defs", null, /*#__PURE__*/React.createElement("filter", {
    id: "building_hover"
  }, /*#__PURE__*/React.createElement("feGaussianBlur", {
    in: "SourceAlpha",
    stdDeviation: "2",
    result: "blur"
  }), /*#__PURE__*/React.createElement("feFlood", {
    floodColor: "white",
    result: "floodColor"
  }), /*#__PURE__*/React.createElement("feComponentTransfer", {
    in: "blur",
    result: "opacityAdjustedBlur"
  }, /*#__PURE__*/React.createElement("feFuncA", {
    type: "linear",
    slope: "1"
  })), /*#__PURE__*/React.createElement("feComposite", {
    in: "floodColor",
    in2: "opacityAdjustedBlur",
    operator: "in",
    result: "coloredBlur"
  }), /*#__PURE__*/React.createElement("feMerge", null, /*#__PURE__*/React.createElement("feMergeNode", {
    in: "coloredBlur"
  }), /*#__PURE__*/React.createElement("feMergeNode", {
    in: "SourceGraphic"
  }))))), /*#__PURE__*/React.createElement("div", {
    className: "row first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Village Buildings"), /*#__PURE__*/React.createElement("div", {
    className: "buildings_container"
  }, buildingUpgradeDataState.map((building, index) => /*#__PURE__*/React.createElement("div", {
    key: building.key,
    className: "building_item",
    onClick: () => setSelectedBuilding(building)
  }, /*#__PURE__*/React.createElement("div", {
    className: "building_item_inner",
    style: {
      background: "url(/images/building_backgrounds/placeholderbuilding.jpg)"
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "building_nameplate"
  }, /*#__PURE__*/React.createElement("div", {
    className: "building_name"
  }, building.tier == 0 && "Basic " + building.name, building.tier > 0 && "Tier " + building.tier + " " + building.name))))), selectedBuilding && /*#__PURE__*/React.createElement("div", {
    className: "upgrades_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "building_details"
  }, /*#__PURE__*/React.createElement("div", {
    className: "building_name"
  }, selectedBuilding.tier == 0 && "Basic " + selectedBuilding.name, selectedBuilding.tier > 0 && "Tier " + selectedBuilding.tier + " " + selectedBuilding.name), /*#__PURE__*/React.createElement("div", {
    className: "building_upkeep"
  }, getBuildingUpkeepString(selectedBuilding))), /*#__PURE__*/React.createElement("div", {
    className: "upgrade_set_list"
  }, selectedBuilding.upgrade_sets.map((upgrade_set, index) => /*#__PURE__*/React.createElement("div", {
    key: upgrade_set.key,
    className: "upgrade_set_item"
  }, /*#__PURE__*/React.createElement("div", {
    className: "upgrade_set_name"
  }, upgrade_set.name), /*#__PURE__*/React.createElement("div", {
    className: "upgrade_list"
  }, renderUpgradeItems(upgrade_set)), hoveredUpgrade !== null && upgrade_set.upgrades.find(u => u.key === hoveredUpgrade.key) ? /*#__PURE__*/React.createElement("div", {
    className: "upgrade_set_description"
  }, hoveredUpgrade.name, /*#__PURE__*/React.createElement("br", null), hoveredUpgrade.description) : /*#__PURE__*/React.createElement("div", {
    className: "upgrade_set_description"
  }, upgrade_set.description))))))));
}