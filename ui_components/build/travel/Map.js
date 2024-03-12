export const Map = ({
  mapData,
  scoutData,
  npcData,
  playerId,
  ranksToView,
  strategicView,
  displayGrid
}) => {
  console.log(npcData);
  // Visible field info
  const map_div = document.getElementsByClassName('travel-container')[0];
  const tile_width = parseInt(mapData.tile_width, 10);
  const tile_height = parseInt(mapData.tile_height, 10);
  const player_x = parseInt(mapData.player_x, 10);
  const player_y = parseInt(mapData.player_y, 10);
  const container_width = map_div.clientWidth - tile_width;
  const container_height = map_div.clientHeight - tile_height;
  const stage_width = Math.floor(container_width / tile_width);
  const stage_height = Math.floor(container_height / tile_height);
  const stage_midpoint_x = Math.floor(stage_width / 2);
  const stage_midpoint_y = Math.floor(stage_height / 2);

  /* Stage offset is how offset the first visible tile should be.
     0 offset = tile 1
     +5 offset = tile 6
     -5 offset = tile -4
       To visualize, imagine the stage is like this. Player location on X
     | visible |
     | 1 2 3 4 | 5 6 7 8
         X
       Easy, offset is 0, first tile is one. What if player moves two tiles to the right?
           | visible |
     1 2 | 3 4 5 6 | 7 8
             X
       There are 4 visible tiles, so the stage midpoint is visible tile 2. This is where the player should be shown,
     but the player is on coordinate 4. Thus we push the stage 2 tiles to the left so that the second visible tile
     is coordinate 4.
       How do we calculate the starting coordinate in this example? We need to offset the first visible tile by +2 which
     is equal to player X - stage midpoint X.
   */
  const stage_offset_x = player_x - stage_midpoint_x;
  const stage_offset_y = player_y - stage_midpoint_y - 1;

  /* Start player at midpoint. Offset is the desired tile number minus 1 so player sits inside the desired tile rather
   than to the right/bottom of it. For example if you want to show the player in visible tile 1, you don't want to
   offset the player at all. */
  const player_offset_x = stage_midpoint_x - 1;
  const player_offset_y = stage_midpoint_y - 1;

  /* Map is anchored to coordinate 1. If stage is starting with +2 offset (first visible tile is coord 3) then we
  need to shift the whole map 2 tiles to the left to make the first part of it showing the row for coord 3.
   */
  const map_offset_x = stage_offset_x * -1;
  const map_offset_y = stage_offset_y * -1;

  // Calculate display values
  const map_width = parseInt(mapData.end_x) - parseInt(mapData.start_x) + 1;
  const map_height = parseInt(mapData.end_y) - parseInt(mapData.start_y) + 1;
  const PlayerStyle = {
    position: "absolute",
    backgroundImage: mapData.war_action_type ? null : `url(./${mapData.invulnerable ? 'images/ninja_head_grey.png' : mapData.player_icon})`,
    top: 0,
    left: 0,
    transform: `translate3d(
            ${(player_x - 1) * tile_width}px,
            ${(player_y - 1) * tile_height}px,
            0
        )`,
    backfaceVisibility: "hidden",
    filter: "blur(0)"
  };
  const MapStyle = {
    backgroundImage: "url(./" + mapData.background_image + ")",
    backgroundPositionX: map_offset_x * tile_width + "px",
    backgroundPositionY: map_offset_y * tile_height + "px"
  };
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(MapGutterX, {
    stageWidth: stage_width,
    stageOffsetX: stage_offset_x
  }), /*#__PURE__*/React.createElement(MapGutterY, {
    stageHeight: stage_height,
    stageOffsetY: stage_offset_y
  }), /*#__PURE__*/React.createElement(LootDisplay, {
    mapData: mapData
  }), /*#__PURE__*/React.createElement("div", {
    className: "travel_map_stage"
  }, /*#__PURE__*/React.createElement("div", {
    className: "travel_map_content",
    style: {
      transform: `translate3d(
                            ${MapStyle.backgroundPositionX},
                            ${MapStyle.backgroundPositionY},
                            0
                        )`,
      width: map_width * tile_width,
      height: map_height * tile_height,
      backfaceVisibility: "hidden",
      filter: "blur(0)"
    }
  }, /*#__PURE__*/React.createElement("div", {
    id: "map_background",
    className: "map_background",
    style: {
      backgroundImage: MapStyle.backgroundImage
    }
  }), displayGrid && /*#__PURE__*/React.createElement("div", {
    className: "map_grid_lines"
  }, /*#__PURE__*/React.createElement(MapGridLines, {
    tileWidth: tile_width,
    tileHeight: tile_height,
    stageOffsetX: stage_offset_x,
    stageOffsetY: stage_offset_y,
    stageWidth: stage_width,
    stageHeight: stage_height,
    regionCoords: mapData.region_coords,
    strategicView: strategicView
  })), /*#__PURE__*/React.createElement(MapLocations, {
    locations: mapData.all_locations || [],
    tileWidth: tile_width,
    tileHeight: tile_height
  }), /*#__PURE__*/React.createElement(MapObjectives, {
    objectives: mapData.map_objectives || [],
    tileWidth: tile_width,
    tileHeight: tile_height
  }), /*#__PURE__*/React.createElement(RegionObjectives, {
    objectives: mapData.region_objectives || [],
    tileWidth: tile_width,
    tileHeight: tile_height,
    strategicView: strategicView,
    player_x: mapData.player_x,
    player_y: mapData.player_y
  }), /*#__PURE__*/React.createElement(MapNearbyPlayers, {
    scoutData: scoutData || [],
    tileWidth: tile_width,
    tileHeight: tile_height,
    playerId: playerId,
    ranksToView: ranksToView
  }), /*#__PURE__*/React.createElement(MapNearbyNPCs, {
    npcData: npcData || [],
    tileWidth: tile_width,
    tileHeight: tile_height,
    playerId: playerId,
    ranksToView: ranksToView,
    strategicView: strategicView
  }), /*#__PURE__*/React.createElement(Player, {
    playerStyle: PlayerStyle,
    mapData: mapData
  }))), /*#__PURE__*/React.createElement(MapGutterY, {
    stageHeight: stage_height,
    stageOffsetY: stage_offset_y
  }));
};
function MapGutters({
  stageWidth,
  stageHeight,
  stageOffsetX,
  stageOffsetY
}) {
  // By default, gutter should show tile 1. Apply stage offset to get to the current number.
  const gutter_start_x = 1 + stageOffsetX;
  const gutter_start_y = 1 + stageOffsetY;
  const gutter_x = Array.from(new Array(stageWidth - 1), (x, i) => i + gutter_start_x);
  const gutter_y = Array.from(new Array(stageHeight), (x, i) => i + gutter_start_y);
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    id: "travel-x-container"
  }, gutter_x && gutter_x.map(gutter => /*#__PURE__*/React.createElement("div", {
    key: 'gutter_x:' + gutter,
    className: "travel-gutter-grid travel-gutter-grid-x"
  }, gutter))), /*#__PURE__*/React.createElement("div", {
    id: "travel-y-container"
  }, gutter_y && gutter_y.map(gutter => /*#__PURE__*/React.createElement("div", {
    key: 'gutter_y:' + gutter,
    className: "travel-gutter-grid travel-gutter-grid-y"
  }, gutter))));
}
function MapGutterX({
  stageWidth,
  stageOffsetX
}) {
  // By default, gutter should show tile 1. Apply stage offset to get to the current number.
  const gutter_start_x = 1 + stageOffsetX;
  const gutter_x = Array.from(new Array(stageWidth - 1), (x, i) => i + gutter_start_x);
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    id: "travel-x-container"
  }, gutter_x && gutter_x.map(gutter => /*#__PURE__*/React.createElement("div", {
    key: 'gutter_x:' + gutter,
    className: "travel-gutter-grid travel-gutter-grid-x"
  }, gutter))));
}
function MapGutterY({
  stageHeight,
  stageOffsetY
}) {
  // By default, gutter should show tile 1. Apply stage offset to get to the current number.
  const gutter_start_y = 1 + stageOffsetY;
  const gutter_y = Array.from(new Array(stageHeight), (x, i) => i + gutter_start_y);
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    id: "travel-y-container"
  }, gutter_y && gutter_y.map(gutter => /*#__PURE__*/React.createElement("div", {
    key: 'gutter_y:' + gutter,
    className: "travel-gutter-grid travel-gutter-grid-y"
  }, gutter))));
}
function MapGridLines({
  tileWidth,
  tileHeight,
  stageOffsetX,
  stageOffsetY,
  stageWidth,
  stageHeight,
  regionCoords,
  strategicView
}) {
  const rows = [];
  for (let i = stageOffsetY - 2; i < stageOffsetY + stageHeight + 2; i++) {
    rows.push(i);
  }
  const cols = [];
  for (let j = stageOffsetX - 2; j < stageOffsetX + stageWidth + 2; j++) {
    cols.push(j);
  }
  return /*#__PURE__*/React.createElement(React.Fragment, null, rows.map(row => cols.map(col => /*#__PURE__*/React.createElement("div", {
    key: `${row}:${col}`,
    style: {
      width: tileWidth,
      height: tileHeight,
      top: row * tileHeight,
      left: col * tileWidth,
      backgroundColor: strategicView && regionCoords?.[col + 1]?.[row + 1]?.color || '',
      borderTop: strategicView && regionCoords?.[col + 1]?.[row + 1]?.border_top ? 'dashed 3px' : '',
      borderBottom: strategicView && regionCoords?.[col + 1]?.[row + 1]?.border_bottom ? 'dashed 3px' : '',
      borderLeft: strategicView && regionCoords?.[col + 1]?.[row + 1]?.border_left ? 'dashed 3px' : '',
      borderRight: strategicView && regionCoords?.[col + 1]?.[row + 1]?.border_right ? 'dashed 3px' : '',
      borderImageSource: strategicView ? 'url(./images/map/border20px.png)' : '',
      borderImageSlice: strategicView ? 2 : '',
      borderImageRepeat: strategicView ? 'round' : ''
    }
  }))));
}
function MapLocations({
  locations,
  tileWidth,
  tileHeight
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "map_locations"
  }, /*#__PURE__*/React.createElement(ReactTransitionGroup.TransitionGroup, null, locations.map(location => /*#__PURE__*/React.createElement(ReactTransitionGroup.CSSTransition, {
    key: location.location_id,
    timeout: 500 // Set the animation duration in milliseconds
    ,
    classNames: "fade"
  }, /*#__PURE__*/React.createElement("div", {
    className: location.location_type != undefined ? 'map_location ' + location.location_type : 'map_location',
    style: {
      cursor: "pointer",
      backgroundColor: "#" + location.background_color,
      backgroundImage: location.background_image ? `url(${location.background_image})` : null,
      transform: location.location_type == 'key_location' ? `translate3d(${(location.x - 1) * tileWidth - 8}px, ${(location.y - 1) * tileHeight - 8}px, 0)` : `translate3d(${(location.x - 1) * tileWidth}px, ${(location.y - 1) * tileHeight}px, 0)`,
      backfaceVisibility: "hidden",
      filter: "blur(0)"
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "map_location_tooltip"
  }, location.name))))));
}
function MapObjectives({
  objectives,
  tileWidth,
  tileHeight
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "map_objectives"
  }, objectives.map(objective => /*#__PURE__*/React.createElement("div", {
    key: objective.id,
    className: objective.objective_type != undefined ? 'map_objective ' + objective.objective_type : 'map_objective',
    style: {
      cursor: "pointer",
      backgroundColor: "#" + objective.background_color,
      backgroundImage: objective.image ? `url(${objective.image})` : null,
      transform: `translate3d(${(objective.x - 1) * tileWidth}px, ${(objective.y - 1) * tileHeight}px, 0)`,
      backfaceVisibility: "hidden",
      filter: "blur(0)"
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "map_objective_tooltip"
  }, objective.name))));
}
function RegionObjectives({
  objectives,
  tileWidth,
  tileHeight,
  strategicView,
  player_x,
  player_y
}) {
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
  function getResourceIcon(resource_id) {
    switch (resource_id) {
      case 1:
        return '/images/map/icons/materials.png';
      case 2:
        return '/images/map/icons/food.png';
      case 3:
        return '/images/map/icons/wealth.png';
      default:
        return null;
    }
  }
  return /*#__PURE__*/React.createElement("div", {
    className: "region_objectives"
  }, /*#__PURE__*/React.createElement(ReactTransitionGroup.TransitionGroup, null, objectives.map(objective => /*#__PURE__*/React.createElement(ReactTransitionGroup.CSSTransition, {
    key: objective.id,
    timeout: 500 // Set the animation duration in milliseconds
    ,
    classNames: "fade"
  }, /*#__PURE__*/React.createElement("div", {
    className: objective.objective_type != undefined ? 'region_objective ' + objective.objective_type : 'region_objective',
    style: {
      cursor: "pointer",
      backgroundColor: "#" + objective.background_color,
      backgroundImage: `url(${objective.image})`,
      transform: `translate3d(${(objective.x - 1) * tileWidth}px, ${(objective.y - 1) * tileHeight}px, 0)`,
      backfaceVisibility: "hidden",
      filter: "blur(0)"
    }
  }, (() => {
    let backgroundColor;
    const stability = objective.stability;
    const maxStability = 100; // assuming 100 is the max value for positive and negative

    if (stability > 100) {
      const excessStability = stability - 100;
      const excessPercentage = excessStability / maxStability * 100;
      backgroundColor = `linear-gradient(to right, rgba(0, 0, 128, 0.6) 0%, rgba(0, 0, 128, 0.6) ${excessPercentage}%, #295C26cc ${excessPercentage}%, #295C26cc 100%)`;
    } else if (stability > 0) {
      const barWidth = stability / maxStability * 100;
      backgroundColor = `linear-gradient(to right, #295C26cc ${barWidth}%, #3c2b2bcc ${barWidth}%)`;
    } else if (stability < 0) {
      const barWidth = -stability / maxStability * 100;
      backgroundColor = `linear-gradient(to right, #620909cc ${barWidth}%, #3c2b2bcc ${barWidth}%)`;
    } else {
      backgroundColor = '#3c2b2bcc'; // Original background
    }
    return /*#__PURE__*/React.createElement("div", {
      className: `region_objective_tooltip${objective.rebellion_active ? ' rebellion_glow' : ''}`,
      style: {
        display: strategicView || objective.x == player_x && objective.y == player_y ? 'flex' : 'none',
        background: backgroundColor
      }
    }, /*#__PURE__*/React.createElement("span", {
      className: `region_objective_tooltip_name${objective.rebellion_active ? ' rebellion_text' : ''}`
    }, objective.name), /*#__PURE__*/React.createElement("div", {
      className: "region_objective_tooltip_tags"
    }, /*#__PURE__*/React.createElement("span", {
      className: "region_objective_tooltip_defense"
    }, objective.defense), /*#__PURE__*/React.createElement("img", {
      className: "region_objective_tooltip_village",
      src: getVillageIcon(objective.village_id)
    }), /*#__PURE__*/React.createElement("img", {
      className: "region_objective_tooltip_resource",
      src: getResourceIcon(objective.resource_id)
    })));
  })(), strategicView && /*#__PURE__*/React.createElement("div", {
    className: "region_objective_details"
  }, /*#__PURE__*/React.createElement("div", {
    className: "region_objective_details_resource"
  }, /*#__PURE__*/React.createElement("img", {
    className: "region_objective_details_resource_icon",
    src: getResourceIcon(objective.resource_id)
  }), "  ", objective.resource_count, " ", objective.resource_name), /*#__PURE__*/React.createElement("div", {
    className: "region_objective_details_health"
  }, /*#__PURE__*/React.createElement("img", {
    className: "region_objective_details_health_icon",
    src: objective.image
  }), "  ", objective.objective_health, " / ", objective.objective_max_health), /*#__PURE__*/React.createElement("div", {
    className: "region_objective_details_stability"
  }, /*#__PURE__*/React.createElement("span", {
    className: "region_objective_details_stability_icon"
  }, objective.stability), "  stability")), objective.objective_health !== undefined && objective.objective_max_health > 0 && (() => {
    const percentage = objective.objective_health / objective.objective_max_health * 100;
    let barColor;
    let strokeColor = '#2b2c2c';
    if (percentage > 50) {
      barColor = '#00b044';
    } else if (percentage > 25) {
      barColor = 'yellow';
    } else {
      barColor = 'red';
    }
    return percentage < 100 || strategicView || objective.x == player_x && objective.y == player_y ? /*#__PURE__*/React.createElement("div", {
      className: "region_objective_health"
    }, /*#__PURE__*/React.createElement("svg", {
      width: "60",
      height: "9"
    }, /*#__PURE__*/React.createElement("g", {
      transform: "skewX(-25)"
    }, /*#__PURE__*/React.createElement("rect", {
      x: "5",
      y: "0",
      width: "50",
      height: "5",
      style: {
        fill: strokeColor,
        stroke: strokeColor,
        strokeWidth: '0'
      }
    })), /*#__PURE__*/React.createElement("g", {
      transform: "skewX(-25)"
    }, /*#__PURE__*/React.createElement("rect", {
      x: "5",
      y: "0",
      width: percentage / 2,
      height: "5",
      style: {
        fill: barColor,
        stroke: strokeColor,
        strokeWidth: '0'
      }
    })), /*#__PURE__*/React.createElement("g", {
      transform: "skewX(-25)"
    }, /*#__PURE__*/React.createElement("rect", {
      x: "5",
      y: "0",
      rx: "2",
      ry: "2",
      width: "10",
      height: "5",
      style: {
        fill: 'transparent',
        stroke: strokeColor,
        strokeWidth: '2'
      }
    }), /*#__PURE__*/React.createElement("rect", {
      x: "15",
      y: "0",
      width: "10",
      height: "5",
      style: {
        fill: 'transparent',
        stroke: strokeColor,
        strokeWidth: '2'
      }
    }), /*#__PURE__*/React.createElement("rect", {
      x: "25",
      y: "0",
      width: "10",
      height: "5",
      style: {
        fill: 'transparent',
        stroke: strokeColor,
        strokeWidth: '2'
      }
    }), /*#__PURE__*/React.createElement("rect", {
      x: "35",
      y: "0",
      width: "10",
      height: "5",
      style: {
        fill: 'transparent',
        stroke: strokeColor,
        strokeWidth: '2'
      }
    }), /*#__PURE__*/React.createElement("rect", {
      x: "45",
      y: "0",
      rx: "2",
      ry: "2",
      width: "10",
      height: "5",
      style: {
        fill: 'transparent',
        stroke: strokeColor,
        strokeWidth: '2'
      }
    })))) : null;
  })())))));
}
function MapNearbyPlayers({
  scoutData,
  tileWidth,
  tileHeight,
  playerId,
  ranksToView
}) {
  return /*#__PURE__*/React.createElement("div", {
    id: "scout_locations",
    className: "map_locations"
  }, scoutData.filter(user => ranksToView[parseInt(user.rank_num)] === true).map((player, index) => player.user_id != playerId && /*#__PURE__*/React.createElement("div", {
    key: player.user_id,
    className: "map_location",
    style: {
      cursor: "pointer",
      transform: `translate3d(${(player.target_x - 1) * tileWidth}px, ${(player.target_y - 1) * tileHeight}px, 0)`,
      backfaceVisibility: "hidden"
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "map_location_tooltip"
  }, player.user_name), /*#__PURE__*/React.createElement("div", {
    className: alignmentClassPlayer(player.alignment, player.village_id) + visibilityClass(player.invulnerable)
  }), player.loot_count > 0 && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("img", {
    className: "loot_icon_1",
    src: "/../images/map/icons/loot1.png"
  })), player.loot_count > 10 && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("img", {
    className: "loot_icon_2",
    src: "/../images/map/icons/loot1.png"
  })), player.loot_count > 20 && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("img", {
    className: "loot_icon_3",
    src: "/../images/map/icons/loot1.png"
  })))));
}
function MapNearbyNPCs({
  npcData,
  tileWidth,
  tileHeight,
  strategicView
}) {
  return /*#__PURE__*/React.createElement("div", {
    id: "npc_locations",
    className: "map_locations"
  }, npcData.map((npc, index) => /*#__PURE__*/React.createElement("div", {
    key: npc.npc_id + '_' + npc.npc_type,
    className: "map_location",
    style: {
      cursor: "pointer",
      transform: `translate3d(${(npc.target_x - 1) * tileWidth}px, ${(npc.target_y - 1) * tileHeight}px, 0)`,
      backfaceVisibility: "hidden"
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "map_location_tooltip"
  }, npc.npc_name), /*#__PURE__*/React.createElement("div", {
    className: alignmentClassNPC(npc.alignment, npc.village_id) + ' ' + npc.npc_type + ' tier_' + npc.tier
  }), npc.resources.length !== 0 && strategicView && /*#__PURE__*/React.createElement("div", {
    className: "npc_details"
  }, npc.resources[1] && /*#__PURE__*/React.createElement("div", {
    className: "npc_details_resource"
  }, /*#__PURE__*/React.createElement("img", {
    className: "npc_details_resource_icon",
    src: "/images/map/icons/materials.png"
  }), "  ", npc.resources[1], " Materials"), npc.resources[2] && /*#__PURE__*/React.createElement("div", {
    className: "npc_details_resource"
  }, /*#__PURE__*/React.createElement("img", {
    className: "npc_details_resource_icon",
    src: "/images/map/icons/food.png"
  }), "  ", npc.resources[2], " Food"), npc.resources[3] && /*#__PURE__*/React.createElement("div", {
    className: "npc_details_resource"
  }, /*#__PURE__*/React.createElement("img", {
    className: "npc_details_resource_icon",
    src: "/images/map/icons/wealth.png"
  }), "  ", npc.resources[3], " Wealth")))));
}
function Player({
  mapData,
  playerStyle
}) {
  return /*#__PURE__*/React.createElement("div", {
    id: "map_player",
    style: playerStyle
  }, mapData.war_action_type && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "war_action_text"
  }, mapData.war_action_type), /*#__PURE__*/React.createElement("div", {
    id: "war_action_progress_bar"
  }, /*#__PURE__*/React.createElement("svg", {
    height: "32",
    width: "32",
    viewBox: "0 0 50 50"
  }, /*#__PURE__*/React.createElement("circle", {
    id: "war_action_progress_circle_background_outer",
    stroke: "#592424",
    cx: "24.5",
    cy: "24",
    r: "15",
    strokeWidth: "5",
    strokeMiterlimit: "0",
    fill: "none",
    transform: "rotate(-90, 24.5, 24)"
  }), /*#__PURE__*/React.createElement("circle", {
    id: "war_action_progress_circle_background",
    stroke: "#592424",
    cx: "24.5",
    cy: "24",
    r: "10",
    strokeWidth: "11",
    strokeMiterlimit: "0",
    fill: "none",
    strokeDasharray: "62.83",
    strokeDashoffset: "0",
    transform: "rotate(-90, 24.5, 24)"
  }), /*#__PURE__*/React.createElement("circle", {
    id: "war_action_progress_circle",
    stroke: "#ff6a6a",
    cx: "24.5",
    cy: "24",
    r: "10",
    strokeWidth: "5",
    strokeMiterlimit: "0",
    fill: "none",
    strokeDasharray: "62.83",
    strokeDashoffset: 62.83 - 62.83 / 100 * mapData.war_action_progress,
    transform: "rotate(-90, 24.5, 24)"
  }), /*#__PURE__*/React.createElement("circle", {
    id: "war_action_interval_circle",
    stroke: "#00b044",
    cx: "24.5",
    cy: "24",
    r: "15",
    strokeWidth: "2",
    strokeMiterlimit: "0",
    fill: "none",
    strokeDasharray: "100",
    strokeDashoffset: 100 - 100 / 100 * mapData.war_action_interval,
    transform: "rotate(-90, 24.5, 24)"
  })))), mapData.is_protected && /*#__PURE__*/React.createElement("img", {
    className: "player_protected_icon",
    src: "/images/map/icons/protected.png",
    style: {
      position: 'absolute',
      bottom: '-4px',
      right: '-4px',
      height: '18px'
    }
  }));
}
function LootDisplay({
  mapData
}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null, mapData.loot_count > 0 && /*#__PURE__*/React.createElement("div", {
    className: "loot_display"
  }, /*#__PURE__*/React.createElement("span", {
    className: "loot_count"
  }, mapData.loot_count)));
}
const visibilityClass = invulnerable => {
  if (invulnerable) {
    return ' invulnerable';
  }
  return '';
};
const alignmentClassPlayer = (alignment, village_id) => {
  let class_name = '';
  switch (alignment) {
    case 'Ally':
      class_name += 'player_ally';
      break;
    case 'Enemy':
      class_name += 'player_enemy';
      break;
    case 'Neutral':
      class_name += 'player_neutral';
      break;
  }
  switch (village_id) {
    case 1:
      class_name += ' player_stone';
      break;
    case 2:
      class_name += ' player_cloud';
      break;
    case 3:
      class_name += ' player_leaf';
      break;
    case 4:
      class_name += ' player_sand';
      break;
    case 5:
      class_name += ' player_mist';
      break;
  }
  return class_name;
};
const alignmentClassNPC = (alignment, village_id) => {
  let class_name = '';
  switch (alignment) {
    case 'Ally':
      class_name += 'npc_ally';
      break;
    case 'Enemy':
      class_name += 'npc_enemy';
      break;
    case 'Neutral':
      class_name += 'npc_neutral';
      break;
  }
  switch (village_id) {
    case 1:
      class_name += ' npc_stone';
      break;
    case 2:
      class_name += ' npc_cloud';
      break;
    case 3:
      class_name += ' npc_leaf';
      break;
    case 4:
      class_name += ' npc_sand';
      break;
    case 5:
      class_name += ' npc_mist';
      break;
  }
  return class_name;
};