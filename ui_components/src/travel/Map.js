export const Map = ({mapData}) => {

    // map should have a smallish border. this is the offset for the border
    const offset = 12;

    const map_div = document.getElementsByClassName('travel-container')[0];
    const tile_width = parseInt(mapData.tile_width, 10);
    const tile_height = parseInt(mapData.tile_height, 10);
    const player_x = parseInt(mapData.player_x, 10);
    const player_y = parseInt(mapData.player_y, 10);
    const container_width = map_div.clientWidth - tile_width;
    const container_height = map_div.clientHeight - tile_height;

    const map_width = Math.floor(container_width / tile_width);
    const map_height = Math.floor(container_height / tile_height);
    const map_tiles = [...Array(map_width * map_height).keys()];
    const gutter_start_x = Math.floor(player_x - (map_width / 2) + 1);
    const gutter_start_y = Math.floor(player_y - (map_height / 2));
    const gutter_x = Array.from(new Array(map_width), (x, i) => i + gutter_start_x);
    const gutter_y = Array.from(new Array(map_height), (x, i) => i + gutter_start_y);

    const player_start_x = Math.floor(map_width / 2);
    const player_start_y = Math.floor(map_height / 2);

    const player_icon_x = player_start_x * tile_width;
    const player_icon_y = player_start_y * tile_height;

    const map_start_x = player_icon_x - (player_x * tile_width) + tile_width - offset;
    const map_start_y = player_icon_y - (player_y * tile_height) + tile_height - offset;


    const PlayerStyle = {
        position: "absolute",
        backgroundImage: "url(./" + mapData.player_icon + ")",
        top: player_icon_y + "px",
        left: player_icon_x + "px"
    };

    const MapStyle = {
        backgroundImage: "url(./" + mapData.background_image + ")",
        backgroundPositionX: map_start_x + "px",
        backgroundPositionY: map_start_y + "px"
    };

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
        <div id='travel-map-container'>
            <div id='map_background' className='map_background' style={ MapStyle }></div>
            <div className='map_grid_lines'>
                {(map_tiles) && map_tiles.map((e) =>
                    <div key={e} style={{
                        width: mapData.tile_width,
                        height: mapData.tile_height
                    }}></div>
                )}
            </div>
            <div className='map_locations'>
                {(mapData.all_locations) && mapData.all_locations.map((location) =>
                    <div key={location.location_id}
                         className='map_location'
                         style={{
                             cursor: "pointer",
                             backgroundColor: "#" + location.background_color,
                             backgroundImage: "url(." + location.background_image + ")",
                             top: (map_start_y + location.y * tile_height - tile_height + offset) + "px",
                             left: (map_start_x + location.x * tile_width - tile_width + offset) + "px",
                         }}>
                        <div className='map_locations_tooltip'>{location.name}</div>
                    </div>
                )}
            </div>
            <div id='map_player' style={ PlayerStyle }></div>
        </div>
        </>
    );
}