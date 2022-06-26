/**
 * @typedef {Object} Response
 *
 * @property {?Mission} mission
 * @property {?String} systemMessage
 */

/**
 * @typedef {Object} Mission
 *
 * @property {Number} mission_id
 * @property {Number} status
 * @property {Number} start_time
 * @property {Number} end_time
 * @property {Number} progress
 * @property {LogEntry[]} log
 * @property {Number} player_health
 * @property {Number} player_max_health
 * @property {Number} reward
 * @property {String} difficulty
 * @property {MissionTarget} target
 *
 *
 * @property {?Boolean} inBattle
 */

/**
 * @typedef {Object} LogEntry
 * @property {String} event
 * @property {Number} timestamp_ms
 * @property {String} description
 */

/**
 * @typedef {Object} MissionTarget
 * @property {String} target
 * @property {Number} x
 * @property {Number} y
 */

let startTime = 0;
const specialMissionsUrl = 'api/legacy_special_missions.php';

// Mission event cooldown + 50ms to help prevent network variance causing premature refreshes
const serverRefreshIntervalMs = (missionEventDurationMs || 3000) + 50;

// Ping server to progress mission on cooldown
let queryInterval = setInterval(() => getMissionData(), serverRefreshIntervalMs);

// Update the timer every second
let displayInterval = setInterval(() => updateTimer(), 1000);

getMissionData();
updateTimer();

function getMissionData() {
    fetch(specialMissionsUrl)
        .then(response => response.json())
        .then(data => {
            // stop everything if user is in battle
            if (data.inBattle === true) {
                inBattle();
                return;
            }
            if(data.systemMessage != null) {
                console.log(data.systemMessage);
            }

            // check the mission status
            missionStatus(data.mission);

            // check the mission timer
            missionTimer(data.mission);

            // check the mission progress
            missionProgress(data.mission);

            // check player health
            playerHealth(data.mission);

            // check reward
            reward(data.mission);

            // check mission logs
            missionLogs(data.mission);
        })
        .catch(err => {
            console.error(err);
        });
}

/**
 * Update the Reward Display
 *
 * @param {Mission} mission
 * @return {boolean}
 */
function reward(mission) {
    if (!mission.reward) {
        return false;
    }

    let target = document.getElementById('spec_miss_reward');
    target.innerHTML = mission.reward.toString();
}

/**
 * Update the Health Display
 *
 * @param {Mission} mission
 */
function playerHealth(mission) {
    let percent = mission.player_health / mission.player_max_health * 100;
    let target = document.getElementById('spec_miss_health_bar');
    target.style.width = percent + '%';
}

/**
 * Update the mission status
 *
 * @param {Mission} mission
 * @return {boolean}
 */
function missionStatus(mission) {
    if (mission.status == null) {
        console.log('Mission Status not set');
        return false;
    }

    // Latest log entry
    let lastEntry = mission.log[0];

    // mission success
    if (lastEntry.event === 'mission_reward') {
        updateMissionStatus('Success');
        stopRefresh();
        return true;
    }

    // mission failed
    if (lastEntry.event === 'mission_failed') {
        updateMissionStatus('Failed');
        stopRefresh();
        return true;
    }

    updateMissionStatus('In Progress');
}


/**
 * Update the mission timer global
 *
 * @param {Mission} mission
 * @return {boolean}
 */
function missionTimer(mission) {
    if (mission.start_time === 0) {
        console.log('Mission Timer not set');
        return false;
    }

    startTime = mission.start_time;
}

/**
 * Run cleanup tasks when mission failed due to being attacked
 *
 * @return {boolean}
 */
function inBattle() {
    updateMissionStatus('Failed');
    stopRefresh();
}

/**
 * Update the Mission Status display
 *
 * @param {String} missionStatus
 * @return {boolean}
 */
function updateMissionStatus(missionStatus) {
    if (missionStatus === 'In Progress') {
        console.log('Mission In Progress');
        return false;
    }

    let className = 'spec_miss_status_' + missionStatus;
    let target = document.getElementById('spec_miss_status_text');
    target.innerHTML = missionStatus;
    target.classList.add(className);
}

/**
 * Updates the Mission Progress display
 *
 * @param {Mission} mission
 * @return {boolean}
 */
function missionProgress(mission) {
    if (mission.progress == null) {
        console.log('Mission Progress not set');
        return false;
    }

    // Update the Progress tracker display
    let progress = (mission.progress > 100 ? 100 : mission.progress);
    let target = document.getElementById('spec_miss_progress').innerHTML = progress;

    // highlight the display if the progress goal has been reached
    if (progress === 100) {
        let className = 'spec_miss_status_Success';
        let classTarget = document.getElementById('spec_miss_progress_div');
        classTarget.classList.add(className);
    }
}

/**
 * Update the Mission Log display
 *
 * @param {Mission} mission
 * @return {boolean}
 */
function missionLogs(mission) {
    if (mission.log == null) {
        return false;
    }

    // template for a log entry
    let template = document.getElementById('log_entry_template').content;
    // the container are appending the entries too
    let container = document.getElementById('spec_miss_log_wrapper');
    // clear the container
    container.innerHTML = '';

    for (const entry of mission.log) {
        // copy of the entry template
        let copy = document.importNode(template, true);
        // Add the icon for the event
        copy.querySelector('.spec_miss_log_entry_icon').classList.add('spec_miss_event_' + entry.event);
        // add the text of the event
        copy.querySelector('.spec_miss_log_entry_text').innerHTML = entry.description.replace(/\[br]/g, "<br />");
        // add the timestamp
        copy.querySelector('#spec_miss_log_entry_timestamp')
            .innerHTML = timeDifference(Math.floor(entry.timestamp_ms / 1000), startTime);
        // add the entry to the container
        container.appendChild(copy);
    }
}

// update the timer
function updateTimer() {
    if (startTime === 0) {
        return false;
    }
    document.getElementById('spec_miss_timer').innerHTML = timeDifference(startTime);
}

function stopRefresh() {
    clearInterval(displayInterval); // Frontend query
    clearInterval(queryInterval); // Backend query
}

// Get the time difference between two times
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