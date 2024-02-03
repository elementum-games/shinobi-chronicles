import { apiFetch } from "../utils/network.js";
export function WarTable({
  warLogData,
  villageAPI,
  handleErrors,
  getVillageIcon
}) {
  const [playerWarLog, setPlayerWarLog] = React.useState(warLogData.player_war_log);
  const [globalLeaderboardWarLogs, setGlobalLeaderboardWarLogs] = React.useState(warLogData.global_leaderboard_war_logs);
  const [globalLeaderboardPageNumber, setGlobalLeaderboardPageNumber] = React.useState(1);
  function WarLogHeader() {
    return /*#__PURE__*/React.createElement("div", {
      className: "warlog_label_row"
    }, /*#__PURE__*/React.createElement("div", {
      className: "warlog_username_label"
    }), /*#__PURE__*/React.createElement("div", {
      className: "warlog_war_score_label"
    }, "war score"), /*#__PURE__*/React.createElement("div", {
      className: "warlog_pvp_wins_label"
    }, "pvp wins"), /*#__PURE__*/React.createElement("div", {
      className: "warlog_raid_label"
    }, "raid"), /*#__PURE__*/React.createElement("div", {
      className: "warlog_reinforce_label"
    }, "reinforce"), /*#__PURE__*/React.createElement("div", {
      className: "warlog_infiltrate_label"
    }, "infiltrate"), /*#__PURE__*/React.createElement("div", {
      className: "warlog_defense_label"
    }, "def"), /*#__PURE__*/React.createElement("div", {
      className: "warlog_captures_label"
    }, "captures"), /*#__PURE__*/React.createElement("div", {
      className: "warlog_patrols_label"
    }, "patrols"), /*#__PURE__*/React.createElement("div", {
      className: "warlog_resources_label"
    }, "resources"), /*#__PURE__*/React.createElement("div", {
      className: "warlog_chart_label"
    }));
  }
  function WarLog({
    log,
    index,
    animate,
    getVillageIcon
  }) {
    const scoreData = [{
      name: 'Objective Score',
      score: log.objective_score
    }, {
      name: 'Resource Score',
      score: log.resource_score
    }, {
      name: 'Battle Score',
      score: log.battle_score
    }];
    const chart_colors = ['#2b5fca', '#5fca8c', '#d64866'];
    return /*#__PURE__*/React.createElement("div", {
      key: index,
      className: "warlog_item"
    }, /*#__PURE__*/React.createElement("div", {
      className: "warlog_data_row"
    }, log.rank == 1 && /*#__PURE__*/React.createElement("span", {
      className: "warlog_rank_wrapper"
    }, /*#__PURE__*/React.createElement("span", {
      className: "warlog_rank first"
    }, log.rank)), log.rank == 2 && /*#__PURE__*/React.createElement("span", {
      className: "warlog_rank_wrapper"
    }, /*#__PURE__*/React.createElement("span", {
      className: "warlog_rank second"
    }, log.rank)), log.rank == 3 && /*#__PURE__*/React.createElement("span", {
      className: "warlog_rank_wrapper"
    }, /*#__PURE__*/React.createElement("span", {
      className: "warlog_rank third"
    }, log.rank)), log.rank > 3 && /*#__PURE__*/React.createElement("span", {
      className: "warlog_rank_wrapper"
    }, /*#__PURE__*/React.createElement("span", {
      className: "warlog_rank"
    }, log.rank)), /*#__PURE__*/React.createElement("div", {
      className: "warlog_username"
    }, /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("img", {
      src: getVillageIcon(log.village_id)
    })), /*#__PURE__*/React.createElement("a", {
      href: "/?id=6&user=" + log.user_name
    }, log.user_name)), /*#__PURE__*/React.createElement("div", {
      className: "warlog_war_score"
    }, log.war_score), /*#__PURE__*/React.createElement("div", {
      className: "warlog_pvp_wins"
    }, log.pvp_wins), /*#__PURE__*/React.createElement("div", {
      className: "warlog_raid"
    }, /*#__PURE__*/React.createElement("span", null, log.raid_count), /*#__PURE__*/React.createElement("span", {
      className: "warlog_red"
    }, "(", log.damage_dealt, ")")), /*#__PURE__*/React.createElement("div", {
      className: "warlog_reinforce"
    }, /*#__PURE__*/React.createElement("span", null, log.reinforce_count), /*#__PURE__*/React.createElement("span", {
      className: "warlog_green"
    }, "(", log.damage_healed, ")")), /*#__PURE__*/React.createElement("div", {
      className: "warlog_infiltrate"
    }, log.infiltrate_count), /*#__PURE__*/React.createElement("div", {
      className: "warlog_defense"
    }, /*#__PURE__*/React.createElement("span", {
      className: "warlog_green"
    }, "+", log.defense_gained), /*#__PURE__*/React.createElement("span", {
      className: "warlog_red"
    }, "-", log.defense_reduced)), /*#__PURE__*/React.createElement("div", {
      className: "warlog_captures"
    }, log.villages_captured + log.regions_captured), /*#__PURE__*/React.createElement("div", {
      className: "warlog_patrols"
    }, log.patrols_defeated), /*#__PURE__*/React.createElement("div", {
      className: "warlog_resources"
    }, log.resources_stolen), /*#__PURE__*/React.createElement("div", {
      className: "warlog_chart"
    }, /*#__PURE__*/React.createElement(Recharts.PieChart, {
      width: 50,
      height: 50
    }, /*#__PURE__*/React.createElement(Recharts.Pie, {
      isAnimationActive: animate,
      stroke: "none",
      data: scoreData,
      dataKey: "score",
      outerRadius: 16,
      fill: "green"
    }, scoreData.map((entry, index) => /*#__PURE__*/React.createElement(Recharts.Cell, {
      key: `cell-${index}`,
      fill: chart_colors[index % chart_colors.length]
    })))), /*#__PURE__*/React.createElement("div", {
      className: "warlog_chart_tooltip"
    }, /*#__PURE__*/React.createElement("div", {
      className: "warlog_chart_tooltip_row"
    }, /*#__PURE__*/React.createElement("svg", {
      width: "12",
      height: "12"
    }, /*#__PURE__*/React.createElement("rect", {
      width: "100",
      height: "100",
      fill: "#2b5fca"
    })), /*#__PURE__*/React.createElement("div", null, "Objective score (", Math.round(log.objective_score / Math.max(log.war_score, 1) * 100), "%)")), /*#__PURE__*/React.createElement("div", {
      className: "warlog_chart_tooltip_row"
    }, /*#__PURE__*/React.createElement("svg", {
      width: "12",
      height: "12"
    }, /*#__PURE__*/React.createElement("rect", {
      width: "100",
      height: "100",
      fill: "#5fca8c"
    })), /*#__PURE__*/React.createElement("div", null, "Resource score (", Math.round(log.resource_score / Math.max(log.war_score, 1) * 100), "%)")), /*#__PURE__*/React.createElement("div", {
      className: "warlog_chart_tooltip_row"
    }, /*#__PURE__*/React.createElement("svg", {
      width: "12",
      height: "12"
    }, /*#__PURE__*/React.createElement("rect", {
      width: "100",
      height: "100",
      fill: "#d64866"
    })), /*#__PURE__*/React.createElement("div", null, "Battle score (", Math.round(log.battle_score / Math.max(log.war_score, 1) * 100), "%)"))))));
  }
  const GlobalLeaderboardNextPage = page_number => {
    apiFetch(villageAPI, {
      request: 'GetGlobalWarLeaderboard',
      page_number: page_number
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      if (response.data.warLogData.global_leaderboard_war_logs.length == 0) {
        return;
      } else {
        setGlobalLeaderboardPageNumber(page_number);
        setGlobalLeaderboardWarLogs(response.data.warLogData.global_leaderboard_war_logs);
      }
    });
  };
  const GlobalLeaderboardPreviousPage = page_number => {
    if (page_number > 0) {
      apiFetch(villageAPI, {
        request: 'GetGlobalWarLeaderboard',
        page_number: page_number
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }
        setGlobalLeaderboardPageNumber(page_number);
        setGlobalLeaderboardWarLogs(response.data.warLogData.global_leaderboard_war_logs);
      });
    }
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "wartable_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "row first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "your war score"), /*#__PURE__*/React.createElement("div", {
    className: "player_warlog_container"
  }, /*#__PURE__*/React.createElement(WarLogHeader, null), /*#__PURE__*/React.createElement(WarLog, {
    log: playerWarLog,
    index: 0,
    animate: false,
    getVillageIcon: getVillageIcon
  })))), /*#__PURE__*/React.createElement("div", {
    className: "row second"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "global war score"), /*#__PURE__*/React.createElement("div", {
    className: "global_leaderboard_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "warlog_label_row"
  }, /*#__PURE__*/React.createElement("div", {
    className: "warlog_username_label"
  }), /*#__PURE__*/React.createElement("div", {
    className: "warlog_war_score_label"
  }, "war score"), /*#__PURE__*/React.createElement("div", {
    className: "warlog_pvp_wins_label"
  }, "pvp wins"), /*#__PURE__*/React.createElement("div", {
    className: "warlog_raid_label"
  }, "raid"), /*#__PURE__*/React.createElement("div", {
    className: "warlog_reinforce_label"
  }, "reinforce"), /*#__PURE__*/React.createElement("div", {
    className: "warlog_infiltrate_label"
  }, "infiltrate"), /*#__PURE__*/React.createElement("div", {
    className: "warlog_defense_label"
  }, "def"), /*#__PURE__*/React.createElement("div", {
    className: "warlog_captures_label"
  }, "captures"), /*#__PURE__*/React.createElement("div", {
    className: "warlog_patrols_label"
  }, "patrols"), /*#__PURE__*/React.createElement("div", {
    className: "warlog_resources_label"
  }, "resources"), /*#__PURE__*/React.createElement("div", {
    className: "warlog_chart_label"
  })), globalLeaderboardWarLogs.map((log, index) => /*#__PURE__*/React.createElement(WarLog, {
    log: log,
    index: index,
    animate: true,
    getVillageIcon: getVillageIcon
  }))), /*#__PURE__*/React.createElement("div", {
    className: "global_leaderboard_navigation"
  }, /*#__PURE__*/React.createElement("div", {
    className: "global_leaderboard_navigation_divider_left"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "100%",
    height: "2"
  }, /*#__PURE__*/React.createElement("line", {
    x1: "0%",
    y1: "1",
    x2: "100%",
    y2: "1",
    stroke: "#4e4535",
    strokeWidth: "1"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "global_leaderboard_pagination_wrapper"
  }, globalLeaderboardPageNumber > 1 && /*#__PURE__*/React.createElement("a", {
    className: "global_leaderboard_pagination",
    onClick: () => GlobalLeaderboardPreviousPage(globalLeaderboardPageNumber - 1)
  }, "<< Prev")), /*#__PURE__*/React.createElement("div", {
    className: "global_leaderboard_navigation_divider_middle"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "100%",
    height: "2"
  }, /*#__PURE__*/React.createElement("line", {
    x1: "0%",
    y1: "1",
    x2: "100%",
    y2: "1",
    stroke: "#4e4535",
    strokeWidth: "1"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "global_leaderboard_pagination_wrapper"
  }, /*#__PURE__*/React.createElement("a", {
    className: "global_leaderboard_pagination",
    onClick: () => GlobalLeaderboardNextPage(globalLeaderboardPageNumber + 1)
  }, "Next >>")), /*#__PURE__*/React.createElement("div", {
    className: "global_leaderboard_navigation_divider_right"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "100%",
    height: "2"
  }, /*#__PURE__*/React.createElement("line", {
    x1: "0%",
    y1: "1",
    x2: "100%",
    y2: "1",
    stroke: "#4e4535",
    strokeWidth: "1"
  })))))));
}