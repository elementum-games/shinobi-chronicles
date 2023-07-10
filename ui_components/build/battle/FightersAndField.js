// Fighters and Field
import FighterDisplay from "./FighterDisplay.js";
import BattleField from "./BattleField.js";
export function FightersAndField({
  battle,
  attackInput,
  membersLink,
  isSelectingTile,
  selectedJutsu,
  onTileSelect
}) {
  const player = battle.fighters[battle.playerId];
  const opponent = battle.fighters[battle.opponentId];
  const {
    fighters,
    field,
    isSpectating
  } = battle;

  const handleTileSelect = tileIndex => {
    onTileSelect(tileIndex);
  };

  let status = '';

  if (battle.isPreparationPhase) {
    status = 'Prepare to Fight';
  } else if (battle.isMovementPhase) {
    status = 'Setup / Move';
  } else {
    status = 'Attack';
  }

  return /*#__PURE__*/React.createElement("table", {
    className: "table"
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", {
    style: {
      width: "50%"
    }
  }, /*#__PURE__*/React.createElement("a", {
    href: `${membersLink}}&user=${player.name}`,
    style: {
      textDecoration: "none"
    }
  }, player.name)), /*#__PURE__*/React.createElement("th", {
    style: {
      width: "50%"
    }
  }, opponent.isNpc ? opponent.name : /*#__PURE__*/React.createElement("a", {
    href: `${membersLink}}&user=${opponent.name}`,
    style: {
      textDecoration: "none"
    }
  }, opponent.name))), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
    colSpan: "2"
  }, /*#__PURE__*/React.createElement("div", {
    className: "fightersRow"
  }, /*#__PURE__*/React.createElement(FighterDisplay, {
    fighter: player,
    showChakra: !isSpectating
  }), /*#__PURE__*/React.createElement("div", {
    className: "battleStatus"
  }, /*#__PURE__*/React.createElement(TimeRemaining, {
    turnCount: battle.turnCount,
    turnSecondsRemaining: battle.turnSecondsRemaining
  }), /*#__PURE__*/React.createElement("div", {
    className: "status"
  }, status)), /*#__PURE__*/React.createElement(FighterDisplay, {
    fighter: opponent,
    isOpponent: true,
    showChakra: !isSpectating
  })))), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
    colSpan: "2"
  }, /*#__PURE__*/React.createElement(BattleField, {
    player: player,
    fighters: fighters,
    tiles: field.tiles,
    fighterLocations: field.fighterLocations,
    selectedJutsu: selectedJutsu,
    isMovementPhase: battle.isMovementPhase,
    lastTurnLog: battle.lastTurnLog,
    onTileSelect: handleTileSelect
  })))));
}

function TimeRemaining({
  turnSecondsRemaining,
  turnCount
}) {
  const [secondsRemaining, setSecondsRemaining] = React.useState(turnSecondsRemaining);
  React.useEffect(() => {
    setSecondsRemaining(turnSecondsRemaining);
  }, [turnCount]);
  React.useEffect(() => {
    const decrementTimeRemaining = () => {
      setSecondsRemaining(prevSeconds => prevSeconds <= 0 ? 0 : prevSeconds - 1);
    };

    const intervalId = setInterval(decrementTimeRemaining, 1000);
    return () => clearInterval(intervalId);
  }, []);
  return /*#__PURE__*/React.createElement("div", {
    className: "turnTimeLeft"
  }, secondsRemaining);
}