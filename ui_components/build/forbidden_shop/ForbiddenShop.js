import { apiFetch } from "../utils/network.js";
import { clickOnEnter } from "../utils/uiHelpers.js";
function ForbiddenShop({
  links,
  eventData,
  jutsuData,
  playerInventory
}) {
  function exchangeEventCurrency(event_name, currency_name, quantity) {
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
    jutsuData: jutsuData,
    userAPI: links.userAPI
  }), /*#__PURE__*/React.createElement(LanternEventCurrencyExchange, {
    playerInventory: playerInventory,
    forbiddenShopAPI: links.forbiddenShopAPI,
    eventData: eventData.lanternEvent,
    userAPI: links.userAPI
  }));
}
function ShopMenu({
  ShopMenuButton
}) {
  const [activeButtonName, setActiveButtonName] = React.useState(null);
  const [dialogueText, setDialogueText] = React.useState(null);
  function questionOneClick() {
    setDialogueText("A remnant from a past forgotten.\nAn abyss between the realms of yours... and <span class='dialogue_highlight'>ours</span>.");
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
    src: "/../images/forbidden_shop/bluelight.png",
    className: "shop_background_light_left"
  }), /*#__PURE__*/React.createElement("img", {
    src: "/../images/forbidden_shop/bluelight.png",
    className: "shop_background_light_right"
  }), /*#__PURE__*/React.createElement("img", {
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
    onClick: questionOneClick,
    buttonText: "What is this place?",
    buttonName: "questionOne",
    activeButtonName: activeButtonName,
    buttonClass: "button_first"
  }), /*#__PURE__*/React.createElement(ShopMenuButton, {
    onClick: questionTwoClick,
    buttonText: "Who- What are you?",
    buttonName: "questionTwo",
    activeButtonName: activeButtonName,
    buttonClass: "button_second"
  }), /*#__PURE__*/React.createElement(ShopMenuButton, {
    onClick: scrollExchangeJump,
    buttonText: "Scroll exchange",
    buttonName: "scrollExchange",
    activeButtonName: activeButtonName,
    buttonClass: "button_third"
  }), /*#__PURE__*/React.createElement(ShopMenuButton, {
    onClick: currencyExchangeJump,
    buttonText: "Currency exchange",
    buttonName: "currencyExchange",
    activeButtonName: activeButtonName,
    buttonClass: "button_fourth"
  })));
}
function ShopMenuButton({
  onClick,
  buttonText,
  buttonName,
  activeButtonName,
  buttonClass
}) {
  return /*#__PURE__*/React.createElement("svg", {
    role: "button",
    tabIndex: "0",
    name: buttonName,
    className: "shop_menu_button " + buttonClass,
    width: "162",
    height: "32",
    onClick: () => onClick(),
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
function LanternEventCurrencyExchange({
  playerInventory,
  forbiddenShopAPI,
  eventData,
  userAPI
}) {
  const [currenciesToExchange, setCurrenciesToExchange] = React.useState({
    redLantern: 10000,
    blueLantern: 1000,
    violetLantern: 500,
    goldLantern: 50,
    shadowEssence: 2
  });
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("h3", {
    className: "forbidden_shop_sub_header"
  }, "Event currency exchange"), /*#__PURE__*/React.createElement("div", {
    className: "currency_exchange box-secondary"
  }, /*#__PURE__*/React.createElement("span", {
    className: "event_name"
  }, "Festival of Lanterns event"), /*#__PURE__*/React.createElement("span", {
    className: "event_date"
  }, "July 1 - July 15, 2023"), /*#__PURE__*/React.createElement("div", {
    className: "currencies_to_exchange"
  }, /*#__PURE__*/React.createElement(CurrencyExchangeInput, {
    name: "Red Lantern",
    quantityToExchange: currenciesToExchange.redLantern,
    setQuantityToExchange: newVal => setCurrenciesToExchange(prevVal => ({
      ...prevVal,
      redLantern: newVal
    })),
    yenForEach: eventData.yen_per_lantern
  }), /*#__PURE__*/React.createElement(CurrencyExchangeInput, {
    name: "Blue Lantern",
    quantityToExchange: currenciesToExchange.blueLantern,
    setQuantityToExchange: newVal => setCurrenciesToExchange(prevVal => ({
      ...prevVal,
      blueLantern: newVal
    })),
    yenForEach: eventData.yen_per_lantern * eventData.red_lanterns_per_blue
  }), /*#__PURE__*/React.createElement(CurrencyExchangeInput, {
    name: "Violet Lantern",
    quantityToExchange: currenciesToExchange.violetLantern,
    setQuantityToExchange: newVal => setCurrenciesToExchange(prevVal => ({
      ...prevVal,
      violetLantern: newVal
    })),
    yenForEach: eventData.yen_per_lantern * eventData.red_lanterns_per_violet
  }), /*#__PURE__*/React.createElement(CurrencyExchangeInput, {
    name: "Gold Lantern",
    quantityToExchange: currenciesToExchange.goldLantern,
    setQuantityToExchange: newVal => setCurrenciesToExchange(prevVal => ({
      ...prevVal,
      goldLantern: newVal
    })),
    yenForEach: eventData.yen_per_lantern * eventData.red_lanterns_per_gold
  }), /*#__PURE__*/React.createElement(CurrencyExchangeInput, {
    name: "Shadow Essence",
    quantityToExchange: currenciesToExchange.shadowEssence,
    setQuantityToExchange: newVal => setCurrenciesToExchange(prevVal => ({
      ...prevVal,
      shadowEssence: newVal
    })),
    yenForEach: eventData.yen_per_lantern * eventData.red_lanterns_per_shadow
  }))));
}
function CurrencyExchangeInput({
  name,
  quantityToExchange,
  setQuantityToExchange,
  yenForEach
}) {
  const yenToReceive = quantityToExchange * yenForEach;
  return /*#__PURE__*/React.createElement("div", {
    className: "currency_to_exchange"
  }, /*#__PURE__*/React.createElement("span", null, name), /*#__PURE__*/React.createElement("span", null, "x", quantityToExchange.toLocaleString()), /*#__PURE__*/React.createElement("input", {
    type: "number",
    value: quantityToExchange,
    onChange: e => setQuantityToExchange(e.target.value)
  }), /*#__PURE__*/React.createElement("span", null, yenToReceive.toLocaleString(), " yen"));
}
function ScrollExchange({
  playerInventory,
  forbiddenShopAPI,
  eventData,
  jutsuData
}) {
  function exchangeForbiddenJutsuScroll(item_type, item_id) {
    apiFetch(forbiddenShopAPI, {
      request: 'exchangeForbiddenJutsuScroll',
      item_type: item_type,
      item_id: item_id
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      } else {
        // update remaining # of scrolls for display, get new list of jutsu
      }
    });
  }
  return /*#__PURE__*/React.createElement("div", {
    className: "scroll_exchange_section"
  }, /*#__PURE__*/React.createElement("div", {
    className: "scroll_exchange_header"
  }, /*#__PURE__*/React.createElement("div", {
    className: "scroll_exchange_title"
  }, "Forbidden scroll exchange"), /*#__PURE__*/React.createElement("div", {
    className: "scroll_count_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "scroll_count_label"
  }, "FORBIDDEN SCROLLS"), /*#__PURE__*/React.createElement("div", {
    className: "scroll_count"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "scroll_exchange_container"
  }, jutsuData.map(jutsu_data => /*#__PURE__*/React.createElement(JutsuScroll, {
    key: jutsu_data.jutsu_id,
    jutsu_data: jutsu_data,
    onClick: exchangeForbiddenJutsuScroll
  }))));
}
function JutsuScroll({
  jutsu_data,
  onClick
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "jutsu_scroll",
    onClick: () => onClick()
  }, /*#__PURE__*/React.createElement("div", {
    className: "jutsu_scroll_inner"
  }, /*#__PURE__*/React.createElement("div", {
    className: "jutsu_name"
  }, jutsu_data.name), /*#__PURE__*/React.createElement("div", {
    className: "jutsu_type"
  }, /*#__PURE__*/React.createElement("div", {
    className: "jutsu_scroll_divider"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "100%",
    height: "2"
  }, /*#__PURE__*/React.createElement("line", {
    x1: "0%",
    y1: "1",
    x2: "95%",
    y2: "1",
    stroke: "#77694e",
    strokeWidth: "1"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "jutsu_type_label"
  }, "forbidden ", jutsu_data.jutsu_type), /*#__PURE__*/React.createElement("div", {
    className: "jutsu_scroll_divider"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "100%",
    height: "2"
  }, /*#__PURE__*/React.createElement("line", {
    x1: "0%",
    y1: "1",
    x2: "95%",
    y2: "1",
    stroke: "#77694e",
    strokeWidth: "1"
  })))), /*#__PURE__*/React.createElement("div", {
    className: "jutsu_description"
  }, jutsu_data.description), /*#__PURE__*/React.createElement("div", {
    className: "jutsu_stats_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "jutsu_power"
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontWeight: "700"
    }
  }, "POWER:"), " ", jutsu_data.power), /*#__PURE__*/React.createElement("div", {
    className: "jutsu_cooldown"
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontWeight: "700"
    }
  }, "COOLDOWN:"), " ", jutsu_data.cooldown, " TURNS"), /*#__PURE__*/React.createElement("div", {
    className: "jutsu_effect"
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontWeight: "700"
    }
  }, "EFFECT:"), " ", jutsu_data.effect, " (", jutsu_data.effect_amount, "%)"), /*#__PURE__*/React.createElement("div", {
    className: "jutsu_duration"
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontWeight: "700"
    }
  }, "DURATION:"), " ", jutsu_data.effect_duration, " TURNS")), /*#__PURE__*/React.createElement("div", {
    className: "jutsu_scroll_divider_bottom"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "100%",
    height: "2"
  }, /*#__PURE__*/React.createElement("line", {
    x1: "0%",
    y1: "1",
    x2: "95%",
    y2: "1",
    stroke: "#77694e",
    strokeWidth: "1"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "jutsu_tags"
  }, /*#__PURE__*/React.createElement("div", {
    className: "jutsu_tag_forbidden"
  }, "forbidden technique"), /*#__PURE__*/React.createElement("div", {
    className: "jutsu_tag_scaling"
  }, "scales with rank"))));
}
window.ForbiddenShop = ForbiddenShop;