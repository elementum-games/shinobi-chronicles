import { apiFetch } from "../utils/network.js";
import { useModal } from '../utils/modalContext.js';
import KageDisplay from "./KageDisplay.js";
export function VillageHQ({
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
  kageRecords,
  handleErrors,
  getVillageIcon,
  getPolicyDisplayData
}) {
  const [resourceDaysToShow, setResourceDaysToShow] = React.useState(1);
  const [policyDisplay, setPolicyDisplay] = React.useState(getPolicyDisplayData(policyDataState.policy_id));
  const selectedTimesUTC = React.useRef([]);
  const selectedTimeUTC = React.useRef(null);
  const challengeTarget = React.useRef(null);
  const {
    openModal
  } = useModal();

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
      openModal({
        header: 'Confirmation',
        text: response.data.response_message,
        ContentComponent: null,
        onConfirm: null
      });
    });
  };

  const Resign = () => {
    apiFetch(villageAPI, {
      request: 'Resign'
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }

      setSeatDataState(response.data.seatData);
      setPlayerSeatState(response.data.playerSeat);
      openModal({
        header: 'Confirmation',
        text: response.data.response_message,
        ContentComponent: null,
        onConfirm: null
      });
    });
  };

  const [challengeSubmittedFlag, setChallengeSubmittedFlag] = React.useState(false);
  React.useEffect(() => {
    if (challengeSubmittedFlag) {
      ConfirmSubmitChallenge();
    }
  }, [challengeSubmittedFlag]);

  const Challenge = target_seat => {
    challengeTarget.current = target_seat;
    openModal({
      header: 'Submit Challenge',
      text: "Select times below that you are available to battle.",
      ContentComponent: TimeGrid,
      componentProps: {
        selectedTimesUTC: selectedTimesUTC,
        startHourUTC: luxon.DateTime.fromObject({
          hour: 0,
          zone: luxon.Settings.defaultZoneName
        }).toUTC().hour
      },
      onConfirm: () => setChallengeSubmittedFlag(true)
    });
  };

  const ConfirmSubmitChallenge = () => {
    setChallengeSubmittedFlag(false);

    if (selectedTimesUTC.length < 12) {
      openModal({
        header: 'Submit Challenge',
        text: "You must select at least 12 slots.",
        ContentComponent: TimeGrid,
        componentProps: {
          selectedTimesUTC: selectedTimesUTC,
          startHourUTC: luxon.DateTime.fromObject({
            hour: 0,
            zone: luxon.Settings.defaultZoneName
          }).toUTC().hour
        },
        onConfirm: () => setChallengeSubmittedFlag(true)
      });
    } else {
      apiFetch(villageAPI, {
        request: 'SubmitChallenge',
        seat_id: challengeTarget.current.seat_id,
        selected_times: selectedTimesUTC.current
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }

        setChallengeDataState(response.data.challengeData);
        openModal({
          header: 'Confirmation',
          text: response.data.response_message,
          ContentComponent: null,
          onConfirm: null
        });
      });
    }
  };

  const CancelChallenge = () => {
    apiFetch(villageAPI, {
      request: 'CancelChallenge'
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }

      setChallengeDataState(response.data.challengeData);
      openModal({
        header: 'Confirmation',
        text: response.data.response_message,
        ContentComponent: null,
        onConfirm: null
      });
    });
  };

  const [challengeAcceptedFlag, setChallengeAcceptedFlag] = React.useState(false);
  React.useEffect(() => {
    if (challengeAcceptedFlag) {
      ConfirmAcceptChallenge();
    }
  }, [challengeAcceptedFlag]);

  const AcceptChallenge = target_challenge => {
    challengeTarget.current = target_challenge;
    openModal({
      header: 'Accept Challenge',
      text: "Select a time slot below to accept the challenge.",
      ContentComponent: TimeGridResponse,
      componentProps: {
        availableTimesUTC: JSON.parse(challengeTarget.current.selected_times),
        selectedTimeUTC: selectedTimeUTC,
        startHourUTC: luxon.DateTime.utc().minute === 0 ? luxon.DateTime.utc().hour + 12 : (luxon.DateTime.utc().hour + 13) % 24
      },
      onConfirm: () => setChallengeAcceptedFlag(true)
    });
  };

  const ConfirmAcceptChallenge = () => {
    setChallengeAcceptedFlag(false);

    if (!selectedTimeUTC) {
      openModal({
        header: 'Accept Challenge',
        text: "Please verify that you have selected a time slot for the challenge.",
        ContentComponent: TimeGridResponse,
        componentProps: {
          availableTimesUTC: JSON.parse(challengeTarget.current.selected_times),
          selectedTimeUTC: selectedTimeUTC,
          startHourUTC: luxon.DateTime.utc().minute === 0 ? luxon.DateTime.utc().hour + 12 : (luxon.DateTime.utc().hour + 13) % 24
        },
        onConfirm: () => setChallengeAcceptedFlag(true)
      });
    } else {
      apiFetch(villageAPI, {
        request: 'AcceptChallenge',
        challenge_id: challengeTarget.current.request_id,
        time: selectedTimeUTC.current
      }).then(response => {
        if (response.errors.length) {
          handleErrors(response.errors);
          return;
        }

        setChallengeDataState(response.data.challengeData);
        openModal({
          header: 'Confirmation',
          text: response.data.response_message,
          ContentComponent: null,
          onConfirm: null
        });
      });
    }
  };

  const LockChallenge = target_challenge => {
    apiFetch(villageAPI, {
      request: 'LockChallenge',
      challenge_id: target_challenge.request_id
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }

      setChallengeDataState(response.data.challengeData);
      openModal({
        header: 'Confirmation',
        text: response.data.response_message,
        ContentComponent: null,
        onConfirm: null
      });
    });
  };

  const totalPopulation = populationData.reduce((acc, rank) => acc + rank.count, 0);
  const kage = seatDataState.find(seat => seat.seat_type === 'kage');
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(ChallengeContainer, {
    playerID: playerID,
    challengeDataState: challengeDataState,
    CancelChallenge: CancelChallenge,
    AcceptChallenge: AcceptChallenge,
    LockChallenge: LockChallenge,
    openModal: openModal
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
  }, /*#__PURE__*/React.createElement(KageDisplay, {
    username: kage.user_name,
    avatarLink: kage.avatar_link,
    villageName: villageName,
    seatTitle: kage.seat_title,
    isProvisional: kage.is_provisional,
    provisionalDaysLabel: kage.provisional_days_label,
    seatId: kage.seat_id,
    playerSeatId: playerSeatState.seat_id,
    onResign: () => openModal({
      header: 'Confirmation',
      text: 'Are you sure you wish to resign from your current position?',
      ContentComponent: null,
      onConfirm: () => Resign()
    }),
    onClaim: () => ClaimSeat("kage"),
    onChallenge: () => Challenge(kage)
  }))), /*#__PURE__*/React.createElement("div", {
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
    onClick: () => openModal({
      header: 'Confirmation',
      text: 'Are you sure you wish to resign from your current position?',
      ContentComponent: null,
      onConfirm: () => Resign()
    })
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
  }, pointsDataState.monthly_points)))))), /*#__PURE__*/React.createElement("div", {
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
  }, resource.spent))))))), /*#__PURE__*/React.createElement("div", {
    className: "row fourth"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "hq_navigation_row"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Kage Record"), /*#__PURE__*/React.createElement("div", {
    className: "kage_record_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "kage_record_header_section"
  }, /*#__PURE__*/React.createElement("div", {
    className: "kage_record_header"
  }, /*#__PURE__*/React.createElement("span", null, "Whispers of the past"), /*#__PURE__*/React.createElement("span", null, "Echoes of a leader's might"), /*#__PURE__*/React.createElement("span", null, "Legacy in stone."))), /*#__PURE__*/React.createElement("div", {
    className: "kage_record_main_section"
  }, kageRecords.map((kage, index) => /*#__PURE__*/React.createElement("div", {
    key: kage.user_id,
    className: "kage_record"
  }, /*#__PURE__*/React.createElement("div", {
    className: "kage_record_item_row_first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "kage_record_item_title"
  }, kage.seat_title), ": ", /*#__PURE__*/React.createElement("div", {
    className: "kage_record_item_name"
  }, /*#__PURE__*/React.createElement("a", {
    href: "/?id=6&user=" + kage.user_name
  }, kage.user_name))), /*#__PURE__*/React.createElement("div", {
    className: "kage_record_item_row_second"
  }, /*#__PURE__*/React.createElement("div", {
    className: "kage_record_item_start"
  }, kage.seat_start), " - ", /*#__PURE__*/React.createElement("div", {
    className: "kage_record_item_length"
  }, kage.time_held)))))))))));
}
export const ChallengeContainer = ({
  playerID,
  challengeDataState,
  CancelChallenge,
  AcceptChallenge,
  LockChallenge,
  openModal
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
    onClick: () => openModal({
      header: 'Confirmation',
      text: "Are you sure you want to lock in?\nYour actions will be restricted until the battle begins.",
      ContentComponent: null,
      onConfirm: () => LockChallenge(challenge)
    })
  }, "lock in", /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/icons/unlocked.png"
  })), challenge.start_time == null && /*#__PURE__*/React.createElement("div", {
    className: "challenge_button cancel",
    onClick: () => openModal({
      header: 'Confirmation',
      text: "Are you sure you wish to cancel your pending challenge request?",
      ContentComponent: null,
      onConfirm: () => CancelChallenge()
    })
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
    onClick: () => openModal({
      header: 'Confirmation',
      text: "Are you sure you want to lock in?\nYour actions will be restricted until the battle begins.",
      ContentComponent: null,
      onConfirm: () => LockChallenge(challenge)
    })
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
export const TimeGrid = ({
  selectedTimesUTC,
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
    selectedTimesUTC.current = convertSlotsToUTC(newSelectedTimes);
  }

  ;

  function convertSlotsToUTC(times) {
    return times.map(time => luxon.DateTime.fromFormat(time, 'HH:mm', {
      zone: 'local'
    }).setZone('utc').toFormat('HH:mm'));
  }

  ;
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "schedule_challenge_subtext_wrapper"
  }, /*#__PURE__*/React.createElement("span", {
    className: "schedule_challenge_subtext"
  }, "Time slots are displayed in your local time."), /*#__PURE__*/React.createElement("span", {
    className: "schedule_challenge_subtext"
  }, "The seat holder will have 24 hours to choose one of your selected times."), /*#__PURE__*/React.createElement("span", {
    className: "schedule_challenge_subtext"
  }, "Your battle will be scheduled a minimum of 12 hours from the time of their selection.")), /*#__PURE__*/React.createElement("div", {
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
export const TimeGridResponse = ({
  availableTimesUTC,
  selectedTimeUTC,
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
      selectedTimeUTC.current = null;
    } else if (availableTimesUTC.includes(formattedTimeUTC)) {
      setSelectedTime(formattedTimeLocal);
      selectedTimeUTC.current = formattedTimeUTC;
    }
  }

  ;
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "schedule_challenge_subtext_wrapper"
  }, /*#__PURE__*/React.createElement("span", {
    className: "schedule_challenge_subtext"
  }, "Time slots are displayed in your local time."), /*#__PURE__*/React.createElement("span", {
    className: "schedule_challenge_subtext"
  }, "The first slot below is set a minimum 12 hours from the current time.")), /*#__PURE__*/React.createElement("div", {
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