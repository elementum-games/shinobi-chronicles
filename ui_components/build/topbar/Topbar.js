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
        response.data.userNotifications.forEach(notification => checkNotificationAlert(notification));
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
  function clearNotificationAlert(notification_id) {
    apiFetch(links.notification_api, {
      request: 'clearNotificationAlert',
      notification_id: notification_id
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
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
  function checkNotificationAlert(notification) {
    if (notification.alert) {
      console.log("here");
      createNotification(notification.message);
      clearNotificationAlert(notification.notification_id);
    }
  }
  function createNotification(message) {
    if (!window.Notification) {
      console.log('Browser does not support notifications.');
    } else {
      // check if permission is already granted
      if (Notification.permission === 'granted') {
        // show notification here
        var notify = new Notification('Shinobi Chronicles', {
          body: message
        });
      } else {
        // request permission from user
        Notification.requestPermission().then(function (p) {
          if (p === 'granted') {
            // show notification here
            var notify = new Notification('Shinobi Chronicles', {
              body: message
            });
          } else {
            console.log('User blocked notifications.');
          }
        }).catch(function (err) {
          console.error(err);
        });
      }
    }
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
      }, notification.type == "training" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
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
        fill: "#ff4141"
      })))), notification.type == "specialmission" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        key: i,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
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
      })))), notification.type == "mission" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        key: i,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
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
      }, notification.mission_rank.charAt(0))))), notification.type == "rank" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        key: i,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
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
      })))), notification.type == "system" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
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
      }, "!")))), notification.type == "warning" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
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
      }, "!")))), notification.type == "report" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
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
      }, "!")))), notification.type == "battle" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
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
      })))), notification.type == "challenge" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
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
      }, "!")))), notification.type == "team" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
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
      }, "!")))), notification.type == "marriage" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
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
      }, "!")))), notification.type == "student" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
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
      }, "!")))), notification.type == "inbox" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
        href: notification.action_url,
        className: notification.duration > 0 ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper",
        "data-content": notification.message,
        "data-time": calculateTimeRemaining(notification.created, notification.duration)
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
      }, "!")))));
    })))));
  }

  // Misc
  function handleErrors(errors) {
    console.warn(errors);
    //setFeedback([errors, 'info']);
  }

  // Initialize
  React.useEffect(() => {
    const notificationInterval = setInterval(() => {
      getNotificationData();
    }, 10000);
    notificationAPIData.userNotifications.forEach(notification => checkNotificationAlert(notification));
    return () => clearInterval(notificationInterval);
  }, []);

  // Display
  return /*#__PURE__*/React.createElement("div", {
    id: "topbar",
    className: "d-flex"
  }, displayTopbar(notificationData));
}
window.Topbar = Topbar;