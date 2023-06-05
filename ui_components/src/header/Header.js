import { apiFetch } from "../utils/network.js";

// Initialize
function Header({ linkData }) {
    // Hooks
    const [header_menu, setHeaderMenu] = React.useState(null);
    const [server_time, setServerTime] = React.useState(null);

    // API
    function getHeaderMenu() {
        apiFetch(linkData.navigation_api, {
            request: 'getHeaderMenu'
        }).then(response => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            else {
                setHeaderMenu(response.data.headerMenu);
            }
        })
    }
    // Utility
    function getCurrentTime() {
        var currentDate = new Date();
        var options = {
            weekday: 'long',
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        };
        var formattedDate = currentDate.toLocaleDateString('en-US', options);
        var formattedTime = currentDate.toLocaleTimeString('en-US', { hour12: true });
        setServerTime(formattedDate + ' - ' + formattedTime);
    }
    // Content
    function displayHeader(header_data, server_time) {
        return (
            <div className="header_bar">
                <div className="header_bar_inner">
                    <div className="header_link_container d-flex">
                        {(header_data) &&
                            header_data.map(function (link, i) {
                                return (
                                    <div key={i} className={"header_link_wrapper t-center"}>
                                        <a href={link.url} className={"header_label ft-default ft-s ft-c5"}>{link.title}</a>
                                    </div>
                                )
                            })
                        }
                        <div className={"header_time_label ft-default ft-s ft-c5"}>{server_time}</div>
                    </div>
                </div>
            </div>
        )
    }

    // Misc
    function handleErrors(errors) {
        console.warn(errors);
        //setFeedback([errors, 'info']);
    }

    // Initialize
    React.useEffect(() => {
        getHeaderMenu();
        getCurrentTime();

        const timeInterval = setInterval(() => {
            getCurrentTime();
        }, 1000);

        return () => clearInterval(timeInterval);

    }, []);

    // Display
    return (
        <>
            <div className="header_bar_left"></div> 
            {header_menu && displayHeader(header_menu, server_time)}
        </>
    )
}

window.Header = Header;