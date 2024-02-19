import { ModalProvider, useModal } from "../utils/modalContext.js";
import { apiFetch } from "../utils/network.js";
function RamenShopReactContainer({
  playerMoney,
  playerHealth,
  ramenOwnerDetails,
  mysteryRamenDetails,
  basicMenuOptions,
  specialMenuOptions
}) {
  return /*#__PURE__*/React.createElement(ModalProvider, null, /*#__PURE__*/React.createElement(RamenShop, {
    playerMoney: playerMoney,
    playerHealth: playerHealth,
    ramenOwnerDetails: ramenOwnerDetails,
    mysteryRamenDetails: mysteryRamenDetails,
    basicMenuOptions: basicMenuOptions,
    specialMenuOptions: specialMenuOptions
  }));
}
function RamenShop({
  playerMoney,
  playerHealth,
  ramenOwnerDetails,
  mysteryRamenDetails,
  basicMenuOptions,
  specialMenuOptions
}) {
  const {
    openModal
  } = useModal();
  function BasicRamen({
    index,
    ramenInfo
  }) {
    return /*#__PURE__*/React.createElement("div", {
      key: index,
      className: "basic_ramen"
    }, /*#__PURE__*/React.createElement("img", {
      src: ramenInfo.image,
      className: "basic_ramen_img"
    }), /*#__PURE__*/React.createElement("div", {
      className: "basic_ramen_details"
    }, /*#__PURE__*/React.createElement("div", {
      className: "basic_ramen_name"
    }, ramenInfo.name), /*#__PURE__*/React.createElement("div", {
      className: "basic_ramen_effect"
    }, ramenInfo.effect), /*#__PURE__*/React.createElement("div", {
      className: "basic_ramen_button"
    }, ramenInfo.cost)));
  }
  function SpecialRamen({
    index,
    ramenInfo
  }) {
    return /*#__PURE__*/React.createElement("div", {
      key: index,
      className: "special_ramen"
    }, /*#__PURE__*/React.createElement("img", {
      src: ramenInfo.image,
      className: "special_ramen_img"
    }), /*#__PURE__*/React.createElement("div", {
      className: "special_ramen_name"
    }, ramenInfo.name), /*#__PURE__*/React.createElement("div", {
      className: "special_ramen_description"
    }, ramenInfo.description), /*#__PURE__*/React.createElement("div", {
      className: "special_ramen_effect"
    }, ramenInfo.effect), /*#__PURE__*/React.createElement("div", {
      className: "special_ramen_duration"
    }, ramenInfo.duration), /*#__PURE__*/React.createElement("div", {
      className: "special_ramen_button"
    }, ramenInfo.cost));
  }
  return /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "row first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_owner_container",
    style: {
      background: `url(${ramenOwnerDetails.background})`
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_dialogue_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_dialogue_nameplate"
  }, ramenOwnerDetails.name), /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_dialogue_text"
  }, ramenOwnerDetails.dialogue)), /*#__PURE__*/React.createElement("img", {
    src: ramenOwnerDetails.image,
    className: "ramen_shop_owner_img"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "column second"
  }, /*#__PURE__*/React.createElement("div", {
    className: "player_info_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "player_health_container"
  }), /*#__PURE__*/React.createElement("div", {
    className: "player_money_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "player_money_label"
  }, "money:"), /*#__PURE__*/React.createElement("div", {
    className: "player_money_text"
  }, playerMoney))), /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_intro_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, ramenOwnerDetails.shop_name), /*#__PURE__*/React.createElement("div", {
    className: "intro_text"
  }, ramenOwnerDetails.shop_description)), /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "basic menu"), basicMenuOptions && /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_basic_menu_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_descriptive_text"
  }), /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_basic_menu"
  }, basicMenuOptions.map((option, index) => {
    return /*#__PURE__*/React.createElement(BasicRamen, {
      index: index,
      ramenInfo: option
    });
  }))), mysteryRamenDetails.mystery_ramen_enabled && /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_mystery_ramen_container"
  }))), specialMenuOptions && /*#__PURE__*/React.createElement("div", {
    className: "row second"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "special menu"), /*#__PURE__*/React.createElement("div", {
    className: "special_menu_container"
  }, specialMenuOptions.map((option, index) => {
    return /*#__PURE__*/React.createElement(SpecialRamen, {
      index: index,
      ramenInfo: option
    });
  })))));
}
window.RamenShopReactContainer = RamenShopReactContainer;