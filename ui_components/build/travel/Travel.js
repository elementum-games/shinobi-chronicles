import { apiFetch } from "../utils/network.js";
import { Map } from "./Map.js";
import { ScoutArea } from "./ScoutArea.js";
const mapDataInterval = 50000; // 1s
const scoutAreaDataInterval = 50000; // 500 ms

const Travel = ({
  travelAPILink,
  missionLink,
  membersLink,
  attackLink
}) => {
  const [errorMessage, setErrorMessage] = React.useState(null);
  const [feedback, setFeedback] = React.useState('(Use WASD, arrow keys, or click a square around you on the map)');
  const [mapData, setMapData] = React.useState(null);
  const [scoutData, setScoutData] = React.useState(null);
  const keysPressed = {};

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

  // keyboard shortcut
  React.useEffect(() => {
    const target_actions = ['ArrowLeft', 'ArrowUp', 'ArrowRight', 'ArrowDown', 'w', 'a', 's', 'd'];
    const keyDown = e => {
      if (target_actions.includes(e.key)) {
        e.preventDefault();
        keysPressed[e.key] = true;
      }
    };
    const keyUp = e => {
      keysPressed[e.key] = false;
    };
    // shortcut listener
    window.addEventListener('keydown', keyDown);
    window.addEventListener('keyup', keyUp);

    // timer to make is smoother? idk
    const timer = setInterval(checkKeyPressed, 100);

    // remove the listener
    return () => {
      // clearInterval(timer);
      window.removeEventListener('keydown', keyDown);
      window.removeEventListener('keyup', keyDown);
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

  // API ACTIONS
  const LoadMapData = () => {
    console.log('Loading Map Data...');
    // setFeedback('Moving...');
    apiFetch(travelAPILink, {
      request: 'LoadMapData'
    }).then(handleAPIResponse);
  };
  const LoadScoutData = () => {
    console.log('Loading Scout Area Data...');
    apiFetch(travelAPILink, {
      request: 'LoadScoutData'
    }).then(handleAPIResponse);
  };
  const MovePlayer = direction => {
    console.log('Moving player...' + direction);
    apiFetch(travelAPILink, {
      request: 'MovePlayer',
      direction: direction
    }).then(handleAPIResponse);
  };
  const EnterPortal = portal_id => {
    console.log('Entering Portal...');
    apiFetch(travelAPILink, {
      request: 'EnterPortal',
      portal_id: portal_id
    }).then(handleAPIResponse);
  };

  // HANDLE API REQUESTS
  const handleAPIResponse = response => {
    // Update errors
    if (response.errors.length > 0) {
      setErrorMessage(response.errors.join(' - '));
    } else {
      switch (response.data.request) {
        case 'LoadMapData':
          console.log('Map loaded.');
          setMapData(response.data.response_data);
          break;
        case 'LoadScoutData':
          console.log('Scout Area updated.');
          setScoutData(response.data.response_data);
          break;
        case 'MovePlayer':
          ResetMessages();
          if (response.data.response_data[0] === false) {
            console.log('Cannot move player.');
            setFeedback(response.data.response_data[1]);
          } else if (response.data.response_data[0] === true) {
            console.log('Player moved successfully');
            LoadMapData(); // Reload map
            LoadScoutData(); // Reload scout area
            setFeedback(response.data.response_data[1]);
          }
          break;
        case 'EnterPortal':
          if (response.data.response_data[0] !== true) {
            break;
          }
          console.log('Player moved through portal.');
          LoadMapData(); // Reload map
          LoadScoutData(); // Reload scout area
          setFeedback(response.data.response_data[1]);
          break;
      }
    }
  };
  const checkKeyPressed = () => {
    const return_actions = {};
    const target_actions = ['ArrowLeft', 'ArrowUp', 'ArrowRight', 'ArrowDown', 'w', 'a', 's', 'd'];
    for (const [key, value] of Object.entries(keysPressed)) {
      if (target_actions.includes(key) && value) {
        return_actions[key] = true;
      }
    }
    if (Object.keys(return_actions).length > 0) {
      setMovement(return_actions);
    }
  };
  const setMovement = actions => {
    if (("ArrowLeft" in actions || "a" in actions) && ("ArrowUp" in actions || "w" in actions)) {
      MovePlayer('move_northwest');
    } else if (("ArrowUp" in actions || "w" in actions) && ("ArrowRight" in actions || "d" in actions)) {
      MovePlayer('move_northeast');
    } else if (("ArrowLeft" in actions || "a" in actions) && ("ArrowDown" in actions || "s" in actions)) {
      MovePlayer('move_southwest');
    } else if (("ArrowDown" in actions || "s" in actions) && ("ArrowRight" in actions || "d" in actions)) {
      MovePlayer('move_southeast');
    } else if ("ArrowLeft" in actions || "a" in actions) {
      MovePlayer('move_west');
    } else if ("ArrowDown" in actions || "s" in actions) {
      MovePlayer('move_south');
    } else if ("ArrowRight" in actions || "d" in actions) {
      MovePlayer('move_east');
    } else if ("ArrowUp" in actions || "w" in actions) {
      MovePlayer('move_north');
    }
  };
  const ResetMessages = () => {
    setErrorMessage(null);
  };
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "contentDivMessages"
  }, errorMessage && /*#__PURE__*/React.createElement("p", {
    className: "systemMessage-new systemMessage-new-error"
  }, errorMessage)), /*#__PURE__*/React.createElement("div", {
    className: "travelAction"
  }, mapData && mapData.portal_display && /*#__PURE__*/React.createElement("button", {
    onClick: () => EnterPortal(mapData.portal_id)
  }, mapData.portal_text), mapData && mapData.mission_button && /*#__PURE__*/React.createElement("a", {
    href: missionLink
  }, /*#__PURE__*/React.createElement("button", null, "Go To Mission Location"))), /*#__PURE__*/React.createElement("div", {
    className: "travel"
  }, /*#__PURE__*/React.createElement("div", {
    className: "travelMapContainer"
  }, mapData && /*#__PURE__*/React.createElement(Map, {
    map_data: mapData,
    onClick: MovePlayer
  }), /*#__PURE__*/React.createElement("div", {
    className: "travelLabel"
  }, feedback)), scoutData && /*#__PURE__*/React.createElement(ScoutArea, {
    mapData: mapData,
    scoutData: scoutData,
    attackLink: attackLink,
    membersLink: membersLink
  })));
};
window.Travel = Travel;