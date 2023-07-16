import { apiFetch } from "../utils/network.js";
import { clickOnEnter } from "../utils/uiHelpers.js";

function ForbiddenShop({
  links,
  eventData,
  availableEventJutsu,
  initialPlayerInventory
}) {
  const scrollExchangeRef = React.useRef(null);
  const currencyExchangeRef = React.useRef(null);
  return /*#__PURE__*/React.createElement("div", {
    className: "forbidden_shop_container"
  }, /*#__PURE__*/React.createElement(ShopMenu, {
    ShopMenuButton: ShopMenuButton,
    scrollExchangeRef: scrollExchangeRef,
    currencyExchangeRef: currencyExchangeRef
  }), /*#__PURE__*/React.createElement(ScrollExchange, {
    initialPlayerInventory: initialPlayerInventory,
    forbiddenShopAPI: links.forbiddenShopAPI,
    eventData: eventData.lanternEvent,
    availableEventJutsu: availableEventJutsu,
    userAPI: links.userAPI,
    scrollExchangeRef: scrollExchangeRef
  }), /*#__PURE__*/React.createElement(LanternEventCurrencyExchange, {
    initialPlayerInventory: initialPlayerInventory,
    eventData: eventData.lanternEvent,
    forbiddenShopApiLink: links.forbiddenShopAPI,
    currencyExchangeRef: currencyExchangeRef
  }));
}

function ShopMenu({
  ShopMenuButton,
  scrollExchangeRef,
  currencyExchangeRef
}) {
  const [activeButtonName, setActiveButtonName] = React.useState(null);
  const [dialogueText, setDialogueText] = React.useState(null);

  function scrollTo(element) {
    if (element == null) return;
    element.scrollIntoView({
      behavior: 'smooth'
    });
  }

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
    scrollTo(scrollExchangeRef.current);
  }

  function currencyExchangeJump() {
    setDialogueText("...");
    setActiveButtonName("currencyExchange");
    scrollTo(currencyExchangeRef.current);
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
  }))), activeButtonName === buttonName && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("rect", {
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
  }, buttonText)), activeButtonName !== buttonName && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("rect", {
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
  initialPlayerInventory,
  eventData,
  forbiddenShopApiLink,
  currencyExchangeRef
}) {
  const [playerInventory, setPlayerInventory] = React.useState(initialPlayerInventory);
  const playerQuantities = {
    redLantern: playerInventory.items[eventData.red_lantern_id]?.quantity || 0,
    blueLantern: playerInventory.items[eventData.blue_lantern_id]?.quantity || 0,
    violetLantern: playerInventory.items[eventData.violet_lantern_id]?.quantity || 0,
    goldLantern: playerInventory.items[eventData.gold_lantern_id]?.quantity || 0,
    shadowEssence: playerInventory.items[eventData.shadow_essence_id]?.quantity || 0
  };
  const [responseMessage, setResponseMessage] = React.useState(null);
  const totalQuantity = Object.values(playerQuantities).reduce((accum, currentValue) => {
    return accum + parseInt(currentValue);
  }, 0);

  function exchangeAllEventCurrency() {
    apiFetch(forbiddenShopApiLink, {
      request: 'exchangeAllEventCurrency',
      event_key: eventData.eventKey
    }).then(response => {
      if (response.errors.length) {
        console.error(response.errors);
        setResponseMessage(response.errors.join(' / '));
      } else {
        setPlayerInventory(response.data.playerInventory);
        setResponseMessage(response.data.message);
      }
    });
  }

  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("h3", {
    className: "currency_exchange_header section_title",
    ref: currencyExchangeRef
  }, "Event currency exchange"), /*#__PURE__*/React.createElement("div", {
    className: "currency_exchange_section box-secondary"
  }, /*#__PURE__*/React.createElement("span", {
    className: "event_name"
  }, "Festival of Lanterns event"), /*#__PURE__*/React.createElement("span", {
    className: "event_date"
  }, "July 1 - July 15, 2023"), /*#__PURE__*/React.createElement("div", {
    className: "currencies_to_exchange"
  }, /*#__PURE__*/React.createElement(CurrencyExchangeInput, {
    name: "Red Lantern",
    playerQuantity: playerQuantities.redLantern,
    quantityToExchange: playerQuantities.redLantern,
    setQuantityToExchange: newVal => setCurrenciesToExchange(prevVal => ({ ...prevVal,
      redLantern: newVal
    })),
    yenForEach: eventData.yen_per_lantern
  }), /*#__PURE__*/React.createElement(CurrencyExchangeInput, {
    name: "Blue Lantern",
    playerQuantity: playerQuantities.blueLantern,
    quantityToExchange: playerQuantities.blueLantern,
    setQuantityToExchange: newVal => setCurrenciesToExchange(prevVal => ({ ...prevVal,
      blueLantern: newVal
    })),
    yenForEach: eventData.yen_per_lantern * eventData.red_lanterns_per_blue
  }), /*#__PURE__*/React.createElement(CurrencyExchangeInput, {
    name: "Violet Lantern",
    playerQuantity: playerQuantities.violetLantern,
    quantityToExchange: playerQuantities.violetLantern,
    setQuantityToExchange: newVal => setCurrenciesToExchange(prevVal => ({ ...prevVal,
      violetLantern: newVal
    })),
    yenForEach: eventData.yen_per_lantern * eventData.red_lanterns_per_violet
  }), /*#__PURE__*/React.createElement(CurrencyExchangeInput, {
    name: "Gold Lantern",
    playerQuantity: playerQuantities.goldLantern,
    quantityToExchange: playerQuantities.goldLantern,
    setQuantityToExchange: newVal => setCurrenciesToExchange(prevVal => ({ ...prevVal,
      goldLantern: newVal
    })),
    yenForEach: eventData.yen_per_lantern * eventData.red_lanterns_per_gold
  }), /*#__PURE__*/React.createElement(CurrencyExchangeInput, {
    name: "Shadow Essence",
    playerQuantity: playerQuantities.shadowEssence,
    quantityToExchange: playerQuantities.shadowEssence,
    setQuantityToExchange: newVal => setCurrenciesToExchange(prevVal => ({ ...prevVal,
      shadowEssence: newVal
    })),
    yenForEach: eventData.yen_per_lantern * eventData.red_lanterns_per_shadow
  })), /*#__PURE__*/React.createElement("button", {
    className: `currency_exchange_button ${totalQuantity <= 0 ? "disabled" : ""}`,
    onClick: () => {
      if (totalQuantity > 0) {
        exchangeAllEventCurrency();
      }
    }
  }, "Exchange All Festival Of Lanterns Items"), /*#__PURE__*/React.createElement("p", {
    className: "response_message"
  }, responseMessage)));
}

function CurrencyExchangeInput({
  name,
  playerQuantity,
  quantityToExchange,
  setQuantityToExchange,
  yenForEach
}) {
  const yenToReceive = quantityToExchange * yenForEach;
  return /*#__PURE__*/React.createElement("div", {
    className: "currency_to_exchange"
  }, /*#__PURE__*/React.createElement("span", null, name), /*#__PURE__*/React.createElement("span", null, "x", playerQuantity.toLocaleString()), /*#__PURE__*/React.createElement("span", null, yenToReceive.toLocaleString(), " yen"));
}

function ScrollExchange({
  initialPlayerInventory,
  forbiddenShopAPI,
  eventData,
  availableEventJutsu,
  scrollExchangeRef
}) {
  const [playerInventory, setPlayerInventory] = React.useState(initialPlayerInventory);
  const [responseMessage, setResponseMessage] = React.useState(null);

  function buyForbiddenJutsu(jutsuId) {
    setResponseMessage(null);
    apiFetch(forbiddenShopAPI, {
      request: 'buyForbiddenJutsu',
      jutsu_id: jutsuId
    }).then(response => {
      if (response.errors.length) {
        setResponseMessage(response.errors);
        console.error(response.errors);
      } else {
        setResponseMessage(response.data.message);
        setPlayerInventory(response.data.playerInventory); // update remaining # of scrolls for display, get new list of jutsu
      }
    });
  }

  const jutsuForPurchase = availableEventJutsu.filter(jutsu => !playerInventory.jutsu.some(j => j.id === jutsu.id)).filter(jutsu => !playerInventory.jutsuScrolls.some(j => j.id === jutsu.id));
  return /*#__PURE__*/React.createElement("div", {
    className: "scroll_exchange_section",
    ref: scrollExchangeRef
  }, /*#__PURE__*/React.createElement("div", {
    className: "scroll_exchange_header"
  }, /*#__PURE__*/React.createElement("div", {
    className: "section_title"
  }, "Forbidden scroll exchange"), /*#__PURE__*/React.createElement("div", {
    className: "scroll_count_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "scroll_count_label"
  }, "FORBIDDEN SCROLLS"), /*#__PURE__*/React.createElement("div", {
    className: "scroll_count"
  }, playerInventory.items[eventData.forbidden_jutsu_scroll_id]?.quantity || 0))), /*#__PURE__*/React.createElement("div", {
    className: "scroll_exchange_container"
  }, jutsuForPurchase.map(jutsu => /*#__PURE__*/React.createElement(JutsuScroll, {
    key: jutsu.id,
    jutsu_data: jutsu,
    onClick: () => buyForbiddenJutsu(jutsu.id)
  })), jutsuForPurchase.length < 1 && /*#__PURE__*/React.createElement("span", {
    className: "scroll_exchange_no_jutsu"
  }, "No more forbidden jutsu available!"), /*#__PURE__*/React.createElement("div", {
    className: "scroll_exchange_response"
  }, responseMessage)));
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