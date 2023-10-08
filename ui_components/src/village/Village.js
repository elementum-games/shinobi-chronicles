import { apiFetch } from "../utils/network.js";

function Village({
    playerSeat,
    villageName,
    villageAPI,
    policyData,
    populationData,
    seatData,
    pointsData,
    diplomacyData,
    resourceData,
    clanData,
}) {
    const [seatDataState, setSeatDataState] = React.useState(seatData);
    const [resourceDataState, setResourceDataState] = React.useState(resourceData);
    const [playerSeatState, setPlayerSeatState] = React.useState(playerSeat);
    const [modalState, setModalState] = React.useState("closed");
    const [resourceDaysToShow, setResourceDaysToShow] = React.useState(1);
    const modalText = React.useRef(null);

    const DisplayFromDays = (days) => {
        switch (days) {
            case 1:
                return 'daily';
                break;
            case 7:
                return 'weekly';
                break;
            case 30:
                return 'monthly';
                break;
            default:
                return days;
                break;
        }
    }
    const FetchNextIntervalTypeResources = () => {
        apiFetch(
            villageAPI,
            {
                request: 'LoadResourceData',
                days: resourceDaysToShow,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            switch (resourceDaysToShow) {
                case 1:
                    setResourceDaysToShow(7);
                    break;
                case 7:
                    setResourceDaysToShow(30);
                    break;
                case 30:
                    setResourceDaysToShow(1);
                    break;
            }
            setResourceDataState(response.data);
        });
    }
    const ClaimSeat = (seat_type) => {
        apiFetch(
            villageAPI,
            {
                request: 'ClaimSeat',
                seat_type: seat_type,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setSeatDataState(response.data.seatData);
            setPlayerSeatState(response.data.playerSeat);
            modalText.current = response.data.response_message;
            setModalState("response_message");
        });
    }
    const Resign = () => {
        if (modalState == "confirm_resign") {
            apiFetch(
                villageAPI,
                {
                    request: 'Resign',
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setSeatDataState(response.data.seatData);
                setPlayerSeatState(response.data.playerSeat);
                modalText.current = response.data.response_message;
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_resign");
            modalText.current = "Are you sure you wish to resign from your current position?";
        }
    }
    function handleErrors(errors) {
        console.warn(errors);
    }
    function getKageKanji(village_id) {
        switch (village_id) {
            case 'Stone': return '土影';
            case 'Cloud': return '雷影';
            case 'Leaf': return '火影';
            case 'Sand': return '風影';
            case 'Mist': return '水影';
        }
    }
    const totalPopulation = populationData.reduce((acc, rank) => acc + rank.count, 0);
    const kage = seatDataState.find(seat => seat.seat_type === 'kage');
    return (
        <>
            <div className="navigation_row">
                <div className="nav_button">village hq</div>
                <div className="nav_button disabled">world info</div>
                <div className="nav_button disabled">war table</div>
                <div className="nav_button disabled">members & teams</div>
                <div className="nav_button disabled">kage's quarters</div>
            </div>
            {modalState !== "closed" &&
                <>
                <div className="hq_modal_backdrop"></div>
                <div className="hq_modal">
                    <div className="hq_modal_header">Confirmation</div>
                    <div className="hq_modal_text">{modalText.current}</div>
                    {modalState == "confirm_resign" &&
                        <>
                        <div className="confirm_resign_button" onClick={() => Resign()}>Confirm</div>
                        <div className="modal_cancel_button" onClick={() => setModalState("closed")}>Cancel</div>
                        </>
                    }
                    {modalState == "response_message" &&
                        <div className="modal_close_button" onClick={() => setModalState("closed")}>Close</div>
                    }
                </div>
                </>
            }
            <div className="hq_container">
                <div className="row first">
                    <div className="column first">
                        <div className="clan_container">
                            <div className="header">Clans</div>
                            <div className="content box-primary">
                                {clanData
                                    .map((clan, index) => (
                                        <div key={clan.clan_id} className="clan_item">
                                            <div className="clan_item_header">{clan.name}</div>
                                        </div>
                                    ))}
                            </div>
                        </div>
                        <div className="population_container">
                            <div className="header">Population</div>
                            <div className="content box-primary">
                                {populationData
                                    .map((rank, index) => (
                                        <div key={rank.rank} className="population_item">
                                            <div className="population_item_header">{rank.rank}</div>
                                            <div className="population_item_count">{rank.count}</div>
                                        </div>
                                    ))}
                                <div className="population_item" style={{width: "100%"}}>
                                    <div className="population_item_header">total</div>
                                    <div className="population_item_count last">{totalPopulation}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="column second">
                        <div className="kage_container">
                            <div className="kage_header">
                                <div className="header">Kage</div>
                                <div className="kage_kanji">{getKageKanji(villageName)}</div>
                            </div>
                            
                            {kage.avatar_link &&
                                <div className="kage_avatar_wrapper">
                                    <img className="kage_avatar" src={kage.avatar_link} />
                                </div>
                            }
                            {!kage.avatar_link &&
                                <div className="kage_avatar_wrapper_empty">
                                    <div className="kage_avatar_fill"></div>
                                </div>
                            }
                            <div className="kage_nameplate_wrapper">
                                <div className="kage_nameplate_decoration nw"></div>
                                <div className="kage_nameplate_decoration ne"></div>
                                <div className="kage_nameplate_decoration se"></div>
                                <div className="kage_nameplate_decoration sw"></div>
                                <div className="kage_name">{kage.user_name ? kage.user_name : "---"}</div>
                                <div className="kage_title">{kage.seat_title + " of " + villageName + " village"}</div>
                                {kage.seat_id && kage.seat_id == playerSeatState.seat_id &&
                                    <div className="kage_resign_button" onClick={() => Resign()}>resign</div>
                                }
                                {!kage.seat_id &&
                                    <div className="kage_claim_button disabled">claim</div>
                                }
                                {(kage.seat_id && kage.seat_id != playerSeatState.seat_id) &&
                                    <div className="kage_challenge_button">challenge</div>
                                }
                            </div>
                        </div>
                    </div>
                    <div className="column third">
                        <div className="elders_container">
                            <div className="header">Elders</div>
                            <div className="elder_list">
                                {seatDataState
                                    .filter(elder => elder.seat_type === 'elder')
                                    .map((elder, index) => (
                                        <div key={elder.seat_key} className="elder_item">
                                            <div className="elder_avatar_wrapper">
                                                {elder.avatar_link && <img className="elder_avatar" src={elder.avatar_link} />}
                                                {!elder.avatar_link && <div className="elder_avatar_fill"></div>}
                                            </div>
                                            <div className="elder_name">{elder.user_name ? elder.user_name : "---"}</div>
                                            {(elder.seat_id && elder.seat_id == playerSeatState.seat_id) &&
                                                <div className="elder_resign_button" onClick={() => Resign()}>resign</div>
                                            }
                                            {!elder.seat_id &&
                                                <div className={playerSeatState.seat_id ? "elder_claim_button disabled" : "elder_claim_button"} onClick={playerSeatState.seat_id ? null : () => ClaimSeat("elder")}>claim</div>
                                            }
                                            {(elder.seat_id && elder.seat_id != playerSeatState.seat_id) &&
                                                <div className="elder_challenge_button">challenge</div>
                                            }
                                        </div>
                                    ))}
                            </div>
                        </div>
                        <div className="points_container">
                            <div className="header">Village points</div>
                            <div className="content box-primary">
                                <div className="points_item">
                                    <div className="points_label">total</div>
                                    <div className="points_total">{pointsData.points}</div>
                                </div>
                                <div className="points_item">
                                    <div className="points_label">monthly</div>
                                    <div className="points_total">{pointsData.points}</div>
                                </div> 
                            </div>
                        </div>
                    </div>
                </div>
                <div className="row second">
                    <div className="column first">
                        <div className="diplomatic_status_container">
                            <div className="header">Diplomatic status</div>
                            <div className="diplomacy_header_row">
                                <div></div>
                                <div>points</div>
                                <div>members</div>
                                <div className="last"></div>
                            </div>
                            <div className="content">
                                {diplomacyData
                                    .map((village, index) => (
                                        <div key={village.village_name} className="diplomacy_item">
                                            <div className="diplomacy_item_name">
                                                <img className="diplomacy_village_icon" src={getVillageIcon(village.village_id)} />
                                                <span>{village.village_name}</span>
                                            </div>
                                            <div className="diplomacy_item_points">{village.village_points}</div>
                                            <div className="diplomacy_item_villagers">{village.villager_count}</div>
                                            <div className={"diplomacy_item_relation " + village.relation_type}>{village.relation_name}</div>
                                        </div>
                                    ))}
                            </div>
                        </div>
                    </div>
                    <div className="column second">
                        <div className="resources_container">
                            <div className="header">Resources overview</div>
                            <div className="content box-primary">
                                <div className="resources_inner_header">
                                    <div className="first"><a onClick={() => FetchNextIntervalTypeResources()}>{DisplayFromDays(resourceDaysToShow)}</a></div>
                                    <div className="second">current</div>
                                    <div>produced</div>
                                    <div>claimed</div>
                                    <div>lost</div>
                                    <div>spent</div>
                                </div>
                                {resourceDataState
                                    .map((resource, index) => (
                                        <div key={resource.resource_id} className="resource_item">
                                            <div className="resource_name">{resource.resource_name}</div>
                                            <div className="resource_count">{resource.count}</div>
                                            <div className="resource_produced">{resource.produced}</div>
                                            <div className="resource_claimed">{resource.claimed}</div>
                                            <div className="resource_lost">{resource.lost}</div>
                                            <div className="resource_spent">{resource.spent}</div>
                                        </div>
                                    ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
    function getVillageIcon(village_id) {
        switch (village_id) {
            case 1:
                return '/images/village_icons/stone.png';
            case 2:
                return '/images/village_icons/cloud.png';
            case 3:
                return '/images/village_icons/leaf.png';
            case 4:
                return '/images/village_icons/sand.png';
            case 5:
                return '/images/village_icons/mist.png';
            default:
                return null;
        }
    }
}

window.Village = Village;