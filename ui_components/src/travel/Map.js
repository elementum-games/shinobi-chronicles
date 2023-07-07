
export const Map = ({ mapData, scoutData, playerId }) => {
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
    const stage_offset_y = player_y - stage_midpoint_y;

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
        backgroundImage: "url(./" + mapData.player_icon + ")",
        top: 0,
        left: 0,
        transform: `translate3d(
            ${(player_x - 1) * tile_width}px,
            ${(player_y - 1) * tile_height}px,
            0
        )`,
    };

    const MapStyle = {
        backgroundImage: "url(./" + mapData.background_image + ")",
        backgroundPositionX: (map_offset_x * tile_width) + "px",
        backgroundPositionY: (map_offset_y * tile_height) + "px",

    };

    return (
        <>
            <MapGutters
                stageWidth={stage_width}
                stageHeight={stage_height}
                stageOffsetX={stage_offset_x}
                stageOffsetY={stage_offset_y}
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
                    }}
                >
                    <div id='map_background' className='map_background' style={{backgroundImage: MapStyle.backgroundImage}}></div>
                    <div className='map_grid_lines'>
                        <MapGridLines
                            mapWidth={map_width}
                            mapHeight={map_height}
                            tileWidth={tile_width}
                            tileHeight={tile_height}
                        />
                    </div>
                    <MapLocations
                        locations={mapData.all_locations || []}
                        tileWidth={tile_width}
                        tileHeight={tile_height}
                    />
                    <ScoutLocations
                        scoutData={scoutData || []}
                        tileWidth={tile_width}
                        tileHeight={tile_height}
                        playerId={playerId}
                    />
                    <div id='map_player' style={ PlayerStyle }></div>
                </div>
            </div>
        </>
    );
}

function MapGutters({ stageWidth, stageHeight, stageOffsetX, stageOffsetY}) {
    // By default, gutter should show tile 1. Apply stage offset to get to the current number.
    const gutter_start_x = 1 + stageOffsetX;
    const gutter_start_y = 1 + stageOffsetY;

    const gutter_x = Array.from(new Array(stageWidth), (x, i) => i + gutter_start_x);
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

function MapGridLines({ mapWidth, mapHeight, tileWidth, tileHeight }) {
    const rows = [...Array(mapHeight).keys()];
    const cols = [...Array(mapWidth).keys()];

    return (
        <>
            {rows.map((row) => (
                cols.map((col) => (
                    <div key={`${row}:${col}`} style={{
                        width: tileWidth,
                        height: tileHeight,
                        top: row * tileHeight,
                        left: col * tileWidth
                    }}></div>
                ))
            ))}
        </>
    );
}

function MapLocations({ locations, tileWidth, tileHeight }) {
    return (
        <div className='map_locations'>
            {locations.map((location) => (
                <div key={location.location_id}
                    className='map_location'
                    style={{
                        cursor: "pointer",
                        backgroundColor: "#" + location.background_color,
                        backgroundImage: "url(." + location.background_image + ")",
                        top: ((location.y - 1) * tileHeight) + "px",
                        left: ((location.x - 1) * tileWidth) + "px",
                    }}>
                    <div className='map_locations_tooltip'>{location.name}</div>
                    {location.objective_image &&
                        <div className='map_location_objective' style={{
                        backgroundImage: "url(." + location.objective_image + ")",
                        zIndex: 5,
                        }}></div>
                    }
                </div>
            ))}
        </div>
    );
}

function ScoutLocations({ scoutData, tileWidth, tileHeight, playerId }) {
    return (
        <div id="scout_locations" className='map_locations'>
            {scoutData.map((player, index) => (
                (player.user_id != playerId) &&
                <div key={player.user_id}
                    className={alignmentClass(player.alignment)}
                    style={{
                        cursor: "pointer",
                        top: ((player.target_y - 1) * tileHeight) + "px",
                        left: ((player.target_x - 1) * tileWidth) + "px",
                        }}>
                    <div className='map_locations_tooltip'>{player.user_name}</div>
                </div>
            ))}
        </div>
    );
}

const alignmentClass = (alignment) => {
    let class_name = 'map_location';
    switch (alignment) {
        case 'Ally':
            class_name += ' player_ally';
            break;
        case 'Enemy':
            class_name += ' player_enemy';
            break;
        case 'Neutral':
            class_name += ' player_neutral';
            break;
    }
    return class_name;
}