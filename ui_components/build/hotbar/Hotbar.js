import { apiFetch } from "../utils/network.js";

// Initialize
function Hotbar({
  links,
  userAPIData
}) {
  // Hooks
  const [playerData, setPlayerData] = React.useState(userAPIData.playerData);
  const [aiData, setAIData] = React.useState(userAPIData.aiData);
  const [missionData, setMissionData] = React.useState(userAPIData.missionData);
  const [quickType, setQuickType] = React.useState("training");
  const [displayHotbar, toggleHotbarDisplay] = React.useState(false);
  const [displayKeybinds, toggleKeybindDisplay] = React.useState(false);
  const trainingFlag = React.useRef(0);
  const specialFlag = React.useRef(0);
  const battleFlag = React.useRef(null);
  const quickFormRef = React.useRef(null);

  // API
  function getPlayerData() {
    apiFetch(links.user_api, {
      request: 'getPlayerData'
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      } else {
        setPlayerData(response.data.playerData);
        checkNotificationFlags(response.data.playerData.training, response.data.playerData.special, response.data.playerData.battle);
      }
    });
  }
  function quickSelectOnChange(event) {
    setQuickType(event.target.selectedOptions[0].getAttribute('data-state'));
  }
  function trainingSelectOnChange(event) {
    event.target.setAttribute("name", event.target.selectedOptions[0].getAttribute('data-name'));
  }
  function quickSubmitOnClick() {
    quickFormRef.current.submit();
  }
  function setKeybindsOnClick() {
    toggleKeybindDisplay(!displayKeybinds);
  }
  function hotbarToggle() {
    toggleHotbarDisplay(!displayHotbar);
  }
  function checkNotificationFlags(training, special, battle) {
    if (training == '0' && trainingFlag.current != '0') {
      createNotification("Training Complete!");
    }
    trainingFlag.current = training;
    if (special == '0' && specialFlag.current != '0') {
      createNotification("Special Mission Complete!");
    }
    specialFlag.current = special;
    if (battle != '0' && battleFlag.current == '0') {
      createNotification("You are in battle!");
    }
    battleFlag.current = battle;
  }
  function createNotification(message) {
    if (!window.Notification) {
      console.log('Browser does not support notifications.');
    } else {
      // check if permission is already granted
      if (Notification.permission === 'granted') {
        // show notification here
        var notify = new Notification('Shinobi Chronicles', {
          body: message
        });
      } else {
        // request permission from user
        Notification.requestPermission().then(function (p) {
          if (p === 'granted') {
            // show notification here
            var notify = new Notification('Shinobi Chronicles', {
              body: message
            });
          } else {
            console.log('User blocked notifications.');
          }
        }).catch(function (err) {
          console.error(err);
        });
      }
    }
  }

  // Content
  function displayToggle() {
    return /*#__PURE__*/React.createElement("div", {
      id: "hb_toggle",
      onClick: hotbarToggle,
      className: "t-hover ft-s ft-c1 ft-default"
    }, "Toggle Hotbar");
  }
  function displayQuickSection(playerData, missionData, aiData, link_data, quickType) {
    return /*#__PURE__*/React.createElement("div", {
      id: "hb_quick_section",
      className: "hb_section"
    }, /*#__PURE__*/React.createElement("div", {
      className: "hb_divider"
    }, /*#__PURE__*/React.createElement("div", {
      className: "hb_quick_title ft-s ft-c1 ft-min ft-b"
    }, "QUICK MENU"), /*#__PURE__*/React.createElement("div", null, quickType == "training" && /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("form", {
      id: "hb_quick_form",
      ref: quickFormRef,
      action: link_data.training,
      method: "post"
    }, /*#__PURE__*/React.createElement("input", {
      id: "hb_quick_submit",
      onClick: quickSubmitOnClick,
      form: "hb_quick_form",
      className: "hb_button button-bar_large t-hover",
      type: "button",
      value: "TRAINING"
    }))), quickType == "arena" && /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("form", {
      id: "hb_quick_form",
      ref: quickFormRef,
      action: link_data.arena,
      method: "get"
    }, /*#__PURE__*/React.createElement("input", {
      id: "hb_quick_submit",
      onClick: quickSubmitOnClick,
      form: "hb_quick_form",
      className: "hb_button button-bar_large t-hover",
      type: "button",
      value: "ARENA"
    }))), quickType == "missions" && /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("form", {
      id: "hb_quick_form",
      ref: quickFormRef,
      action: link_data.mission,
      method: "get"
    }, /*#__PURE__*/React.createElement("input", {
      id: "hb_quick_submit",
      onClick: quickSubmitOnClick,
      form: "hb_quick_form",
      className: "hb_button button-bar_large t-hover",
      type: "button",
      value: "MISSIONS"
    }))), quickType == "specialmissions" && /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("form", {
      id: "hb_quick_form",
      ref: quickFormRef,
      action: link_data.specialmissions,
      method: "get"
    }, /*#__PURE__*/React.createElement("input", {
      id: "hb_quick_submit",
      onClick: quickSubmitOnClick,
      form: "hb_quick_form",
      className: "hb_button button-bar_large t-hover",
      type: "button",
      value: "SPECIAL"
    }))), quickType == "ramen" && /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("form", {
      id: "hb_quick_form",
      ref: quickFormRef,
      action: link_data.healingShop,
      method: "get"
    }, /*#__PURE__*/React.createElement("input", {
      id: "hb_quick_submit",
      onClick: quickSubmitOnClick,
      form: "hb_quick_form",
      className: "hb_button button-bar_LARGE t-hover",
      type: "button",
      value: "RAMEN"
    }))))), /*#__PURE__*/React.createElement("div", {
      className: "hb_divider"
    }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("select", {
      id: "hb_category_select",
      onChange: quickSelectOnChange,
      form: "hb_quick_form",
      name: "id",
      className: "hb_quick_select"
    }, /*#__PURE__*/React.createElement("option", {
      "data-state": "training",
      value: link_data.training.slice(link_data.training.indexOf('=') + 1)
    }, "Training"), /*#__PURE__*/React.createElement("option", {
      "data-state": "arena",
      value: link_data.arena.slice(link_data.training.indexOf('=') + 1)
    }, "Arena"), /*#__PURE__*/React.createElement("option", {
      "data-state": "missions",
      value: link_data.mission.slice(link_data.training.indexOf('=') + 1)
    }, "Missions"), /*#__PURE__*/React.createElement("option", {
      "data-state": "specialmissions",
      value: link_data.specialmissions.slice(link_data.training.indexOf('=') + 1)
    }, "Special Missions"), /*#__PURE__*/React.createElement("option", {
      "data-state": "ramen",
      value: link_data.healingShop.slice(link_data.training.indexOf('=') + 1)
    }, "Ramen"))), quickType == "training" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("select", {
      onChange: trainingSelectOnChange,
      form: "hb_quick_form",
      id: "hb_training_select",
      name: "skill",
      className: "hb_quick_select"
    }, /*#__PURE__*/React.createElement("optgroup", {
      label: "Skills"
    }, /*#__PURE__*/React.createElement("option", {
      "data-name": "skill",
      value: "taijutsu"
    }, "Taijutsu Skill"), /*#__PURE__*/React.createElement("option", {
      "data-name": "skill",
      value: "ninjutsu"
    }, "Ninjutsu Skill"), /*#__PURE__*/React.createElement("option", {
      "data-name": "skill",
      value: "genjutsu"
    }, "Genjutsu Skill"), playerData.has_bloodline == true && /*#__PURE__*/React.createElement("option", {
      "data-name": "skill",
      value: "bloodline"
    }, "Bloodline Skill")), /*#__PURE__*/React.createElement("optgroup", {
      label: "Attributes"
    }, /*#__PURE__*/React.createElement("option", {
      "data-name": "attributes",
      value: "speed"
    }, "Speed Skill"), /*#__PURE__*/React.createElement("option", {
      "data-name": "attributes",
      value: "cast_speed"
    }, "Cast Speed Skill")))), /*#__PURE__*/React.createElement("div", {
      id: "hb_time_container"
    }, /*#__PURE__*/React.createElement("label", {
      className: "hb_time_label ft-s ft-c1 ft-min ft-b"
    }, "TIME:"), /*#__PURE__*/React.createElement("select", {
      form: "hb_quick_form",
      id: "hb_time_select",
      name: "train_type",
      className: "hb_quick_select"
    }, /*#__PURE__*/React.createElement("option", {
      value: "Short"
    }, "Short"), /*#__PURE__*/React.createElement("option", {
      value: "Long"
    }, "Long"), /*#__PURE__*/React.createElement("option", {
      value: "Extended"
    }, "Extended")))), quickType == "arena" && /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("select", {
      form: "hb_quick_form",
      id: "hb_arena_select",
      name: "fight",
      className: "hb_quick_select"
    }, aiData && aiData.map(function (ai, i) {
      return /*#__PURE__*/React.createElement("option", {
        key: i,
        value: ai.ai_id
      }, ai.name);
    }))), quickType == "missions" && /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("select", {
      name: "start_mission",
      form: "hb_quick_form",
      id: "hb_missions_select",
      className: "hb_quick_select"
    }, missionData && missionData.map(function (mission, i) {
      return /*#__PURE__*/React.createElement("option", {
        key: i,
        value: mission.mission_id
      }, mission.name);
    }))), quickType == "specialmissions" && /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("select", {
      name: "start",
      form: "hb_quick_form",
      id: "hb_specialmissions_select",
      className: "hb_quick_select"
    }, /*#__PURE__*/React.createElement("option", {
      value: "easy"
    }, "Easy"), /*#__PURE__*/React.createElement("option", {
      value: "normal"
    }, "Normal"), /*#__PURE__*/React.createElement("option", {
      value: "hard"
    }, "Hard"), /*#__PURE__*/React.createElement("option", {
      value: "nightmare"
    }, "Nightmare"))), quickType == "ramen" && /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("select", {
      form: "hb_quick_form",
      id: "hb_ramen_select",
      name: "heal",
      className: "hb_quick_select"
    }, /*#__PURE__*/React.createElement("option", {
      value: "vegetable"
    }, "Vegetable"), /*#__PURE__*/React.createElement("option", {
      value: "pork"
    }, "Pork"), /*#__PURE__*/React.createElement("option", {
      value: "deluxe"
    }, "Deluxe"))))));
  }
  function displaySettingsSection(playerData) {
    return /*#__PURE__*/React.createElement("div", {
      id: "hb_settings_section",
      className: "hb_section"
    }, /*#__PURE__*/React.createElement("div", {
      className: "hb_divider"
    }, /*#__PURE__*/React.createElement("div", {
      className: "hb_settings_title ft-s ft-c1 ft-min ft-b"
    }, "SETTINGS (WIP)"), /*#__PURE__*/React.createElement("input", {
      id: "hb_settings_display",
      onClick: setKeybindsOnClick,
      className: "hb_button button-bar_large t-hover",
      type: "button",
      value: "SET KEYBINDS"
    })), /*#__PURE__*/React.createElement("div", {
      className: "hb_divider"
    }, /*#__PURE__*/React.createElement("div", {
      className: "hb_checkbox_wrapper"
    }, /*#__PURE__*/React.createElement("input", {
      id: "hb_alert_checkbox",
      type: "checkbox",
      className: "hb_checkbox",
      form: "hb_quick_form",
      name: "alert",
      value: "true"
    }), /*#__PURE__*/React.createElement("label", {
      className: "ft-s ft-c1 ft-min"
    }, "ENABLE ALERTS")), /*#__PURE__*/React.createElement("div", {
      className: "hb_checkbox_wrapper"
    }, /*#__PURE__*/React.createElement("input", {
      id: "hb_hotkey_checkbox",
      type: "checkbox",
      className: "hb_checkbox",
      value: "wow"
    }), /*#__PURE__*/React.createElement("label", {
      className: "ft-s ft-c1 ft-min"
    }, "ENABLE HOTKEYS"))));
  }
  function displaySetKeybinds() {
    return /*#__PURE__*/React.createElement("div", {
      id: "hb_keybind_modal",
      className: displayKeybinds ? "" : "minimize"
    }, /*#__PURE__*/React.createElement("img", {
      src: "images/v2/decorations/nwbigcorner.png",
      className: "nwbigcorner"
    }), /*#__PURE__*/React.createElement("img", {
      src: "images/v2/decorations/nebigcorner.png",
      className: "nebigcorner"
    }), /*#__PURE__*/React.createElement("img", {
      src: "images/v2/decorations/sebigcorner.png",
      className: "sebigcorner"
    }), /*#__PURE__*/React.createElement("img", {
      src: "images/v2/decorations/swbigcorner.png",
      className: "swbigcorner"
    }), /*#__PURE__*/React.createElement("div", {
      className: "t-center ft-min ft-s ft-c1 ft-b"
    }, "KEYBINDS"));
  }

  // Misc
  function handleErrors(errors) {
    console.warn(errors);
    //setFeedback([errors, 'info']);
  }

  // Initialize
  React.useEffect(() => {}, []);

  // Display
  return /*#__PURE__*/React.createElement("div", {
    id: "hotbar",
    className: displayHotbar ? "jc-center d-flex" : "jc-center d-flex minimize"
  }, /*#__PURE__*/React.createElement("div", {
    className: "hb_inner"
  }, /*#__PURE__*/React.createElement("div", {
    className: "hb_section_spacer"
  }), displayToggle(), playerData && displayQuickSection(playerData, missionData, aiData, links, quickType), playerData && displaySettingsSection(playerData), displaySetKeybinds()));
}
window.Hotbar = Hotbar;