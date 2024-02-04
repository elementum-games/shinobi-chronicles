import { apiFetch } from "../utils/network.js";
import { useModal } from '../utils/modalContext.js';
export function VillageHQ({
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
    const { openModal } = useModal();
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
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
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
                                    <div className="kage_resign_button"
                                        onClick={
                                            () => openModal({
                                                header: 'Confirmation',
                                                text: 'Are you sure you wish to resign from your current position?',
                                                ContentComponent: null,
                                                onConfirm: () => Resign(),
                                            })
                                        }
                                    >resign</div>
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

export const ChallengeContainer = ({ playerID, challengeDataState, CancelChallenge, AcceptChallenge, LockChallenge }) => {
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

export const TimeGrid = ({ setSelectedTimesUTC, startHourUTC = 0 }) => {
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

export const TimeGridResponse = ({ availableTimesUTC, setSelectedTimeUTC, startHourUTC = 0 }) => {
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