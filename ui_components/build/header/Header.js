import { apiFetch } from "../utils/network.js";
function Header({
  links,
  navigationAPIData,
  timeZone,
  updateMaintenance,
  scOpen
}) {
  // Hooks
  const [headerMenuLinks, setHeaderMenuLinks] = React.useState(navigationAPIData.headerMenu);
  const [serverTime, setServerTime] = React.useState(null);
  const [maintTime, setMaintTime] = React.useState(null);

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
  function getCurrentTime(timeZone) {
    const currentDate = new Date();
    const options = {
      timeZone: timeZone,
      weekday: 'long',
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    };
    const formattedDate = currentDate.toLocaleDateString('en-US', options);
    const formattedTime = currentDate.toLocaleTimeString('en-US', {
      hour12: true,
      timeZone: timeZone
    });
    setServerTime(formattedDate + ' - ' + formattedTime);
  }
  function calcMaintWindow(timeStamp, timeZone) {
    const currentDate = new Date();
    let timeRemaining = Math.floor(timeStamp - currentDate.getTime() / 1000);
    if (timeRemaining <= 0) {
      setMaintTime("SERVER CLOSED");
      return false;
    }
    let returnString = "Server Maintenance ";

    // Calc time remaining
    // Hours
    if (timeRemaining >= 3600) {
      let hours = Math.floor(timeRemaining / 3600);
      timeRemaining -= hours * 3600;
      if (hours < 10) {
        hours = "0" + hours;
      }
      returnString += hours + ":";
    } else {
      returnString += "00:";
    }
    // Minutes
    if (timeRemaining >= 60) {
      let minutes = Math.floor(timeRemaining / 60);
      timeRemaining -= minutes * 60;
      if (minutes < 10) {
        minutes = "0" + minutes;
      }
      returnString += minutes + ":";
    } else {
      returnString += "00:";
    }
    // Seconds
    if (timeRemaining < 10) {
      returnString += '0' + timeRemaining;
    } else {
      returnString += timeRemaining;
    }
    setMaintTime(returnString);
  }

  // Misc
  function handleErrors(errors) {
    console.warn(errors);
    //setFeedback([errors, 'info']);
  }

  // Initialize server time
  {
    !updateMaintenance && scOpen && React.useEffect(() => {
      getCurrentTime(timeZone);
      const timeInterval = setInterval(() => {
        getCurrentTime(timeZone);
      }, 1000);
      return () => clearInterval(timeInterval);
    }, []);
  }
  // Initialize maintenance countdown
  {
    updateMaintenance && scOpen && React.useEffect(() => {
      calcMaintWindow(updateMaintenance, timeZone);
      const timeInterval = setInterval(() => {
        calcMaintWindow(updateMaintenance, timeZone);
      }, 1000);
      return () => clearInterval(timeInterval);
    }, []);
  }

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
  }), !updateMaintenance && scOpen && /*#__PURE__*/React.createElement("div", {
    className: "header_time_label ft-default ft-s ft-c5"
  }, serverTime), updateMaintenance > 0 && scOpen && /*#__PURE__*/React.createElement("div", {
    className: "header_maint_label ft-default ft-s ft-c5"
  }, maintTime), !scOpen && /*#__PURE__*/React.createElement("div", {
    className: "header_maint_label ft-default ft-s ft-c5"
  }, "SERVER CLOSED"), /*#__PURE__*/React.createElement("div", {
    className: "header_logout_wrapper t-center"
  }, /*#__PURE__*/React.createElement("a", {
    href: links.logout_link,
    className: "header_logout_label ft-default ft-s"
  }, "LOGOUT")))));
}
window.Header = Header;