// @flow
import { apiFetch } from "../utils/network.js";

type Props = {|
    +links: {|
        +navigation_api: string,
    |},
    +navigationAPIData: {
        +headerMenu: $ReadOnlyArray<{|
            +url: string,
            +title: string,
        |}>
    }
|};
function Header({ links, navigationAPIData }: Props) {
    // Hooks
    const [headerMenuLinks, setHeaderMenuLinks] = React.useState(navigationAPIData.headerMenu);
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

            setHeaderMenuLinks(response.data.headerMenu);
        })
    }
    // Utility
    function getCurrentTime() {
        const currentDate = new Date();
        const options = {
            timeZone: 'America/New_York',
            weekday: 'long',
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        };
        const formattedDate = currentDate.toLocaleDateString('en-US', options);
        const formattedTime = currentDate.toLocaleTimeString('en-US', { timeZone: 'America/New_York', hour12: true });
        setServerTime(formattedDate + ' - ' + formattedTime);
    }

    // Misc
    function handleErrors(errors) {
        console.warn(errors);
        //setFeedback([errors, 'info']);
    }

    // Initialize
    React.useEffect(() => {
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
            {headerMenuLinks &&
                <div className="header_bar">
                    <div className="header_link_container d-flex">
                        {headerMenuLinks && headerMenuLinks.map(function (link, i) {
                                return (
                                    <div key={i} className={"header_link_wrapper t-center"}>
                                        <a href={link.url} className={"header_label ft-default ft-s ft-c5"}>{link.title}</a>
                                    </div>
                                )
                            })
                        }
                    <div className="header_time_label ft-default ft-s ft-c5">{serverTime}</div>
                    <div className={"header_logout_wrapper t-center"}>
                        <a href={links.logout_link} className={"header_logout_label ft-default ft-s"}>LOGOUT</a>
                    </div>
                    </div>
                </div>
            }
        </>
    )
}

window.Header = Header;