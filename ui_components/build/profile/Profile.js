import { CharacterAvatar } from "../CharacterAvatar.js";

function Profile({
  playerData,
  playerStats,
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
  })), /*#__PURE__*/React.createElement(StatusAttributes, {
    playerData: playerData
  }), /*#__PURE__*/React.createElement(PlayerStats, {
    playerData: playerData,
    playerStats: playerStats
  }));
}

function StatusAttributes({
  playerData
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "status_attributes box-primary"
  }, /*#__PURE__*/React.createElement("div", {
    className: "name_row ft-c1"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("h2", {
    className: "player_name"
  }, playerData.user_name), /*#__PURE__*/React.createElement("span", {
    className: "player_title ft-p"
  }, playerData.rank_name, " lvl ", playerData.level)), /*#__PURE__*/React.createElement("div", {
    className: "player_badges"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/decorations/red_diamond.png"
  }), /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/decorations/red_diamond.png"
  }), /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/decorations/red_diamond.png"
  }), /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/decorations/red_diamond.png"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "exp_section ft-c3 ft-p ft-small"
  }, /*#__PURE__*/React.createElement("div", {
    className: "exp_bar_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "exp_bar_fill",
    style: {
      width: `${playerData.nextLevelProgressPercent}%`
    }
  })), /*#__PURE__*/React.createElement("span", null, "TOTAL EXP: ", playerData.exp), /*#__PURE__*/React.createElement("span", null, "NEXT LEVEL IN ", Math.max(playerData.expForNextLevel - playerData.exp, 0), " EXP")), /*#__PURE__*/React.createElement("div", {
    className: "status_info_sections ft-c3"
  }, /*#__PURE__*/React.createElement("div", {
    className: "status_info_section",
    style: {
      width: 220
    }
  }, /*#__PURE__*/React.createElement("span", null, "Gender: ", playerData.gender), /*#__PURE__*/React.createElement("span", null, "Element: ", playerData.elements.join(", ")), /*#__PURE__*/React.createElement("span", null, "Money: ", playerData.money, " yen")), /*#__PURE__*/React.createElement("div", {
    className: "status_info_section",
    style: {
      width: 170
    }
  }, /*#__PURE__*/React.createElement("span", null, "Village: ", playerData.villageName), playerData.clanId != null && /*#__PURE__*/React.createElement("span", null, "Clan: ", playerData.clanName), /*#__PURE__*/React.createElement("span", null, "Ancient Kunai: ", playerData.premiumCredits)), /*#__PURE__*/React.createElement("div", {
    className: "status_info_section",
    style: {
      width: 230
    }
  }, /*#__PURE__*/React.createElement("span", null, "Team: ", playerData.teamId == null ? "None" : playerData.teamName), /*#__PURE__*/React.createElement("span", null, "Forbidden Seal: ", playerData.forbiddenSealName))));
}

function PlayerStats({
  playerData,
  playerStats
}) {
  const totalStatsPercent = Math.round(playerData.totalStats / playerData.totalStatCap * 1000) / 10;
  return /*#__PURE__*/React.createElement("div", {
    className: "stats_container"
  }, /*#__PURE__*/React.createElement("h2", null, "Character stats"), /*#__PURE__*/React.createElement("div", {
    className: "total_stats box-primary"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Total stats trained: ", playerData.totalStats, " / ", playerData.totalStatCap), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("div", {
    className: "total_stats_bar_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "total_stats_bar_fill",
    style: {
      width: `${totalStatsPercent}%`
    }
  }))), /*#__PURE__*/React.createElement("div", {
    className: "stat_list skills"
  }, /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Ninjutsu skill: ", playerStats.ninjutsuSkill), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u5FCD\u8853"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Focuses on the use of hand signs and chakra based/elemental attacks.")), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Taijutsu skill: ", playerStats.taijutsuSkill), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u4F53\u8853"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Focuses on the use of hand to hand combat and various weapon effects.")), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Genjutsu skill: ", playerStats.genjutsuSkill), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u5E7B\u8853"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Focuses on the use of illusions and high residual damage.")), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Bloodline skill: ", playerStats.bloodlineSkill), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u8840\u7D99"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Increases the control over one's bloodline.", /*#__PURE__*/React.createElement("br", null), "Helps with its mastery."))), /*#__PURE__*/React.createElement("div", {
    className: "stat_list attributes"
  }, /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Cast speed: ", playerStats.castSpeed), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u5370\u8853"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Increases Ninjutsu/Genjutsu attack speed, affecting damage dealt and received.")), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Speed: ", playerStats.speed), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u901F\u5EA6"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Increases Taijutsu attack speed, affecting damage dealt and received.")), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Intelligence: ", playerStats.intelligence), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u77E5\u80FD"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "lol")), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Willpower: ", playerStats.willpower), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u6839\u6027"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "nope"))));
}

window.Profile = Profile;