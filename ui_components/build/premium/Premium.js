import { apiFetch } from "../utils/network.js";
import { ModalProvider, useModal } from "../utils/modalContext.js";
function PremiumPage({
  page,
  playerData
}) {
  React.State = {
    currentPage: page
  };
  const characterChanges = "character_changes";
  const bloodlines = "bloodlines";
  const forbiddenSeal = "forbidden_seal";
  const purchaseAK = "purchase_ak";
  const [currentPage, setPage] = React.useState(page);
  function handlePageChange(newPage) {
    if (newPage !== currentPage) {
      setPage(newPage);
    }
  }
  return /*#__PURE__*/React.createElement(ModalProvider, null, /*#__PURE__*/React.createElement(MarketHeader, {
    playerData: playerData
  }), /*#__PURE__*/React.createElement(NavBar, {
    handlePageChange: handlePageChange,
    pages: [characterChanges, bloodlines, forbiddenSeal, purchaseAK]
  }), currentPage === characterChanges && /*#__PURE__*/React.createElement(CharacterChanges, null), currentPage === bloodlines && /*#__PURE__*/React.createElement(Bloodlines, null), currentPage === forbiddenSeal && /*#__PURE__*/React.createElement(ForbiddenSeals, null), currentPage === purchaseAK && /*#__PURE__*/React.createElement(PurchaseAK, null));
}
function MarketHeader({
  playerData
}) {
  return /*#__PURE__*/React.createElement("div", null, "Welcome to the ancient Market, where you can purchase premium features.", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("b", null, "Your Ancient Kunai:"), " ", playerData.premiumCredits);
}
function NavBar({
  handlePageChange,
  pages
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "navigation_row"
  }, pages.map(function (name) {
    return /*#__PURE__*/React.createElement("div", {
      key: name,
      className: "nav_button",
      onClick: () => handlePageChange(name)
    }, name.replace('_', ' '));
  }));
}
function CharacterChanges() {
  return /*#__PURE__*/React.createElement("div", null, "Character Changes");
}
function Bloodlines() {
  return /*#__PURE__*/React.createElement("div", null, "Bloodlines");
}
function ForbiddenSeals() {
  return /*#__PURE__*/React.createElement("div", null, "Forbidden Seals");
}
function PurchaseAK() {
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", null, "Purchase AK"), /*#__PURE__*/React.createElement(AKMarket, null));
}
function AKMarket() {
  return /*#__PURE__*/React.createElement("div", null, "AK Market");
}
window.PremiumPage = PremiumPage;