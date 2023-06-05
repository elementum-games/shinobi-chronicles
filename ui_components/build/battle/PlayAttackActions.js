export function PlayAttackActions({
  lastTurnLog,
  tileSize,
  fighterLocations,
  getBoundingRectForTile
}) {
  const turnNumber = lastTurnLog?.turnNumber || 0;
  const [prevTurnNumber, setPrevTurnNumber] = React.useState(turnNumber);
  const [attacksToRender, setAttacksToRender] = React.useState([]);

  if (lastTurnLog == null) {
    return null;
  }

  function addAttackToRender(attack) {
    setAttacksToRender(prevValue => [...prevValue, attack]);
  }

  if (prevTurnNumber !== turnNumber) {
    setPrevTurnNumber(turnNumber);
    setAttacksToRender([]);

    if (lastTurnLog.isAttackPhase) {
      Object.keys(lastTurnLog.fighterActions).forEach(key => {
        addAttackToRender(lastTurnLog.fighterActions[key]);
      });
    }
  }

  console.log('attacksToRender', attacksToRender);
  return /*#__PURE__*/React.createElement(React.Fragment, null, attacksToRender.map((attack, i) => {
    if (attack.jutsuUseType === 'projectile') {
      return /*#__PURE__*/React.createElement(ProjectileAttack, {
        key: `attack:${i}`,
        attackIndex: i,
        attack: attack,
        tileSize: tileSize,
        getBoundingRectForTile: getBoundingRectForTile,
        fighterLocations: fighterLocations
      });
    }
  }));
}

function ProjectileAttack({
  attackIndex,
  attack,
  tileSize,
  getBoundingRectForTile,
  fighterLocations
}) {
  const initialTravelTime = 200;
  const travelTimePerTile = 400;
  const startingTileIndex = attack.pathSegments[0].tileIndex;
  const endingTileIndex = attack.pathSegments[attack.pathSegments.length - 1].tileIndex;
  const startingTileRect = getBoundingRectForTile(startingTileIndex);
  const endingTileRect = getBoundingRectForTile(endingTileIndex);
  const direction = startingTileIndex > fighterLocations[attack.fighterId] ? "right" : "left"; // move attack start 0.5 tile closer to caster

  const leftOffset = (fighterLocations[attack.fighterId] - startingTileIndex) * 0.5 * tileSize;
  const leftDifference = endingTileRect.left - (startingTileRect.left + leftOffset);
  const durationMs = initialTravelTime + Math.abs(endingTileIndex - startingTileIndex) * travelTimePerTile;
  const ninjutsuElementImages = {
    "Fire": '/images/battle/fireball.png',
    "Earth": '/images/battle/rock.png',
    "Wind": '/images/battle/fireball.png',
    "Water": '/images/battle/fireball.png',
    "Lightning": '/images/battle/fireball.png',
    "None": '/images/battle/fireball.png'
  };
  const attackImage = ninjutsuElementImages[attack.jutsuElement]; // TODO: Taijutsu/Genjutsu

  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("style", null, `
                @keyframes attack_${attackIndex} {
                    0% {
                        transform: translateX(0px);
                        opacity: 0;
                    }
                    10% {
                        transform: translateX(0px);
                        opacity: 1;
                    }
                    85% {
                        transform: translateX(${leftDifference}px);
                        opacity: 1;
                    }
                    100% {
                        transform: translateX(${leftDifference}px);
                        opacity: 0;
                    }
                }
            `), /*#__PURE__*/React.createElement("div", {
    className: "attackDisplay",
    style: {
      top: startingTileRect.top,
      left: startingTileRect.left + leftOffset,
      width: startingTileRect.width,
      height: startingTileRect.height,
      animationName: `attack_${attackIndex}`,
      animationDuration: `${durationMs}ms`,
      animationFillMode: "forwards",
      animationTimingFunction: "linear"
    }
  }, /*#__PURE__*/React.createElement("img", {
    src: attackImage,
    className: `projectile ${direction}`
  })));
}