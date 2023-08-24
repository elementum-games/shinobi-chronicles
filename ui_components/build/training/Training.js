function CancelTrainingDetails({
  playerData,
  headers
}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("h2", {
    className: 'themeHeader',
    style: {
      textAlign: 'center'
    }
  }, "Cancel Training"), /*#__PURE__*/React.createElement("p", null, "Are you certain you wish to cancel your training? You will not gain any of your potential gains."), /*#__PURE__*/React.createElement("button", null, /*#__PURE__*/React.createElement("a", {
    href: headers.selfLink + '&cancel_training=1&cancel_confirm=1'
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
  }, "Academy"), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("p", null, "Here at the academy, you can take classes to improve your skills, attributes, or skill with a jutsu."), /*#__PURE__*/React.createElement("h3", null, "Skill/Attribute training"), /*#__PURE__*/React.createElement("p", null, "Short: ", trainingData.short), /*#__PURE__*/React.createElement("p", null, "Long: ", trainingData.long), /*#__PURE__*/React.createElement("p", null, "Extended: ", trainingData.extended))));
}

//Displays Training Data {Training Details, CancelTrainingDetails}
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
function Option({
  title
}) {
  let styleItem = {
    flex: '1 1 calc(25% - 20px)',
    // Distributes space for 4 items, reduces to 33.33% for 3 items
    backgroundColor: '8B4513',
    width: '100%',
    textAlign: 'center'
  };
  let buttonsArray = [];
  return /*#__PURE__*/React.createElement("div", {
    style: styleItem,
    id: "optionContainer"
  }, /*#__PURE__*/React.createElement("h2", null, title), buttonsArray.map((item, index) => /*#__PURE__*/React.createElement("div", {
    key: index
  }, item)));
}

//Displays Training Selection Input {TrainingOption}
function SelectTrainingPanel({}) {
  let styleContainer = {
    display: 'flex',
    flexWrap: 'wrap'
  };
  return /*#__PURE__*/React.createElement("div", {
    style: styleContainer
  }, /*#__PURE__*/React.createElement(Option, {
    key: 0,
    title: "Skills"
  }), /*#__PURE__*/React.createElement(Option, {
    key: 1,
    title: "Attributes"
  }), /*#__PURE__*/React.createElement(Option, {
    key: 2,
    title: "Jutsu"
  }), /*#__PURE__*/React.createElement(Option, {
    key: 3,
    title: "Bloodline Jutsu"
  }));
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
  }), /*#__PURE__*/React.createElement(SelectTrainingPanel, null));
}
window.Training = Training; //man I don't even know what this does