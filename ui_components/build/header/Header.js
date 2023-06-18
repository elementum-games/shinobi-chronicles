import { apiFetch } from "../utils/network.js";
function Header({
  links,
  navigationAPIData
}) {
  // Hooks
  const [headerMenuLinks, setHeaderMenuLinks] = React.useState(navigationAPIData.headerMenu);
  const [serverTime, setServerTime] = React.useState(null);

  // API
  function getHeaderMenu() {
    apiFetch(links.navigation_api, {
      request: 'getHeaderMenu'
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      setHeaderMenuLinks(response.data.headerMenu);
    });
  }
  // Utility
  function getCurrentTime() {
    const currentDate = new Date();
    const options = {
      weekday: 'long',
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    };
    const formattedDate = currentDate.toLocaleDateString('en-US', options);
    const formattedTime = currentDate.toLocaleTimeString('en-US', {
      hour12: true
    });
    setServerTime(formattedDate + ' - ' + formattedTime);
  }

  // Misc
  function handleErrors(errors) {
    console.warn(errors);
    //setFeedback([errors, 'info']);
  }

  // Initialize
  React.useEffect(() => {
    getCurrentTime();
    const timeInterval = setInterval(() => {
      getCurrentTime();
    }, 1000);
    return () => clearInterval(timeInterval);
  }, []);

  // Display
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "header_bar_left"
  }), headerMenuLinks && /*#__PURE__*/React.createElement("div", {
    className: "header_bar"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header_link_container d-flex"
  }, headerMenuLinks && headerMenuLinks.map(function (link, i) {
    return /*#__PURE__*/React.createElement("div", {
      key: i,
      className: "header_link_wrapper t-center"
    }, /*#__PURE__*/React.createElement("a", {
      href: link.url,
      className: "header_label ft-default ft-s ft-c5"
    }, link.title));
  }), /*#__PURE__*/React.createElement("div", {
    className: "header_time_label ft-default ft-s ft-c5"
  }, serverTime), /*#__PURE__*/React.createElement("div", {
    className: "header_logout_wrapper t-center"
  }, /*#__PURE__*/React.createElement("a", {
    href: links.logout_link,
    className: "header_logout_label ft-default ft-s"
  }, "LOGOUT")))));
}
window.Header = Header;