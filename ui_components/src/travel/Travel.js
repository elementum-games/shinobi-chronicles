import { QuickScout } from "./ScoutArea.js";
import { apiFetch } from "../utils/network.js";
import { Map } from "./Map.js";
import { ScoutArea } from "./ScoutArea.js";

/**
 * @param {{
  player_x:            int,
    player_y:            int,
    player_map_id:       int,
    player_id:           int,
    player_icon:         string,
    player_filters:      object,
    map_name:            string,
    background_image:    string,
    start_x:             int,
    end_x:               int,
    start_y:             int,
    end_y:               int,
    in_village:          boolean,
    current_portal:      object,
    current_mission:     boolean,
    all_locations:       object,
    tile_width:          int,
    tile_height:         int,
    action_url:          string,
    action_message:      string,
    invulnerable:        boolean,
    regions:             object,
    region_coords:       object,
    spar_link:           string,
    colosseum_coords:    object,
    region_objectives:   object,
    map_objectives:      object,
    battle_url:          string,
    operations:          object,
    operation_type:      string,
    operation_progress:  int,
    operation_interval:  int,
    travel_message:      string,
    loot_count:          int,
 * }} mapData
 *
 * @param {{
 * location_id:         int,
 * background_color:    string
 * }} all_locations
 *
 * @param {{
 * portal_id:       int,
 * entrance_name:   string
 * }} current_portal
 *
 * @param {[
 * 1: boolean,
 * 2:       boolean,
 * 3:     boolean,
 * 4:       boolean
 * ]} player_filters.travel_ranks_to_view
 **/

let scoutAreaDataInterval = 500; // 500 ms

// Buffer time, go a little slower than the interval to account for network variance
const travelBufferMs = 25;

window.travelRefreshActive = true;
window.travelDebug = false;
window.travelDebugVerbose = false;

if(window.location.host === 'localhost') {
    window.travelDebug = true;
    scoutAreaDataInterval = 2500;
}

function debug(...args) {
    if(window.travelDebug) {
        console.log(...args);
    }
}

type Props = {|
    +playerId: number,
    +travelPageLink: string,
    +travelAPILink: string,
    +missionLink: string,
    +membersLink: string,
    +travelCooldownMs: number,
|};

function Travel({
    playerId,
    travelPageLink,
    travelAPILink,
    missionLink,
    membersLink,
    travelCooldownMs
}): Props {
    const travelIntervalFrequency = 40;

    const [feedback, setFeedback] = React.useState(null);

    const [mapData, setMapData] = React.useState(null);
    const [scoutData, setScoutData] = React.useState(null);
    const [patrolData, setPatrolData] = React.useState(null);

    const [ranksToView, setRanksToView] = React.useState({
        1: false,
        2: false,
        3: false,
        4: false
    });
    const [strategicView, setStrategicView] = React.useState(false);
    const [displayGrid, setDisplayGrid] = React.useState(true);

    const refreshIntervalId = React.useRef(null);

    const movementDirection = React.useRef(null);
    const lastTravelStartTime = React.useRef(null);
    const lastTravelEndTime = React.useRef(null);
    const lastTravelSuccessTime = React.useRef(null);
    const lastTravelLatencyMs = React.useRef(0);
    const travelIntervalId = React.useRef(null);

    const headerLocation = React.useRef(null);
    const headerCoords = React.useRef(null);

    // API ACTIONS
    const LoadTravelData = () => {
        if(!window.travelRefreshActive) {
            return;
        }

        apiFetch(
            travelAPILink,
            {
                request: 'LoadTravelData'
            }
        ).then((response) => {
            if (response.data.mapData.battle_url) {
                window.location.href = response.data.mapData.battle_url;
            }
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            if (response.data.travel_message) {
                setFeedback(null);
                setFeedback([response.data.travel_message, 'info']);
            }
            if (!response.data.success) {
                return;
            }
            setRanksToView(response.data.mapData.player_filters.travel_ranks_to_view);
            setStrategicView(response.data.mapData.player_filters.strategic_view === "true");
            setDisplayGrid(response.data.mapData.player_filters.display_grid === "true");
            setMapData(response.data.mapData);
            setScoutData(response.data.nearbyPlayers);
            setPatrolData(response.data.nearbyPatrols);
        });
    }

    const MovePlayer = (direction) => {
        resetRefreshInterval();

        debug('Moving player...' + direction);

        lastTravelStartTime.current = Date.now();
        const requestStart = Date.now();

        apiFetch(
            travelAPILink,
            {
                request: 'MovePlayer',
                direction: direction
            }
        ).then((response) => {
            const requestEnd = Date.now();
            lastTravelEndTime.current = requestEnd;
            lastTravelLatencyMs.current = requestEnd - requestStart;
            debug(`MovePlayer Latency: ${lastTravelLatencyMs.current}ms`);

            if (response.errors.length > 0) {
                handleErrors(response.errors);
                return;
            }
            if (response.data.travel_message) {
                setFeedback(null);
                setFeedback([response.data.travel_message, 'info']);
            }
            if (response.data.success) {
                //console.log("Response Time: " + response.data.time);
                debug(`Move completed ${requestEnd - lastTravelSuccessTime.current} ms after last move`);
                if (headerLocation.current !== null) {
                    headerLocation.current.innerHTML = "";
                }
                if (headerCoords.current !== null) {
                    headerCoords.current.innerHTML = " | " + response.data.mapData.region.name + " (" + response.data.mapData.player_x + "." + response.data.mapData.player_y + ")";
                }
                lastTravelSuccessTime.current = requestEnd;
                setMapData(response.data.mapData);
                setScoutData(response.data.nearbyPlayers);
                setPatrolData(response.data.nearbyPatrols);
            }
            else {
                debug('Cannot move player.');
            }
        });
    }

    const EnterPortal = (portal_id) => {
        debug('Entering Portal...');
        apiFetch(
            travelAPILink,
            {
                request: 'EnterPortal',
                portal_id: portal_id
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            if (response.data.travel_message) {
                setFeedback(null);
                setFeedback([response.data.travel_message, 'info']);
            }
            if (response.data.success) {
                setFeedback(null);
                debug('Player moved through portal.');

                setMapData(response.data.mapData);
                setScoutData(response.data.nearbyPlayers);
            }
            else {
                debug('Cannot move through gate!');
            }
        });
    }

    const UpdateFilter = (filter, value) => {
        debug('Updating Filter...');
        apiFetch(
            travelAPILink,
            {
                request: 'UpdateFilter',
                filter: filter,
                filter_value: value
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            if (response.data.travel_message) {
                setFeedback(null);
                setFeedback([response.data.travel_message, 'info']);
            }
            if (!response.data.success) {
                return;
            }

            debug('Filters updated!');
            setRanksToView(response.data.mapData.player_filters.travel_ranks_to_view);
            setStrategicView(response.data.mapData.player_filters.strategic_view === "true");
            setDisplayGrid(response.data.mapData.player_filters.display_grid === "true");
            setMapData(response.data.mapData);
            setScoutData(response.data.nearbyPlayers);
        });
    }

    function handleErrors(errors) {
        console.warn(errors);
        setFeedback(null);
        setFeedback([errors, 'info']);
        debug([errors]);
    }

    const AttackPlayer = (target) => {
        apiFetch(
            travelAPILink,
            {
                request: 'AttackPlayer',
                target: target
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            if (response.data.travel_message) {
                setFeedback(null);
                setFeedback([response.data.travel_message, 'info']);
            }
            if (!response.data.success) {
                return;
            }

            window.location.href = response.data.redirect;
        });
    }

    const BeginOperation = (type) => {
        apiFetch(
            travelAPILink,
            {
                request: 'BeginOperation',
                operation_type: type,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            if (response.data.travel_message) {
                setFeedback(null);
                setFeedback([response.data.travel_message, 'info']);
            }
            if (!response.data.success) {
                return;
            }
            setMapData(response.data.mapData);
        });
    }

    const CancelOperation = () => {
        apiFetch(
            travelAPILink,
            {
                request: 'CancelOperation',
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            if (response.data.travel_message) {
                setFeedback(null);
                setFeedback([response.data.travel_message, 'info']);
            }
            if (!response.data.success) {
                return;
            }
            setMapData(response.data.mapData);
        });
    }

    const ClaimLoot = () => {
        apiFetch(
            travelAPILink,
            {
                request: 'ClaimLoot',
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            if (response.data.travel_message) {
                setFeedback(null);
                setFeedback([response.data.travel_message, 'info']);
            }
            if (!response.data.success) {
                return;
            }
            setMapData(response.data.mapData);
        });
    }

    const SparPlayer = (target) => {
        window.location.href = mapData.spar_link + "&challenge=" + target;
    }

    // Handle travel
    function changeMovementDirection(newDirection) {
        if(newDirection === movementDirection.current) {
            debug('movement direction same, ignoring');
        }

        const prevDirection = movementDirection.current;
        movementDirection.current = newDirection;

        if(newDirection == null) {
            debug('stop moving');
            clearInterval(travelIntervalId.current);
            travelIntervalId.current = null;
        }
        else if(prevDirection == null) {
            doTravelIfOkay();
            travelIntervalId.current = setInterval(doTravelIfOkay, travelIntervalFrequency);
        }
    }

    function doTravelIfOkay() {
        if(movementDirection.current == null) {
            return;
        }

        const estimatedNetworkDelay = Math.floor(lastTravelLatencyMs.current * 0.4);
        const timeToWait = travelCooldownMs + travelBufferMs - estimatedNetworkDelay;

        const timeSinceLastTravelStart = Date.now() - lastTravelStartTime.current;
        const timeSinceLastTravelSuccess = Date.now() - lastTravelSuccessTime.current;

        // If waiting on a travel request, don't send another one unless it's been more than 200ms
        if(lastTravelStartTime.current > lastTravelEndTime.current && timeSinceLastTravelStart < 200) {
            return;
        }
        if(timeSinceLastTravelSuccess < timeToWait) {
            return;
        }

        debug('base time to wait / network delay', travelCooldownMs + travelBufferMs, estimatedNetworkDelay);
        debug('traveling / +last start / +last success', timeSinceLastTravelStart, timeSinceLastTravelSuccess);

        MovePlayer(movementDirection.current);
    }

    function updateStrategicView(value) {
        UpdateFilter("strategic_view", value);
    }

    function updateDisplayGrid(value) {
        UpdateFilter("display_grid", value);
    }

    // Initial Load, fetch map info from user location
    React.useEffect(() => {
        LoadTravelData();
        headerLocation.current = document.getElementById('contentHeaderLocation');
        headerCoords.current = document.getElementById('contentHeaderCoords');

        // scout area loading
        refreshIntervalId.current = setInterval(() => LoadTravelData(), scoutAreaDataInterval);

        // remove the loop when data is displayed. Clear travel interval too if that's set
        return () => {
            clearInterval(refreshIntervalId.current);
            clearInterval(travelIntervalId.current);
        };
    }, []);

    function resetRefreshInterval() {
        clearInterval(refreshIntervalId.current);
        refreshIntervalId.current = setInterval(() => LoadTravelData(), scoutAreaDataInterval);
    }

    // this is the temporary workaround for the sidemenu reflecting the player's new location
    // otherwise people will have to refresh before attempting to train outside of village
    React.useEffect(() => {
        const menu = document.getElementsByClassName('sm-tmp-class')[0];
        if (menu) {
            if (mapData && !mapData.in_village) {
                menu.classList.add('sm-tmp-outvillage');
                menu.classList.remove('sm-tmp-invillage');
            }
            if (mapData && mapData.in_village) {
                menu.classList.add('sm-tmp-invillage');
                menu.classList.remove('sm-tmp-outvillage');
            }
        }
    }, [mapData]);

    return (
        <>
            <TravelFilters
                ranksToView={ranksToView}
                updateRanksToView={(newRanksToView) => {
                    let newRanksToViewCsv = Object.keys(newRanksToView)
                        .filter(rank => newRanksToView[rank])
                        .join(',');
                    UpdateFilter("travel_ranks_to_view", newRanksToViewCsv)
                }}
                strategicView={strategicView}
                updateStrategicView={updateStrategicView}
                displayGrid={displayGrid}
                updateDisplayGrid={updateDisplayGrid}
            />
            <div className='travel-wrapper'>
                <div className="travel-panel">
                    <div id='travel-container' className='travel-container'>
                        <div className='travel-buttons'>
                            {mapData?.current_portal?.portal_id != null && (
                                <button className='button'
                                    onClick={() => EnterPortal(mapData.current_portal.portal_id)}>
                                    Go to {mapData.current_portal.entrance_name}
                                </button>
                            )}
                            {(mapData && mapData.current_mission) && (
                                <a href={missionLink}>
                                    <button className='button'>Go to Mission Location</button>
                                </a>
                            )}
                            {(mapData && mapData.action_url) && (
                                <a href={mapData.action_url}>
                                    <button className='button'>{mapData.action_message}</button>
                                </a>
                            )}
                            {(mapData && mapData.operation_type) && (
                                <button className='button' onClick={() => CancelOperation()}>Cancel</button>
                            )}
                            {((mapData && mapData.operations && !mapData.operation_type) && typeof mapData.operations_type === 'undefined') && (
                                Object.entries(mapData.operations).map(([key, value], index) => (
                                    <button key={index} onClick={() => BeginOperation(key)} className='button' dangerouslySetInnerHTML={{ __html: value }}>
                                    </button>
                                ))
                            )}
                            {(mapData && mapData.in_village && mapData.loot_count > 0) && (
                                <button className='button' onClick={() => ClaimLoot()}>Deposit Resources</button>
                            )}
                        </div>
                        {feedback && (
                            <div className='travel-messages'>
                                <Message message={feedback[0]} messageType={feedback[1]} />
                            </div>
                        )}
                        {(mapData && scoutData) &&
                            (<Map mapData={mapData}
                                scoutData={scoutData}
                                patrolData={patrolData}
                                playerId={playerId}
                                ranksToView={ranksToView}
                                strategicView={strategicView}
                                displayGrid={displayGrid}
                            />)}
                    </div>
                    <TravelActions
                        travelPageLink={travelPageLink}
                        travelCooldownMs={travelCooldownMs}
                        updateMovementDirection={changeMovementDirection}
                        movePlayer={MovePlayer}
                        mapData={mapData}
                        scoutData={scoutData}
                        attackPlayer={AttackPlayer}
                        sparPlayer={SparPlayer}
                        ranksToView={ranksToView}
                        playerId={playerId}
                    />
                </div>
                {(mapData && scoutData) && (
                    <ScoutArea
                        mapData={mapData}
                        scoutData={scoutData}
                        membersLink={membersLink}
                        attackPlayer={AttackPlayer}
                        sparPlayer={SparPlayer}
                        ranksToView={ranksToView}
                        playerId={playerId}
                        displayAllies={true}
                    />
                )}
                {(mapData && scoutData) && (
                    <ScoutArea
                        mapData={mapData}
                        scoutData={scoutData}
                        membersLink={membersLink}
                        attackPlayer={AttackPlayer}
                        sparPlayer={SparPlayer}
                        ranksToView={ranksToView}
                        playerId={playerId}
                        displayAllies={false}
                    />
                )}

            </div>
            <GlowFilters/>
        </>
    );
}

function TravelFilters({ ranksToView, updateRanksToView, strategicView, updateStrategicView, displayGrid, updateDisplayGrid }) {
    function updateRankVisibility(rank, newValue) {
        updateRanksToView({
            ...ranksToView,
            [rank]: newValue
        });
    }

    return (
        <div className='travel-filter'>
            <div className='travel-filter-title'>
                Filter Options:
            </div>
            <div className='travel-filter-options'>
                <input id='travel-filter-jonin'
                       type='checkbox'
                       checked={ranksToView[4]}
                       onChange={(e) => updateRankVisibility(4, e.target.checked)}
                />
                <label>Jonin</label>

                <input id='travel-filter-chuunin'
                       type='checkbox'
                       checked={ranksToView[3]}
                       onChange={(e) => updateRankVisibility(3, e.target.checked)}
                />
                <label>Chuunin</label>

                <input id='travel-filter-genin'
                       type='checkbox'
                       checked={ranksToView[2]}
                       onChange={(e) => updateRankVisibility(2, e.target.checked)}
                />
                <label>Genin</label>

                <input id='travel-filter-as'
                       type='checkbox'
                       checked={ranksToView[1]}
                       onChange={(e) => updateRankVisibility(1, e.target.checked)}
                />
                <label>Akademi-sei</label>

                <input id='travel-filter-as'
                    type='checkbox'
                    checked={displayGrid}
                    onChange={(e) => updateDisplayGrid(e.target.checked)}
                    style={{marginLeft: "auto"}}
                />
                <label>Display Grid</label>

                <input id='travel-filter-as'
                    type='checkbox'
                    checked={strategicView}
                    onChange={(e) => updateStrategicView(e.target.checked)}
                />
                <label>Strategic View</label>
            </div>
        </div>
    );
}

function TravelActions({
    travelPageLink,
    updateMovementDirection,
    mapData,
    scoutData,
    attackPlayer,
    sparPlayer,
    ranksToView,
    playerId,
}) {
    // If player presses one key, wait a short amount before updating the direction in case they press a second key
    const inputBufferMs = 25;

    const directionKeysPressed = React.useRef({
        left: false,
        up: false,
        right: false,
        down: false,
    });
    const directionButtonClicked = React.useRef(null);

    const [movementDirection, _setMovementDirection] = React.useState(null);
    function setMovementDirection(newDirection) {
        debug('Do direction change', newDirection);
        // Update internally and externally
        _setMovementDirection(newDirection);
        updateMovementDirection(newDirection);
    }

    const inputBufferTimeoutId = React.useRef(null);
    React.useEffect(() => {
        return () => {
            clearTimeout(inputBufferTimeoutId.current);
        };
    }, []);

    // Actions
    const changeMovementDirection = React.useCallback((newDirection) => {
        debug('Check direction change', newDirection);
        if(newDirection === movementDirection) {
            debug('identical direction, quit');
            return;
        }

        // If player is first starting to move, wait to see if they press two keys
        if(movementDirection == null) {
            // Run immediately if there's already a timeout
            if(inputBufferTimeoutId.current != null) {
                debug('move immediate');
                setMovementDirection(newDirection);

                clearTimeout(inputBufferTimeoutId.current);
                inputBufferTimeoutId.current = null;
            }
            else {
                debug('set delayed move');
                inputBufferTimeoutId.current = setTimeout(() => {
                    debug('do delayed move');
                    setMovementDirection(newDirection);

                    inputBufferTimeoutId.current = null;
                }, inputBufferMs);
            }
        }
        // Otherwise, all good
        else {
            setMovementDirection(newDirection);
        }
    }, [movementDirection]);

    // Keyboard input
    const allowed_keys = ['ArrowLeft', 'ArrowUp', 'ArrowRight', 'ArrowDown', 'w', 'a', 's', 'd'];
    // Top to bottom left to right so the grid stacks right
    const allowedDirections = [
        'northwest', 'north', 'northeast',
        'west', 'east',
        'southwest', 'south', 'southeast',
    ];

    const keyDown = (e) => {
        if(e.repeat) return;

        switch(e.key) {
            case 'ArrowLeft':
            case 'a':
                directionKeysPressed.current.left = true;
                break;
            case 'ArrowUp':
            case 'w':
                directionKeysPressed.current.up = true;
                break;
            case 'ArrowRight':
            case 'd':
                directionKeysPressed.current.right = true;
                break;
            case 'ArrowDown':
            case 's':
                directionKeysPressed.current.down = true;
                break;
            default:
                return;
        }

        e.preventDefault();

        if(directionButtonClicked.current != null) {
            return;
        }

        changeMovementDirection(directionFromKeysPressed(directionKeysPressed.current));
    }
    const keyUp = (e) => {
        if(!allowed_keys.includes(e.key)) {
            return;
        }

        switch(e.key) {
            case 'ArrowLeft':
            case 'a':
                directionKeysPressed.current.left = false;
                break;
            case 'ArrowUp':
            case 'w':
                directionKeysPressed.current.up = false;
                break;
            case 'ArrowRight':
            case 'd':
                directionKeysPressed.current.right = false;
                break;
            case 'ArrowDown':
            case 's':
                directionKeysPressed.current.down = false;
                break;
            default:
                return;
        }

        e.preventDefault();

        if(directionButtonClicked.current != null) {
            return;
        }

        changeMovementDirection(directionFromKeysPressed(directionKeysPressed.current));
    }

    React.useEffect(() => {
        // shortcut listener
        window.addEventListener('keydown', keyDown);
        window.addEventListener('keyup', keyUp);

        // remove the listener
        return () => {
            window.removeEventListener('keydown', keyDown);
            window.removeEventListener('keyup', keyUp);
        };
    }, [keyDown, keyUp]);

    const handlePointerUp = (e) => {
        // e.preventDefault();

        if(directionButtonClicked.current == null) return;

        directionButtonClicked.current = null;
        setMovementDirection(null);
    };

    return (
        <div className='travel-actions'>
            {allowedDirections.map((direction) => (
                <a
                    key={`travel:${direction}`}
                    href={`${travelPageLink}&travel=${direction}`}
                    className={`${direction} ${direction === movementDirection ? "active" : ""}`}
                    onPointerDown={e => {
                        e.preventDefault();
                        if(e.button !== 0) {
                            return;
                        }

                        directionButtonClicked.current = direction;
                        setMovementDirection(direction);
                    }}
                    onPointerUp={handlePointerUp}
                    onPointerLeave={handlePointerUp}
                    onContextMenu={e => {
                        e.preventDefault();
                    }}
                    onClick={e => {
                        e.preventDefault();
                    }}
                ></a>
            ))}
            <QuickScout
                mapData={mapData}
                scoutData={scoutData}
                attackPlayer={attackPlayer}
                sparPlayer={sparPlayer}
                ranksToView={ranksToView}
                playerId={playerId}
                updateMovementDirection={updateMovementDirection}
            />

        </div>
    )
}

const Message = ({message, messageType}) => {
    return (
        <div className={`systemMessage-new systemMessage-new-${messageType}`}>
            {message}
        </div>
    );
}

function directionFromKeysPressed(directionKeysPressed) {
    let direction = null;

    if (directionKeysPressed.up && directionKeysPressed.left) {
        direction = 'northwest';
    }
    else if (directionKeysPressed.up && directionKeysPressed.right) {
        direction = 'northeast';
    }
    else if (directionKeysPressed.down && directionKeysPressed.left) {
        direction = 'southwest';
    }
    else if (directionKeysPressed.down && directionKeysPressed.right) {
        direction = 'southeast';
    }
    else if (directionKeysPressed.left) {
        direction = 'west';
    }
    else if (directionKeysPressed.down) {
        direction = 'south';
    }
    else if (directionKeysPressed.right) {
        direction = 'east';
    }
    else if (directionKeysPressed.up) {
        direction = 'north';
    }

    return direction;
}

function GlowFilters() {
    return (
        <svg height="0" width="0">
            <defs>
                <filter id="ally_glow">
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
                <filter id="neutral_glow">
                    <feGaussianBlur in="SourceAlpha" stdDeviation="2" result="blur" />
                    <feFlood floodColor="#ffb600" result="floodColor" />
                    <feComponentTransfer in="blur" result="opacityAdjustedBlur">
                        <feFuncA type="linear" slope="3" />
                    </feComponentTransfer>
                    <feComposite in="floodColor" in2="opacityAdjustedBlur" operator="in" result="coloredBlur" />
                    <feMerge>
                        <feMergeNode in="coloredBlur" />
                        <feMergeNode in="SourceGraphic" />
                    </feMerge>
                </filter>
                <filter id="enemy_glow">
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
                <filter id="stone_glow">
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
                <filter id="cloud_glow">
                    <feGaussianBlur in="SourceAlpha" stdDeviation="2" result="blur" />
                    <feFlood floodColor="yellow" result="floodColor" />
                    <feComponentTransfer in="blur" result="opacityAdjustedBlur">
                        <feFuncA type="linear" slope="2" />
                    </feComponentTransfer>
                    <feComposite in="floodColor" in2="opacityAdjustedBlur" operator="in" result="coloredBlur" />
                    <feMerge>
                        <feMergeNode in="coloredBlur" />
                        <feMergeNode in="SourceGraphic" />
                    </feMerge>
                </filter>
                <filter id="leaf_glow">
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
                <filter id="sand_glow">
                    <feGaussianBlur in="SourceAlpha" stdDeviation="2" result="blur" />
                    <feFlood floodColor="orange" result="floodColor" />
                    <feComponentTransfer in="blur" result="opacityAdjustedBlur">
                        <feFuncA type="linear" slope="2" />
                    </feComponentTransfer>
                    <feComposite in="floodColor" in2="opacityAdjustedBlur" operator="in" result="coloredBlur" />
                    <feMerge>
                        <feMergeNode in="coloredBlur" />
                        <feMergeNode in="SourceGraphic" />
                    </feMerge>
                </filter>
                <filter id="mist_glow">
                    <feGaussianBlur in="SourceAlpha" stdDeviation="2" result="blur" />
                    <feFlood floodColor="blue" result="floodColor" />
                    <feComponentTransfer in="blur" result="opacityAdjustedBlur">
                        <feFuncA type="linear" slope="1" />
                    </feComponentTransfer>
                    <feComposite in="floodColor" in2="opacityAdjustedBlur" operator="in" result="coloredBlur" />
                    <feMerge>
                        <feMergeNode in="coloredBlur" />
                        <feMergeNode in="SourceGraphic" />
                    </feMerge>
                </filter>
            </defs>
        </svg>
    );
}

window.Travel = Travel;