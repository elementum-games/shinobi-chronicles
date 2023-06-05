import { apiFetch } from "../utils/network.js";

// Initialize
function Topbar({ links, notificationAPIData }) {
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
            }
            else {
                setNotificationData(response.data.userNotifications);
            }
        })
    }

    // Utility
    function calculateTimeRemaining(created, duration) {
        var currentTimeTicks = new Date().getTime();
        return formatTimeRemaining((created + duration) - currentTimeTicks / 1000);
    }

    function formatTimeRemaining(seconds) {
        console.log(seconds);
        var hours = Math.floor(seconds / 3600);
        var minutes = Math.floor((seconds % 3600) / 60);
        var seconds = Math.floor(seconds % 60);

        var formattedHours = hours.toString().padStart(2, '0');
        var formattedMinutes = minutes.toString().padStart(2, '0');
        var formattedSeconds = seconds.toString().padStart(2, '0');

        return (formattedHours > 0) ? formattedHours + ':' + formattedMinutes + ':' + formattedSeconds : formattedMinutes + ':' + formattedSeconds;
    }

    // Content
    function displayTopbar(notificationData) {
        return (
            <>
            <div className="topbar_left"></div>
            <div className={"topbar_right d-flex"}>
                <div className="topbar_inner_left"></div>
                <div className={"topbar_inner_center main_logo"}></div>
                <div className="topbar_inner_right">
                    <div className={"topbar_notifications_container d-flex"}>
                        {(notificationData) &&
                            notificationData.map(function (notification, i) {
                                return (
                                    <a href={notification.action_url} key={i} className={(notification.duration > 0) ? "topbar_notification_wrapper has_duration" : "topbar_notification_wrapper"} data-content={notification.message} data-time={calculateTimeRemaining(notification.created, notification.duration)}>
                                        <svg className="topbar_notification_svg" width="35" height="35" viewBox="0 0 100 100">
                                            {notification.type == "battle" &&
                                                <>
                                                <polygon points="6,50 50,94 94,50 50,6" strokeWidth="8px" stroke="#5d5c4b" fill="#ae5576" />
                                                <polygon points="6,50 50,94 94,50 50,6" strokeWidth="2px" stroke="#000000" fill="#ae5576" />
                                                <image className="topbar_notification_icon" height="50" width="50" x="25.5%" y="27.5%" href="images/v2/icons/anbutracking.png" />
                                                </>
                                            }
                                            {notification.type == "report" &&
                                                <>
                                                <polygon points="6,50 50,94 94,50 50,6" strokeWidth="8px" stroke="#5d5c4b" fill="#ae5576" />
                                                <polygon points="6,50 50,94 94,50 50,6" strokeWidth="2px" stroke="#000000" fill="#ae5576" />
                                                <text x="40%" y="70%" className="topbar_notification_important">!</text>
                                                </>
                                            }
                                            {notification.type == "training" &&
                                                <>
                                                <polygon points="6,50 50,94 94,50 50,6" strokeWidth="8px" stroke="#5d5c4b" fill="#52466a" />
                                                <polygon points="6,50 50,94 94,50 50,6" strokeWidth="2px" stroke="#000000" fill="#52466a" />
                                                <image className="topbar_notification_icon" height="50" width="50" x="25.5%" y="27.5%" href="images/v2/icons/timer.png" />
                                                </>
                                            }
                                            {notification.type == "training_complete" &&
                                                <>
                                                <polygon points="6,50 50,94 94,50 50,6" strokeWidth="8px" stroke="#5d5c4b" fill="#5964a6" />
                                                <polygon points="6,50 50,94 94,50 50,6" strokeWidth="2px" stroke="#000000" fill="#5964a6" />
                                                <image className="topbar_notification_icon" height="50" width="50" x="25.5%" y="27.5%" href="images/v2/icons/timer.png" />
                                                </>
                                            }
                                            {notification.type == "mission" &&
                                                <>
                                                <polygon points="6,50 50,94 94,50 50,6" strokeWidth="8px" stroke="#5d5c4b" fill="#52466a" />
                                                <polygon points="6,50 50,94 94,50 50,6" strokeWidth="2px" stroke="#000000" fill="#52466a" />
                                                <text x="10%" y="80%" className="topbar_notification_mission">A</text>
                                                <text x="35%" y="65%" className="topbar_notification_mission">A</text>
                                                <text x="60%" y="50%" className="topbar_notification_mission">A</text>
                                                </>
                                            }
                                            {notification.type == "specialmission" &&
                                                <>
                                                <polygon points="6,50 50,94 94,50 50,6" strokeWidth="8px" stroke="#5d5c4b" fill="#52466a" />
                                                <polygon points="6,50 50,94 94,50 50,6" strokeWidth="2px" stroke="#000000" fill="#52466a" />
                                                <text x="24%" y="65%" className="topbar_notification_specialmission">sm</text>
                                                </>
                                            }
                                            {notification.type == "rank" &&
                                                <>
                                                <polygon points="6,50 50,94 94,50 50,6" strokeWidth="8px" stroke="#5d5c4b" fill="#2e2c36" />
                                                <polygon points="6,50 50,94 94,50 50,6" strokeWidth="2px" stroke="#000000" fill="#2e2c36" />
                                                <image className="topbar_notification_icon" height="50" width="50" x="25.5%" y="27.5%" href="images/v2/icons/levelup.png" />
                                                </>
                                            }
                                            {notification.type == "level" &&
                                                <>
                                                <polygon points="6,50 50,94 94,50 50,6" strokeWidth="8px" stroke="#5d5c4b" fill="#2e2c36" />
                                                <polygon points="6,50 50,94 94,50 50,6" strokeWidth="2px" stroke="#000000" fill="#2e2c36" />
                                                <image className="topbar_notification_icon" height="50" width="50" x="25.5%" y="27.5%" href="images/v2/icons/levelup.png" />
                                                </>
                                            }
                                        </svg>
                                    </a>
                                )
                            })
                        }
                    </div>
                </div>
            </div>
            </>
        )
    }

    // Misc
    function handleErrors(errors) {
        console.warn(errors);
        //setFeedback([errors, 'info']);
    }

    // Initialize
    React.useEffect(() => {

    }, []);

    // Display
    return (
        <div id="topbar" className="d-flex">
            {displayTopbar(notificationData)}
        </div>
    )
}

window.Topbar = Topbar;