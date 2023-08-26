const styles = {
  // general style
  // 1em = 16px
  body: {
    margin: '1em 0.5em',
    padding: '0em 0em 2em 0em',
    border: '0em solid #554d35e6',
    borderRadius: '0.5em'
  },
  topHeader: {
    border: '0em solid',
    borderRadius: '0.5em 0.5em, 0em, 0em' // Should match parent element
  },

  bgColor: {
    backgroundColor: '#d2c9b3',
    color: '958556e6'
  },
  indent: {
    margin: '0em 1em'
  },
  indentLabel: {
    fontWeight: 'bold',
    width: '80%'
  },
  roundBorder: {
    borderRadius: '0.5em 0.5em 0em 0em'
  },
  //specific elements
  option: {
    padding: '1em, 0em',
    margin: '0em 0.3em',
    border: '0.5px solid #958556e6',
    borderRadius: '0 0 0.5em 0.5em',
    // Should match parent element
    flex: '1 1 calc(25% - 20px)',
    // Distributes space for 4 items, reduces to 33.33% for 3 items
    minWidth: '200px',
    backgroundColor: '8B4513',
    width: '100%',
    textAlign: 'center'
  }
};
function CancelTrainingDetails({
  playerData,
  headers
}) {
  return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("h2", null, "Cancel Training"), /*#__PURE__*/React.createElement("p", null, "Are you certain you wish to cancel your training? You will not gain any of your potential ", /*#__PURE__*/React.createElement("strong", null, playerData.trainGains), " gains."), /*#__PURE__*/React.createElement("button", null, /*#__PURE__*/React.createElement("a", {
    href: headers.selfLink + '&cancel_training=1&cancel_confirm=1'
  }, "Confirm")));
}
function TrainingDetails({
  playerData,
  trainingData
}) {
  const combinedHeaderStyles = {
    ...styles.header,
    ...styles.topHeader
  };
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    id: "DetailPanelContainer"
  }, /*#__PURE__*/React.createElement("h2", {
    style: combinedHeaderStyles
  }, "Academy"), /*#__PURE__*/React.createElement("div", {
    style: styles.indent
  }, /*#__PURE__*/React.createElement("p", null, "Here at the academy, you can take classes to improve your skills, attributes, or skill with a jutsu."), /*#__PURE__*/React.createElement("h2", null, "Skill/Attribute training"), /*#__PURE__*/React.createElement("p", null, "Short: ", trainingData.short), /*#__PURE__*/React.createElement("p", null, "Long: ", trainingData.long), /*#__PURE__*/React.createElement("p", null, "Extended: ", trainingData.extended), /*#__PURE__*/React.createElement("h2", null, "Jutsu Training"), /*#__PURE__*/React.createElement("p", null, trainingData.jutsuTrainingInfo), playerData.hasTeam && playerData.hasTeamBoostTraining ? /*#__PURE__*/React.createElement("em", null, "*Note: Your team has a chance at additional stat gains, these are not reflected above.") : /*#__PURE__*/React.createElement(React.Fragment, null))));
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
      playerData: playerData,
      trainingData: trainingData
    });
  }
  return /*#__PURE__*/React.createElement(React.Fragment, null, content);
}
function Option({
  title,
  children
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
    style: styles.option
  }, /*#__PURE__*/React.createElement("h2", {
    className: "themeHeader"
  }, title), children);
}

//Displays Training Selection Input {TrainingOption}
function SelectTrainingPanel({
  playerData,
  headers
}) {
  // TODO: I think this is a heavy calculation for a react component so might need to change this in the future
  //Only works on words with _ separators 
  function capitalize(word) {
    const arr = word.replace("_", " ").split(" ");
    for (var i = 0; i < arr.length; i++) {
      arr[i] = arr[i].charAt(0).toUpperCase() + arr[i].slice(1);
    }
    const result = arr.join(" ");
    return result;
  }
  let styleContainer = {
    display: 'flex',
    flexWrap: 'wrap'
  };
  let tempkey = 0; //for child elements

  return /*#__PURE__*/React.createElement("div", {
    style: styleContainer
  }, /*#__PURE__*/React.createElement(Option, {
    key: 0,
    title: "Skills"
  }, /*#__PURE__*/React.createElement("form", {
    action: headers.selfLink,
    method: "post"
  }, /*#__PURE__*/React.createElement("select", {
    name: "skill"
  }, playerData.validSkillsArray.map(skillName => {
    return /*#__PURE__*/React.createElement("option", {
      key: tempkey++,
      value: skillName
    }, capitalize(skillName));
  })), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("input", {
    type: "submit",
    name: "train_type",
    value: "Short"
  }), /*#__PURE__*/React.createElement("input", {
    type: "submit",
    name: "train_type",
    value: "Long"
  }), /*#__PURE__*/React.createElement("input", {
    type: "submit",
    name: "train_type",
    value: "Extended"
  }))), /*#__PURE__*/React.createElement(Option, {
    key: 1,
    title: "Attributes"
  }, /*#__PURE__*/React.createElement("form", {
    action: headers.selfLink,
    method: "post"
  }, /*#__PURE__*/React.createElement("select", {
    name: "attributes"
  }, playerData.validAttributesArray.map(attributeName => {
    return /*#__PURE__*/React.createElement("option", {
      key: tempkey++,
      value: attributeName
    }, capitalize(attributeName));
  })), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("input", {
    type: "submit",
    name: "train_type",
    value: "Short"
  }), /*#__PURE__*/React.createElement("input", {
    type: "submit",
    name: "train_type",
    value: "Long"
  }), /*#__PURE__*/React.createElement("input", {
    type: "submit",
    name: "train_type",
    value: "Extended"
  }))), /*#__PURE__*/React.createElement(Option, {
    key: 2,
    title: "Jutsu"
  }, /*#__PURE__*/React.createElement("form", {
    action: headers.selfLink,
    method: "post"
  }, /*#__PURE__*/React.createElement("select", {
    name: "jutsu"
  }, Object.keys(playerData.allPlayerJutsu).map(key => {
    const item = playerData.allPlayerJutsu[key].name;
    const id = playerData.allPlayerJutsu[key].id;
    const level = playerData.allPlayerJutsu[key].level;
    if (level <= playerData.jutsuMaxLevel) {
      return /*#__PURE__*/React.createElement("option", {
        key: key,
        value: id
      }, item);
    } else {
      return null;
    }
  })), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("input", {
    type: "submit",
    name: "train_type",
    value: "Train"
  }))), playerData.bloodlineID != 0 && playerData.hasTrainableBLJutsu ? /*#__PURE__*/React.createElement(Option, {
    key: 3,
    title: "Bloodline Jutsu"
  }, /*#__PURE__*/React.createElement("form", {
    action: headers.selfLink,
    method: "post"
  }, /*#__PURE__*/React.createElement("select", {
    name: "bloodline_jutsu"
  }, Object.keys(playerData.allPlayerBloodlineJutsu).map(key => {
    const item = playerData.allPlayerBloodlineJutsu[key].name;
    const id = playerData.allPlayerBloodlineJutsu[key].id;
    const level = playerData.allPlayerBloodlineJutsu[key].level;
    if (level <= playerData.jutsuMaxLevel) {
      return /*#__PURE__*/React.createElement("option", {
        key: key,
        value: id
      }, item);
    } else {
      return null;
    }
  })), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("input", {
    type: "submit",
    name: "train_type",
    value: "Train"
  }))) : /*#__PURE__*/React.createElement(React.Fragment, null));
}
function Training({
  playerData,
  trainingData,
  headers
}) {
  const combinedStyles = {
    ...styles.bgColor,
    ...styles.body
  };
  return /*#__PURE__*/React.createElement("div", {
    style: combinedStyles,
    id: "TrainingContainer"
  }, /*#__PURE__*/React.createElement(DetailPanel, {
    playerData: playerData,
    trainingData: trainingData,
    headers: headers
  }), !playerData.hasActiveTraining ? /*#__PURE__*/React.createElement(SelectTrainingPanel, {
    playerData: playerData,
    headers: headers
  }) : /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("h2", {
    style: styles.header
  }, " ", trainingData.trainType, " Training"), /*#__PURE__*/React.createElement("p", null, trainingData.trainingDisplay), /*#__PURE__*/React.createElement("p", {
    id: "train_time_remaining"
  }, trainingData.timeRemaining, " remaining..."), !headers.isSetCancelTraining ? /*#__PURE__*/React.createElement("button", null, /*#__PURE__*/React.createElement("a", {
    href: headers.selfLink + "&cancel_training=1"
  }, "Cancel Training")) : /*#__PURE__*/React.createElement(React.Fragment, null)));
}
window.Training = Training; //man I don't even know what this does