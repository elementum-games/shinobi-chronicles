import FighterDisplay from "./FighterDisplay.js";
import BattleField from "./BattleField.js";
import BattleLog from "./BattleLog.js";
import BattleActionPrompt from "./BattleActionPrompt.js";

function Battle({
  battle,
  membersLink
}) {
  return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement(FightersAndField, {
    battle: battle,
    membersLink: membersLink
  }), battle.isSpectating && /*#__PURE__*/React.createElement(SpectateStatus, null), !battle.isSpectating && !battle.isComplete && /*#__PURE__*/React.createElement(BattleActionPrompt, {
    battle: battle
  }), battle.lastTurnText != null && /*#__PURE__*/React.createElement(BattleLog, {
    lastTurnText: battle.lastTurnText
  }));
} // Fighters and Field


function FightersAndField({
  battle,
  membersLink
}) {
  const player = battle.fighters[battle.playerId];
  const opponent = battle.fighters[battle.opponentId];
  const {
    fighters,
    field,
    isSpectating,
    isMovementPhase
  } = battle;
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
  }, opponent.name))), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement(FighterDisplay, {
    fighter: player,
    showChakra: !isSpectating
  })), /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement(FighterDisplay, {
    fighter: opponent,
    isOpponent: true,
    showChakra: !isSpectating
  }))), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
    colSpan: "2"
  }, /*#__PURE__*/React.createElement(BattleField, {
    fighters: fighters,
    tiles: field.tiles,
    isMovementPhase: isMovementPhase
  })))));
}

function SpectateStatus() {
  return /*#__PURE__*/React.createElement("div", null, "Spectate Status");
  /*
      <table class='table' style='margin-top:2px;'>
      <tr><td style='text-align:center;'>
          <?php if($battle->winner == Battle::TEAM1): ?>
             <?=  $battle->player1->getName() ?> won!
          <?php elseif($battle->winner == Battle::TEAM2): ?>
              <?= $battle->player2->getName() ?> won!
          <?php elseif($battle->winner == Battle::DRAW): ?>
              Fight ended in a draw.
          <?php else: ?>
              <b><?= $battle->timeRemaining() ?></b> seconds remaining<br />
              <a href='<?= $refresh_link ?>'>Refresh</a>
          <?php endif; ?>
      </td></tr>
  </table>
     */
}

window.Battle = Battle;