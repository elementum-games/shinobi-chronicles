export const Map = ({map_data, onClick}) => {

    const containerStyle = {
        width: map_data.container_width,
        height: map_data.container_height
    }

    const mapStyle = {
        background: "url("+ map_data.background_image +")",
        backgroundPositionY: "-" + map_data.bg_img_start_y + "px",
        backgroundPositionX: "-" + map_data.bg_img_start_x + "px",
        backgroundRepeat: "no-repeat"
    };

    const playerStyle = {
        backgroundImage: "url("+map_data.player_icon+")",
        backgroundPosition: "center center",
        backgroundRepeat: "no-repeat",
        width: map_data.tile_width,
        height: map_data.tile_height,
        position: "absolute",
        top: map_data.player_img_y,
        left: map_data.player_img_x
    }

    const mobileButtonStyle = {
        width: parseInt(map_data.container_width, 10 ) / 3,
        height: parseInt(map_data.container_height, 10 ) / 3
    }

    return (
        <>
        <div className='mapMobileContainer'>
            <a href='#' style={mobileButtonStyle} onClick={() => onClick('move_northwest')}>&nbsp;</a>
            <a href='#' style={mobileButtonStyle} onClick={() => onClick('move_north')}>&nbsp;</a>
            <a href='#' style={mobileButtonStyle} onClick={() => onClick('move_northeast')}>&nbsp;</a>
            <a href='#' style={mobileButtonStyle} onClick={() => onClick('move_west')}>&nbsp;</a>
            <a href='#' style={mobileButtonStyle}>&nbsp;</a>
            <a href='#' style={mobileButtonStyle} onClick={() => onClick('move_east')}>&nbsp;</a>
            <a href='#' style={mobileButtonStyle} onClick={() => onClick('move_southwest')}>&nbsp;</a>
            <a href='#' style={mobileButtonStyle} onClick={() => onClick('move_south')}>&nbsp;</a>
            <a href='#' style={mobileButtonStyle} onClick={() => onClick('move_southeast')}>&nbsp;</a>
        </div>
        <div className='mapContainer' style={containerStyle}>
            {/* X GUTTER */}
            <GutterX map_data={map_data} />
            {/* Y GUTTER */}
            <GutterY map_data={map_data} />
            {/*Background*/}
            <div className='map' style={mapStyle}></div>
            {/* LOCATIONS */}
            <div className='mapLocations'>
                <MapLocations map_data={map_data} />
            </div>
            <div className='mapPlayer'>
                {/*PLAYER ICON*/}
                <div className='playerIcon' style={playerStyle}></div>
                {/*PLAYER ACTIONS*/}
                <MapActions map_data={map_data} onClick={onClick} />
            </div>
            <div className='mapGrid'>
                {/*GRID LINES*/}
                <MapGrid map_data={map_data} />
            </div>
        </div>
        </>
    );
}

const GutterX = ({map_data}) => {
    return (
        <div className='mapGutterX'>
            {Object.keys(map_data['gutters_x']).map((tile) => (
                <div key={'gutter-x: ' + tile}
                     style={{
                         height: map_data.tile_height,
                         width: map_data.tile_width
                     }}>
                    {tile}
                </div>
            ))}
        </div>
    );
};

const GutterY = ({map_data}) => {
    return (
        <div className='mapGutterY'>
            {Object.keys(map_data['gutters_y']).map((tile) => (
                <div key={'gutter-y:' + tile}
                     style={{
                         height: map_data.tile_height,
                         width: map_data.tile_width
                     }}>
                    {tile}
                </div>
            ))}
        </div>
    );
};

const MapLocations = ({map_data}) => {
    return (
        <>
        {map_data['locations_data'].map((loc) => (
            <div key={loc.location_name}
                 className={'mapGridLocationTile mapGridLocation' + loc.location_name}
                 style={{
                     top: loc.bm_img_start_y,
                     left: loc.bm_img_start_x,
                     width: map_data.tile_width,
                     height: map_data.tile_height,
                     backgroundColor: "#"+loc.background_color,
                     backgroundImage: "url("+loc.background_image+")"
                }}>
            </div>
        ))}
        </>
    );
};

const MapActions = ({map_data, onClick}) => {
    return (
        <>
        {map_data['movement_actions'].map((action) => (
            <a key={'act:' + action.move_direction}
               className='mapGridAction'
               href='#'
               onClick={() => onClick(action.move_direction)}
               style={{
                   position: "absolute",
                   top: action.px_top,
                   left: action.px_left,
                   width: map_data.tile_width,
                   height: map_data.tile_height
                }}>
            </a>
        ))}
        </>
    );
};

const MapGrid = ({map_data}) => {

    const tileBlankStyle = {
        width: map_data.tile_width,
        height: map_data.tile_height
    };

    const totalTiles = parseInt(map_data.container_width_tiles, 10) * parseInt(map_data.container_height_tiles, 10);
    const emptyArray = [...Array(totalTiles).keys()];

    return (
        <>
        {emptyArray.map((tile) => (
                <div key={'empty-grid:' + tile}
                     className='mapGridTileBlank'
                     style={tileBlankStyle}>
                </div>
        ))}
        </>
    );
};