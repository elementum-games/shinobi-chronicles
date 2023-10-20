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
    proposalData,
    strategicData,
    challengeData,
}) {
    const [playerSeatState, setPlayerSeatState] = React.useState(playerSeat);
    const [policyDataState, setPolicyDataState] = React.useState(policyData);
    const [seatDataState, setSeatDataState] = React.useState(seatData);
    const [pointsDataState, setPointsDataState] = React.useState(pointsData);
    const [diplomacyDataState, setDiplomacyDataState] = React.useState(diplomacyData);
    const [resourceDataState, setResourceDataState] = React.useState(resourceData);
    const [proposalDataState, setProposalDataState] = React.useState(proposalData);
    const [strategicDataState, setStrategicDataState] = React.useState(strategicData);
    const [challengeDataState, setChallengeDataState] = React.useState(challengeData);
    const [villageTab, setVillageTab] = React.useState("villageHQ");

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
    function getPolicyDisplayData(policy_id) {
        let data = {
            banner: "",
            name: "",
            phrase: "",
            description: "",
            bonuses: [],
            penalties: [],
            glowClass: ""
        };

        switch (policy_id) {
            case 0:
                data.banner = "";
                data.name = "Inactive Policy";
                data.phrase = "";
                data.description = "";
                data.bonuses = [];
                data.penalties = [];
                data.glowClass = "";
                break;
            case 1:
                data.banner = "/images/v2/decorations/policy_banners/growthpolicy.png";
                data.name = "From the Ashes";
                data.phrase = "bonds forged, courage shared.";
                data.description = "In unity, find the strength to overcome.\nOne village, one heart, one fight.";
                data.bonuses = ["25% increased Caravan speed", "+100% resource production [home region]", "+5% training speed", "Free incoming village transfer"];
                data.penalties = ["-15 Materials/hour", "-25 Food/hour", "-10 Wealth/hour", "Cannot declare War"];
                data.glowClass = "growth_glow";
                break;
            case 2:
                data.banner = "/images/v2/decorations/policy_banners/espionagepolicy.png";
                data.name = "Eye of the Storm";
                data.phrase = "half truths, all lies.";
                data.description = "Become informants dealing in truths and lies.\nDeceive, divide and destroy.";
                data.bonuses = ["25% increased Infiltrate speed", "+1 Defense reduction from Infiltrate", "+1 Stealth", "+15 Loot Capacity"];
                data.penalties = ["-10 Materials/hour", "-10 Food/hour", "-30 Wealth/hour"];
                data.glowClass = "espionage_glow";
                break;
            case 3:
                data.banner = "/images/v2/decorations/policy_banners/defensepolicy.png";
                data.name = "Fortress of Solitude";
                data.phrase = "vigilant minds, enduring hearts.";
                data.description = "Show the might of will unyielding.\nPrepare, preserve, prevail.";
                data.bonuses = ["25% increased Reinforce speed", "+1 Defense gain from Reinforce", "+1 Scouting", "Increased Patrol strength"];
                data.penalties = ["-25 Materials/hour", "-15 Food/hour", "-10 Wealth/hour"];
                data.glowClass = "defense_glow";
                break;
            case 4:
                data.banner = "/images/v2/decorations/policy_banners/warpolicy.png";
                data.name = "Forged in Flames";
                data.phrase = "blades sharp, minds sharper.";
                data.description = "Lead your village on the path of a warmonger.\nFeel no fear, no hesitation, no doubt.";
                data.bonuses = ["25% increased Raid speed", "+1 Defense reduction from Raid", "+1 Village Point from PvP", "Faster Patrol respawn"];
                data.penalties = ["-15 Materials/hour", "-20 Food/hour", "-15 Wealth/hour", "Cannot form Alliances"];
                data.glowClass = "war_glow";
                break;
            case 5:
                data.banner = "/images/v2/decorations/policy_banners/prosperitypolicy.png";
                data.name = "The Gilded Hand";
                data.phrase = "";
                data.description = "";
                data.bonuses = [];
                data.penalties = [];
                data.glowClass = "prosperity_glow";
                break;
        }

        return data;
    }

    return (
        <>
            <div className="navigation_row">
                <div className="nav_button" onClick={() => setVillageTab("villageHQ")}>village hq</div>
                <div className="nav_button" onClick={() => setVillageTab("worldInfo")}>world info</div>
                <div className="nav_button disabled">war table</div>
                <div className="nav_button disabled">members & teams</div>
                <div className={playerSeatState.seat_id != null ? "nav_button" : "nav_button disabled"} onClick={() => setVillageTab("kageQuarters")}>kage's quarters</div>
            </div>
            {villageTab == "villageHQ" &&
                <VillageHQ
                playerSeatState={playerSeatState}
                setPlayerSeatState={setPlayerSeatState}
                villageName={villageName}
                villageAPI={villageAPI}
                policyDataState={policyDataState}
                populationData={populationData}
                seatDataState={seatDataState}
                setSeatDataState={setSeatDataState}
                pointsDataState={pointsDataState}
                diplomacyDataState={diplomacyDataState}
                resourceDataState={resourceDataState}
                setResourceDataState={setResourceDataState}
                challengeDataState={challengeDataState}
                setChallengeDataState={setChallengeDataState}
                clanData={clanData}
                handleErrors={handleErrors}
                getKageKanji={getKageKanji}
                getVillageIcon={getVillageIcon}
                getPolicyDisplayData={getPolicyDisplayData}
                TimeGrid={TimeGrid}
                TimeGridResponse={TimeGridResponse}
                />
            }
            {villageTab == "kageQuarters" &&
                <KageQuarters
                playerSeatState={playerSeatState}
                setPlayerSeatState={setPlayerSeatState}
                villageName={villageName}
                villageAPI={villageAPI}
                policyDataState={policyDataState}
                setPolicyDataState={setPolicyDataState}
                seatDataState={seatDataState}
                pointsDataState={pointsDataState}
                setPointsDataState={setPointsDataState}
                diplomacyDataState={diplomacyDataState}
                setDiplomacyDataState={setDiplomacyDataState}
                resourceDataState={resourceDataState}
                setResourceDataState={setResourceDataState}
                proposalDataState={proposalDataState}
                setProposalDataState={setProposalDataState}
                strategicDataState={strategicDataState}
                setStrategicDataState={setStrategicDataState}
                handleErrors={handleErrors}
                getKageKanji={getKageKanji}
                getVillageIcon={getVillageIcon}
                getPolicyDisplayData={getPolicyDisplayData}
                StrategicInfoItem={StrategicInfoItem}
                />
            }
            {villageTab == "worldInfo" &&
                <WorldInfo
                villageName={villageName}
                strategicDataState={strategicDataState}
                getVillageIcon={getVillageIcon}
                StrategicInfoItem={StrategicInfoItem}
                getPolicyDisplayData={getPolicyDisplayData}
                />
            }
        </>
    );
}

function VillageHQ({
    playerSeatState,
    setPlayerSeatState,
    villageName,
    villageAPI,
    policyDataState,
    populationData,
    seatDataState,
    setSeatDataState,
    pointsDataState,
    diplomacyDataState,
    resourceDataState,
    setResourceDataState,
    challengeDataState,
    setChallengeDataState,
    clanData,
    handleErrors,
    getKageKanji,
    getVillageIcon,
    getPolicyDisplayData
}) {
    const [modalState, setModalState] = React.useState("closed");
    const [resourceDaysToShow, setResourceDaysToShow] = React.useState(1);
    const [policyDisplay, setPolicyDisplay] = React.useState(getPolicyDisplayData(policyDataState.policy_id));
    const [selectedTimesUTC, setSelectedTimesUTC] = React.useState([]);
    const [selectedTimeUTC, setSelectedTimeUTC] = React.useState(null);
    const [modalHeader, setModalHeader] = React.useState(null);
    const [modalText, setModalText] = React.useState(null);
    const [challengeTarget, setChallengeTarget] = React.useState(null);
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
        var days;
        switch (resourceDaysToShow) {
            case 1:
                days = 7;
                break;
            case 7:
                days = 30;
                break;
            case 30:
                days = 1;
                break;
        }
        apiFetch(
            villageAPI,
            {
                request: 'LoadResourceData',
                days: days,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setResourceDaysToShow(days);
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
            setModalHeader("Confirmation");
            setModalText(response.data.response_message);
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
                setModalHeader("Confirmation");
                setModalText(response.data.response_message);
                setModalState("response_message");
            });
        }
        else {
            setModalHeader("Confirmation");
            setModalState("confirm_resign");
            setModalText("Are you sure you wish to resign from your current position?");
        }
    }
    const Challenge = (target_seat) => {
        setChallengeTarget(target_seat);
        setModalState("submit_challenge");
        setModalHeader("Submit Challenge");
        setModalText("Select times below that you are available to battle.");
    }
    const ConfirmSubmitChallenge = () => {
        if (selectedTimesUTC.length < 12) {
            setModalText("Insufficient slots selected.");
        } else {
            apiFetch(
                villageAPI,
                {
                    request: 'SubmitChallenge',
                    seat_id: challengeTarget.seat_id,
                    selected_times: selectedTimesUTC,
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setChallengeDataState(response.data.challengeData);
                setModalHeader("Confirmation");
                setModalText(response.data.response_message);
                setModalState("response_message");
            });
        }
    }
    const CancelChallenge = () => {
        if (modalState == "confirm_cancel_challenge") {
            apiFetch(
                villageAPI,
                {
                    request: 'CancelChallenge',
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setChallengeDataState(response.data.challengeData);
                setModalHeader("Confirmation");
                setModalText(response.data.response_message);
                setModalState("response_message");
            });
        }
        else {
            setModalHeader("Confirmation");
            setModalState("confirm_cancel_challenge");
            setModalText("Are you sure you wish to cancel your pending challenge request?");
        }
    }
    const AcceptChallenge = (target_challenge) => {
        setChallengeTarget(target_challenge);
        setModalState("accept_challenge");
        setModalHeader("Accept Challenge");
        setModalText("Select a time slot below to accept the challenge.");
    }
    const ConfirmAcceptChallenge = () => {
        if (!selectedTimeUTC) {
            setModalText("Select a slot to accept the challenge.");
        } else {
            apiFetch(
                villageAPI,
                {
                    request: 'AcceptChallenge',
                    challenge_id: challengeTarget.request_id,
                    time: selectedTimeUTC,
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setChallengeDataState(response.data.challengeData);
                setModalHeader("Confirmation");
                setModalText(response.data.response_message);
                setModalState("response_message");
            });
        }
    }
    const CancelChallengeSchedule = () => {
        setSelectedTimesUTC([]);
    }
    const totalPopulation = populationData.reduce((acc, rank) => acc + rank.count, 0);
    const kage = seatDataState.find(seat => seat.seat_type === 'kage');
    return (
        <>
            {modalState !== "closed" &&
                <>
                    <div className="modal_backdrop"></div>
                    <div className="modal">
                        <div className="modal_header">{modalHeader}</div>
                        <div className="modal_text">{modalText}</div>
                        {modalState == "confirm_resign" &&
                            <>
                                <div className="modal_confirm_button" onClick={() => Resign()}>confirm</div>
                                <div className="modal_cancel_button" onClick={() => setModalState("closed")}>cancel</div>
                            </>
                        }
                        {modalState == "submit_challenge" &&
                            <>
                                <div className="schedule_challenge_subtext_wrapper">
                                    <span className="schedule_challenge_subtext">Time slots are displayed in your local time.</span>
                                    <span className="schedule_challenge_subtext">The seat holder will have 24 hours to choose one of your selected times.</span>
                                    <span className="schedule_challenge_subtext">Your battle will be scheduled a minimum of 12 hours from the time of their selection.</span>
                                </div>
                                <TimeGrid
                                setSelectedTimesUTC={setSelectedTimesUTC}
                                startHourUTC={luxon.DateTime.fromObject({ hour: 0, zone: luxon.Settings.defaultZoneName }).toUTC().hour}
                                />
                                <div className="modal_confirm_button" onClick={() => ConfirmSubmitChallenge()}>confirm</div>
                                <div className="modal_cancel_button" onClick={() => setModalState("closed")}>cancel</div>
                            </>
                        }
                        {modalState == "accept_challenge" &&
                            <>
                                <div className="schedule_challenge_subtext_wrapper">
                                    <span className="schedule_challenge_subtext">Time slots are displayed in your local time.</span>
                                    <span className="schedule_challenge_subtext">The first slot below is set a minimum 12 hours from the current time.</span>
                                </div>
                                <TimeGridResponse
                                availableTimesUTC={JSON.parse(challengeTarget.selected_times)}
                                setSelectedTimeUTC={setSelectedTimeUTC}
                                startHourUTC={(luxon.DateTime.utc().minute === 0 ? luxon.DateTime.utc().hour + 12 : luxon.DateTime.utc().hour + 13) % 24}
                                />
                                <div className="modal_confirm_button" onClick={() => ConfirmAcceptChallenge()}>confirm</div>
                                <div className="modal_cancel_button" onClick={() => setModalState("closed")}>cancel</div>
                            </>
                        }
                        {modalState == "confirm_cancel_challenge" &&
                            <>
                                <div className="modal_confirm_button" onClick={() => CancelChallenge()}>confirm</div>
                                <div className="modal_cancel_button" onClick={() => setModalState("closed")}>cancel</div>
                            </>
                        }
                        {modalState == "response_message" &&
                            <div className="modal_close_button" onClick={() => setModalState("closed")}>close</div>
                        }
                    </div>
                </>
            }
            <ChallengeContainer
                challengeDataState={challengeDataState}
                playerSeatState={playerSeatState}
                CancelChallenge={CancelChallenge}
                AcceptChallenge={AcceptChallenge}
            />
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
                                <div className="population_item" style={{ width: "100%" }}>
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
                                <div className="kage_title">{kage.seat_title + " of " + villageName}</div>
                                {kage.seat_id && kage.seat_id == playerSeatState.seat_id &&
                                    <div className="kage_resign_button" onClick={() => Resign()}>resign</div>
                                }
                                {!kage.seat_id &&
                                    <div className="kage_claim_button" onClick={() => ClaimSeat("kage")}>claim</div>
                                }
                                {(kage.seat_id && kage.seat_id != playerSeatState.seat_id) &&
                                    <div className="kage_challenge_button" onClick={() => Challenge(kage)}>challenge</div>
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
                                            <div className="elder_name">{elder.user_name ? <a href={"/?id=6&user=" + elder.user_name}>{elder.user_name}</a> : "---"}</div>
                                            {(elder.seat_id && elder.seat_id == playerSeatState.seat_id) &&
                                                <div className="elder_resign_button" onClick={() => Resign()}>resign</div>
                                            }
                                            {!elder.seat_id &&
                                                <div className={playerSeatState.seat_id ? "elder_claim_button disabled" : "elder_claim_button"} onClick={playerSeatState.seat_id ? null : () => ClaimSeat("elder")}>claim</div>
                                            }
                                            {(elder.seat_id && elder.seat_id != playerSeatState.seat_id) &&
                                                <div className="elder_challenge_button" onClick={() => Challenge(elder)}>challenge</div>
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
                                    <div className="points_total">{pointsDataState.points}</div>
                                </div>
                                <div className="points_item">
                                    <div className="points_label">monthly</div>
                                    <div className="points_total">{pointsDataState.points}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="row second">
                    <div className="column first">
                        <div className="header">Village policy</div>
                        <div className="village_policy_container">
                            <div className="village_policy_bonus_container">
                                {policyDisplay.bonuses.map((bonus, index) => (
                                    <div key={index} className="policy_bonus_item">
                                        <svg width="16" height="16" viewBox="0 0 100 100">
                                            <polygon points="25,20 50,45 25,70 0,45" fill="#4a5e45" />
                                            <polygon points="25,0 50,25 25,50 0,25" fill="#6ab352" />
                                        </svg>
                                        <div className="policy_bonus_text">{bonus}</div>
                                    </div>
                                ))}
                            </div>
                            <div className="village_policy_main_container">
                                <div className="village_policy_main_inner">
                                    <div className="village_policy_banner" style={{ backgroundImage: "url(" + policyDisplay.banner + ")" }}></div>
                                    <div className="village_policy_name_container">
                                        <div className={"village_policy_name " + policyDisplay.glowClass}>{policyDisplay.name}</div>
                                    </div>
                                    <div className="village_policy_phrase">{policyDisplay.phrase}</div>
                                    <div className="village_policy_description">{policyDisplay.description}</div>
                                </div>
                            </div>
                            <div className="village_policy_penalty_container">
                                {policyDisplay.penalties.map((penalty, index) => (
                                    <div key={index} className="policy_penalty_item">
                                        <svg width="16" height="16" viewBox="0 0 100 100">
                                            <polygon points="25,20 50,45 25,70 0,45" fill="#4f1e1e" />
                                            <polygon points="25,0 50,25 25,50 0,25" fill="#ad4343" />
                                        </svg>
                                        <div className="policy_penalty_text">{penalty}</div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
                <div className="row third">
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
                                {diplomacyDataState
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
}

function KageQuarters({
    playerSeatState,
    villageName,
    villageAPI,
    policyDataState,
    setPolicyDataState,
    seatDataState,
    pointsDataState,
    setPointsDataState,
    diplomacyDataState,
    resourceDataState,
    setResourceDataState,
    proposalDataState,
    setProposalDataState,
    strategicDataState,
    setStrategicDataState,
    handleErrors,
    getKageKanji,
    getVillageIcon,
    getPolicyDisplayData,
    StrategicInfoItem
}) {
    const kage = seatDataState.find(seat => seat.seat_type === 'kage');
    const [currentProposal, setCurrentProposal] = React.useState(null);
    const [currentProposalKey, setCurrentProposalKey] = React.useState(null);
    const [displayPolicyID, setDisplayPolicyID] = React.useState(policyDataState.policy_id);
    const [policyDisplay, setPolicyDisplay] = React.useState(getPolicyDisplayData(displayPolicyID));
    const [proposalRepAdjustment, setProposalRepAdjustment] = React.useState(0);
    const [strategicDisplayLeft, setStrategicDisplayLeft] = React.useState(strategicDataState.find(item => item.village.name == villageName));
    const [strategicDisplayRight, setStrategicDisplayRight] = React.useState(strategicDataState.find(item => item.village.name != villageName));
    const [modalState, setModalState] = React.useState("closed");
    const modalText = React.useRef(null);
    const ChangePolicy = () => {
        if (modalState == "confirm_policy") {
            apiFetch(
                villageAPI,
                {
                    request: 'CreateProposal',
                    type: 'policy',
                    policy_id: displayPolicyID,
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setProposalDataState(response.data.proposalData);
                modalText.current = response.data.response_message;
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_policy");
            modalText.current = "Are you sure you want to change policies? You will be unable to select a new policy for 14 days.";
        }
    }
    const DeclareWar = () => {
        if (modalState == "confirm_declare_war") {
            apiFetch(
                villageAPI,
                {
                    request: 'CreateProposal',
                    type: 'declare_war',
                    target_village_id: strategicDisplayRight.village.village_id,
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setProposalDataState(response.data.proposalData);
                modalText.current = response.data.response_message;
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_declare_war");
            modalText.current = "Are you sure you declare war with " + strategicDisplayRight.village.name + "?";
        }
    }
    const OfferPeace = () => {
        if (modalState == "confirm_offer_peace") {
            apiFetch(
                villageAPI,
                {
                    request: 'CreateProposal',
                    type: 'offer_peace',
                    target_village_id: strategicDisplayRight.village.village_id,
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setProposalDataState(response.data.proposalData);
                modalText.current = response.data.response_message;
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_offer_peace");
            modalText.current = "Are you sure you want to offer peace with " + strategicDisplayRight.village.name + "?";
        }
    }
    const OfferAlliance = () => {
        if (modalState == "confirm_form_alliance") {
            apiFetch(
                villageAPI,
                {
                    request: 'CreateProposal',
                    type: 'offer_alliance',
                    target_village_id: strategicDisplayRight.village.village_id,
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setProposalDataState(response.data.proposalData);
                modalText.current = response.data.response_message;
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_form_alliance");
            modalText.current = "Are you sure you want to form an alliance with " + strategicDisplayRight.village.name + "?\nYou can be a member of only one Alliance at any given time.";
        }
    }
    const BreakAlliance = () => {
        if (modalState == "confirm_break_alliance") {
            apiFetch(
                villageAPI,
                {
                    request: 'CreateProposal',
                    type: 'break_alliance',
                    target_village_id: strategicDisplayRight.village.village_id,
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setProposalDataState(response.data.proposalData);
                modalText.current = response.data.response_message;
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_break_alliance");
            modalText.current = "Are you sure you want break an alliance with " + strategicDisplayRight.village.name + "?";
        }
    }
    const CancelProposal = () => {
        if (modalState == "confirm_cancel_proposal") {
            apiFetch(
                villageAPI,
                {
                    request: 'CancelProposal',
                    proposal_id: currentProposal.proposal_id,
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setProposalDataState(response.data.proposalData);
                setCurrentProposal(null);
                setCurrentProposalKey(null);
                modalText.current = response.data.response_message;
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_cancel_proposal");
            modalText.current = "Are you sure you want to cancel this proposal?";
        }
    }
    const EnactProposal = () => {
        if (modalState == "confirm_enact_proposal") {
            apiFetch(
                villageAPI,
                {
                    request: 'EnactProposal',
                    proposal_id: currentProposal.proposal_id,
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setProposalDataState(response.data.proposalData);
                setCurrentProposal(null);
                setCurrentProposalKey(null);
                setPolicyDataState(response.data.policyData);
                setDisplayPolicyID(response.data.policyData.policy_id);
                setPolicyDisplay(getPolicyDisplayData(response.data.policyData.policy_id));
                setStrategicDataState(response.data.strategicData);
                setStrategicDisplayLeft(response.data.strategicData.find(item => item.village.name == villageName));
                setStrategicDisplayRight(response.data.strategicData.find(item => item.village.name == strategicDisplayRight.village.name));
                modalText.current = response.data.response_message;
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_enact_proposal");
            modalText.current = "Are you sure you want to enact this proposal?";
        }
    }
    const BoostVote = () => {
        if (modalState == "confirm_boost_vote") {
            apiFetch(
                villageAPI,
                {
                    request: 'BoostVote',
                    proposal_id: currentProposal.proposal_id,
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setProposalDataState(response.data.proposalData);
                setCurrentProposal(response.data.proposalData[currentProposalKey]);
                modalText.current = response.data.response_message;
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_boost_vote");
            modalText.current = "Are you sure you wish to boost this vote?\nThe Kage will gain/lose 500 Reputation based on their decision. This will cost 500 Reputation when the proposal is enacted.";
        }
    }
    const CancelVote = () => {
        if (modalState == "confirm_cancel_vote") {
            apiFetch(
                villageAPI,
                {
                    request: 'CancelVote',
                    proposal_id: currentProposal.proposal_id,
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setProposalDataState(response.data.proposalData);
                setCurrentProposal(response.data.proposalData[currentProposalKey]);
                modalText.current = response.data.response_message;
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_cancel_vote");
            modalText.current = "Are you sure you wish to cancel your vote for this proposal?";
        }
    }
    const SubmitVote = (vote) => {
        apiFetch(
            villageAPI,
            {
                request: 'SubmitVote',
                proposal_id: currentProposal.proposal_id,
                vote: vote,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setProposalDataState(response.data.proposalData);
            setCurrentProposal(response.data.proposalData[currentProposalKey]);
        });
    }
    React.useEffect(() => {
        if (proposalDataState.length && currentProposal === null) {
            setCurrentProposal(proposalDataState[0]);
            setCurrentProposalKey(0);
            setProposalRepAdjustment(proposalDataState[0].votes.reduce((acc, vote) => acc + parseInt(vote.rep_adjustment), 0));
        }
    }, [proposalDataState]);
    return (
        <>
            {modalState !== "closed" &&
                <>
                    <div className="modal_backdrop"></div>
                    <div className="modal">
                        <div className="modal_header">Confirmation</div>
                        <div className="modal_text">{modalText.current}</div>
                        {modalState == "confirm_policy" &&
                            <>
                                <div className="modal_confirm_button" onClick={() => ChangePolicy()}>Confirm</div>
                                <div className="modal_cancel_button" onClick={() => setModalState("closed")}>cancel</div>
                            </>
                        }
                        {modalState == "confirm_cancel_proposal" &&
                            <>
                                <div className="modal_confirm_button" onClick={() => CancelProposal()}>Confirm</div>
                                <div className="modal_cancel_button" onClick={() => setModalState("closed")}>cancel</div>
                            </>
                        }
                        {modalState == "confirm_enact_proposal" &&
                            <>
                                <div className="modal_confirm_button" onClick={() => EnactProposal()}>Confirm</div>
                                <div className="modal_cancel_button" onClick={() => setModalState("closed")}>cancel</div>
                            </>
                        }
                        {modalState == "confirm_boost_vote" &&
                            <>
                                <div className="modal_confirm_button" onClick={() => BoostVote()}>Confirm</div>
                                <div className="modal_cancel_button" onClick={() => setModalState("closed")}>cancel</div>
                            </>
                        }
                        {modalState == "confirm_cancel_vote" &&
                            <>
                                <div className="modal_confirm_button" onClick={() => CancelVote()}>Confirm</div>
                                <div className="modal_cancel_button" onClick={() => setModalState("closed")}>cancel</div>
                            </>
                        }
                        {modalState == "confirm_declare_war" &&
                            <>
                                <div className="modal_confirm_button" onClick={() => DeclareWar()}>Confirm</div>
                                <div className="modal_cancel_button" onClick={() => setModalState("closed")}>cancel</div>
                            </>
                        }
                        {modalState == "confirm_offer_peace" &&
                        <>
                                <div className="modal_confirm_button" onClick={() => OfferPeace()}>Confirm</div>
                                <div className="modal_cancel_button" onClick={() => setModalState("closed")}>cancel</div>
                            </>
                        }
                        {modalState == "confirm_form_alliance" &&
                        <>
                                <div className="modal_confirm_button" onClick={() => OfferAlliance()}>Confirm</div>
                                <div className="modal_cancel_button" onClick={() => setModalState("closed")}>cancel</div>
                            </>
                        }
                        {modalState == "confirm_break_alliance" &&
                        <>
                            <div className="modal_confirm_button" onClick={() => BreakAlliance()}>Confirm</div>
                                <div className="modal_cancel_button" onClick={() => setModalState("closed")}>cancel</div>
                            </>
                        }
                        {modalState == "response_message" &&
                            <div className="modal_close_button" onClick={() => setModalState("closed")}>close</div>
                        }
                    </div>
                </>
            }
            <div className="kq_container">
                <div className="row first">
                    <div className="column first">
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
                                <div className="kage_title">{kage.seat_title + " of " + villageName}</div>
                            </div>
                        </div>
                    </div>
                    <div className="column second">
                        <div className="proposal_container">
                            <div className="header">Proposals</div>
                            <div className="content box-primary">
                                <div className="proposal_container_top">
                                    <div className="proposal_container_left">
                                        <svg className="previous_proposal_button" width="25" height="25" viewBox="0 0 100 100" onClick={() => cycleProposal("decrement")}>
                                            <polygon className="previous_proposal_triangle_inner" points="100,0 100,100 35,50" />
                                            <polygon className="previous_proposal_triangle_outer" points="65,0 65,100 0,50" />
                                        </svg>
                                        <div className="previous_proposal_button_label">previous</div>
                                    </div>
                                    <div className="proposal_container_middle">
                                        {currentProposalKey !== null &&
                                            <div className="proposal_count">
                                                PROPOSAL {currentProposalKey + 1} OUT OF {proposalDataState.length}
                                            </div>
                                        }
                                        {currentProposalKey === null &&
                                            <div className="proposal_count">
                                                PROPOSAL 0 OUT OF {proposalDataState.length}
                                            </div>
                                        }
                                        <div className="active_proposal_name_container">
                                            <svg className="proposal_decoration_nw" width="18" height="8">
                                                <polygon points="0,4 4,0 8,4 4,8" fill="#ad9357" />
                                                <polygon points="10,4 14,0 18,4 14,8" fill="#ad9357" />
                                            </svg>
                                            <div className="active_proposal_name">
                                                {currentProposal ? currentProposal.name : "NO ACTIVE PROPOSALs"}
                                            </div>
                                            <svg className="proposal_decoration_se" width="18" height="8">
                                                <polygon points="0,4 4,0 8,4 4,8" fill="#ad9357" />
                                                <polygon points="10,4 14,0 18,4 14,8" fill="#ad9357" />
                                            </svg>
                                        </div>
                                        <div className="active_proposal_timer">
                                            {(currentProposal && currentProposal.vote_time_remaining !== null) && currentProposal.vote_time_remaining}
                                            {(currentProposal && currentProposal.enact_time_remaining !== null) && currentProposal.enact_time_remaining}
                                        </div>
                                    </div>
                                    <div className="proposal_container_right">
                                        <svg className="next_proposal_button" width="25" height="25" viewBox="0 0 100 100" onClick={() => cycleProposal("increment")}>
                                            <polygon className="next_proposal_triangle_inner" points="0,0 0,100 65,50" />
                                            <polygon className="next_proposal_triangle_outer" points="35,0 35,100 100,50" />
                                        </svg>
                                        <div className="next_proposal_button_label">next</div>
                                    </div>
                                </div>
                                <div className="proposal_container_bottom">
                                    {playerSeatState.seat_type == "kage" &&
                                        <>
                                        <div className="proposal_cancel_button_wrapper">
                                            <div className={currentProposal ? "proposal_cancel_button" : "proposal_cancel_button disabled"} onClick={() => CancelProposal()}>cancel proposal</div>
                                        </div>
                                        <div className="proposal_enact_button_wrapper">
                                            <div className={(currentProposal && (currentProposal.enact_time_remaining !== null ||
                                                currentProposal.votes.length == seatDataState.filter(seat => seat.seat_type == "elder" && seat.seat_id != null).length
                                            )) ? "proposal_enact_button" : "proposal_enact_button disabled"} onClick={() => EnactProposal()}>enact proposal</div>
                                            {proposalRepAdjustment > 0 &&
                                                <div className="rep_change positive">REPUATION GAIN: +{proposalRepAdjustment}</div>
                                            }
                                            {proposalRepAdjustment < 0 &&
                                                <div className="rep_change negative">REPUTATION LOSS: {proposalRepAdjustment}</div>
                                            }
                                        </div>
                                        </>
                                    }
                                    {playerSeatState.seat_type == "elder" &&
                                        <>
                                        {!currentProposal &&
                                            <>
                                                <div className="proposal_yes_button_wrapper">
                                                    <div className="proposal_yes_button disabled">vote in favor</div>
                                                </div>
                                                <div className="proposal_no_button_wrapper">
                                                    <div className="proposal_no_button disabled">vote against</div>
                                                </div>
                                            </>
                                        }
                                        {(currentProposal && currentProposal.vote_time_remaining != null && !currentProposal.votes.find(vote => vote.user_id == playerSeatState.user_id)) &&
                                            <>
                                                <div className="proposal_yes_button_wrapper">
                                                    <div className="proposal_yes_button" onClick={() => SubmitVote(1)}>vote in favor</div>
                                                </div>
                                                <div className="proposal_no_button_wrapper">
                                                    <div className="proposal_no_button" onClick={() => SubmitVote(0)}>vote against</div>
                                                </div>
                                            </>
                                        }
                                        {(currentProposal && currentProposal.vote_time_remaining == null && !currentProposal.votes.find(vote => vote.user_id == playerSeatState.user_id)) &&
                                            <>
                                                <div className="proposal_yes_button_wrapper">
                                                    <div className="proposal_yes_button disabled">vote in favor</div>
                                                </div>
                                                <div className="proposal_no_button_wrapper">
                                                    <div className="proposal_no_button disabled">vote against</div>
                                                </div>
                                            </>
                                        }
                                        {(currentProposal && currentProposal.vote_time_remaining != null && currentProposal.votes.find(vote => vote.user_id == playerSeatState.user_id)) &&
                                            <>
                                                <div className="proposal_cancel_vote_button_wrapper">
                                                <div className="proposal_cancel_vote_button" onClick={() => CancelVote()}>change vote</div>
                                                </div>
                                                <div className="proposal_boost_vote_button_wrapper">
                                                <div className="proposal_boost_vote_button" onClick={() => BoostVote()}>boost vote</div>
                                                </div>
                                            </>
                                        }
                                        {(currentProposal && currentProposal.vote_time_remaining == null && currentProposal.votes.find(vote => vote.user_id == playerSeatState.user_id)) &&
                                            <>
                                                <div className="proposal_cancel_vote_button_wrapper">
                                                    <div className="proposal_cancel_vote_button disabled">cancel vote</div>
                                                </div>
                                                <div className="proposal_boost_vote_button_wrapper">
                                                    <div className="proposal_boost_vote_button disabled">boost vote</div>
                                                </div>
                                            </>
                                        }
                                        </>
                                    }
                                </div>
                            </div>
                            <div className="proposal_elder_header">Elders</div>
                            <div className="elder_list">
                                <svg height="0" width="0">
                                    <defs>
                                        <filter id="green_glow">
                                            <feGaussianBlur in="SourceAlpha" stdDeviation="2" result="blur" />
                                            <feFlood floodColor="green" result="floodColor" />
                                            <feComponentTransfer in="blur" result="opacityAdjustedBlur">
                                                <feFuncA type="linear" slope="3" />
                                            </feComponentTransfer>
                                            <feComposite in="floodColor" in2="opacityAdjustedBlur" operator="in" result="coloredBlur" />
                                            <feMerge>
                                                <feMergeNode in="coloredBlur" />
                                                <feMergeNode in="SourceGraphic" />
                                            </feMerge>
                                        </filter>
                                        <filter id="red_glow">
                                            <feGaussianBlur in="SourceAlpha" stdDeviation="2" result="blur" />
                                            <feFlood floodColor="red" result="floodColor" />
                                            <feComponentTransfer in="blur" result="opacityAdjustedBlur">
                                                <feFuncA type="linear" slope="2" />
                                            </feComponentTransfer>
                                            <feComposite in="floodColor" in2="opacityAdjustedBlur" operator="in" result="coloredBlur" />
                                            <feMerge>
                                                <feMergeNode in="coloredBlur" />
                                                <feMergeNode in="SourceGraphic" />
                                            </feMerge>
                                        </filter>
                                    </defs>
                                </svg>
                                {seatDataState
                                    .filter(elder => elder.seat_type === 'elder')
                                    .map((elder, index) => (
                                        <div key={elder.seat_key} className="elder_item">
                                            <div className="elder_vote_wrapper" style={{visibility: (currentProposal && currentProposal.votes.find(vote => vote.user_id == elder.user_id )) ? null : "hidden"}}>
                                                <div className="elder_vote">
                                                    {(currentProposal && currentProposal.votes.find(vote => vote.user_id == elder.user_id && vote.vote == 1 && parseInt(vote.rep_adjustment) > 0)) &&
                                                        <img className="vote_yes_image glow" src="/images/v2/icons/yesvote.png" />
                                                    }
                                                    {(currentProposal && currentProposal.votes.find(vote => vote.user_id == elder.user_id && vote.vote == 0 && parseInt(vote.rep_adjustment) < 0)) &&
                                                        <img className="vote_no_image glow" src="/images/v2/icons/novote.png" />
                                                    }
                                                    {(currentProposal && currentProposal.votes.find(vote => vote.user_id == elder.user_id && vote.vote == 1 && parseInt(vote.rep_adjustment) == 0)) &&
                                                        <img className="vote_yes_image" src="/images/v2/icons/yesvote.png"/>
                                                    }
                                                    {(currentProposal && currentProposal.votes.find(vote => vote.user_id == elder.user_id && vote.vote == 0 && parseInt(vote.rep_adjustment)  == 0)) &&
                                                        <img className="vote_no_image" src="/images/v2/icons/novote.png" />
                                                    }
                                                </div>
                                            </div>
                                            <div className="elder_avatar_wrapper">
                                                {elder.avatar_link && <img className="elder_avatar" src={elder.avatar_link} />}
                                                {!elder.avatar_link && <div className="elder_avatar_fill"></div>}
                                            </div>
                                            <div className="elder_name">{elder.user_name ? <a href={"/?id=6&user=" + elder.user_name}>{elder.user_name}</a> : "---"}</div>
                                        </div>
                                    ))}
                            </div>
                        </div>
                    </div>
                </div>
                <div className="row second">
                    <div className="column first">
                        <div className="header">Village policy</div>
                        <div className="village_policy_container">
                            <div className="village_policy_bonus_container">
                                {policyDisplay.bonuses.map((bonus, index) => (
                                    <div key={index} className="policy_bonus_item">
                                        <svg width="16" height="16" viewBox="0 0 100 100">
                                            <polygon points="25,20 50,45 25,70 0,45" fill="#4a5e45" />
                                            <polygon points="25,0 50,25 25,50 0,25" fill="#6ab352" />
                                        </svg>
                                        <div className="policy_bonus_text">{bonus}</div>
                                    </div>
                                ))}
                            </div>
                            {displayPolicyID != policyDataState.policy_id &&
                                <div className={playerSeatState.seat_type == "kage" ? "village_policy_change_button" : "village_policy_change_button disabled"} onClick={() => ChangePolicy()}>change policy</div>
                            }
                            <div className="village_policy_main_container">
                                <div className="village_policy_main_inner">
                                    <div className="village_policy_banner" style={{ backgroundImage: "url(" + policyDisplay.banner + ")" }}></div>
                                    <div className="village_policy_name_container">
                                        <div className={"village_policy_name " + policyDisplay.glowClass}>{policyDisplay.name}</div>
                                    </div>
                                    <div className="village_policy_phrase">{policyDisplay.phrase}</div>
                                    <div className="village_policy_description">{policyDisplay.description}</div>
                                    {displayPolicyID > 1 &&
                                        <div className="village_policy_previous_wrapper">
                                            <svg className="previous_policy_button" width="20" height="20" viewBox="0 0 100 100" onClick={() => cyclePolicy("decrement")}>
                                                <polygon className="previous_policy_triangle_inner" points="100,0 100,100 35,50" />
                                                <polygon className="previous_policy_triangle_outer" points="65,0 65,100 0,50" />
                                            </svg>
                                        </div>
                                    }
                                    {displayPolicyID < 4 &&
                                        <div className="village_policy_next_wrapper">
                                            <svg className="next_policy_button" width="20" height="20" viewBox="0 0 100 100" onClick={() => cyclePolicy("increment")}>
                                                <polygon className="next_policy_triangle_inner" points="0,0 0,100 65,50" />
                                                <polygon className="next_policy_triangle_outer" points="35,0 35,100 100,50" />
                                            </svg>
                                        </div>
                                    }
                                </div>
                            </div>
                            <div className="village_policy_penalty_container">
                                {policyDisplay.penalties.map((penalty, index) => (
                                    <div key={index} className="policy_penalty_item">
                                        <svg width="16" height="16" viewBox="0 0 100 100">
                                            <polygon points="25,20 50,45 25,70 0,45" fill="#4f1e1e" />
                                            <polygon points="25,0 50,25 25,50 0,25" fill="#ad4343" />
                                        </svg>
                                        <div className="policy_penalty_text">{penalty}</div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
                <div className="row third">
                    <div className="column first">
                        <div className="kq_navigation_row">
                            <div className="header">Strategic information</div>
                        </div>
                    </div>
                </div>
                <div className="row fourth">
                    <div className="column first">
                        <div className="strategic_info_container">
                            <StrategicInfoItem
                                strategicInfoData={strategicDisplayLeft}
                                getPolicyDisplayData={getPolicyDisplayData}
                            />
                            <div className="strategic_info_navigation">
                                <div className="strategic_info_navigation_diplomacy_buttons">
                                    {strategicDisplayLeft.enemies.find(enemy => enemy == strategicDisplayRight.village.name) ?
                                        <div className="diplomacy_action_button_wrapper war cancel" onClick={() => OfferPeace()}>
                                            <div className="diplomacy_action_button_inner">
                                            </div>
                                        </div>
                                        :
                                        <div className="diplomacy_action_button_wrapper war" onClick={() => DeclareWar()}>
                                            <div className="diplomacy_action_button_inner">
                                                <img src="/images/icons/war.png" className="diplomacy_action_button_icon" />
                                            </div>
                                        </div>
                                    }
                                    {strategicDisplayLeft.allies.find(ally => ally == strategicDisplayRight.village.name) ?
                                        <div className="diplomacy_action_button_wrapper alliance cancel" onClick={() => BreakAlliance()}>
                                            <div className="diplomacy_action_button_inner">
                                            </div>
                                        </div>
                                        :
                                        <div className="diplomacy_action_button_wrapper alliance" onClick={() => OfferAlliance()}>
                                            <div className="diplomacy_action_button_inner">
                                                <img src="/images/icons/ally.png" className="diplomacy_action_button_icon" />
                                            </div>
                                        </div>
                                    }
                                </div>
                                <div className="strategic_info_navigation_village_buttons">
                                    {villageName != "Stone" &&
                                        <div className={strategicDisplayRight.village.village_id == 1 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[0])}>
                                            <div className="strategic_info_nav_button_inner">
                                                <img src={getVillageIcon(1)} className="strategic_info_nav_button_icon" />
                                            </div>
                                        </div>
                                    }
                                    {villageName != "Cloud" &&
                                        <div className={strategicDisplayRight.village.village_id == 2 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[1])}>
                                            <div className="strategic_info_nav_button_inner">
                                                <img src={getVillageIcon(2)} className="strategic_info_nav_button_icon" />
                                            </div>
                                        </div>
                                    }
                                    {villageName != "Leaf" &&
                                        <div className={strategicDisplayRight.village.village_id == 3 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[2])}>
                                            <div className="strategic_info_nav_button_inner">
                                                <img src={getVillageIcon(3)} className="strategic_info_nav_button_icon" />
                                            </div>
                                        </div>
                                    }
                                    {villageName != "Sand" &&
                                        <div className={strategicDisplayRight.village.village_id == 4 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[3])}>
                                            <div className="strategic_info_nav_button_inner">
                                                <img src={getVillageIcon(4)} className="strategic_info_nav_button_icon" />
                                            </div>
                                        </div>
                                    }
                                    {villageName != "Mist" &&
                                        <div className={strategicDisplayRight.village.village_id == 5 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[4])}>
                                            <div className="strategic_info_nav_button_inner">
                                                <img src={getVillageIcon(5)} className="strategic_info_nav_button_icon" />
                                            </div>
                                        </div>
                                    }
                                </div>
                            </div>
                            <StrategicInfoItem
                                strategicInfoData={strategicDisplayRight}
                                getPolicyDisplayData={getPolicyDisplayData}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
    function cyclePolicy(direction) {
        var newPolicyID;
        switch (direction) {
            case "increment":
                newPolicyID = Math.min(4, displayPolicyID + 1);
                setDisplayPolicyID(newPolicyID);
                setPolicyDisplay(getPolicyDisplayData(newPolicyID));
                break;
            case "decrement":
                newPolicyID = Math.max(1, displayPolicyID - 1);
                setDisplayPolicyID(newPolicyID);
                setPolicyDisplay(getPolicyDisplayData(newPolicyID));
                break;
        }
    }
    function cycleProposal(direction) {
        if (proposalDataState.length == 0) {
            return;
        }
        var newProposalKey;
        switch (direction) {
            case "increment":
                newProposalKey = Math.min(proposalDataState.length - 1, currentProposalKey + 1);
                setCurrentProposalKey(newProposalKey);
                setCurrentProposal(proposalDataState[newProposalKey]);
                setProposalRepAdjustment(proposalDataState[newProposalKey].votes.reduce((acc, vote) => acc + vote.rep_adjustment, 0));
                break;
            case "decrement":
                newProposalKey = Math.max(0, currentProposalKey - 1);
                setCurrentProposalKey(newProposalKey);
                setCurrentProposal(proposalDataState[newProposalKey]);
                setProposalRepAdjustment(proposalDataState[newProposalKey].votes.reduce((acc, vote) => acc + vote.rep_adjustment, 0));
                break;
        }
    }
}

function WorldInfo({
    villageName,
    strategicDataState,
    getVillageIcon,
    StrategicInfoItem,
    getPolicyDisplayData
}) {
    const [strategicDisplayLeft, setStrategicDisplayLeft] = React.useState(strategicDataState.find(item => item.village.name == villageName));
    const [strategicDisplayRight, setStrategicDisplayRight] = React.useState(strategicDataState.find(item => item.village.name != villageName));
    return (
        <div className="worldInfo_container">
            <div className="row first">
                <div className="column first">
                    <div className="header">Strategic information</div>
                    <div className="strategic_info_container">
                        <StrategicInfoItem
                            strategicInfoData={strategicDisplayLeft}
                            getPolicyDisplayData={getPolicyDisplayData}
                        />
                        <div className="strategic_info_navigation" style={{marginTop: "155px"}}>
                            <div className="strategic_info_navigation_village_buttons">
                                {villageName != "Stone" &&
                                    <div className={strategicDisplayRight.village.village_id == 1 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[0])}>
                                        <div className="strategic_info_nav_button_inner">
                                            <img src={getVillageIcon(1)} className="strategic_info_nav_button_icon" />
                                        </div>
                                    </div>
                                }
                                {villageName != "Cloud" &&
                                    <div className={strategicDisplayRight.village.village_id == 2 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[1])}>
                                        <div className="strategic_info_nav_button_inner">
                                            <img src={getVillageIcon(2)} className="strategic_info_nav_button_icon" />
                                        </div>
                                    </div>
                                }
                                {villageName != "Leaf" &&
                                    <div className={strategicDisplayRight.village.village_id == 3 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[2])}>
                                        <div className="strategic_info_nav_button_inner">
                                            <img src={getVillageIcon(3)} className="strategic_info_nav_button_icon" />
                                        </div>
                                    </div>
                                }
                                {villageName != "Sand" &&
                                    <div className={strategicDisplayRight.village.village_id == 4 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[3])}>
                                        <div className="strategic_info_nav_button_inner">
                                            <img src={getVillageIcon(4)} className="strategic_info_nav_button_icon" />
                                        </div>
                                    </div>
                                }
                                {villageName != "Mist" &&
                                    <div className={strategicDisplayRight.village.village_id == 5 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[4])}>
                                        <div className="strategic_info_nav_button_inner">
                                            <img src={getVillageIcon(5)} className="strategic_info_nav_button_icon" />
                                        </div>
                                    </div>
                                }
                            </div>
                        </div>
                        <StrategicInfoItem
                            strategicInfoData={strategicDisplayRight}
                            getPolicyDisplayData={getPolicyDisplayData}
                        />
                    </div>
                </div>
            </div>
        </div>
    );
}

function StrategicInfoItem({ strategicInfoData, getPolicyDisplayData }) {
    function getStrategicInfoBanner(village_id) {
        switch (village_id) {
            case 1:
                return '/images/v2/decorations/strategic_banners/stratbannerstone.png';
            case 2:
                return '/images/v2/decorations/strategic_banners/stratbannercloud.png';
            case 3:
                return '/images/v2/decorations/strategic_banners/stratbannerleaf.png';
            case 4:
                return '/images/v2/decorations/strategic_banners/stratbannersand.png';
            case 5:
                return '/images/v2/decorations/strategic_banners/stratbannermist.png';
            default:
                return null;
        }
    }
    return (
        <div className="strategic_info_item">
            <div className="strategic_info_name_wrapper">
                <div className="strategic_info_name">{strategicInfoData.village.name}</div>
                <div className="strategic_info_policy">{getPolicyDisplayData(strategicInfoData.village.policy_id).name}</div>
            </div>
            <div className="strategic_info_banner" style={{ backgroundImage: "url(" + getStrategicInfoBanner(strategicInfoData.village.village_id) + ")" }}></div>
            <div className="strategic_info_top">
                <div className="column">
                    <div className="strategic_info_kage_wrapper">
                        <div className="strategic_info_label">kage:</div>
                        {strategicInfoData.seats.find(seat => seat.seat_type == "kage").user_name ?
                            <div className="strategic_info_seat"><a href={"/?id=6&user=" + strategicInfoData.seats.find(seat => seat.seat_type == "kage").user_name}>{strategicInfoData.seats.find(seat => seat.seat_type == "kage").user_name}</a></div> :
                            <div className="strategic_info_seat">-None-</div>
                        }
                    </div>
                    <div className="strategic_info_elder_wrapper">
                        <div className="strategic_info_label">elders:</div>
                        <div className="strategic_info_elders">
                            {strategicInfoData.seats.filter(seat => seat.seat_type == "elder")
                                .map((elder, index) => (
                                    elder.user_name ?
                                        <div key={elder.seat_key} className="strategic_info_seat"><a href={"/?id=6&user=" + elder.user_name}>{elder.user_name}</a></div> :
                                        <div key={elder.seat_key} className="strategic_info_seat">-None-</div>
                                ))}
                        </div>
                    </div>
                    <div className="strategic_info_points_wrapper">
                        <div className="strategic_info_label">points:</div>
                        <div className="strategic_info_points">{strategicInfoData.village.points}</div>
                    </div>
                    <div className="strategic_info_enemy_wrapper">
                        <div className="strategic_info_label">at war with <img className="strategic_info_war_icon" src="/images/icons/war.png" /></div>
                        <div className="strategic_info_relations">
                            {strategicInfoData.enemies
                                .map((enemy, index) => (
                                    <div key={index} className="strategic_info_relation_item">{enemy}</div>
                                ))}
                        </div>
                    </div>
                </div>
                <div className="column">
                    <div className="strategic_info_population_wrapper">
                        <div className="strategic_info_label">village ninja:</div>
                        <div className="strategic_info_population">
                            {strategicInfoData.population
                                .map((rank, index) => (
                                    <div key={rank.rank} className="strategic_info_population_item">{rank.count + " " + rank.rank}</div>
                                ))}
                            <div className="strategic_info_population_item total">{strategicInfoData.population.reduce((acc, rank) => acc + rank.count, 0)} total</div>
                        </div>
                    </div>
                    <div className="strategic_info_ally_wrapper">
                        <div className="strategic_info_label">allied with <img className="strategic_info_war_icon" src="/images/icons/ally.png" /></div>
                        <div className="strategic_info_relations">
                            {strategicInfoData.allies
                                .map((ally, index) => (
                                    <div key={index} className="strategic_info_relation_item">{ally}</div>
                                ))}
                        </div>
                    </div>
                </div>
            </div>
            <div className="strategic_info_bottom">
                <div className="column">
                    <div className="strategic_info_region_wrapper">
                        <div className="strategic_info_label">regions owned:</div>
                        <div className="strategic_info_regions">
                        {strategicInfoData.regions
                            .map((region, index) => (
                                <div key={region.name} className="strategic_info_region_item">{region.name}</div>
                            ))}
                        </div>
                    </div>
                </div>
                <div className="column">
                    <div className="strategic_info_resource_wrapper">
                        <div className="strategic_info_label">resource points:</div>
                        <div className="strategic_info_supply_points">
                        {Object.values(strategicInfoData.supply_points)
                            .map((supply_point, index) => (
                                <div key={index} className="strategic_info_supply_item"><span className="supply_point_name">{supply_point.name}</span> x{supply_point.count}</div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

const TimeGrid = ({ setSelectedTimesUTC, startHourUTC = 0 }) => {
    const [selectedTimes, setSelectedTimes] = React.useState([]);
    const timeSlots = generateSlotsForTimeZone(startHourUTC);

    function generateSlotsForTimeZone(startHour) {
        return Array.from({ length: 24 }, (slot, index) => (index + startHour) % 24).map(hour =>
            luxon.DateTime.fromObject({ hour }, { zone: 'utc' })
                .setZone('local')
        );
    };

    function toggleSlot(time) {
        const formattedTime = time.toFormat('HH:mm');
        let newSelectedTimes;

        if (selectedTimes.includes(formattedTime)) {
            newSelectedTimes = selectedTimes.filter(t => t !== formattedTime);
        } else {
            newSelectedTimes = [...selectedTimes, formattedTime];
        }

        setSelectedTimes(newSelectedTimes);
        setSelectedTimesUTC(convertSlotsToUTC(newSelectedTimes));
    };

    function convertSlotsToUTC(times) {
        return times.map(time =>
            luxon.DateTime.fromFormat(time, 'HH:mm', { zone: 'local' })
                .setZone('utc')
                .toFormat('HH:mm')
        );
    };

    return (
        <>
            <div className="timeslot_container">
                {timeSlots.map(time => (
                    <div key={time.toFormat('HH:mm')} onClick={() => toggleSlot(time)} className={selectedTimes.includes(time.toFormat('HH:mm')) ? "timeslot selected" : "timeslot"}>
                        {time.toFormat('HH:mm')}
                        <div className="timeslot_label">
                            {time.toFormat('h:mm') + " " + time.toFormat('a')}
                        </div>
                    </div>
                ))}
            </div>
            <div className="slot_requirements_container">
                <div className="slot_requirements">At least 12 slots must be selected for availability.</div>
                <div className="slot_count_wrapper">
                    <div className="slot_count">Selected: {selectedTimes.length}</div>
                </div>
            </div>
        </>
    );
}

const TimeGridResponse = ({ availableTimesUTC, setSelectedTimeUTC, startHourUTC = 0 }) => {
    const [selectedTime, setSelectedTime] = React.useState(null);
    const timeSlots = generateSlotsForTimeZone(startHourUTC);

    function generateSlotsForTimeZone(startHour) {
        return Array.from({ length: 24 }, (slot, index) => (index + startHour) % 24).map(hour =>
            luxon.DateTime.fromObject({ hour }, { zone: 'utc' }).toLocal()
        );
    };

    function toggleSlot(time) {
        const formattedTimeLocal = time.toFormat('HH:mm');
        const formattedTimeUTC = time.toUTC().toFormat('HH:mm');

        if (selectedTime === formattedTimeLocal) {
            setSelectedTime(null);
            setSelectedTimeUTC(null);
        } else if (availableTimesUTC.includes(formattedTimeUTC)) {
            setSelectedTime(formattedTimeLocal);
            setSelectedTimeUTC(formattedTimeUTC);
        }
    };

    return (
        <>
            <div className="timeslot_container">
                {timeSlots.map(time => {
                    const formattedTimeLocal = time.toFormat('HH:mm');
                    const formattedTimeUTC = time.toUTC().toFormat('HH:mm');
                    const isAvailable = availableTimesUTC.includes(formattedTimeUTC);
                    const slotClass = isAvailable ?
                        (selectedTime === formattedTimeLocal ? "timeslot selected" : "timeslot")
                        : "timeslot unavailable";

                    return (
                        <div key={formattedTimeLocal} onClick={() => toggleSlot(time)} className={slotClass}>
                            {formattedTimeLocal}
                            <div className="timeslot_label">
                                {time.toFormat('h:mm') + " " + time.toFormat('a')}
                            </div>
                        </div>
                    );
                })}
            </div>
        </>
    );
}

const ChallengeContainer = ({ challengeDataState, playerSeatState, CancelChallenge, AcceptChallenge }) => {
    return (
        <>
        {challengeDataState.length > 0 &&
            <div className="challenge_container">
                <div className="header">Challenges</div>
                <div className="challenge_list">
                    {challengeDataState && challengeDataState
                        .filter(challenge => challenge.challenger_id === playerSeatState.user_id)
                        .map((challenge, index) => (
                            <div key={challenge.request_id} className="challenge_item">
                                <div className="challenge_avatar_wrapper">
                                    <img className="challenge_avatar" src={challenge.seat_holder_avatar} />
                                </div>
                                <div className="challenge_details">
                                    <div className="challenge_header">
                                        ACTIVE CHALLENGE
                                    </div>
                                    <div>Seat Holder: <a href={"/?id=6&user=" + challenge.seat_holder_name}>{challenge.seat_holder_name}</a></div>
                                    <div>Time: {challenge.start_time
                                        ? (luxon.DateTime.fromSeconds(challenge.start_time).toLocal() <= luxon.DateTime.local()
                                            ? <span className="challenge_time_now">NOW</span>
                                            : luxon.DateTime.fromSeconds(challenge.start_time).toLocal().toFormat("LLL d, h:mm a"))
                                            : <span className="challenge_time_pending">PENDING</span>}
                                    </div>
                                    {(challenge.start_time && luxon.DateTime.fromSeconds(challenge.start_time).toLocal() >= luxon.DateTime.local() && !challenge.challenger_locked) &&
                                        <div className="challenge_button lock disabled">lock in<img src="/images/v2/icons/unlocked.png" /></div>
                                    }
                                    {(challenge.start_time && luxon.DateTime.fromSeconds(challenge.start_time).toLocal() <= luxon.DateTime.local() && !challenge.challenger_locked) &&
                                        <div className="challenge_button lock">lock in<img src="/images/v2/icons/unlocked.png"/></div>
                                    }
                                    {(challenge.start_time == null) &&
                                        <div className="challenge_button cancel" onClick={() => CancelChallenge()}>cancel</div>
                                    }
                                    {(challenge.challenger_locked) &&
                                        <div className="challenge_button locked">locked in<img src="/images/v2/icons/locked.png" /></div>
                                    }
                                </div>
                            </div>
                        ))}
                    {challengeDataState && challengeDataState
                        .filter(challenge => challenge.challenger_id !== playerSeatState.user_id)
                        .map((challenge, index) => (
                            <div key={challenge.request_id} className="challenge_item">
                                <div className="challenge_avatar_wrapper">
                                    <img className="challenge_avatar" src={challenge.challenger_avatar} />
                                </div>
                                <div className="challenge_details">
                                    <div className="challenge_header">
                                        CHALLENGER {index + 1}
                                    </div>
                                    <div>Challenger: <a href={"/?id=6&user=" + challenge.challenger_name}>{challenge.challenger_name}</a></div>
                                    <div>Time: {challenge.start_time
                                            ? (luxon.DateTime.fromSeconds(challenge.start_time).toLocal() <= luxon.DateTime.local()
                                                ? <span className="challenge_time_now">NOW</span>
                                                : luxon.DateTime.fromSeconds(challenge.start_time).toLocal().toFormat("LLL d, h:mm a"))
                                                : <span className="challenge_time_pending">PENDING</span>}
                                    </div>
                                    {(challenge.start_time && luxon.DateTime.fromSeconds(challenge.start_time).toLocal() >= luxon.DateTime.local() && !challenge.seat_holder_locked) &&
                                        <div className="challenge_button lock disabled">lock in<img src="/images/v2/icons/unlocked.png" /></div>
                                    }
                                    {(challenge.start_time && luxon.DateTime.fromSeconds(challenge.start_time).toLocal() <= luxon.DateTime.local() && !challenge.seat_holder_locked) &&
                                        <div className="challenge_button lock">lock in<img src="/images/v2/icons/unlocked.png" /></div>
                                    }
                                    {(challenge.start_time == null) &&
                                        <div className="challenge_button schedule" onClick={() => AcceptChallenge(challenge)}>schedule</div>
                                    }
                                    {(challenge.seat_holder_locked) &&
                                        <div className="challenge_button locked">locked in<img src="/images/v2/icons/locked.png" /></div>
                                    }
                                </div>
                            </div>
                        ))}
                </div>
                <svg style={{ marginTop: "45px" }} width="100%" height="1"><line x1="0%" y1="1" x2="100%" y2="1" stroke="#77694e" strokeWidth="1"></line></svg>
            </div>
        }
        </>
    );
}

window.Village = Village;