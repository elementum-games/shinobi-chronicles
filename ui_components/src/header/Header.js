import { apiFetch } from "../utils/network.js";

// Initialize
function Header({ links }) {
    // Hooks
    const [headerMenu, setHeaderMenu] = React.useState(null);
    const [serverTime, setServerTime] = React.useState(null);

    // API
    function getHeaderMenu() {
        apiFetch(links.navigation_api, {
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
    function displayHeader(headerData, serverTime) {
        return (
            <div className="header_bar">
                <div className="header_bar_inner">
                    <div className="header_link_container d-flex">
                        {(headerData) &&
                            headerData.map(function (link, i) {
                                return (
                                    <div key={i} className={"header_link_wrapper t-center"}>
                                        <a href={link.url} className={"header_label ft-default ft-s ft-c5"}>{link.title}</a>
                                    </div>
                                )
                            })
                        }
                        <div className={"header_time_label ft-default ft-s ft-c5"}>{serverTime}</div>
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
            {headerMenu && displayHeader(headerMenu, serverTime)}
        </>
    )
}

window.Header = Header;