export default function BattleLog({
  lastTurnText
}) {
  const textSegments = lastTurnText.split('[hr]');
  return /*#__PURE__*/React.createElement("table", {
    className: "table"
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, "Last turn")), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
    style: {
      textAlign: "center"
    }
  }, textSegments.map((segment, i) => /*#__PURE__*/React.createElement("p", {
    key: i
  }, segment))))));
}