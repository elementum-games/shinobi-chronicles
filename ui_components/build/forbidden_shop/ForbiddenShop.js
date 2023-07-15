import { apiFetch } from "../utils/network.js";
import { clickOnEnter } from "../utils/uiHelpers.js";
function ForbiddenShop({
  links,
  eventData,
  playerInventory
}) {
  function echangeEventCurrency(event_name, currency_name, quantity) {
    apiFetch(links.forbiddenShopAPI, {
      request: 'exchangeEventCurrency',
      event_name: event_name,
      currency_name: currency_name,
      quantity: quantity
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      } else {}
    });
  }
  function exchangeForbiddenJutsuScroll(item_type, item_id) {
    apiFetch(links.forbiddenShopAPI, {
      request: 'exchangeForbiddenJutsuScroll',
      item_type: item_type,
      item_id: item_id
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      } else {}
    });
  }
  function handleErrors(errors) {
    console.warn(errors);
  }
  return /*#__PURE__*/React.createElement("div", {
    className: "forbidden_shop_container"
  }, /*#__PURE__*/React.createElement(ShopMenu, {
    ShopMenuButton: ShopMenuButton
  }), /*#__PURE__*/React.createElement(ScrollExchange, {
    playerInventory: playerInventory,
    forbiddenShopAPI: links.forbiddenShopAPI,
    eventData: eventData,
    userAPI: links.userAPI
  }), /*#__PURE__*/React.createElement(CurrencyExchange, {
    playerInventory: playerInventory,
    forbiddenShopAPI: links.forbiddenShopAPI,
    eventData: eventData,
    userAPI: links.userAPI
  }));
}
function ShopMenu({
  ShopMenuButton
}) {
  const [activeButtonName, setActiveButtonName] = React.useState(null);
  const [dialogueText, setDialogueText] = React.useState(null);
  function questionOneClick() {
    setDialogueText("...");
    setActiveButtonName("questionOne");
  }
  function questionTwoClick() {
    setDialogueText("... <span class='dialogue_highlight'>Akuji</span>. Nevermind the what.\n Now, you have something that\n belongs to me.");
    setActiveButtonName("questionTwo");
  }
  function scrollExchangeJump() {
    setDialogueText("...");
    setActiveButtonName("scrollExchange");
  }
  function currencyExchangeJump() {
    setDialogueText("...");
    setActiveButtonName("currencyExchange");
  }
  return /*#__PURE__*/React.createElement("div", {
    className: "shop_menu_container"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/../images/forbidden_shop/frame.png",
    className: "shop_frame_nw"
  }), /*#__PURE__*/React.createElement("img", {
    src: "/../images/forbidden_shop/frame.png",
    className: "shop_frame_ne"
  }), /*#__PURE__*/React.createElement("img", {
    src: "/../images/forbidden_shop/frame.png",
    className: "shop_frame_se"
  }), /*#__PURE__*/React.createElement("img", {
    src: "/../images/forbidden_shop/frame.png",
    className: "shop_frame_sw"
  }), /*#__PURE__*/React.createElement("div", {
    className: "shop_title"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/../images/forbidden_shop/ayakashiabysslogo.png",
    className: "shop_title_image"
  })), /*#__PURE__*/React.createElement("div", {
    className: "shop_owner_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "shop_owner"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/../images/forbidden_shop/akuji.png",
    className: "shop_owner_image"
  })), dialogueText != null && /*#__PURE__*/React.createElement("div", {
    className: "shop_owner_dialogue_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "shop_owner_nameplate"
  }, "Akuji"), /*#__PURE__*/React.createElement("div", {
    className: "shop_owner_dialogue_text",
    dangerouslySetInnerHTML: {
      __html: dialogueText
    }
  }))), /*#__PURE__*/React.createElement("div", {
    className: "shop_menu"
  }, /*#__PURE__*/React.createElement(ShopMenuButton, {
    onCLick: questionOneClick,
    buttonText: "What is this place?",
    buttonName: "questionOne",
    activeButtonName: activeButtonName
  }), /*#__PURE__*/React.createElement(ShopMenuButton, {
    onCLick: questionTwoClick,
    buttonText: "Who- What are you?",
    buttonName: "questionTwo",
    activeButtonName: activeButtonName
  }), /*#__PURE__*/React.createElement(ShopMenuButton, {
    onCLick: scrollExchangeJump,
    buttonText: "Scroll exchange",
    buttonName: "scrollExchange",
    activeButtonName: activeButtonName
  }), /*#__PURE__*/React.createElement(ShopMenuButton, {
    onCLick: currencyExchangeJump,
    buttonText: "Currency exchange",
    buttonName: "currencyExchange",
    activeButtonName: activeButtonName
  })));
}
function ShopMenuButton({
  onCLick,
  buttonText,
  buttonName,
  activeButtonName
}) {
  return /*#__PURE__*/React.createElement("svg", {
    role: "button",
    tabIndex: "0",
    name: buttonName,
    className: "shop_menu_button",
    width: "162",
    height: "32",
    onClick: () => onCLick(),
    onKeyPress: clickOnEnter
  }, /*#__PURE__*/React.createElement("defs", null, /*#__PURE__*/React.createElement("radialGradient", {
    id: "shop_button_fade",
    cx: "50%",
    cy: "50%",
    r: "95%",
    fx: "50%",
    fy: "50%"
  }, /*#__PURE__*/React.createElement("stop", {
    offset: "0%",
    style: {
      stopColor: 'black'
    }
  }), /*#__PURE__*/React.createElement("stop", {
    offset: "50%",
    style: {
      stopColor: 'rgb(0,0,0,0.3)'
    }
  }), /*#__PURE__*/React.createElement("stop", {
    offset: "90%",
    style: {
      stopColor: 'rgb(0,0,0,0.0)'
    }
  }))), activeButtonName == buttonName && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("rect", {
    className: "shop_button_back_shadow",
    width: "88%",
    height: "70%",
    y: "30%",
    x: "6%",
    fill: "url(#shop_button_fade)"
  }), /*#__PURE__*/React.createElement("rect", {
    className: "shop_button_center_shadow",
    width: "80%",
    height: "100%",
    x: "10%",
    y: "25%",
    fill: "url(#shop_button_fade)"
  }), /*#__PURE__*/React.createElement("rect", {
    className: "shop_button_back active out",
    width: "92%",
    height: "70%",
    y: "15%",
    x: "4%"
  }), /*#__PURE__*/React.createElement("rect", {
    className: "shop_button_center active",
    width: "80%",
    height: "100%",
    x: "10%"
  }), /*#__PURE__*/React.createElement("text", {
    className: "shop_button_shadow_text",
    x: "50%",
    y: "18",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, buttonText), /*#__PURE__*/React.createElement("text", {
    className: "shop_button_text",
    x: "50%",
    y: "16",
    textAnchor: "middle",
    dominantBaseline: "middle",
    fill: "white",
    style: {
      textDecoration: "none"
    }
  }, buttonText)), activeButtonName != buttonName && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("rect", {
    className: "shop_button_back_shadow",
    width: "88%",
    height: "70%",
    y: "30%",
    x: "6%",
    fill: "url(#shop_button_fade)"
  }), /*#__PURE__*/React.createElement("rect", {
    className: "shop_button_center_shadow",
    width: "80%",
    height: "100%",
    x: "10%",
    y: "25%",
    fill: "url(#shop_button_fade)"
  }), /*#__PURE__*/React.createElement("rect", {
    className: "shop_button_back",
    width: "88%",
    height: "70%",
    y: "15%",
    x: "6%"
  }), /*#__PURE__*/React.createElement("rect", {
    className: "shop_button_center",
    width: "80%",
    height: "100%",
    x: "10%"
  }), /*#__PURE__*/React.createElement("text", {
    className: "shop_button_shadow_text",
    x: "50%",
    y: "18",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, buttonText), /*#__PURE__*/React.createElement("text", {
    className: "shop_button_text",
    x: "50%",
    y: "16",
    textAnchor: "middle",
    dominantBaseline: "middle",
    fill: "#f0e2c6"
  }, buttonText)));
}
function CurrencyExchange({
  playerInventory,
  forbiddenShopAPI,
  eventData,
  userAPI
}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null);
}
function ScrollExchange({
  playerInventory,
  forbiddenShopAPI,
  eventData,
  userAPI
}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null);
}
window.ForbiddenShop = ForbiddenShop;