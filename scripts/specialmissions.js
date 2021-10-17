let startTime = 0;
let userBattle = false;


// check the state of the mission every 2 seconds
let queryInterval = setInterval(() => {
    getMissionData();
}, 1000);

// Update the timer if it's set every second
let displayInterval = setInterval(() => {
    updateTimer();
}, 1000);

getMissionData();
updateTimer();

function getMissionData() {
    // CREATE XHR OBJECT
    let xhr = new XMLHttpRequest();
    const ajaxTarget = 'ajax_specialmissions.php';
    xhr.open('GET', ajaxTarget, true);
    xhr.onreadystatechange = function() {
        // If the response is OK
        if (this.readyState == 4 && this.status === 200) {
            if (!this.responseText) {
                return false;
            }
            let res = JSON.parse(this.responseText);

            
            if (res.log) {
                res.log = JSON.parse(res.log);
            }
            // check if the user is in battle
            let battleStatus = inBattle(res);

            // stop everything if user is in battle
            if (battleStatus) {
                return;
            }

            // check the mission status
            missionStatus(res);

            // check the mission timer
            missionTimer(res);

            // check the mission progress
            missionProgress(res);

            // check player health
            playerHealth(res);

            // check reward
            reward(res);

            // check mission logs
            missionLogs(res);
        }
    }
    // Send Request
    xhr.send();
}

// Update the Reward Display
function reward(res) {
    if (!res.reward) {
        return false;
    }

    let target = document.getElementById('spec_miss_reward');
    target.innerHTML = res.reward;
}

// Update the Health Display
function playerHealth(res) {
    let percent = res.player_health / res.player_max_health * 100;
    let target = document.getElementById('spec_miss_health_bar');
    target.style.width = percent + '%';
}

// Update the mission status
function missionStatus(res) {
    if (res.status == undefined) {
        console.log('Mission Status not set');
        return false;
    }

    // Latest log entry
    let lastEntry = res.log[0];

    // mission success
    if (lastEntry.event == 'mission_reward') {
        updateMissionStatus('Success');

        // clear intervals
        clearInterval(displayInterval); // Frontend query
        clearInterval(queryInterval); // Backend query
        return true;
    }

    // mission failed
    if (lastEntry.event == 'mission_failed') {
        updateMissionStatus('Failed');

        // clear intervals
        clearInterval(displayInterval); // Frontend query
        clearInterval(queryInterval); // Backend query
        return true;
    }

    updateMissionStatus('In Progress');
}

// Update the mission timer global
function missionTimer(res) {
    if (res.start_time == 0) {
        console.log('Mission Timer not set');
        return false;
    }

    startTime = res.start_time;
}

// check if the user in battle
function inBattle(res) {
    if (res == 'battle') {
        // update the UI with the status
        updateMissionStatus('Failed');

        // clear intervals
        clearInterval(displayInterval); // Frontend query
        clearInterval(queryInterval); // Backend query
        return true;
    }
    return false;
}

// Update the Mission Status display
function updateMissionStatus(missionStatus) {
    if (missionStatus == 'In Progress') {
        console.log('Mission In Progress');
        return false;
    }

    let className = 'spec_miss_status_' + missionStatus;
    let target = document.getElementById('spec_miss_status_text');
    target.innerHTML = missionStatus;
    target.classList.add(className);
}

// Updates the Mission Progress display
function missionProgress(res) {
    if (res.progress == undefined) {
        console.log('Mission Progress not set');
        return false;
    }

    // Update the Progress trackeer display
    let progress = (res.progress > 100 ? 100 : res.progress);
    let target = document.getElementById('spec_miss_progress').innerHTML = progress;

    // highlight the display if the progress goal has been reached
    if (progress == 100) {
        let className = 'spec_miss_status_Success';
        let classTarget = document.getElementById('spec_miss_progress_div');
        classTarget.classList.add(className);
    }
}

// Update the Mission Log display
function missionLogs(res) {
    if (res.log == undefined) {
        return false;
    }

    // template for a log entry
    let template = document.getElementById('log_entry_template').content;
    // the container are appending the entries too
    let container = document.getElementById('spec_miss_log_wrapper');
    // clear the container
    container.innerHTML = '';

    // generate entries
    for (const entry of res.log) {
        // copy of the entry template
        let copy = document.importNode(template, true);
        // Add the icon for the event
        copy.querySelector('.spec_miss_log_entry_icon').classList.add('spec_miss_event_' + entry.event);
        // add the text of the event
        copy.querySelector('.spec_miss_log_entry_text').innerHTML = entry.description;
        // add the timestamp
        copy.querySelector('#spec_miss_log_entry_timestamp').innerHTML = timeDifference(entry.timestamp, startTime);
        // add the entry to the container
        container.appendChild(copy);
    }
}

// update the timer
function updateTimer() {
    if (startTime == 0) {
        return false;
    }
    let timeEllapsed = timeDifference(startTime);
    let timer = document.getElementById('spec_miss_timer').innerHTML = timeEllapsed;
}

// Get the time difference between two times
function timeDifference(timestamp, target = false) {
    // get the current time in seconds, UTC
    let currentTime = Math.floor(Date.now() / 1000);
    // the newest timestamp to subtract
    let timeTarget = ( target ? timestamp : currentTime );
    // the older timestamp
    let timeMinus = ( target ? target : timestamp );
    let timeDifference = timeTarget - timeMinus;
    let timeEllapsed = timeRemaining(timeDifference, 'short', false, true);
    return timeEllapsed;
}