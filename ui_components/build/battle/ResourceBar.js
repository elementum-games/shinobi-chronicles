const styles = {
  resourceBarOuter: {
    height: "16px",
    width: "225px",
    margin: "2px 0",
    borderStyle: "solid",
    borderWidth: "1px",
    position: "relative",
    background: "rgba(0, 0, 0, 0.7)"
  },
  resourceBarOuterText: {
    position: "absolute",
    left: "0",
    top: "0",
    right: "0",
    textAlign: "center",
    color: "#f0f0f0",
    lineHeight: "16px",
    fontSize: "12px",
    textShadow: "0 0 2px black"
  },
  resourceFill: {
    height: "100%"
  },
  healthFill: {
    background: "linear-gradient(to bottom, #A00000, #D00000, #A00000)"
  },
  chakraFill: {
    background: "linear-gradient(to bottom, #001aA0, #1030e0, #001aA0)"
  },
  staminaFill: {
    background: "linear-gradient(to bottom, #00A000, #00D000, #00A000)"
  }
};
export function ResourceBar({
  currentAmount,
  maxAmount,
  resourceType
}) {
  const resource_percent = Math.round(currentAmount / maxAmount * 100);
  let resourceFillStyle = {};

  switch (resourceType) {
    case 'health':
      resourceFillStyle = styles.healthFill;
      break;

    case 'chakra':
      resourceFillStyle = styles.chakraFill;
      break;
  }

  return /*#__PURE__*/React.createElement("div", {
    className: "resourceBarOuter",
    style: styles.resourceBarOuter
  }, /*#__PURE__*/React.createElement("div", {
    style: { ...styles.resourceFill,
      ...resourceFillStyle,
      width: `${resource_percent}%`
    }
  }, "\xA0"), /*#__PURE__*/React.createElement("div", {
    className: "text",
    style: styles.resourceBarOuterText
  }, currentAmount.toFixed(2), " / ", maxAmount.toFixed(2)));
}