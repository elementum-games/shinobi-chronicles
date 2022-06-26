export function JutsuInput({
  battle,
  player,
  onChange
}) {
  const standardCategories = [{
    key: 'ninjutsu',
    jutsuType: battle.jutsuTypes.ninjutsu,
    initial: 'N'
  }, {
    key: 'taijutsu',
    jutsuType: battle.jutsuTypes.taijutsu,
    initial: 'T'
  }, {
    key: 'genjutsu',
    jutsuType: battle.jutsuTypes.genjutsu,
    initial: 'G'
  }];
  const [selectedJutsu, setSelectedJutsu] = React.useState({
    id: 0,
    categoryKey: ''
  });

  const handleJutsuSelect = (categoryKey, jutsu) => {
    setSelectedJutsu({
      categoryKey: categoryKey,
      id: jutsu.id,
      jutsuType: jutsu.jutsuType
    });
    onChange(jutsu.id, categoryKey);
  };

  return /*#__PURE__*/React.createElement("div", {
    id: "jutsuContainer"
  }, standardCategories.map((category, x) => {
    let categoryJutsuCount = 1;
    return /*#__PURE__*/React.createElement("div", {
      className: "jutsuCategory",
      key: x
    }, battle.playerDefaultAttacks.filter(jutsu => jutsu.jutsuType === category.jutsuType).map((jutsu, i) => /*#__PURE__*/React.createElement(Jutsu, {
      key: i,
      jutsu: jutsu,
      selected: category.key === selectedJutsu.categoryKey && jutsu.id === selectedJutsu.id,
      onClick: () => handleJutsuSelect(category.key, jutsu),
      hotkeyDisplay: `${category.initial}${categoryJutsuCount}`
    })), battle.playerEquippedJutsu.filter(jutsu => jutsu.jutsuType === category.jutsuType).map((jutsu, i) => {
      return /*#__PURE__*/React.createElement(Jutsu, {
        key: i,
        jutsu: jutsu,
        selected: category.key === selectedJutsu.categoryKey && jutsu.id === selectedJutsu.id,
        onClick: () => handleJutsuSelect(category.key, jutsu),
        hotkeyDisplay: `${category.initial}${categoryJutsuCount}`
      });
    }));
  }), player.hasBloodline && battle.playerBloodlineJutsu.length > 0 && /*#__PURE__*/React.createElement("div", {
    className: "jutsuCategory"
  }, battle.playerBloodlineJutsu.map((jutsu, i) => /*#__PURE__*/React.createElement(Jutsu, {
    key: i,
    jutsu: jutsu,
    selected: 'bloodline' === selectedJutsu.categoryKey && jutsu.id === selectedJutsu.id,
    onClick: () => handleJutsuSelect('bloodline', jutsu),
    hotkeyDisplay: `B${i}`,
    isBloodline: true
  }))));
}

function Jutsu({
  jutsu,
  selected,
  onClick,
  hotkeyDisplay,
  isBloodline = false
}) {
  const classes = ['jutsuButton', isBloodline ? 'bloodline_jutsu' : jutsu.jutsuType, selected ? 'selected' : ''];
  return /*#__PURE__*/React.createElement("div", {
    className: classes.join(' '),
    onClick: onClick,
    "aria-disabled": jutsu.activeCooldownTurnsLeft > 0
  }, jutsu.name, /*#__PURE__*/React.createElement("br", null), jutsu.activeCooldownTurnsLeft > 0 ? /*#__PURE__*/React.createElement("span", null, `(CD: ${jutsu.activeCooldownTurnsLeft} turns)`) : /*#__PURE__*/React.createElement("strong", null, hotkeyDisplay));
}