import { useModal } from "../utils/modalContext.js";
import { RenderPurchaseConfirmation } from "./PurchaseConfirmation.js";
export function CharacterChanges({
  playerData
}) {
  const {
    openModal
  } = useModal();
  const stats = ['ninjutsu', 'taijutsu', 'genjutsu', 'speed', 'cast_speed', 'intelligence', 'willpower'];
  const [newUsername, setName] = React.useState(playerData.user_name);
  const [statResetName, setStatReset] = React.useState(stats[0]);
  const [statResetAmount, setStatRestAmount] = React.useState(100);
  const handleNameFieldChange = event => {
    setName(event.target.value);
  };
  const handleStatResetChange = event => {
    setStatReset(event.target.value);
  };
  const handleStateResetAmountChange = event => {
    setStatRestAmount(event.target.value);
  };
  React.useEffect(() => {
    const testInterval = setInterval(() => {
      console.log(statResetName + ' @ ' + statResetAmount + '%');
    }, 1000);
    return () => clearInterval(testInterval);
  });
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "purchaseContainer"
  }, /*#__PURE__*/React.createElement("div", {
    className: "box-secondary halfWidth center"
  }, /*#__PURE__*/React.createElement("b", null, "Reset Character"), "You can reset your to a level 1 Akademi-sai.", /*#__PURE__*/React.createElement("br", null), "This change is free and can not be reversed.", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement(RenderPurchaseConfirmation, {
    text: "Are you certain you wish to reset your character?"
  })), /*#__PURE__*/React.createElement("div", {
    className: "box-secondary halfWidth center"
  }, /*#__PURE__*/React.createElement("b", null, "Individual Stat Reset"), "You can reset an individual stat to free up space in your total stat cap.", /*#__PURE__*/React.createElement("br", null), "This change is free and can be used to allow further training.", /*#__PURE__*/React.createElement("select", {
    className: "purchaseSelectField",
    onChange: handleStatResetChange
  }, stats.map(function (name) {
    return /*#__PURE__*/React.createElement("option", {
      key: name,
      value: name
    }, name.replace('_', ' '));
  })), /*#__PURE__*/React.createElement("select", {
    className: "purchaseSelectField",
    onChange: handleStateResetAmountChange
  }, [100, 90, 80, 70, 60, 50, 40, 30, 20, 10].map(function (percentAmount) {
    return /*#__PURE__*/React.createElement("option", {
      key: percentAmount,
      value: percentAmount
    }, percentAmount, "%");
  })), /*#__PURE__*/React.createElement(RenderPurchaseConfirmation, {
    text: "Are you certain you wish to reset your X stat?"
  }))), /*#__PURE__*/React.createElement("table", {
    className: "table"
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", {
    colSpan: "2"
  }, "Character Changes")), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, "Reset"), /*#__PURE__*/React.createElement("th", null, "Individual Stat Reset")), /*#__PURE__*/React.createElement("tr", {
    className: "center"
  }, /*#__PURE__*/React.createElement("td", null, "You can reset your character to a level 1 Akademi-sai.", /*#__PURE__*/React.createElement("br", null), "This change is ", /*#__PURE__*/React.createElement("b", null, "free"), " and ", /*#__PURE__*/React.createElement("u", null, "is not"), " reversible.", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("div", {
    className: "purchase_button",
    onClick: () => openModal({
      header: 'Purchase Confirmation',
      text: 'Are you sure you would like to reset your character?',
      ContentComponent: null,
      onConfirm: () => console.log("purchase....")
    })
  }, "purchase")), /*#__PURE__*/React.createElement("td", null, "Your can reset an individual stat, freeing up space in your total stat cap to train something else higher.", /*#__PURE__*/React.createElement("br", null), "This purchase is ", /*#__PURE__*/React.createElement("b", null, "free"), " and ", /*#__PURE__*/React.createElement("u", null, "is not"), " reversible.", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("div", {
    className: "purchase_button",
    onClick: () => openModal({
      header: 'Purchase Confirmation',
      text: 'Are you sure you would like to reset your character?',
      ContentComponent: null,
      onConfirm: () => {
        playerData.premiumCredits -= 5;
        openModal({
          header: 'Confirmation',
          text: 'Stat reset....',
          ContentComponent: null,
          onConfirm: () => window.location.reload()
        });
      }
    })
  }, "purchase"))), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, "Reset AI Battle Counts"), /*#__PURE__*/React.createElement("th", null, "Reset PvP Battle Counts")), /*#__PURE__*/React.createElement("tr", {
    className: "center"
  }, /*#__PURE__*/React.createElement("td", null, "This will reset your AI Wins and AI Losses to 0.", /*#__PURE__*/React.createElement("br", null)), /*#__PURE__*/React.createElement("td", null, "This will reset your PvP Wins and PvP Losses to 0.", /*#__PURE__*/React.createElement("br", null))), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, "Username Change"), /*#__PURE__*/React.createElement("th", null, "Gender Change")), /*#__PURE__*/React.createElement("tr", {
    className: "center"
  }, /*#__PURE__*/React.createElement("td", null, "Your first username change is free and X Ancient Kunai afterward.", /*#__PURE__*/React.createElement("br", null), "Free changes: Y", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("input", {
    type: "text",
    onChange: handleNameFieldChange
  }), /*#__PURE__*/React.createElement("div", {
    className: "purchase_button",
    onClick: () => openModal({
      header: 'Confirm Purchase',
      text: 'Are you certain you would like to change your username?',
      ContentComponent: null,
      onConfirm: () => openModal({
        header: 'Purchase Confirmed',
        text: 'You have successfully changed your name to ' + newUsername,
        ContentComponent: null
      })
    })
  }, "purchase")), /*#__PURE__*/React.createElement("td", null)))));
}