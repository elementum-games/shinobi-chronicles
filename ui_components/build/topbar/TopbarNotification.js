export function TopbarNotification({
  notification,
  closeNotification
}) {
  switch (notification.type) {
    case "training":
    case "training_complete":
      return /*#__PURE__*/React.createElement(TrainingNotification, {
        notification: notification,
        closeNotification: closeNotification
      });

    case "specialmission":
    case "specialmission_complete":
    case "specialmission_failed":
      return /*#__PURE__*/React.createElement(SpecialMissionNotification, {
        notification: notification,
        closeNotification: closeNotification
      });

    default:
      break;
  }

  const timeRemainingDisplay = formatTimeRemaining(calculateTimeRemaining(notification.created, notification.duration));
  return /*#__PURE__*/React.createElement(React.Fragment, null, notification.type === "mission" && /*#__PURE__*/React.createElement("a", {
    href: notification.action_url,
    className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
    "data-content": notification.message,
    "data-time": timeRemainingDisplay
  }, /*#__PURE__*/React.createElement("svg", {
    className: "topbar_notification_svg",
    width: "40",
    height: "40",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "8px",
    stroke: "#5d5c4b",
    fill: "#52466a"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "2px",
    stroke: "#000000",
    fill: "#52466a"
  }), /*#__PURE__*/React.createElement("text", {
    x: "10%",
    y: "80%",
    className: "topbar_notification_mission"
  }, notification.mission_rank.charAt(0)), /*#__PURE__*/React.createElement("text", {
    x: "35%",
    y: "65%",
    className: "topbar_notification_mission"
  }, notification.mission_rank.charAt(0)), /*#__PURE__*/React.createElement("text", {
    x: "60%",
    y: "50%",
    className: "topbar_notification_mission"
  }, notification.mission_rank.charAt(0)))), notification.type === "rank" && /*#__PURE__*/React.createElement("a", {
    href: notification.action_url,
    className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
    "data-content": notification.message,
    "data-time": timeRemainingDisplay
  }, /*#__PURE__*/React.createElement("svg", {
    className: "topbar_notification_svg",
    width: "40",
    height: "40",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "8px",
    stroke: "#5d5c4b",
    fill: "#B09A65"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "2px",
    stroke: "#000000",
    fill: "#B09A65"
  }), /*#__PURE__*/React.createElement("image", {
    className: "topbar_notification_icon",
    height: "40",
    width: "40",
    x: "30%",
    y: "27%",
    href: "images/v2/icons/levelup.png"
  }))), notification.type === "system" && /*#__PURE__*/React.createElement("a", {
    href: notification.action_url,
    className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
    "data-content": notification.message,
    "data-time": timeRemainingDisplay
  }, /*#__PURE__*/React.createElement("svg", {
    className: "topbar_notification_svg",
    width: "40",
    height: "40",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "8px",
    stroke: "#5d5c4b",
    fill: "#ae5576"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "2px",
    stroke: "#000000",
    fill: "#ae5576"
  }), /*#__PURE__*/React.createElement("text", {
    x: "40%",
    y: "70%",
    className: "topbar_notification_important"
  }, "!"))), notification.type === "warning" && /*#__PURE__*/React.createElement("a", {
    href: notification.action_url,
    className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
    "data-content": notification.message,
    "data-time": timeRemainingDisplay
  }, /*#__PURE__*/React.createElement("svg", {
    className: "topbar_notification_svg",
    width: "40",
    height: "40",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "8px",
    stroke: "#5d5c4b",
    fill: "#ae5576"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "2px",
    stroke: "#000000",
    fill: "#ae5576"
  }), /*#__PURE__*/React.createElement("text", {
    x: "40%",
    y: "70%",
    className: "topbar_notification_important"
  }, "!"))), notification.type === "report" && /*#__PURE__*/React.createElement("a", {
    href: notification.action_url,
    className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
    "data-content": notification.message,
    "data-time": timeRemainingDisplay
  }, /*#__PURE__*/React.createElement("svg", {
    className: "topbar_notification_svg",
    width: "40",
    height: "40",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "8px",
    stroke: "#5d5c4b",
    fill: "#B09A65"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "2px",
    stroke: "#000000",
    fill: "#B09A65"
  }), /*#__PURE__*/React.createElement("text", {
    x: "40%",
    y: "70%",
    className: "topbar_notification_important"
  }, "!"))), notification.type === "battle" && /*#__PURE__*/React.createElement("a", {
    href: notification.action_url,
    className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
    "data-content": notification.message,
    "data-time": timeRemainingDisplay
  }, /*#__PURE__*/React.createElement("svg", {
    className: "topbar_notification_svg",
    width: "40",
    height: "40",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "8px",
    stroke: "#4c1f1f",
    fill: "#eb4648"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "2px",
    stroke: "#000000",
    fill: "#eb4648"
  }), /*#__PURE__*/React.createElement("image", {
    className: "topbar_notification_icon",
    height: "85",
    width: "85",
    x: "5%",
    y: "12%",
    href: "images/v2/icons/combat.png"
  }))), notification.type === "challenge" && /*#__PURE__*/React.createElement("a", {
    href: notification.action_url,
    className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
    "data-content": notification.message,
    "data-time": timeRemainingDisplay
  }, /*#__PURE__*/React.createElement("svg", {
    className: "topbar_notification_svg",
    width: "40",
    height: "40",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "8px",
    stroke: "#5d5c4b",
    fill: "#B09A65"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "2px",
    stroke: "#000000",
    fill: "#B09A65"
  }), /*#__PURE__*/React.createElement("text", {
    x: "40%",
    y: "70%",
    className: "topbar_notification_important"
  }, "!"))), notification.type === "team" && /*#__PURE__*/React.createElement("a", {
    href: notification.action_url,
    className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
    "data-content": notification.message,
    "data-time": timeRemainingDisplay
  }, /*#__PURE__*/React.createElement("svg", {
    className: "topbar_notification_svg",
    width: "40",
    height: "40",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "8px",
    stroke: "#5d5c4b",
    fill: "#5964a6"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "2px",
    stroke: "#000000",
    fill: "#5964a6"
  }), /*#__PURE__*/React.createElement("text", {
    x: "40%",
    y: "70%",
    className: "topbar_notification_important"
  }, "!"))), notification.type === "marriage" && /*#__PURE__*/React.createElement("a", {
    href: notification.action_url,
    className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
    "data-content": notification.message,
    "data-time": timeRemainingDisplay
  }, /*#__PURE__*/React.createElement("svg", {
    className: "topbar_notification_svg",
    width: "40",
    height: "40",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "8px",
    stroke: "#5d5c4b",
    fill: "#5964a6"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "2px",
    stroke: "#000000",
    fill: "#5964a6"
  }), /*#__PURE__*/React.createElement("text", {
    x: "40%",
    y: "70%",
    className: "topbar_notification_important"
  }, "!"))), notification.type === "student" && /*#__PURE__*/React.createElement("a", {
    href: notification.action_url,
    className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
    "data-content": notification.message,
    "data-time": timeRemainingDisplay
  }, /*#__PURE__*/React.createElement("svg", {
    className: "topbar_notification_svg",
    width: "40",
    height: "40",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "8px",
    stroke: "#5d5c4b",
    fill: "#5964a6"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "2px",
    stroke: "#000000",
    fill: "#5964a6"
  }), /*#__PURE__*/React.createElement("text", {
    x: "40%",
    y: "70%",
    className: "topbar_notification_important"
  }, "!"))), notification.type === "inbox" && /*#__PURE__*/React.createElement("a", {
    href: notification.action_url,
    className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
    "data-content": notification.message,
    "data-time": timeRemainingDisplay
  }, /*#__PURE__*/React.createElement("svg", {
    className: "topbar_notification_svg",
    width: "40",
    height: "40",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "8px",
    stroke: "#5d5c4b",
    fill: "#5964a6"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "2px",
    stroke: "#000000",
    fill: "#5964a6"
  }), /*#__PURE__*/React.createElement("text", {
    x: "40%",
    y: "70%",
    className: "topbar_notification_important"
  }, "!"))), notification.type === "chat" && /*#__PURE__*/React.createElement("a", {
    href: notification.action_url,
    className: "topbar_notification_wrapper",
    "data-content": notification.message,
    "data-time": timeRemainingDisplay
  }, /*#__PURE__*/React.createElement("svg", {
    className: "topbar_notification_svg",
    width: "40",
    height: "40",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "8px",
    stroke: "#5d5c4b",
    fill: "#5964a6"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "2px",
    stroke: "#000000",
    fill: "#5964a6"
  }), /*#__PURE__*/React.createElement("image", {
    className: "topbar_notification_icon",
    height: "40",
    width: "40",
    x: "30%",
    y: "32%",
    href: "images/v2/icons/quote_hover.png"
  })), /*#__PURE__*/React.createElement("label", {
    className: "topbar_close_notification",
    onClick: e => {
      e.preventDefault();
      closeNotification(notification.notification_id);
    }
  }, "X")));
}

function SpecialMissionNotification({
  notification,
  closeNotification
}) {
  const timeRemainingDisplay = formatTimeRemaining(calculateTimeRemaining(notification.created, notification.duration));
  return /*#__PURE__*/React.createElement(React.Fragment, null, notification.type === "specialmission" && /*#__PURE__*/React.createElement("a", {
    href: notification.action_url,
    className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
    "data-content": notification.message,
    "data-time": timeRemainingDisplay
  }, /*#__PURE__*/React.createElement("svg", {
    className: "topbar_notification_svg",
    width: "40",
    height: "40",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "8px",
    stroke: "#5d5c4b",
    fill: "#52466a"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "2px",
    stroke: "#000000",
    fill: "#52466a"
  }), /*#__PURE__*/React.createElement("text", {
    x: "24%",
    y: "65%",
    className: "topbar_notification_specialmission"
  }, "sm"))), notification.type === "specialmission_complete" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
    href: notification.action_url,
    className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
    "data-content": notification.message,
    "data-time": timeRemainingDisplay
  }, /*#__PURE__*/React.createElement("svg", {
    className: "topbar_notification_svg",
    width: "40",
    height: "40",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "8px",
    stroke: "#5d5c4b",
    fill: "#5964a6"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "2px",
    stroke: "#000000",
    fill: "#5964a6"
  }), /*#__PURE__*/React.createElement("text", {
    x: "24%",
    y: "65%",
    className: "topbar_notification_specialmission"
  }, "sm"), /*#__PURE__*/React.createElement("circle", {
    cx: "75",
    cy: "25",
    r: "12",
    fill: "#31e1a1"
  })), /*#__PURE__*/React.createElement("label", {
    className: "topbar_close_notification",
    onClick: e => {
      e.preventDefault();
      closeNotification(notification.notification_id);
    }
  }, "X"))), notification.type === "specialmission_failed" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
    href: notification.action_url,
    className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
    "data-content": notification.message,
    "data-time": timeRemainingDisplay
  }, /*#__PURE__*/React.createElement("svg", {
    className: "topbar_notification_svg",
    width: "40",
    height: "40",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "8px",
    stroke: "#5d5c4b",
    fill: "#5964a6"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "2px",
    stroke: "#000000",
    fill: "#5964a6"
  }), /*#__PURE__*/React.createElement("text", {
    x: "24%",
    y: "65%",
    className: "topbar_notification_specialmission"
  }, "sm"), /*#__PURE__*/React.createElement("circle", {
    cx: "75",
    cy: "25",
    r: "12",
    fill: "#ff4141"
  })), /*#__PURE__*/React.createElement("label", {
    className: "topbar_close_notification",
    onClick: e => {
      e.preventDefault();
      closeNotification(notification.notification_id);
    }
  }, "X"))));
}

function TrainingNotification({
  notification,
  closeNotification
}) {
  const [timeRemaining, setTimeRemaining] = React.useState(calculateTimeRemaining(notification.created, notification.duration));
  React.useEffect(() => {
    const intervalId = setInterval(function () {
      setTimeRemaining(prevTime => prevTime - 1);
    }, 999);
    return () => clearInterval(intervalId);
  }, [timeRemaining]);
  return /*#__PURE__*/React.createElement(React.Fragment, null, notification.type === "training" && /*#__PURE__*/React.createElement("a", {
    href: notification.action_url,
    className: "topbar_notification_wrapper_training",
    "data-content": notification.message,
    "data-time": formatTimeRemaining(timeRemaining)
  }, /*#__PURE__*/React.createElement("svg", {
    className: "topbar_notification_svg",
    width: "40",
    height: "40",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "8px",
    stroke: "#5d5c4b",
    fill: "#52466a"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "2px",
    stroke: "#000000",
    fill: "#52466a"
  }), /*#__PURE__*/React.createElement("image", {
    className: "topbar_notification_icon",
    height: "50",
    width: "50",
    x: "25.5%",
    y: "27.5%",
    href: "images/v2/icons/timer.png"
  }))), notification.type === "training_complete" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
    href: notification.action_url,
    className: "topbar_notification_wrapper_training_complete",
    "data-content": notification.message,
    "data-time": formatTimeRemaining(timeRemaining)
  }, /*#__PURE__*/React.createElement("svg", {
    className: "topbar_notification_svg",
    width: "40",
    height: "40",
    viewBox: "0 0 100 100"
  }, /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "8px",
    stroke: "#5d5c4b",
    fill: "#5964a6"
  }), /*#__PURE__*/React.createElement("polygon", {
    points: "6,50 50,94 94,50 50,6",
    strokeWidth: "2px",
    stroke: "#000000",
    fill: "#5964a6"
  }), /*#__PURE__*/React.createElement("image", {
    className: "topbar_notification_icon",
    height: "50",
    width: "50",
    x: "25.5%",
    y: "27.5%",
    href: "images/v2/icons/timer.png"
  }), /*#__PURE__*/React.createElement("circle", {
    cx: "75",
    cy: "25",
    r: "12",
    fill: "#31e1a1"
  })), /*#__PURE__*/React.createElement("label", {
    className: "topbar_close_notification",
    onClick: e => {
      e.preventDefault();
      closeNotification(notification.notification_id);
    }
  }, "X"))));
} // Utilities


function calculateTimeRemaining(created, duration) {
  const currentTimeTicks = new Date().getTime();
  return created + duration - currentTimeTicks / 1000;
}

function formatTimeRemaining(seconds) {
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor(seconds % 3600 / 60);
  seconds = Math.floor(seconds % 60);
  const formattedHours = hours.toString().padStart(2, '0');
  const formattedMinutes = minutes.toString().padStart(2, '0');
  const formattedSeconds = seconds.toString().padStart(2, '0');
  return hours > 0 ? formattedHours + ':' + formattedMinutes + ':' + formattedSeconds : formattedMinutes + ':' + formattedSeconds;
}