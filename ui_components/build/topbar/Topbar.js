import { apiFetch } from "../utils/network.js";

// Initialize
function Topbar({
  linkData
}) {
  // Hooks - WIP notifications

  // API - WIP notifications

  // Utility

  // Content
  function displayTopbar() {
    return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
      className: "topbar_left"
    }), /*#__PURE__*/React.createElement("div", {
      className: "topbar_right d-flex"
    }, /*#__PURE__*/React.createElement("div", {
      className: "topbar_inner_left"
    }), /*#__PURE__*/React.createElement("div", {
      className: "topbar_inner_center main_logo"
    }), /*#__PURE__*/React.createElement("div", {
      className: "topbar_inner_right"
    }, /*#__PURE__*/React.createElement("div", {
      className: "topbar_notifications_container d-flex"
    }))));
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
    id: "topbar",
    class: "d-flex"
  }, displayTopbar());
}
window.Topbar = Topbar;