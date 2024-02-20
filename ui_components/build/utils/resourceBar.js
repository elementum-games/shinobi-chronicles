export function ResourceBar({
  current_amount,
  max_amount,
  resource_type
}) {
  const resource_percent = Math.round(current_amount / max_amount * 100);
  return /*#__PURE__*/React.createElement("div", {
    className: 'resourceBarOuter ' + resource_type + 'Preview'
  }, /*#__PURE__*/React.createElement("div", {
    className: 'resourceFill ' + resource_type,
    style: {
      width: resource_percent + "%"
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "text"
  }, current_amount, " / ", max_amount));
}