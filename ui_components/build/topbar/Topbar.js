import { apiFetch } from "../utils/network.js";

// Initialize
function Topbar({
  links,
  notificationAPIData
}) {
  // Hooks
  const [notificationData, setNotificationData] = React.useState(notificationAPIData.userNotifications);

  // API
  function getNotificationData() {
    apiFetch(links.notification_api, {
      request: 'getUserNotifications'
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      } else {
        setNotificationData(response.data.userNotifications);
      }
    });
  }
  function closeNotification(event) {
    apiFetch(links.notification_api, {
      request: 'closeNotification',
      notification_id: event.currentTarget.getAttribute("data-id")
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      } else {
        getNotificationData();
      }
    });
  }

  // Utility
  function calculateTimeRemaining(created, duration) {
    var currentTimeTicks = new Date().getTime();
    return formatTimeRemaining(created + duration - currentTimeTicks / 1000);
  }
  function formatTimeRemaining(seconds) {
    var hours = Math.floor(seconds / 3600);
    var minutes = Math.floor(seconds % 3600 / 60);
    var seconds = Math.floor(seconds % 60);
    var formattedHours = hours.toString().padStart(2, '0');
    var formattedMinutes = minutes.toString().padStart(2, '0');
    var formattedSeconds = seconds.toString().padStart(2, '0');
    return formattedHours > 0 ? formattedHours + ':' + formattedMinutes + ':' + formattedSeconds : formattedMinutes + ':' + formattedSeconds;
  }

  // Content
  function displayTopbar(notificationData) {
    return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
      className: "topbar_left"
    }), /*#__PURE__*/React.createElement("div", {
      className: "topbar_right d-flex"
    }, /*#__PURE__*/React.createElement("div", {
      className: "topbar_inner_left"
    }), /*#__PURE__*/React.createElement("div", {
      className: "topbar_inner_center main_logo"
    }), /*#__PURE__*/React.createElement("div", {
      className: "topbar_inner_right"
    }, /*#__PURE__*/React.createElement("div", {
      className: "topbar_notifications_container d-flex"
    }, notificationData && notificationData.map(function (notification, i) {
      return /*#__PURE__*/React.createElement("div", {
        key: i
      }, notification.type == "battle" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
      }, /*#__PURE__*/React.createElement("svg", {
        className: "topbar_notification_svg",
        width: "35",
        height: "35",
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
      }), /*#__PURE__*/React.createElement("image", {
        className: "topbar_notification_icon",
        height: "50",
        width: "50",
        x: "25.5%",
        y: "27.5%",
        href: "images/map/icons/attack-off.png"
      })))), notification.type == "report" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
      }, /*#__PURE__*/React.createElement("svg", {
        className: "topbar_notification_svg",
        width: "35",
        height: "35",
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
      }, "!")))), notification.type == "training" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
      }, /*#__PURE__*/React.createElement("svg", {
        className: "topbar_notification_svg",
        width: "35",
        height: "35",
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
      })))), notification.type == "training_complete" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("label", {
        onClick: closeNotification,
        "data-id": notification.notification_id,
        className: "topbar_close_notification"
      }, "X"), /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
      }, /*#__PURE__*/React.createElement("svg", {
        className: "topbar_notification_svg",
        width: "35",
        height: "35",
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
        fill: "#ff4141"
      })))), notification.type == "mission" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        key: i,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
      }, /*#__PURE__*/React.createElement("svg", {
        className: "topbar_notification_svg",
        width: "35",
        height: "35",
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
      }, notification.attributes.mission_rank), /*#__PURE__*/React.createElement("text", {
        x: "35%",
        y: "65%",
        className: "topbar_notification_mission"
      }, notification.attributes.mission_rank), /*#__PURE__*/React.createElement("text", {
        x: "60%",
        y: "50%",
        className: "topbar_notification_mission"
      }, notification.attributes.mission_rank)))), notification.type == "specialmission" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        key: i,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
      }, /*#__PURE__*/React.createElement("svg", {
        className: "topbar_notification_svg",
        width: "35",
        height: "35",
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
      }, "sm")))), notification.type == "specialmission_complete" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("label", {
        onClick: closeNotification,
        className: "topbar_close_notification",
        "data-id": notification.notification_id
      }, "X"), /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        key: i,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
      }, /*#__PURE__*/React.createElement("svg", {
        className: "topbar_notification_svg",
        width: "35",
        height: "35",
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
      })))), notification.type == "rank" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        key: i,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
      }, /*#__PURE__*/React.createElement("svg", {
        className: "topbar_notification_svg",
        width: "35",
        height: "35",
        viewBox: "0 0 100 100"
      }, /*#__PURE__*/React.createElement("polygon", {
        points: "6,50 50,94 94,50 50,6",
        strokeWidth: "8px",
        stroke: "#5d5c4b",
        fill: "#2e2c36"
      }), /*#__PURE__*/React.createElement("polygon", {
        points: "6,50 50,94 94,50 50,6",
        strokeWidth: "2px",
        stroke: "#000000",
        fill: "#2e2c36"
      }), /*#__PURE__*/React.createElement("image", {
        className: "topbar_notification_icon",
        height: "50",
        width: "50",
        x: "25.5%",
        y: "27.5%",
        href: "images/v2/icons/levelup.png"
      })))), notification.type == "level" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        key: i,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
      }, /*#__PURE__*/React.createElement("svg", {
        className: "topbar_notification_svg",
        width: "35",
        height: "35",
        viewBox: "0 0 100 100"
      }, /*#__PURE__*/React.createElement("polygon", {
        points: "6,50 50,94 94,50 50,6",
        strokeWidth: "8px",
        stroke: "#5d5c4b",
        fill: "#2e2c36"
      }), /*#__PURE__*/React.createElement("polygon", {
        points: "6,50 50,94 94,50 50,6",
        strokeWidth: "2px",
        stroke: "#000000",
        fill: "#2e2c36"
      }), /*#__PURE__*/React.createElement("image", {
        className: "topbar_notification_icon",
        height: "50",
        width: "50",
        x: "25.5%",
        y: "27.5%",
        href: "images/v2/icons/levelup.png"
      })))));
    })))));
  }

  // Misc
  function handleErrors(errors) {
    console.warn(errors);
    //setFeedback([errors, 'info']);
  }

  // Initialize
  React.useEffect(() => {}, []);

  // Display
  return /*#__PURE__*/React.createElement("div", {
    id: "topbar",
    className: "d-flex"
  }, displayTopbar(notificationData));
}
window.Topbar = Topbar;