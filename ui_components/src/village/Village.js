import { apiFetch } from "../utils/network.js";

function Village({
    playerID,
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
    playerWarLogData,
    warRecordData,
    kageRecords,
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
                data.resources = [];
                data.penalties = [];
                data.glowClass = "";
                break;
            case 1:
                data.banner = "/images/v2/decorations/policy_banners/growthpolicy.jpg";
                data.name = "From the Ashes";
                data.phrase = "bonds forged, courage shared.";
                data.description = "In unity, find the strength to overcome.\nOne village, one heart, one fight.";
                data.bonuses = ["25% increased Caravan speed", "15% increased Construction speed", "15% increased Research speed", "50% reduced cost for village transfers"];
                data.resources = ["+70 Materials production / hour", "+100 Food production / hour", "+40 Wealth production / hour"];
                data.penalties = ["Cannot declare War"];
                data.glowClass = "growth_glow";
                break;
            case 2:
                data.banner = "/images/v2/decorations/policy_banners/espionagepolicy.jpg";
                data.name = "Eye of the Storm";
                data.phrase = "half truths, all lies.";
                data.description = "Become informants dealing in truths and lies.\nDeceive, divide and destroy.";
                data.bonuses = ["25% increased Infiltrate speed", "+1 Defense/Stability reduction from Infiltrate", "+1 Stability reduction from Infiltrate", "+1 Stealth"];
                data.resources = ["+70 Materials production / hour", "+40 Food production / hour", "+100 Wealth production / hour"];
                data.penalties = [];
                data.glowClass = "espionage_glow";
                break;
            case 3:
                data.banner = "/images/v2/decorations/policy_banners/defensepolicy.jpg";
                data.name = "Fortress of Solitude";
                data.phrase = "vigilant minds, enduring hearts.";
                data.description = "Show the might of will unyielding.\nPrepare, preserve, prevail.";
                data.bonuses = ["25% increased Reinforce speed", "+1 Defense gain from Reinforce", "+1 Stability gain from Reinforce", "+1 Scouting"];
                data.resources = ["+100 Materials production / hour", "+70 Food production / hour", "+40 Wealth production / hour"];
                data.penalties = [];
                data.glowClass = "defense_glow";
                break;
            case 4:
                data.banner = "/images/v2/decorations/policy_banners/warpolicy.jpg";
                data.name = "Forged in Flames";
                data.phrase = "blades sharp, minds sharper.";
                data.description = "Lead your village on the path of a warmonger.\nFeel no fear, no hesitation, no doubt.";
                data.bonuses = ["25% increased Raid speed", "+1 Defense reduction from Raid", "+1 Stability reduction from Raid", "+1 Village Point from PvP"];
                data.resources = ["+70 Materials production / hour", "+70 Food production / hour", "+70 Wealth production / hour"];
                data.penalties = ["Cannot form Alliances"];
                data.glowClass = "war_glow";
                break;
            case 5:
                data.banner = "/images/v2/decorations/policy_banners/prosperitypolicy.jpg";
                data.name = "The Gilded Hand";
                data.phrase = "golden touch, boundless reach.";
                data.description = "In the art of war, wealth is our canvas.\nBuild empires, foster riches, command respect.";
                data.bonuses = ["25% reduced upkeep cost from Upgrades", "+25 baseline Stability", "+25 maximum Stability", "+25% increased income from PvE"];
                data.resources = ["+40 Materials production / hour", "+70 Food production / hour", "+100 Wealth production / hour"];
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
                <div className="nav_button" onClick={() => setVillageTab("warTable")}>war table</div>
                <div className="nav_button disabled">members & teams</div>
                <div className={playerSeatState.seat_id != null ? "nav_button" : "nav_button disabled"} onClick={() => setVillageTab("kageQuarters")}>kage's quarters</div>
            </div>
            {villageTab == "villageHQ" &&
                <VillageHQ
                playerID={playerID}
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
                kageRecords={kageRecords}
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
                playerID={playerID}
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
            {villageTab == "warTable" &&
                <WarTable
                playerWarLogData={playerWarLogData}
                warRecordData={warRecordData}
                strategicDataState={strategicDataState}
                villageAPI={villageAPI}
                handleErrors={handleErrors}
                getVillageIcon={getVillageIcon}
                getPolicyDisplayData={getPolicyDisplayData}
                />
            }
        </>
    );
}

function VillageHQ({
    playerID,
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
    kageRecords,
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
    const LockChallenge = (target_challenge) => {
        if (modalState == "confirm_lock_challenge") {
            apiFetch(
                villageAPI,
                {
                    request: 'LockChallenge',
                    challenge_id: challengeTarget.request_id,
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
            setChallengeTarget(target_challenge);
            setModalHeader("Confirmation");
            setModalState("confirm_lock_challenge");
            setModalText("Are you sure you want to lock in?\nYour actions will be restricted until the battle begins.");
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
                        {modalState == "confirm_lock_challenge" &&
                            <>
                                <div className="modal_confirm_button" onClick={() => LockChallenge()}>confirm</div>
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
                playerID={playerID}
                challengeDataState={challengeDataState}
                CancelChallenge={CancelChallenge}
                AcceptChallenge={AcceptChallenge}
                LockChallenge={LockChallenge}
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
                                <div className="kage_title">
                                    {kage.is_provisional ? kage.seat_title + ": " + kage.provisional_days_label : kage.seat_title + " of " + villageName}
                                </div>
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
                                            {(elder.seat_id && playerSeatState.seat_id == null) &&
                                                <div className="elder_challenge_button" onClick={() => Challenge(elder)}>challenge</div>
                                            }
                                            {(elder.seat_id && playerSeatState.seat_id !== null && playerSeatState.seat_id != elder.seat_id) &&
                                                <div className="elder_challenge_button disabled">challenge</div>
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
                                    <div className="points_total">{pointsDataState.monthly_points}</div>
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
                                {policyDisplay.resources.map((resource, index) => (
                                    <div key={index} className="policy_resource_item">
                                        <svg width="16" height="16" viewBox="0 0 100 100">
                                            <polygon points="25,20 50,45 25,70 0,45" fill="#414b8c" />
                                            <polygon points="25,0 50,25 25,50 0,25" fill="#5964a6" />
                                        </svg>
                                        <div className="policy_resource_text">{resource}</div>
                                    </div>
                                ))}
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
                <div className="row fourth">
                    <div className="column first">
                        <div className="hq_navigation_row">
                            <div className="header">Kage Record</div>
                            <div className="kage_record_container">
                                <div className="kage_record_header_section">
                                    <div className="kage_record_header">
                                        <span>Whispers of the past</span>
                                        <span>Echoes of a leader's might</span>
                                        <span>Legacy in stone.</span>
                                    </div>
                                </div>
                                <div className="kage_record_main_section">
                                    {kageRecords
                                        .map((kage, index) => (
                                            <div key={kage.user_id} className="kage_record">
                                                <div className="kage_record_item_row_first">
                                                    <div className="kage_record_item_title">{kage.seat_title}</div>: <div className="kage_record_item_name"><a href={"/?id=6&user=" + kage.user_name}>{kage.user_name}</a></div>
                                                </div>
                                                <div className="kage_record_item_row_second">
                                                    <div className="kage_record_item_start">{kage.seat_start}</div> - <div className="kage_record_item_length">{kage.time_held}</div>
                                                </div>
                                            </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

function KageQuarters({
    playerID,
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
    const [offeredResources, setOfferedResources] = React.useState([
        { resource_id: 1, resource_name: "materials", count: 0 },
        { resource_id: 2, resource_name: "food", count: 0 },
        { resource_id: 3, resource_name: "wealth", count: 0 }
    ]);
    const [offeredRegions, setOfferedRegions] = React.useState([]);
    const [requestedResources, setRequestedResources] = React.useState([
        { resource_id: 1, resource_name: "materials", count: 0 },
        { resource_id: 2, resource_name: "food", count: 0 },
        { resource_id: 3, resource_name: "wealth", count: 0 }
    ]);
    const [requestedRegions, setRequestedRegions] = React.useState([]);
    const [modalState, setModalState] = React.useState("closed");
    const [modalHeader, setModalHeader] = React.useState(null);
    const [modalText, setModalText] = React.useState(null);
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
                setModalHeader("Confirmation");
                setModalText(response.data.response_message);
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_policy");
            setModalHeader("Confirmation");
            setModalText("Are you sure you want to change policies? You will be unable to select a new policy for 3 days.");
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
                setModalText(response.data.response_message);
                setModalHeader("Confirmation");
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_declare_war");
            setModalText("Are you sure you declare war with " + strategicDisplayRight.village.name + "?");
            setModalHeader("Confirmation");
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
                setModalText(response.data.response_message);
                setModalHeader("Confirmation");
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_offer_peace");
            setModalText("Are you sure you want to offer peace with " + strategicDisplayRight.village.name + "?");
            setModalHeader("Confirmation");
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
                setModalText(response.data.response_message);
                setModalHeader("Confirmation");
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_form_alliance");
            setModalText("Are you sure you want to form an alliance with " + strategicDisplayRight.village.name + "?\nYou can be a member of only one Alliance at any given time.");
            setModalHeader("Confirmation");
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
                setModalText(response.data.response_message);
                setModalHeader("Confirmation");
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_break_alliance");
            setModalText("Are you sure you want break an alliance with " + strategicDisplayRight.village.name + "?");
            setModalHeader("Confirmation");
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
                setModalText(response.data.response_message);
                setModalHeader("Confirmation");
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_cancel_proposal");
            setModalText("Are you sure you want to cancel this proposal?");
            setModalHeader("Confirmation");
        }
    }
    const OfferTrade = () => {
        if (modalState == "confirm_offer_trade") {
            apiFetch(
                villageAPI,
                {
                    request: 'CreateProposal',
                    type: 'offer_trade',
                    target_village_id: strategicDisplayRight.village.village_id,
                    offered_resources: offeredResources,
                    offered_regions: offeredRegions,
                    requested_resources: requestedResources,
                    requested_regions: requestedRegions
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setProposalDataState(response.data.proposalData);
                setModalText(response.data.response_message);
                setModalHeader("Confirmation");
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_offer_trade");
            setModalText(null);
            setModalHeader("Trade resources and regions");
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
                setModalText(response.data.response_message);
                setModalHeader("Confirmation");
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_enact_proposal");
            setModalText("Are you sure you want to enact this proposal?");
            setModalHeader("Confirmation");
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
                setModalText(response.data.response_message);
                setModalHeader("Confirmation");
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_boost_vote");
            setModalText("When a vote Against is boosted:\n The Kage will lose 500 Reputation when the proposal is enacted.\n\nWhen a vote In Favor is boosted:\nTotal Reputation loss from Against votes will be reduced by 500.\n\nBoosting a vote will cost 500 Reputation when the proposal is passed.\n\nHowever, a boosted vote In Favor will only cost Reputation if there is a boosted vote Against. If there are more boosted votes In Favor than Against, the cost will be split between between votes In Favor.");
            setModalHeader("Confirmation");
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
                setModalText(response.data.response_message);
                setModalHeader("Confirmation");
                setModalState("response_message");
            });
        }
        else {
            setModalState("confirm_cancel_vote");
            setModalText("Are you sure you wish to cancel your vote for this proposal?");
            setModalHeader("Confirmation");
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
    const ViewTrade = () => {
        setModalState("view_trade");
        setModalText(null);
        setModalHeader("View trade offer");
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
                        <div className="modal_header">{modalHeader}</div>
                        <div className="modal_text">{modalText}</div>
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
                        {modalState == "confirm_offer_trade" &&
                        <>
                                <div class="schedule_challenge_subtext_wrapper" style={{ marginBottom: "20px", marginTop: "-10px" }}>
                                    <span class="schedule_challenge_subtext">Each village can offer up to 25000 resources of each resource type per trade.</span>
                                    <span class="schedule_challenge_subtext">Trades have a cooldown of 24 hours.</span>
                                </div>
                                {TradeDisplay({
                                    viewOnly:false,
                                    offeringVillageResources:resourceDataState,
                                    offeringVillageRegions:strategicDisplayLeft.regions,
                                    offeredResources:offeredResources,
                                    setOfferedResources:setOfferedResources,
                                    offeredRegions:offeredRegions,
                                    setOfferedRegions:setOfferedRegions,
                                    targetVillageResources:null,
                                    targetVillageRegions:strategicDisplayRight.regions,
                                    requestedResources:requestedResources,
                                    setRequestedResources:setRequestedResources,
                                    requestedRegions:requestedRegions,
                                    setRequestedRegions:setRequestedRegions,
                                })}
                                <div className="modal_confirm_button" onClick={() => OfferTrade()}>confirm</div>
                                <div className="modal_cancel_button" onClick={() => setModalState("closed")}>cancel</div>
                            </>
                        }
                        {modalState == "view_trade" &&
                            <>
                                <TradeDisplay
                                    viewOnly={true}
                                    offeringVillageResources={null}
                                    offeringVillageRegions={null}
                            offeredResources={currentProposal.trade_data.offered_resources}
                                    setOfferedResources={null}
                            offeredRegions={currentProposal.trade_data.offered_regions}
                                    setOfferedRegions={null}
                                    targetVillageResources={null}
                                    targetVillageRegions={null}
                            requestedResources={currentProposal.trade_data.requested_resources}
                                    setRequestedResources={null}
                            requestedRegions={currentProposal.trade_data.requested_regions}
                                    setRequestedRegions={null}
                                />
                                <div className="modal_cancel_button" onClick={() => setModalState("closed")}>close</div>
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
                                        {(currentProposal && (currentProposal.type == "offer_trade" || currentProposal.type == "accept_trade")) &&
                                            <div className="trade_view_button_wrapper alliance" onClick={() => ViewTrade()}>
                                                <div className="trade_view_button_inner">
                                                    <img src="/images/v2/icons/trade.png" className="trade_view_button_icon" />
                                                </div>
                                            </div>
                                        }
                                        <div className="proposal_enact_button_wrapper">
                                            <div className={(currentProposal && (currentProposal.enact_time_remaining !== null ||
                                                currentProposal.votes.length == seatDataState.filter(seat => seat.seat_type == "elder" && seat.seat_id != null).length
                                            )) ? "proposal_enact_button" : "proposal_enact_button disabled"} onClick={() => EnactProposal()}>enact proposal</div>
                                            {/*proposalRepAdjustment > 0 &&
                                                <div className="rep_change positive">REPUATION GAIN: +{proposalRepAdjustment}</div>
                                            */}
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
                                        {(currentProposal && currentProposal.vote_time_remaining != null && !currentProposal.votes.find(vote => vote.user_id == playerID)) &&
                                            <>
                                                <div className="proposal_yes_button_wrapper">
                                                    <div className="proposal_yes_button" onClick={() => SubmitVote(1)}>vote in favor</div>
                                                </div>
                                                {(currentProposal && (currentProposal.type == "offer_trade" || currentProposal.type == "accept_trade")) &&
                                                    <div className="trade_view_button_wrapper alliance" onClick={() => ViewTrade()}>
                                                        <div className="trade_view_button_inner">
                                                            <img src="/images/v2/icons/trade.png" className="trade_view_button_icon" />
                                                        </div>
                                                    </div>
                                                }
                                                <div className="proposal_no_button_wrapper">
                                                    <div className="proposal_no_button" onClick={() => SubmitVote(0)}>vote against</div>
                                                </div>
                                            </>
                                        }
                                        {(currentProposal && currentProposal.vote_time_remaining == null && !currentProposal.votes.find(vote => vote.user_id == playerID)) &&
                                            <>
                                                <div className="proposal_yes_button_wrapper">
                                                    <div className="proposal_yes_button disabled">vote in favor</div>
                                                </div>
                                                {(currentProposal && (currentProposal.type == "offer_trade" || currentProposal.type == "accept_trade")) &&
                                                    <div className="trade_view_button_wrapper alliance" onClick={() => ViewTrade()}>
                                                        <div className="trade_view_button_inner">
                                                            <img src="/images/v2/icons/trade.png" className="trade_view_button_icon" />
                                                        </div>
                                                    </div>
                                                }
                                                <div className="proposal_no_button_wrapper">
                                                    <div className="proposal_no_button disabled">vote against</div>
                                                </div>
                                            </>
                                        }
                                        {(currentProposal && currentProposal.vote_time_remaining != null && currentProposal.votes.find(vote => vote.user_id == playerID)) &&
                                            <>
                                                <div className="proposal_cancel_vote_button_wrapper">
                                                <div className="proposal_cancel_vote_button" onClick={() => CancelVote()}>change vote</div>
                                                </div>
                                                {(currentProposal && (currentProposal.type == "offer_trade" || currentProposal.type == "accept_trade")) &&
                                                    <div className="trade_view_button_wrapper alliance" onClick={() => ViewTrade()}>
                                                        <div className="trade_view_button_inner">
                                                            <img src="/images/v2/icons/trade.png" className="trade_view_button_icon" />
                                                        </div>
                                                    </div>
                                                }
                                                {(currentProposal.votes.find(vote => vote.user_id == playerID).rep_adjustment == 0) &&
                                                    <div className="proposal_boost_vote_button_wrapper">
                                                        <div className="proposal_boost_vote_button" onClick={() => BoostVote()}>boost vote</div>
                                                    </div>
                                                }
                                            </>
                                        }
                                        {(currentProposal && currentProposal.vote_time_remaining == null && currentProposal.votes.find(vote => vote.user_id == playerID)) &&
                                            <>
                                                <div className="proposal_cancel_vote_button_wrapper">
                                                    <div className="proposal_cancel_vote_button disabled">cancel vote</div>
                                                </div>
                                                {(currentProposal && (currentProposal.type == "offer_trade" || currentProposal.type == "accept_trade")) &&
                                                    <div className="trade_view_button_wrapper alliance" onClick={() => ViewTrade()}>
                                                        <div className="trade_view_button_inner">
                                                            <img src="/images/v2/icons/trade.png" className="trade_view_button_icon" />
                                                        </div>
                                                    </div>
                                                }
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
                                    {displayPolicyID < 5 &&
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
                                {policyDisplay.resources.map((resource, index) => (
                                    <div key={index} className="policy_resource_item">
                                        <svg width="16" height="16" viewBox="0 0 100 100">
                                            <polygon points="25,20 50,45 25,70 0,45" fill="#414b8c" />
                                            <polygon points="25,0 50,25 25,50 0,25" fill="#5964a6" />
                                        </svg>
                                        <div className="policy_resource_text">{resource}</div>
                                    </div>
                                ))}
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
                                <div className="strategic_info_navigation_diplomacy_buttons">
                                    {strategicDisplayLeft.allies.find(ally => ally == strategicDisplayRight.village.name) &&
                                        <div className="diplomacy_action_button_wrapper alliance" onClick={() => OfferTrade()}>
                                            <div className="diplomacy_action_button_inner">
                                                <img src="/images/v2/icons/trade.png" className="diplomacy_action_button_icon" />
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

    function TradeDisplay({
        viewOnly,
        offeringVillageResources,
        offeringVillageRegions,
        offeredResources,
        setOfferedResources,
        offeredRegions,
        setOfferedRegions,
        targetVillageResources,
        targetVillageRegions,
        requestedResources,
        setRequestedResources,
        requestedRegions,
        setRequestedRegions,
    }) {
        const toggleOfferedRegion = (regionId) => {
            setOfferedRegions(current => {
                // Check if the region is already selected
                if (current.includes(regionId)) {
                    // If it is, filter it out (unselect it)
                    return current.filter(id => id !== regionId);
                } else {
                    // Otherwise, add it to the selected regions
                    return [...current, regionId];
                }
            });
        };
        const toggleRequestedRegion = (regionId) => {
            setRequestedRegions(current => {
                // Check if the region is already selected
                if (current.includes(regionId)) {
                    // If it is, filter it out (unselect it)
                    return current.filter(id => id !== regionId);
                } else {
                    // Otherwise, add it to the selected regions
                    return [...current, regionId];
                }
            });
        };
        const handleOfferedResourcesChange = (resourceName, value) => {
            setOfferedResources(currentResources =>
                currentResources.map(resource =>
                    resource.resource_name === resourceName
                        ? { ...resource, count: value }
                        : resource
                )
            );
        };
        const handleRequestedResourcesChange = (resourceName, value) => {
            setRequestedResources(currentResources =>
                currentResources.map(resource =>
                    resource.resource_name === resourceName
                        ? { ...resource, count: value }
                        : resource
                )
            );
        };

        return (
            viewOnly ?
                <div className="trade_display_container">
                    <div className="trade_display_offer_container">
                        <div className="header">Offered Resources</div>
                        <div className="trade_display_resources">
                            {offeredResources
                                .map((resource, index) => {
                                    const total = offeringVillageResources ? offeringVillageResources.find(total => total.resource_id === resource.resource_id).count : null;
                                    return (
                                        <div key={resource.resource_id} className="trade_display_resource_wrapper">
                                            <input
                                                type="text"
                                                min="0"
                                                max={total ? total : 25000}
                                                step="100"
                                                placeholder="0"
                                                className="trade_display_resource_input"
                                                value={resource.count}
                                                style={{ userSelect: "none" }}
                                                readOnly
                                            />
                                            <div className="trade_display_resource_name">{resource.resource_name}</div>
                                            {total ?
                                                <div className="trade_display_resource_total">{total}</div>
                                                :
                                                <div className="trade_display_resource_total">???</div>
                                            }
                                        </div>
                                    );
                                })}
                        </div>
                        <div className="header">Offered Regions</div>
                        <div className="trade_display_regions">
                            {offeredRegions
                                .filter(region => region.region_id > 5)
                                .map((region, index) => (
                                    <div key={region.name} className="trade_display_region_wrapper">
                                        <div className="trade_display_region_name">{region.name}</div>
                                    </div>
                                ))}
                        </div>
                    </div>
                    <div className="trade_display_request_container">
                        <div className="header">Requested Resources</div>
                        <div className="trade_display_resources">
                            {requestedResources
                                .map((resource, index) => {
                                    const total = targetVillageResources ? targetVillageResources.find(total => total.resource_id === resource.resource_id).count : null;
                                    return (
                                        <div key={resource.resource_id} className="trade_display_resource_wrapper">
                                            <input
                                                type="text"
                                                min="0"
                                                max={total ? total : 25000}
                                                step="100"
                                                placeholder="0"
                                                className="trade_display_resource_input"
                                                value={resource.count}
                                                onChange={(e) => handleRequestedResourcesChange(resource.resource_name, parseInt(e.target.value))}
                                                style={{ userSelect: "none" }}
                                                readOnly
                                            />
                                            <div className="trade_display_resource_name">{resource.resource_name}</div>
                                            {total ?
                                                <div className="trade_display_resource_total">{total}</div>
                                                :
                                                <div className="trade_display_resource_total">???</div>
                                            }
                                        </div>
                                    );
                                })}
                        </div>
                        <div className="header">Requested Regions</div>
                        <div className="trade_display_regions">
                            {requestedRegions
                                .filter(region => region.region_id > 5)
                                .map((region, index) => (
                                    <div key={region.name} className="trade_display_region_wrapper">
                                        <div className="trade_display_region_name">{region.name}</div>
                                    </div>
                                ))}
                        </div>
                    </div>
                </div>
            :
                <div className="trade_display_container">
                    <div className="trade_display_offer_container">
                        <div className="header">Offer Resources</div>
                        <div className="trade_display_resources">
                            {offeredResources
                                .map((resource, index) => {
                                    const total = offeringVillageResources ? offeringVillageResources.find(total => total.resource_id === resource.resource_id).count : null;
                                    return (
                                        <div key={resource.resource_id} className="trade_display_resource_wrapper">
                                            <input
                                                type="number"
                                                min="0"
                                                max={total ? total : 25000}
                                                step="100"
                                                placeholder="0"
                                                className="trade_display_resource_input"
                                                value={resource.count}
                                                onChange={(e) => handleOfferedResourcesChange(resource.resource_name, parseInt(e.target.value))}
                                            />
                                            <div className="trade_display_resource_name">{resource.resource_name}</div>
                                            {total ?
                                                <div className="trade_display_resource_total">{total}</div>
                                                :
                                                <div className="trade_display_resource_total">???</div>
                                            }
                                        </div>
                                    );
                                })}
                        </div>
                        <div className="header">Offer Regions</div>
                        <div className="trade_display_regions">
                            {offeringVillageRegions
                                .filter(region => region.region_id > 5)
                                .map((region, index) => (
                                    <div key={region.name} className="trade_display_region_wrapper">
                                        <div className="trade_display_region_name">{region.name}</div>
                                        <input
                                            type="checkbox"
                                            checked={offeredRegions.includes(region.region_id)}
                                            onChange={() => toggleOfferedRegion(region.region_id)}
                                        />
                                    </div>
                                ))}
                        </div>
                    </div>
                    <div className="trade_display_request_container">
                        <div className="header">Request Resources</div>
                        <div className="trade_display_resources">
                            {requestedResources
                                .map((resource, index) => {
                                    const total = targetVillageResources ? targetVillageResources.find(total => total.resource_id === resource.resource_id).count : null;
                                    return (
                                        <div key={resource.resource_id} className="trade_display_resource_wrapper">
                                            <input
                                                type="number"
                                                min="0"
                                                max={total ? total : 25000}
                                                step="100"
                                                placeholder="0"
                                                className="trade_display_resource_input"
                                                value={resource.count}
                                                onChange={(e) => handleRequestedResourcesChange(resource.resource_name, parseInt(e.target.value))}
                                            />
                                            <div className="trade_display_resource_name">{resource.resource_name}</div>
                                            {total ?
                                                <div className="trade_display_resource_total">{total}</div>
                                                :
                                                <div className="trade_display_resource_total">???</div>
                                            }
                                        </div>
                                    );
                                })}
                        </div>
                        <div className="header">Request Regions</div>
                        <div className="trade_display_regions">
                            {targetVillageRegions
                                .filter(region => region.region_id > 5)
                                .map((region, index) => (
                                    <div key={region.name} className="trade_display_region_wrapper">
                                        <div className="trade_display_region_name">{region.name}</div>
                                        <input
                                            type="checkbox"
                                            checked={requestedRegions.includes(region.region_id)}
                                            onChange={() => toggleRequestedRegion(region.region_id)}
                                        />
                                    </div>
                                ))}
                        </div>
                    </div>
                </div>
        );
    }

    function cyclePolicy(direction) {
        var newPolicyID;
        switch (direction) {
            case "increment":
                newPolicyID = Math.min(5, displayPolicyID + 1);
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

function WarTable({
    warLogData,
    villageAPI,
    handleErrors,
    getVillageIcon
}) {
    const [playerWarLog, setPlayerWarLog] = React.useState(warLogData.player_war_log);
    const [globalLeaderboardWarLogs, setGlobalLeaderboardWarLogs] = React.useState(warLogData.global_leaderboard_war_logs);
    const [globalLeaderboardPageNumber, setGlobalLeaderboardPageNumber] = React.useState(1);

    function WarLogHeader() {
        return (
            <div className="warlog_label_row">
                <div className="warlog_username_label"></div>
                <div className="warlog_war_score_label">war score</div>
                <div className="warlog_pvp_wins_label">pvp wins</div>
                <div className="warlog_raid_label">raid</div>
                <div className="warlog_reinforce_label">reinforce</div>
                <div className="warlog_infiltrate_label">infiltrate</div>
                <div className="warlog_defense_label">def</div>
                <div className="warlog_captures_label">captures</div>
                <div className="warlog_patrols_label">patrols</div>
                <div className="warlog_resources_label">resources</div>
                <div className="warlog_chart_label"></div>
            </div>
        );
    }
    function WarLog({ log, index, animate, getVillageIcon }) {
        const scoreData = [
            { name: 'Objective Score', score: log.objective_score },
            { name: 'Resource Score', score: log.resource_score },
            { name: 'Battle Score', score: log.battle_score }
        ];
        const chart_colors = ['#2b5fca', '#5fca8c', '#d64866'];
        return (
            <div key={index} className="warlog_item">
                <div className="warlog_data_row">
                    {log.rank == 1 && 
                        <span className="warlog_rank_wrapper"><span className="warlog_rank first">{log.rank}</span></span>
                    }
                    {log.rank == 2 &&
                        <span className="warlog_rank_wrapper"><span className="warlog_rank second">{log.rank}</span></span>
                    }
                    {log.rank == 3 &&
                        <span className="warlog_rank_wrapper"><span className="warlog_rank third">{log.rank}</span></span>
                    }
                    {log.rank > 3 &&
                        <span className="warlog_rank_wrapper"><span className="warlog_rank">{log.rank}</span></span>
                    }
                    <div className="warlog_username">
                        <span><img src={getVillageIcon(log.village_id)} /></span>
                        <a href={"/?id=6&user=" + log.user_name}>{log.user_name}</a>
                    </div>
                    <div className="warlog_war_score">{log.war_score}</div>
                    <div className="warlog_pvp_wins">{log.pvp_wins}</div>
                    <div className="warlog_raid">
                        <span>{log.raid_count}</span>
                        <span className="warlog_red">({log.damage_dealt})</span>
                    </div>
                    <div className="warlog_reinforce">
                        <span>{log.reinforce_count}</span>
                        <span className="warlog_green">({log.damage_healed})</span>
                    </div>
                    <div className="warlog_infiltrate">{log.infiltrate_count}</div>
                    <div className="warlog_defense">
                        <span className="warlog_green">+{log.defense_gained}</span>
                        <span className="warlog_red">-{log.defense_reduced}</span>
                    </div>
                    <div className="warlog_captures">{log.villages_captured + log.regions_captured}</div>
                    <div className="warlog_patrols">{log.patrols_defeated}</div>
                    <div className="warlog_resources">{log.resources_stolen}</div>
                    <div className="warlog_chart">
                        <Recharts.PieChart width={50} height={50}>
                            <Recharts.Pie isAnimationActive={animate} stroke="none" data={scoreData} dataKey="score" outerRadius={16} fill="green">
                                {scoreData.map((entry, index) => (
                                    <Recharts.Cell key={`cell-${index}`} fill={chart_colors[index % chart_colors.length]} />
                                ))}
                            </Recharts.Pie>
                        </Recharts.PieChart>
                        <div className="warlog_chart_tooltip">
                            <div className="warlog_chart_tooltip_row">
                                <svg width="12" height="12">
                                    <rect width="100" height="100" fill="#2b5fca" />
                                </svg>
                                <div>Objective score ({Math.round((log.objective_score / Math.max(log.war_score, 1)) * 100)}%)</div>
                            </div>
                            <div className="warlog_chart_tooltip_row">
                                <svg width="12" height="12">
                                    <rect width="100" height="100" fill="#5fca8c" />
                                </svg>
                                <div>Resource score ({Math.round((log.resource_score / Math.max(log.war_score, 1)) * 100)}%)</div>
                            </div>
                            <div className="warlog_chart_tooltip_row">
                                <svg width="12" height="12">
                                    <rect width="100" height="100" fill="#d64866" />
                                </svg>
                                <div>Battle score ({Math.round((log.battle_score / Math.max(log.war_score, 1)) * 100)}%)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    const GlobalLeaderboardNextPage = (page_number) => {
        apiFetch(
            villageAPI,
            {
                request: 'GetGlobalWarLeaderboard',
                page_number: page_number,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            if (response.data.warLogData.global_leaderboard_war_logs.length == 0) {
                return;
            } else { 
                setGlobalLeaderboardPageNumber(page_number);
                setGlobalLeaderboardWarLogs(response.data.warLogData.global_leaderboard_war_logs);
            }
        });
    }
    const GlobalLeaderboardPreviousPage = (page_number) => {
        if (page_number > 0) {
            apiFetch(
                villageAPI,
                {
                    request: 'GetGlobalWarLeaderboard',
                    page_number: page_number,
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setGlobalLeaderboardPageNumber(page_number);
                setGlobalLeaderboardWarLogs(response.data.warLogData.global_leaderboard_war_logs);
            });
        }
    }

    return (
        <div className="wartable_container">
            <div className="row first">
                <div className="column first">
                    <div className="header">your war score</div>
                    <div className="player_warlog_container">
                        <WarLogHeader />
                        <WarLog log={playerWarLog} index={0} animate={false} getVillageIcon={getVillageIcon} />
                    </div>
                </div>
            </div>
            <div className="row second">
                <div className="column first">
                    <div className="header">global war score</div>
                    <div className="global_leaderboard_container">
                        <div className="warlog_label_row">
                            <div className="warlog_username_label"></div>
                            <div className="warlog_war_score_label">war score</div>
                            <div className="warlog_pvp_wins_label">pvp wins</div>
                            <div className="warlog_raid_label">raid</div>
                            <div className="warlog_reinforce_label">reinforce</div>
                            <div className="warlog_infiltrate_label">infiltrate</div>
                            <div className="warlog_defense_label">def</div>
                            <div className="warlog_captures_label">captures</div>
                            <div className="warlog_patrols_label">patrols</div>
                            <div className="warlog_resources_label">resources</div>
                            <div className="warlog_chart_label"></div>
                        </div>
                        {globalLeaderboardWarLogs
                            .map((log, index) => (
                                <WarLog log={log} index={index} animate={true} getVillageIcon={getVillageIcon} />
                            ))}
                    </div>
                    <div className="global_leaderboard_navigation">
                        <div className="global_leaderboard_navigation_divider_left">
                            <svg width="100%" height="2"><line x1="0%" y1="1" x2="100%" y2="1" stroke="#4e4535" strokeWidth="1"></line></svg>
                        </div>
                        <div className="global_leaderboard_pagination_wrapper">
                            {globalLeaderboardPageNumber > 1 && <a className="global_leaderboard_pagination" onClick={() => GlobalLeaderboardPreviousPage(globalLeaderboardPageNumber - 1)}>{"<< Prev"}</a>}
                        </div>
                        <div className="global_leaderboard_navigation_divider_middle"><svg width="100%" height="2"><line x1="0%" y1="1" x2="100%" y2="1" stroke="#4e4535" strokeWidth="1"></line></svg></div>
                        <div className="global_leaderboard_pagination_wrapper">
                            <a className="global_leaderboard_pagination" onClick={() => GlobalLeaderboardNextPage(globalLeaderboardPageNumber + 1)}>{"Next >>"}</a>
                        </div>
                        <div className="global_leaderboard_navigation_divider_right"><svg width="100%" height="2"><line x1="0%" y1="1" x2="100%" y2="1" stroke="#4e4535" strokeWidth="1"></line></svg></div>
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
                return '/images/v2/decorations/strategic_banners/stratbannerstone.jpg';
            case 2:
                return '/images/v2/decorations/strategic_banners/stratbannercloud.jpg';
            case 3:
                return '/images/v2/decorations/strategic_banners/stratbannerleaf.jpg';
            case 4:
                return '/images/v2/decorations/strategic_banners/stratbannersand.jpg';
            case 5:
                return '/images/v2/decorations/strategic_banners/stratbannermist.jpg';
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

const ChallengeContainer = ({ playerID, challengeDataState, CancelChallenge, AcceptChallenge, LockChallenge }) => {
    return (
        <>
        {challengeDataState.length > 0 &&
            <div className="challenge_container">
                <div className="header">Challenges</div>
                <div className="challenge_list">
                    {challengeDataState && challengeDataState
                        .filter(challenge => challenge.challenger_id === playerID)
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
                                        <div className="challenge_button lock" onClick={() => LockChallenge(challenge)}>lock in<img src="/images/v2/icons/unlocked.png" /></div>
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
                        .filter(challenge => challenge.challenger_id !== playerID)
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
                                        <div className="challenge_button lock" onClick={() => LockChallenge(challenge)}>lock in<img src="/images/v2/icons/unlocked.png" /></div>
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