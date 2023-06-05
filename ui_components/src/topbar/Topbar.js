import { apiFetch } from "../utils/network.js";

// Initialize
function Topbar({ linkData }) {
    // Hooks
    const [notification_data, setNotificationData] = React.useState(null);

    // API
    function getNotificationData() {
        apiFetch(linkData.notification_api, {
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
    function topbarNotificationOnClick(event) {
        window.location.href = event.currentTarget.getAttribute("href");
    }

    // Content
    function displayTopbar(notification_data) {
        return (
            <>
            <div className="topbar_left"></div>
            <div className={"topbar_right d-flex"}>
                <div className="topbar_inner_left"></div>
                <div className={"topbar_inner_center main_logo"}></div>
                <div className="topbar_inner_right">
                    <div className={"topbar_notifications_container d-flex"}>
                        {(notification_data) &&
                            notification_data.map(function (notification, i) {
                                return (
                                    <div onClick={topbarNotificationOnClick} href={notification.action_url} key={i} className="topbar_notification_wrapper">
                                        <svg className="topbar_notification_svg" width="35" height="35" viewBox="0 0 100 100">
                                            {notification.type == "Battle" &&
                                                <>
                                                    <polygon points="6,50 50,94 94,50 50,6" strokeWidth="8px" stroke="#5d5c4b" fill="#ae5576" />
                                                    <polygon points="6,50 50,94 94,50 50,6" strokeWidth="2px" stroke="#000000" fill="#ae5576" />
                                                    <image className="topbar_notification_icon" height="50" width="50" x="25.5%" y="27.5%" href="images/v2/icons/anbutracking.png" />
                                                </>
                                            }
                                            {notification.type == "Report" &&
                                                <>
                                                    <polygon points="6,50 50,94 94,50 50,6" strokeWidth="8px" stroke="#5d5c4b" fill="#ae5576" />
                                                    <polygon points="6,50 50,94 94,50 50,6" strokeWidth="2px" stroke="#000000" fill="#ae5576" />
                                                    <text x="40%" y="70%" className="topbar_notification_important">!</text>
                                                </>
                                            }
                                            {notification.type == "Training" &&
                                            <>
                                                <polygon points="6,50 50,94 94,50 50,6" strokeWidth="8px" stroke="#5d5c4b" fill="#52466a" />
                                                <polygon points="6,50 50,94 94,50 50,6" strokeWidth="2px" stroke="#000000" fill="#52466a" />
                                                <image className="topbar_notification_icon" height="50" width="50" x="25.5%" y="27.5%" href="images/v2/icons/timer.png" />
                                            </>
                                            }
                                            {notification.type == "Mission" &&
                                                <>
                                                    <polygon points="6,50 50,94 94,50 50,6" strokeWidth="8px" stroke="#5d5c4b" fill="#52466a" />
                                                    <polygon points="6,50 50,94 94,50 50,6" strokeWidth="2px" stroke="#000000" fill="#52466a" />
                                                    <text x="10%" y="80%" className="topbar_notification_mission">A</text>
                                                    <text x="35%" y="65%" className="topbar_notification_mission">A</text>
                                                    <text x="60%" y="50%" className="topbar_notification_mission">A</text>
                                                </>
                                            }
                                            {notification.type == "Special Mission" &&
                                                <>
                                                    <polygon points="6,50 50,94 94,50 50,6" strokeWidth="8px" stroke="#5d5c4b" fill="#52466a" />
                                                    <polygon points="6,50 50,94 94,50 50,6" strokeWidth="2px" stroke="#000000" fill="#52466a" />
                                                    <text x="24%" y="65%" className="topbar_notification_specialmission">sm</text>
                                                </>
                                            }
                                        </svg>
                                    </div>
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
        getNotificationData();
    }, []);

    // Display
    return (
        <div id="topbar" className="d-flex">
            {displayTopbar(notification_data)}
        </div>
    )
}

window.Topbar = Topbar;