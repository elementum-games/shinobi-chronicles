import { apiFetch } from "../utils/network.js";
import { Map } from "./Map.js";
import { ScoutArea} from "./ScoutArea.js";

/**
 * @param {{
 * player_x:            int,
 * player_y:            int,
 * player_z:            int,
 * player_id:           int,
 * player_icon:         string,
 * player_filters:      object,
 * map_name:            string,
 * background_image:    string,
 * start_x:             int,
 * end_x:               int,
 * start_y:             int,
 * end_y:               int,
 * in_village:          boolean,
 * current_portal:      object,
 * current_mission:     boolean,
 * all_locations:       object,
 * tile_width:          int,
 * tile_height:         int,
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
 * Akademi-sei: boolean,
 * genin:       boolean,
 * chuunin:     boolean,
 * jonin:       boolean
 * ]} player_filters.travel_filter
 **/


const scoutAreaDataInterval = 500; // 500 ms
const keyInterval = 100; // 100ms

const keysPressed = {};

const Travel = ({
    travelAPILink,
    missionLink,
    membersLink,
    attackLink
}) => {

    const [feedback, setFeedback] = React.useState(null);

    const [mapData, setMapData] = React.useState(null);
    const [scoutData, setScoutData] = React.useState(null);

    const [viewAS, setViewAS] = React.useState(false);
    const [viewGenin, setViewGenin] = React.useState(false);
    const [viewChuunin, setViewChuunin] = React.useState(false);
    const [viewJonin, setViewJonin] = React.useState(false);

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

    const setFilters = (travel_filters) => {
        if (travel_filters['Akademi-sei']) {
            setViewAS(true);
        } else {
            setViewAS(false);
        }
        if (travel_filters['Genin']) {
            setViewGenin(true);
        } else {
            setViewGenin(false);
        }
        if (travel_filters['Chuunin']) {
            setViewChuunin(true);
        } else {
            setViewChuunin(false);
        }
        if (travel_filters['Jonin']) {
            setViewJonin(true);
        } else {
            setViewJonin(false);
        }
    }

    // API ACTIONS
    const LoadMapData = () => {
        console.log('Loading Map Data...');
        // setFeedback('Moving...');
        apiFetch(
            travelAPILink,
            {
                request: 'LoadMapData'
            }
        ).then(handleAPIResponse);
    }

    const LoadScoutData = () => {
        console.log('Loading Scout Area Data...');
        apiFetch(
            travelAPILink,
            {
                request: 'LoadScoutData'
            }
        ).then(handleAPIResponse);
    }

    const MovePlayer = (direction) => {
        setFeedback(['Moving...', 'info']);
        console.log('Moving player...' + direction);
        apiFetch(
            travelAPILink,
            {
                request: 'MovePlayer',
                direction: direction
            }
        ).then(handleAPIResponse);
    }

    const EnterPortal = (portal_id) => {
        console.log('Entering Portal...');
        apiFetch(
            travelAPILink,
            {
                request: 'EnterPortal',
                portal_id: portal_id
            }
        ).then(handleAPIResponse);
    }

    const UpdateFilter = (filter, value) => {
        console.log('Updating Filter...');
        apiFetch(
            travelAPILink,
            {
                request: 'UpdateFilter',
                filter: filter,
                filter_value: + value
            }
        ).then(handleAPIResponse)
    }

    // HANDLE API REQUESTS
    const handleAPIResponse = (response) => {
        // Update errors
        if (response.errors.length) {
            console.log(response.errors);
            setFeedback([response.errors, 'info']);
            return;
        }

        switch (response.data.request) {
            case 'LoadMapData':
                console.log('Map loaded.');
                setFilters(response.data.response.player_filters.travel_filter);
                setMapData(response.data.response);
                break;
            case 'LoadScoutData':
                console.log('Scout Area updated.');
                setScoutData(response.data.response);
                break;
            case 'MovePlayer':
                if (response.data.response) {
                    console.log('Player moved successfully');
                    LoadMapData(); // Reload map
                    LoadScoutData(); // Reload scout area
                } else {
                    console.log('Cannot move player.');
                }
                break;
            case 'EnterPortal':
                if (response.data.response) {
                    setFeedback(null);
                    console.log('Player moved through portal.');
                    LoadMapData(); // Reload map
                    LoadScoutData(); // Reload scout area
                } else {
                    console.log('Cannot move through gate!');
                }
                break;
            case 'UpdateFilter':
                console.log('Filter updated!');
                LoadMapData(); // Reload map
                LoadScoutData(); // Reload scout area
                break;
        }
    };

    return (
        <>
            <div className='travel-filter'>
                <div className='travel-filter-title'>
                    Filter Options:
                </div>
                <div className='travel-filter-options'>
                    <input id='travel-filter-jonin'
                           type='checkbox'
                           checked={viewJonin}
                           onChange={() => UpdateFilter('Jonin', viewJonin)}
                    />
                    <label>Jonin</label>
                    <input id='travel-filter-chuunin'
                           type='checkbox'
                           checked={viewChuunin}
                           onChange={() => UpdateFilter('Chuunin', viewChuunin)}
                    />
                    <label>Chuunin</label>
                    <input id='travel-filter-genin'
                           type='checkbox'
                           checked={viewGenin}
                           onChange={() => UpdateFilter('Genin', viewGenin)}
                    />
                    <label>Genin</label>
                    <input id='travel-filter-as'
                           type='checkbox'
                           checked={viewAS}
                           onChange={() => UpdateFilter('Akademi-sei', viewAS)}
                    />
                    <label>Akademi-sei</label>
                </div>
            </div>
        <div className='travel-wrapper'>
            <div className='travel-actions'>
                <a onClick={() => MovePlayer('northwest') }></a>
                <a onClick={() => MovePlayer('north') }></a>
                <a onClick={() => MovePlayer('northeast') }></a>

                <a onClick={() => MovePlayer('west') }></a>
                <a onClick={() => MovePlayer('east') }></a>

                <a onClick={() => MovePlayer('southwest') }></a>
                <a onClick={() => MovePlayer('south') }></a>
                <a onClick={() => MovePlayer('southeast') }></a>
            </div>
            <div id='travel-container' className='travel-container'>
                <div className='travel-buttons'>
                    {(mapData && !Array.isArray(mapData.current_portal)) && (
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
                <ScoutArea mapData={mapData}
                           scoutData={scoutData}
                           membersLink={membersLink}
                           attackLink={attackLink}
                           view_as={viewAS}
                           view_genin={viewGenin}
                           view_chuunin={viewChuunin}
                           view_jonin={viewJonin}
                />
            )}
        </>
    );
}

const Message = ({message, messageType}) => {

    return (
        <div className={`systemMessage-new systemMessage-new-${messageType}`}>
            {message}
        </div>
    );
}

window.Travel = Travel;