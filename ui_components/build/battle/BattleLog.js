export default function BattleLog({
  lastTurnLog,
  leftFighterId,
  rightFighterId
}) {
  if (lastTurnLog == null) {
    return null;
  }

  return /*#__PURE__*/React.createElement("table", {
    className: "table"
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, "Last turn")), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "inline-flex",
      width: "49.9%",
      justifyContent: "center",
      borderRight: "1px solid rgba(0,0,0,0.1)"
    }
  }, Object.keys(lastTurnLog.fighterActions).filter(fighterId => fighterId === leftFighterId).map((fighterId, i) => {
    return /*#__PURE__*/React.createElement(FighterAction, {
      key: `battleLog:fighterAction:${fighterId}`,
      action: lastTurnLog.fighterActions[fighterId],
      isAttackPhase: lastTurnLog.isAttackPhase
    });
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      display: "inline-flex",
      width: "49.9%",
      justifyContent: "center"
    }
  }, Object.keys(lastTurnLog.fighterActions).filter(fighterId => fighterId === rightFighterId).map((fighterId, i) => {
    return /*#__PURE__*/React.createElement(FighterAction, {
      key: `battleLog:fighterAction:${fighterId}`,
      action: lastTurnLog.fighterActions[fighterId],
      isAttackPhase: lastTurnLog.isAttackPhase
    });
  }))))));
}

function FighterAction({
  action,
  isAttackPhase
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "fighterActions"
  }, /*#__PURE__*/React.createElement("p", {
    className: "actionDescription"
  }, action.actionDescription), action.hits.map((hit, i) => {
    return /*#__PURE__*/React.createElement("p", {
      key: i,
      className: `hit ${hit.damageType}Damage`,
      style: {
        fontWeight: 'bold'
      }
    }, hit.attackerName, " deals ", hit.damage, " ", hit.damageType, " damage to ", hit.targetName, ".");
  }), isAttackPhase && action.hits.length < 1 && /*#__PURE__*/React.createElement("p", {
    style: {
      fontStyle: 'italic'
    }
  }, "The attack missed."), action.effectHits.map((effectHit, i) => /*#__PURE__*/React.createElement("p", {
    key: `effectHit:${i}`,
    className: `effectHit ${styleForEffectHitType(effectHit.type)}`
  }, effectHit.description)), action.newEffectAnnouncements.map((announcement, i) => /*#__PURE__*/React.createElement("p", {
    className: "effectAnnouncement"
  }, announcement)));
}

function styleForEffectHitType(effectHitType) {
  switch (effectHitType) {
    case 'heal':
      return 'heal';

    case 'ninjutsu_damage':
      return 'ninjutsuDamage';

    case 'taijutsu_damage':
      return 'taijutsuDamage';

    case 'genjutsu_damage':
      return 'genjutsuDamage';

    default:
      return '';
  }
}