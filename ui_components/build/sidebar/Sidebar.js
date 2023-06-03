import { apiFetch } from "../utils/network.js";

// Initialize
function Sidebar({
  linkData
}) {
  // Hooks
  const [user_menu, setUserMenu] = React.useState(null);
  const [activity_menu, setActivityMenu] = React.useState(null);
  const [village_menu, setVillageMenu] = React.useState(null);
  const queryParameters = new URLSearchParams(window.location.search);
  const pageID = React.useRef(queryParameters.get("id"));

  // API
  function getSidebarLinks() {
    apiFetch(linkData.sidebar_api, {
      request: 'getSidebarLinks'
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      } else {
        setUserMenu(response.data.userMenu);
        setActivityMenu(response.data.activityMenu);
        setVillageMenu(response.data.villageMenu);
      }
    });
  }
  // Utility
  function sbLinkOnClick(event) {
    window.location.href = event.currentTarget.getAttribute("href");
  }
  function logoutOnClick(event) {
    window.location.href = event.currentTarget.getAttribute("href");
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
      return /*#__PURE__*/React.createElement("div", {
        onClick: sbLinkOnClick,
        href: link.url,
        key: i,
        className: pageID.current == link.id ? "sb_link_wrapper selected t-center ft-small ft-s ft-c3" : "sb_link_wrapper t-center ft-small ft-s ft-c3"
      }, /*#__PURE__*/React.createElement("label", {
        className: "sb_label"
      }, link.title));
    }), section_data.length % 2 != 0 && /*#__PURE__*/React.createElement("div", {
      className: "sb_link_filler"
    })));
  }
  function displayAvatar(avatar_link) {
    return /*#__PURE__*/React.createElement("div", {
      className: "sb_avatar_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "sb_avatar_wrapper"
    }, /*#__PURE__*/React.createElement("img", {
      className: "sb_avatar_img",
      src: avatar_link
    })));
  }

  // Misc
  function handleErrors(errors) {
    console.warn(errors);
    //setFeedback([errors, 'info']);
  }

  // Initialize
  React.useEffect(() => {
    getSidebarLinks();
  }, []);

  // Display
  return /*#__PURE__*/React.createElement("div", {
    id: "sidebar"
  }, displayAvatar(linkData.avatar_link), user_menu && displaySection(user_menu, "Player Menu"), activity_menu && displaySection(activity_menu, "Action Menu"), village_menu && displaySection(village_menu, "Village Menu"));
}
window.Sidebar = Sidebar;