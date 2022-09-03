export function JutsuInput({
  battle,
  player,
  onChange
}) {
  const [selectedCategory, setSelectedCategory] = React.useState(null);
  const playerJutsu = [...battle.playerDefaultAttacks, ...battle.playerEquippedJutsu];
  const jutsuCategories = {
    ninjutsu: {
      key: 'ninjutsu',
      initial: 'Q',
      jutsu: playerJutsu.filter(jutsu => jutsu.jutsuType === battle.jutsuTypes.ninjutsu)
    },
    taijutsu: {
      key: 'taijutsu',
      initial: 'W',
      jutsu: playerJutsu.filter(jutsu => jutsu.jutsuType === battle.jutsuTypes.taijutsu)
    },
    genjutsu: {
      key: 'genjutsu',
      initial: 'E',
      jutsu: playerJutsu.filter(jutsu => jutsu.jutsuType === battle.jutsuTypes.genjutsu)
    },
    bloodline: {
      key: 'bloodline',
      initial: 'R',
      jutsu: battle.playerBloodlineJutsu
    }
  };
  const jutsuCategoryKeys = ['ninjutsu', 'taijutsu', 'genjutsu', 'bloodline'];
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

  const handleKeyDown = event => {
    // $FlowIssue[incompatible-type]: Flow doesn't infer Object.values return type from the argument
    for (const category of Object.values(jutsuCategories)) {
      if (event.key === category.initial.toLowerCase() || event.key === category.initial.toUpperCase()) {
        setSelectedCategory(category.key);
        return;
      }
    } // Check for jutsu select


    let numericKey = parseInt(event.key);

    if (!isNaN(numericKey) && selectedCategory != null) {
      // Offset to avoid using 0 as a visible number
      numericKey -= 1;
      const category = jutsuCategories[selectedCategory];

      if (category.jutsu[numericKey] != null) {
        handleJutsuSelect(selectedCategory, category.jutsu[numericKey]);
      }
    }
  };

  React.useEffect(() => {
    document.addEventListener('keydown', handleKeyDown);
    return () => document.removeEventListener('keydown', handleKeyDown);
  });
  return /*#__PURE__*/React.createElement("div", {
    id: "jutsuContainer"
  }, jutsuCategoryKeys.map(categoryKey => {
    const category = jutsuCategories[categoryKey];

    if (category.key === 'bloodline' && !player.hasBloodline) {
      return null;
    }

    return /*#__PURE__*/React.createElement("div", {
      className: "jutsuCategory",
      key: categoryKey
    }, category.jutsu.map((jutsu, i) => {
      return /*#__PURE__*/React.createElement(Jutsu, {
        key: i,
        jutsu: jutsu,
        selected: category.key === selectedJutsu.categoryKey && jutsu.id === selectedJutsu.id,
        onClick: () => handleJutsuSelect(category.key, jutsu),
        hotkeyDisplay: category.key === selectedCategory ? `${i + 1}` : `${category.initial}${i + 1}`,
        isBloodline: category.key === 'bloodline'
      });
    }));
  }));
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