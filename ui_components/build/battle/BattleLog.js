export default function BattleLog({
  lastTurnLog
}) {
  if (lastTurnLog == null) {
    return null;
  }

  return /*#__PURE__*/React.createElement("table", {
    className: "table"
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, "Last turn")), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
    style: {
      textAlign: "center"
    }
  }, Object.keys(lastTurnLog.fighterActions).map((fighterId, i) => {
    const action = lastTurnLog.fighterActions[fighterId];
    return /*#__PURE__*/React.createElement("div", {
      key: i,
      style: {
        borderBottom: '1px solid rgba(0,0,0,0.5)'
      }
    }, /*#__PURE__*/React.createElement("p", null, action.actionDescription), /*#__PURE__*/React.createElement("br", null), action.hits.map((hit, i) => {
      return /*#__PURE__*/React.createElement("p", {
        key: i,
        className: `${hit.damageType}Damage`,
        style: {
          fontWeight: 'bold'
        }
      }, hit.attackerName, " deals ", hit.damage, " ", hit.damageType, " damage to ", hit.targetName, ".");
    }), lastTurnLog.isAttackPhase && action.hits.length < 1 && /*#__PURE__*/React.createElement("p", {
      key: i,
      style: {
        fontStyle: 'italic'
      }
    }, "The attack missed."), action.appliedEffectDescriptions.map((description, i) => /*#__PURE__*/React.createElement("p", {
      key: i
    }, description)), action.newEffectAnnouncements.map((announcement, i) => /*#__PURE__*/React.createElement("p", {
      key: i,
      style: {
        fontWeight: 'italic',
        marginTop: '3px'
      }
    }, announcement)));
  })))));
}