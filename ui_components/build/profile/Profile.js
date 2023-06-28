import { CharacterAvatar } from "../CharacterAvatar.js";

function Profile({
  playerData,
  playerSettings
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "profile_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "profile_avatar_container"
  }, /*#__PURE__*/React.createElement(CharacterAvatar, {
    imageSrc: playerData.avatar_link,
    maxWidth: playerData.avatar_size,
    maxHeight: playerData.avatar_size,
    avatarStyle: playerSettings.avatar_style
  })), /*#__PURE__*/React.createElement("div", {
    className: "status_attributes box-primary"
  }), /*#__PURE__*/React.createElement("div", {
    className: "stats_container"
  }, /*#__PURE__*/React.createElement("h2", null, "Character stats"), /*#__PURE__*/React.createElement("div", {
    className: "total_stats box-primary"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Total stats trained: 25 000 / 25 000"), /*#__PURE__*/React.createElement("br", null), "[resource bar]"), /*#__PURE__*/React.createElement("div", {
    className: "stat_list skills"
  }, /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Ninjutsu skill: 138"), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u5FCD\u8853"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Focuses on the use of hand signs and chakra based/elemental attacks.")), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Taijutsu skill: 138"), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u4F53\u8853"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Focuses on the use of hand to hand combat and various weapon effects.")), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Genjutsu skill: 138"), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u5E7B\u8853"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Focuses on the use of illusions and high residual damage.")), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Bloodline skill: 138"), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u8840\u7D99"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Increases the control over one's bloodline.", /*#__PURE__*/React.createElement("br", null), "Helps with its mastery."))), /*#__PURE__*/React.createElement("div", {
    className: "stat_list attributes"
  }, /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Cast speed: 138"), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u5370\u8853"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Increases Ninjutsu/Genjutsu attack speed, affecting damage dealt and received.")), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Speed: 138"), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u901F\u5EA6"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Increases Taijutsu attack speed, affecting damage dealt and received.")), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Intelligence: 138"), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u77E5\u80FD"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "lol")), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Willpower: 138"), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u6839\u6027"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "nope")))));
}

window.Profile = Profile;