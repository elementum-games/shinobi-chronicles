import { apiFetch } from "../utils/network.js";
import { ModalProvider } from "../utils/modalContext.js";
export function WarTable({
  playerWarLogData,
  warRecordData,
  strategicDataState,
  villageAPI,
  handleErrors,
  getVillageIcon,
  getPolicyDisplayData
}) {
  const [playerWarLog, setPlayerWarLog] = React.useState(playerWarLogData.player_war_log);
  const [globalLeaderboardWarLogs, setGlobalLeaderboardWarLogs] = React.useState(playerWarLogData.global_leaderboard_war_logs);
  const [globalLeaderboardPageNumber, setGlobalLeaderboardPageNumber] = React.useState(1);
  const [warRecords, setWarRecords] = React.useState(warRecordData.war_records);
  const [warRecordsPageNumber, setWarRecordsPageNumber] = React.useState(1);
  const [selectedWarRecord, setSelectedWarRecord] = React.useState(null);
  function getVillageBanner(village_id) {
    switch (village_id) {
      case 1:
        return '/images/v2/decorations/strategic_banners/stratbannerstone.jpg';
      case 2:
        return '/images/v2/decorations/strategic_banners/stratbannercloud.jpg';
      case 3:
        return '/images/v2/decorations/strategic_banners/stratbannerleaf.jpg';
      case 4:
        return '/images/v2/decorations/strategic_banners/stratbannersand.jpg';
      case 5:
        return '/images/v2/decorations/strategic_banners/stratbannermist.jpg';
      default:
        return null;
    }
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
  const WarRecordsNextPage = page_number => {
    apiFetch(villageAPI, {
      request: 'GetWarRecords',
      page_number: page_number
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      if (response.data.warRecordData.war_records.length == 0) {
        return;
      } else {
        setWarRecordsPageNumber(page_number);
        setWarRecords(response.data.warRecordData.war_records);
      }
    });
  };
  const WarRecordsPreviousPage = page_number => {
    if (page_number > 0) {
      apiFetch(villageAPI, {
        request: 'GetWarRecords',
        page_number: page_number
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }
        setWarRecordsPageNumber(page_number);
        setWarRecords(response.data.warRecordData.war_records);
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
  }, /*#__PURE__*/React.createElement(WarLogHeader, null), /*#__PURE__*/React.createElement(PlayerWarLog, {
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
  })), globalLeaderboardWarLogs.map((log, index) => /*#__PURE__*/React.createElement(PlayerWarLog, {
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
  })))))), /*#__PURE__*/React.createElement("div", {
    className: "row third"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement("svg", {
    height: "0",
    width: "0"
  }, /*#__PURE__*/React.createElement("defs", null, /*#__PURE__*/React.createElement("filter", {
    id: "war_record_hover"
  }, /*#__PURE__*/React.createElement("feGaussianBlur", {
    in: "SourceAlpha",
    stdDeviation: "2",
    result: "blur"
  }), /*#__PURE__*/React.createElement("feFlood", {
    floodColor: "white",
    result: "floodColor"
  }), /*#__PURE__*/React.createElement("feComponentTransfer", {
    in: "blur",
    result: "opacityAdjustedBlur"
  }, /*#__PURE__*/React.createElement("feFuncA", {
    type: "linear",
    slope: "1"
  })), /*#__PURE__*/React.createElement("feComposite", {
    in: "floodColor",
    in2: "opacityAdjustedBlur",
    operator: "in",
    result: "coloredBlur"
  }), /*#__PURE__*/React.createElement("feMerge", null, /*#__PURE__*/React.createElement("feMergeNode", {
    in: "coloredBlur"
  }), /*#__PURE__*/React.createElement("feMergeNode", {
    in: "SourceGraphic"
  }))))), /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "war records"), /*#__PURE__*/React.createElement("div", {
    className: "war_records_container"
  }, warRecords.map((record, index) => /*#__PURE__*/React.createElement(WarRecord, {
    record: record,
    index: index,
    getVillageIcon: getVillageIcon,
    getVillageBanner: getVillageBanner
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
  }, warRecordsPageNumber > 1 && /*#__PURE__*/React.createElement("a", {
    className: "global_leaderboard_pagination",
    onClick: () => WarRecordsPreviousPage(warRecordsPageNumber - 1)
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
    onClick: () => WarRecordsNextPage(warRecordsPageNumber + 1)
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
  })))), selectedWarRecord && /*#__PURE__*/React.createElement("div", {
    className: "village_warlog_container"
  }, /*#__PURE__*/React.createElement(VillageWarLog, {
    log: selectedWarRecord.attacker_war_log,
    getVillageIcon: getVillageIcon,
    animate: true,
    is_attacker: true,
    getPolicyDisplayData: getPolicyDisplayData,
    strategicDataState: strategicDataState
  }), /*#__PURE__*/React.createElement(VillageWarLog, {
    log: selectedWarRecord.defender_war_log,
    getVillageIcon: getVillageIcon,
    animate: true,
    is_attacker: false,
    getPolicyDisplayData: getPolicyDisplayData,
    strategicDataState: strategicDataState
  })))));
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
  function PlayerWarLog({
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
  function WarRecord({
    record,
    index,
    getVillageIcon,
    getVillageBanner
  }) {
    const is_active = record.village_relation.relation_end ? false : true;
    function calculateBarPercent() {
      let victory_percent_required = record.victory_percent_required;
      victory_percent_required = victory_percent_required / 100;
      return 100 * (1 + victory_percent_required) / (2 + victory_percent_required);
    }
    const renderScoreBar = () => {
      const total_score = record.attacker_war_log.war_score + record.defender_war_log.war_score;
      const attacker_score_percentage = Math.round(record.attacker_war_log.war_score / total_score * 100);
      const victory_bar_percentage = calculateBarPercent();
      return /*#__PURE__*/React.createElement("div", {
        className: "war_record_score_bar"
      }, /*#__PURE__*/React.createElement("div", {
        className: "war_record_score_bar_attacker",
        style: {
          width: attacker_score_percentage + "%"
        }
      }), /*#__PURE__*/React.createElement("div", {
        className: "war_record_score_bar_defender",
        style: {
          width: 100 - attacker_score_percentage + "%"
        }
      }), /*#__PURE__*/React.createElement("svg", {
        className: "war_record_score_divider",
        viewBox: "0 0 200 200",
        width: "7",
        height: "7",
        style: {
          paddingBottom: "1px",
          left: attacker_score_percentage - 1 + "%"
        }
      }, /*#__PURE__*/React.createElement("defs", null, /*#__PURE__*/React.createElement("linearGradient", {
        id: "war_record_score_divider_gradient"
      }, /*#__PURE__*/React.createElement("stop", {
        stopColor: "#f8de97",
        offset: "0%"
      }), /*#__PURE__*/React.createElement("stop", {
        stopColor: "#bfa458",
        offset: "100%"
      })), /*#__PURE__*/React.createElement("filter", {
        id: "record_name_glow"
      }, /*#__PURE__*/React.createElement("feGaussianBlur", {
        in: "SourceAlpha",
        stdDeviation: "2",
        result: "blur"
      }), /*#__PURE__*/React.createElement("feFlood", {
        floodColor: "#ac3b3b",
        result: "floodColor"
      }), /*#__PURE__*/React.createElement("feComponentTransfer", {
        in: "blur",
        result: "opacityAdjustedBlur"
      }, /*#__PURE__*/React.createElement("feFuncA", {
        type: "linear",
        slope: "2"
      })), /*#__PURE__*/React.createElement("feComposite", {
        in: "floodColor",
        in2: "opacityAdjustedBlur",
        operator: "in",
        result: "coloredBlur"
      }), /*#__PURE__*/React.createElement("feMerge", null, /*#__PURE__*/React.createElement("feMergeNode", {
        in: "coloredBlur"
      }), /*#__PURE__*/React.createElement("feMergeNode", {
        in: "SourceGraphic"
      })))), /*#__PURE__*/React.createElement("polygon", {
        points: "0,0 0,25 40,25 40,175 0,175 0,200 200,200 200,175 160,175 160,25 200,25 200,0",
        fill: "url(#war_record_score_divider_gradient)",
        stroke: "#4d401c",
        strokeWidth: "20"
      })));
    };
    return /*#__PURE__*/React.createElement("div", {
      key: index,
      className: "war_record" + (selectedWarRecord && record.village_relation.relation_id == selectedWarRecord.village_relation.relation_id ? " selected" : ""),
      onClick: () => setSelectedWarRecord(record),
      style: {
        background: `linear-gradient(to right, transparent 0%, #17161b 30%, #17161b 70%, transparent 100%), url('${getVillageBanner(record.village_relation.village1_id)}'), url('${getVillageBanner(record.village_relation.village2_id)}')`,
        backgroundPosition: "center, -20% center, 115% center",
        backgroundSize: "cover, auto, auto",
        backgroundRepeat: "no-repeat"
      }
    }, /*#__PURE__*/React.createElement("div", {
      className: "war_record_village left"
    }, /*#__PURE__*/React.createElement("div", {
      className: "war_record_village_inner"
    }, /*#__PURE__*/React.createElement("img", {
      src: getVillageIcon(record.village_relation.village1_id)
    }))), /*#__PURE__*/React.createElement("div", {
      className: "war_record_details_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "war_record_relation_name" + (is_active ? " active" : " inactive")
    }, record.village_relation.relation_name), /*#__PURE__*/React.createElement("div", {
      className: "war_record_label_row"
    }, /*#__PURE__*/React.createElement("div", {
      className: "war_record_score left" + (is_active ? " active" : " inactive")
    }, record.attacker_war_log.war_score), /*#__PURE__*/React.createElement("div", {
      className: "war_record_status" + (is_active ? " active" : " inactive")
    }, record.village_relation.relation_end ? /*#__PURE__*/React.createElement(React.Fragment, null, record.village_relation.relation_start + " - " + record.village_relation.relation_end) : /*#__PURE__*/React.createElement(React.Fragment, null, "war active")), /*#__PURE__*/React.createElement("div", {
      className: "war_record_score right" + (is_active ? " active" : " inactive")
    }, record.defender_war_log.war_score)), renderScoreBar()), /*#__PURE__*/React.createElement("div", {
      className: "war_record_village right"
    }, /*#__PURE__*/React.createElement("div", {
      className: "war_record_village_inner"
    }, /*#__PURE__*/React.createElement("img", {
      src: getVillageIcon(record.village_relation.village2_id)
    }))));
  }
  function VillageWarLog({
    log,
    getVillageIcon,
    animate,
    is_attacker,
    getPolicyDisplayData,
    strategicDataState
  }) {
    console.log(strategicDataState.find(item => item.village.name == "Stone"));
    const policy_name = getPolicyDisplayData(strategicDataState.find(item => item.village.name == log.village_name).village.policy_id).name;
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
      className: "village_warlog"
    }, /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_header"
    }, /*#__PURE__*/React.createElement("img", {
      src: getVillageIcon(log.village_id)
    }), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_header_name"
    }, log.village_name), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_header_policy"
    }, policy_name), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_header_war_score" + (is_attacker ? " attacker" : " defender")
    }, log.war_score)), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_chart_row"
    }, /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_chart"
    }, /*#__PURE__*/React.createElement(Recharts.PieChart, {
      width: 150,
      height: 150
    }, /*#__PURE__*/React.createElement(Recharts.Pie, {
      isAnimationActive: animate,
      stroke: "none",
      data: scoreData,
      dataKey: "score",
      outerRadius: 75,
      fill: "green"
    }, scoreData.map((entry, index) => /*#__PURE__*/React.createElement(Recharts.Cell, {
      key: `cell-${index}`,
      fill: chart_colors[index % chart_colors.length]
    }))))), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_chart_breakdown"
    }, /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_chart_breakdown_row"
    }, /*#__PURE__*/React.createElement("svg", {
      width: "18",
      height: "18"
    }, /*#__PURE__*/React.createElement("rect", {
      width: "100",
      height: "100",
      fill: "#2b5fca"
    })), /*#__PURE__*/React.createElement("div", null, "Objective score (", Math.round(log.objective_score / Math.max(log.war_score, 1) * 100), "%)")), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_chart_breakdown_row"
    }, /*#__PURE__*/React.createElement("svg", {
      width: "18",
      height: "18"
    }, /*#__PURE__*/React.createElement("rect", {
      width: "100",
      height: "100",
      fill: "#5fca8c"
    })), /*#__PURE__*/React.createElement("div", null, "Resource score (", Math.round(log.resource_score / Math.max(log.war_score, 1) * 100), "%)")), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_chart_breakdown_row"
    }, /*#__PURE__*/React.createElement("svg", {
      width: "18",
      height: "18"
    }, /*#__PURE__*/React.createElement("rect", {
      width: "100",
      height: "100",
      fill: "#d64866"
    })), /*#__PURE__*/React.createElement("div", null, "Battle score (", Math.round(log.battle_score / Math.max(log.war_score, 1) * 100), "%)")))), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details"
    }, /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_item"
    }, /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_label"
    }, "PVP WINS"), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_value"
    }, log.pvp_wins)), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_item"
    }, /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_label"
    }, "RAID"), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_value"
    }, /*#__PURE__*/React.createElement("span", null, log.raid_count), /*#__PURE__*/React.createElement("span", {
      className: "warlog_red"
    }, "(", log.damage_dealt, ")"))), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_item"
    }, /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_label"
    }, "REINFORCE"), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_value"
    }, /*#__PURE__*/React.createElement("span", null, log.reinforce_count), /*#__PURE__*/React.createElement("span", {
      className: "warlog_green"
    }, "(", log.damage_healed, ")"))), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_item"
    }, /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_label"
    }, "INFILTRATE"), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_value"
    }, log.infiltrate_count)), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_item"
    }, /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_label"
    }, "DEF"), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_value"
    }, /*#__PURE__*/React.createElement("span", {
      className: "warlog_green"
    }, "+", log.defense_gained), /*#__PURE__*/React.createElement("span", {
      className: "warlog_red"
    }, "-", log.defense_reduced))), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_item"
    }, /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_label"
    }, "CAPTURES"), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_value"
    }, log.regions_captured + log.villages_captured)), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_item"
    }, /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_label"
    }, "PATROLS"), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_value"
    }, log.patrols_defeated)), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_item"
    }, /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_label"
    }, "RESOURCES"), /*#__PURE__*/React.createElement("div", {
      className: "village_warlog_details_value"
    }, log.resources_stolen))));
  }
}