import { apiFetch } from "../utils/network.js";
import { TopbarNotification } from "./TopbarNotification.js";
const notificationRefreshInterval = 5000;

// Initialize
function Topbar({
  links,
  notificationAPIData,
  userAPIData
}) {
  // Hooks
  const [notificationData, setNotificationData] = React.useState(notificationAPIData.userNotifications);
  const [enableAlerts, setEnableAlerts] = React.useState(userAPIData.playerSettings.enable_alerts);

  // API
  function getNotificationData() {
    apiFetch(links.notification_api, {
      request: 'getUserNotifications'
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      setNotificationData(response.data.userNotifications);
      response.data.userNotifications.forEach(notification => checkNotificationAlert(notification));
    });
  }
  function closeNotification(notificationId) {
    apiFetch(links.notification_api, {
      request: 'closeNotification',
      notification_id: notificationId
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      getNotificationData();
    });
  }
  function clearNotificationAlert(notification_id) {
    apiFetch(links.notification_api, {
      request: 'clearNotificationAlert',
      notification_id: notification_id
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
      }
    });
  }
  function checkNotificationAlert(notification) {
    if (notification.alert) {
      if (enableAlerts) {
        createNotification(notification.message);
      }
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

  // Misc
  function handleErrors(errors) {
    console.warn(errors);
    //setFeedback([errors, 'info']);
  }

  // Initialize
  React.useEffect(() => {
    const notificationIntervalId = setInterval(() => {
      getNotificationData();
    }, notificationRefreshInterval);
    notificationAPIData.userNotifications.forEach(notification => checkNotificationAlert(notification));
    return () => clearInterval(notificationIntervalId);
  }, []);

  // Display
  const leftNotificationTypes = ["training", "training_complete", "stat_transfer"];
  const rightNotificationTypes = ["specialmission", "specialmission_complete", "specialmission_failed", "mission", "mission_team", "mission_clan", "rank", "system", "warning", "report", "battle", "challenge", "team", "marriage", "student", "inbox", "chat", "event"];
  return /*#__PURE__*/React.createElement("div", {
    id: "topbar",
    className: "d-flex"
  }, /*#__PURE__*/React.createElement("div", {
    className: "topbar_inner_left"
  }, /*#__PURE__*/React.createElement("div", {
    className: "topbar_notifications_container_left d-flex"
  }, notificationData.filter(notification => leftNotificationTypes.includes(notification.type)).map((notification, i) => /*#__PURE__*/React.createElement(TopbarNotification, {
    key: i,
    notification: notification,
    closeNotification: closeNotification
  })))), /*#__PURE__*/React.createElement("div", {
    className: "topbar_inner_center main_logo"
  }), /*#__PURE__*/React.createElement("div", {
    className: "topbar_inner_right"
  }, /*#__PURE__*/React.createElement("div", {
    className: "topbar_notifications_container_right d-flex"
  }, notificationData.filter(notification => rightNotificationTypes.includes(notification.type)).map((notification, i) => /*#__PURE__*/React.createElement(TopbarNotification, {
    key: i,
    notification: notification,
    closeNotification: closeNotification
  })))));
}
window.Topbar = Topbar;