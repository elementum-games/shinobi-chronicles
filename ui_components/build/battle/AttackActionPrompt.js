import { JutsuInput } from "./JutsuInput.js";
import { unSlug } from "../utils/string.js";
import { findPlayerJutsu } from "./playerUtils.js";
export default function AttackActionPrompt({
  battle,
  selectedAttack,
  updateSelectedAttack
}) {
  const player = battle.fighters[battle.playerId];
  const opponent = battle.fighters[battle.opponentId];
  const {
    jutsuId,
    jutsuCategory,
    jutsuType,
    handSeals,
    weaponId
  } = selectedAttack;
  const isSelectingHandSeals = ['ninjutsu', 'genjutsu'].includes(jutsuCategory);
  const isSelectingWeapon = jutsuCategory === 'taijutsu';

  const handleHandSealsChange = handSeals => {
    updateSelectedAttack({
      handSeals
    });
  };

  const handleJutsuChange = (jutsuId, newJutsuCategory) => {
    let newSelectedAttack = {
      jutsuCategory: newJutsuCategory,
      jutsuId
    };
    const jutsu = findPlayerJutsu(battle, jutsuId, newJutsuCategory === 'bloodline');

    if (jutsu != null) {
      newSelectedAttack.jutsuType = jutsu.jutsuType;

      if (newJutsuCategory === "ninjutsu" || newJutsuCategory === "genjutsu") {
        newSelectedAttack.handSeals = jutsu.handSeals;
      } else {
        newSelectedAttack.handSeals = [];
      }
    } else {
      console.error("Invalid jutsu!");
    }

    updateSelectedAttack(newSelectedAttack);
  };

  const handleWeaponChange = weaponId => {
    console.log("Weapon selected ", weaponId);
    updateSelectedAttack({
      weaponId
    });
  };

  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", null, isSelectingHandSeals && /*#__PURE__*/React.createElement(HandSealsInput, {
    initialHandSeals: handSeals,
    onChange: handleHandSealsChange
  }), isSelectingWeapon && /*#__PURE__*/React.createElement(WeaponInput, {
    weapons: battle.playerEquippedWeapons,
    selectedWeaponId: weaponId,
    onChange: handleWeaponChange
  }))), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", {
    className: "jutsuCategoryHeader"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("span", null, "Ninjutsu"), /*#__PURE__*/React.createElement("span", null, "Taijutsu"), /*#__PURE__*/React.createElement("span", null, "Genjutsu"), player.hasBloodline && /*#__PURE__*/React.createElement("span", null, "Bloodline")))), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement(JutsuInput, {
    battle: battle,
    player: player,
    onChange: handleJutsuChange
  }))));
}

function HandSealsInput({
  initialHandSeals,
  onChange,
  tooltips = {}
}) {
  const [selectedHandSeals, setSelectedHandSeals] = React.useState(initialHandSeals);
  let handSeals = {
    "1": {
      selectedIndex: -1
    },
    "2": {
      selectedIndex: -1
    },
    "3": {
      selectedIndex: -1
    },
    "4": {
      selectedIndex: -1
    },
    "5": {
      selectedIndex: -1
    },
    "6": {
      selectedIndex: -1
    },
    "7": {
      selectedIndex: -1
    },
    "8": {
      selectedIndex: -1
    },
    "9": {
      selectedIndex: -1
    },
    "10": {
      selectedIndex: -1
    },
    "11": {
      selectedIndex: -1
    },
    "12": {
      selectedIndex: -1
    }
  };
  selectedHandSeals.forEach((hs, i) => {
    handSeals[hs].selectedIndex = i;
  });
  const setHandSealSelected = React.useCallback((num, selected) => {
    if (handSeals[num].selectedIndex !== -1 && !selected) {
      // Deselect
      const index = handSeals[num].selectedIndex;
      let newHandSeals = [...selectedHandSeals.slice(0, index), ...selectedHandSeals.slice(index + 1)];
      setSelectedHandSeals(newHandSeals);
      onChange(newHandSeals);
    } else if (handSeals[num].selectedIndex === -1 && selected) {
      let newHandSeals = [...selectedHandSeals, num];
      setSelectedHandSeals(newHandSeals);
      onChange(newHandSeals);
    } else {
      console.log(`tried to set ${num} to ${selected ? 'selected' : 'unselected'} but `, handSeals, selectedHandSeals);
    }
  }, [handSeals, selectedHandSeals]);
  React.useEffect(() => {
    setSelectedHandSeals(initialHandSeals);
  }, [initialHandSeals]);
  return /*#__PURE__*/React.createElement("div", {
    id: "handSeals"
  }, Object.keys(handSeals).map(num => {
    const selected = handSeals[num].selectedIndex !== -1;
    return /*#__PURE__*/React.createElement("div", {
      key: `handseal:${num}`,
      className: "handSealContainer"
    }, /*#__PURE__*/React.createElement("div", {
      className: `handSeal ${selected ? "selected" : ""}`,
      onClick: () => setHandSealSelected(num, !selected)
    }, /*#__PURE__*/React.createElement("img", {
      src: `./images/handseal_${num}.png`,
      draggable: "false"
    }), /*#__PURE__*/React.createElement("span", {
      className: "handSealNumber",
      style: {
        display: selected ? "initial" : "none"
      }
    }, handSeals[num].selectedIndex + 1), /*#__PURE__*/React.createElement("span", {
      className: "handsealTooltip"
    }, tooltips[num] ?? "")));
  }), /*#__PURE__*/React.createElement("div", {
    id: "handsealOverlay"
  }));
}

function WeaponInput({
  weapons,
  selectedWeaponId,
  onChange
}) {
  return /*#__PURE__*/React.createElement("div", {
    id: "weapons"
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      textAlign: "center",
      fontStyle: "italic"
    }
  }, "Please select a weapon to augment your Taijutsu with:"), /*#__PURE__*/React.createElement("p", {
    className: `weapon ${selectedWeaponId === 0 ? 'selected' : ''}`,
    onClick: () => onChange(0)
  }, /*#__PURE__*/React.createElement("b", null, "None")), weapons.map((weapon, i) => /*#__PURE__*/React.createElement("p", {
    key: i,
    className: `weapon ${selectedWeaponId === weapon.id ? 'selected' : ''}`,
    onClick: () => onChange(weapon.id)
  }, /*#__PURE__*/React.createElement("b", null, weapon.name), /*#__PURE__*/React.createElement("br", null), unSlug(weapon.effect), " (", weapon.effectAmount, "%)")));
}