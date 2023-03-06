/**
 * @param array{{
 * user_id:         int,
 * user_name:       string,
 * location_array:  [int, int, int],
 * name:            string,
 * village_icon:    string,
 * alignment:       string,
 * attack:          boolean,
 * attack_id:       string
 * }} player
 */
export const ScoutArea = ({
  mapData,
  scoutData,
  membersLink,
  attackLink,
  view_as,
  view_genin,
  view_chuunin,
  view_jonin
}) => {
  return /*#__PURE__*/React.createElement("div", {
    className: "travel-scout-container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "travel-scout"
  }, mapData && scoutData.map(player_data => /*#__PURE__*/React.createElement(Player, {
    key: player_data.user_id,
    player_data: player_data,
    membersLink: membersLink,
    attackLink: attackLink,
    view_as: view_as,
    view_genin: view_genin,
    view_chuunin: view_chuunin,
    view_jonin: view_jonin
  }))));
};
const Player = ({
  player_data,
  membersLink,
  attackLink,
  view_as,
  view_genin,
  view_chuunin,
  view_jonin
}) => {
  if (player_data.name === 'Akademi-sei' && view_as === false) {
    return /*#__PURE__*/React.createElement(React.Fragment, null);
  } else if (player_data.name === 'Genin' && view_genin === false) {
    return /*#__PURE__*/React.createElement(React.Fragment, null);
  } else if (player_data.name === 'Chuunin' && view_chuunin === false) {
    return /*#__PURE__*/React.createElement(React.Fragment, null);
  } else if (player_data.name === 'Jonin' && view_jonin === false) {
    return /*#__PURE__*/React.createElement(React.Fragment, null);
  }
  return /*#__PURE__*/React.createElement("div", {
    key: player_data.user_id,
    className: alignmentClass(player_data.alignment)
  }, /*#__PURE__*/React.createElement("div", {
    className: "travel-scout-rank"
  }, player_data.name.slice(0, 2)), /*#__PURE__*/React.createElement("div", {
    className: "travel-scout-name"
  }, /*#__PURE__*/React.createElement("a", {
    href: membersLink + '&user=' + player_data.user_name
  }, player_data.user_name)), /*#__PURE__*/React.createElement("div", {
    className: "travel-scout-location"
  }, player_data.location_array[0], " \u2219 ", player_data.location_array[1]), /*#__PURE__*/React.createElement("div", {
    className: "travel-scout-faction"
  }, /*#__PURE__*/React.createElement("img", {
    src: './' + player_data.village_icon,
    alt: "mist"
  })), /*#__PURE__*/React.createElement("div", {
    className: "travel-scout-attack"
  }, player_data.attack === true && /*#__PURE__*/React.createElement("a", {
    href: attackLink + '&attack=' + player_data.attack_id
  })));
};
const alignmentClass = alignment => {
  let class_name = 'travel-scout-entry travel-scout-';
  switch (alignment) {
    case 'Ally':
      class_name += 'ally';
      break;
    case 'Enemy':
      class_name += 'enemy';
      break;
    case 'Neutral':
      class_name += 'neutral';
      break;
  }
  return class_name;
};