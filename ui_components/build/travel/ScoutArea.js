export const ScoutArea = ({
  mapData,
  scoutData,
  attackLink,
  membersLink
}) => {
  return /*#__PURE__*/React.createElement("div", {
    className: "travelScoutContainer"
  }, /*#__PURE__*/React.createElement("table", {
    className: "table scoutTable"
  }, /*#__PURE__*/React.createElement("thead", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, "Name"), /*#__PURE__*/React.createElement("th", null, "Rank"), /*#__PURE__*/React.createElement("th", null, "Lvl"), /*#__PURE__*/React.createElement("th", null, "Village"), /*#__PURE__*/React.createElement("th", null, "Loc"), /*#__PURE__*/React.createElement("th", null, "Action"))), /*#__PURE__*/React.createElement("tbody", null, scoutData && mapData && scoutData.map(player => /*#__PURE__*/React.createElement("tr", {
    key: 'userid:' + player.user_id
  }, /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement("a", {
    href: membersLink + '&user=' + player.user_name
  }, player.user_name)), /*#__PURE__*/React.createElement("td", null, player.name), /*#__PURE__*/React.createElement("td", null, player.level), /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement("img", {
    src: player.village_icon,
    alt: player.village,
    className: "scoutTableVillageIcon"
  }), "\xA0", /*#__PURE__*/React.createElement("span", {
    className: 'scoutTableAlign' + player.alignment
  }, player.village)), /*#__PURE__*/React.createElement("td", null, player.location), /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement(ActionDisplay, {
    key: player.user_id,
    player_data: player,
    map_data: mapData,
    attackLink: attackLink
  })))))));
};
const ActionDisplay = ({
  player_data,
  map_data,
  attackLink
}) => {
  let return_attack = false;
  let return_blank = true;
  let return_text = '';
  const battle_id = parseInt(player_data.battle_id, 10);

  // if the user is an ally or enemy
  if (player_data.alignment === 'Ally') {
    return_text = 'Ally';
  } else {
    return_text = 'Protected';
    return_attack = true;
  }
  if (battle_id !== 0) {
    return_text = 'In Battle';
  }

  // if the user is on the same tile
  if (player_data.location === map_data.player_location) {
    return_blank = false;
  }

  // if the user is the display player
  if (parseInt(map_data.self_user_id, 10) === parseInt(player_data.user_id, 10)) {
    return /*#__PURE__*/React.createElement("span", null, "You");
  }

  // if the user is on the same tile and it's an enemy of the same rank
  if (!return_blank && return_attack && player_data.attackable && battle_id === 0) {
    return /*#__PURE__*/React.createElement("a", {
      href: attackLink + '&attack=' + player_data.attack_id
    }, "Attack");
  }
  if (!return_blank) {
    return /*#__PURE__*/React.createElement("span", null, return_text);
  } else {
    return /*#__PURE__*/React.createElement(React.Fragment, null);
  }
};