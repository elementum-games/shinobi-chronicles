function CancelTrainingDetails({
  playerData,
  headers
}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("p", null, "Cancel Training"), /*#__PURE__*/React.createElement("p", null, "Are you certain you wish to cancel your training? You will not gain any of your potential gains."), /*#__PURE__*/React.createElement("button", null, /*#__PURE__*/React.createElement("a", {
    href: "<?=$self_link?>&cancel_training=1&cancel_confirm=1"
  }, "Confirm")));
}
function TrainingDetails({
  trainingData
}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    id: "DetailPanelContainer"
  }, /*#__PURE__*/React.createElement("h2", {
    className: 'themeHeader',
    style: {
      textAlign: 'center'
    }
  }, "Academy"), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("p", null, "Here at the academy, you can take classes to improve your skills, attributes, or skill with a jutsu."), /*#__PURE__*/React.createElement("h3", null, "Skill/Attribute training"), /*#__PURE__*/React.createElement("label", null, "Short: ", trainingData.short), /*#__PURE__*/React.createElement("label", null, "Long: ", trainingData.long), /*#__PURE__*/React.createElement("label", null, "Extended: ", trainingData.Extended))));
}
function DetailPanel({
  playerData,
  trainingData,
  headers
}) {
  let content;
  if (playerData.hasActiveTraining && headers.isSetCancelTraining && !headers.isSetCancelConfirm) {
    content = /*#__PURE__*/React.createElement(CancelTrainingDetails, {
      playerData: playerData,
      headers: headers
    });
  } else {
    content = /*#__PURE__*/React.createElement(TrainingDetails, {
      trainingData: trainingData
    });
  }
  return /*#__PURE__*/React.createElement(React.Fragment, null, content);
}
function Training({
  playerData,
  trainingData,
  headers
}) {
  return /*#__PURE__*/React.createElement("div", {
    id: "TrainingContainer"
  }, /*#__PURE__*/React.createElement(DetailPanel, {
    playerData: playerData,
    trainingData: trainingData,
    headers: headers
  }));
}
window.Training = Training; //man I don't even know what this does