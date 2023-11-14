import { apiFetch } from "../utils/network.js";
function Village({
  playerID,
  playerSeat,
  villageName,
  villageAPI,
  policyData,
  populationData,
  seatData,
  pointsData,
  diplomacyData,
  resourceData,
  clanData,
  proposalData,
  strategicData,
  challengeData,
  warLogData
}) {
  const [playerSeatState, setPlayerSeatState] = React.useState(playerSeat);
  const [policyDataState, setPolicyDataState] = React.useState(policyData);
  const [seatDataState, setSeatDataState] = React.useState(seatData);
  const [pointsDataState, setPointsDataState] = React.useState(pointsData);
  const [diplomacyDataState, setDiplomacyDataState] = React.useState(diplomacyData);
  const [resourceDataState, setResourceDataState] = React.useState(resourceData);
  const [proposalDataState, setProposalDataState] = React.useState(proposalData);
  const [strategicDataState, setStrategicDataState] = React.useState(strategicData);
  const [challengeDataState, setChallengeDataState] = React.useState(challengeData);
  const [villageTab, setVillageTab] = React.useState("villageHQ");
  function handleErrors(errors) {
    console.warn(errors);
  }
  function getKageKanji(village_id) {
    switch (village_id) {
      case 'Stone':
        return '土影';
      case 'Cloud':
        return '雷影';
      case 'Leaf':
        return '火影';
      case 'Sand':
        return '風影';
      case 'Mist':
        return '水影';
    }
  }
  function getVillageIcon(village_id) {
    switch (village_id) {
      case 1:
        return '/images/village_icons/stone.png';
      case 2:
        return '/images/village_icons/cloud.png';
      case 3:
        return '/images/village_icons/leaf.png';
      case 4:
        return '/images/village_icons/sand.png';
      case 5:
        return '/images/village_icons/mist.png';
      default:
        return null;
    }
  }
  function getPolicyDisplayData(policy_id) {
    let data = {
      banner: "",
      name: "",
      phrase: "",
      description: "",
      bonuses: [],
      penalties: [],
      glowClass: ""
    };
    switch (policy_id) {
      case 0:
        data.banner = "";
        data.name = "Inactive Policy";
        data.phrase = "";
        data.description = "";
        data.bonuses = [];
        data.penalties = [];
        data.glowClass = "";
        break;
      case 1:
        data.banner = "/images/v2/decorations/policy_banners/growthpolicy.jpg";
        data.name = "From the Ashes";
        data.phrase = "bonds forged, courage shared.";
        data.description = "In unity, find the strength to overcome.\nOne village, one heart, one fight.";
        data.bonuses = ["25% increased Caravan speed", "+25 base resource production", "+5% training speed", "Free incoming village transfer"];
        data.penalties = ["-30 Materials/hour", "-50 Food/hour", "-20 Wealth/hour", "Cannot declare War"];
        data.glowClass = "growth_glow";
        break;
      case 2:
        data.banner = "/images/v2/decorations/policy_banners/espionagepolicy.jpg";
        data.name = "Eye of the Storm";
        data.phrase = "half truths, all lies.";
        data.description = "Become informants dealing in truths and lies.\nDeceive, divide and destroy.";
        data.bonuses = ["25% increased Infiltrate speed", "+1 Defense reduction from Infiltrate", "+1 Stealth", "+10 Loot Capacity"];
        data.penalties = ["-25 Materials/hour", "-25 Food/hour", "-50 Wealth/hour"];
        data.glowClass = "espionage_glow";
        break;
      case 3:
        data.banner = "/images/v2/decorations/policy_banners/defensepolicy.jpg";
        data.name = "Fortress of Solitude";
        data.phrase = "vigilant minds, enduring hearts.";
        data.description = "Show the might of will unyielding.\nPrepare, preserve, prevail.";
        data.bonuses = ["25% increased Reinforce speed", "+1 Defense gain from Reinforce", "+1 Scouting", "Increased Patrol strength"];
        data.penalties = ["-45 Materials/hour", "-30 Food/hour", "-25 Wealth/hour"];
        data.glowClass = "defense_glow";
        break;
      case 4:
        data.banner = "/images/v2/decorations/policy_banners/warpolicy.jpg";
        data.name = "Forged in Flames";
        data.phrase = "blades sharp, minds sharper.";
        data.description = "Lead your village on the path of a warmonger.\nFeel no fear, no hesitation, no doubt.";
        data.bonuses = ["25% increased Raid speed", "+1 Defense reduction from Raid", "+1 Village Point from PvP", "Faster Patrol respawn"];
        data.penalties = ["-30 Materials/hour", "-40 Food/hour", "-30 Wealth/hour", "Cannot form Alliances"];
        data.glowClass = "war_glow";
        break;
      case 5:
        data.banner = "/images/v2/decorations/policy_banners/prosperitypolicy.jpg";
        data.name = "The Gilded Hand";
        data.phrase = "";
        data.description = "";
        data.bonuses = [];
        data.penalties = [];
        data.glowClass = "prosperity_glow";
        break;
    }
    return data;
  }
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "navigation_row"
  }, /*#__PURE__*/React.createElement("div", {
    className: "nav_button",
    onClick: () => setVillageTab("villageHQ")
  }, "village hq"), /*#__PURE__*/React.createElement("div", {
    className: "nav_button",
    onClick: () => setVillageTab("worldInfo")
  }, "world info"), /*#__PURE__*/React.createElement("div", {
    className: "nav_button",
    onClick: () => setVillageTab("warTable")
  }, "war table"), /*#__PURE__*/React.createElement("div", {
    className: "nav_button disabled"
  }, "members & teams"), /*#__PURE__*/React.createElement("div", {
    className: playerSeatState.seat_id != null ? "nav_button" : "nav_button disabled",
    onClick: () => setVillageTab("kageQuarters")
  }, "kage's quarters")), villageTab == "villageHQ" && /*#__PURE__*/React.createElement(VillageHQ, {
    playerID: playerID,
    playerSeatState: playerSeatState,
    setPlayerSeatState: setPlayerSeatState,
    villageName: villageName,
    villageAPI: villageAPI,
    policyDataState: policyDataState,
    populationData: populationData,
    seatDataState: seatDataState,
    setSeatDataState: setSeatDataState,
    pointsDataState: pointsDataState,
    diplomacyDataState: diplomacyDataState,
    resourceDataState: resourceDataState,
    setResourceDataState: setResourceDataState,
    challengeDataState: challengeDataState,
    setChallengeDataState: setChallengeDataState,
    clanData: clanData,
    handleErrors: handleErrors,
    getKageKanji: getKageKanji,
    getVillageIcon: getVillageIcon,
    getPolicyDisplayData: getPolicyDisplayData,
    TimeGrid: TimeGrid,
    TimeGridResponse: TimeGridResponse
  }), villageTab == "kageQuarters" && /*#__PURE__*/React.createElement(KageQuarters, {
    playerID: playerID,
    playerSeatState: playerSeatState,
    setPlayerSeatState: setPlayerSeatState,
    villageName: villageName,
    villageAPI: villageAPI,
    policyDataState: policyDataState,
    setPolicyDataState: setPolicyDataState,
    seatDataState: seatDataState,
    pointsDataState: pointsDataState,
    setPointsDataState: setPointsDataState,
    diplomacyDataState: diplomacyDataState,
    setDiplomacyDataState: setDiplomacyDataState,
    resourceDataState: resourceDataState,
    setResourceDataState: setResourceDataState,
    proposalDataState: proposalDataState,
    setProposalDataState: setProposalDataState,
    strategicDataState: strategicDataState,
    setStrategicDataState: setStrategicDataState,
    handleErrors: handleErrors,
    getKageKanji: getKageKanji,
    getVillageIcon: getVillageIcon,
    getPolicyDisplayData: getPolicyDisplayData,
    StrategicInfoItem: StrategicInfoItem
  }), villageTab == "worldInfo" && /*#__PURE__*/React.createElement(WorldInfo, {
    villageName: villageName,
    strategicDataState: strategicDataState,
    getVillageIcon: getVillageIcon,
    StrategicInfoItem: StrategicInfoItem,
    getPolicyDisplayData: getPolicyDisplayData
  }), villageTab == "warTable" && /*#__PURE__*/React.createElement(WarTable, {
    warLogData: warLogData,
    villageAPI: villageAPI,
    handleErrors: handleErrors,
    getVillageIcon: getVillageIcon
  }));
}
function VillageHQ({
  playerID,
  playerSeatState,
  setPlayerSeatState,
  villageName,
  villageAPI,
  policyDataState,
  populationData,
  seatDataState,
  setSeatDataState,
  pointsDataState,
  diplomacyDataState,
  resourceDataState,
  setResourceDataState,
  challengeDataState,
  setChallengeDataState,
  clanData,
  handleErrors,
  getKageKanji,
  getVillageIcon,
  getPolicyDisplayData
}) {
  const [modalState, setModalState] = React.useState("closed");
  const [resourceDaysToShow, setResourceDaysToShow] = React.useState(1);
  const [policyDisplay, setPolicyDisplay] = React.useState(getPolicyDisplayData(policyDataState.policy_id));
  const [selectedTimesUTC, setSelectedTimesUTC] = React.useState([]);
  const [selectedTimeUTC, setSelectedTimeUTC] = React.useState(null);
  const [modalHeader, setModalHeader] = React.useState(null);
  const [modalText, setModalText] = React.useState(null);
  const [challengeTarget, setChallengeTarget] = React.useState(null);
  const DisplayFromDays = days => {
    switch (days) {
      case 1:
        return 'daily';
        break;
      case 7:
        return 'weekly';
        break;
      case 30:
        return 'monthly';
        break;
      default:
        return days;
        break;
    }
  };
  const FetchNextIntervalTypeResources = () => {
    var days;
    switch (resourceDaysToShow) {
      case 1:
        days = 7;
        break;
      case 7:
        days = 30;
        break;
      case 30:
        days = 1;
        break;
    }
    apiFetch(villageAPI, {
      request: 'LoadResourceData',
      days: days
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      setResourceDaysToShow(days);
      setResourceDataState(response.data);
    });
  };
  const ClaimSeat = seat_type => {
    apiFetch(villageAPI, {
      request: 'ClaimSeat',
      seat_type: seat_type
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      setSeatDataState(response.data.seatData);
      setPlayerSeatState(response.data.playerSeat);
      setModalHeader("Confirmation");
      setModalText(response.data.response_message);
      setModalState("response_message");
    });
  };
  const Resign = () => {
    if (modalState == "confirm_resign") {
      apiFetch(villageAPI, {
        request: 'Resign'
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }
        setSeatDataState(response.data.seatData);
        setPlayerSeatState(response.data.playerSeat);
        setModalHeader("Confirmation");
        setModalText(response.data.response_message);
        setModalState("response_message");
      });
    } else {
      setModalHeader("Confirmation");
      setModalState("confirm_resign");
      setModalText("Are you sure you wish to resign from your current position?");
    }
  };
  const Challenge = target_seat => {
    setChallengeTarget(target_seat);
    setModalState("submit_challenge");
    setModalHeader("Submit Challenge");
    setModalText("Select times below that you are available to battle.");
  };
  const ConfirmSubmitChallenge = () => {
    if (selectedTimesUTC.length < 12) {
      setModalText("Insufficient slots selected.");
    } else {
      apiFetch(villageAPI, {
        request: 'SubmitChallenge',
        seat_id: challengeTarget.seat_id,
        selected_times: selectedTimesUTC
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }
        setChallengeDataState(response.data.challengeData);
        setModalHeader("Confirmation");
        setModalText(response.data.response_message);
        setModalState("response_message");
      });
    }
  };
  const CancelChallenge = () => {
    if (modalState == "confirm_cancel_challenge") {
      apiFetch(villageAPI, {
        request: 'CancelChallenge'
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }
        setChallengeDataState(response.data.challengeData);
        setModalHeader("Confirmation");
        setModalText(response.data.response_message);
        setModalState("response_message");
      });
    } else {
      setModalHeader("Confirmation");
      setModalState("confirm_cancel_challenge");
      setModalText("Are you sure you wish to cancel your pending challenge request?");
    }
  };
  const AcceptChallenge = target_challenge => {
    setChallengeTarget(target_challenge);
    setModalState("accept_challenge");
    setModalHeader("Accept Challenge");
    setModalText("Select a time slot below to accept the challenge.");
  };
  const ConfirmAcceptChallenge = () => {
    if (!selectedTimeUTC) {
      setModalText("Select a slot to accept the challenge.");
    } else {
      apiFetch(villageAPI, {
        request: 'AcceptChallenge',
        challenge_id: challengeTarget.request_id,
        time: selectedTimeUTC
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }
        setChallengeDataState(response.data.challengeData);
        setModalHeader("Confirmation");
        setModalText(response.data.response_message);
        setModalState("response_message");
      });
    }
  };
  const LockChallenge = target_challenge => {
    if (modalState == "confirm_lock_challenge") {
      apiFetch(villageAPI, {
        request: 'LockChallenge',
        challenge_id: challengeTarget.request_id
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }
        setChallengeDataState(response.data.challengeData);
        setModalHeader("Confirmation");
        setModalText(response.data.response_message);
        setModalState("response_message");
      });
    } else {
      setChallengeTarget(target_challenge);
      setModalHeader("Confirmation");
      setModalState("confirm_lock_challenge");
      setModalText("Are you sure you want to lock in?\nYour actions will be restricted until the battle begins.");
    }
  };
  const CancelChallengeSchedule = () => {
    setSelectedTimesUTC([]);
  };
  const totalPopulation = populationData.reduce((acc, rank) => acc + rank.count, 0);
  const kage = seatDataState.find(seat => seat.seat_type === 'kage');
  return /*#__PURE__*/React.createElement(React.Fragment, null, modalState !== "closed" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "modal_backdrop"
  }), /*#__PURE__*/React.createElement("div", {
    className: "modal"
  }, /*#__PURE__*/React.createElement("div", {
    className: "modal_header"
  }, modalHeader), /*#__PURE__*/React.createElement("div", {
    className: "modal_text"
  }, modalText), modalState == "confirm_resign" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "modal_confirm_button",
    onClick: () => Resign()
  }, "confirm"), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => setModalState("closed")
  }, "cancel")), modalState == "submit_challenge" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "schedule_challenge_subtext_wrapper"
  }, /*#__PURE__*/React.createElement("span", {
    className: "schedule_challenge_subtext"
  }, "Time slots are displayed in your local time."), /*#__PURE__*/React.createElement("span", {
    className: "schedule_challenge_subtext"
  }, "The seat holder will have 24 hours to choose one of your selected times."), /*#__PURE__*/React.createElement("span", {
    className: "schedule_challenge_subtext"
  }, "Your battle will be scheduled a minimum of 12 hours from the time of their selection.")), /*#__PURE__*/React.createElement(TimeGrid, {
    setSelectedTimesUTC: setSelectedTimesUTC,
    startHourUTC: luxon.DateTime.fromObject({
      hour: 0,
      zone: luxon.Settings.defaultZoneName
    }).toUTC().hour
  }), /*#__PURE__*/React.createElement("div", {
    className: "modal_confirm_button",
    onClick: () => ConfirmSubmitChallenge()
  }, "confirm"), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => setModalState("closed")
  }, "cancel")), modalState == "accept_challenge" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "schedule_challenge_subtext_wrapper"
  }, /*#__PURE__*/React.createElement("span", {
    className: "schedule_challenge_subtext"
  }, "Time slots are displayed in your local time."), /*#__PURE__*/React.createElement("span", {
    className: "schedule_challenge_subtext"
  }, "The first slot below is set a minimum 12 hours from the current time.")), /*#__PURE__*/React.createElement(TimeGridResponse, {
    availableTimesUTC: JSON.parse(challengeTarget.selected_times),
    setSelectedTimeUTC: setSelectedTimeUTC,
    startHourUTC: (luxon.DateTime.utc().minute === 0 ? luxon.DateTime.utc().hour + 12 : luxon.DateTime.utc().hour + 13) % 24
  }), /*#__PURE__*/React.createElement("div", {
    className: "modal_confirm_button",
    onClick: () => ConfirmAcceptChallenge()
  }, "confirm"), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => setModalState("closed")
  }, "cancel")), modalState == "confirm_cancel_challenge" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "modal_confirm_button",
    onClick: () => CancelChallenge()
  }, "confirm"), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => setModalState("closed")
  }, "cancel")), modalState == "confirm_lock_challenge" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "modal_confirm_button",
    onClick: () => LockChallenge()
  }, "confirm"), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => setModalState("closed")
  }, "cancel")), modalState == "response_message" && /*#__PURE__*/React.createElement("div", {
    className: "modal_close_button",
    onClick: () => setModalState("closed")
  }, "close"))), /*#__PURE__*/React.createElement(ChallengeContainer, {
    playerID: playerID,
    challengeDataState: challengeDataState,
    CancelChallenge: CancelChallenge,
    AcceptChallenge: AcceptChallenge,
    LockChallenge: LockChallenge
  }), /*#__PURE__*/React.createElement("div", {
    className: "hq_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "row first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "clan_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Clans"), /*#__PURE__*/React.createElement("div", {
    className: "content box-primary"
  }, clanData.map((clan, index) => /*#__PURE__*/React.createElement("div", {
    key: clan.clan_id,
    className: "clan_item"
  }, /*#__PURE__*/React.createElement("div", {
    className: "clan_item_header"
  }, clan.name))))), /*#__PURE__*/React.createElement("div", {
    className: "population_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Population"), /*#__PURE__*/React.createElement("div", {
    className: "content box-primary"
  }, populationData.map((rank, index) => /*#__PURE__*/React.createElement("div", {
    key: rank.rank,
    className: "population_item"
  }, /*#__PURE__*/React.createElement("div", {
    className: "population_item_header"
  }, rank.rank), /*#__PURE__*/React.createElement("div", {
    className: "population_item_count"
  }, rank.count))), /*#__PURE__*/React.createElement("div", {
    className: "population_item",
    style: {
      width: "100%"
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "population_item_header"
  }, "total"), /*#__PURE__*/React.createElement("div", {
    className: "population_item_count last"
  }, totalPopulation))))), /*#__PURE__*/React.createElement("div", {
    className: "column second"
  }, /*#__PURE__*/React.createElement("div", {
    className: "kage_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "kage_header"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Kage"), /*#__PURE__*/React.createElement("div", {
    className: "kage_kanji"
  }, getKageKanji(villageName))), kage.avatar_link && /*#__PURE__*/React.createElement("div", {
    className: "kage_avatar_wrapper"
  }, /*#__PURE__*/React.createElement("img", {
    className: "kage_avatar",
    src: kage.avatar_link
  })), !kage.avatar_link && /*#__PURE__*/React.createElement("div", {
    className: "kage_avatar_wrapper_empty"
  }, /*#__PURE__*/React.createElement("div", {
    className: "kage_avatar_fill"
  })), /*#__PURE__*/React.createElement("div", {
    className: "kage_nameplate_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "kage_nameplate_decoration nw"
  }), /*#__PURE__*/React.createElement("div", {
    className: "kage_nameplate_decoration ne"
  }), /*#__PURE__*/React.createElement("div", {
    className: "kage_nameplate_decoration se"
  }), /*#__PURE__*/React.createElement("div", {
    className: "kage_nameplate_decoration sw"
  }), /*#__PURE__*/React.createElement("div", {
    className: "kage_name"
  }, kage.user_name ? kage.user_name : "---"), /*#__PURE__*/React.createElement("div", {
    className: "kage_title"
  }, kage.seat_title + " of " + villageName), kage.seat_id && kage.seat_id == playerSeatState.seat_id && /*#__PURE__*/React.createElement("div", {
    className: "kage_resign_button",
    onClick: () => Resign()
  }, "resign"), !kage.seat_id && /*#__PURE__*/React.createElement("div", {
    className: "kage_claim_button",
    onClick: () => ClaimSeat("kage")
  }, "claim"), kage.seat_id && kage.seat_id != playerSeatState.seat_id && /*#__PURE__*/React.createElement("div", {
    className: "kage_challenge_button",
    onClick: () => Challenge(kage)
  }, "challenge")))), /*#__PURE__*/React.createElement("div", {
    className: "column third"
  }, /*#__PURE__*/React.createElement("div", {
    className: "elders_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Elders"), /*#__PURE__*/React.createElement("div", {
    className: "elder_list"
  }, seatDataState.filter(elder => elder.seat_type === 'elder').map((elder, index) => /*#__PURE__*/React.createElement("div", {
    key: elder.seat_key,
    className: "elder_item"
  }, /*#__PURE__*/React.createElement("div", {
    className: "elder_avatar_wrapper"
  }, elder.avatar_link && /*#__PURE__*/React.createElement("img", {
    className: "elder_avatar",
    src: elder.avatar_link
  }), !elder.avatar_link && /*#__PURE__*/React.createElement("div", {
    className: "elder_avatar_fill"
  })), /*#__PURE__*/React.createElement("div", {
    className: "elder_name"
  }, elder.user_name ? /*#__PURE__*/React.createElement("a", {
    href: "/?id=6&user=" + elder.user_name
  }, elder.user_name) : "---"), elder.seat_id && elder.seat_id == playerSeatState.seat_id && /*#__PURE__*/React.createElement("div", {
    className: "elder_resign_button",
    onClick: () => Resign()
  }, "resign"), !elder.seat_id && /*#__PURE__*/React.createElement("div", {
    className: playerSeatState.seat_id ? "elder_claim_button disabled" : "elder_claim_button",
    onClick: playerSeatState.seat_id ? null : () => ClaimSeat("elder")
  }, "claim"), elder.seat_id && playerSeatState.seat_id == null && /*#__PURE__*/React.createElement("div", {
    className: "elder_challenge_button",
    onClick: () => Challenge(elder)
  }, "challenge"), elder.seat_id && playerSeatState.seat_id !== null && playerSeatState.seat_id != elder.seat_id && /*#__PURE__*/React.createElement("div", {
    className: "elder_challenge_button disabled"
  }, "challenge"))))), /*#__PURE__*/React.createElement("div", {
    className: "points_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Village points"), /*#__PURE__*/React.createElement("div", {
    className: "content box-primary"
  }, /*#__PURE__*/React.createElement("div", {
    className: "points_item"
  }, /*#__PURE__*/React.createElement("div", {
    className: "points_label"
  }, "total"), /*#__PURE__*/React.createElement("div", {
    className: "points_total"
  }, pointsDataState.points)), /*#__PURE__*/React.createElement("div", {
    className: "points_item"
  }, /*#__PURE__*/React.createElement("div", {
    className: "points_label"
  }, "monthly"), /*#__PURE__*/React.createElement("div", {
    className: "points_total"
  }, pointsDataState.points)))))), /*#__PURE__*/React.createElement("div", {
    className: "row second"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Village policy"), /*#__PURE__*/React.createElement("div", {
    className: "village_policy_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "village_policy_bonus_container"
  }, policyDisplay.bonuses.map((bonus, index) => /*#__PURE__*/React.createElement("div", {
    key: index,
    className: "policy_bonus_item"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "16",
    height: "16",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "25,20 50,45 25,70 0,45",
    fill: "#4a5e45"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "25,0 50,25 25,50 0,25",
    fill: "#6ab352"
  })), /*#__PURE__*/React.createElement("div", {
    className: "policy_bonus_text"
  }, bonus)))), /*#__PURE__*/React.createElement("div", {
    className: "village_policy_main_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "village_policy_main_inner"
  }, /*#__PURE__*/React.createElement("div", {
    className: "village_policy_banner",
    style: {
      backgroundImage: "url(" + policyDisplay.banner + ")"
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "village_policy_name_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "village_policy_name " + policyDisplay.glowClass
  }, policyDisplay.name)), /*#__PURE__*/React.createElement("div", {
    className: "village_policy_phrase"
  }, policyDisplay.phrase), /*#__PURE__*/React.createElement("div", {
    className: "village_policy_description"
  }, policyDisplay.description))), /*#__PURE__*/React.createElement("div", {
    className: "village_policy_penalty_container"
  }, policyDisplay.penalties.map((penalty, index) => /*#__PURE__*/React.createElement("div", {
    key: index,
    className: "policy_penalty_item"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "16",
    height: "16",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "25,20 50,45 25,70 0,45",
    fill: "#4f1e1e"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "25,0 50,25 25,50 0,25",
    fill: "#ad4343"
  })), /*#__PURE__*/React.createElement("div", {
    className: "policy_penalty_text"
  }, penalty))))))), /*#__PURE__*/React.createElement("div", {
    className: "row third"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "diplomatic_status_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Diplomatic status"), /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_header_row"
  }, /*#__PURE__*/React.createElement("div", null), /*#__PURE__*/React.createElement("div", null, "points"), /*#__PURE__*/React.createElement("div", null, "members"), /*#__PURE__*/React.createElement("div", {
    className: "last"
  })), /*#__PURE__*/React.createElement("div", {
    className: "content"
  }, diplomacyDataState.map((village, index) => /*#__PURE__*/React.createElement("div", {
    key: village.village_name,
    className: "diplomacy_item"
  }, /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_item_name"
  }, /*#__PURE__*/React.createElement("img", {
    className: "diplomacy_village_icon",
    src: getVillageIcon(village.village_id)
  }), /*#__PURE__*/React.createElement("span", null, village.village_name)), /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_item_points"
  }, village.village_points), /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_item_villagers"
  }, village.villager_count), /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_item_relation " + village.relation_type
  }, village.relation_name)))))), /*#__PURE__*/React.createElement("div", {
    className: "column second"
  }, /*#__PURE__*/React.createElement("div", {
    className: "resources_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Resources overview"), /*#__PURE__*/React.createElement("div", {
    className: "content box-primary"
  }, /*#__PURE__*/React.createElement("div", {
    className: "resources_inner_header"
  }, /*#__PURE__*/React.createElement("div", {
    className: "first"
  }, /*#__PURE__*/React.createElement("a", {
    onClick: () => FetchNextIntervalTypeResources()
  }, DisplayFromDays(resourceDaysToShow))), /*#__PURE__*/React.createElement("div", {
    className: "second"
  }, "current"), /*#__PURE__*/React.createElement("div", null, "produced"), /*#__PURE__*/React.createElement("div", null, "claimed"), /*#__PURE__*/React.createElement("div", null, "lost"), /*#__PURE__*/React.createElement("div", null, "spent")), resourceDataState.map((resource, index) => /*#__PURE__*/React.createElement("div", {
    key: resource.resource_id,
    className: "resource_item"
  }, /*#__PURE__*/React.createElement("div", {
    className: "resource_name"
  }, resource.resource_name), /*#__PURE__*/React.createElement("div", {
    className: "resource_count"
  }, resource.count), /*#__PURE__*/React.createElement("div", {
    className: "resource_produced"
  }, resource.produced), /*#__PURE__*/React.createElement("div", {
    className: "resource_claimed"
  }, resource.claimed), /*#__PURE__*/React.createElement("div", {
    className: "resource_lost"
  }, resource.lost), /*#__PURE__*/React.createElement("div", {
    className: "resource_spent"
  }, resource.spent)))))))));
}
function KageQuarters({
  playerID,
  playerSeatState,
  villageName,
  villageAPI,
  policyDataState,
  setPolicyDataState,
  seatDataState,
  pointsDataState,
  setPointsDataState,
  diplomacyDataState,
  resourceDataState,
  setResourceDataState,
  proposalDataState,
  setProposalDataState,
  strategicDataState,
  setStrategicDataState,
  handleErrors,
  getKageKanji,
  getVillageIcon,
  getPolicyDisplayData,
  StrategicInfoItem
}) {
  const kage = seatDataState.find(seat => seat.seat_type === 'kage');
  const [currentProposal, setCurrentProposal] = React.useState(null);
  const [currentProposalKey, setCurrentProposalKey] = React.useState(null);
  const [displayPolicyID, setDisplayPolicyID] = React.useState(policyDataState.policy_id);
  const [policyDisplay, setPolicyDisplay] = React.useState(getPolicyDisplayData(displayPolicyID));
  const [proposalRepAdjustment, setProposalRepAdjustment] = React.useState(0);
  const [strategicDisplayLeft, setStrategicDisplayLeft] = React.useState(strategicDataState.find(item => item.village.name == villageName));
  const [strategicDisplayRight, setStrategicDisplayRight] = React.useState(strategicDataState.find(item => item.village.name != villageName));
  const [offeredResources, setOfferedResources] = React.useState([{
    resource_id: 1,
    resource_name: "materials",
    count: 0
  }, {
    resource_id: 2,
    resource_name: "food",
    count: 0
  }, {
    resource_id: 3,
    resource_name: "wealth",
    count: 0
  }]);
  const [offeredRegions, setOfferedRegions] = React.useState([]);
  const [requestedResources, setRequestedResources] = React.useState([{
    resource_id: 1,
    resource_name: "materials",
    count: 0
  }, {
    resource_id: 2,
    resource_name: "food",
    count: 0
  }, {
    resource_id: 3,
    resource_name: "wealth",
    count: 0
  }]);
  const [requestedRegions, setRequestedRegions] = React.useState([]);
  const [modalState, setModalState] = React.useState("closed");
  const [modalHeader, setModalHeader] = React.useState(null);
  const [modalText, setModalText] = React.useState(null);
  const ChangePolicy = () => {
    if (modalState == "confirm_policy") {
      apiFetch(villageAPI, {
        request: 'CreateProposal',
        type: 'policy',
        policy_id: displayPolicyID
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }
        setProposalDataState(response.data.proposalData);
        setModalHeader("Confirmation");
        setModalText(response.data.response_message);
        setModalState("response_message");
      });
    } else {
      setModalState("confirm_policy");
      setModalHeader("Confirmation");
      setModalText("Are you sure you want to change policies? You will be unable to select a new policy for 14 days.");
    }
  };
  const DeclareWar = () => {
    if (modalState == "confirm_declare_war") {
      apiFetch(villageAPI, {
        request: 'CreateProposal',
        type: 'declare_war',
        target_village_id: strategicDisplayRight.village.village_id
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }
        setProposalDataState(response.data.proposalData);
        setModalText(response.data.response_message);
        setModalHeader("Confirmation");
        setModalState("response_message");
      });
    } else {
      setModalState("confirm_declare_war");
      setModalText("Are you sure you declare war with " + strategicDisplayRight.village.name + "?");
      setModalHeader("Confirmation");
    }
  };
  const OfferPeace = () => {
    if (modalState == "confirm_offer_peace") {
      apiFetch(villageAPI, {
        request: 'CreateProposal',
        type: 'offer_peace',
        target_village_id: strategicDisplayRight.village.village_id
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }
        setProposalDataState(response.data.proposalData);
        setModalText(response.data.response_message);
        setModalHeader("Confirmation");
        setModalState("response_message");
      });
    } else {
      setModalState("confirm_offer_peace");
      setModalText("Are you sure you want to offer peace with " + strategicDisplayRight.village.name + "?");
      setModalHeader("Confirmation");
    }
  };
  const OfferAlliance = () => {
    if (modalState == "confirm_form_alliance") {
      apiFetch(villageAPI, {
        request: 'CreateProposal',
        type: 'offer_alliance',
        target_village_id: strategicDisplayRight.village.village_id
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }
        setProposalDataState(response.data.proposalData);
        setModalText(response.data.response_message);
        setModalHeader("Confirmation");
        setModalState("response_message");
      });
    } else {
      setModalState("confirm_form_alliance");
      setModalText("Are you sure you want to form an alliance with " + strategicDisplayRight.village.name + "?\nYou can be a member of only one Alliance at any given time.");
      setModalHeader("Confirmation");
    }
  };
  const BreakAlliance = () => {
    if (modalState == "confirm_break_alliance") {
      apiFetch(villageAPI, {
        request: 'CreateProposal',
        type: 'break_alliance',
        target_village_id: strategicDisplayRight.village.village_id
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }
        setProposalDataState(response.data.proposalData);
        setModalText(response.data.response_message);
        setModalHeader("Confirmation");
        setModalState("response_message");
      });
    } else {
      setModalState("confirm_break_alliance");
      setModalText("Are you sure you want break an alliance with " + strategicDisplayRight.village.name + "?");
      setModalHeader("Confirmation");
    }
  };
  const CancelProposal = () => {
    if (modalState == "confirm_cancel_proposal") {
      apiFetch(villageAPI, {
        request: 'CancelProposal',
        proposal_id: currentProposal.proposal_id
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }
        setProposalDataState(response.data.proposalData);
        setCurrentProposal(null);
        setCurrentProposalKey(null);
        setModalText(response.data.response_message);
        setModalHeader("Confirmation");
        setModalState("response_message");
      });
    } else {
      setModalState("confirm_cancel_proposal");
      setModalText("Are you sure you want to cancel this proposal?");
      setModalHeader("Confirmation");
    }
  };
  const OfferTrade = () => {
    if (modalState == "confirm_offer_trade") {
      apiFetch(villageAPI, {
        request: 'CreateProposal',
        type: 'offer_trade',
        target_village_id: strategicDisplayRight.village.village_id,
        offered_resources: offeredResources,
        offered_regions: offeredRegions,
        requested_resources: requestedResources,
        requested_regions: requestedRegions
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }
        setProposalDataState(response.data.proposalData);
        setModalText(response.data.response_message);
        setModalHeader("Confirmation");
        setModalState("response_message");
      });
    } else {
      setModalState("confirm_offer_trade");
      setModalText(null);
      setModalHeader("Trade resources and regions");
    }
  };
  const EnactProposal = () => {
    if (modalState == "confirm_enact_proposal") {
      apiFetch(villageAPI, {
        request: 'EnactProposal',
        proposal_id: currentProposal.proposal_id
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }
        setProposalDataState(response.data.proposalData);
        setCurrentProposal(null);
        setCurrentProposalKey(null);
        setPolicyDataState(response.data.policyData);
        setDisplayPolicyID(response.data.policyData.policy_id);
        setPolicyDisplay(getPolicyDisplayData(response.data.policyData.policy_id));
        setStrategicDataState(response.data.strategicData);
        setStrategicDisplayLeft(response.data.strategicData.find(item => item.village.name == villageName));
        setStrategicDisplayRight(response.data.strategicData.find(item => item.village.name == strategicDisplayRight.village.name));
        setModalText(response.data.response_message);
        setModalHeader("Confirmation");
        setModalState("response_message");
      });
    } else {
      setModalState("confirm_enact_proposal");
      setModalText("Are you sure you want to enact this proposal?");
      setModalHeader("Confirmation");
    }
  };
  const BoostVote = () => {
    if (modalState == "confirm_boost_vote") {
      apiFetch(villageAPI, {
        request: 'BoostVote',
        proposal_id: currentProposal.proposal_id
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }
        setProposalDataState(response.data.proposalData);
        setCurrentProposal(response.data.proposalData[currentProposalKey]);
        setModalText(response.data.response_message);
        setModalHeader("Confirmation");
        setModalState("response_message");
      });
    } else {
      setModalState("confirm_boost_vote");
      setModalText("Are you sure you wish to boost this vote?\nThe Kage will gain/lose 500 Reputation based on their decision. This will cost 500 Reputation when the proposal is enacted.");
      setModalHeader("Confirmation");
    }
  };
  const CancelVote = () => {
    if (modalState == "confirm_cancel_vote") {
      apiFetch(villageAPI, {
        request: 'CancelVote',
        proposal_id: currentProposal.proposal_id
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }
        setProposalDataState(response.data.proposalData);
        setCurrentProposal(response.data.proposalData[currentProposalKey]);
        setModalText(response.data.response_message);
        setModalHeader("Confirmation");
        setModalState("response_message");
      });
    } else {
      setModalState("confirm_cancel_vote");
      setModalText("Are you sure you wish to cancel your vote for this proposal?");
      setModalHeader("Confirmation");
    }
  };
  const SubmitVote = vote => {
    apiFetch(villageAPI, {
      request: 'SubmitVote',
      proposal_id: currentProposal.proposal_id,
      vote: vote
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      setProposalDataState(response.data.proposalData);
      setCurrentProposal(response.data.proposalData[currentProposalKey]);
    });
  };
  const ViewTrade = () => {
    setModalState("view_trade");
    setModalText(null);
    setModalHeader("View trade offer");
  };
  React.useEffect(() => {
    if (proposalDataState.length && currentProposal === null) {
      setCurrentProposal(proposalDataState[0]);
      setCurrentProposalKey(0);
      setProposalRepAdjustment(proposalDataState[0].votes.reduce((acc, vote) => acc + parseInt(vote.rep_adjustment), 0));
    }
  }, [proposalDataState]);
  return /*#__PURE__*/React.createElement(React.Fragment, null, modalState !== "closed" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "modal_backdrop"
  }), /*#__PURE__*/React.createElement("div", {
    className: "modal"
  }, /*#__PURE__*/React.createElement("div", {
    className: "modal_header"
  }, modalHeader), /*#__PURE__*/React.createElement("div", {
    className: "modal_text"
  }, modalText), modalState == "confirm_policy" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "modal_confirm_button",
    onClick: () => ChangePolicy()
  }, "Confirm"), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => setModalState("closed")
  }, "cancel")), modalState == "confirm_cancel_proposal" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "modal_confirm_button",
    onClick: () => CancelProposal()
  }, "Confirm"), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => setModalState("closed")
  }, "cancel")), modalState == "confirm_enact_proposal" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "modal_confirm_button",
    onClick: () => EnactProposal()
  }, "Confirm"), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => setModalState("closed")
  }, "cancel")), modalState == "confirm_boost_vote" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "modal_confirm_button",
    onClick: () => BoostVote()
  }, "Confirm"), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => setModalState("closed")
  }, "cancel")), modalState == "confirm_cancel_vote" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "modal_confirm_button",
    onClick: () => CancelVote()
  }, "Confirm"), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => setModalState("closed")
  }, "cancel")), modalState == "confirm_declare_war" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "modal_confirm_button",
    onClick: () => DeclareWar()
  }, "Confirm"), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => setModalState("closed")
  }, "cancel")), modalState == "confirm_offer_peace" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "modal_confirm_button",
    onClick: () => OfferPeace()
  }, "Confirm"), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => setModalState("closed")
  }, "cancel")), modalState == "confirm_form_alliance" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "modal_confirm_button",
    onClick: () => OfferAlliance()
  }, "Confirm"), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => setModalState("closed")
  }, "cancel")), modalState == "confirm_break_alliance" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "modal_confirm_button",
    onClick: () => BreakAlliance()
  }, "Confirm"), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => setModalState("closed")
  }, "cancel")), modalState == "confirm_offer_trade" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(TradeDisplay, {
    viewOnly: false,
    offeringVillageResources: resourceDataState,
    offeringVillageRegions: strategicDisplayLeft.regions,
    offeredResources: offeredResources,
    setOfferedResources: setOfferedResources,
    offeredRegions: offeredRegions,
    setOfferedRegions: setOfferedRegions,
    targetVillageResources: null,
    targetVillageRegions: strategicDisplayRight.regions,
    requestedResources: requestedResources,
    setRequestedResources: setRequestedResources,
    requestedRegions: requestedRegions,
    setRequestedRegions: setRequestedRegions
  }), /*#__PURE__*/React.createElement("div", {
    className: "modal_confirm_button",
    onClick: () => OfferTrade()
  }, "confirm"), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => setModalState("closed")
  }, "cancel")), modalState == "view_trade" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(TradeDisplay, {
    viewOnly: true,
    offeringVillageResources: null,
    offeringVillageRegions: null,
    offeredResources: currentProposal.trade_data.offered_resources,
    setOfferedResources: null,
    offeredRegions: currentProposal.trade_data.offered_regions,
    setOfferedRegions: null,
    targetVillageResources: null,
    targetVillageRegions: null,
    requestedResources: currentProposal.trade_data.requested_resources,
    setRequestedResources: null,
    requestedRegions: currentProposal.trade_data.requested_regions,
    setRequestedRegions: null
  }), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => setModalState("closed")
  }, "close")), modalState == "response_message" && /*#__PURE__*/React.createElement("div", {
    className: "modal_close_button",
    onClick: () => setModalState("closed")
  }, "close"))), /*#__PURE__*/React.createElement("div", {
    className: "kq_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "row first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "kage_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "kage_header"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Kage"), /*#__PURE__*/React.createElement("div", {
    className: "kage_kanji"
  }, getKageKanji(villageName))), kage.avatar_link && /*#__PURE__*/React.createElement("div", {
    className: "kage_avatar_wrapper"
  }, /*#__PURE__*/React.createElement("img", {
    className: "kage_avatar",
    src: kage.avatar_link
  })), !kage.avatar_link && /*#__PURE__*/React.createElement("div", {
    className: "kage_avatar_wrapper_empty"
  }, /*#__PURE__*/React.createElement("div", {
    className: "kage_avatar_fill"
  })), /*#__PURE__*/React.createElement("div", {
    className: "kage_nameplate_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "kage_nameplate_decoration nw"
  }), /*#__PURE__*/React.createElement("div", {
    className: "kage_nameplate_decoration ne"
  }), /*#__PURE__*/React.createElement("div", {
    className: "kage_nameplate_decoration se"
  }), /*#__PURE__*/React.createElement("div", {
    className: "kage_nameplate_decoration sw"
  }), /*#__PURE__*/React.createElement("div", {
    className: "kage_name"
  }, kage.user_name ? kage.user_name : "---"), /*#__PURE__*/React.createElement("div", {
    className: "kage_title"
  }, kage.seat_title + " of " + villageName)))), /*#__PURE__*/React.createElement("div", {
    className: "column second"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Proposals"), /*#__PURE__*/React.createElement("div", {
    className: "content box-primary"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_container_top"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_container_left"
  }, /*#__PURE__*/React.createElement("svg", {
    className: "previous_proposal_button",
    width: "25",
    height: "25",
    viewBox: "0 0 100 100",
    onClick: () => cycleProposal("decrement")
  }, /*#__PURE__*/React.createElement("polygon", {
    className: "previous_proposal_triangle_inner",
    points: "100,0 100,100 35,50"
  }), /*#__PURE__*/React.createElement("polygon", {
    className: "previous_proposal_triangle_outer",
    points: "65,0 65,100 0,50"
  })), /*#__PURE__*/React.createElement("div", {
    className: "previous_proposal_button_label"
  }, "previous")), /*#__PURE__*/React.createElement("div", {
    className: "proposal_container_middle"
  }, currentProposalKey !== null && /*#__PURE__*/React.createElement("div", {
    className: "proposal_count"
  }, "PROPOSAL ", currentProposalKey + 1, " OUT OF ", proposalDataState.length), currentProposalKey === null && /*#__PURE__*/React.createElement("div", {
    className: "proposal_count"
  }, "PROPOSAL 0 OUT OF ", proposalDataState.length), /*#__PURE__*/React.createElement("div", {
    className: "active_proposal_name_container"
  }, /*#__PURE__*/React.createElement("svg", {
    className: "proposal_decoration_nw",
    width: "18",
    height: "8"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "0,4 4,0 8,4 4,8",
    fill: "#ad9357"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "10,4 14,0 18,4 14,8",
    fill: "#ad9357"
  })), /*#__PURE__*/React.createElement("div", {
    className: "active_proposal_name"
  }, currentProposal ? currentProposal.name : "NO ACTIVE PROPOSALs"), /*#__PURE__*/React.createElement("svg", {
    className: "proposal_decoration_se",
    width: "18",
    height: "8"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "0,4 4,0 8,4 4,8",
    fill: "#ad9357"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "10,4 14,0 18,4 14,8",
    fill: "#ad9357"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "active_proposal_timer"
  }, currentProposal && currentProposal.vote_time_remaining !== null && currentProposal.vote_time_remaining, currentProposal && currentProposal.enact_time_remaining !== null && currentProposal.enact_time_remaining)), /*#__PURE__*/React.createElement("div", {
    className: "proposal_container_right"
  }, /*#__PURE__*/React.createElement("svg", {
    className: "next_proposal_button",
    width: "25",
    height: "25",
    viewBox: "0 0 100 100",
    onClick: () => cycleProposal("increment")
  }, /*#__PURE__*/React.createElement("polygon", {
    className: "next_proposal_triangle_inner",
    points: "0,0 0,100 65,50"
  }), /*#__PURE__*/React.createElement("polygon", {
    className: "next_proposal_triangle_outer",
    points: "35,0 35,100 100,50"
  })), /*#__PURE__*/React.createElement("div", {
    className: "next_proposal_button_label"
  }, "next"))), /*#__PURE__*/React.createElement("div", {
    className: "proposal_container_bottom"
  }, playerSeatState.seat_type == "kage" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "proposal_cancel_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: currentProposal ? "proposal_cancel_button" : "proposal_cancel_button disabled",
    onClick: () => CancelProposal()
  }, "cancel proposal")), currentProposal && (currentProposal.type == "offer_trade" || currentProposal.type == "accept_trade") && /*#__PURE__*/React.createElement("div", {
    className: "trade_view_button_wrapper alliance",
    onClick: () => ViewTrade()
  }, /*#__PURE__*/React.createElement("div", {
    className: "trade_view_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/icons/trade.png",
    className: "trade_view_button_icon"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "proposal_enact_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: currentProposal && (currentProposal.enact_time_remaining !== null || currentProposal.votes.length == seatDataState.filter(seat => seat.seat_type == "elder" && seat.seat_id != null).length) ? "proposal_enact_button" : "proposal_enact_button disabled",
    onClick: () => EnactProposal()
  }, "enact proposal"), proposalRepAdjustment > 0 && /*#__PURE__*/React.createElement("div", {
    className: "rep_change positive"
  }, "REPUATION GAIN: +", proposalRepAdjustment), proposalRepAdjustment < 0 && /*#__PURE__*/React.createElement("div", {
    className: "rep_change negative"
  }, "REPUTATION LOSS: ", proposalRepAdjustment))), playerSeatState.seat_type == "elder" && /*#__PURE__*/React.createElement(React.Fragment, null, !currentProposal && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "proposal_yes_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_yes_button disabled"
  }, "vote in favor")), /*#__PURE__*/React.createElement("div", {
    className: "proposal_no_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_no_button disabled"
  }, "vote against"))), currentProposal && currentProposal.vote_time_remaining != null && !currentProposal.votes.find(vote => vote.user_id == playerID) && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "proposal_yes_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_yes_button",
    onClick: () => SubmitVote(1)
  }, "vote in favor")), /*#__PURE__*/React.createElement("div", {
    className: "proposal_no_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_no_button",
    onClick: () => SubmitVote(0)
  }, "vote against"))), currentProposal && currentProposal.vote_time_remaining == null && !currentProposal.votes.find(vote => vote.user_id == playerID) && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "proposal_yes_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_yes_button disabled"
  }, "vote in favor")), /*#__PURE__*/React.createElement("div", {
    className: "proposal_no_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_no_button disabled"
  }, "vote against"))), currentProposal && currentProposal.vote_time_remaining != null && currentProposal.votes.find(vote => vote.user_id == playerID) && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "proposal_cancel_vote_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_cancel_vote_button",
    onClick: () => CancelVote()
  }, "change vote")), /*#__PURE__*/React.createElement("div", {
    className: "proposal_boost_vote_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_boost_vote_button",
    onClick: () => BoostVote()
  }, "boost vote"))), currentProposal && currentProposal.vote_time_remaining == null && currentProposal.votes.find(vote => vote.user_id == playerID) && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "proposal_cancel_vote_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_cancel_vote_button disabled"
  }, "cancel vote")), /*#__PURE__*/React.createElement("div", {
    className: "proposal_boost_vote_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_boost_vote_button disabled"
  }, "boost vote")))))), /*#__PURE__*/React.createElement("div", {
    className: "proposal_elder_header"
  }, "Elders"), /*#__PURE__*/React.createElement("div", {
    className: "elder_list"
  }, /*#__PURE__*/React.createElement("svg", {
    height: "0",
    width: "0"
  }, /*#__PURE__*/React.createElement("defs", null, /*#__PURE__*/React.createElement("filter", {
    id: "green_glow"
  }, /*#__PURE__*/React.createElement("feGaussianBlur", {
    in: "SourceAlpha",
    stdDeviation: "2",
    result: "blur"
  }), /*#__PURE__*/React.createElement("feFlood", {
    floodColor: "green",
    result: "floodColor"
  }), /*#__PURE__*/React.createElement("feComponentTransfer", {
    in: "blur",
    result: "opacityAdjustedBlur"
  }, /*#__PURE__*/React.createElement("feFuncA", {
    type: "linear",
    slope: "3"
  })), /*#__PURE__*/React.createElement("feComposite", {
    in: "floodColor",
    in2: "opacityAdjustedBlur",
    operator: "in",
    result: "coloredBlur"
  }), /*#__PURE__*/React.createElement("feMerge", null, /*#__PURE__*/React.createElement("feMergeNode", {
    in: "coloredBlur"
  }), /*#__PURE__*/React.createElement("feMergeNode", {
    in: "SourceGraphic"
  }))), /*#__PURE__*/React.createElement("filter", {
    id: "red_glow"
  }, /*#__PURE__*/React.createElement("feGaussianBlur", {
    in: "SourceAlpha",
    stdDeviation: "2",
    result: "blur"
  }), /*#__PURE__*/React.createElement("feFlood", {
    floodColor: "red",
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
  }))))), seatDataState.filter(elder => elder.seat_type === 'elder').map((elder, index) => /*#__PURE__*/React.createElement("div", {
    key: elder.seat_key,
    className: "elder_item"
  }, /*#__PURE__*/React.createElement("div", {
    className: "elder_vote_wrapper",
    style: {
      visibility: currentProposal && currentProposal.votes.find(vote => vote.user_id == elder.user_id) ? null : "hidden"
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "elder_vote"
  }, currentProposal && currentProposal.votes.find(vote => vote.user_id == elder.user_id && vote.vote == 1 && parseInt(vote.rep_adjustment) > 0) && /*#__PURE__*/React.createElement("img", {
    className: "vote_yes_image glow",
    src: "/images/v2/icons/yesvote.png"
  }), currentProposal && currentProposal.votes.find(vote => vote.user_id == elder.user_id && vote.vote == 0 && parseInt(vote.rep_adjustment) < 0) && /*#__PURE__*/React.createElement("img", {
    className: "vote_no_image glow",
    src: "/images/v2/icons/novote.png"
  }), currentProposal && currentProposal.votes.find(vote => vote.user_id == elder.user_id && vote.vote == 1 && parseInt(vote.rep_adjustment) == 0) && /*#__PURE__*/React.createElement("img", {
    className: "vote_yes_image",
    src: "/images/v2/icons/yesvote.png"
  }), currentProposal && currentProposal.votes.find(vote => vote.user_id == elder.user_id && vote.vote == 0 && parseInt(vote.rep_adjustment) == 0) && /*#__PURE__*/React.createElement("img", {
    className: "vote_no_image",
    src: "/images/v2/icons/novote.png"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "elder_avatar_wrapper"
  }, elder.avatar_link && /*#__PURE__*/React.createElement("img", {
    className: "elder_avatar",
    src: elder.avatar_link
  }), !elder.avatar_link && /*#__PURE__*/React.createElement("div", {
    className: "elder_avatar_fill"
  })), /*#__PURE__*/React.createElement("div", {
    className: "elder_name"
  }, elder.user_name ? /*#__PURE__*/React.createElement("a", {
    href: "/?id=6&user=" + elder.user_name
  }, elder.user_name) : "---"))))))), /*#__PURE__*/React.createElement("div", {
    className: "row second"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Village policy"), /*#__PURE__*/React.createElement("div", {
    className: "village_policy_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "village_policy_bonus_container"
  }, policyDisplay.bonuses.map((bonus, index) => /*#__PURE__*/React.createElement("div", {
    key: index,
    className: "policy_bonus_item"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "16",
    height: "16",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "25,20 50,45 25,70 0,45",
    fill: "#4a5e45"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "25,0 50,25 25,50 0,25",
    fill: "#6ab352"
  })), /*#__PURE__*/React.createElement("div", {
    className: "policy_bonus_text"
  }, bonus)))), displayPolicyID != policyDataState.policy_id && /*#__PURE__*/React.createElement("div", {
    className: playerSeatState.seat_type == "kage" ? "village_policy_change_button" : "village_policy_change_button disabled",
    onClick: () => ChangePolicy()
  }, "change policy"), /*#__PURE__*/React.createElement("div", {
    className: "village_policy_main_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "village_policy_main_inner"
  }, /*#__PURE__*/React.createElement("div", {
    className: "village_policy_banner",
    style: {
      backgroundImage: "url(" + policyDisplay.banner + ")"
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "village_policy_name_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "village_policy_name " + policyDisplay.glowClass
  }, policyDisplay.name)), /*#__PURE__*/React.createElement("div", {
    className: "village_policy_phrase"
  }, policyDisplay.phrase), /*#__PURE__*/React.createElement("div", {
    className: "village_policy_description"
  }, policyDisplay.description), displayPolicyID > 1 && /*#__PURE__*/React.createElement("div", {
    className: "village_policy_previous_wrapper"
  }, /*#__PURE__*/React.createElement("svg", {
    className: "previous_policy_button",
    width: "20",
    height: "20",
    viewBox: "0 0 100 100",
    onClick: () => cyclePolicy("decrement")
  }, /*#__PURE__*/React.createElement("polygon", {
    className: "previous_policy_triangle_inner",
    points: "100,0 100,100 35,50"
  }), /*#__PURE__*/React.createElement("polygon", {
    className: "previous_policy_triangle_outer",
    points: "65,0 65,100 0,50"
  }))), displayPolicyID < 4 && /*#__PURE__*/React.createElement("div", {
    className: "village_policy_next_wrapper"
  }, /*#__PURE__*/React.createElement("svg", {
    className: "next_policy_button",
    width: "20",
    height: "20",
    viewBox: "0 0 100 100",
    onClick: () => cyclePolicy("increment")
  }, /*#__PURE__*/React.createElement("polygon", {
    className: "next_policy_triangle_inner",
    points: "0,0 0,100 65,50"
  }), /*#__PURE__*/React.createElement("polygon", {
    className: "next_policy_triangle_outer",
    points: "35,0 35,100 100,50"
  }))))), /*#__PURE__*/React.createElement("div", {
    className: "village_policy_penalty_container"
  }, policyDisplay.penalties.map((penalty, index) => /*#__PURE__*/React.createElement("div", {
    key: index,
    className: "policy_penalty_item"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "16",
    height: "16",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "25,20 50,45 25,70 0,45",
    fill: "#4f1e1e"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "25,0 50,25 25,50 0,25",
    fill: "#ad4343"
  })), /*#__PURE__*/React.createElement("div", {
    className: "policy_penalty_text"
  }, penalty))))))), /*#__PURE__*/React.createElement("div", {
    className: "row third"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "kq_navigation_row"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Strategic information")))), /*#__PURE__*/React.createElement("div", {
    className: "row fourth"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_container"
  }, /*#__PURE__*/React.createElement(StrategicInfoItem, {
    strategicInfoData: strategicDisplayLeft,
    getPolicyDisplayData: getPolicyDisplayData
  }), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_navigation"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_navigation_diplomacy_buttons"
  }, strategicDisplayLeft.enemies.find(enemy => enemy == strategicDisplayRight.village.name) ? /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_wrapper war cancel",
    onClick: () => OfferPeace()
  }, /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_inner"
  })) : /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_wrapper war",
    onClick: () => DeclareWar()
  }, /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/images/icons/war.png",
    className: "diplomacy_action_button_icon"
  }))), strategicDisplayLeft.allies.find(ally => ally == strategicDisplayRight.village.name) ? /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_wrapper alliance cancel",
    onClick: () => BreakAlliance()
  }, /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_inner"
  })) : /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_wrapper alliance",
    onClick: () => OfferAlliance()
  }, /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/images/icons/ally.png",
    className: "diplomacy_action_button_icon"
  })))), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_navigation_village_buttons"
  }, villageName != "Stone" && /*#__PURE__*/React.createElement("div", {
    className: strategicDisplayRight.village.village_id == 1 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setStrategicDisplayRight(strategicDataState[0])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(1),
    className: "strategic_info_nav_button_icon"
  }))), villageName != "Cloud" && /*#__PURE__*/React.createElement("div", {
    className: strategicDisplayRight.village.village_id == 2 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setStrategicDisplayRight(strategicDataState[1])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(2),
    className: "strategic_info_nav_button_icon"
  }))), villageName != "Leaf" && /*#__PURE__*/React.createElement("div", {
    className: strategicDisplayRight.village.village_id == 3 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setStrategicDisplayRight(strategicDataState[2])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(3),
    className: "strategic_info_nav_button_icon"
  }))), villageName != "Sand" && /*#__PURE__*/React.createElement("div", {
    className: strategicDisplayRight.village.village_id == 4 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setStrategicDisplayRight(strategicDataState[3])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(4),
    className: "strategic_info_nav_button_icon"
  }))), villageName != "Mist" && /*#__PURE__*/React.createElement("div", {
    className: strategicDisplayRight.village.village_id == 5 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setStrategicDisplayRight(strategicDataState[4])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(5),
    className: "strategic_info_nav_button_icon"
  })))), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_navigation_diplomacy_buttons"
  }, !strategicDisplayLeft.enemies.find(enemy => enemy == strategicDisplayRight.village.name) && /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_wrapper alliance",
    onClick: () => OfferTrade()
  }, /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/icons/trade.png",
    className: "diplomacy_action_button_icon"
  }))))), /*#__PURE__*/React.createElement(StrategicInfoItem, {
    strategicInfoData: strategicDisplayRight,
    getPolicyDisplayData: getPolicyDisplayData
  }))))));
  function TradeDisplay({
    viewOnly,
    offeringVillageResources,
    offeringVillageRegions,
    offeredResources,
    setOfferedResources,
    offeredRegions,
    setOfferedRegions,
    targetVillageResources,
    targetVillageRegions,
    requestedResources,
    setRequestedResources,
    requestedRegions,
    setRequestedRegions
  }) {
    const toggleOfferedRegion = regionId => {
      setOfferedRegions(current => {
        // Check if the region is already selected
        if (current.includes(regionId)) {
          // If it is, filter it out (unselect it)
          return current.filter(id => id !== regionId);
        } else {
          // Otherwise, add it to the selected regions
          return [...current, regionId];
        }
      });
    };
    const toggleRequestedRegion = regionId => {
      setRequestedRegions(current => {
        // Check if the region is already selected
        if (current.includes(regionId)) {
          // If it is, filter it out (unselect it)
          return current.filter(id => id !== regionId);
        } else {
          // Otherwise, add it to the selected regions
          return [...current, regionId];
        }
      });
    };
    const handleOfferedResourcesChange = (resourceName, value) => {
      setOfferedResources(currentResources => currentResources.map(resource => resource.resource_name === resourceName ? {
        ...resource,
        count: value
      } : resource));
    };
    const handleRequestedResourcesChange = (resourceName, value) => {
      setRequestedResources(currentResources => currentResources.map(resource => resource.resource_name === resourceName ? {
        ...resource,
        count: value
      } : resource));
    };
    return viewOnly ? /*#__PURE__*/React.createElement("div", {
      className: "trade_display_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "trade_display_offer_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "header"
    }, "Offered Resources"), /*#__PURE__*/React.createElement("div", {
      className: "trade_display_resources"
    }, offeredResources.map((resource, index) => {
      const total = offeringVillageResources ? offeringVillageResources.find(total => total.resource_id === resource.resource_id).count : null;
      return /*#__PURE__*/React.createElement("div", {
        key: resource.resource_id,
        className: "trade_display_resource_wrapper"
      }, /*#__PURE__*/React.createElement("input", {
        type: "text",
        min: "0",
        max: total ? total : null,
        step: "100",
        placeholder: "0",
        className: "trade_display_resource_input",
        value: resource.count,
        onChange: e => handleOfferedResourcesChange(resource.resource_name, parseInt(e.target.value)),
        style: {
          userSelect: "none"
        },
        readOnly: true
      }), /*#__PURE__*/React.createElement("div", {
        className: "trade_display_resource_name"
      }, resource.resource_name), total && /*#__PURE__*/React.createElement("div", {
        className: "trade_display_resource_total"
      }, total));
    })), /*#__PURE__*/React.createElement("div", {
      className: "header"
    }, "Offered Regions"), /*#__PURE__*/React.createElement("div", {
      className: "trade_display_regions"
    }, offeredRegions.filter(region => region.region_id > 5).map((region, index) => /*#__PURE__*/React.createElement("div", {
      key: region.name,
      className: "trade_display_region_wrapper"
    }, /*#__PURE__*/React.createElement("div", {
      className: "trade_display_region_name"
    }, region.name))))), /*#__PURE__*/React.createElement("div", {
      className: "trade_display_request_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "header"
    }, "Requested Resources"), /*#__PURE__*/React.createElement("div", {
      className: "trade_display_resources"
    }, requestedResources.map((resource, index) => {
      const total = targetVillageResources ? targetVillageResources.find(total => total.resource_id === resource.resource_id).count : null;
      return /*#__PURE__*/React.createElement("div", {
        key: resource.resource_id,
        className: "trade_display_resource_wrapper"
      }, /*#__PURE__*/React.createElement("input", {
        type: "text",
        min: "0",
        max: total ? total : null,
        step: "100",
        placeholder: "0",
        className: "trade_display_resource_input",
        value: resource.count,
        onChange: e => handleRequestedResourcesChange(resource.resource_name, parseInt(e.target.value)),
        style: {
          userSelect: "none"
        },
        readOnly: true
      }), /*#__PURE__*/React.createElement("div", {
        className: "trade_display_resource_name"
      }, resource.resource_name), total && /*#__PURE__*/React.createElement("div", {
        className: "trade_display_resource_total"
      }, total));
    })), /*#__PURE__*/React.createElement("div", {
      className: "header"
    }, "Requested Regions"), /*#__PURE__*/React.createElement("div", {
      className: "trade_display_regions"
    }, requestedRegions.filter(region => region.region_id > 5).map((region, index) => /*#__PURE__*/React.createElement("div", {
      key: region.name,
      className: "trade_display_region_wrapper"
    }, /*#__PURE__*/React.createElement("div", {
      className: "trade_display_region_name"
    }, region.name)))))) : /*#__PURE__*/React.createElement("div", {
      className: "trade_display_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "trade_display_offer_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "header"
    }, "Offer Resources"), /*#__PURE__*/React.createElement("div", {
      className: "trade_display_resources"
    }, offeredResources.map((resource, index) => {
      const total = offeringVillageResources ? offeringVillageResources.find(total => total.resource_id === resource.resource_id).count : null;
      return /*#__PURE__*/React.createElement("div", {
        key: resource.resource_id,
        className: "trade_display_resource_wrapper"
      }, /*#__PURE__*/React.createElement("input", {
        type: "number",
        min: "0",
        max: total ? total : null,
        step: "100",
        placeholder: "0",
        className: "trade_display_resource_input",
        value: resource.count,
        onChange: e => handleOfferedResourcesChange(resource.resource_name, parseInt(e.target.value))
      }), /*#__PURE__*/React.createElement("div", {
        className: "trade_display_resource_name"
      }, resource.resource_name), total && /*#__PURE__*/React.createElement("div", {
        className: "trade_display_resource_total"
      }, total));
    })), /*#__PURE__*/React.createElement("div", {
      className: "header"
    }, "Offer Regions"), /*#__PURE__*/React.createElement("div", {
      className: "trade_display_regions"
    }, offeringVillageRegions.filter(region => region.region_id > 5).map((region, index) => /*#__PURE__*/React.createElement("div", {
      key: region.name,
      className: "trade_display_region_wrapper"
    }, /*#__PURE__*/React.createElement("div", {
      className: "trade_display_region_name"
    }, region.name), /*#__PURE__*/React.createElement("input", {
      type: "checkbox",
      checked: offeredRegions.includes(region.region_id),
      onChange: () => toggleOfferedRegion(region.region_id)
    }))))), /*#__PURE__*/React.createElement("div", {
      className: "trade_display_request_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "header"
    }, "Request Resources"), /*#__PURE__*/React.createElement("div", {
      className: "trade_display_resources"
    }, requestedResources.map((resource, index) => {
      const total = targetVillageResources ? targetVillageResources.find(total => total.resource_id === resource.resource_id).count : null;
      return /*#__PURE__*/React.createElement("div", {
        key: resource.resource_id,
        className: "trade_display_resource_wrapper"
      }, /*#__PURE__*/React.createElement("input", {
        type: "number",
        min: "0",
        max: total ? total : null,
        step: "100",
        placeholder: "0",
        className: "trade_display_resource_input",
        value: resource.count,
        onChange: e => handleRequestedResourcesChange(resource.resource_name, parseInt(e.target.value))
      }), /*#__PURE__*/React.createElement("div", {
        className: "trade_display_resource_name"
      }, resource.resource_name), total && /*#__PURE__*/React.createElement("div", {
        className: "trade_display_resource_total"
      }, total));
    })), /*#__PURE__*/React.createElement("div", {
      className: "header"
    }, "Request Regions"), /*#__PURE__*/React.createElement("div", {
      className: "trade_display_regions"
    }, targetVillageRegions.filter(region => region.region_id > 5).map((region, index) => /*#__PURE__*/React.createElement("div", {
      key: region.name,
      className: "trade_display_region_wrapper"
    }, /*#__PURE__*/React.createElement("div", {
      className: "trade_display_region_name"
    }, region.name), /*#__PURE__*/React.createElement("input", {
      type: "checkbox",
      checked: requestedRegions.includes(region.region_id),
      onChange: () => toggleRequestedRegion(region.region_id)
    }))))));
  }
  function cyclePolicy(direction) {
    var newPolicyID;
    switch (direction) {
      case "increment":
        newPolicyID = Math.min(4, displayPolicyID + 1);
        setDisplayPolicyID(newPolicyID);
        setPolicyDisplay(getPolicyDisplayData(newPolicyID));
        break;
      case "decrement":
        newPolicyID = Math.max(1, displayPolicyID - 1);
        setDisplayPolicyID(newPolicyID);
        setPolicyDisplay(getPolicyDisplayData(newPolicyID));
        break;
    }
  }
  function cycleProposal(direction) {
    if (proposalDataState.length == 0) {
      return;
    }
    var newProposalKey;
    switch (direction) {
      case "increment":
        newProposalKey = Math.min(proposalDataState.length - 1, currentProposalKey + 1);
        setCurrentProposalKey(newProposalKey);
        setCurrentProposal(proposalDataState[newProposalKey]);
        setProposalRepAdjustment(proposalDataState[newProposalKey].votes.reduce((acc, vote) => acc + vote.rep_adjustment, 0));
        break;
      case "decrement":
        newProposalKey = Math.max(0, currentProposalKey - 1);
        setCurrentProposalKey(newProposalKey);
        setCurrentProposal(proposalDataState[newProposalKey]);
        setProposalRepAdjustment(proposalDataState[newProposalKey].votes.reduce((acc, vote) => acc + vote.rep_adjustment, 0));
        break;
    }
  }
}
function WorldInfo({
  villageName,
  strategicDataState,
  getVillageIcon,
  StrategicInfoItem,
  getPolicyDisplayData
}) {
  const [strategicDisplayLeft, setStrategicDisplayLeft] = React.useState(strategicDataState.find(item => item.village.name == villageName));
  const [strategicDisplayRight, setStrategicDisplayRight] = React.useState(strategicDataState.find(item => item.village.name != villageName));
  return /*#__PURE__*/React.createElement("div", {
    className: "worldInfo_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "row first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Strategic information"), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_container"
  }, /*#__PURE__*/React.createElement(StrategicInfoItem, {
    strategicInfoData: strategicDisplayLeft,
    getPolicyDisplayData: getPolicyDisplayData
  }), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_navigation",
    style: {
      marginTop: "155px"
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_navigation_village_buttons"
  }, villageName != "Stone" && /*#__PURE__*/React.createElement("div", {
    className: strategicDisplayRight.village.village_id == 1 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setStrategicDisplayRight(strategicDataState[0])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(1),
    className: "strategic_info_nav_button_icon"
  }))), villageName != "Cloud" && /*#__PURE__*/React.createElement("div", {
    className: strategicDisplayRight.village.village_id == 2 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setStrategicDisplayRight(strategicDataState[1])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(2),
    className: "strategic_info_nav_button_icon"
  }))), villageName != "Leaf" && /*#__PURE__*/React.createElement("div", {
    className: strategicDisplayRight.village.village_id == 3 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setStrategicDisplayRight(strategicDataState[2])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(3),
    className: "strategic_info_nav_button_icon"
  }))), villageName != "Sand" && /*#__PURE__*/React.createElement("div", {
    className: strategicDisplayRight.village.village_id == 4 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setStrategicDisplayRight(strategicDataState[3])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(4),
    className: "strategic_info_nav_button_icon"
  }))), villageName != "Mist" && /*#__PURE__*/React.createElement("div", {
    className: strategicDisplayRight.village.village_id == 5 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setStrategicDisplayRight(strategicDataState[4])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(5),
    className: "strategic_info_nav_button_icon"
  }))))), /*#__PURE__*/React.createElement(StrategicInfoItem, {
    strategicInfoData: strategicDisplayRight,
    getPolicyDisplayData: getPolicyDisplayData
  })))));
}
function WarTable({
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
function StrategicInfoItem({
  strategicInfoData,
  getPolicyDisplayData
}) {
  function getStrategicInfoBanner(village_id) {
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
  return /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_item"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_name_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_name"
  }, strategicInfoData.village.name), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_policy"
  }, getPolicyDisplayData(strategicInfoData.village.policy_id).name)), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_banner",
    style: {
      backgroundImage: "url(" + getStrategicInfoBanner(strategicInfoData.village.village_id) + ")"
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_top"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_kage_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_label"
  }, "kage:"), strategicInfoData.seats.find(seat => seat.seat_type == "kage").user_name ? /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_seat"
  }, /*#__PURE__*/React.createElement("a", {
    href: "/?id=6&user=" + strategicInfoData.seats.find(seat => seat.seat_type == "kage").user_name
  }, strategicInfoData.seats.find(seat => seat.seat_type == "kage").user_name)) : /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_seat"
  }, "-None-")), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_elder_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_label"
  }, "elders:"), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_elders"
  }, strategicInfoData.seats.filter(seat => seat.seat_type == "elder").map((elder, index) => elder.user_name ? /*#__PURE__*/React.createElement("div", {
    key: elder.seat_key,
    className: "strategic_info_seat"
  }, /*#__PURE__*/React.createElement("a", {
    href: "/?id=6&user=" + elder.user_name
  }, elder.user_name)) : /*#__PURE__*/React.createElement("div", {
    key: elder.seat_key,
    className: "strategic_info_seat"
  }, "-None-")))), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_points_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_label"
  }, "points:"), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_points"
  }, strategicInfoData.village.points)), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_enemy_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_label"
  }, "at war with ", /*#__PURE__*/React.createElement("img", {
    className: "strategic_info_war_icon",
    src: "/images/icons/war.png"
  })), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_relations"
  }, strategicInfoData.enemies.map((enemy, index) => /*#__PURE__*/React.createElement("div", {
    key: index,
    className: "strategic_info_relation_item"
  }, enemy))))), /*#__PURE__*/React.createElement("div", {
    className: "column"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_population_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_label"
  }, "village ninja:"), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_population"
  }, strategicInfoData.population.map((rank, index) => /*#__PURE__*/React.createElement("div", {
    key: rank.rank,
    className: "strategic_info_population_item"
  }, rank.count + " " + rank.rank)), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_population_item total"
  }, strategicInfoData.population.reduce((acc, rank) => acc + rank.count, 0), " total"))), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_ally_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_label"
  }, "allied with ", /*#__PURE__*/React.createElement("img", {
    className: "strategic_info_war_icon",
    src: "/images/icons/ally.png"
  })), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_relations"
  }, strategicInfoData.allies.map((ally, index) => /*#__PURE__*/React.createElement("div", {
    key: index,
    className: "strategic_info_relation_item"
  }, ally)))))), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_bottom"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_region_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_label"
  }, "regions owned:"), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_regions"
  }, strategicInfoData.regions.map((region, index) => /*#__PURE__*/React.createElement("div", {
    key: region.name,
    className: "strategic_info_region_item"
  }, region.name))))), /*#__PURE__*/React.createElement("div", {
    className: "column"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_resource_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_label"
  }, "resource points:"), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_supply_points"
  }, Object.values(strategicInfoData.supply_points).map((supply_point, index) => /*#__PURE__*/React.createElement("div", {
    key: index,
    className: "strategic_info_supply_item"
  }, /*#__PURE__*/React.createElement("span", {
    className: "supply_point_name"
  }, supply_point.name), " x", supply_point.count)))))));
}
const TimeGrid = ({
  setSelectedTimesUTC,
  startHourUTC = 0
}) => {
  const [selectedTimes, setSelectedTimes] = React.useState([]);
  const timeSlots = generateSlotsForTimeZone(startHourUTC);
  function generateSlotsForTimeZone(startHour) {
    return Array.from({
      length: 24
    }, (slot, index) => (index + startHour) % 24).map(hour => luxon.DateTime.fromObject({
      hour
    }, {
      zone: 'utc'
    }).setZone('local'));
  }
  ;
  function toggleSlot(time) {
    const formattedTime = time.toFormat('HH:mm');
    let newSelectedTimes;
    if (selectedTimes.includes(formattedTime)) {
      newSelectedTimes = selectedTimes.filter(t => t !== formattedTime);
    } else {
      newSelectedTimes = [...selectedTimes, formattedTime];
    }
    setSelectedTimes(newSelectedTimes);
    setSelectedTimesUTC(convertSlotsToUTC(newSelectedTimes));
  }
  ;
  function convertSlotsToUTC(times) {
    return times.map(time => luxon.DateTime.fromFormat(time, 'HH:mm', {
      zone: 'local'
    }).setZone('utc').toFormat('HH:mm'));
  }
  ;
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "timeslot_container"
  }, timeSlots.map(time => /*#__PURE__*/React.createElement("div", {
    key: time.toFormat('HH:mm'),
    onClick: () => toggleSlot(time),
    className: selectedTimes.includes(time.toFormat('HH:mm')) ? "timeslot selected" : "timeslot"
  }, time.toFormat('HH:mm'), /*#__PURE__*/React.createElement("div", {
    className: "timeslot_label"
  }, time.toFormat('h:mm') + " " + time.toFormat('a'))))), /*#__PURE__*/React.createElement("div", {
    className: "slot_requirements_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "slot_requirements"
  }, "At least 12 slots must be selected for availability."), /*#__PURE__*/React.createElement("div", {
    className: "slot_count_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "slot_count"
  }, "Selected: ", selectedTimes.length))));
};
const TimeGridResponse = ({
  availableTimesUTC,
  setSelectedTimeUTC,
  startHourUTC = 0
}) => {
  const [selectedTime, setSelectedTime] = React.useState(null);
  const timeSlots = generateSlotsForTimeZone(startHourUTC);
  function generateSlotsForTimeZone(startHour) {
    return Array.from({
      length: 24
    }, (slot, index) => (index + startHour) % 24).map(hour => luxon.DateTime.fromObject({
      hour
    }, {
      zone: 'utc'
    }).toLocal());
  }
  ;
  function toggleSlot(time) {
    const formattedTimeLocal = time.toFormat('HH:mm');
    const formattedTimeUTC = time.toUTC().toFormat('HH:mm');
    if (selectedTime === formattedTimeLocal) {
      setSelectedTime(null);
      setSelectedTimeUTC(null);
    } else if (availableTimesUTC.includes(formattedTimeUTC)) {
      setSelectedTime(formattedTimeLocal);
      setSelectedTimeUTC(formattedTimeUTC);
    }
  }
  ;
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "timeslot_container"
  }, timeSlots.map(time => {
    const formattedTimeLocal = time.toFormat('HH:mm');
    const formattedTimeUTC = time.toUTC().toFormat('HH:mm');
    const isAvailable = availableTimesUTC.includes(formattedTimeUTC);
    const slotClass = isAvailable ? selectedTime === formattedTimeLocal ? "timeslot selected" : "timeslot" : "timeslot unavailable";
    return /*#__PURE__*/React.createElement("div", {
      key: formattedTimeLocal,
      onClick: () => toggleSlot(time),
      className: slotClass
    }, formattedTimeLocal, /*#__PURE__*/React.createElement("div", {
      className: "timeslot_label"
    }, time.toFormat('h:mm') + " " + time.toFormat('a')));
  })));
};
const ChallengeContainer = ({
  playerID,
  challengeDataState,
  CancelChallenge,
  AcceptChallenge,
  LockChallenge
}) => {
  return /*#__PURE__*/React.createElement(React.Fragment, null, challengeDataState.length > 0 && /*#__PURE__*/React.createElement("div", {
    className: "challenge_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Challenges"), /*#__PURE__*/React.createElement("div", {
    className: "challenge_list"
  }, challengeDataState && challengeDataState.filter(challenge => challenge.challenger_id === playerID).map((challenge, index) => /*#__PURE__*/React.createElement("div", {
    key: challenge.request_id,
    className: "challenge_item"
  }, /*#__PURE__*/React.createElement("div", {
    className: "challenge_avatar_wrapper"
  }, /*#__PURE__*/React.createElement("img", {
    className: "challenge_avatar",
    src: challenge.seat_holder_avatar
  })), /*#__PURE__*/React.createElement("div", {
    className: "challenge_details"
  }, /*#__PURE__*/React.createElement("div", {
    className: "challenge_header"
  }, "ACTIVE CHALLENGE"), /*#__PURE__*/React.createElement("div", null, "Seat Holder: ", /*#__PURE__*/React.createElement("a", {
    href: "/?id=6&user=" + challenge.seat_holder_name
  }, challenge.seat_holder_name)), /*#__PURE__*/React.createElement("div", null, "Time: ", challenge.start_time ? luxon.DateTime.fromSeconds(challenge.start_time).toLocal() <= luxon.DateTime.local() ? /*#__PURE__*/React.createElement("span", {
    className: "challenge_time_now"
  }, "NOW") : luxon.DateTime.fromSeconds(challenge.start_time).toLocal().toFormat("LLL d, h:mm a") : /*#__PURE__*/React.createElement("span", {
    className: "challenge_time_pending"
  }, "PENDING")), challenge.start_time && luxon.DateTime.fromSeconds(challenge.start_time).toLocal() >= luxon.DateTime.local() && !challenge.challenger_locked && /*#__PURE__*/React.createElement("div", {
    className: "challenge_button lock disabled"
  }, "lock in", /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/icons/unlocked.png"
  })), challenge.start_time && luxon.DateTime.fromSeconds(challenge.start_time).toLocal() <= luxon.DateTime.local() && !challenge.challenger_locked && /*#__PURE__*/React.createElement("div", {
    className: "challenge_button lock",
    onClick: () => LockChallenge(challenge)
  }, "lock in", /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/icons/unlocked.png"
  })), challenge.start_time == null && /*#__PURE__*/React.createElement("div", {
    className: "challenge_button cancel",
    onClick: () => CancelChallenge()
  }, "cancel"), challenge.challenger_locked && /*#__PURE__*/React.createElement("div", {
    className: "challenge_button locked"
  }, "locked in", /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/icons/locked.png"
  }))))), challengeDataState && challengeDataState.filter(challenge => challenge.challenger_id !== playerID).map((challenge, index) => /*#__PURE__*/React.createElement("div", {
    key: challenge.request_id,
    className: "challenge_item"
  }, /*#__PURE__*/React.createElement("div", {
    className: "challenge_avatar_wrapper"
  }, /*#__PURE__*/React.createElement("img", {
    className: "challenge_avatar",
    src: challenge.challenger_avatar
  })), /*#__PURE__*/React.createElement("div", {
    className: "challenge_details"
  }, /*#__PURE__*/React.createElement("div", {
    className: "challenge_header"
  }, "CHALLENGER ", index + 1), /*#__PURE__*/React.createElement("div", null, "Challenger: ", /*#__PURE__*/React.createElement("a", {
    href: "/?id=6&user=" + challenge.challenger_name
  }, challenge.challenger_name)), /*#__PURE__*/React.createElement("div", null, "Time: ", challenge.start_time ? luxon.DateTime.fromSeconds(challenge.start_time).toLocal() <= luxon.DateTime.local() ? /*#__PURE__*/React.createElement("span", {
    className: "challenge_time_now"
  }, "NOW") : luxon.DateTime.fromSeconds(challenge.start_time).toLocal().toFormat("LLL d, h:mm a") : /*#__PURE__*/React.createElement("span", {
    className: "challenge_time_pending"
  }, "PENDING")), challenge.start_time && luxon.DateTime.fromSeconds(challenge.start_time).toLocal() >= luxon.DateTime.local() && !challenge.seat_holder_locked && /*#__PURE__*/React.createElement("div", {
    className: "challenge_button lock disabled"
  }, "lock in", /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/icons/unlocked.png"
  })), challenge.start_time && luxon.DateTime.fromSeconds(challenge.start_time).toLocal() <= luxon.DateTime.local() && !challenge.seat_holder_locked && /*#__PURE__*/React.createElement("div", {
    className: "challenge_button lock",
    onClick: () => LockChallenge(challenge)
  }, "lock in", /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/icons/unlocked.png"
  })), challenge.start_time == null && /*#__PURE__*/React.createElement("div", {
    className: "challenge_button schedule",
    onClick: () => AcceptChallenge(challenge)
  }, "schedule"), challenge.seat_holder_locked && /*#__PURE__*/React.createElement("div", {
    className: "challenge_button locked"
  }, "locked in", /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/icons/locked.png"
  })))))), /*#__PURE__*/React.createElement("svg", {
    style: {
      marginTop: "45px"
    },
    width: "100%",
    height: "1"
  }, /*#__PURE__*/React.createElement("line", {
    x1: "0%",
    y1: "1",
    x2: "100%",
    y2: "1",
    stroke: "#77694e",
    strokeWidth: "1"
  }))));
};
window.Village = Village;