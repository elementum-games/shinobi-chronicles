import { apiFetch } from "../utils/network.js";

// Initialize
function Sidebar({
  linkData,
  logout_timer
}) {
  // Hooks
  const [user_menu, setUserMenu] = React.useState(null);
  const [activity_menu, setActivityMenu] = React.useState(null);
  const [village_menu, setVillageMenu] = React.useState(null);
  const [staff_menu, setStaffMenu] = React.useState(null);
  const [player_data, setPlayerData] = React.useState(null);
  const [regen_time, setRegenTime] = React.useState(null);
  const [regen_offset, setRegenOffset] = React.useState(null);
  const [logout_time, setLogoutTime] = React.useState(null);
  const regen_time_var = React.useRef(0);
  const logout_time_var = React.useRef(logout_timer);
  const queryParameters = new URLSearchParams(window.location.search);
  const pageID = React.useRef(queryParameters.get("id"));

  // API
  function getSidebarLinks() {
    apiFetch(linkData.navigation_api, {
      request: 'getNavigationLinks'
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      } else {
        setUserMenu(response.data.userMenu);
        setActivityMenu(response.data.activityMenu);
        setVillageMenu(response.data.villageMenu);
        setStaffMenu(response.data.staffMenu);
      }
    });
  }
  function getPlayerData() {
    apiFetch(linkData.user_api, {
      request: 'getPlayerData'
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      } else {
        setPlayerData(response.data.playerData);
        setRegenTime(response.data.playerData.regen_time);
        setRegenOffset(calculateRegenOffset(response.data.playerData.regen_time));
        regen_time_var.current = response.data.playerData.regen_time;
      }
    });
  }
  // Utility
  function handleRegen() {
    if (regen_time_var.current <= 0 || regen_time_var.current == 30) {
      getPlayerData();
    } else {
      regen_time_var.current = regen_time_var.current - 1;
      setRegenTime(regen_time => regen_time - 1);
      setRegenOffset(calculateRegenOffset(regen_time_var.current));
    }
  }
  function calculateRegenOffset(time) {
    var percent = (time / 60 * 100).toFixed(0);
    var offset = 126 - 126 * percent / 100;
    return offset;
  }
  function handleLogout() {
    logout_time_var.current--;
    setLogoutTime(logout_time_var.current);
  }
  function formatLogoutTimer(ticks) {
    var hours = Math.floor(ticks / 3600);
    var minutes = Math.floor(ticks % 3600 / 60);
    var seconds = ticks % 60;
    var formattedHours = hours.toString().padStart(2, '0');
    var formattedMinutes = minutes.toString().padStart(2, '0');
    var formattedSeconds = seconds.toString().padStart(2, '0');
    return formattedHours + ':' + formattedMinutes + ':' + formattedSeconds;
  }

  // Content
  function displaySection(section_data, title) {
    return /*#__PURE__*/React.createElement("div", {
      className: "sb_section_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "sb_header_bar d-flex"
    }, /*#__PURE__*/React.createElement("div", {
      className: "sb_header_image_wrapper"
    }, /*#__PURE__*/React.createElement("img", {
      src: "/images/v2/icons/menudecor.png",
      className: "sb_header_image"
    })), /*#__PURE__*/React.createElement("div", {
      className: "sb_header_text_wrapper ft-p ft-c2 ft-b ft-medium"
    }, title)), /*#__PURE__*/React.createElement("div", {
      className: "sb_link_container d-flex"
    }, section_data && section_data.map(function (link, i) {
      return /*#__PURE__*/React.createElement("a", {
        key: i,
        href: link.url,
        className: pageID.current == link.id ? "sb_link_wrapper selected t-center ft-small ft-s ft-c3" : "sb_link_wrapper t-center ft-small ft-s ft-c3"
      }, /*#__PURE__*/React.createElement("label", {
        className: "sb_label"
      }, link.title));
    }), section_data.length % 2 != 0 && /*#__PURE__*/React.createElement("div", {
      className: "sb_link_filler"
    })));
  }
  function displayCharacterSection(player_data, regen_time, regen_offset) {
    const health_width = Math.round(player_data.health / player_data.max_health * 100);
    const chakra_width = Math.round(player_data.chakra / player_data.max_chakra * 100);
    const stamina_width = Math.round(player_data.stamina / player_data.max_stamina * 100);
    return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
      className: "sb_avatar_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "sb_avatar_wrapper"
    }, /*#__PURE__*/React.createElement("img", {
      className: "sb_avatar_img",
      src: player_data.avatar_link
    }))), /*#__PURE__*/React.createElement("div", {
      className: "sb_resources d-in_block"
    }, /*#__PURE__*/React.createElement("div", {
      className: "sb_name_container t-left d-flex"
    }, /*#__PURE__*/React.createElement("div", {
      className: "d-in_block"
    }, /*#__PURE__*/React.createElement("div", {
      className: "ft-p ft-c1 ft-xlarge ft-b"
    }, player_data.user_name), /*#__PURE__*/React.createElement("div", {
      className: "ft-s ft-c1 ft-default"
    }, player_data.rank_name, " lvl ", player_data.level)), /*#__PURE__*/React.createElement("div", {
      style: {
        width: "100%"
      },
      className: "d-in_block"
    }, /*#__PURE__*/React.createElement("div", {
      id: "sb_regentimer"
    }, /*#__PURE__*/React.createElement("svg", {
      height: "40",
      width: "40",
      viewBox: "0 0 50 50"
    }, /*#__PURE__*/React.createElement("circle", {
      id: "sb_regentimer_circle",
      stroke: "#7C88C3",
      cx: "24.5",
      cy: "24",
      r: "20",
      strokeWidth: "4",
      "stroke-mitterlimit": "0",
      fill: "none",
      strokeDasharray: "126",
      strokeDashoffset: regen_offset,
      transform: "rotate(-90, 24.5, 24)"
    }), /*#__PURE__*/React.createElement("text", {
      id: "sb_regentimer_text",
      className: "ft-s ft-b ft-large",
      x: "50%",
      y: "50%",
      textAnchor: "middle",
      dominantBaseline: "middle"
    }, regen_time))))), /*#__PURE__*/React.createElement("div", {
      className: "sb_resourceContainer"
    }, /*#__PURE__*/React.createElement("div", {
      id: "sb_health",
      className: "sb_resourceBarOuter"
    }, /*#__PURE__*/React.createElement("img", {
      className: "sb_resource_corner_left",
      src: "images/v2/decorations/barrightcorner.png"
    }), /*#__PURE__*/React.createElement("label", {
      className: "sb_innerResourceBarLabel"
    }, player_data.health, " / ", player_data.max_health), /*#__PURE__*/React.createElement("div", {
      className: "sb_health sb_fill",
      style: {
        width: health_width + "%"
      }
    }, /*#__PURE__*/React.createElement("svg", {
      className: "sb_resource_highlight_container"
    }, /*#__PURE__*/React.createElement("svg", {
      className: "sb_resource_highlight_wrapper",
      viewBox: "0 0 50 50"
    }, /*#__PURE__*/React.createElement("polygon", {
      x: "50",
      points: "20,25 0,5 5,5 25,25 5,45 0,45",
      id: "sb_health_highlight",
      className: "sb_resource_highlight"
    })))), /*#__PURE__*/React.createElement("div", {
      className: "sb_health sb_preview"
    }), /*#__PURE__*/React.createElement("img", {
      className: "sb_resource_corner_right",
      src: "images/v2/decorations/barrightcorner.png"
    }))), /*#__PURE__*/React.createElement("div", {
      className: "sb_resourceContainer"
    }, /*#__PURE__*/React.createElement("div", {
      id: "sb_chakra",
      className: "sb_resourceBarOuter"
    }, /*#__PURE__*/React.createElement("img", {
      className: "sb_resource_corner_left",
      src: "images/v2/decorations/barrightcorner.png"
    }), /*#__PURE__*/React.createElement("label", {
      className: "sb_innerResourceBarLabel"
    }, player_data.chakra, " / ", player_data.max_chakra), /*#__PURE__*/React.createElement("div", {
      className: "sb_chakra sb_fill",
      style: {
        width: chakra_width + "%"
      }
    }, /*#__PURE__*/React.createElement("svg", {
      className: "sb_resource_highlight_container"
    }, /*#__PURE__*/React.createElement("svg", {
      className: "sb_resource_highlight_wrapper",
      viewBox: "0 0 50 50"
    }, /*#__PURE__*/React.createElement("polygon", {
      x: "50",
      points: "20,25 0,5 5,5 25,25 5,45 0,45",
      id: "sb_chakra_highlight",
      className: "sb_resource_highlight"
    })))), /*#__PURE__*/React.createElement("div", {
      className: "sb_chakra sb_preview"
    }), /*#__PURE__*/React.createElement("img", {
      className: "sb_resource_corner_right",
      src: "images/v2/decorations/barrightcorner.png"
    }))), /*#__PURE__*/React.createElement("div", {
      className: "sb_resourceContainer"
    }, /*#__PURE__*/React.createElement("div", {
      id: "sb_stamina",
      className: "sb_resourceBarOuter"
    }, /*#__PURE__*/React.createElement("img", {
      className: "sb_resource_corner_left",
      src: "images/v2/decorations/barrightcorner.png"
    }), /*#__PURE__*/React.createElement("label", {
      className: "sb_innerResourceBarLabel"
    }, player_data.stamina, " / ", player_data.max_stamina), /*#__PURE__*/React.createElement("div", {
      className: "sb_stamina sb_fill",
      style: {
        width: stamina_width + "%"
      }
    }, /*#__PURE__*/React.createElement("svg", {
      className: "sb_resource_highlight_container"
    }, /*#__PURE__*/React.createElement("svg", {
      className: "sb_resource_highlight_wrapper",
      viewBox: "0 0 50 50"
    }, /*#__PURE__*/React.createElement("polygon", {
      x: "50",
      points: "20,25 0,5 5,5 25,25 5,45 0,45",
      id: "sb_stamina_highlight",
      className: "sb_resource_highlight"
    })))), /*#__PURE__*/React.createElement("div", {
      className: "sb_stamina sb_preview"
    }), /*#__PURE__*/React.createElement("img", {
      className: "sb_resource_corner_right",
      src: "images/v2/decorations/barrightcorner.png"
    })))));
  }
  function displayLogout(logout_link, logout_time) {
    return /*#__PURE__*/React.createElement("div", {
      className: "sb_logout_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "sb_logout_timer_wrapper"
    }, formatLogoutTimer(logout_time)), /*#__PURE__*/React.createElement("div", {
      className: "sb_logout_button_wrapper"
    }, /*#__PURE__*/React.createElement("input", {
      className: "sb_logout_button button-bar_large t-hover",
      type: "button",
      value: "LOGOUT"
    })), /*#__PURE__*/React.createElement("img", {
      className: "swcorner",
      src: "images/v2/decorations/swcorner.png"
    }), /*#__PURE__*/React.createElement("img", {
      className: "sidebar_secorner",
      src: "images/v2/decorations/secorner.png"
    }));
  }

  // Misc
  function handleErrors(errors) {
    console.warn(errors);
    //setFeedback([errors, 'info']);
  }

  // Initialize
  React.useEffect(() => {
    getPlayerData();
    getSidebarLinks();
    setLogoutTime(logout_time_var.current);
    console.log(logout_time_var.current);
    const regenInterval = setInterval(() => {
      handleLogout();
      handleRegen();
    }, 1000);
    return () => clearInterval(regenInterval);
  }, []);

  // Display
  return /*#__PURE__*/React.createElement("div", {
    id: "sidebar"
  }, player_data && displayCharacterSection(player_data, regen_time, regen_offset), user_menu && displaySection(user_menu, "Player Menu"), activity_menu && displaySection(activity_menu, "Action Menu"), village_menu && displaySection(village_menu, "Village Menu"), staff_menu && displaySection(staff_menu, "Staff Menu"), displayLogout(linkData.logout_link, logout_time));
}
window.Sidebar = Sidebar;