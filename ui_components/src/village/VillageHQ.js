// @flow

import { apiFetch } from "../utils/network.js";
import { useModal } from '../utils/modalContext.js';
import KageDisplay from "./KageDisplay.js";
import { getVillageIcon } from "./villageUtils.js";
import type { VillageSeatType } from "./villageSchema.js";
import VillagePolicy from "./VillagePolicy.js";

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
}) {
    const [resourceDaysToShow, setResourceDaysToShow] = React.useState(1);
    const selectedTimesUTC = React.useRef([]);
    const selectedTimeUTC = React.useRef(null);
    const challengeTarget = React.useRef(null);
    const { openModal } = useModal();

    const [challengeSubmittedFlag, setChallengeSubmittedFlag] = React.useState(false);
    const [challengeAcceptedFlag, setChallengeAcceptedFlag] = React.useState(false);

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
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
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
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }
    const Challenge = (target_seat) => {
        challengeTarget.current = target_seat;
        openModal({
            header: 'Submit Challenge',
            text: "Select times below that you are available to battle.",
            ContentComponent: TimeGrid,
            componentProps: ({
                selectedTimesUTC: selectedTimesUTC,
                startHourUTC: luxon.DateTime.fromObject({ hour: 0, zone: luxon.Settings.defaultZoneName }).toUTC().hour
            }),
            onConfirm: () => setChallengeSubmittedFlag(true),
        });
    }
    const ConfirmSubmitChallenge = () => {
        setChallengeSubmittedFlag(false);
        if (selectedTimesUTC.length < 12) {
            openModal({
                header: 'Submit Challenge',
                text: "You must select at least 12 slots.",
                ContentComponent: TimeGrid,
                componentProps: ({
                    selectedTimesUTC: selectedTimesUTC,
                    startHourUTC: luxon.DateTime.fromObject({ hour: 0, zone: luxon.Settings.defaultZoneName }).toUTC().hour
                }),
                onConfirm: () => setChallengeSubmittedFlag(true),
            });
        } else {
            apiFetch(
                villageAPI,
                {
                    request: 'SubmitChallenge',
                    seat_id: challengeTarget.current.seat_id,
                    selected_times: selectedTimesUTC.current,
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setChallengeDataState(response.data.challengeData);
                openModal({
                    header: 'Confirmation',
                    text: response.data.response_message,
                    ContentComponent: null,
                    onConfirm: null,
                });
            });
        }
    }
    const CancelChallenge = () => {
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
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }

    const AcceptChallenge = (target_challenge) => {
        challengeTarget.current = target_challenge;
        openModal({
            header: 'Accept Challenge',
            text: "Select a time slot below to accept the challenge.",
            ContentComponent: TimeGridResponse,
            componentProps: ({
                availableTimesUTC: JSON.parse(challengeTarget.current.selected_times),
                selectedTimeUTC: selectedTimeUTC,
                startHourUTC: luxon.DateTime.utc().minute === 0 ? luxon.DateTime.utc().hour + 12 : (luxon.DateTime.utc().hour + 13) % 24
            }),
            onConfirm: () => setChallengeAcceptedFlag(true),
        });
    }
    const ConfirmAcceptChallenge = () => {
        setChallengeAcceptedFlag(false);
        if (!selectedTimeUTC) {
            openModal({
                header: 'Accept Challenge',
                text: "Please verify that you have selected a time slot for the challenge.",
                ContentComponent: TimeGridResponse,
                componentProps: ({
                    availableTimesUTC: JSON.parse(challengeTarget.current.selected_times),
                    selectedTimeUTC: selectedTimeUTC,
                    startHourUTC: luxon.DateTime.utc().minute === 0 ? luxon.DateTime.utc().hour + 12 : (luxon.DateTime.utc().hour + 13) % 24
                }),
                onConfirm: () => setChallengeAcceptedFlag(true),
            });
        } else {
            apiFetch(
                villageAPI,
                {
                    request: 'AcceptChallenge',
                    challenge_id: challengeTarget.current.request_id,
                    time: selectedTimeUTC.current,
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setChallengeDataState(response.data.challengeData);
                openModal({
                    header: 'Confirmation',
                    text: response.data.response_message,
                    ContentComponent: null,
                    onConfirm: null,
                });
            });
        }
    }
    const LockChallenge = (target_challenge) => {
        apiFetch(
            villageAPI,
            {
                request: 'LockChallenge',
                challenge_id: target_challenge.request_id,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setChallengeDataState(response.data.challengeData);
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }

    React.useEffect(() => {
        if (challengeSubmittedFlag) {
            ConfirmSubmitChallenge();
        }
    }, [challengeSubmittedFlag]);
    React.useEffect(() => {
        if (challengeAcceptedFlag) {
            ConfirmAcceptChallenge();
        }
    }, [challengeAcceptedFlag]);

    const totalPopulation = populationData.reduce((acc, rank) => acc + rank.count, 0);
    const kage = seatDataState.find(seat => seat.seat_type === 'kage');

    return (
        <>
            <ChallengeContainer
                playerID={playerID}
                challengeDataState={challengeDataState}
                CancelChallenge={CancelChallenge}
                AcceptChallenge={AcceptChallenge}
                LockChallenge={LockChallenge}
                openModal={openModal}
            />
            <div className="hq_container">
                <div className="row first">
                    <div className="column first">
                        <VillageMembers
                            clans={clanData}
                            populationData={populationData}
                            totalPopulation={totalPopulation}
                        />
                    </div>
                    <div className="column second">
                        <KageDisplay
                            username={kage.user_name}
                            avatarLink={kage.avatar_link}
                            villageName={villageName}
                            seatTitle={kage.seat_title}
                            isProvisional={kage.is_provisional}
                            provisionalDaysLabel={kage.provisional_days_label}
                            seatId={kage.seat_id}
                            playerSeatId={playerSeatState.seat_id}
                            onResign={() => openModal({
                                header: 'Confirmation',
                                text: 'Are you sure you wish to resign from your current position?',
                                ContentComponent: null,
                                onConfirm: () => Resign(),
                            })}
                            onClaim={() => ClaimSeat("kage")}
                            onChallenge={() => Challenge(kage)}
                        />
                    </div>
                    <div className="column third">
                        <VillageElders
                            seatDataState={seatDataState}
                            playerSeatState={playerSeatState}
                            handleResign={Resign}
                            handleClaim={ClaimSeat}
                            handleChallenge={Challenge}
                        />
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
                        <VillagePolicy
                            policyDataState={policyDataState}
                            playerSeatState={playerSeatState}
                            displayPolicyID={policyDataState.policy_id}
                        />
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

function DisplayFromDays(days) {
    switch (days) {
        case 1:
            return 'daily';
        case 7:
            return 'weekly';
        case 30:
            return 'monthly';
        default:
            return days;
    }
}

function VillageMembers({ clans, populationData, totalPopulation }) {
    return <>
        <div className="clan_container">
            <div className="header">Clans</div>
            <div className="content box-primary">
                {clans.map((clan, index) => (
                    <div key={clan.clan_id} className="clan_item">
                        <div className="clan_item_header">{clan.name}</div>
                    </div>
                ))}
            </div>
        </div>
        <div className="population_container">
            <div className="header">Population</div>
            <div className="content box-primary">
                {populationData.map((rank, index) => (
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
    </>;
}

type VillageEldersProps = {|
    +seatDataState: $ReadOnlyArray<VillageSeatType>,
    +playerSeatState: VillageSeatType,
    +handleResign: () => void,
    +handleClaim: (string) => void,
    +handleChallenge: (string) => void,
|};
function VillageElders({
    seatDataState,
    playerSeatState,
    handleResign,
    handleClaim,
    handleChallenge
}: VillageEldersProps) {
    const { openModal } = useModal();

    return <div className="elders_container">
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
                        <div className="elder_name">
                            {elder.user_name ? <a href={`/?id=6&user=${elder.user_name}`}>{elder.user_name}</a> : "---"}
                        </div>
                        {(elder.seat_id && elder.seat_id === playerSeatState.seat_id) &&
                            <div
                                className="elder_resign_button"
                                onClick={() => openModal({
                                    header: 'Confirmation',
                                    text: 'Are you sure you wish to resign from your current position?',
                                    ContentComponent: null,
                                    onConfirm: handleResign,
                                })}
                            >resign</div>
                        }
                        {!elder.seat_id &&
                            <div
                                className={playerSeatState.seat_id
                                    ? "elder_claim_button disabled"
                                    : "elder_claim_button"
                                }
                                onClick={playerSeatState.seat_id ? null : () => handleClaim("elder")}
                            >claim</div>
                        }
                        {(elder.seat_id && playerSeatState.seat_id == null) &&
                            <div className="elder_challenge_button" onClick={() => handleChallenge(elder)}>challenge</div>
                        }
                        {(elder.seat_id && playerSeatState.seat_id !== null && playerSeatState.seat_id !== elder.seat_id) &&
                            <div className="elder_challenge_button disabled">challenge</div>
                        }
                    </div>
                ))}
        </div>
    </div>;
}


export const ChallengeContainer = ({
    playerID,
    challengeDataState,
    CancelChallenge,
    AcceptChallenge,
    LockChallenge,
    openModal
}) => {
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
                                            <div className="challenge_button lock" onClick={() => openModal({
                                                header: 'Confirmation',
                                                text: "Are you sure you want to lock in?\nYour actions will be restricted until the battle begins.",
                                                ContentComponent: null,
                                                onConfirm: () => LockChallenge(challenge),
                                            })}>lock in<img src="/images/v2/icons/unlocked.png" /></div>
                                        }
                                        {(challenge.start_time == null) &&
                                            <div className="challenge_button cancel" onClick={() => openModal({
                                                header: 'Confirmation',
                                                text: "Are you sure you wish to cancel your pending challenge request?",
                                                ContentComponent: null,
                                                onConfirm: () => CancelChallenge(),
                                            })}>cancel</div>
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
                                            <div className="challenge_button lock" onClick={() => openModal({
                                                header: 'Confirmation',
                                                text: "Are you sure you want to lock in?\nYour actions will be restricted until the battle begins.",
                                                ContentComponent: null,
                                                onConfirm: () => LockChallenge(challenge),
                                            })}>lock in<img src="/images/v2/icons/unlocked.png" /></div>
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

export const TimeGrid = ({ selectedTimesUTC, startHourUTC = 0 }) => {
    const [selectedTimes, setSelectedTimes] = React.useState([]);
    const timeSlots = generateSlotsForTimeZone(startHourUTC);

    function toggleSlot(time) {
        const formattedTime = time.toFormat('HH:mm');
        let newSelectedTimes;

        if (selectedTimes.includes(formattedTime)) {
            newSelectedTimes = selectedTimes.filter(t => t !== formattedTime);
        } else {
            newSelectedTimes = [...selectedTimes, formattedTime];
        }

        setSelectedTimes(newSelectedTimes);
        selectedTimesUTC.current = convertSlotsToUTC(newSelectedTimes);
    };

    return (
        <>
            <div className="schedule_challenge_subtext_wrapper">
                <span className="schedule_challenge_subtext">Time slots are displayed in your local time.</span>
                <span className="schedule_challenge_subtext">The seat holder will have 24 hours to choose one of your selected times.</span>
                <span className="schedule_challenge_subtext">Your battle will be scheduled a minimum of 12 hours from the time of their selection.</span>
            </div>
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

function generateSlotsForTimeZone(startHour) {
    return Array.from(
        { length: 24 },
        (slot, index) => (index + startHour) % 24
    ).map(hour =>
        luxon.DateTime.fromObject({ hour }, { zone: 'utc' }).setZone('local')
    );
}
function convertSlotsToUTC(times) {
    return times.map(time =>
        luxon.DateTime.fromFormat(time, 'HH:mm', { zone: 'local' })
            .setZone('utc')
            .toFormat('HH:mm')
    );
}

export const TimeGridResponse = ({ availableTimesUTC, selectedTimeUTC, startHourUTC = 0 }) => {
    const [selectedTime, setSelectedTime] = React.useState(null);
    const timeSlots = generateSlotsForTimeZone(startHourUTC);

    function generateSlotsForTimeZone(startHour) {
        return Array.from(
            { length: 24 },
            (slot, index) => (index + startHour) % 24
        ).map(hour =>
            luxon.DateTime.fromObject({ hour }, { zone: 'utc' }).toLocal()
        );
    }

    function toggleSlot(time) {
        const formattedTimeLocal = time.toFormat('HH:mm');
        const formattedTimeUTC = time.toUTC().toFormat('HH:mm');

        if (selectedTime === formattedTimeLocal) {
            setSelectedTime(null);
            selectedTimeUTC.current = null;
        } else if (availableTimesUTC.includes(formattedTimeUTC)) {
            setSelectedTime(formattedTimeLocal);
            selectedTimeUTC.current = formattedTimeUTC;
        }
    }

    return (
        <>
            <div className="schedule_challenge_subtext_wrapper">
                <span className="schedule_challenge_subtext">Time slots are displayed in your local time.</span>
                <span className="schedule_challenge_subtext">The first slot below is set a minimum 12 hours from the current time.</span>
            </div>
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
