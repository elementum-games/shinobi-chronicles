// @flow

import { apiFetch } from "../utils/network.js";
import { TopbarNotification } from "./TopbarNotification.js";

const notificationRefreshInterval = 5000;

// Initialize
function Topbar({ links, notificationAPIData, userAPIData}) {
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
        })
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
        })
    }

    function clearNotificationAlert(notification_id) {
        apiFetch(links.notification_api, {
            request: 'clearNotificationAlert',
            notification_id: notification_id
        }).then(response => {
            if (response.errors.length) {
                handleErrors(response.errors);
            }
        })
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
            Notification.requestPermission()
                .then(permission => {
                    if (permission === "granted") {
                        new Notification("Shinobi Chronicles", { body: message });
                    }
                })
                .catch(err => console.error(err));
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
    const leftNotificationTypes = [
        "training",
        "training_complete",
        "stat_transfer"
    ];

    const rightNotificationTypes = [
         "specialmission",
         "specialmission_complete",
         "specialmission_failed",
         "mission",
         "mission_team",
         "mission_clan",
         "rank",
         "system",
         "warning",
         "report",
         "battle",
         "challenge",
         "team",
         "marriage",
         "student",
         "inbox",
         "chat",
         "event"
    ];

    return (
        <div id="topbar" className="d-flex">
            <div className="topbar_inner_left">
                <div className={"topbar_notifications_container_left d-flex"}>
                    {notificationData
                        .filter(notification => leftNotificationTypes.includes(notification.type))
                        .map((notification, i) => (
                            <TopbarNotification
                                key={i}
                                notification={notification}
                                closeNotification={closeNotification}
                            />
                        ))
                    }
                </div>
            </div>
            <div className={"topbar_inner_center main_logo"}></div>
            <div className="topbar_inner_right">
                <div className={"topbar_notifications_container_right d-flex"}>
                    {notificationData
                        .filter(notification => rightNotificationTypes.includes(notification.type))
                        .map((notification, i) => (
                            <TopbarNotification
                                key={i}
                                notification={notification}
                                closeNotification={closeNotification}
                            />
                        ))
                    }
                </div>
            </div>
        </div>
    )
}


window.Topbar = Topbar;