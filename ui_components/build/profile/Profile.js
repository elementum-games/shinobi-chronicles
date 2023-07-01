import { CharacterAvatar } from "../CharacterAvatar.js";

function Profile({
  links,
  playerData,
  playerStats,
  playerSettings,
  playerDailyTasks
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "profile_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "profile_row_first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "profile_avatar_container"
  }, /*#__PURE__*/React.createElement(CharacterAvatar, {
    imageSrc: playerData.avatar_link,
    maxWidth: playerData.avatar_size * 0.5,
    maxHeight: playerData.avatar_size * 0.5,
    avatarStyle: playerSettings.avatar_style,
    frameClassNames: ["profile_avatar_frame"]
  })), /*#__PURE__*/React.createElement(StatusAttributes, {
    playerData: playerData,
    links: links
  })), /*#__PURE__*/React.createElement("div", {
    className: "profile_row_second"
  }, /*#__PURE__*/React.createElement(PlayerStats, {
    playerData: playerData,
    playerStats: playerStats
  }), /*#__PURE__*/React.createElement("div", {
    className: "profile_row_second_col2"
  }, /*#__PURE__*/React.createElement(PlayerBloodline, {
    bloodlinePageUrl: links.bloodlinePage
  }), /*#__PURE__*/React.createElement(DailyTasks, {
    dailyTasks: playerDailyTasks
  }))));
}

function StatusAttributes({
  playerData,
  links
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "status_attributes_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
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
  })), /*#__PURE__*/React.createElement("span", null, "TOTAL EXP: ", playerData.exp.toLocaleString()), /*#__PURE__*/React.createElement("span", null, "NEXT LEVEL IN ", Math.max(playerData.expForNextLevel - playerData.exp, 0).toLocaleString(), " EXP")), /*#__PURE__*/React.createElement("div", {
    className: "status_info_sections ft-c3"
  }, /*#__PURE__*/React.createElement("div", {
    className: "status_info_section section1",
    style: {
      flexBasis: "38%"
    }
  }, /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, "Gender:"), /*#__PURE__*/React.createElement("span", null, playerData.gender)), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, "Element", playerData.elements.length > 1 ? "s" : "", ":"), /*#__PURE__*/React.createElement("span", null, playerData.elements.join(", "))), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, "Forbidden Seal:"), playerData.forbiddenSealName ? /*#__PURE__*/React.createElement("span", null, playerData.forbiddenSealName, " (", playerData.forbiddenSealTimeLeft, ")") : /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("a", {
    href: links.buyForbiddenSeal
  }, "None")))), /*#__PURE__*/React.createElement("div", {
    className: "status_info_section section2",
    style: {
      flexBasis: "32%"
    }
  }, /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, "Money:"), /*#__PURE__*/React.createElement("span", null, "\xA5", playerData.money.toLocaleString())), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, "Ancient Kunai:"), /*#__PURE__*/React.createElement("span", null, playerData.premiumCredits.toLocaleString())), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, "AK Purchased:"), /*#__PURE__*/React.createElement("span", null, playerData.premiumCreditsPurchased.toLocaleString()))), /*#__PURE__*/React.createElement("div", {
    className: "status_info_section section3",
    style: {
      flexBasis: "30%"
    }
  }, /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, "Village:"), /*#__PURE__*/React.createElement("span", null, playerData.villageName)), playerData.clanId != null && /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, "Clan:"), /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("a", {
    href: links.clan
  }, playerData.clanName))), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, "Team:"), /*#__PURE__*/React.createElement("span", null, playerData.teamId == null ? "None" : /*#__PURE__*/React.createElement("a", {
    href: links.team
  }, playerData.teamName)))))));
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
  }, "Total stats trained: ", playerData.totalStats.toLocaleString(), " / ", playerData.totalStatCap.toLocaleString()), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("div", {
    className: "progress_bar_container total_stats_bar_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "progress_bar_fill",
    style: {
      width: `${totalStatsPercent}%`
    }
  }))), /*#__PURE__*/React.createElement("div", {
    className: "stat_list skills"
  }, /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Ninjutsu skill: ", playerStats.ninjutsuSkill.toLocaleString()), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u5FCD\u8853"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Focuses on the use of hand signs and chakra based/elemental attacks.")), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Taijutsu skill: ", playerStats.taijutsuSkill.toLocaleString()), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u4F53\u8853"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Focuses on the use of hand to hand combat and various weapon effects.")), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Genjutsu skill: ", playerStats.genjutsuSkill.toLocaleString()), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u5E7B\u8853"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Focuses on the use of illusions and high residual damage.")), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Bloodline skill: ", playerStats.bloodlineSkill.toLocaleString()), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u8840\u7D99"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Increases the control over one's bloodline.", /*#__PURE__*/React.createElement("br", null), "Helps with its mastery."))), /*#__PURE__*/React.createElement("div", {
    className: "stat_list attributes"
  }, /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Cast speed: ", playerStats.castSpeed.toLocaleString()), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u5370\u8853"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Increases Ninjutsu/Genjutsu attack speed, affecting damage dealt and received.")), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Speed: ", playerStats.speed.toLocaleString()), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u901F\u5EA6"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "Increases Taijutsu attack speed, affecting damage dealt and received.")), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Intelligence: ", playerStats.intelligence.toLocaleString()), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u77E5\u80FD"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "lol")), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Willpower: ", playerStats.willpower.toLocaleString()), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u6839\u6027"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  }, "nope"))));
}

function PlayerBloodline({
  bloodlinePageUrl
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "bloodline_display"
  }, /*#__PURE__*/React.createElement("div", {
    className: "bloodline_mastery_indicator"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/bloodline/level3.png"
  })), /*#__PURE__*/React.createElement("div", {
    className: "bloodline_name ft-c3"
  }, "Bloodline:\xA0", /*#__PURE__*/React.createElement("a", {
    href: bloodlinePageUrl
  }, "Ancient's Deception")));
}

function DailyTasks({
  dailyTasks
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "daily_tasks_container"
  }, /*#__PURE__*/React.createElement("h2", null, "Daily tasks"), dailyTasks.map((dailyTask, i) => /*#__PURE__*/React.createElement("div", {
    key: `daily_task:${i}`,
    className: "daily_task"
  }, /*#__PURE__*/React.createElement("h3", null, dailyTask.name), /*#__PURE__*/React.createElement("section", {
    className: "ft-small ft-c3 prompt_rewards"
  }, /*#__PURE__*/React.createElement("span", null, dailyTask.prompt), /*#__PURE__*/React.createElement("p", {
    style: {
      margin: 0,
      textAlign: "right"
    }
  }, /*#__PURE__*/React.createElement("span", null, "\xA5", dailyTask.rewardYen), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("span", null, dailyTask.rewardRep, " rep"))), /*#__PURE__*/React.createElement("section", {
    className: "ft-small ft-c1"
  }, /*#__PURE__*/React.createElement("div", {
    className: "progress_bar_container dark"
  }, /*#__PURE__*/React.createElement("div", {
    className: "progress_bar_fill",
    style: {
      width: `${dailyTask.progressPercent}%`
    }
  })), /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: "6px"
    }
  }, dailyTask.progressCaption)))));
}

window.Profile = Profile;