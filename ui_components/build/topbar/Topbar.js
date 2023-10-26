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
  function closeNotification(notificationId, actionUrl) {
    apiFetch(links.notification_api, {
      request: 'closeNotification',
      notification_id: notificationId
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      if (actionUrl !== undefined) {
        window.location.href = actionUrl;
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
    // Check for Notification API support
    if ("Notification" in window) {
      // Request permission if needed, then show notification
      Notification.requestPermission().then(permission => {
        if (permission === "granted") {
          new Notification("Shinobi Chronicles", {
            body: message
          });
        }
      }).catch(err => console.error(err));
    } else {
      console.log("Browser does not support notifications.");
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
  const rightNotificationTypes = ["specialmission", "specialmission_complete", "specialmission_failed", "mission", "mission_team", "mission_clan", "rank", "system", "warning", "report", "battle", "challenge", "team", "marriage", "student", "inbox", "chat", "caravan", "raid_ally", "raid_enemy", "event", "seat_challenge", "lock_challenge", "proposal_created", "proposal_passed", "proposal_canceled", "proposal_expired", "policy_change", "diplomacy_declare_war", "diplomacy_form_alliance", "diplomacy_end_war", "diplomacy_end_alliance", "news", "challenge_pending", "challenge_accepted", "kage_change", "achievement"];
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