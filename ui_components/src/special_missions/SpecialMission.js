// @flow

import { apiFetch } from "../utils/network.js";

type MissionData = {|
    +progress: number,
    +log: $ReadOnlyArray<{
        +event: string,
        +description: string,
        +timestamp_ms: number,
    }>,
    +player_health: number,
    +player_max_health: number,
    +start_time: number,
    +reward: number,
|};

type Props = {|
    +selfLink: string,
    +specialMissionId: number,
    +missionEventDurationMs: number,
    +initialMissionData: ?MissionData;
|};
function SpecialMission({
    selfLink,
    missionEventDurationMs,
    specialMissionId,
    initialMissionData,
}: Props) {
    if(specialMissionId !== 0) {
        return <ActiveSpecialMission
            missionId={specialMissionId}
            selfLink={selfLink}
            missionEventDurationMs={missionEventDurationMs}
            initialMissionData={initialMissionData}
        />;
    }
    else {
        return <SpecialMissionSelect selfLink={selfLink} />;
    }
}

function SpecialMissionSelect({ selfLink }: {| +selfLink: string |}) {
    // TODO: Make this submit via API fetch so we don't need to reload
    return (
        <div className="contentDiv">
            <h2 className="contentDivHeader">
                Special Missions
            </h2>

            <p style={{width: "inherit", textAlign: "center"}}>
                As nations vie for power it falls upon shinobi to undertake missions that
                see these goals realized. Occasionally tasks of great importance warrant special designation.
                These missions challenge even the strongest shinobi but come with great rewards.
            </p>
            <ul style={{ listStyleType: "none" }}>
                <li>Special missions take 2-5 minutes to complete.</li>
                <li>Your character will automatically scout enemy territory while completing battles.</li>
                <li>You can be attacked by other players while your character is moving around the map.</li>
                <li>Special missions reward money, experience and village reputation increasing with mission
                    difficulty.
                </li>
                <li>Completing special missions gradually drains chakra or stamina in exchange for jutsu experience.
                </li>
            </ul>
            <br/>
            <a href={`${selfLink}&start=easy`}>
                <button>Start Easy Mission!</button>
            </a>
            <a href={`${selfLink}&start=normal`}>
                <button>Start Normal Mission!</button>
            </a>
            <a href={`${selfLink}&start=hard`}>
                <button>Start Hard Mission!</button>
            </a>
            <a href={`${selfLink}&start=nightmare`}>
                <button>Start Nightmare Mission!</button>
            </a><br/><br/>
        </div>
    );
}

function ActiveSpecialMission({
    missionId,
    initialMissionData,
    selfLink,
    missionEventDurationMs
}) {
    const apiUrl = `api/legacy_special_missions.php?mission_id=${missionId}`;
    const serverRefreshIntervalMs = missionEventDurationMs + 125;

    const [mission, setMission] = React.useState(initialMissionData);
    const [missionComplete, setMissionComplete] = React.useState(false);

    let refreshIntervalIdRef = React.useRef(null);
    function stopRefresh() {
        if(refreshIntervalIdRef.current == null) return;

        clearInterval(refreshIntervalIdRef.current);
        refreshIntervalIdRef.current = null;
    }

    function getMissionData() {
        apiFetch(apiUrl)
            .then(data => {
                if(data.systemMessage) {
                    console.log(data.systemMessage);
                }
                if(data.mission == null) {
                    console.log("Not on a special mission!");
                    stopRefresh();
                    return true;
                }

                if(data.mission.progress >= 100) {
                    data.mission.progress = 100;
                }
                if(data.missionComplete) {
                    setMissionComplete(true);
                    stopRefresh();
                }

                setMission(data.mission);
            })
            .catch(err => {
                console.error(err);
            });
    }

    React.useEffect(() => {
        refreshIntervalIdRef.current = setInterval(getMissionData, serverRefreshIntervalMs);

        return stopRefresh;
    }, [])

    if(mission == null) {
        return null;
    }

    let missionStatus = 'In Progress';
    switch(mission.log[0].event) {
        case 'mission_reward':
            missionStatus = 'Success';
            break;
        case 'mission_failed':
            missionStatus = 'Failed';
            break;
    }

    // TODO: Make cancel submit via API fetch so we don't need to reload
    return (
        <div id="spec_miss_wrapper">
            <div id="spec_miss_cancel_wrapper">
                <span className="spec_miss_page_warning">Stay on this page to keep your mission progressing!</span>
                <a id="spec_miss_cancel" href={`${selfLink}&cancelmission=true`}>Cancel Mission</a>
            </div>
            <SpecialMissionHeader
                missionComplete={missionComplete}
                missionStatus={missionStatus}
                mission={mission}
            />
            <SpecialMissionLog
                logEntries={mission.log}
                missionStartTime={mission.start_time}
            />
        </div>
    );
}

function SpecialMissionHeader({
    missionStatus,
    missionComplete,
    mission,
}: {|
    +missionStatus: string,
    +missionComplete: boolean,
    +mission: MissionData;
|}) {
    let statusClass = '';
    if(missionStatus !== 'In Progress') {
        statusClass = `spec_miss_status_${missionStatus}`;
    }

    const playerHealthPercent = (mission.player_health / mission.player_max_health) * 100;

    return (
        <div id="spec_miss_header">
            <SpecialMissionTimer
                missionStartTime={mission.start_time}
                missionComplete={missionComplete}
            />
            <div id="spec_miss_character_wrapper">
                <div id="spec_miss_status_wrapper">
                    <div id="spec_miss_status_title">
                        Status
                    </div>
                    <div id="spec_miss_status_text" className={statusClass}>
                        {missionStatus}
                    </div>
                </div>
                <div id="spec_miss_health_wrapper">
                    <div id="spec_miss_health_icon"></div>
                    <div id="spec_miss_health_bar_wrapper">
                        <div id="spec_miss_health_bar_out">
                            <div id="spec_miss_health_bar" style={{width: `${playerHealthPercent}%`}}></div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="spec_miss_progress_wrapper">
                <div id="spec_miss_progress_title">
                    Progress
                </div>
                <div id="spec_miss_progress_div" className={mission.progress === 100 ? "spec_miss_status_Success" : ""}>
                    <span id="spec_miss_progress">{mission.progress}</span>/100
                </div>
                <div id="spec_miss_reward_wrapper">
                    ¥<span id="spec_miss_reward">{mission.reward}</span>
                </div>
            </div>
        </div>

    );
}

function SpecialMissionTimer({
    missionStartTime,
    missionComplete
}: {|
    +missionStartTime: number,
    +missionComplete: boolean
|}) {
    const [timeElapsed, setTimeElapsed] = React.useState(timeDifference(missionStartTime));

    const refreshIntervalIdRef = React.useRef(null);
    React.useEffect(() => {
        refreshIntervalIdRef.current = setInterval(() => {
            setTimeElapsed(timeDifference(missionStartTime));
        }, 1000);

        return () => {
            clearInterval(refreshIntervalIdRef.current);
        };
    }, [missionStartTime]);

    React.useEffect(() => {
        if(missionComplete && refreshIntervalIdRef.current != null) {
            clearInterval(refreshIntervalIdRef.current)
        }
    }, [missionComplete])

    return (
        <div id="spec_miss_timer_wrapper">
            <div id="spec_miss_timer_title">
                Duration
            </div>
            <div id="spec_miss_timer">
                {timeElapsed}
            </div>
        </div>
    );
}

function SpecialMissionLog({ logEntries, missionStartTime }) {
    return (
        <div id="spec_miss_log_wrapper">
            {logEntries.map((log, i) => (
                <LogEntry
                    key={`log:${i}`}
                    eventType={log.event}
                    description={log.description}
                    timestampMs={log.timestamp_ms}
                    missionStartTime={missionStartTime}
                />
            ))}
        </div>
    );
}

function LogEntry({ eventType, description, timestampMs, missionStartTime }) {
    return (
        <div className="spec_miss_log_entry">
            <div className="spec_miss_log_entry_icon_wrapper">
                <div className={`spec_miss_log_entry_icon spec_miss_event_${eventType}`}></div>
            </div>
            <div
                className="spec_miss_log_entry_text"
                dangerouslySetInnerHTML={{__html: description.replace(/\[br]/g, "<br />")}}
            />
            <div className="spec_miss_log_entry_timestamp_wrapper">
            <span id="spec_miss_log_entry_timestamp">
                {timeDifference(Math.floor(timestampMs / 1000), missionStartTime)}
            </span>
            </div>
        </div>
    );
}

function timeDifference(timestamp, target = false) {
    // get the current time in milliseconds, UTC
    let currentTime = Math.floor(Date.now() / 1000);

    // the newest timestamp to subtract
    let timeTarget = ( target ? timestamp : currentTime );

    // the older timestamp
    let timeMinus = ( target ? target : timestamp );
    let timeDifference = timeTarget - timeMinus;

    return timeRemaining(timeDifference, 'short', false, true);
}

window.SpecialMission = SpecialMission;