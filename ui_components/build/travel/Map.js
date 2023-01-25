export const Map = ({
  map_data,
  onClick
}) => {
  const containerStyle = {
    width: map_data.container_width,
    height: map_data.container_height
  };
  const mapStyle = {
    background: "url(" + map_data.background_image + ")",
    backgroundPositionY: "-" + map_data.bg_img_start_y + "px",
    backgroundPositionX: "-" + map_data.bg_img_start_x + "px",
    backgroundRepeat: "no-repeat"
  };
  const playerStyle = {
    backgroundImage: "url(" + map_data.player_icon + ")",
    backgroundPosition: "center center",
    backgroundRepeat: "no-repeat",
    width: map_data.tile_width,
    height: map_data.tile_height,
    position: "absolute",
    top: map_data.player_img_y,
    left: map_data.player_img_x
  };
  const mobileButtonStyle = {
    width: parseInt(map_data.container_width, 10) / 3,
    height: parseInt(map_data.container_height, 10) / 3
  };
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "mapMobileContainer"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    style: mobileButtonStyle,
    onClick: () => onClick('move_northwest')
  }, "\xA0"), /*#__PURE__*/React.createElement("a", {
    href: "#",
    style: mobileButtonStyle,
    onClick: () => onClick('move_north')
  }, "\xA0"), /*#__PURE__*/React.createElement("a", {
    href: "#",
    style: mobileButtonStyle,
    onClick: () => onClick('move_northeast')
  }, "\xA0"), /*#__PURE__*/React.createElement("a", {
    href: "#",
    style: mobileButtonStyle,
    onClick: () => onClick('move_west')
  }, "\xA0"), /*#__PURE__*/React.createElement("a", {
    href: "#",
    style: mobileButtonStyle
  }, "\xA0"), /*#__PURE__*/React.createElement("a", {
    href: "#",
    style: mobileButtonStyle,
    onClick: () => onClick('move_east')
  }, "\xA0"), /*#__PURE__*/React.createElement("a", {
    href: "#",
    style: mobileButtonStyle,
    onClick: () => onClick('move_southwest')
  }, "\xA0"), /*#__PURE__*/React.createElement("a", {
    href: "#",
    style: mobileButtonStyle,
    onClick: () => onClick('move_south')
  }, "\xA0"), /*#__PURE__*/React.createElement("a", {
    href: "#",
    style: mobileButtonStyle,
    onClick: () => onClick('move_southeast')
  }, "\xA0")), /*#__PURE__*/React.createElement("div", {
    className: "mapContainer",
    style: containerStyle
  }, /*#__PURE__*/React.createElement(GutterX, {
    map_data: map_data
  }), /*#__PURE__*/React.createElement(GutterY, {
    map_data: map_data
  }), /*#__PURE__*/React.createElement("div", {
    className: "map",
    style: mapStyle
  }), /*#__PURE__*/React.createElement("div", {
    className: "mapLocations"
  }, /*#__PURE__*/React.createElement(MapLocations, {
    map_data: map_data
  })), /*#__PURE__*/React.createElement("div", {
    className: "mapPlayer"
  }, /*#__PURE__*/React.createElement("div", {
    className: "playerIcon",
    style: playerStyle
  }), /*#__PURE__*/React.createElement(MapActions, {
    map_data: map_data,
    onClick: onClick
  })), /*#__PURE__*/React.createElement("div", {
    className: "mapGrid"
  }, /*#__PURE__*/React.createElement(MapGrid, {
    map_data: map_data
  }))));
};
const GutterX = ({
  map_data
}) => {
  return /*#__PURE__*/React.createElement("div", {
    className: "mapGutterX"
  }, Object.keys(map_data['gutters_x']).map(tile => /*#__PURE__*/React.createElement("div", {
    key: 'gutter-x: ' + tile,
    style: {
      height: map_data.tile_height,
      width: map_data.tile_width
    }
  }, tile)));
};
const GutterY = ({
  map_data
}) => {
  return /*#__PURE__*/React.createElement("div", {
    className: "mapGutterY"
  }, Object.keys(map_data['gutters_y']).map(tile => /*#__PURE__*/React.createElement("div", {
    key: 'gutter-y:' + tile,
    style: {
      height: map_data.tile_height,
      width: map_data.tile_width
    }
  }, tile)));
};
const MapLocations = ({
  map_data
}) => {
  return /*#__PURE__*/React.createElement(React.Fragment, null, map_data['locations_data'].map(loc => /*#__PURE__*/React.createElement("div", {
    key: loc.location_name,
    className: 'mapGridLocationTile mapGridLocation' + loc.location_name,
    style: {
      top: loc.bm_img_start_y,
      left: loc.bm_img_start_x,
      width: map_data.tile_width,
      height: map_data.tile_height,
      backgroundColor: "#" + loc.background_color,
      backgroundImage: "url(" + loc.background_image + ")"
    }
  })));
};
const MapActions = ({
  map_data,
  onClick
}) => {
  return /*#__PURE__*/React.createElement(React.Fragment, null, map_data['movement_actions'].map(action => /*#__PURE__*/React.createElement("a", {
    key: 'act:' + action.move_direction,
    className: "mapGridAction",
    href: "#",
    onClick: () => onClick(action.move_direction),
    style: {
      position: "absolute",
      top: action.px_top,
      left: action.px_left,
      width: map_data.tile_width,
      height: map_data.tile_height
    }
  })));
};
const MapGrid = ({
  map_data
}) => {
  const tileBlankStyle = {
    width: map_data.tile_width,
    height: map_data.tile_height
  };
  const totalTiles = parseInt(map_data.container_width_tiles, 10) * parseInt(map_data.container_height_tiles, 10);
  const emptyArray = [...Array(totalTiles).keys()];
  return /*#__PURE__*/React.createElement(React.Fragment, null, emptyArray.map(tile => /*#__PURE__*/React.createElement("div", {
    key: 'empty-grid:' + tile,
    className: "mapGridTileBlank",
    style: tileBlankStyle
  })));
};