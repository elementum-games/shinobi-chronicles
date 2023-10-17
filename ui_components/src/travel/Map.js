
export const Map = ({ mapData, scoutData, patrolData, playerId, ranksToView, strategicView, displayGrid}) => {
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
        backgroundImage: mapData.operation_type ? null : `url(./${mapData.invulnerable ? 'images/ninja_head_grey.png' : mapData.player_icon})`,
        top: 0,
        left: 0,
        transform: `translate3d(
            ${(player_x - 1) * tile_width}px,
            ${(player_y - 1) * tile_height}px,
            0
        )`,
        backfaceVisibility: "hidden",
        filter: "blur(0)",
    };

    const MapStyle = {
        backgroundImage: "url(./" + mapData.background_image + ")",
        backgroundPositionX: (map_offset_x * tile_width) + "px",
        backgroundPositionY: (map_offset_y * tile_height) + "px",

    };

    return (
        <>
            <MapGutterX
                stageWidth={stage_width}
                stageOffsetX={stage_offset_x}
            />
            <MapGutterY
                stageHeight={stage_height}
                stageOffsetY={stage_offset_y}
            />
            <LootDisplay
                mapData={mapData}
            />
            <div className='travel_map_stage'>
                <div className='travel_map_content'
                     style={{
                        transform: `translate3d(
                            ${MapStyle.backgroundPositionX},
                            ${MapStyle.backgroundPositionY},
                            0
                        )`,
                        width: map_width * tile_width,
                        height: map_height * tile_height,
                        backfaceVisibility: "hidden",
                        filter: "blur(0)",
                    }}
                >
                    <div id='map_background' className='map_background' style={{backgroundImage: MapStyle.backgroundImage}}></div>
                    {displayGrid &&
                        <div className='map_grid_lines'>
                            <MapGridLines
                                tileWidth={tile_width}
                                tileHeight={tile_height}
                                stageOffsetX={stage_offset_x}
                                stageOffsetY={stage_offset_y}
                                stageWidth={stage_width}
                                stageHeight={stage_height}
                                regionCoords={mapData.region_coords}
                                strategicView={strategicView}
                            />
                        </div>
                    }
                    <MapLocations
                        locations={mapData.all_locations || []}
                        tileWidth={tile_width}
                        tileHeight={tile_height}
                    />
                    <MapObjectives
                        objectives={mapData.map_objectives || []}
                        tileWidth={tile_width}
                        tileHeight={tile_height}
                    />
                    <RegionObjectives
                        objectives={mapData.region_objectives || []}
                        tileWidth={tile_width}
                        tileHeight={tile_height}
                        strategicView={strategicView}
                        player_x={mapData.player_x}
                        player_y={mapData.player_y}
                    />
                    <MapNearbyPlayers
                        scoutData={scoutData || []}
                        tileWidth={tile_width}
                        tileHeight={tile_height}
                        playerId={playerId}
                        ranksToView={ranksToView}
                    />
                    <MapNearbyPatrols
                        patrolData={patrolData || []}
                        tileWidth={tile_width}
                        tileHeight={tile_height}
                        playerId={playerId}
                        ranksToView={ranksToView}
                        strategicView={strategicView}
                    />
                    <Player
                        playerStyle={PlayerStyle}
                        mapData={mapData}
                    />
                </div>
            </div>
            <MapGutterY
                stageHeight={stage_height}
                stageOffsetY={stage_offset_y}
            />
        </>
    );
}

function MapGutters({ stageWidth, stageHeight, stageOffsetX, stageOffsetY}) {
    // By default, gutter should show tile 1. Apply stage offset to get to the current number.
    const gutter_start_x = 1 + stageOffsetX;
    const gutter_start_y = 1 + stageOffsetY;

    const gutter_x = Array.from(new Array(stageWidth-1), (x, i) => i + gutter_start_x);
    const gutter_y = Array.from(new Array(stageHeight), (x, i) => i + gutter_start_y);

    return (
        <>
            <div id='travel-x-container'>
                {(gutter_x) && gutter_x.map((gutter) =>
                    <div key={ 'gutter_x:' + gutter }
                         className='travel-gutter-grid travel-gutter-grid-x'>
                        { gutter }
                    </div>
                )}
            </div>
            <div id="travel-y-container">
                {(gutter_y) && gutter_y.map((gutter) =>
                    <div key={ 'gutter_y:' + gutter }
                         className='travel-gutter-grid travel-gutter-grid-y'>
                        { gutter }
                    </div>
                )}
            </div>
        </>
    )
}

function MapGutterX({ stageWidth, stageOffsetX }) {
    // By default, gutter should show tile 1. Apply stage offset to get to the current number.
    const gutter_start_x = 1 + stageOffsetX;
    const gutter_x = Array.from(new Array(stageWidth - 1), (x, i) => i + gutter_start_x);

    return (
        <>
            <div id='travel-x-container'>
                {(gutter_x) && gutter_x.map((gutter) =>
                    <div key={'gutter_x:' + gutter}
                        className='travel-gutter-grid travel-gutter-grid-x'>
                        {gutter}
                    </div>
                )}
            </div>
        </>
    )
}

function MapGutterY({ stageHeight, stageOffsetY }) {
    // By default, gutter should show tile 1. Apply stage offset to get to the current number.
    const gutter_start_y = 1 + stageOffsetY;
    const gutter_y = Array.from(new Array(stageHeight), (x, i) => i + gutter_start_y);

    return (
        <>
            <div id="travel-y-container">
                {(gutter_y) && gutter_y.map((gutter) =>
                    <div key={'gutter_y:' + gutter}
                        className='travel-gutter-grid travel-gutter-grid-y'>
                        {gutter}
                    </div>
                )}
            </div>
        </>
    )
}

function MapGridLines({ tileWidth, tileHeight, stageOffsetX, stageOffsetY, stageWidth, stageHeight, regionCoords, strategicView }) {
    const rows = [];
    for (let i = stageOffsetY - 2; i < stageOffsetY + stageHeight + 2; i++) {
        rows.push(i);
    }

    const cols = [];
    for (let j = stageOffsetX - 2; j < stageOffsetX + stageWidth + 2; j++) {
        cols.push(j);
    }

    return (
        <>
            {rows.map((row) => (
                cols.map((col) => (
                    <div key={`${row}:${col}`} style={{
                        width: tileWidth,
                        height: tileHeight,
                        top: row * tileHeight,
                        left: col * tileWidth,
                        backgroundColor: strategicView && regionCoords?.[col + 1]?.[row + 1]?.color || '',
                        borderTop: strategicView && regionCoords?.[col+1]?.[row+1]?.border_top ? 'dashed 3px' : '',
                        borderBottom: strategicView && regionCoords?.[col+1]?.[row+1]?.border_bottom ? 'dashed 3px' : '',
                        borderLeft: strategicView && regionCoords?.[col+1]?.[row+1]?.border_left ? 'dashed 3px' : '',
                        borderRight: strategicView && regionCoords?.[col + 1]?.[row + 1]?.border_right ? 'dashed 3px' : '',
                        borderImageSource: strategicView ? 'url(./images/map/border20px.png)' : '',
                        borderImageSlice: strategicView ? 2 : '',
                        borderImageRepeat: strategicView ? 'round' : '',
                    }}></div>
                ))
            ))}
        </>
    );
}

function MapLocations({ locations, tileWidth, tileHeight }) {
    return (
        <div className='map_locations'>
            <ReactTransitionGroup.TransitionGroup>
                {locations.map((location) => (
                    <ReactTransitionGroup.CSSTransition
                        key={location.location_id}
                        timeout={500} // Set the animation duration in milliseconds
                        classNames="fade"
                    >
                        <div className={location.location_type != undefined ? 'map_location ' + location.location_type : 'map_location'}
                            style={{
                                cursor: "pointer",
                                backgroundColor: "#" + location.background_color,
                                backgroundImage: location.background_image ? `url(${location.background_image})` : null,
                                transform: location.location_type == 'key_location' ? `translate3d(${((location.x - 1) * tileWidth) - 8}px, ${((location.y - 1) * tileHeight) - 8}px, 0)`
                                    : `translate3d(${(location.x - 1) * tileWidth}px, ${(location.y - 1) * tileHeight}px, 0)`,
                                backfaceVisibility: "hidden",
                                filter: "blur(0)",
                            }}>
                            <div className='map_location_tooltip'>{location.name}</div>
                        </div>
                    </ReactTransitionGroup.CSSTransition>
                ))}
            </ReactTransitionGroup.TransitionGroup>
        </div>
    );
}

function MapObjectives({ objectives, tileWidth, tileHeight }) {
    return (
        <div className='map_objectives'>
            {objectives.map((objective) => (
                <div key={objective.id}
                    className={objective.objective_type != undefined ? 'map_objective ' + objective.objective_type : 'map_objective'}
                    style={{
                        cursor: "pointer",
                        backgroundColor: "#" + objective.background_color,
                        backgroundImage: objective.image ? `url(${objective.image})` : null,
                        transform: `translate3d(${(objective.x - 1) * tileWidth}px, ${(objective.y - 1) * tileHeight}px, 0)`,
                        backfaceVisibility: "hidden",
                        filter: "blur(0)",
                    }}>
                    <div className='map_objective_tooltip'>{objective.name}</div>
                </div>
            ))}
        </div>
    );
}

function RegionObjectives({ objectives, tileWidth, tileHeight, strategicView, player_x, player_y }) {
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
    return (
        <div className='region_objectives'>
            <ReactTransitionGroup.TransitionGroup>
                {objectives.map((objective) => (
                    <ReactTransitionGroup.CSSTransition
                        key={objective.id}
                        timeout={500} // Set the animation duration in milliseconds
                        classNames="fade"
                    >
                        <div
                            className={objective.objective_type != undefined ? 'region_objective ' + objective.objective_type : 'region_objective'}
                            style={{
                                cursor: "pointer",
                                backgroundColor: "#" + objective.background_color,
                                backgroundImage: `url(${objective.image})`,
                                transform: `translate3d(${(objective.x - 1) * tileWidth}px, ${(objective.y - 1) * tileHeight}px, 0)`,
                                backfaceVisibility: "hidden",
                                filter: "blur(0)",
                            }}
                        >
                            <div className='region_objective_tooltip' style={{ display: strategicView || (objective.x == player_x && objective.y == player_y) ? 'flex' : 'none' }}>
                                <span className='region_objective_tooltip_name'>{objective.name}</span>
                                <div className='region_objective_tooltip_tags'>
                                    <span className='region_objective_tooltip_defense'>{objective.defense}</span>
                                    <img className='region_objective_tooltip_village' src={getVillageIcon(objective.village_id)} />
                                    <img className='region_objective_tooltip_resource' src={getResourceIcon(objective.resource_id)} />
                                </div>
                            </div>
                            {strategicView &&
                                <div className='region_objective_details'>
                                    {/*<div className='region_objective_details_name'>
                                        <img className='region_objective_details_village_icon' src={getVillageIcon(objective.village_id)} />  {objective.name}
                                    </div>*/}
                                    <div className='region_objective_details_resource'>
                                        <img className='region_objective_details_resource_icon' src={getResourceIcon(objective.resource_id)} />  {objective.resource_count} {objective.resource_name}
                                    </div>
                                    {/*<div className='region_objective_details_defense'>
                                        <span className='region_objective_details_defense_icon'>{objective.defense}</span>  defense
                                    </div>*/}
                                    <div className='region_objective_details_health'>
                                        <img className='region_objective_details_health_icon' src={objective.image} />  {objective.objective_health} / {objective.objective_max_health}
                                    </div>
                                </div>
                            }
                            {objective.objective_health !== undefined && objective.objective_max_health > 0 &&
                                (() => {
                                    const percentage = (objective.objective_health / objective.objective_max_health) * 100;
                                    let barColor;
                                    let strokeColor = '#2b2c2c';
                                    if (percentage > 50) {
                                        barColor = '#00b044';
                                    } else if (percentage > 25) {
                                        barColor = 'yellow';
                                    } else {
                                        barColor = 'red';
                                }

                                return (percentage < 100 || strategicView || (objective.x == player_x && objective.y == player_y) ? (
                                    <div className='region_objective_health'>
                                        <svg width="60" height="9">
                                            <g transform="skewX(-25)">
                                                <rect x="5" y="0" width="50" height="5" style={{ fill: strokeColor, stroke: strokeColor, strokeWidth: '0' }} />
                                            </g>
                                            <g transform="skewX(-25)">
                                                <rect x="5" y="0" width={percentage / 2} height="5" style={{ fill: barColor, stroke: strokeColor, strokeWidth: '0' }} />
                                            </g>
                                            <g transform="skewX(-25)">
                                                <rect x="5" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth:'2'}} />
                                                <rect x="15" y="0" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth:'2'}} />
                                                <rect x="25" y="0" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth:'2'}} />
                                                <rect x="35" y="0" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth:'2'}} />
                                                <rect x="45" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth:'2'}} />
                                            </g>
                                          </svg>
                                    </div>) : null
                                    );
                                })()
                            }
                        </div>
                    </ReactTransitionGroup.CSSTransition>
                ))}
            </ReactTransitionGroup.TransitionGroup>
        </div>
    );
}

function MapNearbyPlayers({ scoutData, tileWidth, tileHeight, playerId, ranksToView }) {
    return (
        <div id="scout_locations" className='map_locations'>
            {scoutData
                .filter(user => ranksToView[parseInt(user.rank_num)] === true)
                .map((player, index) => (
                    (player.user_id != playerId) &&
                    <div key={player.user_id}
                        className='map_location'
                        style={{
                            cursor: "pointer",
                            transform: `translate3d(${(player.target_x - 1) * tileWidth}px, ${(player.target_y - 1) * tileHeight}px, 0)`,
                            backfaceVisibility: "hidden",
                        }}>
                        <div className='map_location_tooltip'>{player.user_name}</div>
                        <div className={alignmentClassPlayer(player.alignment, player.village_id) + visibilityClass(player.invulnerable)}></div>
                        {player.loot_count > 0 &&
                            <>
                                <img className='loot_icon_1' src='/../images/map/icons/loot1.png' />
                            </>
                        }
                        {player.loot_count > 5 &&
                            <>
                                <img className='loot_icon_2' src='/../images/map/icons/loot1.png' />
                            </>
                        }
                        {player.loot_count > 10 &&
                            <>
                                <img className='loot_icon_3' src='/../images/map/icons/loot1.png' />
                            </>
                        }
                    </div>
                ))}
        </div>
    );
}

function MapNearbyPatrols({ patrolData, tileWidth, tileHeight, strategicView }) {
    return (
        <div id="patrol_locations" className='map_locations'>
            {patrolData
                .map((patrol, index) => (
                    <div key={patrol.patrol_id + '_' + patrol.patrol_type}
                        className='map_location'
                        style={{
                            cursor: "pointer",
                            transform: `translate3d(${(patrol.target_x - 1) * tileWidth}px, ${(patrol.target_y - 1) * tileHeight}px, 0)`,
                            backfaceVisibility: "hidden",
                        }}>
                        <div className='map_location_tooltip'>{patrol.patrol_name}</div>
                        <div className={alignmentClassPatrol(patrol.alignment, patrol.village_id) + ' ' + patrol.patrol_type + ' tier_' + patrol.tier}></div>
                        {(patrol.resources.length !== 0 && strategicView) &&
                            <div className='patrol_details'>
                            {patrol.resources[1] &&
                                <div className='patrol_details_resource'>
                                <img className='patrol_details_resource_icon' src='/images/map/icons/materials.png' />  {patrol.resources[1]} Materials
                                </div>
                            }
                            {patrol.resources[2] &&
                                <div className='patrol_details_resource'>
                                <img className='patrol_details_resource_icon' src='/images/map/icons/food.png' />  {patrol.resources[2]} Food
                                </div>
                            }
                            {patrol.resources[3] &&
                                <div className='patrol_details_resource'>
                                <img className='patrol_details_resource_icon' src='/images/map/icons/wealth.png' />  {patrol.resources[3]} Wealth
                                </div>
                            }
                            </div>
                        }
                    </div>
                ))}
        </div>
    );
}

function Player({ mapData, playerStyle }) {
    return (
        <div id='map_player' style={playerStyle}>
            {mapData.operation_type &&
                <>
                    <div className='operation_text'>{mapData.operation_type}</div>
                    <div id="operation_progress_bar">
                        <svg height="32" width="32" viewBox="0 0 50 50">
                            <circle
                                id="operation_progress_circle_background_outer"
                                stroke="#592424"
                                cx="24.5"
                                cy="24"
                                r="15"
                                strokeWidth="5"
                                strokeMiterlimit="0"
                                fill="none"
                                transform="rotate(-90, 24.5, 24)"
                            />
                            <circle
                                id="operation_progress_circle_background"
                                stroke="#592424"
                                cx="24.5"
                                cy="24"
                                r="10"
                                strokeWidth="11"
                                strokeMiterlimit="0"
                                fill="none"
                                strokeDasharray="62.83"
                                strokeDashoffset="0"
                                transform="rotate(-90, 24.5, 24)"
                            />
                            <circle
                                id="operation_progress_circle"
                                stroke="#ff6a6a"
                                cx="24.5"
                                cy="24"
                                r="10"
                                strokeWidth="5"
                                strokeMiterlimit="0"
                                fill="none"
                                strokeDasharray="62.83"
                                strokeDashoffset={62.83 - ((62.83 / 100) * mapData.operation_progress)}
                                transform="rotate(-90, 24.5, 24)"
                            />
                            <circle
                                id="operation_interval_circle"
                                stroke="#00b044"
                                cx="24.5"
                                cy="24"
                                r="15"
                                strokeWidth="2"
                                strokeMiterlimit="0"
                                fill="none"
                                strokeDasharray="100"
                                strokeDashoffset={100 - ((100 / 100) * mapData.operation_interval)}
                                transform="rotate(-90, 24.5, 24)"
                            />
                        </svg>
                    </div>
                </>
            }
        </div>
    );
}

function LootDisplay({ mapData }) {
    return (
        <>
            {mapData.loot_count > 0 &&
                <div className='loot_display'>
                    <span className='loot_count'>{mapData.loot_count}</span>
                </div>
            }
        </>
    );
}

const visibilityClass = (invulnerable) => {
    if (invulnerable) {
        return ' invulnerable';
    }
    return '';
}

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
}

const alignmentClassPatrol = (alignment, village_id) => {
    let class_name = '';
    switch (alignment) {
        case 'Ally':
            class_name += 'patrol_ally';
            break;
        case 'Enemy':
            class_name += 'patrol_enemy';
            break;
        case 'Neutral':
            class_name += 'patrol_neutral';
            break;
    }
    switch (village_id) {
        case 1:
            class_name += ' patrol_stone';
            break;
        case 2:
            class_name += ' patrol_cloud';
            break;
        case 3:
            class_name += ' patrol_leaf';
            break;
        case 4:
            class_name += ' patrol_sand';
            break;
        case 5:
            class_name += ' patrol_mist';
            break;
    }
    return class_name;
}