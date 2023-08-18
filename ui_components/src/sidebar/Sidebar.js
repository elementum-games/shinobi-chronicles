import { apiFetch } from "../utils/network.js";

// Initialize
function Sidebar({ links, navigationAPIData, userAPIData }) {
    // Hooks
    const [userMenu, setUserMenu] = React.useState(navigationAPIData.userMenu);
    const [activityMenu, setActivityMenu] = React.useState(navigationAPIData.activityMenu);
    const [villageMenu, setVillageMenu] = React.useState(navigationAPIData.villageMenu);
    const [staffMenu, setStaffMenu] = React.useState(navigationAPIData.staffMenu);
    const [playerData, setPlayerData] = React.useState(userAPIData.playerData);
    const [playerResources, setPlayerResources] = React.useState(userAPIData.playerResources);
    const [playerSettings, setPlayerSettings] = React.useState(userAPIData.playerSettings);
    const [regenTime, setRegenTime] = React.useState(userAPIData.playerResources.regen_time);
    const [regenOffset, setRegenOffset] = React.useState(calculateRegenOffset(userAPIData.playerResources.regen_time));
    const regenTimeVar = React.useRef(userAPIData.playerResources.regen_time);
    const queryParameters = new URLSearchParams(window.location.search);
    const pageID = React.useRef(queryParameters.get("id"));

    // API
    function getSidebarLinks() {
        apiFetch(links.navigation_api, {
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
                setStaffMenu(response.data.staffMenu);
            }
        })
    }
    function getPlayerData() {
        apiFetch(links.user_api, {
            request: 'getPlayerResources'
        }).then(response => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            else {
                setPlayerResources(response.data.playerResources);
                setRegenTime(response.data.playerResources.regen_time);
                setRegenOffset(calculateRegenOffset(response.data.playerResources.regen_time));
                regenTimeVar.current = response.data.playerResources.regen_time;
            }
        })
    }
    // Utility
    function handleRegen() {
        if (regenTimeVar.current % 10 == 0 || regenTimeVar < 0) {
            getPlayerData();
        }
        else {
            regenTimeVar.current = regenTimeVar.current - 1;
            setRegenTime(regenTime => regenTime - 1);
            setRegenOffset(calculateRegenOffset(regenTimeVar.current));
        }
    }

    function calculateRegenOffset(time) {
        var percent = ((time / 60) * 100).toFixed(0);
        var offset = 126 - (126 * percent) / 100;
        return offset;
    }

    const toggleNav = (e) => {
        const currentClass = e.target.parentElement.className;
        if (currentClass.includes('toggle')) {
            e.target.parentElement.className = currentClass.replace('toggle', '').trim();
        } else {
            e.target.parentElement.className += ' toggle';
        }
    };

    // Content
    function displaySection(section_data, title, toggleNav) {
        return (
            <div className={"sb_section_container " + title} onClick={toggleNav}>
                <div className={"sb_header_bar d-flex"}>
                    <div className={"sb_header_image_wrapper"}>
                        <img src="/images/v2/icons/menudecor.png" className="sb_header_image" />
                    </div>
                    <div className={"sb_header_text_wrapper ft-p ft-c2 ft-b ft-medium"}>
                        {title}
                    </div>
                    <div className="sb_header_line">
                        <svg width="100%" height="2">
                            <line x1="0%" y1="1" x2="95%" y2="1" stroke="#77694e" strokeWidth="1" />
                        </svg>
                    </div>
                </div>
                <div className="sb_link_container d-flex">
                    {(section_data) &&
                        section_data.map(function (link, i) {
                            return (
                                <a key={i} href={link.url} className={pageID.current == link.id ? "sb_link_wrapper selected t-center ft-small ft-s ft-c3" : "sb_link_wrapper t-center ft-small ft-s ft-c3"} draggable="false">
                                        <label className={"sb_label"}>{link.title}</label>
                                </a>
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

    // Misc
    function handleErrors(errors) {
        console.warn(errors);
        //setFeedback([errors, 'info']);
    }

    // Initialize
    React.useEffect(() => {

        const regenInterval = setInterval(() => {
            handleRegen();
        }, 1000);

        return () => clearInterval(regenInterval);
    }, []);

    // Display
    return (
        <div id="sidebar">
            <SBCharacterProfile
                playerData={playerData}
                playerResources={playerResources}
                playerSettings={playerSettings}
                regenTime={regenTime}
                regenOffset={regenOffset}
            />
            {displaySection(userMenu, "Player Menu", toggleNav)}
            {displaySection(activityMenu, "Action Menu", toggleNav)}
            {displaySection(villageMenu, "Village Menu", toggleNav)}
            {staffMenu.length ? displaySection(staffMenu, "Staff Menu", toggleNav) : null}
        </div>
    )
}

function SBCharacterProfile({playerData, playerResources, playerSettings, regenTime, regenOffset}) {
    const avatarStyles = { maxWidth: playerData.avatar_size, maxHeight: playerData.avatar_size };

    return (
        <>
            <div className="sb_avatar_container">
                <div className={"sb_avatar_wrapper " + playerSettings.avatar_style + " " + playerSettings.avatar_frame} style={avatarStyles}>
                    <img className={"sb_avatar_img " + playerSettings.avatar_style + " " + playerSettings.avatar_frame} style={avatarStyles} src={playerData.avatar_link} />
                </div>
            </div>
            <div className={"sb_resources d-in_block"}>
                <div className={"sb_name_container t-left d-flex"}>
                    <div className="sb_name_wrapper">
                        <div className={"ft-p ft-c1 ft-xlarge ft-b"}>{playerData.user_name}</div>
                        <div className={"ft-s ft-c1 ft-default"}>{playerData.rank_name} lvl {playerData.level}</div>
                    </div>
                    <div className="sb_regentimer_container">
                        <div id="sb_regentimer">
                            <svg height="30" width="30" viewBox="0 0 50 50">
                                <circle
                                    id="sb_regentimer_circle_rear"
                                    stroke="#181b2c"
                                    cx="24.5"
                                    cy="24"
                                    r="20"
                                    strokeWidth="4"
                                    strokeMiterlimit="0"
                                    fill="none"
                                    strokeDasharray="126"
                                />
                                <circle
                                    id="sb_regentimer_circle"
                                    stroke="#7C88C3"
                                    cx="24.5"
                                    cy="24"
                                    r="20"
                                    strokeWidth="4"
                                    strokeMiterlimit="0"
                                    fill="none"
                                    strokeDasharray="126"
                                    strokeDashoffset={regenOffset}
                                    transform="rotate(-90, 24.5, 24)"
                                />
                                <text
                                    id="sb_regentimer_text"
                                    className={"ft-s ft-b ft-large"}
                                    x="48.75%"
                                    y="50.5%"
                                    textAnchor="middle"
                                    dominantBaseline="middle"
                                >{regenTime}</text>
                            </svg>
                        </div>
                    </div>
                </div>

                <SBResourceBar
                    resourceType="health"
                    resourceAmount={playerResources.health}
                    resourceMaxAmount={playerResources.max_health}
                    regenRate={playerResources.health_regen}
                />
                <SBResourceBar
                    resourceType="chakra"
                    resourceAmount={playerResources.chakra}
                    resourceMaxAmount={playerResources.max_chakra}
                    regenRate={playerResources.pool_regen}
                />
                <SBResourceBar
                    resourceType="stamina"
                    resourceAmount={playerResources.stamina}
                    resourceMaxAmount={playerResources.max_stamina}
                    regenRate={playerResources.pool_regen}
                />
            </div>
        </>
    )
}

type SBResourceBarProps = {|
    +resourceType: "health",
    +resourceAmount: number,
    +resourceMaxAmount: number,
    +regenRate: number,
|};
function SBResourceBar({
    resourceType,
    resourceAmount,
    resourceMaxAmount,
    regenRate,
}: SBResourceBarProps) {
    const fillPercent = Math.max(Math.round((resourceAmount / resourceMaxAmount) * 100), 6);

    return (
        <div className="sb_resourceContainer">
            <div id={`sb_${resourceType}`} className="sb_resourceBarOuter">
                <img className="sb_resource_corner_left" src="/images/v2/decorations/barrightcorner.png" />
                <label className="sb_innerResourceBarLabel">
                    {resourceAmount} / {resourceMaxAmount}
                </label>
                <label className="sb_innerResourceBarLabel_hover">
                    {resourceAmount} / {resourceMaxAmount} (+{regenRate})
                </label>
                <div className={`sb_${resourceType} sb_fill`} style={{ width: fillPercent + "%" }}>
                    <svg className="sb_resource_highlight_wrapper" viewBox="0 0 50 50">
                        <polygon x="50" points="20,25 0,0 5,0 25,25 5,50 0,50" id={`sb_${resourceType}_highlight`} className="sb_resource_highlight" />
                    </svg>
                </div>
                <div className={`sb_${resourceType} sb_preview`}></div>
                <img className={"sb_resource_corner_right"} src="/images/v2/decorations/barrightcorner.png" />
            </div>
        </div>
    );
}

window.Sidebar = Sidebar;