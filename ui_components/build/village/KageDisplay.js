export default function KageDisplay({
  username,
  avatarLink,
  seatTitle,
  villageName,
  isProvisional = false,
  provisionalDaysLabel = "",
  seatId = null,
  playerSeatId = null,
  onResign = null,
  onClaim = null,
  onChallenge = null
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "kage_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "kage_header"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Kage"), /*#__PURE__*/React.createElement("div", {
    className: "kage_kanji"
  }, getKageKanji(villageName))), avatarLink && /*#__PURE__*/React.createElement("div", {
    className: "kage_avatar_wrapper"
  }, /*#__PURE__*/React.createElement("img", {
    className: "kage_avatar",
    src: avatarLink
  })), !avatarLink && /*#__PURE__*/React.createElement("div", {
    className: "kage_avatar_wrapper_empty"
  }, /*#__PURE__*/React.createElement("div", {
    className: "kage_avatar_fill"
  })), /*#__PURE__*/React.createElement("div", {
    className: "kage_nameplate_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "kage_nameplate_decoration nw"
  }), /*#__PURE__*/React.createElement("div", {
    className: "kage_nameplate_decoration ne"
  }), /*#__PURE__*/React.createElement("div", {
    className: "kage_nameplate_decoration se"
  }), /*#__PURE__*/React.createElement("div", {
    className: "kage_nameplate_decoration sw"
  }), /*#__PURE__*/React.createElement("div", {
    className: "kage_name"
  }, username ? username : "---"), /*#__PURE__*/React.createElement("div", {
    className: "kage_title"
  }, isProvisional ? seatTitle + ": " + provisionalDaysLabel : seatTitle + " of " + villageName), seatId != null && seatId === playerSeatId && onResign && /*#__PURE__*/React.createElement("div", {
    className: "kage_resign_button",
    onClick: onResign
  }, "resign"), seatId == null && onClaim && /*#__PURE__*/React.createElement("div", {
    className: "kage_claim_button",
    onClick: onClaim
  }, "claim"), seatId != null && seatId !== playerSeatId && onChallange && /*#__PURE__*/React.createElement("div", {
    className: "kage_challenge_button",
    onClick: onChallenge
  }, "challenge")));
}

function getKageKanji(village_name) {
  switch (village_name) {
    case 'Stone':
      return '土影';

    case 'Cloud':
      return '雷影';

    case 'Leaf':
      return '火影';

    case 'Sand':
      return '風影';

    case 'Mist':
      return '水影';
  }
}