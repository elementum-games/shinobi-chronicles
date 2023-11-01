import { CharacterAvatar } from "../CharacterAvatar.js";
import RadarNinjaChart from '../charts/Chart.js';

function Profile({
  isDevEnvironment,
  links,
  playerData,
  playerStats,
  playerSettings,
  playerDailyTasks,
  playerAchievements
}) {
  //Chart.js variables
  const [showChart, setShowChart] = React.useState(false);

  function handleShowGraph() {
    setShowChart(!showChart);
  } //marginRight temp fix for wrapping to same row as chart when window width changes


  let showChartButtonStyle = {
    display: 'block',
    marginRight: '75%',
    backgroundColor: 'rgb(20, 19, 23)',
    color: 'rgb(209, 197, 173)',
    borderRadius: '12px 12px 0 0',
    marginTop: '10px'
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "profile_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "profile_row_first"
  }, /*#__PURE__*/React.createElement(StatusAttributes, {
    playerData: playerData,
    links: links,
    playerSettings: playerSettings
  })), isDevEnvironment && /*#__PURE__*/React.createElement("button", {
    style: showChartButtonStyle,
    onClick: handleShowGraph
  }, !showChart ? "Show Graph" : "Show Stats"), /*#__PURE__*/React.createElement("div", {
    className: "profile_row_second"
  }, !showChart ? /*#__PURE__*/React.createElement(PlayerStats, {
    playerData: playerData,
    playerStats: playerStats
  }) : /*#__PURE__*/React.createElement(RadarNinjaChart, {
    playerStats: playerStats
  }), /*#__PURE__*/React.createElement("div", {
    className: "profile_row_second_col2"
  }, /*#__PURE__*/React.createElement(PlayerUserRep, {
    playerData: playerData
  }), /*#__PURE__*/React.createElement(PlayerBloodline, {
    bloodlinePageUrl: links.bloodlinePage,
    buyBloodlineUrl: links.buyBloodline,
    playerData: playerData
  }), /*#__PURE__*/React.createElement(DailyTasks, {
    dailyTasks: playerDailyTasks
  }))), /*#__PURE__*/React.createElement("div", {
    className: "profile_row_third"
  }, /*#__PURE__*/React.createElement("h2", null, "Achievements"), /*#__PURE__*/React.createElement(PlayerAchievements, {
    playerAchievements: playerAchievements
  })));
}

function StatusAttributes({
  playerData,
  playerSettings,
  links
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "status_attributes_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "status_attributes box-primary"
  }, /*#__PURE__*/React.createElement("div", {
    className: "name_row ft-c1"
  }, /*#__PURE__*/React.createElement("div", {
    className: "player_avatar_name_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "profile_avatar_container"
  }, /*#__PURE__*/React.createElement(CharacterAvatar, {
    imageSrc: playerData.avatar_link,
    maxWidth: playerData.avatar_size * 0.5,
    maxHeight: playerData.avatar_size * 0.5,
    avatarStyle: playerSettings.avatar_style,
    frameClassNames: ["profile_avatar_frame", playerSettings.avatar_frame]
  })), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("h2", {
    className: "player_name"
  }, playerData.user_name), /*#__PURE__*/React.createElement("span", {
    className: "player_title ft-p"
  }, playerData.rank_name, " lvl ", playerData.level))), /*#__PURE__*/React.createElement("div", {
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
      flexBasis: "28%"
    }
  }, /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, "Money:"), /*#__PURE__*/React.createElement("span", null, "\xA5", playerData.money.toLocaleString())), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, "Ancient Kunai:"), /*#__PURE__*/React.createElement("span", null, playerData.premiumCredits.toLocaleString())), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, "AK Purchased:"), /*#__PURE__*/React.createElement("span", null, playerData.premiumCreditsPurchased.toLocaleString()))), /*#__PURE__*/React.createElement("div", {
    className: "status_info_section section3",
    style: {
      flexBasis: "34%"
    }
  }, /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, "Village:"), playerData.villageName), playerData.clanId != null && /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, "Clan:"), /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("a", {
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
  }, "Total stats trained: ", playerData.totalStats.toLocaleString(), " / ", playerData.totalStatCap.toLocaleString()), /*#__PURE__*/React.createElement("div", {
    className: "progress_bar_container total_stats_bar_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "progress_bar_fill",
    style: {
      width: `${totalStatsPercent}%`
    }
  }))), /*#__PURE__*/React.createElement("div", {
    className: "stat_lists"
  }, /*#__PURE__*/React.createElement("div", {
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
  }, "Increases control and mastery over one's bloodline."))), /*#__PURE__*/React.createElement("div", {
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
  })), /*#__PURE__*/React.createElement("div", {
    className: "stat box-secondary"
  }, /*#__PURE__*/React.createElement("h3", null, "Willpower: ", playerStats.willpower.toLocaleString()), /*#__PURE__*/React.createElement("div", {
    className: "badge"
  }, "\u6839\u6027"), /*#__PURE__*/React.createElement("span", {
    className: "ft-c3"
  })))));
}

function PlayerBloodline({
  playerData,
  bloodlinePageUrl,
  buyBloodlineUrl
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "bloodline_display"
  }, /*#__PURE__*/React.createElement("div", {
    className: "bloodline_mastery_indicator"
  }, playerData.has_bloodline ? /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/bloodline/level3.png"
  }) : /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/bloodline/inactive.png"
  })), /*#__PURE__*/React.createElement("div", {
    className: "bloodline_name ft-c3"
  }, "Bloodline:\xA0", playerData.bloodlineName != null ? /*#__PURE__*/React.createElement("a", {
    href: bloodlinePageUrl
  }, playerData.bloodlineName.replace(/&#039;/g, "'")) : /*#__PURE__*/React.createElement("a", {
    href: buyBloodlineUrl
  }, "None")));
}

function PlayerUserRep({
  playerData
}) {
  let img_link = "images/village_icons/" + playerData.villageName.toLowerCase() + ".png";
  return /*#__PURE__*/React.createElement("div", {
    className: "reputation_display"
  }, /*#__PURE__*/React.createElement("div", {
    className: "reputation_indicator"
  }, /*#__PURE__*/React.createElement("img", {
    src: img_link
  }), /*#__PURE__*/React.createElement("span", {
    className: "village_name"
  }, playerData.villageName)), /*#__PURE__*/React.createElement("div", {
    className: "reputation_info ft-c3"
  }, /*#__PURE__*/React.createElement("span", {
    className: "reputation_name"
  }, /*#__PURE__*/React.createElement("b", null, playerData.villageRepTier), "\xA0(", playerData.villageRep, " rep)"), /*#__PURE__*/React.createElement("span", {
    className: "weekly_reputation"
  }, playerData.weeklyPveRep, "/", playerData.maxWeeklyPveRep, " PvE \xA0|\xA0", playerData.weeklyWarRep, "/", playerData.maxWeeklyWarRep, " War \xA0|\xA0", playerData.weeklyPvpRep, "/", playerData.maxWeeklyPvpRep, " PvP")));
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

function PlayerAchievements({
  playerAchievements
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "achievements_container"
  }, playerAchievements.completedAchievements.map(achievement => /*#__PURE__*/React.createElement("div", {
    key: `achievement:${achievement.id}`,
    className: "achievement completed box-secondary"
  }, /*#__PURE__*/React.createElement("span", {
    className: "achievement_name"
  }, achievement.name), /*#__PURE__*/React.createElement("span", {
    className: "achievement_prompt"
  }, achievement.prompt), /*#__PURE__*/React.createElement("div", {
    className: "achievement_progress"
  }, /*#__PURE__*/React.createElement("div", {
    className: "progress_bar_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "progress_bar_fill",
    style: {
      width: `${achievement.progressPercent}%`
    }
  })), /*#__PURE__*/React.createElement("span", {
    className: "progress_label"
  }, achievement.progressLabel)))));
}

window.Profile = Profile;