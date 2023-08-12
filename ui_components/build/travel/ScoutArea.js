/**
 * @param array{{
 * user_id:         int,
 * user_name:       string,
 * target_x:        int,
 * target_y:        int,
 * target_map_id:   int,
 * rank_name:       string,
 * rank_num:            int,
 * village_icon:    string,
 * alignment:       string,
 * attack:          boolean,
 * attack_id:       string,
 * level:           int,
 * battle_id:       int,
 * direction:       string,
 * invulnerable:    boolean,
 * }} player
 */
export const ScoutArea = ({
  mapData,
  scoutData,
  membersLink,
  attackPlayer,
  sparPlayer,
  ranksToView,
  playerId
}) => {
  return /*#__PURE__*/React.createElement("div", {
    className: "travel-scout-container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "travel-scout"
  }, mapData && scoutData.filter(user => ranksToView[parseInt(user.rank_num)] === true).map(player_data => player_data.user_id != playerId && /*#__PURE__*/React.createElement(Player, {
    key: player_data.user_id,
    player_data: player_data,
    membersLink: membersLink,
    attackPlayer: attackPlayer,
    sparPlayer: sparPlayer,
    colosseumCoords: mapData.colosseum_coords
  }))));
};
const Player = ({
  player_data,
  membersLink,
  attackPlayer,
  sparPlayer,
  colosseumCoords
}) => {
  return /*#__PURE__*/React.createElement("div", {
    key: player_data.user_id,
    className: alignmentClass(player_data.alignment)
  }, /*#__PURE__*/React.createElement("div", {
    className: 'travel-scout-name' + " " + visibilityClass(player_data.invulnerable)
  }, /*#__PURE__*/React.createElement("a", {
    href: membersLink + '&user=' + player_data.user_name
  }, player_data.user_name), /*#__PURE__*/React.createElement("span", null, "Lv.", player_data.level, " - ", player_data.rank_name)), /*#__PURE__*/React.createElement("div", {
    className: "travel-scout-location"
  }, player_data.target_x, " \u2219 ", player_data.target_y), /*#__PURE__*/React.createElement("div", {
    className: "travel-scout-faction"
  }, /*#__PURE__*/React.createElement("img", {
    src: './' + player_data.village_icon,
    alt: "mist"
  })), /*#__PURE__*/React.createElement("div", {
    className: "travel-scout-attack"
  }, player_data.attack === true && parseInt(player_data.battle_id, 10) === 0 && !player_data.invulnerable && (player_data.target_x === colosseumCoords.x && player_data.target_y === colosseumCoords.y ? /*#__PURE__*/React.createElement("a", {
    onClick: () => sparPlayer(player_data.user_id)
  }) : /*#__PURE__*/React.createElement("a", {
    onClick: () => attackPlayer(player_data.attack_id)
  })), player_data.attack === true && parseInt(player_data.battle_id, 10) > 0 && /*#__PURE__*/React.createElement("span", {
    className: "in-battle"
  }), player_data.attack === false && player_data.direction !== 'none' && /*#__PURE__*/React.createElement("span", {
    className: `direction ${player_data.direction}`
  })));
};
const visibilityClass = invulnerable => {
  if (invulnerable) {
    return 'invulnerable';
  }
  return ' ';
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