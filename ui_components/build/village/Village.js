import { apiFetch } from "../utils/network.js";
function Village({
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
  strategicData
}) {
  const [playerSeatState, setPlayerSeatState] = React.useState(playerSeat);
  const [policyDataState, setPolicyDataState] = React.useState(policyData);
  const [seatDataState, setSeatDataState] = React.useState(seatData);
  const [pointsDataState, setPointsDataState] = React.useState(pointsData);
  const [diplomacyDataState, setDiplomacyDataState] = React.useState(diplomacyData);
  const [resourceDataState, setResourceDataState] = React.useState(resourceData);
  const [proposalDataState, setProposalDataState] = React.useState(proposalData);
  const [strategicDataState, setStrategicDataState] = React.useState(strategicData);
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
        data.banner = "/images/v2/decorations/policy_banners/growthpolicy.png";
        data.name = "From the Ashes";
        data.phrase = "bonds forged, courage shared.";
        data.description = "In unity, find the strength to overcome.\nOne village, one heart, one fight.";
        data.bonuses = ["25% increased Caravan speed", "+5% training speed", "+100% resource production for home region", "Free incoming village transfer"];
        data.penalties = ["Cannot declare War", "-25 Food/hour"];
        data.glowClass = "growth_glow";
        break;
      case 2:
        data.banner = "/images/v2/decorations/policy_banners/espionagepolicy.png";
        data.name = "Eye of the Storm";
        data.phrase = "half truths, all lies.";
        data.description = "Become informants dealing in truths and lies.\nDeceive, divide and destroy.";
        data.bonuses = ["25% increased Infiltrate speed", "+1 Loot gain from Infiltrate", "+1 Stealth", "+5 Loot Capacity"];
        data.penalties = ["-25 Wealth/hour"];
        data.glowClass = "espionage_glow";
        break;
      case 3:
        data.banner = "/images/v2/decorations/policy_banners/defensepolicy.png";
        data.name = "Fortress of Solitude";
        data.phrase = "vigilant minds, enduring hearts.";
        data.description = "Show the might of will unyielding.\nPrepare, preserve, prevail.";
        data.bonuses = ["25% increased Reinforce speed", "+1 Defense gain from Reinforce", "+1 Scouting", "Increased Patrol strength"];
        data.penalties = ["-25 Materials/hour"];
        data.glowClass = "defense_glow";
        break;
      case 4:
        data.banner = "/images/v2/decorations/policy_banners/warpolicy.png";
        data.name = "Forged in Flames";
        data.phrase = "blades sharp, minds sharper.";
        data.description = "Lead your village on the path of a warmonger.\nFeel no fear, no hesitation, no doubt.";
        data.bonuses = ["25% increased Raid speed", "+1 Defense damage from Raid", "+1 Village Point from PvP", "Faster Patrol respawn"];
        data.penalties = ["Cannot form Alliances", "-25 Materials/hour"];
        data.glowClass = "war_glow";
        break;
      case 5:
        data.banner = "/images/v2/decorations/policy_banners/prosperitypolicy.png";
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
    className: "nav_button disabled"
  }, "world info"), /*#__PURE__*/React.createElement("div", {
    className: "nav_button disabled"
  }, "war table"), /*#__PURE__*/React.createElement("div", {
    className: "nav_button disabled"
  }, "members & teams"), /*#__PURE__*/React.createElement("div", {
    className: playerSeatState.seat_id != null ? "nav_button" : "nav_button disabled",
    onClick: () => setVillageTab("kageQuarters")
  }, "kage's quarters")), villageTab == "villageHQ" && /*#__PURE__*/React.createElement(VillageHQ, {
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
    clanData: clanData,
    handleErrors: handleErrors,
    getKageKanji: getKageKanji,
    getVillageIcon: getVillageIcon,
    getPolicyDisplayData: getPolicyDisplayData
  }), villageTab == "kageQuarters" && /*#__PURE__*/React.createElement(KageQuarters, {
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
    handleErrors: handleErrors,
    getKageKanji: getKageKanji,
    getVillageIcon: getVillageIcon,
    getPolicyDisplayData: getPolicyDisplayData,
    StrategicInfoItem: StrategicInfoItem
  }));
}
function VillageHQ({
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
  clanData,
  handleErrors,
  getKageKanji,
  getVillageIcon,
  getPolicyDisplayData
}) {
  const [modalState, setModalState] = React.useState("closed");
  const [resourceDaysToShow, setResourceDaysToShow] = React.useState(1);
  const [policyDisplay, setPolicyDisplay] = React.useState(getPolicyDisplayData(policyDataState.policy_id));
  const modalText = React.useRef(null);
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
      modalText.current = response.data.response_message;
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
        modalText.current = response.data.response_message;
        setModalState("response_message");
      });
    } else {
      setModalState("confirm_resign");
      modalText.current = "Are you sure you wish to resign from your current position?";
    }
  };
  const totalPopulation = populationData.reduce((acc, rank) => acc + rank.count, 0);
  const kage = seatDataState.find(seat => seat.seat_type === 'kage');
  return /*#__PURE__*/React.createElement(React.Fragment, null, modalState !== "closed" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "modal_backdrop"
  }), /*#__PURE__*/React.createElement("div", {
    className: "modal"
  }, /*#__PURE__*/React.createElement("div", {
    className: "modal_header"
  }, "Confirmation"), /*#__PURE__*/React.createElement("div", {
    className: "modal_text"
  }, modalText.current), modalState == "confirm_resign" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "modal_confirm_button",
    onClick: () => Resign()
  }, "confirm"), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => setModalState("closed")
  }, "cancel")), modalState == "response_message" && /*#__PURE__*/React.createElement("div", {
    className: "modal_close_button",
    onClick: () => setModalState("closed")
  }, "close"))), /*#__PURE__*/React.createElement("div", {
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
    className: "kage_challenge_button"
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
  }, elder.user_name ? elder.user_name : "---"), elder.seat_id && elder.seat_id == playerSeatState.seat_id && /*#__PURE__*/React.createElement("div", {
    className: "elder_resign_button",
    onClick: () => Resign()
  }, "resign"), !elder.seat_id && /*#__PURE__*/React.createElement("div", {
    className: playerSeatState.seat_id ? "elder_claim_button disabled" : "elder_claim_button",
    onClick: playerSeatState.seat_id ? null : () => ClaimSeat("elder")
  }, "claim"), elder.seat_id && elder.seat_id != playerSeatState.seat_id && /*#__PURE__*/React.createElement("div", {
    className: "elder_challenge_button"
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
  const [modalState, setModalState] = React.useState("closed");
  const modalText = React.useRef(null);
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
        modalText.current = response.data.response_message;
        setModalState("response_message");
      });
    } else {
      setModalState("confirm_policy");
      modalText.current = "Are you sure you want to change policies? You will be unable to select a new policy for 14 days.";
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
        modalText.current = response.data.response_message;
        setModalState("response_message");
      });
    } else {
      setModalState("confirm_cancel_proposal");
      modalText.current = "Are you sure you want to cancel this proposal?";
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
        if (response.data.proposalData) {
          setCurrentProposal(response.data.proposalData[0]);
          setCurrentProposalKey(0);
        } else {
          setCurrentProposal(null);
          setCurrentProposalKey(null);
        }
        setPolicyDataState(response.data.policyData);
        setDisplayPolicyID(response.data.policyData.policy_id);
        setPolicyDisplay(getPolicyDisplayData(response.data.policyData.policy_id));
        modalText.current = response.data.response_message;
        setModalState("response_message");
      });
    } else {
      setModalState("confirm_enact_proposal");
      modalText.current = "Are you sure you want to enact this proposal?";
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
        modalText.current = response.data.response_message;
        setModalState("response_message");
      });
    } else {
      setModalState("confirm_boost_vote");
      modalText.current = "Are you sure you wish to boost this vote?\nThe Kage will gain/lose 500 Reputation based on their decision. This will cost 500 Reputation when the proposal is enacted.";
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
        modalText.current = response.data.response_message;
        setModalState("response_message");
      });
    } else {
      setModalState("confirm_cancel_vote");
      modalText.current = "Are you sure you wish to cancel your vote for this proposal?";
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
  }, "Confirmation"), /*#__PURE__*/React.createElement("div", {
    className: "modal_text"
  }, modalText.current), modalState == "confirm_policy" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
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
  }, "cancel")), modalState == "response_message" && /*#__PURE__*/React.createElement("div", {
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
    className: "content"
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
  }, /*#__PURE__*/React.createElement("div", {
    className: "active_proposal_name"
  }, currentProposal ? currentProposal.name : "NO ACTIVE PROPOSALs")), /*#__PURE__*/React.createElement("div", {
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
  }, "cancel proposal")), /*#__PURE__*/React.createElement("div", {
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
  }, "vote against"))), currentProposal && currentProposal.vote_time_remaining != null && !currentProposal.votes.find(vote => vote.user_id == playerSeatState.user_id) && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "proposal_yes_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_yes_button",
    onClick: () => SubmitVote(1)
  }, "vote in favor")), /*#__PURE__*/React.createElement("div", {
    className: "proposal_no_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_no_button",
    onClick: () => SubmitVote(0)
  }, "vote against"))), currentProposal && currentProposal.vote_time_remaining == null && !currentProposal.votes.find(vote => vote.user_id == playerSeatState.user_id) && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "proposal_yes_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_yes_button disabled"
  }, "vote in favor")), /*#__PURE__*/React.createElement("div", {
    className: "proposal_no_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_no_button disabled"
  }, "vote against"))), currentProposal && currentProposal.vote_time_remaining != null && currentProposal.votes.find(vote => vote.user_id == playerSeatState.user_id) && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "proposal_cancel_vote_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_cancel_vote_button",
    onClick: () => CancelVote()
  }, "change vote")), /*#__PURE__*/React.createElement("div", {
    className: "proposal_boost_vote_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_boost_vote_button",
    onClick: () => BoostVote()
  }, "boost vote"))), currentProposal && currentProposal.vote_time_remaining == null && currentProposal.votes.find(vote => vote.user_id == playerSeatState.user_id) && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
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
  }, elder.user_name ? elder.user_name : "---"))))))), /*#__PURE__*/React.createElement("div", {
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
    strategicInfoData: strategicDisplayLeft
  }), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_navigation"
  }, villageName != "Stone" && /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_wrapper stone",
    onClick: () => setStrategicDisplayRight(strategicDataState[0])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(1),
    className: "strategic_info_nav_button_icon"
  }))), villageName != "Cloud" && /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_wrapper cloud",
    onClick: () => setStrategicDisplayRight(strategicDataState[1])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(2),
    className: "strategic_info_nav_button_icon"
  }))), villageName != "Leaf" && /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_wrapper leaf",
    onClick: () => setStrategicDisplayRight(strategicDataState[2])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(3),
    className: "strategic_info_nav_button_icon"
  }))), villageName != "Sand" && /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_wrapper sand",
    onClick: () => setStrategicDisplayRight(strategicDataState[3])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(4),
    className: "strategic_info_nav_button_icon"
  }))), villageName != "Mist" && /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_wrapper mist",
    onClick: () => setStrategicDisplayRight(strategicDataState[4])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(5),
    className: "strategic_info_nav_button_icon"
  })))), /*#__PURE__*/React.createElement(StrategicInfoItem, {
    strategicInfoData: strategicDisplayRight
  }))))));
  function cyclePolicy(direction) {
    var newPolicyID;
    switch (direction) {
      case "increment":
        newPolicyID = Math.min(4, displayPolicyID + 1);
        setDisplayPolicyID(newPolicyID);
        setPolicyDisplay(getPolicyDisplayData(newPolicyID));
        setProposalRepAdjustment(proposalDataState[newPolicyID].votes.reduce((acc, vote) => acc + vote.rep_adjustment, 0));
        break;
      case "decrement":
        newPolicyID = Math.max(1, displayPolicyID - 1);
        setDisplayPolicyID(newPolicyID);
        setPolicyDisplay(getPolicyDisplayData(newPolicyID));
        setProposalRepAdjustment(proposalDataState[newPolicyID].votes.reduce((acc, vote) => acc + vote.rep_adjustment, 0));
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
        newProposalKey = Math.min(activeProposals.length - 1, currentProposalKey + 1);
        setCurrentProposalKey(newProposalKey);
        setCurrentProposal(activeProposals[newProposalKey]);
        break;
      case "decrement":
        newProposalKey = Math.max(0, currentProposalKey - 1);
        setCurrentProposalKey(newProposalKey);
        setCurrentProposal(activeProposals[newProposalKey]);
        break;
    }
  }
}
function StrategicInfoItem({
  strategicInfoData
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_item"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_banner"
  }), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_name"
  }, strategicInfoData.village.name), /*#__PURE__*/React.createElement("div", {
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
  }, strategicInfoData.enemies.map((ally, index) => /*#__PURE__*/React.createElement("div", {
    key: index,
    className: "strategic_info_relation_item"
  }, ally))))), /*#__PURE__*/React.createElement("div", {
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
window.Village = Village;