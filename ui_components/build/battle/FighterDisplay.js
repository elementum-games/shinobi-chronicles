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