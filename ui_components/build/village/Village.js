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
  clanData
}) {
  const [seatDataState, setSeatDataState] = React.useState(seatData);
  const [resourceDataState, setResourceDataState] = React.useState(resourceData);
  const [playerSeatState, setPlayerSeatState] = React.useState(playerSeat);
  const [modalState, setModalState] = React.useState("closed");
  const [resourceDaysToShow, setResourceDaysToShow] = React.useState(1);
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
    apiFetch(villageAPI, {
      request: 'LoadResourceData',
      days: resourceDaysToShow
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      switch (resourceDaysToShow) {
        case 1:
          setResourceDaysToShow(7);
          break;
        case 7:
          setResourceDaysToShow(30);
          break;
        case 30:
          setResourceDaysToShow(1);
          break;
      }
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
  const totalPopulation = populationData.reduce((acc, rank) => acc + rank.count, 0);
  const kage = seatDataState.find(seat => seat.seat_type === 'kage');
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "navigation_row"
  }, /*#__PURE__*/React.createElement("div", {
    className: "nav_button"
  }, "village hq"), /*#__PURE__*/React.createElement("div", {
    className: "nav_button disabled"
  }, "world info"), /*#__PURE__*/React.createElement("div", {
    className: "nav_button disabled"
  }, "war table"), /*#__PURE__*/React.createElement("div", {
    className: "nav_button disabled"
  }, "members & teams"), /*#__PURE__*/React.createElement("div", {
    className: "nav_button disabled"
  }, "kage's quarters")), modalState !== "closed" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "hq_modal_backdrop"
  }), /*#__PURE__*/React.createElement("div", {
    className: "hq_modal"
  }, /*#__PURE__*/React.createElement("div", {
    className: "hq_modal_header"
  }, "Confirmation"), /*#__PURE__*/React.createElement("div", {
    className: "hq_modal_text"
  }, modalText.current), modalState == "confirm_resign" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "confirm_resign_button",
    onClick: () => Resign()
  }, "Confirm"), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => setModalState("closed")
  }, "Cancel")), modalState == "response_message" && /*#__PURE__*/React.createElement("div", {
    className: "modal_close_button",
    onClick: () => setModalState("closed")
  }, "Close"))), /*#__PURE__*/React.createElement("div", {
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
  }, kage.seat_title + " of " + villageName + " village"), kage.seat_id && kage.seat_id == playerSeatState.seat_id && /*#__PURE__*/React.createElement("div", {
    className: "kage_resign_button",
    onClick: () => Resign()
  }, "resign"), !kage.seat_id && /*#__PURE__*/React.createElement("div", {
    className: "kage_claim_button disabled"
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
  }, pointsData.points)), /*#__PURE__*/React.createElement("div", {
    className: "points_item"
  }, /*#__PURE__*/React.createElement("div", {
    className: "points_label"
  }, "monthly"), /*#__PURE__*/React.createElement("div", {
    className: "points_total"
  }, pointsData.points)))))), /*#__PURE__*/React.createElement("div", {
    className: "row second"
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
  }, diplomacyData.map((village, index) => /*#__PURE__*/React.createElement("div", {
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
}
window.Village = Village;