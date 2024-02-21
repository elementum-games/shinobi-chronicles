import { apiFetch } from "../utils/network.js";
import { ModalProvider, useModal } from "../utils/modalContext.js";
import { CharacterChanges } from "./CharacterChanges.js";
function PremiumPage({
  APILinks,
  APIData,
  initialPage,
  genders,
  skills
}) {
  let costsLoaded = Date.now();
  const reloadCostsMS = 15 * 60 * 1000; // 15 minutes
  const reloadPlayerData = 30 * 1000; // 30 seconds

  const characterChanges = "character_changes";
  const bloodlines = "bloodlines";
  const forbiddenSeal = "forbidden_seal";
  const purchaseAK = "purchase_ak";
  const [page, setPage] = React.useState(initialPage);
  const [playerData, setPlayerData] = React.useState(APIData.playerData);
  const [costs, setCosts] = React.useState(APIData.costs);
  function handlePageChange(newPage) {
    if (newPage !== page) {
      setPage(newPage);
    }
  }
  function handleAPIErrors(errors) {
    console.warn(errors);
  }
  function handlePurchaseError() {}
  function handlePremiumPurchase(purchaseType, purchaseVars = {}) {
    apiFetch(APILinks.premium_shop, {
      request: 'Purchase',
      purchaseType: purchaseType,
      purchaseVars: purchaseVars
    }).then(response => {
      if (response.errors.length) {
        handleAPIErrors(response.errors);
        return response;
      } else {}
    });
  }
  function getPlayerData() {
    apiFetch(APILinks.user, {
      request: 'getPlayerData'
    }).then(response => {
      if (response.errors.length) {
        handleAPIErrors(response.errors);
        return;
      } else {
        setPlayerData(response.data.playerData);
      }
    });
  }
  function getCosts() {
    apiFetch(APILinks.premium_shop, {
      request: 'LoadCosts'
    }).then(response => {
      if (response.errors.length) {
        handleAPIErrors(response.errors);
        return;
      } else {
        setCosts(response.data.costs);
      }
    });
  }
  React.useEffect(() => {
    const dataInterval = setInterval(() => {
      getPlayerData();
      if (Date.now() - costsLoaded >= reloadCostsMS) {
        getCosts();
        costsLoaded = Date.now();
      }
    }, reloadPlayerData);
    return () => clearInterval(dataInterval);
  }, []);
  // Display
  return /*#__PURE__*/React.createElement(ModalProvider, null, /*#__PURE__*/React.createElement(MarketHeader, {
    playerData: playerData,
    pages: [characterChanges, bloodlines, forbiddenSeal, purchaseAK],
    page: page,
    handlePageChange: handlePageChange
  }), page === characterChanges && /*#__PURE__*/React.createElement(CharacterChanges, {
    handlePremiumPurchase: handlePremiumPurchase,
    playerData: playerData,
    costs: costs,
    genders: genders,
    skills: skills
  }), page === bloodlines && /*#__PURE__*/React.createElement(Bloodlines, null), page === forbiddenSeal && /*#__PURE__*/React.createElement(ForbiddenSeals, null), page === purchaseAK && /*#__PURE__*/React.createElement(PurchaseAK, null));
}
function MarketHeader({
  playerData,
  pages,
  page,
  handlePageChange
}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "box-primary"
  }, /*#__PURE__*/React.createElement(NavBar, {
    handlePageChange: handlePageChange,
    pages: pages,
    page: page
  }), /*#__PURE__*/React.createElement("p", {
    className: "center"
  }, "Welcome to the Ancient Market! The vendors you find here seek something ", /*#__PURE__*/React.createElement("b", null, "more valuable"), " than just yen.", /*#__PURE__*/React.createElement("br", null), "You can trade your ", /*#__PURE__*/React.createElement("em", null, "Ancient Kunai"), " to purchase and manage premium benefits.", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("br", null), "Ancient Kunai: ", playerData.premiumCredits.toLocaleString('US'), /*#__PURE__*/React.createElement("br", null), "\xA5", playerData.money.toLocaleString('US'))), /*#__PURE__*/React.createElement("br", null));
}
function NavBar({
  handlePageChange,
  pages,
  page
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "navigation_row"
  }, pages.map(function (name) {
    let buttonClass = name === page ? 'nav_button selected' : 'nav_button';
    return /*#__PURE__*/React.createElement("div", {
      key: name,
      className: buttonClass,
      onClick: () => handlePageChange(name)
    }, name.replace('_', ' '));
  }));
}
function Bloodlines() {
  return /*#__PURE__*/React.createElement("table", {
    className: "table"
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, "Bloodline"))));
}
function ForbiddenSeals() {
  return /*#__PURE__*/React.createElement("table", {
    className: "table"
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, "Forbidden Seal"))));
}
function PurchaseAK() {
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("table", {
    className: "table"
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, "Purchase Ancient Kunai")))), /*#__PURE__*/React.createElement(AKMarket, null));
}
function AKMarket() {
  return /*#__PURE__*/React.createElement("table", {
    className: "table"
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, "Anicnet Kunai Market"))));
}
window.PremiumPage = PremiumPage;