import { FighterAvatar } from "./FighterAvatar.js";
import { ResourceBar } from "./ResourceBar.js";
const styles = {
  fighterDisplay: {
    display: "flex",
    flexDirection: "row",
    gap: "8px"
  },
  opponent: {
    flexDirection: "row-reverse"
  }
};
export function FighterDisplay({
  fighter,
  showChakra,
  isOpponent
}) {
  const containerStyles = { ...styles.fighterDisplay,
    ...(isOpponent && styles.opponent)
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "fighterDisplay",
    style: containerStyles
  }, /*#__PURE__*/React.createElement(FighterAvatar, {
    fighterName: fighter.name,
    avatarLink: fighter.avatarLink,
    maxAvatarSize: 125
  }), /*#__PURE__*/React.createElement("div", {
    className: "resourceBars"
  }, /*#__PURE__*/React.createElement(ResourceBar, {
    currentAmount: fighter.health,
    maxAmount: fighter.maxHealth,
    resourceType: "health"
  }), !showChakra && /*#__PURE__*/React.createElement(ResourceBar, {
    currentAmount: 40,
    maxAmount: 100,
    resourceType: "chakra"
  })));
}
export function FighterDisplays({
  membersLink,
  player,
  opponent,
  isSpectating
}) {
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
    showChakra: isSpectating
  })), /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement(FighterDisplay, {
    fighter: opponent,
    isOpponent: true,
    showChakra: false
  }))), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
    colSpan: "2"
  }))));
}