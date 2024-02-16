function SpecialMission({
  selfLink,
  missionEventDurationMs,
  specialMissionId,
  initialMissionData
}) {
  if (specialMissionId !== 0 && initialMissionData != null) {
    return /*#__PURE__*/React.createElement(ActiveSpecialMission, {
      missionId: specialMissionId,
      selfLink: selfLink,
      missionEventDurationMs: missionEventDurationMs,
      initialMissionData: initialMissionData
    });
  } else {
    return /*#__PURE__*/React.createElement(SpecialMissionSelect, {
      selfLink: selfLink
    });
  }
}

function SpecialMissionSelect({
  selfLink
}) {
  // TODO: Make this submit via API fetch so we don't need to reload
  return /*#__PURE__*/React.createElement("div", {
    className: "contentDiv"
  }, /*#__PURE__*/React.createElement("h2", {
    className: "contentDivHeader"
  }, "Special Missions"), /*#__PURE__*/React.createElement("p", {
    style: {
      width: "inherit",
      textAlign: "center"
    }
  }, "As nations vie for power it falls upon shinobi to undertake missions that see these goals realized. Occasionally tasks of great importance warrant special designation. These missions challenge even the strongest shinobi but come with great rewards."), /*#__PURE__*/React.createElement("ul", {
    style: {
      listStyleType: "none"
    }
  }, /*#__PURE__*/React.createElement("li", null, "Special missions take 2-5 minutes to complete."), /*#__PURE__*/React.createElement("li", null, "Your character will automatically scout enemy territory while completing battles."), /*#__PURE__*/React.createElement("li", null, "You can be attacked by other players while your character is moving around the map."), /*#__PURE__*/React.createElement("li", null, "Special missions reward money, experience and village reputation increasing with mission difficulty."), /*#__PURE__*/React.createElement("li", null, "Completing special missions gradually drains chakra or stamina in exchange for jutsu experience.")), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("a", {
    href: `${selfLink}&start=easy`
  }, /*#__PURE__*/React.createElement("button", null, "Start Easy Mission!")), /*#__PURE__*/React.createElement("a", {
    href: `${selfLink}&start=normal`
  }, /*#__PURE__*/React.createElement("button", null, "Start Normal Mission!")), /*#__PURE__*/React.createElement("a", {
    href: `${selfLink}&start=hard`
  }, /*#__PURE__*/React.createElement("button", null, "Start Hard Mission!")), /*#__PURE__*/React.createElement("a", {
    href: `${selfLink}&start=nightmare`
  }, /*#__PURE__*/React.createElement("button", null, "Start Nightmare Mission!")), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("br", null));
}

function ActiveSpecialMission({
  missionId,
  initialMissionData,
  selfLink,
  missionEventDurationMs
}) {
  const serverRefreshIntervalMs = missionEventDurationMs + 125;
  const [mission, setMission] = React.useState(initialMissionData);
  const [missionComplete, setMissionComplete] = React.useState(false);
  const workerRef = React.useRef(null);

  function startRefresh() {
    if (workerRef.current == null) {
      console.error("No worker active!");
    }

    console.log('starting refresh');

    workerRef.current.onmessage = function (message) {
      console.log('Component received message from worker ', message);
      handleDataReceived(message.data);
    };

    workerRef.current.postMessage({
      refreshActive: true,
      missionId: missionId,
      refreshIntervalMs: serverRefreshIntervalMs
    });
  }

  function stopRefresh() {
    if (workerRef.current == null) return;
    workerRef.current.postMessage({
      refreshActive: false
    });
  }

  function handleDataReceived(data) {
    if (data.systemMessage) {
      console.log(data.systemMessage);
    }

    if (data.mission == null) {
      console.log("Not on a special mission!");
      stopRefresh();
      return true;
    }

    if (data.mission.progress >= 100) {
      data.mission.progress = 100;
    }

    if (data.missionComplete) {
      setMissionComplete(true);
      stopRefresh();
    }

    setMission(data.mission);
  }

  React.useEffect(() => {
    workerRef.current = new Worker("ui_components/build/special_missions/specialMissionWorker.js");
    startRefresh();
    return () => {
      workerRef.current.terminate();
    };
  }, []);
  let missionStatus = 'In Progress';

  switch (mission.log[0].event) {
    case 'mission_reward':
      missionStatus = 'Success';
      break;

    case 'mission_failed':
      missionStatus = 'Failed';
      break;
  } // TODO: Make cancel submit via API fetch so we don't need to reload


  return /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_cancel_wrapper"
  }, /*#__PURE__*/React.createElement("span", {
    className: "spec_miss_page_warning"
  }, "Stay on this page to keep your mission progressing!"), /*#__PURE__*/React.createElement("a", {
    id: "spec_miss_cancel",
    href: `${selfLink}&cancelmission=true`
  }, "Cancel Mission")), /*#__PURE__*/React.createElement(SpecialMissionHeader, {
    missionComplete: missionComplete,
    missionStatus: missionStatus,
    mission: mission
  }), /*#__PURE__*/React.createElement(SpecialMissionLog, {
    logEntries: mission.log,
    missionStartTime: mission.start_time
  }));
}

function SpecialMissionHeader({
  missionStatus,
  missionComplete,
  mission
}) {
  let statusClass = '';

  if (missionStatus !== 'In Progress') {
    statusClass = `spec_miss_status_${missionStatus}`;
  }

  const playerHealthPercent = mission.player_health / mission.player_max_health * 100;
  return /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_header"
  }, /*#__PURE__*/React.createElement(SpecialMissionTimer, {
    missionStartTime: mission.start_time,
    missionComplete: missionComplete
  }), /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_character_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_status_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_status_title"
  }, "Status"), /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_status_text",
    className: statusClass
  }, missionStatus)), /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_health_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_health_icon"
  }), /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_health_bar_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_health_bar_out"
  }, /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_health_bar",
    style: {
      width: `${playerHealthPercent}%`
    }
  }))))), /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_progress_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_progress_title"
  }, "Progress"), /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_progress_div",
    className: mission.progress === 100 ? "spec_miss_status_Success" : ""
  }, /*#__PURE__*/React.createElement("span", {
    id: "spec_miss_progress"
  }, mission.progress), "/100"), /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_reward_wrapper"
  }, "\xA5", /*#__PURE__*/React.createElement("span", {
    id: "spec_miss_reward"
  }, mission.reward))));
}

function SpecialMissionTimer({
  missionStartTime,
  missionComplete
}) {
  const [timeElapsed, setTimeElapsed] = React.useState(timeDifference(missionStartTime));
  const refreshIntervalIdRef = React.useRef(null);
  React.useEffect(() => {
    refreshIntervalIdRef.current = setInterval(() => {
      setTimeElapsed(timeDifference(missionStartTime));
    }, 1000);
    return () => {
      clearInterval(refreshIntervalIdRef.current);
    };
  }, [missionStartTime]);
  React.useEffect(() => {
    if (missionComplete && refreshIntervalIdRef.current != null) {
      clearInterval(refreshIntervalIdRef.current);
    }
  }, [missionComplete]);
  return /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_timer_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_timer_title"
  }, "Duration"), /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_timer"
  }, timeElapsed));
}

function SpecialMissionLog({
  logEntries,
  missionStartTime
}) {
  return /*#__PURE__*/React.createElement("div", {
    id: "spec_miss_log_wrapper"
  }, logEntries.map((log, i) => /*#__PURE__*/React.createElement(LogEntry, {
    key: `log:${i}`,
    eventType: log.event,
    description: log.description,
    timestampMs: log.timestamp_ms,
    missionStartTime: missionStartTime
  })));
}

function LogEntry({
  eventType,
  description,
  timestampMs,
  missionStartTime
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "spec_miss_log_entry"
  }, /*#__PURE__*/React.createElement("div", {
    className: "spec_miss_log_entry_icon_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: `spec_miss_log_entry_icon spec_miss_event_${eventType}`
  })), /*#__PURE__*/React.createElement("div", {
    className: "spec_miss_log_entry_text",
    dangerouslySetInnerHTML: {
      __html: description.replace(/\[br]/g, "<br />")
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "spec_miss_log_entry_timestamp_wrapper"
  }, /*#__PURE__*/React.createElement("span", {
    id: "spec_miss_log_entry_timestamp"
  }, timeDifference(Math.floor(timestampMs / 1000), missionStartTime))));
}

function timeDifference(timestamp, target = false) {
  // get the current time in milliseconds, UTC
  let currentTime = Math.floor(Date.now() / 1000); // the newest timestamp to subtract

  let timeTarget = target ? timestamp : currentTime; // the older timestamp

  let timeMinus = target ? target : timestamp;
  let timeDifference = timeTarget - timeMinus;
  return timeRemaining(timeDifference, 'short', false, true);
}

window.SpecialMission = SpecialMission;