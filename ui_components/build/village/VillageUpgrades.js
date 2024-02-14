import { apiFetch } from "../utils/network.js";
import { useModal } from '../utils/modalContext.js';
export function VillageUpgrades({
  playerSeatState,
  villageName,
  villageAPI,
  buildingUpgradeDataState,
  setBuildingUpgradeDataState,
  resourceDataState,
  setResourceDataState
}) {
  const [selectedBuilding, setSelectedBuilding] = React.useState(null);
  const [selectedUpgrade, setSelectedUpgrade] = React.useState(null);
  const [hoveredUpgrade, setHoveredUpgrade] = React.useState(null);
  const {
    openModal
  } = useModal();
  const current_materials = resourceDataState.find(resource => resource.resource_name == "materials").count;
  const current_food = resourceDataState.find(resource => resource.resource_name == "food").count;
  const current_wealth = resourceDataState.find(resource => resource.resource_name == "wealth").count;
  function handleErrors(errors) {
    console.warn(errors);
  }
  const getBuildingUpkeep = building => {
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
    return {
      materials: materials_cost,
      food: food_cost,
      wealth: wealth_cost
    };
  };
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
  const BeginConstruction = () => {
    apiFetch(villageAPI, {
      request: 'BeginConstruction',
      building_key: selectedBuilding.key
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      setBuildingUpgradeDataState(response.data.buildingUpgradeData);
      setSelectedBuilding(response.data.buildingUpgradeData.find(b => b.key === selectedBuilding.key));
      openModal({
        header: 'Confirmation',
        text: response.data.response_message,
        ContentComponent: null,
        onConfirm: null
      });
    });
  };
  const CancelConstruction = () => {
    apiFetch(villageAPI, {
      request: 'CancelConstruction',
      building_key: selectedBuilding.key
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      setBuildingUpgradeDataState(response.data.buildingUpgradeData);
      setSelectedBuilding(response.data.buildingUpgradeData.find(b => b.key === selectedBuilding.key));
      openModal({
        header: 'Confirmation',
        text: response.data.response_message,
        ContentComponent: null,
        onConfirm: null
      });
    });
  };
  const BeginResearch = () => {
    apiFetch(villageAPI, {
      request: 'BeginResearch',
      upgrade_key: selectedUpgrade.key
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      setBuildingUpgradeDataState(response.data.buildingUpgradeData);
      setSelectedUpgrade(null);
      setSelectedBuilding(response.data.buildingUpgradeData.find(b => b.key === selectedBuilding.key));
      openModal({
        header: 'Confirmation',
        text: response.data.response_message,
        ContentComponent: null,
        onConfirm: null
      });
    });
  };
  const CancelResearch = () => {
    apiFetch(villageAPI, {
      request: 'CancelResearch',
      upgrade_key: selectedUpgrade.key
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      setBuildingUpgradeDataState(response.data.buildingUpgradeData);
      setSelectedUpgrade(null);
      setSelectedBuilding(response.data.buildingUpgradeData.find(b => b.key === selectedBuilding.key));
      openModal({
        header: 'Confirmation',
        text: response.data.response_message,
        ContentComponent: null,
        onConfirm: null
      });
    });
  };
  const buildingClickHandler = building => {
    setSelectedUpgrade(null);
    setSelectedBuilding(building);
  };
  const renderBuildingDetails = () => {
    return /*#__PURE__*/React.createElement("div", {
      className: "building_details"
    }, /*#__PURE__*/React.createElement("div", {
      className: "building_visual"
    }, /*#__PURE__*/React.createElement("div", {
      className: "building_visual_inner",
      style: {
        background: "url(/images/building_backgrounds/placeholderbuilding.jpg)"
      }
    })), /*#__PURE__*/React.createElement("div", {
      className: "building_details_contents"
    }, /*#__PURE__*/React.createElement("div", {
      className: "building_details_contents_row",
      style: {
        flex: 1
      }
    }, /*#__PURE__*/React.createElement("div", {
      className: "building_info"
    }), /*#__PURE__*/React.createElement("div", {
      className: "building_upkeep"
    })), /*#__PURE__*/React.createElement("div", {
      className: "building_details_contents_row",
      style: {
        flex: 0
      }
    }, /*#__PURE__*/React.createElement("div", {
      className: "building_controls_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "building_controls_label"
    }, "upgrade to next tier"), /*#__PURE__*/React.createElement("div", {
      className: "building_controls"
    }, /*#__PURE__*/React.createElement("div", {
      className: "building_upgrade_requirements"
    }, /*#__PURE__*/React.createElement("span", {
      style: {
        fontSize: "10px",
        lineHeight: "16px"
      }
    }, "upgrade cost: "), /*#__PURE__*/React.createElement("img", {
      src: "/images/icons/materials.png",
      alt: "materials",
      style: {
        height: "16px"
      }
    }), /*#__PURE__*/React.createElement("span", {
      style: {
        color: current_materials > selectedBuilding.materials_construction_cost ? "#96eeaf" : "#e98b99"
      }
    }, selectedBuilding.materials_construction_cost), /*#__PURE__*/React.createElement("img", {
      src: "/images/icons/food.png",
      alt: "food",
      style: {
        height: "16px"
      }
    }), /*#__PURE__*/React.createElement("span", {
      style: {
        color: current_food > selectedBuilding.food_construction_cost ? "#96eeaf" : "#e98b99"
      }
    }, selectedBuilding.food_construction_cost), /*#__PURE__*/React.createElement("img", {
      src: "/images/icons/wealth.png",
      alt: "wealth",
      style: {
        height: "16px"
      }
    }), /*#__PURE__*/React.createElement("span", {
      style: {
        color: current_wealth > selectedBuilding.wealth_construction_cost ? "#96eeaf" : "#e98b99"
      }
    }, selectedBuilding.wealth_construction_cost), /*#__PURE__*/React.createElement("img", {
      src: "images/v2/icons/timer.png",
      alt: "materials",
      style: {
        height: "16px"
      }
    }), /*#__PURE__*/React.createElement("span", null, selectedBuilding.construction_time, " ", selectedBuilding.construction_time > 1 ? " days" : " day")), /*#__PURE__*/React.createElement("div", {
      className: "building_buttons_container"
    }, selectedBuilding.status === "default" && playerSeatState.seat_type === "kage" && /*#__PURE__*/React.createElement(React.Fragment, null, !!selectedBuilding.requirements_met && current_materials > selectedBuilding.materials_construction_cost && current_food > selectedBuilding.food_construction_cost && current_wealth > selectedBuilding.wealth_construction_cost ? /*#__PURE__*/React.createElement("div", {
      className: "construction_begin_button",
      onClick: () => openModal({
        header: 'Confirmation',
        text: "sometext?",
        ContentComponent: null,
        onConfirm: () => BeginConstruction()
      })
    }, "upgrade") : /*#__PURE__*/React.createElement("div", {
      className: "construction_begin_button disabled"
    }, "upgrade")), selectedBuilding.status === "upgrading" && playerSeatState.seat_type === "kage" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
      className: "construction_cancel_button",
      onClick: () => openModal({
        header: 'Confirmation',
        text: "sometext?",
        ContentComponent: null,
        onConfirm: () => CancelConstruction()
      })
    }, "cancel")), selectedBuilding.health < selectedBuilding.max_health && playerSeatState.seat_type === "kage" ? /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
      className: "repair_begin_button",
      onClick: () => openModal({
        header: 'Confirmation',
        text: "sometext?",
        ContentComponent: null,
        onConfirm: () => BeginRepairs()
      })
    }, "repair")) : /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
      className: "repair_begin_button disabled"
    }, "repair"))))))));
  };
  const renderUpgradesContainer = () => {
    const renderUpgradeItems = upgrade_set => upgrade_set.upgrades.map((upgrade, index) => /*#__PURE__*/React.createElement("div", {
      key: upgrade.key,
      className: `upgrade_item ${upgrade.requirements_met && upgrade.status === "locked" ? "available" : upgrade.status}`,
      onMouseEnter: () => setHoveredUpgrade(upgrade),
      onMouseLeave: () => setHoveredUpgrade(null)
    }, /*#__PURE__*/React.createElement("div", {
      className: "upgrade_item_wrapper",
      onClick: () => setSelectedUpgrade(upgrade)
    }, /*#__PURE__*/React.createElement("div", {
      className: "upgrade_item_inner"
    }, /*#__PURE__*/React.createElement("div", {
      className: "upgrade_tier"
    }, romanize(index + 1))))));
    const renderUpgradeDetails = () => {
      return /*#__PURE__*/React.createElement("div", {
        className: "upgrade_details_container"
      }, selectedUpgrade.status === "researching" && /*#__PURE__*/React.createElement("div", {
        className: "research_progress_text_container"
      }, /*#__PURE__*/React.createElement("div", {
        className: "research_progress_label"
      }, "RESEARCH IN PROGRESS"), /*#__PURE__*/React.createElement("div", {
        className: "research_progress_text"
      }, selectedUpgrade.research_time_remaining)), /*#__PURE__*/React.createElement("div", {
        className: "upgrade_name"
      }, selectedUpgrade.name), /*#__PURE__*/React.createElement("div", {
        className: "upgrade_description"
      }, selectedUpgrade.description), /*#__PURE__*/React.createElement("div", {
        className: "upgrade_requirement_line"
      }, /*#__PURE__*/React.createElement("span", {
        style: {
          fontSize: "10px",
          lineHeight: "16px"
        }
      }, "research cost: "), /*#__PURE__*/React.createElement("img", {
        src: "/images/icons/materials.png",
        alt: "materials",
        style: {
          height: "16px"
        }
      }), /*#__PURE__*/React.createElement("span", {
        style: {
          color: "#e98b99"
        }
      }, selectedUpgrade.materials_research_cost), /*#__PURE__*/React.createElement("img", {
        src: "/images/icons/food.png",
        alt: "food",
        style: {
          height: "16px"
        }
      }), /*#__PURE__*/React.createElement("span", {
        style: {
          color: "#e98b99"
        }
      }, selectedUpgrade.food_research_cost), /*#__PURE__*/React.createElement("img", {
        src: "/images/icons/wealth.png",
        alt: "wealth",
        style: {
          height: "16px"
        }
      }), /*#__PURE__*/React.createElement("span", {
        style: {
          color: "#e98b99"
        }
      }, selectedUpgrade.wealth_research_cost)), /*#__PURE__*/React.createElement("div", {
        className: "upgrade_requirement_line"
      }, /*#__PURE__*/React.createElement("span", {
        style: {
          fontSize: "10px",
          lineHeight: "16px"
        }
      }, "daily upkeep: "), /*#__PURE__*/React.createElement("img", {
        src: "/images/icons/materials.png",
        alt: "materials",
        style: {
          height: "16px"
        }
      }), /*#__PURE__*/React.createElement("span", {
        style: {
          color: "#e98b99"
        }
      }, selectedUpgrade.materials_upkeep * 24), /*#__PURE__*/React.createElement("img", {
        src: "/images/icons/food.png",
        alt: "food",
        style: {
          height: "16px"
        }
      }), /*#__PURE__*/React.createElement("span", {
        style: {
          color: "#e98b99"
        }
      }, selectedUpgrade.food_upkeep * 24), /*#__PURE__*/React.createElement("img", {
        src: "/images/icons/wealth.png",
        alt: "wealth",
        style: {
          height: "16px"
        }
      }), /*#__PURE__*/React.createElement("span", {
        style: {
          color: "#e98b99"
        }
      }, selectedUpgrade.wealth_upkeep * 24)), /*#__PURE__*/React.createElement("div", {
        className: "upgrade_requirement_line"
      }, /*#__PURE__*/React.createElement("span", {
        style: {
          fontSize: "10px",
          lineHeight: "16px"
        }
      }, "research time: "), /*#__PURE__*/React.createElement("img", {
        src: "images/v2/icons/timer.png",
        alt: "materials",
        style: {
          height: "16px"
        }
      }), /*#__PURE__*/React.createElement("span", null, selectedUpgrade.research_time, " ", selectedUpgrade.research_time > 1 ? " days" : " day")), /*#__PURE__*/React.createElement("div", {
        className: "upgrade_buttons_container"
      }, !!selectedUpgrade.requirements_met && selectedUpgrade.status === "locked" && playerSeatState.seat_type === "kage" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
        className: "research_begin_button",
        onClick: () => openModal({
          header: 'Confirmation',
          text: "sometext?",
          ContentComponent: null,
          onConfirm: () => BeginResearch()
        })
      }, "begin research")), selectedUpgrade.status === "researching" && playerSeatState.seat_type === "kage" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
        className: "research_cancel_button",
        onClick: () => openModal({
          header: 'Confirmation',
          text: "sometext?",
          ContentComponent: null,
          onConfirm: () => CancelResearch()
        })
      }, "cancel research"))));
    };
    return /*#__PURE__*/React.createElement("div", {
      className: "upgrades_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "upgrade_set_list"
    }, selectedBuilding.upgrade_sets.map((upgrade_set, index) => /*#__PURE__*/React.createElement("div", {
      key: upgrade_set.key,
      className: "upgrade_set_item"
    }, selectedUpgrade !== null && upgrade_set.upgrades.find(u => u.key === selectedUpgrade.key) ? renderUpgradeDetails() : /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
      className: "upgrade_set_name"
    }, upgrade_set.name), /*#__PURE__*/React.createElement("div", {
      className: "upgrade_list"
    }, renderUpgradeItems(upgrade_set)), hoveredUpgrade !== null && upgrade_set.upgrades.find(u => u.key === hoveredUpgrade.key) ? /*#__PURE__*/React.createElement("div", {
      className: "upgrade_set_description"
    }, hoveredUpgrade.name, /*#__PURE__*/React.createElement("br", null), hoveredUpgrade.description) : /*#__PURE__*/React.createElement("div", {
      className: "upgrade_set_description"
    }, upgrade_set.description)))).concat(Array.from({
      length: fillerDivsNeeded
    }).map((_, fillerIndex) => /*#__PURE__*/React.createElement("div", {
      key: `filler-${fillerIndex}`,
      className: "upgrade_set_item filler"
    })))));
  };
  const remainder = selectedBuilding !== null ? selectedBuilding.upgrade_sets.length % 3 : 0;
  const fillerDivsNeeded = remainder === 0 ? 0 : 3 - remainder;
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
    onClick: () => buildingClickHandler(building)
  }, /*#__PURE__*/React.createElement("div", {
    className: "building_item_inner",
    style: {
      background: "url(/images/building_backgrounds/placeholderbuilding.jpg)"
    }
  }, building.status === "upgrading" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "construction_overlay"
  }), /*#__PURE__*/React.createElement("div", {
    className: "construction_progress_overlay",
    style: {
      height: `${building.construction_progress / building.construction_progress_required * 100}%`
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "construction_progress_text_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "construction_progress_label"
  }, "UNDER CONSTRUCTION"), /*#__PURE__*/React.createElement("div", {
    className: "construction_progress_text"
  }, building.construction_time_remaining)))), /*#__PURE__*/React.createElement("div", {
    className: "building_nameplate"
  }, /*#__PURE__*/React.createElement("div", {
    className: "building_name"
  }, building.tier == 0 && "Basic " + building.name, building.tier > 0 && "Tier " + building.tier + " " + building.name))))), selectedBuilding && /*#__PURE__*/React.createElement("div", {
    className: "building_details_container"
  }, renderBuildingDetails(), renderUpgradesContainer()))));
}