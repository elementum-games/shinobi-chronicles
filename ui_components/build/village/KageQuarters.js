import { apiFetch } from "../utils/network.js";
import { StrategicInfoItem } from "./StrategicInfoItem.js";
import { useModal } from '../utils/modalContext.js';
import { TradeDisplay } from "./TradeDisplay.js";
import KageDisplay from "./KageDisplay.js";
import VillagePolicy from "./VillagePolicy.js";
import { getVillageIcon } from "./villageUtils.js";
export function KageQuarters({
  playerSeatState,
  villageName,
  villageApiUrl,
  policyDataState,
  setPolicyDataState,
  seatDataState,
  resourceDataState,
  setResourceDataState,
  proposalDataState,
  setProposalDataState,
  strategicDataState,
  setStrategicDataState,
  buildingUpgradeDataState,
  setBuildingUpgradeDataState,
  handleErrors
}) {
  const kage = seatDataState.find(seat => seat.seat_type === 'kage');
  const [currentProposal, setCurrentProposal] = React.useState(null);
  const [currentProposalKey, setCurrentProposalKey] = React.useState(null);
  const [displayPolicyID, setDisplayPolicyID] = React.useState(policyDataState.policy_id);
  const [playerVillageData, setPlayerVillageData] = React.useState(strategicDataState.find(item => item.village.name === villageName));
  const [viewingTargetVillage, setViewingTargetVillage] = React.useState(strategicDataState.find(item => item.village.name !== villageName));
  const offeredResources = React.useRef([{
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
  const offeredRegions = React.useRef([]);
  const requestedResources = React.useRef([{
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
  const requestedRegions = React.useRef([]);
  const {
    openModal
  } = useModal();
  const ChangePolicy = () => {
    apiFetch(villageApiUrl, {
      request: 'CreateProposal',
      type: 'policy',
      policy_id: displayPolicyID
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      setProposalDataState(response.data.proposalData);
      openModal({
        header: 'Confirmation',
        text: response.data.response_message,
        ContentComponent: null,
        onConfirm: null
      });
    });
  };
  const DeclareWar = () => {
    apiFetch(villageApiUrl, {
      request: 'CreateProposal',
      type: 'declare_war',
      target_village_id: viewingTargetVillage.village.village_id
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      setProposalDataState(response.data.proposalData);
      openModal({
        header: 'Confirmation',
        text: response.data.response_message,
        ContentComponent: null,
        onConfirm: null
      });
    });
  };
  const OfferPeace = () => {
    apiFetch(villageApiUrl, {
      request: 'CreateProposal',
      type: 'offer_peace',
      target_village_id: viewingTargetVillage.village.village_id
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      setProposalDataState(response.data.proposalData);
      openModal({
        header: 'Confirmation',
        text: response.data.response_message,
        ContentComponent: null,
        onConfirm: null
      });
    });
  };
  const OfferAlliance = () => {
    apiFetch(villageApiUrl, {
      request: 'CreateProposal',
      type: 'offer_alliance',
      target_village_id: viewingTargetVillage.village.village_id
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      setProposalDataState(response.data.proposalData);
      openModal({
        header: 'Confirmation',
        text: response.data.response_message,
        ContentComponent: null,
        onConfirm: null
      });
    });
  };
  const BreakAlliance = () => {
    apiFetch(villageApiUrl, {
      request: 'CreateProposal',
      type: 'break_alliance',
      target_village_id: viewingTargetVillage.village.village_id
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      setProposalDataState(response.data.proposalData);
      openModal({
        header: 'Confirmation',
        text: response.data.response_message,
        ContentComponent: null,
        onConfirm: null
      });
    });
  };
  const CancelProposal = () => {
    apiFetch(villageApiUrl, {
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
      openModal({
        header: 'Confirmation',
        text: response.data.response_message,
        ContentComponent: null,
        onConfirm: null
      });
    });
  };
  const OfferTrade = () => {
    apiFetch(villageApiUrl, {
      request: 'CreateProposal',
      type: 'offer_trade',
      target_village_id: viewingTargetVillage.village.village_id,
      offered_resources: offeredResources.current,
      offered_regions: offeredRegions.current,
      requested_resources: requestedResources.current,
      requested_regions: requestedRegions.current
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      setProposalDataState(response.data.proposalData);
      openModal({
        header: 'Confirmation',
        text: response.data.response_message,
        ContentComponent: null,
        onConfirm: null
      });
    });
  };
  const EnactProposal = () => {
    apiFetch(villageApiUrl, {
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
      setStrategicDataState(response.data.strategicData);
      setPlayerVillageData(response.data.strategicData.find(item => item.village.name === villageName));
      setViewingTargetVillage(response.data.strategicData.find(item => item.village.name === viewingTargetVillage.village.name));
      setBuildingUpgradeDataState(response.data.buildingUpgradeData);
      setResourceDataState(response.data.resourceData);
      openModal({
        header: 'Confirmation',
        text: response.data.response_message,
        ContentComponent: null,
        onConfirm: null
      });
    });
  };
  const BoostVote = () => {
    apiFetch(villageApiUrl, {
      request: 'BoostVote',
      proposal_id: currentProposal.proposal_id
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      setProposalDataState(response.data.proposalData);
      setCurrentProposal(response.data.proposalData[currentProposalKey]);
      openModal({
        header: 'Confirmation',
        text: response.data.response_message,
        ContentComponent: null,
        onConfirm: null
      });
    });
  };
  const CancelVote = () => {
    apiFetch(villageApiUrl, {
      request: 'CancelVote',
      proposal_id: currentProposal.proposal_id
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      setProposalDataState(response.data.proposalData);
      setCurrentProposal(response.data.proposalData[currentProposalKey]);
      openModal({
        header: 'Confirmation',
        text: response.data.response_message,
        ContentComponent: null,
        onConfirm: null
      });
    });
  };
  const SubmitVote = vote => {
    apiFetch(villageApiUrl, {
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
    }
  }, [proposalDataState]);
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "kq_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "row first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement(KageDisplay, {
    username: kage.user_name,
    avatarLink: kage.avatar_link,
    seatTitle: kage.seat_title,
    villageName: villageName
  })), /*#__PURE__*/React.createElement("div", {
    className: "column second"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Proposals"), /*#__PURE__*/React.createElement(VillageProposals, {
    playerSeatState: playerSeatState,
    seatDataState: seatDataState,
    proposalDataState: proposalDataState,
    resourceDataState: resourceDataState,
    strategicDataState: strategicDataState,
    playerVillageData: playerVillageData,
    currentProposalKey: currentProposalKey,
    currentProposal: currentProposal,
    villageRegions: playerVillageData.regions,
    onEnactClick: () => EnactProposal(),
    onCancelClick: () => CancelProposal(),
    onPrevProposalClick: () => cycleProposal("decrement"),
    onNextProposalClick: () => cycleProposal("increment"),
    handleBoostVote: BoostVote,
    handleCancelVote: CancelVote,
    handleSubmitVote: SubmitVote
  }), /*#__PURE__*/React.createElement("div", {
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
      visibility: currentProposal && currentProposal.votes.find(vote => vote.user_id === elder.user_id) ? null : "hidden"
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "elder_vote"
  }, currentProposal && currentProposal.votes.find(vote => vote.user_id === elder.user_id && vote.vote === 1 && parseInt(vote.rep_adjustment) > 0) && /*#__PURE__*/React.createElement("img", {
    className: "vote_yes_image glow",
    src: "/images/v2/icons/yesvote.png"
  }), currentProposal && currentProposal.votes.find(vote => vote.user_id === elder.user_id && vote.vote === 0 && parseInt(vote.rep_adjustment) < 0) && /*#__PURE__*/React.createElement("img", {
    className: "vote_no_image glow",
    src: "/images/v2/icons/novote.png"
  }), currentProposal && currentProposal.votes.find(vote => vote.user_id === elder.user_id && vote.vote === 1 && parseInt(vote.rep_adjustment) === 0) && /*#__PURE__*/React.createElement("img", {
    className: "vote_yes_image",
    src: "/images/v2/icons/yesvote.png"
  }), currentProposal && currentProposal.votes.find(vote => vote.user_id === elder.user_id && vote.vote === 0 && parseInt(vote.rep_adjustment) === 0) && /*#__PURE__*/React.createElement("img", {
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
  }, "Village policy"), /*#__PURE__*/React.createElement(VillagePolicy, {
    policyDataState: policyDataState,
    playerSeatState: playerSeatState,
    displayPolicyID: displayPolicyID,
    handlePrevPolicyClick: () => cyclePolicy("decrement"),
    handleNextPolicyClick: () => cyclePolicy("increment"),
    handlePolicyChange: ChangePolicy,
    showPolicyControls: true
  }))), /*#__PURE__*/React.createElement("div", {
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
    strategicInfoData: playerVillageData
  }), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_navigation"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_navigation_diplomacy_buttons"
  }, playerVillageData.enemies.find(enemy => enemy === viewingTargetVillage.village.name) ? /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_wrapper war cancel",
    onClick: () => openModal({
      header: 'Confirmation',
      text: "Are you sure you want to offer peace with " + viewingTargetVillage.village.name + "?",
      ContentComponent: null,
      onConfirm: () => OfferPeace()
    })
  }, /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_inner"
  })) : /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_wrapper war",
    onClick: () => openModal({
      header: 'Confirmation',
      text: "Are you sure you declare war with " + viewingTargetVillage.village.name + "?",
      ContentComponent: null,
      onConfirm: () => DeclareWar()
    })
  }, /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/images/icons/war.png",
    className: "diplomacy_action_button_icon"
  }))), playerVillageData.allies.find(ally => ally === viewingTargetVillage.village.name) ? /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_wrapper alliance cancel",
    onClick: () => openModal({
      header: 'Confirmation',
      text: "Are you sure you want break an alliance with " + viewingTargetVillage.village.name + "?",
      ContentComponent: null,
      onConfirm: () => BreakAlliance()
    })
  }, /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_inner"
  })) : /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_wrapper alliance",
    onClick: () => openModal({
      header: 'Confirmation',
      text: "Are you sure you want to form an alliance with " + viewingTargetVillage.village.name + "?\nYou can be a member of only one Alliance at any given time.",
      ContentComponent: null,
      onConfirm: () => OfferAlliance()
    })
  }, /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/images/icons/ally.png",
    className: "diplomacy_action_button_icon"
  })))), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_navigation_village_buttons"
  }, villageName !== "Stone" && /*#__PURE__*/React.createElement("div", {
    className: viewingTargetVillage.village.village_id === 1 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setViewingTargetVillage(strategicDataState[0])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(1),
    className: "strategic_info_nav_button_icon"
  }))), villageName !== "Cloud" && /*#__PURE__*/React.createElement("div", {
    className: viewingTargetVillage.village.village_id === 2 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setViewingTargetVillage(strategicDataState[1])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(2),
    className: "strategic_info_nav_button_icon"
  }))), villageName !== "Leaf" && /*#__PURE__*/React.createElement("div", {
    className: viewingTargetVillage.village.village_id === 3 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setViewingTargetVillage(strategicDataState[2])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(3),
    className: "strategic_info_nav_button_icon"
  }))), villageName !== "Sand" && /*#__PURE__*/React.createElement("div", {
    className: viewingTargetVillage.village.village_id === 4 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setViewingTargetVillage(strategicDataState[3])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(4),
    className: "strategic_info_nav_button_icon"
  }))), villageName !== "Mist" && /*#__PURE__*/React.createElement("div", {
    className: viewingTargetVillage.village.village_id === 5 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper",
    onClick: () => setViewingTargetVillage(strategicDataState[4])
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_nav_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: getVillageIcon(5),
    className: "strategic_info_nav_button_icon"
  })))), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_navigation_diplomacy_buttons"
  }, playerVillageData.allies.find(ally => ally === viewingTargetVillage.village.name) && /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_wrapper alliance",
    onClick: () => openModal({
      header: 'Offer trade',
      text: '',
      ContentComponent: TradeDisplay,
      componentProps: {
        viewOnly: false,
        offeringVillageResources: resourceDataState,
        offeringVillageRegions: playerVillageData.regions,
        offeredResources: offeredResources,
        offeredRegions: offeredRegions,
        targetVillageResources: null,
        targetVillageRegions: viewingTargetVillage.regions,
        requestedResources: requestedResources,
        requestedRegions: requestedRegions
      },
      onConfirm: () => OfferTrade()
    })
  }, /*#__PURE__*/React.createElement("div", {
    className: "diplomacy_action_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/icons/trade.png",
    className: "diplomacy_action_button_icon"
  }))))), /*#__PURE__*/React.createElement(StrategicInfoItem, {
    strategicInfoData: viewingTargetVillage
  }))))));
  function cyclePolicy(direction) {
    var newPolicyID;
    switch (direction) {
      case "increment":
        newPolicyID = Math.min(5, displayPolicyID + 1);
        setDisplayPolicyID(newPolicyID);
        break;
      case "decrement":
        newPolicyID = Math.max(1, displayPolicyID - 1);
        setDisplayPolicyID(newPolicyID);
        break;
    }
  }
  function cycleProposal(direction) {
    if (proposalDataState.length === 0) {
      return;
    }
    var newProposalKey;
    switch (direction) {
      case "increment":
        newProposalKey = Math.min(proposalDataState.length - 1, currentProposalKey + 1);
        setCurrentProposalKey(newProposalKey);
        setCurrentProposal(proposalDataState[newProposalKey]);
        break;
      case "decrement":
        newProposalKey = Math.max(0, currentProposalKey - 1);
        setCurrentProposalKey(newProposalKey);
        setCurrentProposal(proposalDataState[newProposalKey]);
        break;
    }
  }
}
function VillageProposals({
  playerSeatState,
  proposalDataState,
  seatDataState,
  resourceDataState,
  strategicDataState,
  playerVillageData,
  currentProposalKey,
  currentProposal,
  onEnactClick,
  onCancelClick,
  onPrevProposalClick,
  onNextProposalClick,
  handleBoostVote,
  handleCancelVote,
  handleSubmitVote
}) {
  let proposalRepAdjustment = 0;
  let canEnactProposal = false;
  if (currentProposal != null) {
    proposalRepAdjustment = currentProposal.votes.reduce((acc, vote) => acc + parseInt(vote.rep_adjustment), 0);
    canEnactProposal = currentProposal.enact_time_remaining !== null || currentProposal.votes.length === seatDataState.filter(seat => seat.seat_type === "elder" && seat.seat_id != null).length;
  }
  const {
    openModal
  } = useModal();
  return /*#__PURE__*/React.createElement("div", {
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
    onClick: onPrevProposalClick
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
    onClick: onNextProposalClick
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
  }, playerSeatState.seat_type === "kage" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "proposal_cancel_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: currentProposal ? "proposal_cancel_button" : "proposal_cancel_button disabled",
    onClick: () => openModal({
      header: 'Confirmation',
      text: "Are you sure you want to cancel this proposal?",
      ContentComponent: null,
      onConfirm: () => onCancelClick()
    })
  }, "cancel proposal")), currentProposal && (currentProposal.type === "offer_trade" || currentProposal.type === "accept_trade") && /*#__PURE__*/React.createElement("div", {
    className: "trade_view_button_wrapper alliance",
    onClick: () => openModal({
      header: 'View trade offer',
      text: '',
      ContentComponent: TradeDisplay,
      componentProps: {
        viewOnly: true,
        offeringVillageResources: resourceDataState,
        offeringVillageRegions: playerVillageData.regions,
        offeredResources: {
          current: currentProposal.trade_data.offered_resources
        },
        offeredRegions: {
          current: currentProposal.trade_data.offered_regions
        },
        targetVillageResources: null,
        targetVillageRegions: strategicDataState.find(item => item.village.village_id !== currentProposal.target_village_id),
        requestedResources: {
          current: currentProposal.trade_data.requested_resources
        },
        requestedRegions: {
          current: currentProposal.trade_data.requested_regions
        },
        proposalData: currentProposal.trade_data
      },
      onConfirm: null
    })
  }, /*#__PURE__*/React.createElement("div", {
    className: "trade_view_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/icons/trade.png",
    className: "trade_view_button_icon"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "proposal_enact_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: currentProposal && canEnactProposal ? "proposal_enact_button" : "proposal_enact_button disabled",
    onClick: () => openModal({
      header: 'Confirmation',
      text: 'Are you sure you want to enact this proposal?',
      ContentComponent: null,
      onConfirm: () => onEnactClick()
    })
  }, "enact proposal"), proposalRepAdjustment < 0 && /*#__PURE__*/React.createElement("div", {
    className: "rep_change negative"
  }, "REPUTATION LOSS: ", proposalRepAdjustment))), playerSeatState.seat_type === "elder" && /*#__PURE__*/React.createElement(React.Fragment, null, !currentProposal && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "proposal_yes_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_yes_button disabled"
  }, "vote in favor")), /*#__PURE__*/React.createElement("div", {
    className: "proposal_no_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_no_button disabled"
  }, "vote against"))), currentProposal && currentProposal.vote_time_remaining != null && !currentProposal.votes.find(vote => vote.user_id === playerID) && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "proposal_yes_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_yes_button",
    onClick: () => handleSubmitVote(1)
  }, "vote in favor")), currentProposal && (currentProposal.type === "offer_trade" || currentProposal.type === "accept_trade") && /*#__PURE__*/React.createElement("div", {
    className: "trade_view_button_wrapper alliance",
    onClick: () => openModal({
      header: 'View trade offer',
      text: '',
      ContentComponent: TradeDisplay,
      componentProps: {
        viewOnly: true,
        offeringVillageResources: resourceDataState,
        offeringVillageRegions: playerVillageData.regions,
        offeredResources: offeredResources,
        offeredRegions: offeredRegions,
        targetVillageResources: null,
        targetVillageRegions: strategicDisplayRight.regions,
        requestedResources: requestedResources,
        requestedRegions: requestedRegions,
        proposalData: currentProposal.trade_data
      },
      onConfirm: null
    })
  }, /*#__PURE__*/React.createElement("div", {
    className: "trade_view_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/icons/trade.png",
    className: "trade_view_button_icon"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "proposal_no_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_no_button",
    onClick: () => handleSubmitVote(0)
  }, "vote against"))), currentProposal && currentProposal.vote_time_remaining == null && !currentProposal.votes.find(vote => vote.user_id === playerID) && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "proposal_yes_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_yes_button disabled"
  }, "vote in favor")), currentProposal && (currentProposal.type === "offer_trade" || currentProposal.type === "accept_trade") && /*#__PURE__*/React.createElement("div", {
    className: "trade_view_button_wrapper alliance",
    onClick: () => openModal({
      header: 'View trade offer',
      text: '',
      ContentComponent: TradeDisplay,
      componentProps: {
        viewOnly: true,
        offeringVillageResources: resourceDataState,
        offeringVillageRegions: playerVillageData.regions,
        offeredResources: offeredResources,
        offeredRegions: offeredRegions,
        targetVillageResources: null,
        targetVillageRegions: strategicDisplayRight.regions,
        requestedResources: requestedResources,
        requestedRegions: requestedRegions,
        proposalData: currentProposal.trade_data
      },
      onConfirm: null
    })
  }, /*#__PURE__*/React.createElement("div", {
    className: "trade_view_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/icons/trade.png",
    className: "trade_view_button_icon"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "proposal_no_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_no_button disabled"
  }, "vote against"))), currentProposal && currentProposal.vote_time_remaining != null && currentProposal.votes.find(vote => vote.user_id === playerID) && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "proposal_cancel_vote_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_cancel_vote_button",
    onClick: () => openModal({
      header: 'Confirmation',
      text: null,
      ContentComponent: null,
      onConfirm: () => handleCancelVote()
    })
  }, "change vote")), currentProposal && (currentProposal.type === "offer_trade" || currentProposal.type === "accept_trade") && /*#__PURE__*/React.createElement("div", {
    className: "trade_view_button_wrapper alliance",
    onClick: () => openModal({
      header: 'View trade offer',
      text: '',
      ContentComponent: TradeDisplay,
      componentProps: {
        viewOnly: true,
        offeringVillageResources: resourceDataState,
        offeringVillageRegions: playerVillageData.regions,
        offeredResources: offeredResources,
        offeredRegions: offeredRegions,
        targetVillageResources: null,
        targetVillageRegions: strategicDisplayRight.regions,
        requestedResources: requestedResources,
        requestedRegions: requestedRegions,
        proposalData: currentProposal.trade_data
      },
      onConfirm: null
    })
  }, /*#__PURE__*/React.createElement("div", {
    className: "trade_view_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/icons/trade.png",
    className: "trade_view_button_icon"
  }))), currentProposal.votes.find(vote => vote.user_id === playerID).rep_adjustment === 0 && /*#__PURE__*/React.createElement("div", {
    className: "proposal_boost_vote_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_boost_vote_button",
    onClick: () => openModal({
      header: 'Confirmation',
      text: "When a vote Against is boosted:\n The Kage will lose 500 Reputation when the proposal is enacted.\n\nWhen a vote In Favor is boosted:\nTotal Reputation loss from Against votes will be reduced by 500.\n\nBoosting a vote will cost 500 Reputation when the proposal is passed.\n\nHowever, a boosted vote In Favor will only cost Reputation if there is a boosted vote Against. If there are more boosted votes In Favor than Against, the cost will be split between between votes In Favor.",
      ContentComponent: null,
      onConfirm: () => handleBoostVote()
    })
  }, "boost vote"))), currentProposal && currentProposal.vote_time_remaining == null && currentProposal.votes.find(vote => vote.user_id === playerID) && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "proposal_cancel_vote_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_cancel_vote_button disabled"
  }, "cancel vote")), currentProposal && (currentProposal.type === "offer_trade" || currentProposal.type === "accept_trade") && /*#__PURE__*/React.createElement("div", {
    className: "trade_view_button_wrapper alliance",
    onClick: () => openModal({
      header: 'View trade offer',
      text: '',
      ContentComponent: TradeDisplay,
      componentProps: {
        viewOnly: true,
        offeringVillageResources: resourceDataState,
        offeringVillageRegions: playerVillageData.regions,
        offeredResources: offeredResources,
        offeredRegions: offeredRegions,
        targetVillageResources: null,
        targetVillageRegions: strategicDisplayRight.regions,
        requestedResources: requestedResources,
        requestedRegions: requestedRegions,
        proposalData: currentProposal.trade_data
      },
      onConfirm: null
    })
  }, /*#__PURE__*/React.createElement("div", {
    className: "trade_view_button_inner"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/icons/trade.png",
    className: "trade_view_button_icon"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "proposal_boost_vote_button_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "proposal_boost_vote_button disabled"
  }, "boost vote"))))));
}