import { apiFetch } from "../utils/network.js";

// Initialize
function Sidebar({ linkData }) {
    // Hooks
    const [user_menu, setUserMenu] = React.useState(null);
    const [activity_menu, setActivityMenu] = React.useState(null);
    const [village_menu, setVillageMenu] = React.useState(null);
    const queryParameters = new URLSearchParams(window.location.search);
    const pageID = React.useRef(queryParameters.get("id"));

    // API
    function getSidebarLinks() {
        apiFetch(linkData.navigation_api, {
            request: 'getNavigationLinks'
        }).then(response => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            else {
                setUserMenu(response.data.userMenu);
                setActivityMenu(response.data.activityMenu);
                setVillageMenu(response.data.villageMenu);
            }
        })
    }
    // Utility
    function sbLinkOnClick(event) {
        window.location.href = event.currentTarget.getAttribute("href");
    }

    // Content
    function displaySection(section_data, title) {
        return (
            <div className="sb_section_container">
                <div className={"sb_header_bar d-flex"}>
                    <div className={"sb_header_image_wrapper"}>
                        <img src="/images/v2/icons/menudecor.png" className="sb_header_image" />
                    </div>
                    <div className={"sb_header_text_wrapper ft-p ft-c2 ft-b ft-medium"}>
                        {title}
                    </div>
                </div>
                <div className="sb_link_container d-flex">
                    {(section_data) &&
                        section_data.map(function (link, i) {
                            return (
                                <div onClick={sbLinkOnClick} href={link.url} key={i} className={pageID.current == link.id ? "sb_link_wrapper selected t-center ft-small ft-s ft-c3" : "sb_link_wrapper t-center ft-small ft-s ft-c3"}>
                                    <label className={"sb_label"}>{link.title}</label>
                                </div>
                            )
                        })
                    }
                    {(section_data.length % 2 != 0) &&
                        <div className="sb_link_filler"></div>
                    }
                </div>
            </div>
        )
    }

    function displayAvatar(avatar_link) {
        return (
            <div className="sb_avatar_container">
                <div className="sb_avatar_wrapper">
                    <img className="sb_avatar_img" src={avatar_link}/>
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
        getSidebarLinks();
    }, []);

    // Display
    return (
        <div id="sidebar">
            {displayAvatar(linkData.avatar_link)}
            {user_menu && displaySection(user_menu, "Player Menu")}
            {activity_menu && displaySection(activity_menu, "Action Menu")}
            {village_menu && displaySection(village_menu, "Village Menu")}
        </div>
    )
}

window.Sidebar = Sidebar;