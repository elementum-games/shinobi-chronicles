import { apiFetch } from "../utils/network.js";
import { Map } from "./Map.js";
import { ScoutArea} from "./ScoutArea.js";

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


const scoutAreaDataInterval = 500; // 500 ms
const keyInterval = 100; // 100ms

const keysPressed = {};

window.travelRefreshActive = true;
window.travelDebug = false;

function debug(message) {
    if(window.travelDebug) {
        console.log(message);
    }
}

type Props = {|
    +travelPageLink: string,
    +travelAPILink: string,
    +missionLink: string,
    +membersLink: string,
    +attackLink: string,
    +playerId: number,
|};

function Travel({
    travelPageLink,
    travelAPILink,
    missionLink,
    membersLink,
    attackLink,
    playerId,
}): Props {
    const [feedback, setFeedback] = React.useState(null);

    const [mapData, setMapData] = React.useState(null);
    const [scoutData, setScoutData] = React.useState(null);

    const [ranksToView, setRanksToView] = React.useState({
        1: false,
        2: false,
        3: false,
        4: false
    });

    // Initial Load, fetch map info from user location
    React.useEffect(() => {

        // initial map load
        LoadMapData();
        // initial scout area load
        LoadScoutData();

        // scout area loading
        const timerLoadScoutData = setInterval(() => LoadScoutData(), scoutAreaDataInterval);

        // remove the loop  when  data is displayed
        return () => {
            clearInterval(timerLoadScoutData);
        };
    }, []);

    // this is the temporary workaround for the sidemenu reflecting the player's new location
    // otherwise people will have to refresh before attempting to train outside of village
    React.useEffect(() => {
        const menu = document.getElementsByClassName('sm-tmp-class')[0];

        if (mapData && !mapData.in_village) {
            menu.classList.add('sm-tmp-outvillage');
            menu.classList.remove('sm-tmp-invillage');
        }
        if (mapData && mapData.in_village) {
            menu.classList.add('sm-tmp-invillage');
            menu.classList.remove('sm-tmp-outvillage');
        }
    }, [mapData]);

    // keyboard shortcut
    React.useEffect(() => {

        const allowed_keys = ['ArrowLeft', 'ArrowUp', 'ArrowRight', 'ArrowDown', 'w', 'a', 's', 'd'];
        const keyDown = (e) => {
            if (allowed_keys.includes(e.key)) {
                e.preventDefault();
                keysPressed[e.key] = true;
            }
        }
        const keyUp = (e) => {
            keysPressed[e.key] = false;
        }
        // shortcut listener
        window.addEventListener('keydown', keyDown);
        window.addEventListener('keyup', keyUp);

        // timer to make is smoother
        const timer = setInterval(checkKeyPressed, keyInterval);

        // remove the listener
        return () => {
            clearInterval(timer);
            window.removeEventListener('keydown', keyDown);
            window.removeEventListener('keyup', keyDown);
        };
    }, []);

    const checkKeyPressed = () => {
        const return_actions = {};
        for(const [key, value] of Object.entries(keysPressed)) {
            if (value) {
                return_actions[key] = value;
            }
        }
        if (Object.keys(return_actions).length > 0) {
            setMovement(return_actions);
        }
    }

    const setMovement = (actions) => {
        let direction;
        if (("ArrowUp" in actions || "w" in actions) && ("ArrowLeft" in actions || "a" in actions)) {
            direction = 'northwest';
        } else if (("ArrowUp" in actions || "w" in actions) && ("ArrowRight" in actions || "d" in actions)) {
            direction = 'northeast';
        } else if (("ArrowDown" in actions || "s" in actions) && ("ArrowLeft" in actions || "a" in actions)) {
            direction = 'southwest';
        } else if (("ArrowDown" in actions || "s" in actions) && ("ArrowRight" in actions || "d" in actions)) {
            direction = 'southeast';
        } else if ("ArrowLeft" in actions || "a" in actions) {
            direction = 'west';
        } else if ("ArrowDown" in actions || "s" in actions) {
            direction = 'south';
        } else if ("ArrowRight" in actions || "d" in actions) {
            direction = 'east';
        } else if ("ArrowUp" in actions || "w" in actions) {
            direction = 'north';
        }
        MovePlayer(direction);
    }

    // API ACTIONS
    const LoadMapData = () => {
        debug('Loading Map Data...');
        // setFeedback('Moving...');
        apiFetch(
            travelAPILink,
            {
                request: 'LoadMapData'
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }

            debug('Map loaded.');
            setRanksToView(response.data.response.player_filters.travel_ranks_to_view);
            setMapData(response.data.response);
        });
    }

    const LoadScoutData = () => {
        if(!window.travelRefreshActive) {
            return;
        }

        debug('Loading Scout Area Data...');
        apiFetch(
            travelAPILink,
            {
                request: 'LoadScoutData'
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }

            debug('Scout Area updated.');
            setScoutData(response.data.response);

            const player = response.data.response.filter(user => parseInt(user.user_id) === playerId)[0];
            if(player != null) {
                setMapData(prevMapData => {
                    if (prevMapData == null) {
                        return null;
                    }

                    return {
                        ...prevMapData,
                        player_x: player.target_x,
                        player_y: player.target_y
                    };
                });
            }
        });
    }

    const MovePlayer = (direction) => {
        setFeedback(['Moving...', 'info']);
        debug('Moving player...' + direction);
        apiFetch(
            travelAPILink,
            {
                request: 'MovePlayer',
                direction: direction
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }

            if (response.data.response) {
                debug('Player moved successfully');
                LoadMapData(); // Reload map
                LoadScoutData(); // Reload scout area
            } else {
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

            if (response.data.response) {
                setFeedback(null);
                debug('Player moved through portal.');
                LoadMapData(); // Reload map
                LoadScoutData(); // Reload scout area
            } else {
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

            debug('Filters updated!');
            LoadMapData(); // Reload map
            LoadScoutData(); // Reload scout area
        });
    }

    function handleErrors(errors) {
        console.log(errors);
        setFeedback([errors, 'info']);
    }

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
            />
            <div className='travel-wrapper'>
                <TravelActions
                    travelPageLink={travelPageLink}
                    movePlayer={MovePlayer}
                />
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
                    </div>
                    {(feedback) && (
                        <div className='travel-messages'>
                            <Message message={feedback[0]} messageType={feedback[1]} />
                        </div>
                    )}
                    {(mapData) && (<Map mapData={mapData} />)}
                </div>
            </div>
            {(mapData && scoutData) && (
                <ScoutArea
                    mapData={mapData}
                    scoutData={scoutData}
                    membersLink={membersLink}
                    attackLink={attackLink}
                    ranksToView={ranksToView}
                />
            )}
        </>
    );
}

function TravelFilters({ ranksToView, updateRanksToView}) {
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
            </div>
        </div>
    );
}

function TravelActions({ travelPageLink, movePlayer }) {
    const makeTravelClickHandler = (direction) => {
        return (e) => {
            e.preventDefault();
            movePlayer(direction);
        }
    }

    return (
        <div className='travel-actions'>
            <a href={`${travelPageLink}&travel=northwest`} onClick={makeTravelClickHandler('northwest') }></a>
            <a href={`${travelPageLink}&travel=north`} onClick={makeTravelClickHandler('north') }></a>
            <a href={`${travelPageLink}&travel=northeast`} onClick={makeTravelClickHandler('northeast') }></a>

            <a href={`${travelPageLink}&travel=west`} onClick={makeTravelClickHandler('west') }></a>
            <a href={`${travelPageLink}&travel=east`} onClick={makeTravelClickHandler('east') }></a>

            <a href={`${travelPageLink}&travel=southwest`} onClick={makeTravelClickHandler('southwest') }></a>
            <a href={`${travelPageLink}&travel=south`} onClick={makeTravelClickHandler('south') }></a>
            <a href={`${travelPageLink}&travel=southeast`} onClick={makeTravelClickHandler('southeast') }></a>
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

window.Travel = Travel;